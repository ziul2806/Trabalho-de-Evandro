<?php
require_once __DIR__ . '/../models/categorias.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/componentes/apresentacao.php';
$categorias = (new Categoria())->listarCategorias();
$navAtiva = 'categorias';
$erro = ($_GET['erro'] ?? '') === 'uso' ? 'Esta categoria está ligada a um jogo e não pode ser excluída.' : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Categorias — GameStock</title>
    <link rel="stylesheet" href="../public/css/style.css?v=4">
    <link rel="stylesheet" href="../public/css/experiencia.css?v=6">
</head>
<body>
<div class="site-shell">
    <?php require __DIR__ . '/componentes/cabecalho.php'; ?>
    <main class="users-main">
        <div class="page-heading">
            <p class="eyebrow">GAMESTOCK / CATÁLOGO</p>
            <h1>Categorias<br><span>dos jogos.</span></h1>
            <p>Cada jogo do catálogo aponta para uma categoria cadastrada aqui.</p>
        </div>
        <?php if ($erro): ?><p class="erro" role="alert"><?= h($erro) ?></p><?php endif; ?>
        <div class="users-toolbar">
            <h2>Categorias <span class="count-badge"><?= count($categorias) ?></span></h2>
            <a class="button button-primary" href="cadastrar_categoria.php"><?= icone('plus') ?> Nova categoria</a>
        </div>
        <?php if (!$categorias): ?>
            <div class="empty-state"><h3>Nenhuma categoria cadastrada.</h3><p>Crie a primeira para classificar os jogos.</p></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="users-table">
                    <thead><tr><th>Nome</th><th>Descrição</th><th>Jogos</th><th>Ações</th></tr></thead>
                    <tbody>
                    <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td><strong><?= h($categoria['nome']) ?></strong></td>
                            <td><?= h($categoria['descricao'] ?: 'Sem descrição') ?></td>
                            <td><?= (int) $categoria['jogos'] ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="icon-button" href="editar_categoria.php?id=<?= (int) $categoria['id'] ?>" aria-label="Editar <?= h($categoria['nome']) ?>"><?= icone('edit') ?></a>
                                    <form action="../controllers/deletar_categoria.php" method="POST" onsubmit="return confirm('Excluir esta categoria?');">
                                        <input type="hidden" name="id" value="<?= (int) $categoria['id'] ?>">
                                        <input type="hidden" name="token" value="<?= h(csrf()) ?>">
                                        <button class="delete-button" type="submit" aria-label="Excluir <?= h($categoria['nome']) ?>"><?= icone('trash') ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
