<?php
require_once __DIR__ . '/../models/pedidos.php';
require_once __DIR__ . '/../models/clientes.php';
require_once __DIR__ . '/../models/usuarios.php';
require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
$model = new Pedido();
$id = $edicao ? idValido($_GET['id'] ?? null) : null;
$pedido = $edicao ? $model->buscarPedidoPorId($id) : null;
if ($edicao && !$pedido) {
    http_response_code(404);
    exit('Pedido não encontrado.');
}
$erro = '';
$status = $pedido['status'] ?? 'pago';
$observacao = $pedido['observacao'] ?? '';
$comprador = '';
$gameId = '';
$quantidade = '1';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf($_POST['token'] ?? null);
    $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
    $observacao = is_string($_POST['observacao'] ?? null) ? trim($_POST['observacao']) : '';
    if ($edicao) {
        $ok = $model->editarPedido($id, $status, $observacao);
        if ($ok) {
            header('Location: ver_pedidos.php');
            exit;
        }
        $erro = 'Não foi possível atualizar. Se o pedido estava cancelado, confira se ainda há estoque.';
        $pedido['status'] = $status;
        $pedido['observacao'] = $observacao;
    } else {
        $comprador = is_string($_POST['comprador'] ?? null) ? $_POST['comprador'] : '';
        $gameId = filter_var($_POST['game_id'] ?? null, FILTER_VALIDATE_INT) ?: '';
        $quantidade = filter_var($_POST['quantidade'] ?? null, FILTER_VALIDATE_INT) ?: '';
        $clienteId = null;
        $usuarioId = null;
        if (preg_match('/^cliente-(\d+)$/', $comprador, $m)) {
            $clienteId = (int) $m[1];
        } elseif (preg_match('/^usuario-(\d+)$/', $comprador, $m)) {
            $usuarioId = (int) $m[1];
        }
        $novo = $model->cadastrarPedido([
            'cliente_id' => $clienteId,
            'usuario_id' => $usuarioId,
            'game_id' => $gameId,
            'quantidade' => $quantidade,
            'status' => $status,
            'observacao' => $observacao,
        ]);
        if ($novo) {
            header('Location: ver_pedidos.php');
            exit;
        }
        $erro = 'Não foi possível registrar. Confira comprador, jogo e estoque. O preço usado é o do catálogo.';
    }
}
$clientes = $edicao ? [] : (new Cliente())->listarClientes();
$usuarios = $edicao ? [] : (new Usuario())->listarUsuarios();
$games = $edicao ? [] : (new Game())->listarGames();
$tituloPagina = $edicao ? 'Editar pedido' : 'Novo pedido';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($tituloPagina) ?> — GameStock</title>
    <link rel="stylesheet" href="../public/css/formularios.css?v=4">
</head>
<body>
<div class="form-shell">
    <a class="back-link" href="ver_pedidos.php">← Voltar aos pedidos</a>
    <p class="eyebrow">GAMESTOCK / PEDIDOS</p>
    <h1><?= h($tituloPagina) ?></h1>
    <p class="form-intro"><?= $edicao ? 'Cancelar devolve o estoque. Sair de cancelado baixa o estoque outra vez.' : 'O preço unitário é lido do jogo no momento do registro. A quantidade sai do estoque, exceto no status cancelado.' ?></p>
    <form method="POST" class="formulario game-form">
        <?php if ($erro): ?><p class="erro" role="alert"><?= h($erro) ?></p><?php endif; ?>
        <input type="hidden" name="token" value="<?= h(csrf()) ?>">
        <?php if ($edicao): ?>
            <label>Código</label>
            <input value="<?= h($pedido['codigo']) ?>" readonly>
            <label>Comprador</label>
            <input value="<?= h($pedido['comprador']) ?>" readonly>
            <label>Itens</label>
            <textarea readonly><?php foreach ($pedido['itens'] as $item) { echo h($item['titulo'] . ' x' . $item['quantidade'] . ' — ' . moeda($item['preco'])) . "\n"; } ?></textarea>
        <?php else: ?>
            <label for="comprador">Comprador</label>
            <select id="comprador" name="comprador" required>
                <option value="">Selecione</option>
                <optgroup label="Clientes">
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="cliente-<?= (int) $cliente['id'] ?>" <?= $comprador === 'cliente-' . $cliente['id'] ? 'selected' : '' ?>><?= h($cliente['nome']) ?></option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Usuários">
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="usuario-<?= (int) $usuario['id'] ?>" <?= $comprador === 'usuario-' . $usuario['id'] ? 'selected' : '' ?>><?= h($usuario['nome']) ?></option>
                    <?php endforeach; ?>
                </optgroup>
            </select>
            <label for="game_id">Jogo</label>
            <select id="game_id" name="game_id" required>
                <option value="">Selecione</option>
                <?php foreach ($games as $game): ?>
                    <option value="<?= (int) $game['id'] ?>" <?= (string) $gameId === (string) $game['id'] ? 'selected' : '' ?>><?= h($game['titulo']) ?> — <?= h(moeda($game['preco'])) ?> — estoque <?= (int) $game['estoque'] ?></option>
                <?php endforeach; ?>
            </select>
            <label for="quantidade">Quantidade</label>
            <input id="quantidade" name="quantidade" type="number" min="1" max="99" step="1" value="<?= h($quantidade) ?>" required>
        <?php endif; ?>
        <label for="status">Status</label>
        <select id="status" name="status" required>
            <?php foreach (['pendente' => 'Pendente', 'pago' => 'Pago', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado'] as $valor => $rotulo): ?>
                <option value="<?= h($valor) ?>" <?= $status === $valor ? 'selected' : '' ?>><?= h($rotulo) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="observacao">Observação</label>
        <textarea id="observacao" name="observacao" maxlength="255"><?= h($observacao) ?></textarea>
        <div class="form-actions">
            <button class="button button-primary" type="submit"><?= $edicao ? 'Salvar' : 'Registrar pedido' ?></button>
            <a class="button button-secondary" href="ver_pedidos.php">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
