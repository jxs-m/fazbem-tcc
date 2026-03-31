<?php
session_start();
ob_clean();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $sql = "SELECT u.id, u.nome, u.email, u.telefone, u.endereco, 
                       a.frequencia, a.status,
                       COALESCE((SELECT SUM(valor_total) FROM pedidos WHERE usuario_id = u.id AND status_pagamento != 'Cancelado'), 0) as total_gasto
                FROM usuarios u
                LEFT JOIN assinaturas a ON u.id = a.usuario_id
                WHERE u.tipo_usuario = 'cliente'
                ORDER BY u.nome ASC";

        $stmt = $pdo->query($sql);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id'])) throw new Exception("ID do cliente não informado.");

        $pdo->beginTransaction();

        
        $sqlUser = "UPDATE usuarios SET nome = ?, telefone = ?, endereco = ? WHERE id = ?";
        $stmtUser = $pdo->prepare($sqlUser);
        $stmtUser->execute([$data['nome'], $data['telefone'], $data['endereco'], $data['id']]);

        
        $check = $pdo->prepare("SELECT id FROM assinaturas WHERE usuario_id = ?");
        $check->execute([$data['id']]);
        
        if ($check->rowCount() > 0) {
            $sqlAss = "UPDATE assinaturas SET frequencia = ?, status = ? WHERE usuario_id = ?";
            $stmtAss = $pdo->prepare($sqlAss);
            $stmtAss->execute([$data['frequencia'], $data['status'], $data['id']]);
        } else {
            $sqlAss = "INSERT INTO assinaturas (usuario_id, frequencia, status) VALUES (?, ?, ?)";
            $stmtAss = $pdo->prepare($sqlAss);
            $stmtAss->execute([$data['id'], $data['frequencia'], $data['status']]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Dados atualizados com sucesso!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}//sou gay
//gosto dd um tal de wilson
//eu vi o bofe tomar banho
//e o tamanho da sua mala era demais 
// eu virei gay e me assumi
// a lua de mel foi no Egito
// abri as pernas e dei um grito
// hey vai devagar
?>