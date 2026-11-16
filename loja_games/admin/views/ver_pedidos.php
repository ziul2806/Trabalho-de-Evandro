<?php
require_once __DIR__ . '/../models/pedidos.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/componentes/apresentacao.php';
$pedidos = (new Pedido())->listarPedidos();
$navAtiva = 'pedidos';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedidos — GameStock</title>
    <link rel="stylesheet" href="../public/css/style.css?v=4">
    <link rel="stylesheet" href="../public/css/experiencia.css?v=6">
</head>
<body>
<div class="site-shell">
    <?php require __DIR__ . '/componentes/cabecalho.php'; ?>
    <main class="users-main">
        <div class="page-heading">
            <p class="eyebrow">GAMESTOCK / VENDAS</p>
            <h1>Pedidos<br><span>da loja.</span></h1>
            <p>Uma compra no catálogo também gera um pedido. Cancelar ou excluir devolve as unidades ao estoque.</p>
        </div>
        <div class="users-toolbar">
            <h2>Pedidos <span class="count-badge"><?= count($pedidos) ?></span></h2>
            <a class="button button-primary" href="cadastrar_pedido.php"><?= icone('plus') ?> Novo pedido</a>
        </div>
        <?php if (!$pedidos): ?>
            <div class="empty-state"><h3>Nenhum pedido registrado.</h3><p>Registre uma venda ou compre um jogo no catálogo.</p></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="users-table">
                    <thead><tr><th>Código</th><th>Comprador</th><th>Itens</th><th>Total</th><th>Status</th><th>Data</th><th>Ações</th></tr></thead>
                    <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= h($pedido['codigo']) ?></td>
                            <td><?= h($pedido['comprador']) ?></td>
                            <td><?= h(implode(', ', $pedido['itens_texto']) ?: 'Sem itens') ?></td>
                            <td><?= h(moeda($pedido['total'])) ?></td>
                            <td><span class="status-badge status-<?= h($pedido['status']) ?>"><?= h(rotuloStatus($pedido['status'])) ?></span></td>
                            <td class="user-date"><?= h(date('d/m/Y', strtotime($pedido['created_at']))) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="icon-button" href="editar_pedido.php?id=<?= (int) $pedido['id'] ?>" aria-label="Editar pedido <?= h($pedido['codigo']) ?>"><?= icone('edit') ?></a>
                                    <form action="../controllers/deletar_pedido.php" method="POST" onsubmit="return confirm('Excluir este pedido? Se ele não estiver cancelado, o estoque volta.');">
                                        <input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
                                        <input type="hidden" name="token" value="<?= h(csrf()) ?>">
                                        <button class="delete-button" type="submit" aria-label="Excluir pedido <?= h($pedido['codigo']) ?>"><?= icone('trash') ?></button>
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
