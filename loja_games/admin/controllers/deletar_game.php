<?php
require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../config/funcoes.php';
exigirAdmin();
validarCsrf($_GET['token'] ?? null);
if (isset($_GET['id'])) {
    // Atribuição separada evita avisos dos outros campos no construtor do roteiro.
    $game = new Game();
    $game->id = idValido($_GET['id']);
    try {
        if ($game->deletarGame()) {
            header('Location: ../views/ver_games.php?deletado=1');
            exit;
        }
        header('Location: ../views/ver_games.php?erro=vinculo');
        exit;
    } catch (PDOException $e) {
        if ((string) $e->getCode() === '23000') {
            header('Location: ../views/ver_games.php?erro=vinculo');
            exit;
        }
        throw $e;
    }
} else {
    echo 'ID do jogo não fornecido.';
}
