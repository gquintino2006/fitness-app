<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();
exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('treinos.php');
}

$uid = utilizadorId();
$id  = (int)($_POST['id'] ?? 0);

$pdo->prepare('DELETE FROM treinos WHERE id = ? AND utilizador_id = ?')->execute([$id, $uid]);

flash('sucesso', 'Treino eliminado.');
redirecionar('treinos.php');
