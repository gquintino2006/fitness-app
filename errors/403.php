<?php
/**
 * errors/403.php — Acesso proibido (sem permissões)
 */
require_once __DIR__ . '/../includes/functions.php';
http_response_code(403);
$titulo = '403';
require __DIR__ . '/../includes/header.php';
?>
<div class="erro-pagina">
    <h1>403</h1>
    <p>Não tens permissão para aceder a esta página.</p>
    <a href="../index.php" class="btn btn-primary">Voltar ao início</a>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
