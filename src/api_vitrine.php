<?php
// api_vitrine.php
// ACESSO PÚBLICO
header('Content-Type: application/json');
require 'db.php';

try {
    
    $sql = "SELECT id, nome, categoria, preco, unidade, imagem_url 
            FROM produtos 
            WHERE estoque_atual > 0 
            ORDER BY nome ASC";
    
    $stmt = $pdo->query($sql);
    $produtos = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $produtos]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar vitrine.']);
}
?>