<?php
require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../models/compras_demo.php';
require_once __DIR__ . '/../config/funcoes.php';
require_once __DIR__ . '/componentes/apresentacao.php';
$game = new Game();
$admin = ehAdmin();
$comprados = isset($_SESSION['usuario']) ? (new CompraDemo())->jogosComprados((int) $_SESSION['usuario']['id']) : [];
$nome = $_GET['buscar'] ?? '';
$games = $nome ? $game->buscarPorNome($nome) : $game->listarGames();
// As informações visuais usam os mesmos registros da consulta do roteiro.
$todos = $games;
$categorias = array_values(array_unique(array_filter(array_column($todos, 'categoria'))));
sort($categorias);
$estoqueTotal = array_sum(array_column($todos, 'estoque'));
$destaque = null;
foreach ($todos as $item) { if ($item['titulo'] === 'Elden Ring') { $destaque = $item; break; } }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#0c0f14">
    <title>Catálogo de Jogos — GameStock</title>
    <link rel="stylesheet" href="../public/css/style.css?v=4"><link rel="stylesheet" href="../public/css/experiencia.css?v=6"><script defer src="../public/js/catalogo.js?v=6"></script>
</head>
<body class="catalog-page">
<div class="site-shell">
    <?php require __DIR__ . '/componentes/cabecalho.php'; ?>
    <main>
        <?php if (!empty($_SESSION['conta_criada'])): unset($_SESSION['conta_criada']); ?><p class="success-message" role="status">Conta criada! Você já está conectado.</p><?php endif; ?>
        <?php if (($_GET['erro'] ?? '') === 'vinculo'): ?><p class="erro" role="alert">Este jogo não pode ser excluído porque já existe um pedido vinculado a ele.</p><?php endif; ?>
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero-copy"><p class="eyebrow"><span class="live-dot"></span> SUA PRÓXIMA AVENTURA ESTÁ AQUI</p><h1 id="hero-title">Todos os mundos.<br><span>Um só catálogo.</span></h1><p class="hero-description">De grandes aventuras a descobertas indie.<br> Encontre seu próximo jogo e dê play em uma nova história.</p><a class="hero-link" href="#catalogo">Explorar jogos <?= icone('arrow') ?></a><div class="hero-stats"><span><strong><?= count($todos) ?></strong> <?= $nome ? 'jogos encontrados' : 'jogos no catálogo' ?></span><span><strong><?= count($categorias) ?></strong> categorias</span><span><strong><?= h($estoqueTotal) ?></strong> unidades em estoque</span></div></div>
            <?php if ($destaque): ?>
            <a class="featured-game" href="detalhes_game.php?id=<?= (int) $destaque['id'] ?>" target="_blank" rel="noopener" aria-label="Ver detalhes de Elden Ring (abre em nova aba)"><img src="../public/imagens/elden-ring-hero.jpg" alt="Arte de Elden Ring" fetchpriority="high"><span class="featured-shade"></span><span class="featured-label">ESCOLHA SUA PRÓXIMA JORNADA</span><div class="featured-caption"><span class="featured-category">RPG · MUNDO ABERTO</span><h2>Elden Ring</h2><p>As Terras Intermédias esperam por você.</p></div><span class="featured-arrow"><?= icone('arrow') ?></span></a>
            <?php else: ?><div class="hero-placeholder"><?= icone('gamepad') ?><span>Novos mundos esperam por você.</span></div><?php endif; ?>
        </section>
        <section id="catalogo" class="catalog-section" aria-labelledby="catalog-title">
            <div class="catalog-heading"><div><p class="eyebrow">ENCONTRE SUA PRÓXIMA HISTÓRIA</p><h2 id="catalog-title">Explore o catálogo<span class="count-badge"><?= count($games) ?></span></h2></div><span class="catalog-note">Uma coleção para todos os estilos.</span></div>
            <div class="catalog-toolbar">
                <form method="GET" class="filter-form">
                    <div class="search-field"><?= icone('search') ?><input type="search" name="buscar" aria-label="Buscar por nome" placeholder="Qual vai ser o próximo jogo?" value="<?= h($nome) ?>"></div>
                    <select id="category-filter" aria-label="Categoria"><option value="">Todas as categorias</option><?php foreach ($categorias as $c): ?><option value="<?= h($c) ?>"><?= h($c) ?></option><?php endforeach; ?></select>
                    <select id="sort-games" aria-label="Ordenar jogos"><option value="titulo">Nome: A–Z</option><option value="preco_asc">Menor preço</option><option value="preco_desc">Maior preço</option><option value="estoque_desc">Maior estoque</option></select>
                    <button class="button button-filter" type="submit">Buscar</button>
                </form>
                <div class="view-toggle" aria-label="Modo de exibição"><button type="button" class="icon-button selected" data-view="grade" aria-label="Exibir em grade" aria-pressed="true"><?= icone('grid') ?></button><button type="button" class="icon-button" data-view="lista" aria-label="Exibir em lista" aria-pressed="false"><?= icone('list') ?></button></div>
            </div>
            <?php if ($nome !== ''): ?><div class="filter-summary"><span><?= count($games) ?> jogos encontrados para “<?= h($nome) ?>”</span><a href="ver_games.php#catalogo">Limpar busca ×</a></div><?php endif; ?>
            <?php if (isset($_GET['deletado'])): ?><p class="success-message" role="status">Jogo excluído com sucesso.</p><?php endif; ?>
            <?php if (!$games): ?>
            <div class="empty-state"><?= icone('search') ?><h3>Nenhum jogo encontrado.</h3><p>Tente outro nome ou explore todas as categorias.</p><a class="button button-primary" href="ver_games.php#catalogo">Ver todos os jogos</a></div>
            <?php else: ?>
            <div class="games-grid" id="games-grid"><?php foreach ($games as $g): ?>
                <article class="game-card<?= isset($comprados[$g['id']]) ? ' owned' : '' ?>" data-category="<?= h($g['categoria']) ?>" data-title="<?= h($g['titulo']) ?>" data-price="<?= h($g['preco']) ?>" data-stock="<?= (int) $g['estoque'] ?>"><a class="game-cover" href="detalhes_game.php?id=<?= (int) $g['id'] ?>" target="_blank" rel="noopener" aria-label="Ver detalhes de <?= h($g['titulo']) ?> (abre em nova aba)"><img src="<?= h(caminhoCapa($g['imagem'])) ?>" alt="Capa de <?= h($g['titulo']) ?>" loading="lazy" decoding="async" width="600" height="900"><?php if (isset($comprados[$g['id']])): ?><span class="owned-badge">✓ Comprado</span><?php endif; ?><span class="category-pill"><?= h($g['categoria'] ?: 'Sem categoria') ?></span></a><div class="game-info"><a class="game-details-link" href="detalhes_game.php?id=<?= (int) $g['id'] ?>" target="_blank" rel="noopener" aria-label="Ver detalhes de <?= h($g['titulo']) ?> (abre em nova aba)"><h3 title="<?= h($g['titulo']) ?>"><?= h($g['titulo']) ?></h3><p class="game-description"><?= h($g['descricao']) ?></p><div class="game-price-row"><span class="game-price"><small>R$</small> <?= number_format($g['preco'], 2, ',', '.') ?></span><?php if (isset($comprados[$g['id']])): ?><span class="owned-label">✓ Comprado</span><?php else: ?><span class="stock-status <?= (int) $g['estoque'] === 0 ? 'stock-empty' : ((int) $g['estoque'] <= 5 ? 'stock-low' : '') ?>"><i></i><?= (int) $g['estoque'] ?> em estoque</span><?php endif; ?></div></a><?php if ($admin): ?><div class="game-actions"><a href="editar_game.php?id=<?= (int) $g['id'] ?>" class="edit-link" aria-label="Editar <?= h($g['titulo']) ?>"><?= icone('edit') ?> Editar jogo</a><button type="button" class="delete-button" data-id="<?= (int) $g['id'] ?>" data-titulo="<?= h($g['titulo']) ?>" aria-label="Excluir <?= h($g['titulo']) ?>" title="Excluir jogo"><?= icone('trash') ?></button></div><?php else: ?><div class="game-actions"><?php if (isset($comprados[$g['id']])): ?><span class="owned-label">✓ Comprado</span><?php elseif ((int) $g['estoque'] < 1): ?><span class="stock-status stock-empty">Indisponível</span><?php else: ?><a class="edit-link" href="<?= isset($_SESSION['usuario']) ? 'detalhes_game.php?id=' : '../../login.php?jogo=' ?><?= (int) $g['id'] ?>" target="_blank" rel="noopener"><?= icone('gamepad') ?> <?= isset($_SESSION['usuario']) ? 'Comprar' : 'Entrar para comprar' ?></a><?php endif; ?></div><?php endif; ?></div></article>
            <?php endforeach; ?></div>
            <div class="table-wrap" id="games-list" hidden><table class="games-table"><thead><tr><th>Capa</th><th>Jogo</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th><?= $admin ? 'Ações' : 'Compra' ?></th></tr></thead><tbody><?php foreach ($games as $g): ?><tr class="game-row<?= isset($comprados[$g['id']]) ? ' owned' : '' ?>" data-category="<?= h($g['categoria']) ?>" data-title="<?= h($g['titulo']) ?>" data-price="<?= h($g['preco']) ?>" data-stock="<?= (int) $g['estoque'] ?>"><td><a href="detalhes_game.php?id=<?= (int) $g['id'] ?>" target="_blank" rel="noopener" aria-label="Ver detalhes de <?= h($g['titulo']) ?> (abre em nova aba)"><img class="table-cover" src="<?= h(caminhoCapa($g['imagem'])) ?>" alt="Capa de <?= h($g['titulo']) ?>" loading="lazy" width="60" height="90"></a></td><td><a class="table-game-link" href="detalhes_game.php?id=<?= (int) $g['id'] ?>" target="_blank" rel="noopener"><strong><?= h($g['titulo']) ?></strong></a><?php if (isset($comprados[$g['id']])): ?><span class="owned-label table-owned">✓ Comprado</span><?php endif; ?><p><?= h($g['descricao']) ?></p><small class="table-id">#<?= (int) $g['id'] ?></small></td><td><span class="table-category"><?= h($g['categoria'] ?: 'Sem categoria') ?></span></td><td class="table-price">R$ <?= number_format($g['preco'], 2, ',', '.') ?></td><td><span class="stock-status <?= (int) $g['estoque'] === 0 ? 'stock-empty' : '' ?>"><i></i><?= (int) $g['estoque'] ?> unidades</span></td><td><?php if ($admin): ?><div class="table-actions"><a href="editar_game.php?id=<?= (int) $g['id'] ?>" class="icon-button" aria-label="Editar <?= h($g['titulo']) ?>"><?= icone('edit') ?></a><button class="delete-button" type="button" data-id="<?= (int) $g['id'] ?>" data-titulo="<?= h($g['titulo']) ?>" aria-label="Excluir <?= h($g['titulo']) ?>"><?= icone('trash') ?></button></div><?php elseif (isset($comprados[$g['id']])): ?><span class="owned-label">✓ Comprado</span><?php elseif ((int) $g['estoque'] < 1): ?><span class="stock-status stock-empty">Indisponível</span><?php else: ?><a class="edit-link" href="<?= isset($_SESSION['usuario']) ? 'detalhes_game.php?id=' : '../../login.php?jogo=' ?><?= (int) $g['id'] ?>" target="_blank" rel="noopener"><?= isset($_SESSION['usuario']) ? 'Comprar' : 'Entrar para comprar' ?> <?= icone('arrow') ?></a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
            <div id="category-empty" class="empty-state" hidden><?= icone('search') ?><h3>Nenhum jogo nesta categoria.</h3><p>Escolha outra categoria ou limpe a busca.</p></div>
            <?php endif; ?>
        </section>
    </main>
    <footer class="site-footer"><span>GAMESTOCK <span class="footer-divider">/</span> Cada jogo, um novo universo.</span><a href="../../index.php">Voltar ao início <?= icone('arrow') ?></a></footer>
</div>
<?php if ($admin): ?><dialog data-token="<?= h(csrf()) ?>" id="delete-dialog" class="confirm-dialog"><form method="dialog"><span class="dialog-icon"><?= icone('trash') ?></span><h2>Excluir este jogo?</h2><p id="delete-message"></p><p class="dialog-hint">O jogo será removido do catálogo.</p><div class="dialog-actions"><button class="button button-secondary" value="cancel">Cancelar</button><button class="button button-danger" id="confirm-delete" type="button">Excluir jogo</button></div></form></dialog><?php endif; ?>
</body></html>
