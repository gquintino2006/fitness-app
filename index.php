<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();
exigirLogin();

$uid    = utilizadorId();
$semana = inicioDaSemana();

$stmt = $pdo->prepare('SELECT * FROM metas WHERE utilizador_id = ? AND semana_inicio = ?');
$stmt->execute([$uid, $semana]);
$meta = $stmt->fetch();

$stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total,
            COALESCE(SUM(calorias), 0)   AS calorias,
            COALESCE(SUM(duracao_min),0) AS minutos
     FROM treinos
     WHERE utilizador_id = ? AND data >= ?'
);
$stmt->execute([$uid, $semana]);
$semStats = $stmt->fetch();

$stmt = $pdo->prepare(
    'SELECT peso_kg FROM registos_peso WHERE utilizador_id = ? ORDER BY data DESC LIMIT 1'
);
$stmt->execute([$uid]);
$pesoAtual = $stmt->fetchColumn();

$labels   = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
$calorias = array_fill(0, 7, 0);
$stmt = $pdo->prepare(
    'SELECT data, SUM(calorias) AS cal FROM treinos
     WHERE utilizador_id = ? AND data >= ? GROUP BY data'
);
$stmt->execute([$uid, $semana]);
foreach ($stmt->fetchAll() as $linha) {
    $idx = (int)((strtotime($linha['data']) - strtotime($semana)) / 86400);
    if ($idx >= 0 && $idx <= 6) {
        $calorias[$idx] = (int)$linha['cal'];
    }
}

$stmt = $pdo->prepare(
    'SELECT * FROM treinos WHERE utilizador_id = ? ORDER BY data DESC, id DESC LIMIT 5'
);
$stmt->execute([$uid]);
$ultimos = $stmt->fetchAll();

function pct($atual, $total) {
    if ($total <= 0) return 0;
    return min(100, round(($atual / $total) * 100));
}

$titulo = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Olá, <?= e($_SESSION['nome']) ?> 👋</h1>

<section class="stats-grid">
    <div class="stat-card">
        <span class="stat-label">Peso atual</span>
        <span class="stat-value"><?= $pesoAtual ? e($pesoAtual) . ' kg' : '—' ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Treinos esta semana</span>
        <span class="stat-value"><?= (int)$semStats['total'] ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Calorias esta semana</span>
        <span class="stat-value"><?= (int)$semStats['calorias'] ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Minutos ativos</span>
        <span class="stat-value"><?= (int)$semStats['minutos'] ?></span>
    </div>
</section>

<section class="card">
    <div class="card-head">
        <h2>Metas da semana</h2>
        <a href="metas.php" class="btn btn-sm btn-ghost">Definir metas</a>
    </div>

    <?php if ($meta): ?>
        <div class="progress-row">
            <div class="progress-info">
                <span>🏋️ Treinos</span>
                <span><?= (int)$semStats['total'] ?> / <?= (int)$meta['meta_treinos'] ?></span>
            </div>
            <div class="progress-bar"><div style="width: <?= pct($semStats['total'], $meta['meta_treinos']) ?>%"></div></div>
        </div>
        <div class="progress-row">
            <div class="progress-info">
                <span>🔥 Calorias</span>
                <span><?= (int)$semStats['calorias'] ?> / <?= (int)$meta['meta_calorias'] ?></span>
            </div>
            <div class="progress-bar"><div style="width: <?= pct($semStats['calorias'], $meta['meta_calorias']) ?>%"></div></div>
        </div>
        <div class="progress-row">
            <div class="progress-info">
                <span>⏱️ Minutos</span>
                <span><?= (int)$semStats['minutos'] ?> / <?= (int)$meta['meta_minutos'] ?></span>
            </div>
            <div class="progress-bar"><div style="width: <?= pct($semStats['minutos'], $meta['meta_minutos']) ?>%"></div></div>
        </div>
    <?php else: ?>
        <p class="muted">Ainda não definiste metas para esta semana. <a href="metas.php">Define agora</a>.</p>
    <?php endif; ?>
</section>

<section class="card">
    <div class="card-head"><h2>Calorias por dia</h2></div>
    <canvas id="graficoCalorias" height="120"></canvas>
</section>

<section class="card">
    <div class="card-head">
        <h2>Últimos treinos</h2>
        <a href="treino_form.php" class="btn btn-sm btn-primary">+ Novo treino</a>
    </div>

    <?php if ($ultimos): ?>
        <ul class="treino-list">
            <?php foreach ($ultimos as $t): ?>
                <li>
                    <a href="treino_ver.php?id=<?= (int)$t['id'] ?>">
                        <div>
                            <strong><?= e($t['nome']) ?></strong>
                            <small><?= e($t['data']) ?> · <?= e($t['tipo']) ?></small>
                        </div>
                        <div class="treino-meta">
                            <span><?= (int)$t['calorias'] ?> kcal</span>
                            <span><?= (int)$t['duracao_min'] ?> min</span>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="muted">Ainda não registaste nenhum treino.</p>
    <?php endif; ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labelsCal = <?= json_encode($labels) ?>;
    const dadosCal  = <?= json_encode($calorias) ?>;

    new Chart(document.getElementById('graficoCalorias'), {
        type: 'bar',
        data: {
            labels: labelsCal,
            datasets: [{
                label: 'Calorias',
                data: dadosCal,
                backgroundColor: '#ff5a00',
                borderRadius: 6
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
