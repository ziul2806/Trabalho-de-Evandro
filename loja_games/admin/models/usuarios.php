<?php
require_once __DIR__ . '/../config/conexao.php';

// Classe citada na estrutura MVC. Novas senhas usam password_hash().
class Usuario {
    public $id;
    public $nome;
    public $email;
    public $senha;
    public $tipo;
    private $pdo;

    public function __construct($atrib = []) {
        global $pdo;
        $this->pdo = $pdo;
        $this->id = $atrib['id'] ?? null;
        $this->nome = $atrib['nome'] ?? '';
        $this->email = $atrib['email'] ?? '';
        $this->senha = $atrib['senha'] ?? '';
        $this->tipo = $atrib['tipo'] ?? 'user';
    }
    public function listarUsuarios() {
        return $this->pdo->query('SELECT id, nome, email, tipo, created_at, updated_at FROM usuarios ORDER BY nome ASC')->fetchAll(PDO::FETCH_ASSOC);
    }
    public function buscarUsuarioPorId($id) {
        $sth = $this->pdo->prepare('SELECT id, nome, email, tipo, created_at, updated_at FROM usuarios WHERE id = :id');
        $sth->execute(['id' => $id]);
        return $sth->fetch(PDO::FETCH_ASSOC);
    }
    public function cadastrarUsuario() {
        $sth = $this->pdo->prepare('INSERT INTO usuarios (nome, email, senha, tipo, created_at, updated_at) VALUES (:nome, :email, :senha, :tipo, NOW(), NOW())');
        return $sth->execute(['nome' => $this->nome, 'email' => $this->email, 'senha' => password_hash($this->senha, PASSWORD_DEFAULT), 'tipo' => $this->tipo]);
    }
    public function editarUsuario() {
        $params = ['id' => $this->id, 'nome' => $this->nome, 'email' => $this->email, 'tipo' => $this->tipo];
        $sql = 'UPDATE usuarios SET nome = :nome, email = :email, tipo = :tipo, updated_at = NOW()';
        if ($this->senha !== '') {
            $sql .= ', senha = :senha';
            $params['senha'] = password_hash($this->senha, PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE id = :id';
        return $this->pdo->prepare($sql)->execute($params);
    }
    public function deletarUsuario() {
        return $this->pdo->prepare('DELETE FROM usuarios WHERE id = :id')->execute(['id' => $this->id]);
    }
    public function alterarSenha($id, $senhaAtual, $novaSenha) {
        $sth = $this->pdo->prepare('SELECT senha FROM usuarios WHERE id = :id');
        $sth->execute(['id' => $id]);
        $hashAtual = $sth->fetchColumn();
        if (!$hashAtual) { return false; }
        $valida = password_verify($senhaAtual, $hashAtual)
            || (preg_match('/^[a-f0-9]{64}$/D', $hashAtual) && hash_equals($hashAtual, hash('sha256', $senhaAtual)));
        if (!$valida) { return false; }
        // A comparação evita sobrescrever uma troca de senha simultânea.
        $sth = $this->pdo->prepare('UPDATE usuarios SET senha = :nova, updated_at = NOW() WHERE id = :id AND senha = :atual');
        $sth->execute(['nova' => password_hash($novaSenha, PASSWORD_DEFAULT), 'id' => $id, 'atual' => $hashAtual]);
        return $sth->rowCount() === 1;
    }
    public function autenticar($email, $senha) {
        $sth = $this->pdo->prepare('SELECT * FROM usuarios WHERE email = :email');
        $sth->execute(['email' => $email]);
        $u = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$u) { return false; }
        // Compatibilidade com SHA2(..., 256) nos dados de teste do capítulo 1.
        $valida = password_verify($senha, $u['senha']);
        if (!$valida && preg_match('/^[a-f0-9]{64}$/D', $u['senha'])) {
            $valida = hash_equals($u['senha'], hash('sha256', $senha));
        }
        if (!$valida) { return false; }
        $u['versao_senha'] = hash('sha256', $u['senha']);
        $u['conta'] = 'usuarios';
        unset($u['senha']);
        return $u;
    }
}
