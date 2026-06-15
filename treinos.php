<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();
exigirLogin();

$uid = utilizadorId();

$tipo = trim($_GET['tipo'] ?? '');
$de   = trim($_GET['de'] ?? '');
$ate  = trim($_GET['ate'] ?? '');

$porPagina = 6;
$pagina    = max(1, (int)($_GET['pagina'] ?? 1));
$offset    = ($pagina - 1) * $porPagina;

$where  = ['utilizador_id = ?'];
$params = [$uid];

if ($tipo !== '') { $where[] = 'tipo = ?';   $params[] = $tipo; }
if ($de !== '')   { $where[] = 'data >= ?';  $params[] = $de; }
if ($ate !== '')  { $where[] = 'data <= ?';  $params[] = $ate; }
$clausula = implode(' AND ', $where);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM treinos WHERE $clausula");
$stmt->execute($params);
$total    = (int)$stmt->fetchColumn();
$totalPag = max(1, ceil($total / $porPagina));

$stmt = $pdo->prepare(
    "SELECT * FROM treinos WHERE $clausula
     ORDER BY data DESC, id DESC
     LIMIT $porPagina OFFSET $offset"
);
$stmt->execute($params);
$treinos = $stmt->fetchAll();

$tipos = $pdo->prepare('SELECT DISTINCT tipo FROM treinos WHERE utilizador_id = ? ORDER BY tipo');
$tipos->execute([$uid]);
$tiposLista = $tipos->fetchAll(PDO::FETCH_COLUMN);

$titulo = 'Treinos';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1 class="page-title">Os meus treinos</h1>
    <a href="treino_form.php" class="btn btn-primary">+ Novo treino</a>
</div>

<form method="get" action="treinos.php" class="filtros card">
    <label>Tipo
        <select name="tipo">
            <option value="">Todos</option>
            <?php foreach ($tiposLista as $t): ?>
                <option value="<?= e($t) ?>" <?= $tipo === $t ? 'selected' : '' ?>><?= e($t) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>De
        <input type="date" name="de" value="<?= e($de) ?>">
    </label>
    <label>Até
        <input type="date" name="ate" value="<?= e($ate) ?>">
    </label>
    <button type="submit" class="btn btn-primary">Filtrar</button>
    <a href="treinos.php" class="btn btn-ghost">Limpar</a>
</form>

<?php if ($treinos): ?>
    <div class="treino-cards">
        <?php foreach ($treinos as $t): ?>
            <div class="treino-card">
                <div class="treino-card-top">
                    <span class="badge"><?= e($t['tipo']) ?></span>
                    <span class="treino-data"><?= e($t['data']) ?></span>
                </div>
                <h3><?= e($t['nome']) ?></h3>
                <div class="treino-card-stats">
                    <span>🔥 <?= (int)$t['calorias'] ?> kcal</span>
                    <span>⏱️ <?= (int)$t['duracao_min'] ?> min</span>
                </div>
                <div class="treino-card-acoes">
                    <a href="treino_ver.php?id=<?= (int)$t['id'] ?>" class="btn btn-sm btn-ghost">Ver</a>
                    <a href="treino_form.php?id=<?= (int)$t['id'] ?>" class="btn btn-sm btn-ghost">Editar</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPag > 1): ?>
        <nav class="paginacao">
            <?php
            $qs = $_GET;
            for ($p = 1; $p <= $totalPag; $p++):
                $qs['pagina'] = $p;
            ?>
                <a href="?<?= http_build_query($qs) ?>" class="<?= $p === $pagina ? 'ativo' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php else: ?>
    <p class="muted">Nenhum treino encontrado com estes filtros.</p>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
