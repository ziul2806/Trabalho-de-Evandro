<?php
require_once __DIR__ . '/admin/models/usuarios.php';
require_once __DIR__ . '/admin/models/clientes.php';
require_once __DIR__ . '/admin/config/funcoes.php';
$erro = '';
$email = '';
$idJogo = filter_var($_GET['jogo'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$sufixo = $idJogo ? '?jogo=' . $idJogo : '';
if (isset($_SESSION['usuario'])) {
    header('Location: ' . ($idJogo ? destinoAposAutenticacao($idJogo) : 'admin/views/perfil.php'));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf($_POST['token'] ?? null);
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $senhaDigitada = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
    $u = (new Usuario())->autenticar($email, $senhaDigitada);
    if (!$u) {
        $u = (new Cliente())->autenticar($email, $senhaDigitada);
    }
    if ($u) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $u;
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        header('Location: ' . destinoAposAutenticacao($idJogo));
        exit;
    }
    $erro = 'E-mail ou senha incorretos.';
}
?>
<!DOCTYPE html><html lang="pt-br">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Entrar — GameStock</title><link rel="stylesheet" href="admin/public/css/formularios.css?v=4"></head>
<body class="auth-page"><main class="form-shell auth-shell">
    <a class="back-link" href="admin/views/ver_games.php">← Voltar ao catálogo</a><p class="eyebrow">GAMESTOCK / SUA CONTA</p>
    <h1>Seu próximo mundo<br><span>começa aqui.</span></h1><p class="form-intro"><?= $idJogo ? 'Entre para continuar a compra deste jogo.' : 'Entre na sua conta e explore o catálogo.' ?></p>
    <form method="POST" class="formulario auth-form">
        <?php if ($erro): ?><p class="erro" role="alert"><?= h($erro) ?></p><?php endif; ?>
        <input type="hidden" name="token" value="<?= h(csrf()) ?>">
        <label for="email">E-mail</label><input id="email" type="email" name="email" maxlength="150" autocomplete="username" value="<?= h($email) ?>" required>
        <label for="senha">Senha</label><input id="senha" type="password" name="senha" autocomplete="current-password" required>
        <button class="button button-primary auth-submit" type="submit">Entrar</button>
        <p class="auth-switch">Ainda não tem uma conta? <a href="cadastro.php<?= h($sufixo) ?>">Criar conta</a></p>
    </form>
</main></body></html>
