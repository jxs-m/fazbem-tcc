<?php

session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login necessário.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$acao = $input['acao'] ?? ''; 

try {
    // Busca ID da assinatura do usuário logado
    $stmtId = $pdo->prepare("SELECT id FROM assinaturas WHERE usuario_id = ?");
    $stmtId->execute([$_SESSION['usuario_id']]);
    $ass = $stmtId->fetch();
    
    if (!$ass) throw new Exception("Assinatura não encontrada.");
    $assId = $ass['id'];

    $msg = "";

    
    if ($acao === 'pausar') {
        $pdo->prepare("UPDATE assinaturas SET status = 'Pausada' WHERE id = ?")->execute([$assId]);
        $msg = "Assinatura pausada.";
    } 
   
    elseif ($acao === 'reativar') {
        $pdo->prepare("UPDATE assinaturas SET status = 'Ativa' WHERE id = ?")->execute([$assId]);
        $msg = "Assinatura reativada!";
    }
   
    elseif ($acao === 'cancelar') {
        $pdo->prepare("UPDATE assinaturas SET status = 'Cancelada' WHERE id = ?")->execute([$assId]);
        $msg = "Assinatura cancelada.";
    }
   
    elseif ($acao === 'nova_preferencia') {
        $desc = $input['descricao'];
        $tipo = $input['tipo'];
        if(empty($desc)) throw new Exception("Descreva a preferência.");
        
        $pdo->prepare("INSERT INTO preferencias (assinatura_id, tipo, descricao) VALUES (?, ?, ?)")
            ->execute([$assId, $tipo, $desc]);
        $msg = "Preferência salva!";
    }
    // ALTERAR PLANO (feito)
    elseif ($acao === 'alterar_plano') {
        $novaFreq = $input['nova_frequencia'];
        $validos = ['Semanal', 'Quinzenal', 'Mensal'];

        if (!in_array($novaFreq, $validos)) throw new Exception("Plano inválido.");

        $pdo->prepare("UPDATE assinaturas SET frequencia = ? WHERE id = ?")
            ->execute([$novaFreq, $assId]);
        
        $msg = "Plano alterado para " . $novaFreq . " com sucesso!";
    }
    else {
        throw new Exception("Ação inválida.");
    }

    echo json_encode(['success' => true, 'message' => $msg]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>