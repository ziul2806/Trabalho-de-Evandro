<?php
require_once __DIR__ . '/../models/clientes.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
validarCsrf($_POST['token'] ?? null);
$cliente = new Cliente();
$cliente->id = idValido($_POST['id'] ?? null);
if (!$cliente->buscarClientePorId($cliente->id)) {
    http_response_code(404);
    exit('Cliente não encontrado.');
}
$cliente->deletarCliente();
header('Location: ../views/ver_clientes.php');
exit;
