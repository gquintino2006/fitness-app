<?php
/**
 * seed.php — Dados de demonstração (correr UMA vez)
 *
 * Contas criadas:
 *   admin@fittrack.pt  / admin123   (perfil admin)
 *   joao@fittrack.pt   / joao123    (utilizador normal)
 */
require_once __DIR__ . '/config/db.php';
$pdo = getDB();

// Só insere se ainda não houver utilizadores (evita duplicados)
$jaExiste = $pdo->query('SELECT COUNT(*) FROM utilizadores')->fetchColumn();
if ($jaExiste > 0) {
    exit('A base de dados já tem dados. Nada a fazer.');
}

// Utilizadores
$pdo->prepare('INSERT INTO utilizadores (nome, email, password_hash, perfil) VALUES (?, ?, ?, ?)')
    ->execute(['Administrador', 'admin@fittrack.pt', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);

$pdo->prepare('INSERT INTO utilizadores (nome, email, password_hash, perfil) VALUES (?, ?, ?, ?)')
    ->execute(['João Silva', 'joao@fittrack.pt', password_hash('joao123', PASSWORD_DEFAULT), 'utilizador']);

$joaoId = (int)$pdo->query("SELECT id FROM utilizadores WHERE email='joao@fittrack.pt'")->fetchColumn();

// Treinos de exemplo
$treinos = [
    ['Peito + Tríceps', 'Força', date('Y-m-d', strtotime('-1 day')), 52, 420],
    ['Costas + Bíceps', 'Força', date('Y-m-d', strtotime('-3 day')), 48, 390],
    ['Pernas',          'Força', date('Y-m-d', strtotime('-5 day')), 65, 610],
    ['Cardio',          'Cardio', date('Y-m-d'),                     30, 300],
];
$stmtT = $pdo->prepare('INSERT INTO treinos (utilizador_id, nome, tipo, data, duracao_min, calorias) VALUES (?,?,?,?,?,?)');
$stmtS = $pdo->prepare('INSERT INTO series (treino_id, exercicio_id, repeticoes, peso_kg) VALUES (?,?,?,?)');

foreach ($treinos as $t) {
    $stmtT->execute([$joaoId, $t[0], $t[1], $t[2], $t[3], $t[4]]);
    $tid = (int)$pdo->lastInsertId();
    $stmtS->execute([$tid, rand(1, 6), 10, 40]);
    $stmtS->execute([$tid, rand(1, 6), 12, 30]);
}

// Meta da semana atual
$semana = date('Y-m-d', strtotime('monday this week'));
$pdo->prepare('INSERT INTO metas (utilizador_id, semana_inicio, meta_treinos, meta_calorias, meta_minutos) VALUES (?,?,?,?,?)')
    ->execute([$joaoId, $semana, 5, 3500, 200]);

// Registos de peso
$stmtP = $pdo->prepare('INSERT INTO registos_peso (utilizador_id, data, peso_kg) VALUES (?,?,?)');
$pesos = [80.2, 79.8, 79.5, 79.1, 78.8, 78.5];
foreach ($pesos as $i => $p) {
    $stmtP->execute([$joaoId, date('Y-m-d', strtotime("-" . (6 - $i) * 5 . " day")), $p]);
}

// Planos
$pdo->prepare('INSERT INTO planos_treino (utilizador_id, nome, descricao, nivel, publico) VALUES (?,?,?,?,?)')
    ->execute([$joaoId, 'Força 4 dias', 'Treino de hipertrofia dividido por grupos musculares.', 'Intermédio', 1]);

echo 'Dados de demonstração criados com sucesso!<br><br>';
echo 'Admin: admin@fittrack.pt / admin123<br>';
echo 'Utilizador: joao@fittrack.pt / joao123<br><br>';
echo '<a href="login.php">Ir para o login</a>';
