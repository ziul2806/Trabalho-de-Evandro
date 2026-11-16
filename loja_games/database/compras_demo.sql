-- Complemento: compras de demonstração vinculadas à conta.
USE loja_games;
CREATE TABLE IF NOT EXISTS compras_demo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    game_id INT NOT NULL,
    codigo VARCHAR(32) NOT NULL UNIQUE,
    preco DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY usuario_game (usuario_id, game_id),
    CONSTRAINT compra_demo_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT compra_demo_game FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
