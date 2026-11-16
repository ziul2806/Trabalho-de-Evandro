<?php
require_once __DIR__ . '/../models/usuarios.php';
require_once __DIR__ . '/../config/funcoes.php';
require_once __DIR__ . '/componentes/apresentacao.php';
$usuarios = (new Usuario())->listarUsuarios();
$admin = isset($_SESSION['usuario']) && $_SESSION['usuario']['tipo'] === 'admin';
$navAtiva = 'usuarios';
?>
<!DOCTYPE html><html lang="pt-br">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Usuários — GameStock</title><link rel="stylesheet" href="../public/css/style.css?v=4"><link rel="stylesheet" href="../public/css/experiencia.css?v=4"></head>
<body><div class="site-shell">
    <?php require __DIR__ . '/componentes/cabecalho.php'; ?>
    <main class="users-main">
        <div class="users-heading"><div><p class="eyebrow">GAMESTOCK / COMUNIDADE</p><h1>Quem faz parte<br><span>dessa aventura.</span></h1><p class="users-intro">Conheça os usuários cadastrados no GameStock.</p></div><div class="users-total"><strong><?= count($usuarios) ?></strong><span><?= count($usuarios) === 1 ? 'Usuário cadastrado' : 'Usuários cadastrados' ?></span></div></div>
        <div class="users-toolbar"><h2>Usuários cadastrados <span class="count-badge"><?= count($usuarios) ?></span></h2>
            <?php if ($admin): ?><a class="button button-primary" href="cadastrar_usuario.php"><?= icone('plus') ?> Cadastrar usuário</a>
            <?php elseif (!isset($_SESSION['usuario'])): ?><a class="button button-primary" href="../../cadastro.php">Criar minha conta <?= icone('arrow') ?></a><?php endif; ?>
        </div>
        <?php if (!$usuarios): ?><div class="empty-state"><h2>Nenhum usuário cadastrado.</h2><p>Crie uma conta para começar.</p><a class="button button-primary" href="../../cadastro.php">Criar conta</a></div>
        <?php else: ?><div class="table-wrap"><table class="users-table"><thead><tr><th>Usuário</th><?php if ($admin): ?><th>E-mail</th><?php endif; ?><th>Perfil</th><th>Desde</th><?php if ($admin): ?><th>Ações</th><?php endif; ?></tr></thead><tbody>
            <?php foreach ($usuarios as $u): ?><tr><td><div class="user-cell"><span class="user-avatar"><?= h(mb_strtoupper(mb_substr($u['nome'], 0, 1))) ?></span><div><strong><?= h($u['nome']) ?></strong><?php if (isset($_SESSION['usuario']) && (int) $u['id'] === (int) $_SESSION['usuario']['id']): ?><small>Sua conta</small><?php endif; ?></div></div></td>
                <?php if ($admin): ?><td><?= h($u['email']) ?></td><?php endif; ?><td><span class="user-role <?= $u['tipo'] === 'admin' ? 'role-admin' : '' ?>"><?= $u['tipo'] === 'admin' ? 'Administrador' : 'Jogador' ?></span></td><td class="user-date"><?= h(date('d/m/Y', strtotime($u['created_at']))) ?></td>
                <?php if ($admin): ?><td><div class="table-actions"><a class="icon-button" href="editar_usuario.php?id=<?= (int) $u['id'] ?>" aria-label="Editar <?= h($u['nome']) ?>"><?= icone('edit') ?></a><?php if ((int) $u['id'] !== (int) $_SESSION['usuario']['id']): ?><form action="../controllers/deletar_usuario.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>"><input type="hidden" name="token" value="<?= h(csrf()) ?>"><button class="delete-button" type="submit" aria-label="Excluir <?= h($u['nome']) ?>"><?= icone('trash') ?></button></form><?php endif; ?></div></td><?php endif; ?></tr><?php endforeach; ?>
        </tbody></table></div><?php endif; ?>
    </main><footer class="site-footer"><span>GAMESTOCK / Cada jogador, uma nova história.</span><a href="ver_games.php">Explorar jogos <?= icone('arrow') ?></a></footer>
</div></body></html>
