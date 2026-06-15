<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();

if (estaAutenticado()) {
    redirecionar('index.php');
}

$erros = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $erros[] = 'Preenche o email e a password.';
    }

    if (!$erros) {
        $stmt = $pdo->prepare('SELECT * FROM utilizadores WHERE email = ?');
        $stmt->execute([$email]);
        $utilizador = $stmt->fetch();

        if ($utilizador && password_verify($password, $utilizador['password_hash'])) {
            session_regenerate_id(true); // previne session fixation
            $_SESSION['utilizador_id'] = $utilizador['id'];
            $_SESSION['nome']          = $utilizador['nome'];
            $_SESSION['perfil']        = $utilizador['perfil'];
            redirecionar('index.php');
        } else {
            $erros[] = 'Email ou password incorretos.';
        }
    }
}

$titulo = 'Entrar';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-card">
    <h1>Entrar</h1>

    <?php if ($erros): ?>
        <div class="flash flash-erro"><?= e($erros[0]) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label>Email
            <input type="email" name="email" value="<?= e($email) ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn btn-primary btn-block">Entrar</button>
    </form>

    <p class="auth-alt">Ainda não tens conta? <a href="register.php">Regista-te</a>.</p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
