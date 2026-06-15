<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();
exigirAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'add') {
        $nome  = trim($_POST['nome'] ?? '');
        $grupo = trim($_POST['grupo_muscular'] ?? '');
        $tipo  = trim($_POST['tipo'] ?? 'Força');
        if ($nome !== '' && $grupo !== '') {
            $pdo->prepare('INSERT INTO exercicios (nome, grupo_muscular, tipo) VALUES (?, ?, ?)')
                ->execute([$nome, $grupo, $tipo]);
            flash('sucesso', 'Exercício adicionado.');
        } else {
            flash('erro', 'Preenche o nome e o grupo muscular.');
        }
    } elseif ($acao === 'edit') {
        $id    = (int)($_POST['id'] ?? 0);
        $nome  = trim($_POST['nome'] ?? '');
        $grupo = trim($_POST['grupo_muscular'] ?? '');
        $tipo  = trim($_POST['tipo'] ?? 'Força');
        if ($nome !== '' && $grupo !== '') {
            $pdo->prepare('UPDATE exercicios SET nome=?, grupo_muscular=?, tipo=? WHERE id=?')
                ->execute([$nome, $grupo, $tipo, $id]);
            flash('sucesso', 'Exercício atualizado.');
        } else {
            flash('erro', 'Preenche o nome e o grupo muscular.');
        }
    } elseif ($acao === 'del') {
        $pdo->prepare('DELETE FROM exercicios WHERE id = ?')->execute([(int)$_POST['id']]);
        flash('sucesso', 'Exercício eliminado.');
    }

    redirecionar('exercicios.php');
}

$exercicios = $pdo->query('SELECT * FROM exercicios ORDER BY grupo_muscular, nome')->fetchAll();

$titulo = 'Exercícios';
require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Catálogo de exercícios <span class="badge">Admin</span></h1>

<form method="post" action="exercicios.php" class="card form-inline">
    <input type="hidden" name="acao" value="add">
    <label>Nome <input type="text" name="nome" required></label>
    <label>Grupo muscular <input type="text" name="grupo_muscular" required></label>
    <label>Tipo
        <select name="tipo"><option>Força</option><option>Cardio</option></select>
    </label>
    <button type="submit" class="btn btn-primary">Adicionar</button>
</form>

<section class="card">
    <table class="tabela">
        <thead><tr><th>Nome</th><th>Grupo</th><th>Tipo</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($exercicios as $ex): ?>
                <tr>
                    <td><?= e($ex['nome']) ?></td>
                    <td><?= e($ex['grupo_muscular']) ?></td>
                    <td><?= e($ex['tipo']) ?></td>
                    <td style="display:flex;gap:0.4rem">
                        <button class="btn btn-sm btn-ghost" type="button"
                                onclick="toggleEdit(<?= $ex['id'] ?>)">✎</button>
                        <form method="post" action="exercicios.php" style="display:inline"
                              onsubmit="return confirm('Eliminar?');">
                            <input type="hidden" name="acao" value="del">
                            <input type="hidden" name="id" value="<?= (int)$ex['id'] ?>">
                            <button class="btn btn-sm btn-danger">✕</button>
                        </form>
                    </td>
                </tr>
                <tr id="edit-<?= $ex['id'] ?>" style="display:none">
                    <td colspan="4">
                        <form method="post" action="exercicios.php" class="form-inline" style="margin:0.5rem 0">
                            <input type="hidden" name="acao" value="edit">
                            <input type="hidden" name="id" value="<?= (int)$ex['id'] ?>">
                            <label style="margin:0">Nome
                                <input type="text" name="nome" value="<?= e($ex['nome']) ?>"
                                       required style="width:auto">
                            </label>
                            <label style="margin:0">Grupo
                                <input type="text" name="grupo_muscular" value="<?= e($ex['grupo_muscular']) ?>"
                                       required style="width:auto">
                            </label>
                            <label style="margin:0">Tipo
                                <select name="tipo" style="width:auto">
                                    <option <?= $ex['tipo']==='Força'?'selected':'' ?>>Força</option>
                                    <option <?= $ex['tipo']==='Cardio'?'selected':'' ?>>Cardio</option>
                                </select>
                            </label>
                            <button class="btn btn-primary btn-sm">Guardar</button>
                            <button type="button" class="btn btn-ghost btn-sm"
                                    onclick="toggleEdit(<?= $ex['id'] ?>)">Cancelar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<script>
function toggleEdit(id) {
    const row = document.getElementById('edit-' + id);
    row.style.display = row.style.display === 'none' ? '' : 'none';
}
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
