-- GameStock: categorias, clientes e pedidos.
-- Rode este script no banco loja_games já existente. Pode ser executado de novo.
SET NAMES utf8mb4;
USE loja_games;

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO categorias (nome, descricao) VALUES
('Aventura', 'Histórias, exploração e jornadas.'),
('Ação', 'Combate, reflexos e ritmo intenso.'),
('Sandbox', 'Mundos abertos para construir e experimentar.'),
('RPG', 'Personagens, escolhas e progressão.'),
('Simulação', 'Rotinas, gestão e vida virtual.'),
('Metroidvania', 'Mapas interligados e habilidades novas.'),
('Roguelike', 'Tentativas curtas e recomeços.')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao);

INSERT INTO categorias (nome, descricao)
SELECT g.categoria, CONCAT('Jogos de ', g.categoria)
FROM games g
WHERE g.categoria IS NOT NULL AND g.categoria <> ''
GROUP BY g.categoria
HAVING NOT EXISTS (SELECT 1 FROM categorias c WHERE c.nome = g.categoria);

ALTER TABLE games ADD COLUMN IF NOT EXISTS categoria_id INT NULL AFTER categoria;

SELECT COUNT(*) INTO @fk_game_categoria
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'games' AND CONSTRAINT_NAME = 'game_categoria';
SET @sql_game_categoria = IF(@fk_game_categoria = 0,
    'ALTER TABLE games ADD CONSTRAINT game_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT',
    'SELECT 1');
PREPARE stmt_game_categoria FROM @sql_game_categoria;
EXECUTE stmt_game_categoria;
DEALLOCATE PREPARE stmt_game_categoria;

UPDATE games g
INNER JOIN categorias c ON c.nome = g.categoria
SET g.categoria_id = c.id
WHERE g.categoria_id IS NULL;

CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Contas só para o professor testar. A conta já usada no projeto não é alterada.
INSERT INTO usuarios (nome, email, senha, tipo)
SELECT 'Administrador de teste', 'admin@gamestock.local', '$2y$10$MuP9Omt3moFpnlTCLGOjIOYZGBJGKDiE6XLlG16KRawkhp9F6k7UK', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'admin@gamestock.local');

INSERT INTO clientes (nome, email, telefone, senha)
SELECT 'Cliente de teste', 'cliente@gamestock.local', '11987654321', '$2y$10$3TVfCiBUxxV75wXd7Ff3p.VH2MZdtMGSDfq0UD2GuBaTOaI8GYU5W'
WHERE NOT EXISTS (SELECT 1 FROM clientes WHERE email = 'cliente@gamestock.local');

ALTER TABLE compras_demo MODIFY usuario_id INT NULL;
ALTER TABLE compras_demo ADD COLUMN IF NOT EXISTS cliente_id INT NULL AFTER usuario_id;

SELECT COUNT(*) INTO @fk_compra_cliente
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'compras_demo' AND CONSTRAINT_NAME = 'compra_demo_cliente';
SET @sql_compra_cliente = IF(@fk_compra_cliente = 0,
    'ALTER TABLE compras_demo ADD CONSTRAINT compra_demo_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt_compra_cliente FROM @sql_compra_cliente;
EXECUTE stmt_compra_cliente;
DEALLOCATE PREPARE stmt_compra_cliente;

SELECT COUNT(*) INTO @idx_cliente_game
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'compras_demo' AND INDEX_NAME = 'cliente_game';
SET @sql_cliente_game = IF(@idx_cliente_game = 0,
    'ALTER TABLE compras_demo ADD UNIQUE KEY cliente_game (cliente_id, game_id)',
    'SELECT 1');
PREPARE stmt_cliente_game FROM @sql_cliente_game;
EXECUTE stmt_cliente_game;
DEALLOCATE PREPARE stmt_cliente_game;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(32) NOT NULL UNIQUE,
    cliente_id INT NULL,
    usuario_id INT NULL,
    status ENUM('pendente', 'pago', 'cancelado', 'entregue') NOT NULL DEFAULT 'pago',
    total DECIMAL(10,2) NOT NULL,
    observacao VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT pedido_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    CONSTRAINT pedido_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    game_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    CONSTRAINT item_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    CONSTRAINT item_game FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO pedidos (codigo, usuario_id, cliente_id, status, total, observacao, created_at)
SELECT c.codigo, c.usuario_id, c.cliente_id, 'pago', c.preco, 'Compra já existente no catálogo.', c.created_at
FROM compras_demo c
WHERE NOT EXISTS (SELECT 1 FROM pedidos p WHERE p.codigo = c.codigo);

INSERT INTO pedido_itens (pedido_id, game_id, quantidade, preco)
SELECT p.id, c.game_id, 1, c.preco
FROM compras_demo c
INNER JOIN pedidos p ON p.codigo = c.codigo
WHERE NOT EXISTS (
    SELECT 1 FROM pedido_itens i WHERE i.pedido_id = p.id AND i.game_id = c.game_id
);
