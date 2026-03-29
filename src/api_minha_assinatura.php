<?php

session_start();
header('Content-Type: application/json');
require 'db.php';


if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

try {
    $id = $_SESSION['usuario_id'];

    
    $sql = "SELECT 
                u.nome, u.email, u.endereco, u.ponto_referencia,
                a.id as assinatura_id, a.frequencia, a.status, a.data_inicio 
            FROM usuarios u
            LEFT JOIN assinaturas a ON u.id = a.usuario_id
            WHERE u.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $dados = $stmt->fetch();

    if (!$dados) {
        throw new Exception("Usuário não encontrado.");
    }

    
    $sqlPref = "SELECT id, tipo, descricao FROM preferencias WHERE assinatura_id = ?";
    $stmtPref = $pdo->prepare($sqlPref);
    $stmtPref->execute([$dados['assinatura_id']]);
    $prefs = $stmtPref->fetchAll();

    echo json_encode([
        'success' => true, 
        'usuario' => $dados,
        'preferencias' => $prefs
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>