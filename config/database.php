<?php
declare(strict_types=1);
const DB_HOST = 'localhost';
const DB_NAME = 'CHANGE_DATABASE_NAME';
const DB_USER = 'CHANGE_DATABASE_USER';
const DB_PASS = 'CHANGE_DATABASE_PASSWORD';
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
    return $pdo;
}
