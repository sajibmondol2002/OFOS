<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'online_food_ordering';
$charset = 'utf8mb4';

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . $mysqli->connect_error);
}
$mysqli->set_charset($charset);

// Users are created by the application during registration/login.

function db_query(string $sql, string $types = '', array $params = [])
{
    global $mysqli;
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        die('Database prepare error: ' . $mysqli->error);
    }

    if ($types !== '' && count($params) > 0) {
        $bindParams = array_merge([$types], $params);
        $refs = [];
        foreach ($bindParams as $key => $value) {
            $refs[$key] = &$bindParams[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }

    if (!$stmt->execute()) {
        die('Database execute error: ' . $stmt->error);
    }

    return $stmt;
}

function db_fetch_all(string $sql, string $types = '', array $params = []): array
{
    $stmt = db_query($sql, $types, $params);
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function db_fetch_one(string $sql, string $types = '', array $params = []): ?array
{
    $stmt = db_query($sql, $types, $params);
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

function db_execute(string $sql, string $types = '', array $params = []): int
{
    $stmt = db_query($sql, $types, $params);
    return $stmt->affected_rows;
}

function db_insert_id(): int
{
    global $mysqli;
    return $mysqli->insert_id;
}
