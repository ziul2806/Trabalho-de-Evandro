<?php
$navAtiva = $navAtiva ?? 'catalogo';
$baseViews = $baseViews ?? '';
$baseControllers = $baseControllers ?? '../controllers/';
$baseLogin = $baseLogin ?? '../../login.php';
$baseInicio = $baseInicio ?? '../../index.php';
$baseCss = $baseCss ?? '../public/css/';
?>
<link rel="stylesheet" href="<?= h($baseCss) ?>gestao.css?v=5">
<header class="site-header">
    <a class="brand" href="<?= h($baseViews) ?>ver_games.php" aria-label="GameStock, início"><span class="brand-mark"><?= icone('gamepad') ?></span><span>GAME<span class="brand-light">STOCK</span><small>PLAY. EXPLORE. REPEAT.</small></span></a>
    <nav class="main-nav" aria-label="Navegação principal">
        <a href="<?= h($baseInicio) ?>" <?= $navAtiva === 'inicio' ? 'class="active" aria-current="page"' : '' ?>>Início</a>
        <a href="<?= h($baseViews) ?>ver_games.php" <?= $navAtiva === 'catalogo' ? 'class="active" aria-current="page"' : '' ?>>Catálogo</a>
        <a href="<?= h($baseViews) ?>ver_usuarios.php" <?= $navAtiva === 'usuarios' ? 'class="active" aria-current="page"' : '' ?>>Usuários</a>
        <?php if (isset($_SESSION['usuario'])): ?>
        <a href="<?= h($baseViews) ?>perfil.php" class="account-link<?= $navAtiva === 'perfil' ? ' active' : '' ?>" title="Meu perfil" <?= $navAtiva === 'perfil' ? 'aria-current="page"' : '' ?>><span class="account-dot"></span><?= h($_SESSION['usuario']['nome']) ?></a>
        <form action="<?= h($baseControllers) ?>logout.php" method="POST" class="nav-logout"><input type="hidden" name="token" value="<?= h(csrf()) ?>"><button class="nav-link" type="submit">Sair</button></form>
        <?php else: ?><a href="<?= h($baseLogin) ?>">Entrar</a><?php endif; ?>
    </nav>
    <?php if (ehAdmin()): ?><a class="button button-primary new-game" href="<?= h($baseViews) ?>cadastrar_game.php"><?= icone('plus') ?> Novo jogo</a><?php endif; ?>
</header>
<?php if (ehAdmin()): ?>
<nav class="admin-nav" aria-label="Gestão da loja">
    <a href="<?= h($baseViews) ?>dashboard.php" <?= $navAtiva === 'painel' ? 'class="active" aria-current="page"' : '' ?>>Painel</a>
    <a href="<?= h($baseViews) ?>ver_categorias.php" <?= $navAtiva === 'categorias' ? 'class="active" aria-current="page"' : '' ?>>Categorias</a>
    <a href="<?= h($baseViews) ?>ver_clientes.php" <?= $navAtiva === 'clientes' ? 'class="active" aria-current="page"' : '' ?>>Clientes</a>
    <a href="<?= h($baseViews) ?>ver_pedidos.php" <?= $navAtiva === 'pedidos' ? 'class="active" aria-current="page"' : '' ?>>Pedidos</a>
</nav>
<?php endif; ?>
