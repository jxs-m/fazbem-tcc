<?php
// api_cadastro.php
session_start();
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['nome']) || empty($data['email']) || empty($data['senha'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit;
}

try {
    
    $checkEmail = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $checkEmail->execute([$data['email']]);
    if ($checkEmail->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Este e-mail já está cadastrado.']);
        exit;
    }

    
    $senha_criptografada = password_hash($data['senha'], PASSWORD_DEFAULT);

    $pdo->beginTransaction();

   
    $sqlUser = "INSERT INTO usuarios (nome, email, senha, telefone, endereco, ponto_referencia, tipo_usuario) 
                VALUES (?, ?, ?, ?, ?, ?, 'cliente')";
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([
        $data['nome'], 
        $data['email'], 
        $senha_criptografada, 
        $data['telefone'], 
        $data['endereco'], 
        $data['referencia'] ?? null
    ]);

    $usuario_id = $pdo->lastInsertId();

   
    if (!empty($data['frequencia'])) {
        $sqlAssinatura = "INSERT INTO assinaturas (usuario_id, frequencia, status) VALUES (?, ?, 'Ativa')";
        $stmtAssinatura = $pdo->prepare($sqlAssinatura);
        $stmtAssinatura->execute([$usuario_id, $data['frequencia']]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Cadastro realizado com sucesso!']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar: ' . $e->getMessage()]);
}
?>