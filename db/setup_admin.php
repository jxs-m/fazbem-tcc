<?php

require 'db.php';

$email = "admin@fazbem.com";
$senha_plana = "admin123"; 

try {
    
    $sql = "UPDATE usuarios SET senha_hash = ? WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$senha_plana, $email]);

    echo "<h1>Pronto!</h1>";
    echo "A senha do admin foi alterada para texto puro: <b>$senha_plana</b><br>";
    echo "O sistema agora NÃO usa mais hash.";
    echo "<br><a href='login.html'>Testar Login</a>";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>