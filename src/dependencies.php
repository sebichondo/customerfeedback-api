<?php
error_reporting(E_ALL);
// DIC configuration

$container = $app->getContainer();

// PDO database library 
$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];

    $pdo = new PDO(
        "mysql:host=" . $settings['host'],
        $settings['user'], 
        $settings['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create database if it doesn't exist
    $createDatabaseSQL = <<<'EOD'
    CREATE DATABASE IF NOT EXISTS`$settings['dbname']`;
    CREATE USER '$settings['user']'@'$settings['host']' IDENTIFIED BY '$settings['pass']';
    GRANT ALL ON `$settings['dbname']`.* TO '$settings['user']'@'$settings['host']';
    FLUSH PRIVILEGES;"
EOD;

    $pdo->exec($createDatabaseSQL);

    $pdo = new PDO(
        "mysql:host=" . $settings['host']. ";dbname=" . $settings['dbname'],
        $settings['user'], 
        $settings['pass']
    );
    return $pdo;
};

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};
