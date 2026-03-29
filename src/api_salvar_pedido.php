<?php

session_start();
header('Content-Type: application/json');
require 'db.php';


if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Você precisa fazer login para finalizar o pedido.']);
    exit;
}


$input = json_decode(file_get_contents('php://input'), true);
$itens = $input['itens'] ?? [];
$total = $input['total'] ?? 0;
$metodo_pagamento = $input['pagamento'] ?? 'PIX';

if (empty($itens)) {
    echo json_encode(['success' => false, 'message' => 'Seu carrinho está vazio.']);
    exit;
}

try {
    
    $pdo->beginTransaction();

    
    $sqlPedido = "INSERT INTO pedidos (usuario_id, data_entrega, valor_total, status_pagamento, status_entrega, obs_pontual) 
                  VALUES (?, DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), ?, 'Pendente', 'Em separação', ?)";
    
   
    $obs = "Pagamento via: " . $metodo_pagamento;
    
    $stmt = $pdo->prepare($sqlPedido);
    $stmt->execute([$_SESSION['usuario_id'], $total, $obs]);
    $pedido_id = $pdo->lastInsertId();

    
    foreach ($itens as $item) {
       
        $stmtCheck = $pdo->prepare("SELECT estoque_atual FROM produtos WHERE id = ?");
        $stmtCheck->execute([$item['id']]);
        $prodBanco = $stmtCheck->fetch();

        if ($prodBanco['estoque_atual'] < $item['quantidade']) {
            throw new Exception("O produto '{$item['nome']}' acabou de esgotar ou não tem quantidade suficiente.");
        }

        
        $sqlItem = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) 
                    VALUES (?, ?, ?, ?)";
        $stmtItem = $pdo->prepare($sqlItem);
        $stmtItem->execute([$pedido_id, $item['id'], $item['quantidade'], $item['preco']]);

        
        $sqlEstoque = "UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id = ?";
        $stmtEstoque = $pdo->prepare($sqlEstoque);
        $stmtEstoque->execute([$item['quantidade'], $item['id']]);
    }

    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Pedido realizado com sucesso!']);

} catch (Exception $e) {
    
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erro ao processar: ' . $e->getMessage()]);
}
?>