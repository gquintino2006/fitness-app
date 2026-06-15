<?php
/**
 * errors/404.php — Página não encontrada
 */
require_once __DIR__ . '/../includes/functions.php';
http_response_code(404);
$titulo = '404';
require __DIR__ . '/../includes/header.php';
?>
<div class="erro-pagina">
    <h1>404</h1>
    <p>Esta página não existe.</p>
    <a href="../index.php" class="btn btn-primary">Voltar ao início</a>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
