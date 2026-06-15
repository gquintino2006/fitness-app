<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();
exigirLogin();

$uid = utilizadorId();
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$erros = [];

$treino = ['nome' => '', 'tipo' => 'Força', 'data' => date('Y-m-d'),
           'duracao_min' => '', 'calorias' => '', 'notas' => ''];
$seriesExistentes = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM treinos WHERE id = ? AND utilizador_id = ?');
    $stmt->execute([$id, $uid]);
    $treino = $stmt->fetch();
    if (!$treino) {
        flash('erro', 'Treino não encontrado.');
        redirecionar('treinos.php');
    }
    $stmt = $pdo->prepare('SELECT * FROM series WHERE treino_id = ?');
    $stmt->execute([$id]);
    $seriesExistentes = $stmt->fetchAll();
}

$exercicios = $pdo->query('SELECT * FROM exercicios ORDER BY grupo_muscular, nome')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $treino['nome']        = trim($_POST['nome'] ?? '');
    $treino['tipo']        = trim($_POST['tipo'] ?? '');
    $treino['data']        = trim($_POST['data'] ?? '');
    $treino['duracao_min'] = (int)($_POST['duracao_min'] ?? 0);
    $treino['calorias']    = (int)($_POST['calorias'] ?? 0);
    $treino['notas']       = trim($_POST['notas'] ?? '');

    if ($treino['nome'] === '')                                        $erros[] = 'O nome do treino é obrigatório.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $treino['data']))        $erros[] = 'Data inválida.';
    if ($treino['duracao_min'] < 0)                                    $erros[] = 'A duração não pode ser negativa.';

    if (!$erros) {
        if ($id) {
            $pdo->prepare(
                'UPDATE treinos SET nome=?, tipo=?, data=?, duracao_min=?, calorias=?, notas=?
                 WHERE id=? AND utilizador_id=?'
            )->execute([$treino['nome'], $treino['tipo'], $treino['data'],
                        $treino['duracao_min'], $treino['calorias'], $treino['notas'], $id, $uid]);
            $pdo->prepare('DELETE FROM series WHERE treino_id = ?')->execute([$id]);
            $treinoId = $id;
        } else {
            $pdo->prepare(
                'INSERT INTO treinos (utilizador_id, nome, tipo, data, duracao_min, calorias, notas)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([$uid, $treino['nome'], $treino['tipo'], $treino['data'],
                        $treino['duracao_min'], $treino['calorias'], $treino['notas']]);
            $treinoId = (int)$pdo->lastInsertId();
        }

        $exIds = $_POST['exercicio_id'] ?? [];
        $reps  = $_POST['repeticoes']   ?? [];
        $pesos = $_POST['peso_kg']       ?? [];
        $stmt = $pdo->prepare(
            'INSERT INTO series (treino_id, exercicio_id, repeticoes, peso_kg) VALUES (?, ?, ?, ?)'
        );
        foreach ($exIds as $i => $exId) {
            if ((int)$exId > 0) {
                $stmt->execute([$treinoId, (int)$exId, (int)($reps[$i] ?? 0), (float)($pesos[$i] ?? 0)]);
            }
        }

        flash('sucesso', $id ? 'Treino atualizado.' : 'Treino criado.');
        redirecionar('treino_ver.php?id=' . $treinoId);
    }
}

$titulo = $id ? 'Editar treino' : 'Novo treino';
require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><?= $id ? 'Editar treino' : 'Novo treino' ?></h1>

<?php if ($erros): ?>
    <div class="flash flash-erro">
        <ul><?php foreach ($erros as $erro): ?><li><?= e($erro) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post" class="card" action="treino_form.php<?= $id ? '?id=' . $id : '' ?>">
    <div class="form-grid">
        <label>Nome
            <input type="text" name="nome" value="<?= e($treino['nome']) ?>" required maxlength="80">
        </label>
        <label>Tipo
            <select name="tipo">
                <?php foreach (['Força','Cardio','Full Body','HIIT'] as $op): ?>
                    <option value="<?= $op ?>" <?= $treino['tipo'] === $op ? 'selected' : '' ?>><?= $op ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Data
            <input type="date" name="data" value="<?= e($treino['data']) ?>" required>
        </label>
        <label>Duração (min)
            <input type="number" name="duracao_min" value="<?= e($treino['duracao_min']) ?>" min="0">
        </label>
        <label>Calorias
            <input type="number" name="calorias" value="<?= e($treino['calorias']) ?>" min="0">
        </label>
    </div>
    <label>Notas
        <textarea name="notas" rows="2"><?= e($treino['notas']) ?></textarea>
    </label>

    <h3>Exercícios</h3>
    <table class="tabela-series">
        <thead>
            <tr><th>Exercício</th><th>Repetições</th><th>Peso (kg)</th><th></th></tr>
        </thead>
        <tbody id="linhasSeries">
            <?php
            $linhas = $seriesExistentes ?: [['exercicio_id' => '', 'repeticoes' => '', 'peso_kg' => '']];
            foreach ($linhas as $s):
            ?>
            <tr>
                <td>
                    <select name="exercicio_id[]">
                        <option value="">— escolher —</option>
                        <?php foreach ($exercicios as $ex): ?>
                            <option value="<?= (int)$ex['id'] ?>" <?= ($s['exercicio_id'] ?? '') == $ex['id'] ? 'selected' : '' ?>>
                                <?= e($ex['nome']) ?> (<?= e($ex['grupo_muscular']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="number" name="repeticoes[]" value="<?= e($s['repeticoes'] ?? '') ?>" min="0"></td>
                <td><input type="number" step="0.5" name="peso_kg[]" value="<?= e($s['peso_kg'] ?? '') ?>" min="0"></td>
                <td><button type="button" class="btn btn-sm btn-ghost remover-linha">✕</button></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button type="button" id="addLinha" class="btn btn-sm btn-ghost">+ Adicionar exercício</button>

    <div class="form-acoes">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="treinos.php" class="btn btn-ghost">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
