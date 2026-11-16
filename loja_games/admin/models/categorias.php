<?php
require_once __DIR__ . '/../config/conexao.php';

// Categorias do catálogo. O nome também fica em games.categoria para a busca e o filtro.
class Categoria {
    public $id;
    public $nome;
    public $descricao;
    private $pdo;

    public function __construct($atrib = []) {
        global $pdo;
        $this->pdo = $pdo;
        $this->id = $atrib['id'] ?? null;
        $this->nome = trim($atrib['nome'] ?? '');
        $this->descricao = trim($atrib['descricao'] ?? '');
    }

    public function listarCategorias() {
        $sql = 'SELECT c.id, c.nome, c.descricao, c.created_at, c.updated_at,
                (SELECT COUNT(*) FROM games g WHERE g.categoria_id = c.id) AS jogos
            FROM categorias c ORDER BY c.nome ASC';
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarCategoriaPorId($id) {
        $sth = $this->pdo->prepare('SELECT id, nome, descricao, created_at, updated_at FROM categorias WHERE id = :id');
        $sth->execute(['id' => $id]);
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    public function nomePorId($id) {
        $categoria = $this->buscarCategoriaPorId($id);
        return $categoria['nome'] ?? null;
    }

    public function cadastrarCategoria() {
        $sth = $this->pdo->prepare('INSERT INTO categorias (nome, descricao) VALUES (:nome, :descricao)');
        return $sth->execute(['nome' => $this->nome, 'descricao' => $this->descricao !== '' ? $this->descricao : null]);
    }

    public function editarCategoria() {
        $sth = $this->pdo->prepare('UPDATE categorias SET nome = :nome, descricao = :descricao WHERE id = :id');
        $ok = $sth->execute(['id' => $this->id, 'nome' => $this->nome, 'descricao' => $this->descricao !== '' ? $this->descricao : null]);
        // O catálogo filtra pelo texto salvo no jogo. A troca de nome acompanha os jogos ligados.
        $this->pdo->prepare('UPDATE games SET categoria = :nome WHERE categoria_id = :id')->execute(['nome' => $this->nome, 'id' => $this->id]);
        return $ok;
    }

    public function deletarCategoria() {
        return $this->pdo->prepare('DELETE FROM categorias WHERE id = :id')->execute(['id' => $this->id]);
    }
}
