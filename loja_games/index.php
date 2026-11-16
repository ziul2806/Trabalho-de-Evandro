<?php
require_once __DIR__ . '/admin/models/games.php';
require_once __DIR__ . '/admin/config/funcoes.php';
require_once __DIR__ . '/admin/views/componentes/apresentacao.php';
$games = (new Game())->listarGames();
$navAtiva = 'inicio';
$baseViews = 'admin/views/';
$baseControllers = 'admin/controllers/';
$baseLogin = 'login.php';
$baseInicio = 'index.php';
$baseCss = 'admin/public/css/';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GameStock — Loja de jogos</title>
    <link rel="stylesheet" href="admin/public/css/style.css?v=4">
    <link rel="stylesheet" href="admin/public/css/experiencia.css?v=6">
</head>
<body>
<div class="site-shell">
    <?php require __DIR__ . '/admin/views/componentes/cabecalho.php'; ?>
    <main>
        <section class="hero home-banner" aria-labelledby="home-title">
            <img class="home-banner-img" src="admin/public/imagens/banner-home.jpg" alt="">
            <div class="home-banner-shade"></div>
            <div class="hero-copy">
                <p class="eyebrow"><span class="live-dot"></span> LOJA DE JOGOS</p>
                <h1 id="home-title">O próximo jogo<br><span>está no estoque.</span></h1>
                <p class="hero-description">A GameStock reúne aventuras, RPGs e jogos independentes. Escolha um jogo, confira o preço e veja se ainda há unidades.</p>
                <div class="home-search">
                    <form class="home-search" action="admin/views/ver_games.php" method="get">
                        <input type="search" name="buscar" placeholder="Nome do jogo" aria-label="Buscar um jogo pelo nome">
                        <button class="button button-primary" type="submit">Buscar</button>
                    </form>
                </div>
                <p class="home-banner-count"><?= count($games) ?> jogos na loja</p>
            </div>
        </section>
        <section class="about-grid" aria-label="Como a loja funciona">
            <article class="about-card"><h2>Escolha seu jogo</h2><p>Veja a categoria, o preço e quantas cópias ainda estão disponíveis.</p></article>
            <article class="about-card"><h2>Crie sua conta</h2><p>Informe nome, e-mail, telefone e senha para comprar com a sua conta.</p></article>
            <article class="about-card"><h2>Compra de demonstração</h2><p>Confirme o jogo na sua conta. A unidade sai do estoque e nenhum valor é cobrado.</p></article>
        </section>
        <section>
            <div class="catalog-heading">
                <div>
                    <p class="eyebrow">EM DESTAQUE</p>
                    <h2>Nossos jogos</h2>
                </div>
                <a class="button button-secondary" href="admin/views/ver_games.php">Ver catálogo completo</a>
            </div>
            <?php if (!$games): ?>
                <div class="empty-state"><h3>O catálogo ainda está vazio.</h3><p>Os jogos da loja aparecem aqui.</p></div>
            <?php else: ?>
                <div class="home-grid">
                    <?php foreach ($games as $game): ?>
                        <a class="home-card" href="admin/views/detalhes_game.php?id=<?= (int) $game['id'] ?>">
                            <img src="<?= h(caminhoCapa($game['imagem'], 'admin/public/imagens/')) ?>" alt="Capa de <?= h($game['titulo']) ?>">
                            <div>
                                <p><?= h($game['categoria'] ?: 'Sem categoria') ?></p>
                                <h3><?= h($game['titulo']) ?></h3>
                                <strong><?= h(moeda($game['preco'])) ?></strong>
                                <p><?= (int) $game['estoque'] > 0 ? (int) $game['estoque'] . ' em estoque' : 'Indisponível' ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <footer class="site-footer"><span>GAMESTOCK / O próximo jogo está no estoque.</span><a href="cadastro.php">Criar conta <?= icone('arrow') ?></a></footer>
</div>
</body>
</html>
