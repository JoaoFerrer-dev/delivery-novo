<?php
require_once 'config.php';

$pdo = connectDatabase();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listar':
        listarEntregadores();
        break;
    default:
        sendResponse(false, null, 'Ação não especificada');
}

function listarEntregadores() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM entregadores ORDER BY nome");
        $entregadores = $stmt->fetchAll();
        sendResponse(true, $entregadores);
    } catch (PDOException $e) {
        sendResponse(false, null, 'Erro ao listar entregadores: ' . $e->getMessage());
    }
}
?>