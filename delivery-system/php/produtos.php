<?php
require_once 'config.php';

$pdo = connectDatabase();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listar':
        listarProdutos();
        break;
    case 'cadastrar':
        cadastrarProduto();
        break;
    default:
        sendResponse(false, null, 'Ação não especificada');
}

function listarProdutos() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM produtos ORDER BY categoria, nome");
        $produtos = $stmt->fetchAll();
        sendResponse(true, $produtos);
    } catch (PDOException $e) {
        sendResponse(false, null, 'Erro ao listar produtos: ' . $e->getMessage());
    }
}

function cadastrarProduto() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $nome = $input['nome'] ?? '';
    $descricao = $input['descricao'] ?? '';
    $preco = $input['preco'] ?? 0;
    $categoria = $input['categoria'] ?? '';
    
    if (empty($nome) || empty($descricao) || $preco <= 0 || empty($categoria)) {
        sendResponse(false, null, 'Todos os campos são obrigatórios e o preço deve ser maior que zero');
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, preco, categoria) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $descricao, $preco, $categoria]);
        
        sendResponse(true, ['id' => $pdo->lastInsertId()], 'Produto cadastrado com sucesso!');
        
    } catch (PDOException $e) {
        sendResponse(false, null, 'Erro ao cadastrar produto: ' . $e->getMessage());
    }
}
?>