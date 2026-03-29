<?php

session_start();

ob_clean(); 
header('Content-Type: application/json');

require 'db.php'; 

//achei fácil
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$email = $data['email'] ?? '';
$senha = $data['senha'] ?? '';

if (empty($email) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos.']);
    exit;
}

try {
    
    $stmt = $pdo->prepare("SELECT id, nome, senha_hash, tipo_usuario FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    
    if ($user && $senha === $user['senha_hash']) {
        
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nome'] = $user['nome'];
        $_SESSION['tipo_usuario'] = $user['tipo_usuario'];

        $redirectUrl = ($user['tipo_usuario'] === 'admin') ? 'admin.html' : 'catalogo.html';

        echo json_encode([
            'success' => true, 
            'message' => 'Login realizado!',
            'redirect' => $redirectUrl
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'E-mail ou senha incorretos.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>