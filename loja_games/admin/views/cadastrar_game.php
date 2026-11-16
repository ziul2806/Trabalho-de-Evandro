<?php
require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../models/categorias.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/componentes/apresentacao.php';
$listaCategorias = (new Categoria())->listarCategorias();
$mensagem = '';
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf($_POST['token'] ?? null);
    $categoriaId = filter_var($_POST['categoria_id'] ?? null, FILTER_VALIDATE_INT);
    $nomeCategoria = $categoriaId ? (new Categoria())->nomePorId($categoriaId) : null;
    if (!$nomeCategoria) {
        $erro = 'Escolha uma categoria cadastrada.';
    } else {
        $imagemNome = basename($_FILES['imagem']['name']);
        $imagemTmp = $_FILES['imagem']['tmp_name'];
        $caminhoDestino = __DIR__ . '/../public/imagens/' . $imagemNome;
        if (move_uploaded_file($imagemTmp, $caminhoDestino)) {
            $novo = new Game([
                'titulo' => $_POST['titulo'],
                'descricao' => $_POST['descricao'],
                'preco' => $_POST['preco'],
                'estoque' => $_POST['estoque'],
                'categoria' => $nomeCategoria,
                'categoria_id' => $categoriaId,
                'imagem' => $imagemNome
            ]);
            if ($novo->cadastrarGame()) {
                $mensagem = 'sucesso';
            } else {
                $erro = 'Erro ao cadastrar no banco de dados.';
            }
        } else {
            $erro = 'Erro ao fazer upload da imagem.';
        }
    }
}
$dados = ['titulo' => '', 'descricao' => '', 'preco' => '', 'estoque' => '0', 'categoria' => ''];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Cadastrar Novo Jogo — GameStock</title><link rel="stylesheet" href="../public/css/formularios.css?v=3"><script defer src="../public/js/form-capa.js?v=3"></script></head>
<body><div class="form-shell"><a class="back-link" href="ver_games.php">← Voltar ao catálogo</a><p class="eyebrow">GAMESTOCK / CATÁLOGO</p><h1>Cadastrar Novo Jogo</h1><p class="form-intro">Uma nova história começa com os detalhes certos.</p>
<div id="mensagem-sucesso" class="success-message" role="status" <?= $mensagem === 'sucesso' ? '' : 'hidden' ?>>Jogo cadastrado com sucesso!</div>
<?php if ($erro): ?><p class="erro" role="alert"><?= h($erro) ?></p><?php endif; ?>
<?php if (!$listaCategorias): ?><p class="erro" role="alert">Cadastre uma categoria antes de incluir um jogo.</p><?php endif; ?>
<form method="POST" enctype="multipart/form-data" class="formulario game-form">
    <input type="hidden" name="token" value="<?= h(csrf()) ?>">
    <label for="titulo">Título</label><input id="titulo" name="titulo" maxlength="100" value="<?= h($dados['titulo']) ?>" required>
    <label for="descricao">Descrição</label><textarea id="descricao" name="descricao"><?= h($dados['descricao']) ?></textarea>
    <div class="field-pair"><div><label for="preco">Preço (R$)</label><input id="preco" name="preco" type="number" min="0" max="99999999.99" step="0.01" value="<?= h($dados['preco']) ?>" required></div><div><label for="estoque">Estoque</label><input id="estoque" name="estoque" type="number" min="0" max="2147483647" step="1" value="<?= h($dados['estoque']) ?>" required></div></div>
    <label for="categoria_id">Categoria</label><select id="categoria_id" name="categoria_id" required><?php foreach ($listaCategorias as $categoria): ?><option value="<?= (int) $categoria['id'] ?>"><?= h($categoria['nome']) ?></option><?php endforeach; ?></select>
    <label for="capa">Capa do jogo</label><div class="cover-upload"><img id="cover-preview" src="../public/imagens/sem-capa.svg" alt="Prévia da capa"><div><strong>Escolha a capa do seu jogo.</strong><p>Prefira uma imagem vertical.</p><input type="file" id="capa" name="imagem" accept="image/jpeg,image/png,image/webp" required><small id="cover-feedback" aria-live="polite">A capa aparecerá no catálogo após o cadastro.</small></div></div>
    <div class="form-actions"><button class="button button-primary" type="submit">Cadastrar</button><a class="button button-secondary" href="ver_games.php">Cancelar</a></div>
</form>
<?php if ($mensagem === 'sucesso'): ?><script>setTimeout(() => { document.getElementById('mensagem-sucesso').hidden = true; }, 3000);</script><?php endif; ?>
</div></body></html>
