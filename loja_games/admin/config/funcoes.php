<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
// Revalida a conta para que sessões de usuários excluídos não continuem ativas.
require_once __DIR__ . '/conexao.php';
if (isset($_SESSION['usuario'])) {
    $origem = ($_SESSION['usuario']['conta'] ?? 'usuarios') === 'clientes' ? 'clientes' : 'usuarios';
    $sqlConta = $origem === 'clientes'
        ? 'SELECT id, nome, email, telefone, senha, created_at, updated_at FROM clientes WHERE id = :id'
        : 'SELECT id, nome, email, senha, tipo, created_at, updated_at FROM usuarios WHERE id = :id';
    $consultaConta = $pdo->prepare($sqlConta);
    $consultaConta->execute(['id' => $_SESSION['usuario']['id']]);
    $contaAtual = $consultaConta->fetch(PDO::FETCH_ASSOC);
    $versaoSenha = $contaAtual ? hash('sha256', $contaAtual['senha']) : '';
    if (!$contaAtual || $contaAtual['updated_at'] !== ($_SESSION['usuario']['updated_at'] ?? null)
        || (isset($_SESSION['usuario']['versao_senha']) && !hash_equals($_SESSION['usuario']['versao_senha'], $versaoSenha))) {
        unset($_SESSION['usuario'], $_SESSION['csrf'], $_SESSION['conta_criada'], $_SESSION['senha_alterada']);
    } else {
        unset($contaAtual['senha']);
        $contaAtual['conta'] = $origem;
        $contaAtual['tipo'] = $origem === 'clientes' ? 'cliente' : $contaAtual['tipo'];
        $contaAtual['versao_senha'] = $versaoSenha;
        $_SESSION['usuario'] = $contaAtual;
    }
}
function h($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
function csrf() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function validarCsrf($token) {
    if (!is_string($token) || !hash_equals(csrf(), $token)) {
        http_response_code(403);
        exit('Requisição inválida. Volte à página e tente novamente.');
    }
}
function exigirAdmin() {
    if (empty($_SESSION['usuario'])) {
        header('Location: ../../login.php');
        exit;
    }
    if (!ehAdmin()) {
        http_response_code(403);
        exit('Acesso permitido somente ao administrador. Volte ao catálogo para comprar jogos.');
    }
}
function ehAdmin() {
    return isset($_SESSION['usuario']) && $_SESSION['usuario']['tipo'] === 'admin';
}
function idValido($valor) {
    $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        http_response_code(400);
        exit('ID inválido.');
    }
    return $id;
}
function destinoAposAutenticacao($idJogo) {
    $id = filter_var($idJogo, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id ? 'admin/views/detalhes_game.php?id=' . $id : 'admin/views/ver_games.php';
}
function moeda($valor) {
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}
function formatarTelefone($telefone) {
    $digitos = preg_replace('/\D/', '', (string) $telefone);
    if (strlen($digitos) === 11) {
        return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7));
    }
    if (strlen($digitos) === 10) {
        return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6));
    }
    return (string) $telefone;
}
function rotuloStatus($status) {
    $rotulos = ['pendente' => 'Pendente', 'pago' => 'Pago', 'cancelado' => 'Cancelado', 'entregue' => 'Entregue'];
    return $rotulos[$status] ?? (string) $status;
}
function emailDisponivel($email, $tabela, $excetoId = null) {
    global $pdo;
    if (!in_array($tabela, ['usuarios', 'clientes'], true)) {
        return false;
    }
    foreach (['usuarios', 'clientes'] as $origem) {
        $sql = "SELECT id FROM {$origem} WHERE email = :email";
        $params = ['email' => $email];
        if ($origem === $tabela && $excetoId) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excetoId;
        }
        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        if ($sth->fetch(PDO::FETCH_ASSOC)) {
            return false;
        }
    }
    return true;
}
