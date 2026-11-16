<?php
require_once __DIR__ . '/../config/conexao.php';

// Cliente da loja. A senha nova entra apenas por password_hash().
class Cliente {
    public $id;
    public $nome;
    public $email;
    public $telefone;
    public $senha;
    private $pdo;

    public function __construct($atrib = []) {
        global $pdo;
        $this->pdo = $pdo;
        $this->id = $atrib['id'] ?? null;
        $this->nome = trim($atrib['nome'] ?? '');
        $this->email = trim($atrib['email'] ?? '');
        $this->telefone = preg_replace('/\D/', '', (string) ($atrib['telefone'] ?? ''));
        $this->senha = $atrib['senha'] ?? '';
    }

    public function listarClientes() {
        return $this->pdo->query('SELECT id, nome, email, telefone, created_at, updated_at FROM clientes ORDER BY nome ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarClientePorId($id) {
        $sth = $this->pdo->prepare('SELECT id, nome, email, telefone, created_at, updated_at FROM clientes WHERE id = :id');
        $sth->execute(['id' => $id]);
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    public function cadastrarCliente() {
        $sth = $this->pdo->prepare('INSERT INTO clientes (nome, email, telefone, senha) VALUES (:nome, :email, :telefone, :senha)');
        return $sth->execute([
            'nome' => $this->nome,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'senha' => password_hash($this->senha, PASSWORD_DEFAULT),
        ]);
    }

    public function editarCliente() {
        $params = ['id' => $this->id, 'nome' => $this->nome, 'email' => $this->email, 'telefone' => $this->telefone];
        $sql = 'UPDATE clientes SET nome = :nome, email = :email, telefone = :telefone, updated_at = NOW()';
        if ($this->senha !== '') {
            $sql .= ', senha = :senha';
            $params['senha'] = password_hash($this->senha, PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE id = :id';
        return $this->pdo->prepare($sql)->execute($params);
    }

    public function deletarCliente() {
        return $this->pdo->prepare('DELETE FROM clientes WHERE id = :id')->execute(['id' => $this->id]);
    }

    public function alterarSenha($id, $senhaAtual, $novaSenha) {
        $sth = $this->pdo->prepare('SELECT senha FROM clientes WHERE id = :id');
        $sth->execute(['id' => $id]);
        $hashAtual = $sth->fetchColumn();
        if (!$hashAtual || !password_verify($senhaAtual, $hashAtual)) {
            return false;
        }
        $sth = $this->pdo->prepare('UPDATE clientes SET senha = :nova, updated_at = NOW() WHERE id = :id AND senha = :atual');
        $sth->execute(['nova' => password_hash($novaSenha, PASSWORD_DEFAULT), 'id' => $id, 'atual' => $hashAtual]);
        return $sth->rowCount() === 1;
    }

    public function autenticar($email, $senha) {
        $sth = $this->pdo->prepare('SELECT * FROM clientes WHERE email = :email');
        $sth->execute(['email' => $email]);
        $cliente = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$cliente || !password_verify($senha, $cliente['senha'])) {
            return false;
        }
        $cliente['versao_senha'] = hash('sha256', $cliente['senha']);
        $cliente['tipo'] = 'cliente';
        $cliente['conta'] = 'clientes';
        unset($cliente['senha']);
        return $cliente;
    }
}
