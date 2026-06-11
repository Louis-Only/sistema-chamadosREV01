<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once "conexao.php";

// Se o usuário não estiver logado, não deixa atualizar os dados
if (!isset($_SESSION["usuario_id"])) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Sessão expirada. Faça login novamente."
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método de requisição inválido."
    ]);
    exit;
}

$idUsuario = $_SESSION["usuario_id"];

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$telefone = trim($_POST["telefone"] ?? "");
$cpf = trim($_POST["cpf"] ?? "");

// Mantemos uma validação simples também no backend,
// porque a validação do JavaScript ajuda, mas não substitui a validação no servidor.
$cpfNumerico = preg_replace('/\D/', '', $cpf);

if (empty($nome) || empty($email) || empty($telefone) || empty($cpf)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha todos os campos obrigatórios."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Informe um e-mail válido."
    ]);
    exit;
}

if (strlen($cpfNumerico) !== 11) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Informe um CPF válido com 11 números."
    ]);
    exit;
}

try {
    // Verifica se o e-mail informado já pertence a outro usuário
    $sqlEmail = "
        SELECT id_usuario 
        FROM usuarios 
        WHERE email = :email 
        AND id_usuario <> :id_usuario
    ";

    $stmtEmail = $pdo->prepare($sqlEmail);
    $stmtEmail->execute([
        ":email" => $email,
        ":id_usuario" => $idUsuario
    ]);

    if ($stmtEmail->fetch()) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este e-mail já está sendo usado por outro usuário."
        ]);
        exit;
    }

    // Faz a mesma validação para o CPF, evitando duplicidade no cadastro
    $sqlCpf = "
        SELECT id_usuario 
        FROM usuarios 
        WHERE cpf = :cpf 
        AND id_usuario <> :id_usuario
    ";

    $stmtCpf = $pdo->prepare($sqlCpf);
    $stmtCpf->execute([
        ":cpf" => $cpf,
        ":id_usuario" => $idUsuario
    ]);

    if ($stmtCpf->fetch()) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este CPF já está sendo usado por outro usuário."
        ]);
        exit;
    }

    // Atualiza os dados principais do usuário logado
    $sqlUpdate = "
        UPDATE usuarios
        SET 
            nome = :nome,
            email = :email,
            telefone = :telefone,
            cpf = :cpf
        WHERE id_usuario = :id_usuario
    ";

    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([
        ":nome" => $nome,
        ":email" => $email,
        ":telefone" => $telefone,
        ":cpf" => $cpf,
        ":id_usuario" => $idUsuario
    ]);

    // Atualiza também os dados da sessão, para a dashboard mostrar tudo atualizado
    $_SESSION["usuario_nome"] = $nome;
    $_SESSION["usuario_email"] = $email;
    $_SESSION["usuario_telefone"] = $telefone;
    $_SESSION["usuario_cpf"] = $cpf;

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Perfil atualizado com sucesso!"
    ]);
    exit;

} catch (PDOException $erro) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao atualizar perfil: " . $erro->getMessage()
    ]);
    exit;
}