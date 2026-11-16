<?php
require_once __DIR__ . '/../models/pedidos.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
validarCsrf($_POST['token'] ?? null);
$id = idValido($_POST['id'] ?? null);
if (!(new Pedido())->deletarPedido($id)) {
    http_response_code(404);
    exit('Pedido não encontrado.');
}
header('Location: ../views/ver_pedidos.php');
exit;
