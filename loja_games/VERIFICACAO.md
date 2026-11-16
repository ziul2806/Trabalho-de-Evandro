# Verificação de permissões e estoque

Verificado em 08/10/2026 no XAMPP instalado em C:\xampp, Apache na porta 80 e MariaDB na porta 3306.

- Administrador atualizado para Luiz Fernando Mendes Nobre, luizmendes.28mn@gmail.com, com senha armazenada por password_hash. A conta existente do aluno e sua compra anterior foram preservadas. As outras duas contas foram excluídas.
- Sintaxe validada nos 25 arquivos PHP e no JavaScript do catálogo.
- 40 verificações de integração concluídas: login do administrador, rejeição das credenciais antigas/incorretas, cadastro público sempre como jogador, listagem de usuários, ausência de controles administrativos para jogadores, bloqueio de GET e POST nos endereços de cadastro/edição/exclusão, token de sessão, CRUD autorizado com upload de imagem, compra com preço do banco, baixa de uma unidade, marcação Comprado na grade/lista, comprovante e retorno ao catálogo, persistência após novo login, reenvio sem outra baixa, sessão de usuário excluído revogada e consistência dos dados.
- Duas sessões simultâneas da mesma conta produziram um pedido e uma baixa. Duas contas disputando a última unidade produziram somente uma compra, com estoque final zero. Uma falha de integridade desfez a transação sem reduzir estoque.
- Classes Game e Usuario, conexão PDO e SQL original do professor comparados com a cópia anterior: permanecem iguais. Os formulários/controllers de jogos receberam somente as verificações administrativas e o token. A atualização de estoque está no complemento CompraDemo e usa uma transação InnoDB.
- Conferência visual no navegador: jogador sem Novo jogo, Editar ou Excluir; detalhes em outra aba; compra do jogo temporário com estoque passando de 2 para 1; Compra realizada; Voltar ao catálogo; marcação Comprado; botão Comprar ausente; atualização automática da aba do catálogo.
- Contas e jogos temporários removidos após os testes. Jogos e estoque reais não receberam compras de verificação. Estado final: 1 administrador, 12 jogos e 1 compra anterior preservada.
- Instalação completa atualizada para criar somente o administrador solicitado. Senha dessa conta aparece somente como hash no SQL de instalação.

Capturas desta atualização: admin-usuarios.png e compra-estoque.png. A captura da compra usa o jogo e a conta temporários removidos depois da inspeção. Capturas das etapas anteriores continuam disponíveis para referência.

## Atualização: Meu perfil

- Sintaxe validada nos 26 arquivos PHP.
- 29 verificações concluídas para o novo perfil: autenticação obrigatória, biblioteca vazia, biblioteca por conta, capa/data/valor pago, preço histórico, proteção contra seleção de outra conta, retorno ao jogo, token inválido, senha atual obrigatória/correta, limites da nova senha, confirmação, senha diferente, gravação em hash, token renovado, ausência de senhas no HTML, revogação de outras sessões mesmo com timestamp igual, acesso com nova senha, preservação da biblioteca e saída.
- Os testes usaram duas contas e dois jogos temporários. Todos foram removidos. O banco foi comparado antes e depois e permaneceu igual, incluindo o administrador, sua senha, compras e estoque reais.
- Inspeção visual: o nome do cabeçalho abre Meu perfil; a biblioteca do administrador mostra Elden Ring comprado; Alterar senha expande os três campos e Salvar nova senha; Sair da conta fica visível. Nenhuma senha real foi alterada durante a inspeção.
- O CRUD Game e o esquema do banco permanecem iguais. Usuario recebeu alterarSenha e a versão da senha retornada na autenticação; CompraDemo recebeu listarComprados; a revalidação da sessão agora também detecta troca de senha.
- Captura: perfil.png. Arquivos do XAMPP, pasta do projeto e pacote ZIP foram sincronizados.

## Atualização: Login obrigatório para comprar

- 16 verificações concluídas: visitante sem formulário de compra, Entrar para comprar na grade/lista/detalhes, retorno ao jogo após autenticação, POST anônimo com token válido e ID forjado, POST anônimo sem token, bloqueio no model sem sessão ou para ID de outra conta, compra autenticada com token e baixa de uma unidade, sessão encerrada, confirmação por URL forjada, sessão revogada após troca de senha e conta excluída.
- Os testes usaram contas e jogo temporários. A comparação do banco antes e depois confirmou preservação de contas, compras e estoque reais; os registros temporários foram removidos.
- Sintaxe conferida nos três arquivos PHP alterados. No navegador, o visitante viu Entrar para comprar e foi encaminhado a login.php?jogo=5. Nenhum pedido foi criado durante a inspeção. A sessão do administrador foi restaurada após a conferência. Não houve erros de console.
- Captura: login-obrigatorio-compra.png. Arquivos instalados no XAMPP, fonte do projeto e ZIP sincronizados.
