<?php
require_once __DIR__ . '/../models/categorias.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
validarCsrf($_POST['token'] ?? null);
$categoria = new Categoria();
$categoria->id = idValido($_POST['id'] ?? null);
if (!$categoria->buscarCategoriaPorId($categoria->id)) {
    http_response_code(404);
    exit('Categoria não encontrada.');
}
try {
    $categoria->deletarCategoria();
} catch (PDOException $e) {
    if ((string) $e->getCode() === '23000') {
        header('Location: ../views/ver_categorias.php?erro=uso');
        exit;
    }
    throw $e;
}
header('Location: ../views/ver_categorias.php');
exit;
