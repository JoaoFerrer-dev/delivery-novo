<?php
require_once 'config.php';

$pdo = connectDatabase();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listar':
        listarPedidos();
        break;
    case 'simular':
        simularPedidos();
        break;
    case 'estatisticas':
        getEstatisticas();
        break;
    default:
        sendResponse(false, null, 'Ação não especificada');
}

function listarPedidos() {
    global $pdo;
    
    $query = "
        SELECT 
            p.id as pedido_id,
            u.nome as cliente,
            p.total,
            p.status,
            p.created_at,
            COUNT(pi.id) as total_itens
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN pedido_itens pi ON p.id = pi.pedido_id
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT 50
    ";
    
    try {
        $stmt = $pdo->query($query);
        $pedidos = $stmt->fetchAll();
        sendResponse(true, $pedidos);
    } catch (PDOException $e) {
        sendResponse(false, null, 'Erro ao listar pedidos: ' . $e->getMessage());
    }
}

function simularPedidos() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $numeroPedidos = $input['numero_pedidos'] ?? 5;
    
    try {
        $pdo->beginTransaction();
        
        $pedidosCriados = 0;
        
        for ($i = 0; $i < $numeroPedidos; $i++) {
            // Selecionar usuário aleatório
            $usuario = $pdo->query("SELECT id, endereco FROM usuarios ORDER BY RAND() LIMIT 1")->fetch();
            
            if (!$usuario) {
                throw new Exception("Nenhum usuário encontrado");
            }
            
            // Selecionar produtos aleatórios
            $produtos = $pdo->query("SELECT id, preco FROM produtos WHERE disponivel = TRUE ORDER BY RAND() LIMIT " . rand(1, 3))->fetchAll();
            
            if (empty($produtos)) {
                throw new Exception("Nenhum produto disponível");
            }
            
            // Calcular total
            $total = 0;
            foreach ($produtos as $produto) {
                $quantidade = rand(1, 3);
                $total += $produto['preco'] * $quantidade;
            }
            
            // Criar pedido
            $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, endereco_entrega, total) VALUES (?, ?, ?)");
            $stmt->execute([$usuario['id'], $usuario['endereco'], $total]);
            $pedidoId = $pdo->lastInsertId();
            
            // Adicionar itens do pedido
            foreach ($produtos as $produto) {
                $quantidade = rand(1, 3);
                $stmt = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
                $stmt->execute([$pedidoId, $produto['id'], $quantidade, $produto['preco']]);
            }
            
            $pedidosCriados++;
        }
        
        $pdo->commit();
        
        sendResponse(true, [
            'total_pedidos' => $pedidosCriados,
            'mensagem' => "$pedidosCriados pedidos simulados com sucesso!"
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        sendResponse(false, null, 'Erro ao simular pedidos: ' . $e->getMessage());
    }
}

function getEstatisticas() {
    global $pdo;
    
    try {
        $estatisticas = [
            'total_usuarios' => $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn(),
            'total_pedidos' => $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn(),
            'total_produtos' => $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn(),
            'total_entregadores' => $pdo->query("SELECT COUNT(*) FROM entregadores")->fetchColumn(),
            'faturamento_total' => $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos")->fetchColumn(),
            'pedidos_pendentes' => $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status = 'pendente'")->fetchColumn(),
            'pedidos_entregues' => $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status = 'entregue'")->fetchColumn()
        ];
        
        sendResponse(true, $estatisticas);
        
    } catch (PDOException $e) {
        sendResponse(false, null, 'Erro ao obter estatísticas: ' . $e->getMessage());
    }
}
?>