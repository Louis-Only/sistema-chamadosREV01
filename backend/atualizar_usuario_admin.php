<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once "conexao.php";

// Apenas administradores podem editar usuários por esta rota.
if (!isset($_SESSION["usuario_id"]) || ($_SESSION["usuario_tipo"] ?? "") !== "admin") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Acesso negado. Apenas administradores podem editar usuários."
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

$idUsuarioEditado = trim($_POST["id_usuario"] ?? "");
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
    empty($idUsuarioEditado) ||
    empty($nome) ||
    empty($email) ||
    empty($telefone) ||
    empty($cpf) ||
    empty($departamento) ||
    empty($cargo) ||
    empty($tipoUsuario)
) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha todos os campos obrigatórios."
    ]);
    exit;
}

if (!is_numeric($idUsuarioEditado)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Usuário inválido."
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

// Evita que o administrador remova o próprio acesso sem querer.
if ((int) $idUsuarioEditado === (int) $_SESSION["usuario_id"]) {
    $tipoUsuario = "admin";
}

if (!empty($senha) || !empty($confirmarSenha)) {
    if ($senha !== $confirmarSenha) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "As senhas não conferem."
        ]);
        exit;
    }
}

try {
    // Verifica se o e-mail já pertence a outro usuário.
    $sqlEmail = "
        SELECT id_usuario 
        FROM usuarios 
        WHERE email = :email
        AND id_usuario <> :id_usuario
    ";

    $stmtEmail = $pdo->prepare($sqlEmail);
    $stmtEmail->execute([
        ":email" => $email,
        ":id_usuario" => $idUsuarioEditado
    ]);

    if ($stmtEmail->fetch()) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este e-mail já está sendo usado por outro usuário."
        ]);
        exit;
    }

    // Verifica se o CPF já pertence a outro usuário.
    $sqlCpf = "
        SELECT id_usuario 
        FROM usuarios 
        WHERE cpf = :cpf
        AND id_usuario <> :id_usuario
    ";

    $stmtCpf = $pdo->prepare($sqlCpf);
    $stmtCpf->execute([
        ":cpf" => $cpf,
        ":id_usuario" => $idUsuarioEditado
    ]);

    if ($stmtCpf->fetch()) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este CPF já está sendo usado por outro usuário."
        ]);
        exit;
    }

    if (!empty($senha)) {
        // Quando a senha é informada, atualizamos também o hash da senha.
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $sqlUpdate = "
            UPDATE usuarios
            SET 
                nome = :nome,
                email = :email,
                telefone = :telefone,
                cpf = :cpf,
                departamento = :departamento,
                cargo = :cargo,
                tipo_usuario = :tipo_usuario,
                senha = :senha
            WHERE id_usuario = :id_usuario
        ";

        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ":nome" => $nome,
            ":email" => $email,
            ":telefone" => $telefone,
            ":cpf" => $cpf,
            ":departamento" => $departamento,
            ":cargo" => $cargo,
            ":tipo_usuario" => $tipoUsuario,
            ":senha" => $senhaHash,
            ":id_usuario" => $idUsuarioEditado
        ]);
    } else {
        // Se a senha ficou em branco, mantemos a senha atual do usuário.
        $sqlUpdate = "
            UPDATE usuarios
            SET 
                nome = :nome,
                email = :email,
                telefone = :telefone,
                cpf = :cpf,
                departamento = :departamento,
                cargo = :cargo,
                tipo_usuario = :tipo_usuario
            WHERE id_usuario = :id_usuario
        ";

        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ":nome" => $nome,
            ":email" => $email,
            ":telefone" => $telefone,
            ":cpf" => $cpf,
            ":departamento" => $departamento,
            ":cargo" => $cargo,
            ":tipo_usuario" => $tipoUsuario,
            ":id_usuario" => $idUsuarioEditado
        ]);
    }

    // Se o admin alterou o próprio cadastro, atualizamos a sessão também.
    if ((int) $idUsuarioEditado === (int) $_SESSION["usuario_id"]) {
        $_SESSION["usuario_nome"] = $nome;
        $_SESSION["usuario_email"] = $email;
        $_SESSION["usuario_telefone"] = $telefone;
        $_SESSION["usuario_cpf"] = $cpf;
        $_SESSION["usuario_departamento"] = $departamento;
        $_SESSION["usuario_cargo"] = $cargo;
        $_SESSION["usuario_tipo"] = $tipoUsuario;
    }

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Usuário atualizado com sucesso!"
    ]);
    exit;

} catch (PDOException $erro) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao atualizar usuário: " . $erro->getMessage()
    ]);
    exit;
}