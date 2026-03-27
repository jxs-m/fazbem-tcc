<?php

header('Content-Type: application/json');
require 'db.php';

try {
    
    $stmt = $pdo->query("SELECT * FROM produtos WHERE estoque_atual > 0 ORDER BY nome ASC");
    $produtos = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $produtos]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar catálogo.']);
}
?>