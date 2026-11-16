-- Complemento solicitado: nove jogos, preservando os três exemplos originais.
-- Preços e estoques são dados fictícios para o trabalho acadêmico.
USE loja_games;
INSERT INTO games (titulo, descricao, preco, estoque, categoria, imagem)
SELECT 'Elden Ring', 'Explore as Terras Intermédias em um RPG de fantasia e mundo aberto.', 229.90, 12, 'RPG', 'elden-ring.jpg' WHERE NOT EXISTS (SELECT 1 FROM games WHERE titulo = 'Elden Ring');
INSERT INTO games (titulo, descricao, preco, estoque, categoria, imagem)
SELECT 'Cyberpunk 2077', 'Descubra Night City em uma aventura futurista cheia de escolhas.', 199.90, 8, 'RPG', 'cyberpunk.jpg' WHERE NOT EXISTS (SELECT 1 FROM games WHERE titulo = 'Cyberpunk 2077');
INSERT INTO games (titulo, descricao, preco, estoque, categoria, imagem)
SELECT 'Red Dead Redemption 2', 'Viva a história de Arthur Morgan em uma jornada pelo Velho Oeste.', 179.90, 6, 'Aventura', 'rdr2.jpg' WHERE NOT EXISTS (SELECT 1 FROM games WHERE titulo = 'Red Dead Redemption 2');
INSERT INTO games (titulo, descricao, preco, estoque, categoria, imagem)
SELECT 'The Witcher 3: Wild Hunt', 'Assuma o papel de Geralt em um mundo de monstros, magia e escolhas.', 89.90, 14, 'RPG', 'witcher3.jpg' WHERE NOT EXISTS (SELECT 1 FROM games WHERE titulo = 'The Witcher 3: Wild Hunt');
INSERT INTO games (titulo, descricao, preco, estoque, categoria, imagem)
SELECT 'Stardew Valley', 'Cultive sua fazenda, conheça os vizinhos e construa uma vida no campo.', 49.90, 25, 'Simulação', 'stardew.jpg' WHERE NOT EXISTS (SELECT 1 FROM games WHERE titulo = 'Stardew Valley');
INSERT INTO games (titulo, descricao, preco, estoque, categoria, imagem)
SELECT 'Hollow Knight', 'Desça ao reino de Hallownest e descubra seus mistérios e desafios.', 46.90, 18, 'Metroidvania', 'hollow-knight.jpg' WHERE NOT EXISTS (SELECT 1 FROM games WHERE titulo = 'Hollow Knight');
INSERT INTO games (titulo, descricao, preco, estoque, categoria, imagem)
SELECT 'Hades', 'Enfrente o submundo em uma aventura de ação inspirada na mitologia grega.', 73.90, 16, 'Roguelike', 'hades.jpg' WHERE NOT EXISTS (SELECT 1 FROM games WHERE titulo = 'Hades');
INSERT INTO games (titulo, descricao, preco, estoque, categoria, imagem)
SELECT 'Baldur''s Gate 3', 'Reúna seu grupo e escreva sua história em um RPG de fantasia.', 249.90, 9, 'RPG', 'baldurs-gate3.jpg' WHERE NOT EXISTS (SELECT 1 FROM games WHERE titulo = 'Baldur''s Gate 3');
INSERT INTO games (titulo, descricao, preco, estoque, categoria, imagem)
SELECT 'Sekiro: Shadows Die Twice', 'Domine a espada e encare duelos intensos no Japão da era Sengoku.', 159.90, 5, 'Ação', 'sekiro.jpg' WHERE NOT EXISTS (SELECT 1 FROM games WHERE titulo = 'Sekiro: Shadows Die Twice');
