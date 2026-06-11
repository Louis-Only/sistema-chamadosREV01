<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método de requisição inválido."
    ]);
    exit;
}

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$telefone = trim($_POST["telefone"] ?? "");
$cpf = trim($_POST["cpf"] ?? "");
$senha = trim($_POST["senha"] ?? "");
$confirmarSenha = trim($_POST["confirmarSenha"] ?? "");

// Remove caracteres especiais do CPF e telefone para armazenar limpo, se desejar
$cpfNumerico = preg_replace('/\D/', '', $cpf);
$telefoneNumerico = preg_replace('/\D/', '', $telefone);

// Validações básicas
if (empty($nome) || empty($email) || empty($telefone) || empty($cpf) || empty($senha) || empty($confirmarSenha)) {
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

if ($senha !== $confirmarSenha) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "As senhas não conferem."
    ]);
    exit;
}

try {
    // Verifica se e-mail já existe
    $sqlVerificaEmail = "SELECT id_usuario FROM usuarios WHERE email = :email";
    $stmtEmail = $pdo->prepare($sqlVerificaEmail);
    $stmtEmail->execute([":email" => $email]);

    if ($stmtEmail->fetch()) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este e-mail já está cadastrado."
        ]);
        exit;
    }

    // Verifica se CPF já existe
    $sqlVerificaCpf = "SELECT id_usuario FROM usuarios WHERE cpf = :cpf";
    $stmtCpf = $pdo->prepare($sqlVerificaCpf);
    $stmtCpf->execute([":cpf" => $cpf]);

    if ($stmtCpf->fetch()) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este CPF já está cadastrado."
        ]);
        exit;
    }

    // Criptografa a senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere usuário
    $sqlInsert = "INSERT INTO usuarios (nome, email, telefone, cpf, senha)
                  VALUES (:nome, :email, :telefone, :cpf, :senha)";

    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([
        ":nome" => $nome,
        ":email" => $email,
        ":telefone" => $telefone,
        ":cpf" => $cpf,
        ":senha" => $senhaHash
    ]);

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Usuário cadastrado com sucesso!"
    ]);
    exit;

} catch (PDOException $erro) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao cadastrar usuário: " . $erro->getMessage()
    ]);
    exit;
}