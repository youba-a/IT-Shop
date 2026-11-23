<?php

function shopConfig(): array
{
    static $config;
    $local = __DIR__ . '/config.local.php';
    return $config ??= require is_file($local) ? $local : __DIR__ . '/config.example.php';
}
