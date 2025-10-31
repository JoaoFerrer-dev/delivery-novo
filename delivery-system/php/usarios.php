<?php
require_once 'config.php';

$pdo = connectDatabase();

// Headers para CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Lidar com preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';

// Log para debug
error_log("=== CADASTRO USUARIO ===");
error_log("Action: " . $action);
error_log("Method: " . $_SERVER['REQUEST_METHOD']);

switch ($action) {
    case 'listar':
        listarUsuarios();
        break;
    case 'cadastrar':
        cadastrarUsuario();
        break;
    default:
        sendResponse(false, null, 'Ação não especificada. Use: listar ou cadastrar');
}

function listarUsuarios() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY nome");
        $usuarios = $stmt->fetchAll();
        
        error_log("Usuários encontrados: " . count($usuarios));
        
        sendResponse(true, $usuarios);
    } catch (PDOException $e) {
        error_log("Erro ao listar: " . $e->getMessage());
        sendResponse(false, null, 'Erro ao listar usuários: ' . $e->getMessage());
    }
}

function cadastrarUsuario() {
    global $pdo;
    
    // Obter dados do POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log dos dados recebidos
    error_log("Dados recebidos: " . print_r($input, true));
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $input = $_POST; // Tenta pegar do formData
        error_log("Tentando FormData: " . print_r($input, true));
    }
    
    // Validar dados
    if (empty($input)) {
        sendResponse(false, null, 'Nenhum dado recebido');
    }
    
    $nome = trim($input['nome'] ?? '');
    $email = trim($input['email'] ?? '');
    $telefone = trim($input['telefone'] ?? '');
    $endereco = trim($input['endereco'] ?? '');
    
    error_log("Campos extraídos - Nome: '$nome', Email: '$email', Telefone: '$telefone'");
    
    // Validações
    if (empty($nome)) {
        sendResponse(false, null, 'O nome é obrigatório');
    }
    
    if (empty($email)) {
        sendResponse(false, null, 'O email é obrigatório');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, null, 'Formato de email inválido');
    }
    
    if (empty($telefone)) {
        sendResponse(false, null, 'O telefone é obrigatório');
    }
    
    if (empty($endereco)) {
        sendResponse(false, null, 'O endereço é obrigatório');
    }
    
    try {
        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            sendResponse(false, null, 'Este email já está cadastrado');
        }
        
        // Inserir novo usuário
        $sql = "INSERT INTO usuarios (nome, email, telefone, endereco) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$nome, $email, $telefone, $endereco]);
        
        if ($result) {
            $userId = $pdo->lastInsertId();
            
            error_log("USUÁRIO CADASTRADO COM SUCESSO! ID: " . $userId);
            
            sendResponse(true, [
                'id' => $userId,
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'endereco' => $endereco
            ], 'Usuário cadastrado com sucesso!');
            
        } else {
            sendResponse(false, null, 'Falha na execução do INSERT');
        }
        
    } catch (PDOException $e) {
        error_log("ERRO PDO: " . $e->getMessage());
        
        // Verificar tipo de erro
        if ($e->getCode() == '23000') {
            sendResponse(false, null, 'Email já cadastrado no sistema');
        } else {
            sendResponse(false, null, 'Erro no banco de dados: ' . $e->getMessage());
        }
    }
}
?>