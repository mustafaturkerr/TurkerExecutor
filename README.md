# TurkerExecutor

Lightweight database query builder and ORM with support for MySQL and SQLite. Includes transaction management, event system, and validation.

## Features

- Capsule Manager for database connections
- Migration support with Schema Builder
- Eloquent-like Model system
- Query Builder with method chaining
- Relationship support (HasMany, BelongsTo, HasOne, ManyToMany)
- Transaction support with automatic rollback
- Event system for model operations
- Validation system
- Repository pattern implementation
- SOLID principles adherence
- Exception handling
- Multiple database support (MySQL, SQLite)

## Installation

```bash
composer require turker/executor
```

## Basic Usage

### Database Connection

```php
use TurkerExecutor\TurkerExecutor;

// MySQL Connection
TurkerExecutor::getInstance()->createCapsule([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'test',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
]);

// SQLite Connection
TurkerExecutor::getInstance()->createCapsule([
    'driver' => 'sqlite',
    'database' => 'database.sqlite'
]);
```

### Creating Tables (Migrations)

```php
use TurkerExecutor\Migrations\Migration;

class CreatePlayersTable extends Migration
{
    public function up()
    {
        $this->schema->create('players', function ($table) {
            $table->id();
            $table->string('name');
            $table->integer('money')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('players');
    }
}

// Run migration
TurkerExecutor::getInstance()->createTables(new CreatePlayersTable());
```

### Using Models with Relationships

```php
use TurkerExecutor\Model\Model;

class Player extends Model
{
    protected string $table = 'players';
    
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}

class Team extends Model
{
    protected string $table = 'teams';
    
    public function players()
    {
        return $this->hasMany(Player::class);
    }
}

// Create and query
$player = Player::create([
    'name' => 'John Doe',
    'money' => 1000
]);

// Find with relationships
$team = Team::find(1);
$players = $team->players()->where('active', true)->get();
```

### Using Transactions

```php
use TurkerExecutor\Services\DatabaseService;

$dbService = new DatabaseService($connection, $repository);

try {
    $dbService->transaction(function() use ($player1, $player2) {
        // All operations in this closure will be in a transaction
        $player1->update(['money' => $player1->money - 100]);
        $player2->update(['money' => $player2->money + 100]);
        
        // If any operation fails, everything will be rolled back automatically
        return true;
    });
} catch (ValidationException $e) {
    echo "Validation error: " . $e->getMessage();
} catch (ModelException $e) {
    echo "Model error: " . $e->getMessage();
}
```

### Using Events

```php
use TurkerExecutor\Events\EventDispatcher;

$dispatcher = new EventDispatcher();

// Add event listener
$dispatcher->addListener('money.transferred', function($data) {
    echo "Transfer completed: {$data['from']} -> {$data['to']}, Amount: {$data['amount']}";
});

// Model events are also available
$dispatcher->addListener('model.updated', function($data) {
    echo "Model updated: " . $data['model']->name;
});
```

### Using Validation

```php
use TurkerExecutor\Validation\Validator;

$validator = new Validator();

// Add validation rules
$validator->addRule('money', 'positive', fn($value) => $value >= 0);

// Validate data
if (!$validator->validate(['money' => -100])) {
    $errors = $validator->getErrors();
    // Handle validation errors
}
```

### Using Repository Pattern

```php
use TurkerExecutor\Repositories\RepositoryInterface;

class PlayerRepository implements RepositoryInterface
{
    public function find(int $id)
    {
        return Player::find($id);
    }
    
    public function findBy(array $criteria)
    {
        $query = new Player();
        foreach ($criteria as $field => $value) {
            $query = $query->where($field, '=', $value);
        }
        return $query->first();
    }
    
    // Other repository methods...
}
```

### Exception Handling

```php
try {
    $player = Player::find(1);
    $player->update(['money' => -100]);
} catch (ValidationException $e) {
    // Handle validation errors
    echo $e->getMessage();
} catch (ModelException $e) {
    // Handle model errors
    echo $e->getMessage();
} catch (DatabaseException $e) {
    // Handle database errors
    echo $e->getMessage();
}
```

## Testing

Run the test suite:

```bash
php Tests/run_service_tests.php
```

## License

MIT 