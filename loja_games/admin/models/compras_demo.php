<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/funcoes.php';

// Complemento do CRUD do roteiro: compra de demonstração com baixa de estoque.
class CompraDemo {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    private function colunaConta() {
        return (($_SESSION['usuario']['conta'] ?? 'usuarios') === 'clientes') ? 'cliente_id' : 'usuario_id';
    }

    public function buscar($contaId, $gameId) {
        $coluna = $this->colunaConta();
        $sth = $this->pdo->prepare("SELECT * FROM compras_demo WHERE {$coluna} = :conta AND game_id = :game");
        $sth->bindValue(':conta', $contaId, PDO::PARAM_INT);
        $sth->bindValue(':game', $gameId, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    public function jogosComprados($contaId) {
        $coluna = $this->colunaConta();
        $sth = $this->pdo->prepare("SELECT game_id FROM compras_demo WHERE {$coluna} = :conta");
        $sth->bindValue(':conta', $contaId, PDO::PARAM_INT);
        $sth->execute();
        return array_fill_keys($sth->fetchAll(PDO::FETCH_COLUMN), true);
    }

    public function listarComprados($contaId) {
        $coluna = $this->colunaConta();
        $sth = $this->pdo->prepare("SELECT g.id, g.titulo, g.categoria, g.imagem, c.preco AS preco_pago, c.created_at AS comprado_em FROM compras_demo c INNER JOIN games g ON g.id = c.game_id WHERE c.{$coluna} = :conta ORDER BY c.created_at DESC, c.id DESC");
        $sth->bindValue(':conta', $contaId, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public function realizar($usuarioId, $game) {
        if (empty($_SESSION['usuario']) || (int) $_SESSION['usuario']['id'] !== (int) $usuarioId) {
            throw new LogicException('Entre na sua conta para realizar a compra.');
        }
        $this->pdo->beginTransaction();
        try {
            // Serializa as compras do mesmo jogo e consulta preço/estoque atuais.
            $sth = $this->pdo->prepare('SELECT id, preco, estoque FROM games WHERE id = :id FOR UPDATE');
            $sth->execute(['id' => $game['id']]);
            $atual = $sth->fetch(PDO::FETCH_ASSOC);
            $pedido = $this->buscar($usuarioId, $game['id']);
            if ($pedido) {
                $this->pdo->commit();
                return $pedido;
            }
            if (!$atual || (int) $atual['estoque'] < 1) {
                $this->pdo->rollBack();
                return false;
            }
            $codigo = 'GS-DEMO-' . strtoupper(bin2hex(random_bytes(8)));
            $clienteId = $this->colunaConta() === 'cliente_id' ? $usuarioId : null;
            $donoId = $clienteId ? null : $usuarioId;
            $sqlCompra = $clienteId
                ? 'INSERT INTO compras_demo (cliente_id, game_id, codigo, preco) VALUES (:conta, :game, :codigo, :preco)'
                : 'INSERT INTO compras_demo (usuario_id, game_id, codigo, preco) VALUES (:conta, :game, :codigo, :preco)';
            $sth = $this->pdo->prepare($sqlCompra);
            $sth->execute(['conta' => $usuarioId, 'game' => $atual['id'], 'codigo' => $codigo, 'preco' => $atual['preco']]);
            $sth = $this->pdo->prepare('INSERT INTO pedidos (codigo, cliente_id, usuario_id, status, total, observacao) VALUES (:codigo, :cliente, :usuario, :status, :total, :obs)');
            $sth->execute(['codigo' => $codigo, 'cliente' => $clienteId, 'usuario' => $donoId, 'status' => 'pago', 'total' => $atual['preco'], 'obs' => 'Compra feita no catálogo.']);
            $pedidoId = (int) $this->pdo->lastInsertId();
            $sth = $this->pdo->prepare('INSERT INTO pedido_itens (pedido_id, game_id, quantidade, preco) VALUES (:pedido, :game, 1, :preco)');
            $sth->execute(['pedido' => $pedidoId, 'game' => $atual['id'], 'preco' => $atual['preco']]);
            $sth = $this->pdo->prepare('UPDATE games SET estoque = estoque - 1, updated_at = NOW() WHERE id = :id AND estoque > 0');
            $sth->execute(['id' => $atual['id']]);
            if ($sth->rowCount() !== 1) {
                throw new RuntimeException('Não foi possível atualizar o estoque.');
            }
            $pedido = $this->buscar($usuarioId, $atual['id']);
            $this->pdo->commit();
            return $pedido;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) { $this->pdo->rollBack(); }
            throw $e;
        }
    }
}
