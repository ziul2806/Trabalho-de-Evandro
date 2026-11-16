<?php
require_once __DIR__ . '/../models/usuarios.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
validarCsrf($_POST['token'] ?? null);
$id = idValido($_POST['id'] ?? null);
if ($id === (int) $_SESSION['usuario']['id']) { http_response_code(400); exit('Você não pode excluir seu próprio usuário.'); }
$u = new Usuario();
if (!$u->buscarUsuarioPorId($id)) { http_response_code(404); exit('Usuário não encontrado.'); }
$u->id = $id;
$u->deletarUsuario();
header('Location: ../views/ver_usuarios.php');
exit;
