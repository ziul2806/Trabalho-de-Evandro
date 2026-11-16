<?php
require_once __DIR__ . '/../models/usuarios.php';
require_once __DIR__ . '/../models/clientes.php';
require_once __DIR__ . '/../models/compras_demo.php';
require_once __DIR__ . '/../config/funcoes.php';
require_once __DIR__ . '/componentes/apresentacao.php';
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit;
}
$conta = $_SESSION['usuario'];
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf($_POST['token'] ?? null);
    $atual = is_string($_POST['senha_atual'] ?? null) ? $_POST['senha_atual'] : '';
    $nova = is_string($_POST['nova_senha'] ?? null) ? $_POST['nova_senha'] : '';
    $confirmacao = is_string($_POST['confirmar_senha'] ?? null) ? $_POST['confirmar_senha'] : '';
    if ($atual === '') {
        $erro = 'Informe sua senha atual.';
    } elseif (strlen($nova) < 6 || strlen($nova) > 72 || str_contains($nova, "\0")) {
        $erro = 'A nova senha deve ter entre 6 e 72 caracteres.';
    } elseif ($nova !== $confirmacao) {
        $erro = 'A nova senha e a confirmação não coincidem.';
    } elseif ($nova === $atual) {
        $erro = 'Escolha uma senha diferente da atual.';
    } else {
        $ehCliente = ($conta['conta'] ?? 'usuarios') === 'clientes';
        $usuario = $ehCliente ? new Cliente() : new Usuario();
        // ID, e-mail e perfil vêm da sessão; o formulário não escolhe outra conta.
        if ($usuario->alterarSenha((int) $conta['id'], $atual, $nova)) {
            $autenticado = $usuario->autenticar($conta['email'], $nova);
            if (!$autenticado) {
                $_SESSION = [];
                session_destroy();
                header('Location: ../../login.php');
                exit;
            }
            session_regenerate_id(true);
            $_SESSION['usuario'] = $autenticado;
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $_SESSION['senha_alterada'] = true;
            header('Location: perfil.php');
            exit;
        }
        $erro = 'Senha atual incorreta. Se ela foi alterada em outra aba, entre novamente.';
    }
}
$sucesso = !empty($_SESSION['senha_alterada']);
unset($_SESSION['senha_alterada']);
$jogos = (new CompraDemo())->listarComprados((int) $conta['id']);
$navAtiva = 'perfil';
?>
<!DOCTYPE html><html lang="pt-br">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Meu perfil — GameStock</title><link rel="stylesheet" href="../public/css/style.css?v=4"><link rel="stylesheet" href="../public/css/experiencia.css?v=6"><link rel="stylesheet" href="../public/css/perfil.css?v=1"></head>
<body><div class="site-shell">
    <?php require __DIR__ . '/componentes/cabecalho.php'; ?>
    <main class="profile-main">
        <a class="back-link" href="ver_games.php#catalogo">← Voltar ao catálogo</a>
        <section class="profile-heading" aria-labelledby="profile-title">
            <div class="profile-identity"><span class="profile-avatar" aria-hidden="true"><?= h(mb_strtoupper(mb_substr($conta['nome'], 0, 1))) ?></span><div><p class="eyebrow">GAMESTOCK / MEU PERFIL</p><h1 id="profile-title"><?= h($conta['nome']) ?></h1><p class="profile-email"><?= h($conta['email']) ?></p><?php if (!empty($conta['telefone'])): ?><p class="profile-email"><?= h(formatarTelefone($conta['telefone'])) ?></p><?php endif; ?><span class="user-role <?= $conta['tipo'] === 'admin' ? 'role-admin' : '' ?>"><?= $conta['tipo'] === 'admin' ? 'Administrador' : ($conta['tipo'] === 'cliente' ? 'Cliente' : 'Jogador') ?></span></div></div>
            <form action="../controllers/logout.php" method="POST" class="profile-logout"><input type="hidden" name="token" value="<?= h(csrf()) ?>"><button class="button button-secondary" type="submit">Sair da conta <?= icone('arrow') ?></button></form>
        </section>
        <?php if ($sucesso): ?><p class="success-message" role="status">Senha alterada com sucesso. Use a nova senha no próximo acesso.</p><?php endif; ?>
        <div class="profile-layout">
            <section class="profile-library" aria-labelledby="library-title">
                <div class="library-heading"><div><p class="eyebrow">SUA COLEÇÃO DE AVENTURAS</p><h2 id="library-title">Meus jogos <span class="count-badge"><?= count($jogos) ?></span></h2></div></div>
                <?php if (!$jogos): ?><div class="profile-empty"><?= icone('gamepad') ?><h3>Sua biblioteca começa aqui.</h3><p>Os jogos que você comprar aparecerão neste espaço.</p><a class="button button-primary" href="ver_games.php#catalogo">Explorar o catálogo <?= icone('arrow') ?></a></div>
                <?php else: ?><div class="library-grid">
                    <?php foreach ($jogos as $j): ?><a class="library-game" href="detalhes_game.php?id=<?= (int) $j['id'] ?>" target="_blank" rel="noopener" aria-label="Ver <?= h($j['titulo']) ?>, comprado (abre em nova aba)"><img src="<?= h(caminhoCapa($j['imagem'])) ?>" alt="Capa de <?= h($j['titulo']) ?>" width="100" height="150" loading="lazy"><div class="library-game-info"><span class="owned-label">✓ Comprado</span><h3><?= h($j['titulo']) ?></h3><p><?= h($j['categoria'] ?: 'Jogo') ?></p><small>Comprado em <?= h(date('d/m/Y', strtotime($j['comprado_em']))) ?> · R$ <?= number_format($j['preco_pago'], 2, ',', '.') ?></small><span class="library-open">Ver jogo <?= icone('arrow') ?></span></div></a><?php endforeach; ?>
                </div><?php endif; ?>
            </section>
            <section class="profile-security" aria-labelledby="security-title"><p class="eyebrow">SUA CONTA</p><h2 id="security-title">Senha e acesso</h2><p>Atualize sua senha quando precisar.</p>
                <details class="password-settings" <?= $erro ? 'open' : '' ?>><summary>Alterar senha <span aria-hidden="true">+</span></summary>
                    <form method="POST" class="profile-password-form">
                        <?php if ($erro): ?><p class="erro" role="alert"><?= h($erro) ?></p><?php endif; ?>
                        <input type="hidden" name="token" value="<?= h(csrf()) ?>">
                        <label for="senha_atual">Senha atual</label><input id="senha_atual" name="senha_atual" type="password" autocomplete="current-password" required>
                        <label for="nova_senha">Nova senha</label><input id="nova_senha" name="nova_senha" type="password" minlength="6" maxlength="72" autocomplete="new-password" required>
                        <label for="confirmar_senha">Confirmar nova senha</label><input id="confirmar_senha" name="confirmar_senha" type="password" minlength="6" maxlength="72" autocomplete="new-password" required>
                        <small>Use entre 6 e 72 caracteres.</small><button class="button button-primary" type="submit">Salvar nova senha</button>
                    </form>
                </details>
            </section>
        </div>
    </main><footer class="site-footer"><span>GAMESTOCK / Sua coleção, suas histórias.</span><a href="ver_games.php">Explorar jogos <?= icone('arrow') ?></a></footer>
</div></body></html>
