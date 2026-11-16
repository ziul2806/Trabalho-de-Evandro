<?php
require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../models/compras_demo.php';
require_once __DIR__ . '/../config/funcoes.php';
require_once __DIR__ . '/componentes/apresentacao.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$dados = $id ? (new Game())->buscarGamePorId($id) : false;
$compras = new CompraDemo();
$usuarioId = isset($_SESSION['usuario']) ? (int) $_SESSION['usuario']['id'] : null;
$pedido = $dados && $usuarioId ? $compras->buscar($usuarioId, $id) : false;
$comprado = (bool) $pedido;
$erro = '';
if (!$dados) { http_response_code(404); }
if ($dados && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['usuario'])) {
        header('Location: ../../login.php?jogo=' . $id);
        exit;
    }
    validarCsrf($_POST['token'] ?? null);
    if ($comprado) {
        header('Location: detalhes_game.php?id=' . $id . '&pedido=' . $pedido['codigo']);
        exit;
    }
    $pedido = $compras->realizar($usuarioId, $dados);
    if ($pedido) {
        header('Location: detalhes_game.php?id=' . $id . '&pedido=' . $pedido['codigo']);
        exit;
    }
    $erro = 'Este jogo está indisponível no momento.';
    $dados = (new Game())->buscarGamePorId($id);
    if (!$dados) { http_response_code(404); }
}
$mostrarPedido = $comprado
    && is_string($_GET['pedido'] ?? null) && hash_equals($pedido['codigo'], $_GET['pedido']);
$contaCriada = !empty($_SESSION['conta_criada']);
unset($_SESSION['conta_criada']);
?>
<!DOCTYPE html><html lang="pt-br">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $dados ? h($dados['titulo']) : 'Jogo não encontrado' ?> — GameStock</title><link rel="stylesheet" href="../public/css/style.css?v=4"><link rel="stylesheet" href="../public/css/experiencia.css?v=5"><?php if ($mostrarPedido): ?><script defer src="../public/js/compra-concluida.js"></script><?php endif; ?></head>
<body><div class="site-shell">
    <?php require __DIR__ . '/componentes/cabecalho.php'; ?>
    <main class="detail-main">
        <a class="back-link" href="ver_games.php#catalogo">← Voltar ao catálogo</a>
        <?php if ($contaCriada): ?><p class="success-message" role="status">Conta criada! Você já está conectado.</p><?php endif; ?>
        <?php if ($mostrarPedido): ?><section class="purchase-receipt" role="status"><span class="receipt-check">✓</span><div><p class="eyebrow">SEU PEDIDO</p><h2>Compra realizada</h2><p><?= h($dados['titulo']) ?> · 1 unidade · R$ <?= number_format($pedido['preco'], 2, ',', '.') ?></p><p class="receipt-code"><?= h($pedido['codigo']) ?> · Nenhum valor foi cobrado.</p><a class="button button-primary receipt-back" href="ver_games.php#catalogo">Voltar ao catálogo <?= icone('arrow') ?></a></div></section><?php endif; ?>
        <?php if (!$dados): ?><section class="empty-state"><h1>Jogo não encontrado.</h1><p>Explore o catálogo para encontrar outra aventura.</p><a class="button button-primary" href="ver_games.php">Explorar jogos</a></section>
        <?php else: ?>
        <section class="detail-layout" aria-labelledby="game-title">
            <div class="detail-art"><img class="detail-image" src="<?= h(caminhoCapa($dados['imagem'])) ?>" alt="Imagem de <?= h($dados['titulo']) ?>" width="600" height="900" fetchpriority="high"><span class="art-caption">CADA JOGO, UM NOVO UNIVERSO.</span></div>
            <div class="detail-content"><p class="eyebrow">GAMESTOCK / <?= h($dados['categoria'] ?: 'JOGOS') ?></p><h1 id="game-title"><?= h($dados['titulo']) ?></h1><span class="detail-category"><?= h($dados['categoria'] ?: 'Sem categoria') ?></span>
                <section class="detail-description"><h2>Sobre o jogo</h2><p><?= nl2br(h($dados['descricao'] ?: 'Explore uma nova aventura com este jogo do catálogo GameStock.')) ?></p></section>
                <div class="detail-meta"><div><span>Categoria</span><strong><?= h($dados['categoria'] ?: 'Sem categoria') ?></strong></div><div><span>Disponibilidade</span><strong><?= (int) $dados['estoque'] > 0 ? (int) $dados['estoque'] . ' em estoque' : 'Indisponível' ?></strong></div></div>
                <section class="purchase-box"><p class="eyebrow"><?= $comprado ? 'NO SEU CATÁLOGO' : 'SUA PRÓXIMA AVENTURA' ?></p><p class="detail-price"><small>R$</small> <?= number_format($comprado ? $pedido['preco'] : $dados['preco'], 2, ',', '.') ?></p>
                    <?php if ($erro): ?><p class="erro" role="alert"><?= h($erro) ?></p><?php endif; ?>
                    <?php if ($comprado): ?><div class="owned-status"><span>✓</span> Comprado</div><p class="purchase-account">Este jogo já faz parte do seu catálogo.</p><a class="button button-secondary owned-back" href="ver_games.php#catalogo">Voltar ao catálogo <?= icone('arrow') ?></a>
                    <?php elseif ((int) $dados['estoque'] < 1): ?><button class="button button-secondary buy-button" disabled>Indisponível</button>
                    <?php elseif (isset($_SESSION['usuario'])): ?><form method="POST"><input type="hidden" name="token" value="<?= h(csrf()) ?>"><button class="button button-primary buy-button" type="submit"><?= icone('gamepad') ?> Comprar</button></form><p class="purchase-account">Conectado como <?= h($_SESSION['usuario']['nome']) ?>.</p>
                    <?php else: ?><a class="button button-primary buy-button" href="../../login.php?jogo=<?= (int) $id ?>"><?= icone('gamepad') ?> Entrar para comprar</a><p class="purchase-account">É necessário estar conectado a uma conta para comprar este jogo.</p><?php endif; ?>
                    <p class="demo-note"><?= $comprado ? 'Este jogo já é seu. Nenhum valor foi cobrado.' : 'Compra de demonstração: nenhum valor será cobrado.' ?></p>
                </section>
            </div>
        </section>
        <?php endif; ?>
    </main>
    <footer class="site-footer"><span>GAMESTOCK / Cada jogo, um novo universo.</span><a href="ver_games.php">Explorar o catálogo <?= icone('arrow') ?></a></footer>
</div></body></html>
