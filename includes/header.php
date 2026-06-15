<?php
require_once __DIR__ . '/functions.php';
$titulo = $titulo ?? 'FitTrack';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> — FitTrack</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="logo">🏋️ Fit<span>Track</span></a>

        <?php if (estaAutenticado()): ?>
            <nav class="nav-links">
                <a href="index.php">Dashboard</a>
                <a href="treinos.php">Treinos</a>
                <a href="metas.php">Metas</a>
                <a href="evolucao.php">Evolução</a>
                <a href="planos.php">Planos</a>
                <?php if (ehAdmin()): ?>
                    <a href="exercicios.php">Exercícios</a>
                <?php endif; ?>
            </nav>
            <div class="nav-user">
                <span><?= e($_SESSION['nome']) ?></span>
                <a href="logout.php" class="btn btn-sm btn-ghost">Sair</a>
            </div>
        <?php endif; ?>
    </div>
</header>

<main class="container">
    <?php foreach (obterFlash() as $f): ?>
        <div class="flash flash-<?= e($f['tipo']) ?>"><?= e($f['mensagem']) ?></div>
    <?php endforeach; ?>
