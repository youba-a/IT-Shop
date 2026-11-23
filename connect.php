<?php

require_once __DIR__ . '/db.php';
$db = shopConfig()['db'];
try {
    $connexionDB = new mysqli($db['host'], $db['user'], $db['password'], $db['name'], $db['port']);
    $connexionDB->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('Base de données indisponible.');
}
