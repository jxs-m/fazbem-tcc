<?php

session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    
    if ($method === 'GET') {
        
     
        if (isset($_GET['id'])) {
            $pedidoId = $_GET['id'];
            
           
            $sqlItens = "SELECT ip.quantidade, ip.preco_unitario, p.nome, p.unidade 
                         FROM itens_pedido ip
                         JOIN produtos p ON ip.produto_id = p.id
                         WHERE ip.pedido_id = ?";
            $stmt = $pdo->prepare($sqlItens);
            $stmt->execute([$pedidoId]);
            $itens = $stmt->fetchAll();
            
          
            $sqlInfo = "SELECT p.id, p.data_pedido, p.obs_pontual, p.status_pagamento, 
                               u.nome, u.endereco, u.telefone, u.ponto_referencia
                        FROM pedidos p
                        JOIN usuarios u ON p.usuario_id = u.id
                        WHERE p.id = ?";
            $stmtInfo = $pdo->prepare($sqlInfo);
            $stmtInfo->execute([$pedidoId]);
            $info = $stmtInfo->fetch();

            echo json_encode(['success' => true, 'itens' => $itens, 'info' => $info]);
            exit;
        }

       
        $sql = "SELECT p.id, p.data_pedido, p.valor_total, p.status_pagamento, p.status_entrega, u.nome as cliente
                FROM pedidos p
                JOIN usuarios u ON p.usuario_id = u.id
                ORDER BY p.id DESC";
        $stmt = $pdo->query($sql);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $tipo = $data['tipo']; 
        $valor = $data['valor']; 

        $campo = ($tipo === 'pagamento') ? 'status_pagamento' : 'status_entrega';
        $sql = "UPDATE pedidos SET $campo = ? WHERE id = ?";
        
        $pdo->prepare($sql)->execute([$valor, $id]);
        echo json_encode(['success' => true]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>