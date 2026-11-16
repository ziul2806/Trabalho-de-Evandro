<?php
require_once __DIR__ . '/admin/models/clientes.php';
require_once __DIR__ . '/admin/config/funcoes.php';
$idJogo = filter_var($_GET['jogo'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$sufixo = $idJogo ? '?jogo=' . $idJogo : '';
if (isset($_SESSION['usuario'])) {
    header('Location: login.php' . $sufixo);
    exit;
}
$erro = '';
$nome = '';
$email = '';
$telefone = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf($_POST['token'] ?? null);
    $nome = is_string($_POST['nome'] ?? null) ? trim($_POST['nome']) : '';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $telefone = preg_replace('/\D/', '', is_string($_POST['telefone'] ?? null) ? $_POST['telefone'] : '');
    $senhaDigitada = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
    $confirmacao = is_string($_POST['confirmar_senha'] ?? null) ? $_POST['confirmar_senha'] : '';
    if (mb_strlen($nome) < 3 || mb_strlen($nome) > 100 || strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um nome e um e-mail válidos.';
    } elseif (strlen($telefone) < 10 || strlen($telefone) > 11) {
        $erro = 'Informe um telefone com DDD.';
    } elseif (strlen($senhaDigitada) < 6 || strlen($senhaDigitada) > 72) {
        $erro = 'A senha deve ter entre 6 e 72 caracteres.';
    } elseif ($senhaDigitada !== $confirmacao) {
        $erro = 'As senhas não coincidem.';
    } elseif (!emailDisponivel($email, 'clientes')) {
        $erro = 'Este e-mail já está cadastrado. Entre na sua conta.';
    } else {
        try {
            $model = new Cliente(['nome' => $nome, 'email' => $email, 'telefone' => $telefone, 'senha' => $senhaDigitada]);
            if ($model->cadastrarCliente()) {
                $u = $model->autenticar($email, $senhaDigitada);
                if ($u) {
                    session_regenerate_id(true);
                    $_SESSION['usuario'] = $u;
                    $_SESSION['csrf'] = bin2hex(random_bytes(32));
                    $_SESSION['conta_criada'] = true;
                    header('Location: ' . destinoAposAutenticacao($idJogo));
                    exit;
                }
            }
            $erro = 'Não foi possível criar a conta. Tente novamente.';
        } catch (PDOException $e) {
            if ((string) $e->getCode() !== '23000') { throw $e; }
            $erro = 'Este e-mail já está cadastrado. Entre na sua conta.';
        }
    }
}
?>
<!DOCTYPE html><html lang="pt-br">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Criar conta — GameStock</title><link rel="stylesheet" href="admin/public/css/formularios.css?v=4"><script defer src="admin/public/js/validar-cliente.js?v=1"></script></head>
<body class="auth-page"><main class="form-shell auth-shell">
    <a class="back-link" href="login.php<?= h($sufixo) ?>">← Voltar para entrar</a><p class="eyebrow">GAMESTOCK / NOVO CLIENTE</p><h1>Uma conta.<br><span>Novas aventuras.</span></h1><p class="form-intro">Cadastre nome, e-mail, telefone e senha para comprar no catálogo.</p>
    <form method="POST" class="formulario auth-form" data-validar="cliente" data-senha-obrigatoria="1">
        <?php if ($erro): ?><p class="erro" role="alert"><?= h($erro) ?></p><?php endif; ?>
        <input type="hidden" name="token" value="<?= h(csrf()) ?>">
        <label for="nome">Nome</label><input id="nome" name="nome" maxlength="100" autocomplete="name" value="<?= h($nome) ?>" required>
        <label for="email">E-mail</label><input id="email" type="email" name="email" maxlength="150" autocomplete="email" value="<?= h($email) ?>" required>
        <label for="telefone">Telefone</label><input id="telefone" name="telefone" inputmode="numeric" maxlength="20" autocomplete="tel" placeholder="(11) 98765-4321" value="<?= h(formatarTelefone($telefone)) ?>" required>
        <label for="senha">Senha</label><input id="senha" type="password" name="senha" minlength="6" maxlength="72" autocomplete="new-password" required>
        <label for="confirmar_senha">Confirmar senha</label><input id="confirmar_senha" type="password" name="confirmar_senha" minlength="6" maxlength="72" autocomplete="new-password" required>
        <small class="field-hint">Use pelo menos 6 caracteres.</small>
        <button class="button button-primary auth-submit" type="submit">Criar conta e entrar</button>
        <p class="auth-switch">Já tem uma conta? <a href="login.php<?= h($sufixo) ?>">Entrar</a></p>
    </form>
</main></body></html>
