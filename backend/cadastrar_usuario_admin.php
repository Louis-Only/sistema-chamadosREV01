<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once "conexao.php";

// Somente admin pode cadastrar usuários por esta tela.
if (!isset($_SESSION["usuario_id"]) || ($_SESSION["usuario_tipo"] ?? "") !== "admin") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Acesso negado. Apenas administradores podem cadastrar usuários."
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

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$telefone = trim($_POST["telefone"] ?? "");
$cpf = trim($_POST["cpf"] ?? "");
$departamento = trim($_POST["departamento"] ?? "");
$cargo = trim($_POST["cargo"] ?? "");
$tipoUsuario = trim($_POST["tipo_usuario"] ?? "comum");
$senha = trim($_POST["senha"] ?? "");
$confirmarSenha = trim($_POST["confirmarSenha"] ?? "");

$cpfNumerico = preg_replace('/\D/', '', $cpf);

if (
    empty($nome) ||
    empty($email) ||
    empty($telefone) ||
    empty($cpf) ||
    empty($departamento) ||
    empty($cargo) ||
    empty($tipoUsuario) ||
    empty($senha) ||
    empty($confirmarSenha)
) {
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

$departamentosPermitidos = ["TI", "RH", "Financeiro"];
$cargosPermitidos = ["Assistente", "Técnico", "Analista", "Coordenador", "Gerente"];
$tiposPermitidos = ["comum", "admin"];

if (!in_array($departamento, $departamentosPermitidos)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Departamento inválido."
    ]);
    exit;
}

if (!in_array($cargo, $cargosPermitidos)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Cargo inválido."
    ]);
    exit;
}

if (!in_array($tipoUsuario, $tiposPermitidos)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Tipo de usuário inválido."
    ]);
    exit;
}

try {
    // Evita cadastro duplicado por e-mail.
    $sqlEmail = "SELECT id_usuario FROM usuarios WHERE email = :email";
    $stmtEmail = $pdo->prepare($sqlEmail);
    $stmtEmail->execute([
        ":email" => $email
    ]);

    if ($stmtEmail->fetch()) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este e-mail já está cadastrado."
        ]);
        exit;
    }

    // Evita cadastro duplicado por CPF.
    $sqlCpf = "SELECT id_usuario FROM usuarios WHERE cpf = :cpf";
    $stmtCpf = $pdo->prepare($sqlCpf);
    $stmtCpf->execute([
        ":cpf" => $cpf
    ]);

    if ($stmtCpf->fetch()) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este CPF já está cadastrado."
        ]);
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sqlInsert = "
        INSERT INTO usuarios (
            nome,
            email,
            telefone,
            cpf,
            senha,
            departamento,
            cargo,
            tipo_usuario
        ) VALUES (
            :nome,
            :email,
            :telefone,
            :cpf,
            :senha,
            :departamento,
            :cargo,
            :tipo_usuario
        )
    ";

    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([
        ":nome" => $nome,
        ":email" => $email,
        ":telefone" => $telefone,
        ":cpf" => $cpf,
        ":senha" => $senhaHash,
        ":departamento" => $departamento,
        ":cargo" => $cargo,
        ":tipo_usuario" => $tipoUsuario
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