<?php

declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function getDbConnection(): mysqli
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $database = getenv('DB_NAME') ?: 'studenthub';

    $connection = new mysqli($host, $user, $password, $database);

    if ($connection->connect_error) {
        throw new RuntimeException('Database connection failed: ' . $connection->connect_error);
    }

    if (!$connection->set_charset('utf8mb4')) {
        throw new RuntimeException('Database character set could not be configured.');
    }

    return $connection;
}
