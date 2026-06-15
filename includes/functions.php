<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(string $texto): string {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function estaAutenticado(): bool {
    return isset($_SESSION['utilizador_id']);
}

function utilizadorId(): ?int {
    return $_SESSION['utilizador_id'] ?? null;
}

function ehAdmin(): bool {
    return (($_SESSION['perfil'] ?? '') === 'admin');
}

function exigirLogin(): void {
    if (!estaAutenticado()) {
        redirecionar('login.php');
    }
}

function exigirAdmin(): void {
    exigirLogin();
    if (!ehAdmin()) {
        header('Location: errors/403.php');
        exit;
    }
}

function redirecionar(string $url): void {
    header('Location: ' . $url);
    exit;
}

function flash(string $tipo, string $mensagem): void {
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function obterFlash(): array {
    $msgs = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $msgs;
}

function inicioDaSemana(?string $data = null): string {
    $ts = $data ? strtotime($data) : time();
    // 'monday this week' devolve sempre a segunda-feira, mesmo ao domingo
    return date('Y-m-d', strtotime('monday this week', $ts));
}
