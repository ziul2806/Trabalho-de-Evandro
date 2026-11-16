<?php
require_once __DIR__ . '/admin/config/conexao.php';
require_once __DIR__ . '/admin/views/componentes/apresentacao.php';
$sql = 'SELECT * FROM games';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teste de Conexão</title>
    <link rel="stylesheet" href="admin/public/css/style.css">
</head>
<body>
    <h2>Lista de Jogos</h2>
    <table>
        <tr><th>ID</th><th>Título</th><th>Descrição</th><th>Preço</th><th>Estoque</th><th>Categoria</th><th>Imagem</th></tr>
        <?php foreach ($games as $g): ?>
        <tr>
            <td><?= (int) $g['id'] ?></td>
            <td><?= htmlspecialchars($g['titulo'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($g['descricao'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>R$ <?= number_format($g['preco'], 2, ',', '.') ?></td>
            <td><?= (int) $g['estoque'] ?></td>
            <td><?= htmlspecialchars($g['categoria'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><img class="table-cover" src="<?= htmlspecialchars(caminhoCapa($g['imagem'], 'admin/public/imagens/'), ENT_QUOTES, 'UTF-8') ?>" alt="Capa de <?= htmlspecialchars($g['titulo'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" width="60" height="90"></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
