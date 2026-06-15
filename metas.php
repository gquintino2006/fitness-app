<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();
exigirLogin();

$uid    = utilizadorId();
$semana = inicioDaSemana();

$stmt = $pdo->prepare('SELECT * FROM metas WHERE utilizador_id = ? AND semana_inicio = ?');
$stmt->execute([$uid, $semana]);
$meta = $stmt->fetch() ?: ['meta_treinos' => 3, 'meta_calorias' => 2000, 'meta_minutos' => 150];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mt = max(0, (int)($_POST['meta_treinos'] ?? 0));
    $mc = max(0, (int)($_POST['meta_calorias'] ?? 0));
    $mm = max(0, (int)($_POST['meta_minutos'] ?? 0));

    $pdo->prepare(
        'INSERT INTO metas (utilizador_id, semana_inicio, meta_treinos, meta_calorias, meta_minutos)
         VALUES (?, ?, ?, ?, ?)
         ON CONFLICT(utilizador_id, semana_inicio) DO UPDATE SET
             meta_treinos=excluded.meta_treinos,
             meta_calorias=excluded.meta_calorias,
             meta_minutos=excluded.meta_minutos'
    )->execute([$uid, $semana, $mt, $mc, $mm]);

    flash('sucesso', 'Metas guardadas.');
    redirecionar('index.php');
}

$titulo = 'Metas';
require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Metas da semana</h1>
<p class="muted">Semana a começar em <?= e($semana) ?>.</p>

<form method="post" action="metas.php" class="card form-grid-2">
    <label>Treinos
        <input type="number" name="meta_treinos" value="<?= e($meta['meta_treinos']) ?>" min="0" required>
    </label>
    <label>Calorias a queimar
        <input type="number" name="meta_calorias" value="<?= e($meta['meta_calorias']) ?>" min="0" required>
    </label>
    <label>Minutos ativos
        <input type="number" name="meta_minutos" value="<?= e($meta['meta_minutos']) ?>" min="0" required>
    </label>
    <div class="form-acoes">
        <button type="submit" class="btn btn-primary">Guardar metas</button>
        <a href="index.php" class="btn btn-ghost">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
