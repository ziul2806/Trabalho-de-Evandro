<?php
require_once __DIR__ . '/../models/clientes.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
require_once __DIR__ . '/componentes/apresentacao.php';
$clientes = (new Cliente())->listarClientes();
$navAtiva = 'clientes';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clientes — GameStock</title>
    <link rel="stylesheet" href="../public/css/style.css?v=4">
    <link rel="stylesheet" href="../public/css/experiencia.css?v=6">
</head>
<body>
<div class="site-shell">
    <?php require __DIR__ . '/componentes/cabecalho.php'; ?>
    <main class="users-main">
        <div class="page-heading">
            <p class="eyebrow">GAMESTOCK / CLIENTES</p>
            <h1>Quem compra<br><span>na loja.</span></h1>
            <p>O cadastro público cria um cliente. Esta tela é o CRUD usado pela administração.</p>
        </div>
        <div class="users-toolbar">
            <h2>Clientes <span class="count-badge"><?= count($clientes) ?></span></h2>
            <a class="button button-primary" href="cadastrar_cliente.php"><?= icone('plus') ?> Novo cliente</a>
        </div>
        <?php if (!$clientes): ?>
            <div class="empty-state"><h3>Nenhum cliente cadastrado.</h3><p>O primeiro pode se registrar pela página pública ou ser incluído aqui.</p></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="users-table">
                    <thead><tr><th>Cliente</th><th>E-mail</th><th>Telefone</th><th>Desde</th><th>Ações</th></tr></thead>
                    <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><strong><?= h($cliente['nome']) ?></strong></td>
                            <td><?= h($cliente['email']) ?></td>
                            <td><?= h(formatarTelefone($cliente['telefone'])) ?></td>
                            <td class="user-date"><?= h(date('d/m/Y', strtotime($cliente['created_at']))) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="icon-button" href="editar_cliente.php?id=<?= (int) $cliente['id'] ?>" aria-label="Editar <?= h($cliente['nome']) ?>"><?= icone('edit') ?></a>
                                    <form action="../controllers/deletar_cliente.php" method="POST" onsubmit="return confirm('Excluir este cliente? Os pedidos permanecem, sem devolver o estoque.');">
                                        <input type="hidden" name="id" value="<?= (int) $cliente['id'] ?>">
                                        <input type="hidden" name="token" value="<?= h(csrf()) ?>">
                                        <button class="delete-button" type="submit" aria-label="Excluir <?= h($cliente['nome']) ?>"><?= icone('trash') ?></button>
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
