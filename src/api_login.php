<?php
// api_login.php
session_start();
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['email']) || empty($data['senha'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Preencha e-mail e senha.']);
    exit;
}

try {
   
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    
    if ($user && password_verify($data['senha'], $user['senha'])) {
        
       
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['tipo_usuario'] = $user['tipo_usuario'];

       
        $redirect = ($user['tipo_usuario'] === 'admin') ? 'admin.html' : 'catalogo.html';

        echo json_encode([
            'success' => true, 
            'redirect' => $redirect
        ]);

    } else {
        
        echo json_encode(['success' => false, 'message' => 'E-mail ou senha incorretos.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno no servidor.']);
}
?>