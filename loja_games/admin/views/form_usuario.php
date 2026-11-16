<?php
require_once __DIR__ . '/../models/usuarios.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
$model = new Usuario();
$id = $edicao ? idValido($_GET['id'] ?? null) : null;
$dados = ['nome' => '', 'email' => '', 'tipo' => 'user'];
if ($edicao) {
    $dados = $model->buscarUsuarioPorId($id);
    if (!$dados) { http_response_code(404); exit('Usuário não encontrado.'); }
}
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf($_POST['token'] ?? null);
    foreach (['nome', 'email', 'tipo'] as $campo) {
        $dados[$campo] = is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
    }
    $novaSenha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
    if ($dados['nome'] === '' || mb_strlen($dados['nome']) > 100 || strlen($dados['email']) > 150 || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL) || !in_array($dados['tipo'], ['admin', 'user'], true)) {
        $erro = 'Informe um nome, e-mail e tipo válidos.';
    } elseif ((!$edicao || $novaSenha !== '') && strlen($novaSenha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($edicao && (int) $id === (int) $_SESSION['usuario']['id'] && $dados['tipo'] !== 'admin') {
        $erro = 'Mantenha seu próprio usuário como administrador.';
    } elseif (!emailDisponivel($dados['email'], 'usuarios', $edicao ? $id : null)) {
        $erro = 'Este e-mail já está cadastrado.';
    } else {
        try {
            $u = new Usuario($dados + ['id' => $id, 'senha' => $novaSenha]);
            $edicao ? $u->editarUsuario() : $u->cadastrarUsuario();
            if ($edicao && (int) $id === (int) $_SESSION['usuario']['id']) {
                $_SESSION['usuario'] = $model->buscarUsuarioPorId($id);
            }
            header('Location: ver_usuarios.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') { throw $e; }
            $erro = 'Este e-mail já está cadastrado.';
        }
    }
}
$tituloPagina = $edicao ? 'Editar Usuário' : 'Cadastrar Novo Usuário';
?>
<!DOCTYPE html><html lang="pt-br">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= h($tituloPagina) ?></title><link rel="stylesheet" href="../public/css/formularios.css"></head>
<body><h2><?= h($tituloPagina) ?></h2><form method="POST" class="formulario">
<?php if ($erro): ?><p class="erro" role="alert"><?= h($erro) ?></p><?php endif; ?>
<input type="hidden" name="token" value="<?= h(csrf()) ?>">
<label for="nome">Nome</label><input id="nome" name="nome" maxlength="100" value="<?= h($dados['nome']) ?>" required>
<label for="email">E-mail</label><input id="email" type="email" name="email" maxlength="150" value="<?= h($dados['email']) ?>" required>
<label for="senha">Senha<?= $edicao ? ' (deixe em branco para manter)' : '' ?></label><input id="senha" type="password" name="senha" minlength="6" autocomplete="new-password" <?= $edicao ? '' : 'required' ?>>
<label for="tipo">Tipo</label><select id="tipo" name="tipo"><option value="user" <?= $dados['tipo'] === 'user' ? 'selected' : '' ?>>Usuário</option><option value="admin" <?= $dados['tipo'] === 'admin' ? 'selected' : '' ?>>Administrador</option></select>
<button type="submit"><?= $edicao ? 'Salvar Alterações' : 'Cadastrar' ?></button><a href="ver_usuarios.php">Cancelar</a>
</form></body></html>
