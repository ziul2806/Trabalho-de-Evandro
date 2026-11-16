<?php
require_once __DIR__ . '/../config/funcoes.php';
validarCsrf($_POST['token'] ?? null);
$_SESSION = [];
session_destroy();
header('Location: ../../login.php');
exit;
