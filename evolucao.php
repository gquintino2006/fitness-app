<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();
exigirLogin();

$uid = utilizadorId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'del') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM registos_peso WHERE id = ? AND utilizador_id = ?')
        ->execute([$id, $uid]);
    flash('sucesso', 'Registo eliminado.');
    redirecionar('evolucao.php');
}

$stmt = $pdo->prepare('SELECT id, data, peso_kg FROM registos_peso WHERE utilizador_id = ? ORDER BY data');
$stmt->execute([$uid]);
$registos = $stmt->fetchAll();

$labels = array_column($registos, 'data');
$pesos  = array_map('floatval', array_column($registos, 'peso_kg'));

$titulo = 'Evolução';
require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Evolução de peso</h1>

<form id="formPeso" class="card form-inline">
    <label>Data
        <input type="date" name="data" value="<?= date('Y-m-d') ?>" required>
    </label>
    <label>Peso (kg)
        <input type="number" step="0.1" name="peso_kg" min="20" max="400" required>
    </label>
    <button type="submit" class="btn btn-primary">Registar peso</button>
    <span id="msgPeso" class="muted"></span>
</form>

<section class="card">
    <canvas id="graficoPeso" height="120"></canvas>
</section>

<?php if ($registos): ?>
<section class="card">
    <table class="tabela" id="tabelaPeso">
        <thead><tr><th>Data</th><th>Peso</th><th></th></tr></thead>
        <tbody>
            <?php foreach (array_reverse($registos) as $r): ?>
                <tr>
                    <td><?= e($r['data']) ?></td>
                    <td><?= number_format((float)$r['peso_kg'], 1, ',', '') ?> kg</td>
                    <td>
                        <form method="post" onsubmit="return confirm('Eliminar registo?')">
                            <input type="hidden" name="acao" value="del">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button class="btn btn-sm btn-danger">✕</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const dadosPeso = {
        labels: <?= json_encode($labels) ?>,
        valores: <?= json_encode($pesos) ?>
    };

    const grafico = new Chart(document.getElementById('graficoPeso'), {
        type: 'line',
        data: {
            labels: dadosPeso.labels,
            datasets: [{
                label: 'Peso (kg)',
                data: dadosPeso.valores,
                borderColor: '#ff5a00',
                backgroundColor: 'rgba(255,90,0,0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    document.getElementById('formPeso').addEventListener('submit', async (ev) => {
        ev.preventDefault();
        const form = ev.target;
        const dados = new FormData(form);
        const msg = document.getElementById('msgPeso');

        try {
            const resp = await fetch('peso_registar.php', { method: 'POST', body: dados });
            const json = await resp.json();

            if (json.sucesso) {
                grafico.data.labels.push(json.data);
                grafico.data.datasets[0].data.push(json.peso);
                grafico.update();
                msg.textContent = 'Peso registado ✔';
                form.reset();
                form.data.value = json.data;

                const tbody = document.querySelector('#tabelaPeso tbody');
                if (tbody) {
                    const pesoFmt = json.peso.toFixed(1).replace('.', ',') + ' kg';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${json.data}</td><td>${pesoFmt}</td><td>
                        <form method="post" onsubmit="return confirm('Eliminar registo?')">
                            <input type="hidden" name="acao" value="del">
                            <input type="hidden" name="id" value="${json.id}">
                            <button class="btn btn-sm btn-danger">✕</button>
                        </form></td>`;
                    tbody.insertBefore(tr, tbody.firstChild);
                }
            } else {
                msg.textContent = json.erro || 'Erro ao registar.';
            }
        } catch (e) {
            msg.textContent = 'Erro de ligação.';
        }
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
