<?php
require_once 'config.php';

class Database {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            sendResponse(false, null, "Erro de conexão: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function createTables() {
        $queries = [
            "CREATE TABLE IF NOT EXISTS usuarios (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(150) UNIQUE NOT NULL,
                telefone VARCHAR(20),
                endereco TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB",
            
            "CREATE TABLE IF NOT EXISTS entregadores (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nome VARCHAR(100) NOT NULL,
                telefone VARCHAR(20),
                veiculo VARCHAR(50),
                disponivel BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB",
            
            "CREATE TABLE IF NOT EXISTS produtos (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nome VARCHAR(100) NOT NULL,
                descricao TEXT,
                preco DECIMAL(10,2) NOT NULL,
                categoria VARCHAR(50),
                disponivel BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB",
            
            "CREATE TABLE IF NOT EXISTS pedidos (
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
            ) ENGINE=InnoDB",
            
            "CREATE TABLE IF NOT EXISTS pedido_itens (
                id INT PRIMARY KEY AUTO_INCREMENT,
                pedido_id INT NOT NULL,
                produto_id INT NOT NULL,
                quantidade INT NOT NULL,
                preco_unitario DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
                FOREIGN KEY (produto_id) REFERENCES produtos(id)
            ) ENGINE=InnoDB"
        ];
        
        foreach ($queries as $query) {
            try {
                $this->pdo->exec($query);
            } catch (PDOException $e) {
                // Ignora erro se tabela já existe
            }
        }
    }
    
    public function insertSampleData() {
        // Verificar se já existem dados
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM usuarios");
        if ($stmt->fetchColumn() > 0) {
            return;
        }
        
        // Dados de exemplo
        $usuarios = [
            ['João Silva', 'joao@email.com', '11999999999', 'Rua A, 123 - Centro'],
            ['Maria Santos', 'maria@email.com', '11888888888', 'Rua B, 456 - Jardim'],
            ['Pedro Oliveira', 'pedro@email.com', '11777777777', 'Rua C, 789 - Vila']
        ];
        
        $entregadores = [
            ['Carlos Motoboy', '11666666666', 'Moto'],
            ['Ana Entregadora', '11555555555', 'Carro']
        ];
        
        $produtos = [
            ['Pizza Margherita', 'Pizza tradicional com queijo e tomate', 45.90, 'Pizza'],
            ['Hambúrguer Artesanal', 'Hambúrguer 180g com queijo e bacon', 32.50, 'Lanche'],
            ['Refrigerante', 'Lata 350ml', 8.00, 'Bebida'],
            ['Batata Frita', 'Porção média', 15.00, 'Acompanhamento'],
            ['Sushi Mix', '10 peças variadas', 58.00, 'Japonesa']
        ];
        
        // Inserir dados
        foreach ($usuarios as $usuario) {
            $stmt = $this->pdo->prepare("INSERT IGNORE INTO usuarios (nome, email, telefone, endereco) VALUES (?, ?, ?, ?)");
            $stmt->execute($usuario);
        }
        
        foreach ($entregadores as $entregador) {
            $stmt = $this->pdo->prepare("INSERT IGNORE INTO entregadores (nome, telefone, veiculo) VALUES (?, ?, ?)");
            $stmt->execute($entregador);
        }
        
        foreach ($produtos as $produto) {
            $stmt = $this->pdo->prepare("INSERT IGNORE INTO produtos (nome, descricao, preco, categoria) VALUES (?, ?, ?, ?)");
            $stmt->execute($produto);
        }
    }
}
?>