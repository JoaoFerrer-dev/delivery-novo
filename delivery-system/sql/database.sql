-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS deliverydb;
USE deliverydb;

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    endereco TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Entregadores
CREATE TABLE IF NOT EXISTS entregadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    veiculo VARCHAR(50),
    disponivel BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(50),
    disponivel BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    entregador_id INT,
    status ENUM('pendente', 'preparando', 'saiu_entrega', 'entregue', 'cancelado') DEFAULT 'pendente',
    total DECIMAL(10,2) NOT NULL,
    endereco_entrega TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (entregador_id) REFERENCES entregadores(id)
) ENGINE=InnoDB;

-- Tabela de Itens do Pedido
CREATE TABLE IF NOT EXISTS pedido_itens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;

-- Inserir dados de exemplo
INSERT IGNORE INTO usuarios (nome, email, telefone, endereco) VALUES
('João Silva', 'joao@email.com', '11999999999', 'Rua A, 123 - Centro, São Paulo - SP'),
('Maria Santos', 'maria@email.com', '11888888888', 'Rua B, 456 - Jardim, São Paulo - SP'),
('Pedro Oliveira', 'pedro@email.com', '11777777777', 'Rua C, 789 - Vila Madalena, São Paulo - SP'),
('Ana Costa', 'ana@email.com', '11666666666', 'Av. Paulista, 1000 - Bela Vista, São Paulo - SP'),
('Carlos Lima', 'carlos@email.com', '11555555555', 'Rua XV de Novembro, 200 - Centro, São Paulo - SP');

INSERT IGNORE INTO entregadores (nome, telefone, veiculo) VALUES
('Carlos Motoboy', '11666666666', 'Moto Honda CG 160'),
('Ana Entregadora', '11555555555', 'Carro Fiat Uno'),
('Roberto Silva', '11444444444', 'Moto Yamaha Factor 150'),
('Juliana Santos', '11333333333', 'Carro Volkswagen Gol'),
('Marcos Oliveira', '11222222222', 'Moto Bros 160');

INSERT IGNORE INTO produtos (nome, descricao, preco, categoria) VALUES
('Pizza Margherita', 'Pizza tradicional com queijo mussarela, tomate fresco e manjericão', 45.90, 'Pizza'),
('Pizza Calabresa', 'Pizza com calabresa fatiada, cebola e azeitonas', 49.90, 'Pizza'),
('Hambúrguer Artesanal', 'Hambúrguer 180g com queijo cheddar, bacon e molho especial', 32.50, 'Lanche'),
('X-Bacon', 'Hambúrguer, queijo, bacon, alface e tomate', 28.90, 'Lanche'),
('Refrigerante', 'Lata 350ml - Coca-Cola, Guaraná ou Fanta', 8.00, 'Bebida'),
('Suco Natural', 'Suco de laranja, limão ou abacaxi 500ml', 12.00, 'Bebida'),
('Batata Frita', 'Porção média de batata frita crocante', 15.00, 'Acompanhamento'),
('Onion Rings', 'Anéis de cebola empanados - porção', 18.00, 'Acompanhamento'),
('Sushi Mix', '10 peças variadas de sushi (salmão, atum e kani)', 58.00, 'Japonesa'),
('Temaki', 'Temaki de salmão com cream cheese - 1 unidade', 22.00, 'Japonesa');

-- Inserir alguns pedidos de exemplo
INSERT IGNORE INTO pedidos (usuario_id, entregador_id, status, total, endereco_entrega) VALUES
(1, 1, 'entregue', 85.90, 'Rua A, 123 - Centro, São Paulo - SP'),
(2, 2, 'saiu_entrega', 64.50, 'Rua B, 456 - Jardim, São Paulo - SP'),
(3, NULL, 'preparando', 45.90, 'Rua C, 789 - Vila Madalena, São Paulo - SP');

INSERT IGNORE INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) VALUES
(1, 1, 1, 45.90), -- Pizza Margherita
(1, 5, 2, 8.00),  -- 2 Refrigerantes
(1, 7, 1, 15.00), -- Batata Frita
(2, 3, 2, 32.50), -- 2 Hambúrgueres Artesanais
(3, 1, 1, 45.90); -- Pizza Margherita

-- Criar índices para melhor performance
CREATE INDEX idx_pedidos_usuario_id ON pedidos(usuario_id);
CREATE INDEX idx_pedidos_status ON pedidos(status);
CREATE INDEX idx_pedidos_entregador_id ON pedidos(entregador_id);
CREATE INDEX idx_pedido_itens_pedido_id ON pedido_itens(pedido_id);
CREATE INDEX idx_pedido_itens_produto_id ON pedido_itens(produto_id);
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_produtos_categoria ON produtos(categoria);

-- View para relatório de pedidos
CREATE OR REPLACE VIEW view_pedidos_detalhados AS
SELECT 
    p.id as pedido_id,
    u.nome as cliente,
    u.telefone as telefone_cliente,
    e.nome as entregador,
    e.telefone as telefone_entregador,
    p.total,
    p.status,
    p.endereco_entrega,
    p.created_at as data_pedido,
    COUNT(pi.id) as total_itens,
    GROUP_CONCAT(CONCAT(pr.nome, ' (', pi.quantidade, 'x)') SEPARATOR ', ') as itens
FROM pedidos p
JOIN usuarios u ON p.usuario_id = u.id
LEFT JOIN entregadores e ON p.entregador_id = e.id
LEFT JOIN pedido_itens pi ON p.id = pi.pedido_id
LEFT JOIN produtos pr ON pi.produto_id = pr.id
GROUP BY p.id;

-- Procedure para simular pedidos
DELIMITER //
CREATE PROCEDURE SimularPedidos(IN num_pedidos INT)
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE usuario_id INT;
    DECLARE produto_id INT;
    DECLARE pedido_id INT;
    DECLARE total_pedido DECIMAL(10,2);
    DECLARE quantidade_item INT;
    DECLARE preco_produto DECIMAL(10,2);
    
    WHILE i <= num_pedidos DO
        -- Selecionar usuário aleatório
        SELECT id INTO usuario_id FROM usuarios ORDER BY RAND() LIMIT 1;
        
        -- Inserir pedido
        INSERT INTO pedidos (usuario_id, total, endereco_entrega)
        SELECT 
            usuario_id,
            0 as total,
            endereco
        FROM usuarios WHERE id = usuario_id;
        
        SET pedido_id = LAST_INSERT_ID();
        SET total_pedido = 0;
        
        -- Adicionar 1 a 3 produtos aleatórios
        SET @num_itens = FLOOR(1 + RAND() * 3);
        SET @j = 1;
        
        WHILE @j <= @num_itens DO
            -- Selecionar produto aleatório
            SELECT id, preco INTO produto_id, preco_produto 
            FROM produtos 
            WHERE disponivel = TRUE 
            ORDER BY RAND() 
            LIMIT 1;
            
            SET quantidade_item = FLOOR(1 + RAND() * 3);
            
            -- Inserir item do pedido
            INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario)
            VALUES (pedido_id, produto_id, quantidade_item, preco_produto);
            
            SET total_pedido = total_pedido + (preco_produto * quantidade_item);
            SET @j = @j + 1;
        END WHILE;
        
        -- Atualizar total do pedido
        UPDATE pedidos SET total = total_pedido WHERE id = pedido_id;
        
        SET i = i + 1;
    END WHILE;
END//
DELIMITER ;

-- Procedure para estatísticas
DELIMITER //
CREATE PROCEDURE GetEstatisticas()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM usuarios) as total_usuarios,
        (SELECT COUNT(*) FROM pedidos) as total_pedidos,
        (SELECT COUNT(*) FROM produtos) as total_produtos,
        (SELECT COUNT(*) FROM entregadores) as total_entregadores,
        (SELECT COALESCE(SUM(total), 0) FROM pedidos) as faturamento_total,
        (SELECT COUNT(*) FROM pedidos WHERE status = 'pendente') as pedidos_pendentes,
        (SELECT COUNT(*) FROM pedidos WHERE status = 'entregue') as pedidos_entregues;
END//
DELIMITER ;

-- Consultas úteis para teste
SELECT '=== ESTATÍSTICAS DO SISTEMA ===' as info;
CALL GetEstatisticas();

SELECT '=== PEDIDOS DETALHADOS ===' as info;
SELECT * FROM view_pedidos_detalhados ORDER BY data_pedido DESC LIMIT 5;

SELECT '=== PRODUTOS MAIS VENDIDOS ===' as info;
SELECT 
    p.nome,
    p.categoria,
    SUM(pi.quantidade) as total_vendido,
    SUM(pi.quantidade * pi.preco_unitario) as faturamento
FROM produtos p
JOIN pedido_itens pi ON p.id = pi.produto_id
GROUP BY p.id
ORDER BY total_vendido DESC;