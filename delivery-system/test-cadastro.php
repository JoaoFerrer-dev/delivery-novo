<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configurações do banco
$host = 'localhost';
$dbname = 'deliverydb';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro conexão: ' . $e->getMessage()]);
    exit;
}

// Dados de teste fixos - vamos ver se consegue inserir
$dadosTeste = [
    'nome' => 'Usuario Teste Fixo',
    'email' => 'teste.fixo@email.com',
    'telefone' => '11999999999',
    'endereco' => 'Rua Teste, 123'
];

try {
    // Verificar se a tabela existe
    $stmt = $pdo->query("SELECT 1 FROM usuarios LIMIT 1");
    
    // Inserir usuário de teste
    $sql = "INSERT INTO usuarios (nome, email, telefone, endereco) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $dadosTeste['nome'],
        $dadosTeste['email'], 
        $dadosTeste['telefone'],
        $dadosTeste['endereco']
    ]);
    
    $id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuário teste inserido com sucesso!',
        'id' => $id
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao inserir: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}
?>