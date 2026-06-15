<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();

header('Content-Type: application/json');

if (!estaAutenticado()) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada.']);
    exit;
}

$uid  = utilizadorId();
$data = trim($_POST['data'] ?? '');
$peso = (float)($_POST['peso_kg'] ?? 0);

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Data inválida.']);
    exit;
}
if ($peso < 20 || $peso > 400) {
    echo json_encode(['sucesso' => false, 'erro' => 'Peso fora do intervalo válido.']);
    exit;
}

$pdo->prepare('INSERT INTO registos_peso (utilizador_id, data, peso_kg) VALUES (?, ?, ?)')
    ->execute([$uid, $data, $peso]);
$id = (int)$pdo->lastInsertId();

echo json_encode(['sucesso' => true, 'data' => $data, 'peso' => $peso, 'id' => $id]);
