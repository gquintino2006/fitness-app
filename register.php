<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();

if (estaAutenticado()) {
    redirecionar('index.php');
}

$erros = [];
$nome = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirma = $_POST['confirma'] ?? '';

    if ($nome === '')                            $erros[] = 'O nome é obrigatório.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'Email inválido.';
    if (strlen($password) < 6)                   $erros[] = 'A password tem de ter pelo menos 6 caracteres.';
    if ($password !== $confirma)                 $erros[] = 'As passwords não coincidem.';

    if (!$erros) {
        $stmt = $pdo->prepare('SELECT id FROM utilizadores WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erros[] = 'Já existe uma conta com este email.';
        }
    }

    if (!$erros) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO utilizadores (nome, email, password_hash) VALUES (?, ?, ?)')
            ->execute([$nome, $email, $hash]);
        flash('sucesso', 'Conta criada com sucesso! Já podes entrar.');
        redirecionar('login.php');
    }
}

$titulo = 'Registo';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-card">
    <h1>Criar conta</h1>

    <?php if ($erros): ?>
        <div class="flash flash-erro">
            <ul>
                <?php foreach ($erros as $erro): ?>
                    <li><?= e($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php">
        <label>Nome
            <input type="text" name="nome" value="<?= e($nome) ?>" required maxlength="60">
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($email) ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" required minlength="6">
        </label>
        <label>Confirmar password
            <input type="password" name="confirma" required minlength="6">
        </label>
        <button type="submit" class="btn btn-primary btn-block">Registar</button>
    </form>

    <p class="auth-alt">Já tens conta? <a href="login.php">Entra aqui</a>.</p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
