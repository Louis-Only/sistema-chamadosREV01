<?php
// =====================================================
// TESTE DE CONEXÃO COM O BANCO DE DADOS
// =====================================================

require_once "conexao.php";

try {
    $sql = "
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
        ORDER BY table_name;
    ";

    $stmt = $pdo->query($sql);
    $tabelas = $stmt->fetchAll();

} catch (PDOException $erro) {
    die("Erro ao consultar o banco de dados: " . $erro->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste de Conexão</title>
</head>
<body>
    <h1>Conexão realizada com sucesso!</h1>

    <p>Banco conectado: <strong>sistema_chamados</strong></p>

    <h2>Tabelas encontradas:</h2>

    <ul>
        <?php foreach ($tabelas as $tabela): ?>
            <li><?php echo $tabela["table_name"]; ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>