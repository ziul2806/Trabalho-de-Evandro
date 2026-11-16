<?php
require_once __DIR__ . '/../config/conexao.php';

// Pedido de venda. O preço vem do jogo no banco, e o estoque muda na mesma transação.
class Pedido {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function listarPedidos() {
        $sql = "SELECT p.id, p.codigo, p.cliente_id, p.usuario_id, p.status, p.total, p.observacao, p.created_at,
                COALESCE(c.nome, u.nome, 'Conta removida') AS comprador, g.titulo, i.quantidade
            FROM pedidos p
            LEFT JOIN clientes c ON c.id = p.cliente_id
            LEFT JOIN usuarios u ON u.id = p.usuario_id
            LEFT JOIN pedido_itens i ON i.pedido_id = p.id
            LEFT JOIN games g ON g.id = i.game_id
            ORDER BY p.created_at DESC, p.id DESC";
        $pedidos = [];
        foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = $row['id'];
            if (!isset($pedidos[$id])) {
                $row['itens_texto'] = [];
                $pedidos[$id] = $row;
            }
            if (!empty($row['titulo'])) {
                $pedidos[$id]['itens_texto'][] = $row['titulo'] . ' x' . (int) $row['quantidade'];
            }
        }
        return array_values($pedidos);
    }

    public function buscarPedidoPorId($id) {
        $sth = $this->pdo->prepare("SELECT p.*, COALESCE(c.nome, u.nome, 'Conta removida') AS comprador
            FROM pedidos p
            LEFT JOIN clientes c ON c.id = p.cliente_id
            LEFT JOIN usuarios u ON u.id = p.usuario_id
            WHERE p.id = :id");
        $sth->execute(['id' => $id]);
        $pedido = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$pedido) {
            return false;
        }
        $itens = $this->pdo->prepare('SELECT i.game_id, i.quantidade, i.preco, g.titulo FROM pedido_itens i INNER JOIN games g ON g.id = i.game_id WHERE i.pedido_id = :id');
        $itens->execute(['id' => $id]);
        $pedido['itens'] = $itens->fetchAll(PDO::FETCH_ASSOC);
        return $pedido;
    }

    public function relatorio() {
        $faturamento = $this->pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE status IN ('pago', 'entregue')")->fetchColumn();
        $porStatus = $this->pdo->query('SELECT status, COUNT(*) AS quantidade, COALESCE(SUM(total), 0) AS total FROM pedidos GROUP BY status')->fetchAll(PDO::FETCH_ASSOC);
        $baixo = $this->pdo->query('SELECT id, titulo, estoque FROM games WHERE estoque <= 3 ORDER BY estoque ASC, titulo ASC')->fetchAll(PDO::FETCH_ASSOC);
        return ['faturamento' => $faturamento, 'por_status' => $porStatus, 'estoque_baixo' => $baixo];
    }

    public function cadastrarPedido($dados) {
        $gameId = (int) ($dados['game_id'] ?? 0);
        $quantidade = (int) ($dados['quantidade'] ?? 0);
        $status = $dados['status'] ?? 'pago';
        $clienteId = !empty($dados['cliente_id']) ? (int) $dados['cliente_id'] : null;
        $usuarioId = !empty($dados['usuario_id']) ? (int) $dados['usuario_id'] : null;
        $observacao = $this->texto($dados['observacao'] ?? '');
        if ($gameId < 1 || $quantidade < 1 || $quantidade > 99 || !$this->statusValido($status) || ($clienteId && $usuarioId) || (!$clienteId && !$usuarioId)) {
            return false;
        }
        $this->pdo->beginTransaction();
        try {
            if (!$this->compradorExiste($clienteId, $usuarioId)) {
                $this->pdo->rollBack();
                return false;
            }
            $game = $this->pdo->prepare('SELECT id, preco, estoque FROM games WHERE id = :id FOR UPDATE');
            $game->execute(['id' => $gameId]);
            $atual = $game->fetch(PDO::FETCH_ASSOC);
            $reserva = $status !== 'cancelado';
            if (!$atual || ($reserva && (int) $atual['estoque'] < $quantidade)) {
                $this->pdo->rollBack();
                return false;
            }
            $codigo = 'GS-PED-' . strtoupper(bin2hex(random_bytes(6)));
            $total = number_format((float) $atual['preco'] * $quantidade, 2, '.', '');
            $ins = $this->pdo->prepare('INSERT INTO pedidos (codigo, cliente_id, usuario_id, status, total, observacao) VALUES (:codigo, :cliente, :usuario, :status, :total, :obs)');
            $ins->execute(['codigo' => $codigo, 'cliente' => $clienteId, 'usuario' => $usuarioId, 'status' => $status, 'total' => $total, 'obs' => $observacao]);
            $pedidoId = (int) $this->pdo->lastInsertId();
            $item = $this->pdo->prepare('INSERT INTO pedido_itens (pedido_id, game_id, quantidade, preco) VALUES (:pedido, :game, :qtd, :preco)');
            $item->execute(['pedido' => $pedidoId, 'game' => $gameId, 'qtd' => $quantidade, 'preco' => $atual['preco']]);
            if ($reserva && !$this->moverEstoque($pedidoId, -1)) {
                throw new RuntimeException('Não foi possível baixar o estoque.');
            }
            if ($reserva) {
                $this->registrarCompraDemo($codigo, $clienteId, $usuarioId, $gameId, $atual['preco']);
            }
            $this->pdo->commit();
            return $pedidoId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function editarPedido($id, $status, $observacao) {
        if (!$this->statusValido($status)) {
            return false;
        }
        $this->pdo->beginTransaction();
        try {
            $atual = $this->bloquear($id);
            if (!$atual) {
                $this->pdo->rollBack();
                return false;
            }
            $eraAtivo = $atual['status'] !== 'cancelado';
            $ficaAtivo = $status !== 'cancelado';
            if ($eraAtivo && !$ficaAtivo) {
                $this->moverEstoque($id, 1);
                $this->pdo->prepare('DELETE FROM compras_demo WHERE codigo = :codigo')->execute(['codigo' => $atual['codigo']]);
            } elseif (!$eraAtivo && $ficaAtivo && !$this->moverEstoque($id, -1)) {
                $this->pdo->rollBack();
                return false;
            } elseif (!$eraAtivo && $ficaAtivo) {
                $this->reporCompras($atual);
            }
            $sth = $this->pdo->prepare('UPDATE pedidos SET status = :status, observacao = :obs WHERE id = :id');
            $sth->execute(['status' => $status, 'obs' => $this->texto($observacao), 'id' => $id]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function deletarPedido($id) {
        $this->pdo->beginTransaction();
        try {
            $atual = $this->bloquear($id);
            if (!$atual) {
                $this->pdo->rollBack();
                return false;
            }
            if ($atual['status'] !== 'cancelado') {
                $this->moverEstoque($id, 1);
                $this->pdo->prepare('DELETE FROM compras_demo WHERE codigo = :codigo')->execute(['codigo' => $atual['codigo']]);
            }
            $this->pdo->prepare('DELETE FROM pedidos WHERE id = :id')->execute(['id' => $id]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function bloquear($id) {
        $sth = $this->pdo->prepare('SELECT * FROM pedidos WHERE id = :id FOR UPDATE');
        $sth->execute(['id' => $id]);
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    private function compradorExiste($clienteId, $usuarioId) {
        if ($clienteId) {
            $sth = $this->pdo->prepare('SELECT id FROM clientes WHERE id = :id');
            $sth->execute(['id' => $clienteId]);
            return (bool) $sth->fetchColumn();
        }
        $sth = $this->pdo->prepare('SELECT id FROM usuarios WHERE id = :id');
        $sth->execute(['id' => $usuarioId]);
        return (bool) $sth->fetchColumn();
    }

    private function moverEstoque($pedidoId, $direcao) {
        $itens = $this->pdo->prepare('SELECT game_id, quantidade FROM pedido_itens WHERE pedido_id = :id');
        $itens->execute(['id' => $pedidoId]);
        foreach ($itens->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $sql = $direcao < 0
                ? 'UPDATE games SET estoque = estoque - :qtd, updated_at = NOW() WHERE id = :id AND estoque >= :qtd'
                : 'UPDATE games SET estoque = estoque + :qtd, updated_at = NOW() WHERE id = :id';
            $sth = $this->pdo->prepare($sql);
            $sth->execute(['qtd' => $item['quantidade'], 'id' => $item['game_id']]);
            if ($direcao < 0 && $sth->rowCount() !== 1) {
                return false;
            }
        }
        return true;
    }

    private function reporCompras($pedido) {
        $itens = $this->pdo->prepare('SELECT game_id, preco FROM pedido_itens WHERE pedido_id = :id');
        $itens->execute(['id' => $pedido['id']]);
        foreach ($itens->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $this->registrarCompraDemo($pedido['codigo'], $pedido['cliente_id'], $pedido['usuario_id'], $item['game_id'], $item['preco']);
        }
    }

    private function registrarCompraDemo($codigo, $clienteId, $usuarioId, $gameId, $preco) {
        $sql = $clienteId
            ? 'INSERT IGNORE INTO compras_demo (cliente_id, game_id, codigo, preco) VALUES (:conta, :game, :codigo, :preco)'
            : 'INSERT IGNORE INTO compras_demo (usuario_id, game_id, codigo, preco) VALUES (:conta, :game, :codigo, :preco)';
        $sth = $this->pdo->prepare($sql);
        $sth->execute(['conta' => $clienteId ?: $usuarioId, 'game' => $gameId, 'codigo' => $codigo, 'preco' => $preco]);
    }

    private function statusValido($status) {
        return in_array($status, ['pendente', 'pago', 'cancelado', 'entregue'], true);
    }

    private function texto($valor) {
        $texto = trim((string) $valor);
        if ($texto === '') {
            return null;
        }
        return mb_substr($texto, 0, 255);
    }
}
