<?php
require_once __DIR__ . '/../models/categorias.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
$model = new Categoria();
$id = $edicao ? idValido($_GET['id'] ?? null) : null;
$dados = ['nome' => '', 'descricao' => ''];
if ($edicao) {
    $dados = $model->buscarCategoriaPorId($id);
    if (!$dados) {
        http_response_code(404);
        exit('Categoria não encontrada.');
    }
}
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf($_POST['token'] ?? null);
    $dados['nome'] = is_string($_POST['nome'] ?? null) ? trim($_POST['nome']) : '';
    $dados['descricao'] = is_string($_POST['descricao'] ?? null) ? trim($_POST['descricao']) : '';
    if ($dados['nome'] === '' || mb_strlen($dados['nome']) > 50 || mb_strlen($dados['descricao']) > 255) {
        $erro = 'Informe um nome de até 50 caracteres.';
    } else {
        try {
            $categoria = new Categoria($dados + ['id' => $id]);
            $edicao ? $categoria->editarCategoria() : $categoria->cadastrarCategoria();
            header('Location: ver_categorias.php');
            exit;
        } catch (PDOException $e) {
            if ((string) $e->getCode() !== '23000') {
                throw $e;
            }
            $erro = 'Já existe uma categoria com este nome.';
        }
    }
}
$tituloPagina = $edicao ? 'Editar categoria' : 'Nova categoria';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($tituloPagina) ?> — GameStock</title>
    <link rel="stylesheet" href="../public/css/formularios.css?v=4">
</head>
<body>
<div class="form-shell">
    <a class="back-link" href="ver_categorias.php">← Voltar às categorias</a>
    <p class="eyebrow">GAMESTOCK / CATEGORIAS</p>
    <h1><?= h($tituloPagina) ?></h1>
    <p class="form-intro">O nome aparece no catálogo e no filtro de jogos.</p>
    <form method="POST" class="formulario game-form">
        <?php if ($erro): ?><p class="erro" role="alert"><?= h($erro) ?></p><?php endif; ?>
        <input type="hidden" name="token" value="<?= h(csrf()) ?>">
        <label for="nome">Nome</label>
        <input id="nome" name="nome" maxlength="50" value="<?= h($dados['nome']) ?>" required>
        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao" maxlength="255"><?= h($dados['descricao']) ?></textarea>
        <div class="form-actions">
            <button class="button button-primary" type="submit"><?= $edicao ? 'Salvar' : 'Cadastrar' ?></button>
            <a class="button button-secondary" href="ver_categorias.php">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
