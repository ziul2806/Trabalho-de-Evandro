<?php
require_once __DIR__ . '/../models/clientes.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
$id = $edicao ? idValido($_GET['id'] ?? null) : null;
$dados = ['nome' => '', 'email' => '', 'telefone' => ''];
if ($edicao) {
    $dados = (new Cliente())->buscarClientePorId($id);
    if (!$dados) {
        http_response_code(404);
        exit('Cliente não encontrado.');
    }
}
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf($_POST['token'] ?? null);
    $dados['nome'] = is_string($_POST['nome'] ?? null) ? trim($_POST['nome']) : '';
    $dados['email'] = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $dados['telefone'] = preg_replace('/\D/', '', is_string($_POST['telefone'] ?? null) ? $_POST['telefone'] : '');
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
    $confirmacao = is_string($_POST['confirmar_senha'] ?? null) ? $_POST['confirmar_senha'] : '';
    $telefoneOk = strlen($dados['telefone']) >= 10 && strlen($dados['telefone']) <= 11;
    if (mb_strlen($dados['nome']) < 3 || mb_strlen($dados['nome']) > 100 || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL) || !$telefoneOk) {
        $erro = 'Informe nome, e-mail e telefone com DDD.';
    } elseif ((!$edicao || $senha !== '' || $confirmacao !== '') && (strlen($senha) < 6 || strlen($senha) > 72 || $senha !== $confirmacao)) {
        $erro = 'A senha deve ter entre 6 e 72 caracteres e coincidir com a confirmação.';
    } elseif (!emailDisponivel($dados['email'], 'clientes', $edicao ? $id : null)) {
        $erro = 'Este e-mail já está cadastrado.';
    } else {
        try {
            $cliente = new Cliente($dados + ['id' => $id, 'senha' => $senha]);
            $edicao ? $cliente->editarCliente() : $cliente->cadastrarCliente();
            header('Location: ver_clientes.php');
            exit;
        } catch (PDOException $e) {
            if ((string) $e->getCode() !== '23000') {
                throw $e;
            }
            $erro = 'Este e-mail já está cadastrado.';
        }
    }
}
$tituloPagina = $edicao ? 'Editar cliente' : 'Novo cliente';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($tituloPagina) ?> — GameStock</title>
    <link rel="stylesheet" href="../public/css/formularios.css?v=4">
    <script defer src="../public/js/validar-cliente.js?v=1"></script>
</head>
<body>
<div class="form-shell">
    <a class="back-link" href="ver_clientes.php">← Voltar aos clientes</a>
    <p class="eyebrow">GAMESTOCK / CLIENTES</p>
    <h1><?= h($tituloPagina) ?></h1>
    <p class="form-intro">Informe nome, e-mail, telefone e senha do cliente.</p>
    <form method="POST" class="formulario game-form" data-validar="cliente" data-senha-obrigatoria="<?= $edicao ? '0' : '1' ?>">
        <?php if ($erro): ?><p class="erro" role="alert"><?= h($erro) ?></p><?php endif; ?>
        <input type="hidden" name="token" value="<?= h(csrf()) ?>">
        <label for="nome">Nome</label>
        <input id="nome" name="nome" maxlength="100" value="<?= h($dados['nome']) ?>" required>
        <label for="email">E-mail</label>
        <input id="email" type="email" name="email" maxlength="150" value="<?= h($dados['email']) ?>" required>
        <label for="telefone">Telefone</label>
        <input id="telefone" name="telefone" inputmode="numeric" maxlength="20" placeholder="(11) 98765-4321" value="<?= h(formatarTelefone($dados['telefone'])) ?>" required>
        <label for="senha">Senha<?= $edicao ? ' (deixe em branco para manter)' : '' ?></label>
        <input id="senha" type="password" name="senha" minlength="6" maxlength="72" autocomplete="new-password" <?= $edicao ? '' : 'required' ?>>
        <label for="confirmar_senha">Confirmar senha</label>
        <input id="confirmar_senha" type="password" name="confirmar_senha" minlength="6" maxlength="72" autocomplete="new-password" <?= $edicao ? '' : 'required' ?>>
        <div class="form-actions">
            <button class="button button-primary" type="submit"><?= $edicao ? 'Salvar' : 'Cadastrar' ?></button>
            <a class="button button-secondary" href="ver_clientes.php">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
