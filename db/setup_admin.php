<?php

require '../src/db.php';


$CHAVE_SECRETA = 'MeuSetup2026'; 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chave_digitada = $_POST['chave'] ?? '';

    if ($chave_digitada !== $CHAVE_SECRETA) {
        die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                <h2 style='color:#dc2626;'>❌ Chave incorreta! Acesso negado.</h2>
                <a href='setup_admin.php' style='color:#2b8a3e;'>Tentar novamente</a>
             </div>");
    }

   //
    $nome_admin = 'Administrador';
    $email_admin = 'admin@fazbem.com';
    $senha_pura = '123456'; 
   
    $senha_criptografada = password_hash($senha_pura, PASSWORD_DEFAULT);

    try {
        echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>";
        
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->execute([$email_admin]);

        if ($check->rowCount() > 0) {
            $sql = "UPDATE usuarios SET senha = ?, tipo_usuario = 'admin' WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$senha_criptografada, $email_admin]);
            echo "<h2 style='color:#16a34a;'>✅ Administrador atualizado!</h2>";
            echo "<p>A senha do admin (<strong>$email_admin</strong>) foi convertida para Hash.</p>";
        } else {
            $sql = "INSERT INTO usuarios (nome, email, senha, telefone, endereco, tipo_usuario) 
                    VALUES (?, ?, ?, '00000000000', 'Sistema', 'admin')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome_admin, $email_admin, $senha_criptografada]);
            echo "<h2 style='color:#16a34a;'>✅ Administrador criado!</h2>";
            echo "<p>O usuário <strong>$email_admin</strong> foi criado com sucesso.</p>";
        }

        echo "<p style='margin-top:20px;'><a href='login.html' style='padding:10px 20px; background:#2b8a3e; color:white; text-decoration:none; border-radius:5px;'>Ir para o Login</a></p>";
        echo "<p style='color:#dc2626; margin-top:30px;'>⚠️ <strong>Aviso:</strong> Apesar dessa proteção, é altamente recomendado apagar este arquivo (<code>setup_admin.php</code>) do servidor após o uso.</p>";
        echo "</div>";
        
        exit;
        
    } catch (Exception $e) {
        die("<h2 style='color:red;'>❌ Erro no banco de dados:</h2><p>" . $e->getMessage() . "</p>");
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proteção do Setup</title>
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f3f4f6; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            color: #1f2937;
        }
        .box { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
            text-align: center; 
            width: 100%; 
            max-width: 400px;
        }
        input { 
            padding: 12px; 
            margin: 15px 0; 
            width: 100%; 
            box-sizing: border-box; 
            border: 1px solid #d1d5db; 
            border-radius: 6px; 
            font-size: 16px;
        }
        button { 
            background: #2b8a3e; 
            color: white; 
            border: none; 
            padding: 12px 20px; 
            cursor: pointer; 
            border-radius: 6px; 
            width: 100%; 
            font-size: 16px; 
            font-weight: bold;
        }
        button:hover { background: #15803d; }
    </style>
</head>
<body>
    <div class="box">
        <h2 style="margin-top:0; color:#2b8a3e;">🔒 Acesso Restrito</h2>
        <p style="color:#6b7280; font-size:14px;">Digite a chave de segurança para configurar o painel do administrador.</p>
        <form method="POST">
            <input type="password" name="chave" placeholder="Chave do arquivo..." required>
            <button type="submit">Executar Configuração</button>
        </form>
    </div>
</body>
</html>
