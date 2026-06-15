<?php
function getDB(): PDO {
    $dbPath = __DIR__ . '/../data/fittrack.db';
    $primeiraVez = !file_exists($dbPath);
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("PRAGMA journal_mode=WAL");
    $pdo->exec("PRAGMA foreign_keys=ON");
    if ($primeiraVez) {
        $pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
    }
    return $pdo;
}
