<?php

namespace TurkerExecutor\Tests\Services;

use TurkerExecutor\Services\DatabaseService;
use TurkerExecutor\Events\EventDispatcher;
use TurkerExecutor\Validation\Validator;
use TurkerExecutor\Tests\Models\PlayerModel;
use TurkerExecutor\TurkerExecutor;
use TurkerExecutor\Tests\Repositories\PlayerRepository;
use TurkerExecutor\Exceptions\ValidationException;
use TurkerExecutor\Exceptions\ModelException;
use TurkerExecutor\Tests\Migrations\CreatePlayersTable;
use TurkerExecutor\Tests\Migrations\CreateTeamsTable;

class PlayerServiceTest
{
    private DatabaseService $dbService;
    private EventDispatcher $eventDispatcher;
    private Validator $validator;
    private array $eventLog = [];

    public function __construct()
    {
        // Bağlantıyı kur
        TurkerExecutor::getInstance()->createCapsule([
            'driver' => 'sqlite',
            'database' => __DIR__ . '/test.sqlite',
        ]);

        // Tabloları oluştur
        TurkerExecutor::getInstance()->createTables(new CreateTeamsTable());
        TurkerExecutor::getInstance()->createTables(new CreatePlayersTable());

        // Test verilerini oluştur
        PlayerModel::create([
            'player' => 'Player1',
            'money' => 1000,
            'active' => true
        ]);

        PlayerModel::create([
            'player' => 'Player2',
            'money' => 500,
            'active' => true
        ]);

        // Servisleri oluştur
        $connection = TurkerExecutor::getInstance()->getCapsule()->getConnection();
        $this->dbService = new DatabaseService($connection, new PlayerRepository());
        $this->eventDispatcher = new EventDispatcher();
        $this->validator = new Validator();

        // Validasyon kurallarını ekle
        $this->validator->addRule('money', 'positive', fn($value) => $value >= 0);

        // Model event listener'larını ekle
        $this->setupModelEventListeners();
    }

    protected function setupModelEventListeners(): void
    {
        $events = ['saving', 'saved', 'updating', 'updated'];
        foreach ($events as $event) {
            $this->eventDispatcher->addListener("model.{$event}", function($data) use ($event) {
                $this->eventLog[] = "Model {$event}: " . $data['model']->player;
                echo $this->eventLog[count($this->eventLog) - 1] . "\n";
            });
        }
    }

    public function testMoneyTransfer(): void
    {
        echo "Para transferi testi başlıyor...\n";

        // Event listener ekle
        $this->eventDispatcher->addListener('money.transferred', function($data) {
            echo "Transfer gerçekleşti: {$data['from']} -> {$data['to']}, Miktar: {$data['amount']}\n";
        });

        try {
            // Transaction içinde para transferi
            $this->dbService->transaction(function() {
                $player1 = PlayerModel::player('Player1')->first();
                $player2 = PlayerModel::player('Player2')->first();

                $transferAmount = 100;

                // Validasyon kontrolü
                if (!$this->validator->validate(['money' => $player1->money - $transferAmount])) {
                    throw ValidationException::invalidValue('money', $player1->money - $transferAmount);
                }

                // Para transferi
                $player1->update(['money' => $player1->money - $transferAmount]);
                $player2->update(['money' => $player2->money + $transferAmount]);

                // Event'i tetikle
                $this->eventDispatcher->dispatch('money.transferred', [
                    'from' => $player1->player,
                    'to' => $player2->player,
                    'amount' => $transferAmount
                ]);

                return true;
            });

            echo "Test başarılı!\n";
        } catch (ValidationException $e) {
            echo "Validasyon hatası: " . $e->getMessage() . "\n";
        } catch (ModelException $e) {
            echo "Model hatası: " . $e->getMessage() . "\n";
        } catch (\Exception $e) {
            echo "Beklenmeyen hata: " . $e->getMessage() . "\n";
        }
    }

    public function testRollback(): void
    {
        echo "\nRollback testi başlıyor...\n";

        try {
            // İlk bakiyeleri al
            $player1 = PlayerModel::player('Player1')->first();
            $player2 = PlayerModel::player('Player2')->first();
            $initialBalance1 = $player1->money;
            $initialBalance2 = $player2->money;

            echo "Başlangıç bakiyeleri: Player1: {$initialBalance1}, Player2: {$initialBalance2}\n";

            // Hata fırlatacak bir transaction başlat
            $this->dbService->transaction(function() use ($player1, $player2) {
                // İlk transfer başarılı
                $player1->update(['money' => $player1->money - 50]);
                $player2->update(['money' => $player2->money + 50]);

                // İkinci transfer hata fırlatacak (yetersiz bakiye)
                if (!$this->validator->validate(['money' => $player1->money - 1000])) {
                    throw ValidationException::invalidValue('money', $player1->money - 1000);
                }

                return true;
            });
        } catch (ValidationException $e) {
            echo "Beklenen validasyon hatası alındı: " . $e->getMessage() . "\n";

            // Rollback sonrası bakiyeleri kontrol et
            $player1After = PlayerModel::player('Player1')->first();
            $player2After = PlayerModel::player('Player2')->first();

            echo "Rollback sonrası bakiyeler: Player1: {$player1After->money}, Player2: {$player2After->money}\n";
            
            // Bakiyeler eski haline döndü mü?
            if ($player1After->money === $initialBalance1 && $player2After->money === $initialBalance2) {
                echo "Rollback başarılı - Bakiyeler eski haline döndü!\n";
            } else {
                echo "Rollback başarısız - Bakiyeler eski haline dönmedi!\n";
            }
        }
    }
} 