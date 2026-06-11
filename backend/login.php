<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once "conexao.php";

// O login só deve aceitar requisições POST, vindas do formulário.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método de requisição inválido."
    ]);
    exit;
}

$email = trim($_POST["email"] ?? "");
$senha = trim($_POST["senha"] ?? "");

if (empty($email) || empty($senha)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha o e-mail e a senha."
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

try {
    // Agora também buscamos departamento, cargo e tipo_usuario,
    // porque essas informações serão usadas para diferenciar admin e usuário comum.
    $sql = "
        SELECT 
            id_usuario,
            nome,
            email,
            telefone,
            cpf,
            senha,
            departamento,
            cargo,
            tipo_usuario
        FROM usuarios
        WHERE email = :email
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":email" => $email
    ]);

    $usuario = $stmt->fetch();

    if (!$usuario) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Usuário não encontrado."
        ]);
        exit;
    }

    // A senha está criptografada no banco.
    // O password_verify valida se a senha digitada bate com o hash salvo.
    if (!password_verify($senha, $usuario["senha"])) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Senha incorreta."
        ]);
        exit;
    }

    // Salvamos os principais dados em sessão para usar nas páginas internas.
    $_SESSION["usuario_id"] = $usuario["id_usuario"];
    $_SESSION["usuario_nome"] = $usuario["nome"];
    $_SESSION["usuario_email"] = $usuario["email"];
    $_SESSION["usuario_telefone"] = $usuario["telefone"];
    $_SESSION["usuario_cpf"] = $usuario["cpf"];
    $_SESSION["usuario_departamento"] = $usuario["departamento"];
    $_SESSION["usuario_cargo"] = $usuario["cargo"];
    $_SESSION["usuario_tipo"] = $usuario["tipo_usuario"];

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Login realizado com sucesso!",
        "tipo_usuario" => $usuario["tipo_usuario"]
    ]);
    exit;

} catch (PDOException $erro) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao realizar login: " . $erro->getMessage()
    ]);
    exit;
}