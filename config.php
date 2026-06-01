<?php
const DB_HOST = '127.0.0.1';
const DB_NAME = 'stockpro';
const DB_USER = 'root';
const DB_PASS = '';

function getConnection() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_errno) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Falha na conexão ao banco de dados: ' . $mysqli->connect_error]);
        exit;
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}
