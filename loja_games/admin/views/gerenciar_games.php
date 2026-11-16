<?php
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
?>
<!DOCTYPE html><html lang="pt-br">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Área Administrativa - GameStock 2025</title><link rel="stylesheet" href="../public/css/formularios.css"></head>
<body><h2>GameStock 2025 — Área Administrativa</h2>
<p>Olá, <?= h($_SESSION['usuario']['nome']) ?>.</p>
<nav><a href="ver_games.php">Gerenciar Jogos</a><a href="ver_usuarios.php">Gerenciar Usuários</a><a href="../../testar_conexao.php">Testar Conexão</a></nav>
<form action="../controllers/logout.php" method="POST"><input type="hidden" name="token" value="<?= h(csrf()) ?>"><button type="submit">Sair</button></form>
</body></html>
