<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();
exigirLogin();

$uid = utilizadorId();
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM treinos WHERE id = ? AND utilizador_id = ?');
$stmt->execute([$id, $uid]);
$treino = $stmt->fetch();

if (!$treino) {
    flash('erro', 'Treino não encontrado.');
    redirecionar('treinos.php');
}

$stmt = $pdo->prepare(
    'SELECT s.*, e.nome AS exercicio, e.grupo_muscular
     FROM series s
     JOIN exercicios e ON e.id = s.exercicio_id
     WHERE s.treino_id = ?'
);
$stmt->execute([$id]);
$series = $stmt->fetchAll();

$titulo = $treino['nome'];
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1 class="page-title"><?= e($treino['nome']) ?></h1>
    <div>
        <a href="treino_form.php?id=<?= $id ?>" class="btn btn-ghost">Editar</a>
        <form method="post" action="treino_eliminar.php" style="display:inline"
              onsubmit="return confirm('Eliminar este treino?');">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button type="submit" class="btn btn-danger">Eliminar</button>
        </form>
    </div>
</div>

<section class="card">
    <div class="treino-detalhe">
        <div><span class="muted">Data</span><strong><?= e($treino['data']) ?></strong></div>
        <div><span class="muted">Tipo</span><strong><?= e($treino['tipo']) ?></strong></div>
        <div><span class="muted">Duração</span><strong><?= (int)$treino['duracao_min'] ?> min</strong></div>
        <div><span class="muted">Calorias</span><strong><?= (int)$treino['calorias'] ?> kcal</strong></div>
    </div>
    <?php if ($treino['notas']): ?>
        <p class="notas"><?= nl2br(e($treino['notas'])) ?></p>
    <?php endif; ?>
</section>

<section class="card">
    <div class="card-head"><h2>Exercícios</h2></div>
    <?php if ($series): ?>
        <table class="tabela">
            <thead><tr><th>Exercício</th><th>Grupo</th><th>Reps</th><th>Peso</th></tr></thead>
            <tbody>
                <?php foreach ($series as $s): ?>
                    <tr>
                        <td><?= e($s['exercicio']) ?></td>
                        <td><?= e($s['grupo_muscular']) ?></td>
                        <td><?= (int)$s['repeticoes'] ?></td>
                        <td><?= e($s['peso_kg']) ?> kg</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="muted">Sem exercícios registados neste treino.</p>
    <?php endif; ?>
</section>

<a href="treinos.php" class="btn btn-ghost">← Voltar</a>

<?php require __DIR__ . '/includes/footer.php'; ?>
