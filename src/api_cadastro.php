<?php

session_start();
header('Content-Type: application/json');
require 'db.php';


$input = file_get_contents('php://input');
$data = json_decode($input, true);


if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Nenhum dado recebido.']);
    exit;
}

$nome = trim($data['nome']);
$email = trim($data['email']);
$senha = $data['senha']; // Senha Texto em texto 
$cpf = $data['cpf'];
$telefone = $data['telefone'];
$endereco = $data['endereco'];
$referencia = $data['referencia'];
$frequencia = $data['frequencia'];


if (empty($email) || empty($senha) || empty($cpf) || empty($frequencia)) {
    echo json_encode(['success' => false, 'message' => 'Preencha os campos obrigatórios.']);
    exit;
}

try {
    $pdo->beginTransaction();

    
    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmtCheck->execute([$email]);
    if ($stmtCheck->rowCount() > 0) {
        throw new Exception("Este e-mail já está cadastrado.");
    }

    // eu apenas desisti de usar hash porque passei 1 hora tentando arrumar bugs e nada funcionava
    $sqlUser = "INSERT INTO usuarios (nome, email, senha_hash, cpf, telefone, endereco, ponto_referencia, tipo_usuario) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'cliente')";
    
    $stmtUser = $pdo->prepare($sqlUser);
    
    
    $stmtUser->execute([$nome, $email, $senha, $cpf, $telefone, $endereco, $referencia]);
    
    $usuario_id = $pdo->lastInsertId();

    
    $sqlAssinatura = "INSERT INTO assinaturas (usuario_id, frequencia, status) VALUES (?, ?, 'Ativa')";
    $stmtAss = $pdo->prepare($sqlAssinatura);
    $stmtAss->execute([$usuario_id, $frequencia]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Cadastro realizado com sucesso!']);

} catch (Exception $e) {
    $pdo->rollBack();
    
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar: ' . $e->getMessage()]);
}
?>