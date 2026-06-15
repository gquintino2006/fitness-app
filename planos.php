<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();
exigirLogin();

$uid   = utilizadorId();
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? 'create';

    if ($acao === 'del') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM planos_treino WHERE id = ? AND utilizador_id = ?')
            ->execute([$id, $uid]);
        flash('sucesso', 'Plano eliminado.');
        redirecionar('planos.php');
    }

    if ($acao === 'edit') {
        $id        = (int)($_POST['id'] ?? 0);
        $nome      = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $nivel     = trim($_POST['nivel'] ?? 'Iniciante');
        $publico   = isset($_POST['publico']) ? 1 : 0;
        if ($nome !== '') {
            $pdo->prepare(
                'UPDATE planos_treino SET nome=?, descricao=?, nivel=?, publico=? WHERE id=? AND utilizador_id=?'
            )->execute([$nome, $descricao, $nivel, $publico, $id, $uid]);
            flash('sucesso', 'Plano atualizado.');
        } else {
            flash('erro', 'O nome do plano é obrigatório.');
        }
        redirecionar('planos.php');
    }

    // create
    $nome      = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $nivel     = trim($_POST['nivel'] ?? 'Iniciante');
    $publico   = isset($_POST['publico']) ? 1 : 0;

    if ($nome === '') {
        $erros[] = 'O nome do plano é obrigatório.';
    }

    if (!$erros) {
        $pdo->prepare(
            'INSERT INTO planos_treino (utilizador_id, nome, descricao, nivel, publico)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$uid, $nome, $descricao, $nivel, $publico]);
        flash('sucesso', 'Plano criado.');
        redirecionar('planos.php');
    }
}

$stmt = $pdo->prepare('SELECT * FROM planos_treino WHERE utilizador_id = ? ORDER BY criado_em DESC');
$stmt->execute([$uid]);
$meusPlanos = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT p.*, u.nome AS autor
     FROM planos_treino p
     JOIN utilizadores u ON u.id = p.utilizador_id
     WHERE p.publico = 1 AND p.utilizador_id <> ?
     ORDER BY p.criado_em DESC'
);
$stmt->execute([$uid]);
$planosPublicos = $stmt->fetchAll();

$titulo = 'Planos';
require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Planos de treino</h1>

<?php if ($erros): ?>
    <div class="flash flash-erro"><?= e($erros[0]) ?></div>
<?php endif; ?>

<details class="card">
    <summary class="summary-btn">+ Criar novo plano</summary>
    <form method="post" action="planos.php" class="form-grid" style="margin-top:1rem">
        <label>Nome
            <input type="text" name="nome" required maxlength="80">
        </label>
        <label>Nível
            <select name="nivel">
                <option>Iniciante</option><option>Intermédio</option><option>Avançado</option>
            </select>
        </label>
        <label class="span-2">Descrição
            <textarea name="descricao" rows="2"></textarea>
        </label>
        <label class="checkbox span-2">
            <input type="checkbox" name="publico"> Tornar público (partilhar com outros)
        </label>
        <div class="form-acoes span-2">
            <button type="submit" class="btn btn-primary">Criar plano</button>
        </div>
    </form>
</details>

<h2 class="section-title">Os meus planos</h2>
<?php if ($meusPlanos): ?>
    <div class="plano-cards">
        <?php foreach ($meusPlanos as $p): ?>
            <div class="plano-card">
                <div class="plano-top">
                    <h3><?= e($p['nome']) ?></h3>
                    <span class="badge <?= $p['publico'] ? 'badge-verde' : '' ?>">
                        <?= $p['publico'] ? 'Público' : 'Privado' ?>
                    </span>
                </div>
                <p class="muted"><?= e($p['descricao']) ?></p>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.75rem">
                    <span class="badge"><?= e($p['nivel']) ?></span>
                    <div style="display:flex;gap:0.4rem">
                        <button class="btn btn-sm btn-ghost" type="button"
                                onclick="togglePlano(<?= $p['id'] ?>)">✎ Editar</button>
                        <form method="post" action="planos.php" style="display:inline"
                              onsubmit="return confirm('Eliminar plano?')">
                            <input type="hidden" name="acao" value="del">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button class="btn btn-sm btn-danger">✕</button>
                        </form>
                    </div>
                </div>
                <div id="plano-edit-<?= $p['id'] ?>"
                     style="display:none;margin-top:1rem;border-top:1px solid var(--borda);padding-top:1rem">
                    <form method="post" action="planos.php" class="form-grid">
                        <input type="hidden" name="acao" value="edit">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <label>Nome
                            <input type="text" name="nome" value="<?= e($p['nome']) ?>"
                                   required maxlength="80">
                        </label>
                        <label>Nível
                            <select name="nivel">
                                <option <?= $p['nivel']==='Iniciante'?'selected':'' ?>>Iniciante</option>
                                <option <?= $p['nivel']==='Intermédio'?'selected':'' ?>>Intermédio</option>
                                <option <?= $p['nivel']==='Avançado'?'selected':'' ?>>Avançado</option>
                            </select>
                        </label>
                        <label class="span-2">Descrição
                            <textarea name="descricao" rows="2"><?= e($p['descricao']) ?></textarea>
                        </label>
                        <label class="checkbox span-2">
                            <input type="checkbox" name="publico" <?= $p['publico']?'checked':'' ?>>
                            Tornar público
                        </label>
                        <div class="form-acoes span-2">
                            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                            <button type="button" class="btn btn-ghost btn-sm"
                                    onclick="togglePlano(<?= $p['id'] ?>)">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="muted">Ainda não criaste nenhum plano.</p>
<?php endif; ?>

<h2 class="section-title">Planos partilhados pela comunidade</h2>
<?php if ($planosPublicos): ?>
    <div class="plano-cards">
        <?php foreach ($planosPublicos as $p): ?>
            <div class="plano-card">
                <div class="plano-top">
                    <h3><?= e($p['nome']) ?></h3>
                    <span class="badge badge-verde">Público</span>
                </div>
                <p class="muted"><?= e($p['descricao']) ?></p>
                <div class="plano-meta">
                    <span class="badge"><?= e($p['nivel']) ?></span>
                    <small>por <?= e($p['autor']) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="muted">Ainda não há planos públicos de outros utilizadores.</p>
<?php endif; ?>

<script>
function togglePlano(id) {
    const el = document.getElementById('plano-edit-' + id);
    el.style.display = el.style.display === 'none' ? '' : 'none';
}
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
