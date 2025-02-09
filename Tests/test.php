<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TurkerExecutor\TurkerExecutor;
use TurkerExecutor\Tests\Migrations\CreatePlayersTable;
use TurkerExecutor\Tests\Migrations\CreateTeamsTable;
use TurkerExecutor\Tests\Models\PlayerModel;
use TurkerExecutor\Tests\Models\TeamModel;

// MYSQL bağlantısı kur
TurkerExecutor::getInstance()->createCapsule([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'test',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
]);


// Tabloları temizle ve yeniden oluştur
echo "Tablolar yeniden oluşturuluyor...\n";
TurkerExecutor::getInstance()->createTables(new CreateTeamsTable());
TurkerExecutor::getInstance()->createTables(new CreatePlayersTable());

// Test 1: Takım oluşturma ve doğrulama
$team1 = TeamModel::create([
    'name' => 'Team A'
]);

$team2 = TeamModel::create([
    'name' => 'Team B'
]);

// Takımların doğru oluşturulduğunu kontrol et
$foundTeam = TeamModel::find($team1->id);
echo "Test 1.1: Team A ID kontrolü: " . ($foundTeam->id === $team1->id ? "Başarılı" : "Başarısız") . "\n";
echo "Test 1.2: Team A Name kontrolü: " . ($foundTeam->name === 'Team A' ? "Başarılı" : "Başarısız") . "\n";

// Test 2: Oyuncu oluşturma ve ilişki kontrolü
$player1 = PlayerModel::create([
    'player' => 'Player1',
    'money' => 1000,
    'team_id' => $team1->id
]);

$player2 = PlayerModel::create([
    'player' => 'Player2',
    'money' => 500,
    'team_id' => $team2->id
]);

$player3 = PlayerModel::create([
    'player' => 'Player3',
    'money' => 750,
    'team_id' => $team2->id
]);

// Oyuncuların doğru oluşturulduğunu kontrol et
$foundPlayer = PlayerModel::find($player1->id);
echo "Test 2.1: Player1 ID kontrolü: " . ($foundPlayer->id === $player1->id ? "Başarılı" : "Başarısız") . "\n";
echo "Test 2.2: Player1 Team kontrolü: " . ($foundPlayer->team_id === $team1->id ? "Başarılı" : "Başarısız") . "\n";

// Test 3: İlişki testleri
$teamAPlayers = TeamModel::find($team1->id)->players()->get();
$teamBPlayers = TeamModel::find($team2->id)->players()->get();
echo "Test 3.1: Team A oyuncu sayısı: " . count($teamAPlayers) . " (Beklenen: 1)\n";
echo "Test 3.2: Team B oyuncu sayısı: " . count($teamBPlayers) . " (Beklenen: 2)\n";

// Oyuncuların takım ilişkilerini kontrol et
$player = PlayerModel::player('Player1')->first();
$team = $player->team;
echo "Test 3.3: Player1'in takımı: " . $team->name . " (Beklenen: Team A)\n";

// Test 4: Para transferi ve bakiye kontrolü
$initialMoney = $player1->money;
try {
    $player1 = PlayerModel::player('Player1')->first();
    $player1->update(['money' => $player1->money - 200]);
    
    $player2 = PlayerModel::player('Player2')->first();
    $player2->update(['money' => $player2->money + 200]);
    
    // Transfer sonrası bakiyeleri kontrol et
    $player1After = PlayerModel::player('Player1')->first();
    $player2After = PlayerModel::player('Player2')->first();
    
    echo "Test 4.1: Para transferi sonrası Player1 bakiye kontrolü: " . 
        ($player1After->money === $initialMoney - 200 ? "Başarılı" : "Başarısız") . "\n";
    echo "Test 4.2: Para transferi sonrası Player2 bakiye kontrolü: " . 
        ($player2After->money === 700 ? "Başarılı" : "Başarısız") . "\n";
} catch (\Exception $e) {
    echo "Test 4 Hata: " . $e->getMessage() . "\n";
}

// Test 5: Aktiflik durumu ve filtreleme
$player1->update(['active' => false]);
$activePlayers = PlayerModel::active()->get();
echo "Test 5.1: Aktif oyuncu sayısı: " . count($activePlayers) . " (Beklenen: 2)\n";
echo "Test 5.2: Player1 aktiflik durumu: " . ($player1->active ? "Aktif" : "Deaktif") . " (Beklenen: Deaktif)\n";

// Test 6: Toplu güncelleme
$beforeUpdate = PlayerModel::where('active', true)->get();
$beforeMoney = [];
foreach($beforeUpdate as $player) {
    $beforeMoney[$player->id] = $player->money;
}

foreach($beforeUpdate as $player) {
    $player->update(['money' => $player->money + 100]);
}

$afterUpdate = PlayerModel::where('active', true)->get();
$moneyIncreased = true;
foreach($afterUpdate as $player) {
    if($player->money !== $beforeMoney[$player->id] + 100) {
        $moneyIncreased = false;
        break;
    }
}
echo "Test 6.1: Toplu para artırımı: " . ($moneyIncreased ? "Başarılı" : "Başarısız") . "\n";

// Test 7: Veri bütünlüğü kontrolü
$allPlayers = PlayerModel::all();
$allTeams = TeamModel::all();
$playersWithoutTeam = array_filter($allPlayers, function($p) { return $p->team_id === null; });
echo "Test 7.1: Takımsız oyuncu kontrolü: " . (empty($playersWithoutTeam) ? "Başarılı" : "Başarısız") . "\n";

// Son durumu göster
echo "\nSon Durum:\n";
foreach ($allTeams as $team) {
    echo "\nTakım: {$team->name}\n";
    $players = PlayerModel::where('team_id', $team->id)->get();
    foreach ($players as $player) {
        $freshPlayer = PlayerModel::player($player->player)->first();
        echo "  Oyuncu: {$freshPlayer->player}, Para: {$freshPlayer->money}, Aktif: " . ($freshPlayer->active ? "Evet" : "Hayır") . "\n";
    }
} 