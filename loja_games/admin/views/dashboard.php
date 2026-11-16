<?php
require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../models/clientes.php';
require_once __DIR__ . '/../models/usuarios.php';
require_once __DIR__ . '/../models/pedidos.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/componentes/apresentacao.php';
$games = (new Game())->listarGames();
$clientes = (new Cliente())->listarClientes();
$usuarios = (new Usuario())->listarUsuarios();
$pedidos = (new Pedido())->listarPedidos();
$relatorio = (new Pedido())->relatorio();
$navAtiva = 'painel';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel — GameStock</title>
    <link rel="stylesheet" href="../public/css/style.css?v=4">
    <link rel="stylesheet" href="../public/css/experiencia.css?v=6">
</head>
<body>
<div class="site-shell">
    <?php require __DIR__ . '/componentes/cabecalho.php'; ?>
    <main>
        <div class="page-heading">
            <p class="eyebrow">GAMESTOCK / ADMINISTRAÇÃO</p>
            <h1>Painel da loja</h1>
            <p>Resumo do catálogo, dos clientes e das vendas. O faturamento soma pedidos pagos e entregues.</p>
        </div>
        <section class="stat-grid" aria-label="Resumo">
            <article class="stat-card"><span>Jogos</span><strong><?= count($games) ?></strong><a href="ver_games.php">Abrir catálogo</a></article>
            <article class="stat-card"><span>Clientes</span><strong><?= count($clientes) ?></strong><a href="ver_clientes.php">Gerenciar clientes</a></article>
            <article class="stat-card"><span>Usuários</span><strong><?= count($usuarios) ?></strong><a href="ver_usuarios.php">Gerenciar usuários</a></article>
            <article class="stat-card"><span>Faturamento</span><strong><?= h(moeda($relatorio['faturamento'])) ?></strong><a href="ver_pedidos.php"><?= count($pedidos) ?> pedidos</a></article>
        </section>
        <section class="section-space">
            <h2>Relatório de vendas</h2>
            <?php if (!$relatorio['por_status']): ?>
                <p class="catalog-note">Ainda não há pedidos para somar.</p>
            <?php else: ?>
                <div class="table-wrap" style="margin-top:16px">
                    <table class="users-table">
                        <thead><tr><th>Status</th><th>Pedidos</th><th>Total</th></tr></thead>
                        <tbody>
                        <?php foreach ($relatorio['por_status'] as $linha): ?>
                            <tr>
                                <td><?= h(rotuloStatus($linha['status'])) ?></td>
                                <td><?= (int) $linha['quantidade'] ?></td>
                                <td><?= h(moeda($linha['total'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        <section class="section-space">
            <h2>Estoque baixo</h2>
            <?php if (!$relatorio['estoque_baixo']): ?>
                <p class="catalog-note">Nenhum jogo com 3 unidades ou menos.</p>
            <?php else: ?>
                <div class="table-wrap" style="margin-top:16px">
                    <table class="users-table">
                        <thead><tr><th>Jogo</th><th>Estoque</th></tr></thead>
                        <tbody>
                        <?php foreach ($relatorio['estoque_baixo'] as $jogo): ?>
                            <tr>
                                <td><a href="detalhes_game.php?id=<?= (int) $jogo['id'] ?>"><?= h($jogo['titulo']) ?></a></td>
                                <td><?= (int) $jogo['estoque'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
