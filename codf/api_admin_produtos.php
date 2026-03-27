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
        $stmt = $pdo->query("SELECT * FROM produtos ORDER BY nome ASC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }
// tinhna esquecido das fotos e isso me custou algumas horas (sanidade tmb)
    if ($method === 'POST') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (empty($data['nome'])) {
            throw new Exception("Nome obrigatório");
        }

        $nome = trim($data['nome']);
        $categoria = $data['categoria'];
        $unidade = trim($data['unidade']);
        $preco = !empty($data['preco']) ? floatval(str_replace(',', '.', $data['preco'])) : 0.00;
        $estoque = !empty($data['estoque']) ? intval($data['estoque']) : 0;
        $imagem = !empty($data['imagem']) ? $data['imagem'] : null;
        $id = !empty($data['id']) ? $data['id'] : null;

        if ($id) {
            if ($imagem) {
                $sql = "UPDATE produtos SET nome=?, categoria=?, preco=?, unidade=?, estoque_atual=?, imagem_url=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $categoria, $preco, $unidade, $estoque, $imagem, $id]);
            } else {
                $sql = "UPDATE produtos SET nome=?, categoria=?, preco=?, unidade=?, estoque_atual=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $categoria, $preco, $unidade, $estoque, $id]);
            }
            echo json_encode(['success' => true, 'message' => 'Atualizado']);
        } else {
            $sql = "INSERT INTO produtos (nome, categoria, preco, unidade, estoque_atual, imagem_url) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $categoria, $preco, $unidade, $estoque, $imagem]);
            echo json_encode(['success' => true, 'message' => 'Cadastrado']);
        }
        exit;
    }

    if ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!empty($data['id'])) {
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true, 'message' => 'Excluído']);
        } else {
            throw new Exception("ID inválido");
        }
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>