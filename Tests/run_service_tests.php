<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TurkerExecutor\Tests\Services\PlayerServiceTest;

// Test sınıfını oluştur
$test = new PlayerServiceTest();

// Normal para transferi testini çalıştır
$test->testMoneyTransfer();

// Rollback testini çalıştır
$test->testRollback(); 