<?php
require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../models/categorias.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/componentes/apresentacao.php';
$listaCategorias = (new Categoria())->listarCategorias();
$game = new Game();
$dados = null;
if (isset($_GET['id'])) {
    $dados = $game->buscarGamePorId($_GET['id']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf($_POST['token'] ?? null);
    $categoriaId = filter_var($_POST['categoria_id'] ?? null, FILTER_VALIDATE_INT);
    $nomeCategoria = $categoriaId ? (new Categoria())->nomePorId($categoriaId) : null;
    if (!$nomeCategoria) {
        echo 'Escolha uma categoria cadastrada.';
        exit;
    }
    $editar = new Game([
        'id' => $_POST['id'],
        'titulo' => $_POST['titulo'],
        'descricao' => $_POST['descricao'],
        'preco' => $_POST['preco'],
        'estoque' => $_POST['estoque'],
        'categoria' => $nomeCategoria,
        'categoria_id' => $categoriaId,
        'imagem' => $_POST['imagem']
    ]);
    if ($editar->editarGame()) {
        echo "<script>alert('Jogo editado com sucesso!'); window.location.href = 'ver_games.php';</script>";
        exit;
    } else {
        echo 'Erro ao editar!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Editar Jogo — GameStock</title><link rel="stylesheet" href="../public/css/formularios.css?v=3"></head>
<body><div class="form-shell"><a class="back-link" href="ver_games.php">← Voltar ao catálogo</a><p class="eyebrow">GAMESTOCK / CATÁLOGO</p><h1>Editar Jogo</h1><p class="form-intro">Uma nova história começa com os detalhes certos.</p>
<?php if ($dados): ?><form method="POST" class="formulario game-form"><input type="hidden" name="id" value="<?= h($dados['id']) ?>">
    <input type="hidden" name="token" value="<?= h(csrf()) ?>">
    <label for="titulo">Título</label><input id="titulo" name="titulo" maxlength="100" value="<?= h($dados['titulo']) ?>" required>
    <label for="descricao">Descrição</label><textarea id="descricao" name="descricao"><?= h($dados['descricao']) ?></textarea>
    <div class="field-pair"><div><label for="preco">Preço (R$)</label><input id="preco" name="preco" type="number" min="0" max="99999999.99" step="0.01" value="<?= h($dados['preco']) ?>" required></div><div><label for="estoque">Estoque</label><input id="estoque" name="estoque" type="number" min="0" max="2147483647" step="1" value="<?= h($dados['estoque']) ?>" required></div></div>
    <?php
    $categoriaSelecionada = (int) ($dados['categoria_id'] ?? 0);
    if (!$categoriaSelecionada) {
        foreach ($listaCategorias as $categoria) {
            if ($categoria['nome'] === $dados['categoria']) { $categoriaSelecionada = (int) $categoria['id']; }
        }
    }
    ?>
    <label for="categoria_id">Categoria</label><select id="categoria_id" name="categoria_id" required><?php foreach ($listaCategorias as $categoria): ?><option value="<?= (int) $categoria['id'] ?>" <?= $categoriaSelecionada === (int) $categoria['id'] ? 'selected' : '' ?>><?= h($categoria['nome']) ?></option><?php endforeach; ?></select>
    <label for="imagem">Imagem atual</label><div class="cover-upload"><img src="<?= h(caminhoCapa($dados['imagem'])) ?>" alt="Capa atual de <?= h($dados['titulo']) ?>"><div><input id="imagem" name="imagem" value="<?= h($dados['imagem']) ?>" readonly><small>A imagem atual é mantida ao salvar as alterações.</small></div></div>
    <div class="form-actions"><button class="button button-primary" type="submit">Salvar Alterações</button><a class="button button-secondary" href="ver_games.php">Cancelar</a></div>
</form><?php else: ?><p class="erro" role="alert">Jogo não encontrado.</p><?php endif; ?>
</div></body></html>
