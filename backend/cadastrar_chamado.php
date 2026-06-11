<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once "conexao.php";

// Só permitimos cadastrar chamado se houver usuário logado.
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

$titulo = trim($_POST["titulo"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");
$departamento = trim($_POST["departamento"] ?? "");
$assunto = trim($_POST["assunto"] ?? "");
$idResponsavel = trim($_POST["id_responsavel"] ?? "");
$status = trim($_POST["status"] ?? "Em aberto");

// Validação principal do backend.
// Mesmo com validação em JavaScript, o servidor também precisa conferir os dados.
if (
    empty($titulo) ||
    empty($descricao) ||
    empty($departamento) ||
    empty($assunto) ||
    empty($idResponsavel) ||
    empty($status)
) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha todos os campos obrigatórios."
    ]);
    exit;
}

if (!is_numeric($idResponsavel)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Responsável inválido."
    ]);
    exit;
}

$statusPermitidos = ["Em aberto", "Em análise", "Resolvido"];

$assuntosPorDepartamento = [
    "TI" => [
        "Computador não liga",
        "Acesso ao sistema",
        "Problema de internet",
        "Instalação de software"
    ],
    "RH" => [
        "Atualização cadastral",
        "Solicitação de férias",
        "Dúvida sobre benefícios",
        "Documentos e declarações"
    ],
    "Financeiro" => [
        "Reembolso",
        "Nota fiscal",
        "Pagamento",
        "Prestação de contas"
    ]
];

if (!array_key_exists($departamento, $assuntosPorDepartamento)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Departamento inválido."
    ]);
    exit;
}

if (!in_array($assunto, $assuntosPorDepartamento[$departamento])) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Assunto inválido para o departamento selecionado."
    ]);
    exit;
}

if (!in_array($status, $statusPermitidos)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Status inválido."
    ]);
    exit;
}

try {
    // Buscamos o responsável no banco para garantir que ele existe
    // e para conferir se pertence ao mesmo departamento do chamado.
    $sqlResponsavel = "
        SELECT 
            id_usuario,
            nome,
            departamento
        FROM usuarios
        WHERE id_usuario = :id_responsavel
        LIMIT 1
    ";

    $stmtResponsavel = $pdo->prepare($sqlResponsavel);
    $stmtResponsavel->execute([
        ":id_responsavel" => $idResponsavel
    ]);

    $usuarioResponsavel = $stmtResponsavel->fetch();

    if (!$usuarioResponsavel) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Responsável não encontrado."
        ]);
        exit;
    }

    // Regra importante:
    // o responsável precisa pertencer ao mesmo departamento do chamado.
    if ($usuarioResponsavel["departamento"] !== $departamento) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "O responsável selecionado não pertence ao departamento do chamado."
        ]);
        exit;
    }

    // O chamado fica vinculado ao usuário que abriu e ao usuário responsável.
    // Mantemos também o campo textual responsavel com o nome, para facilitar consultas e leitura.
    $sql = "
        INSERT INTO chamados (
            id_usuario,
            id_responsavel,
            titulo,
            descricao,
            departamento,
            responsavel,
            assunto,
            status
        ) VALUES (
            :id_usuario,
            :id_responsavel,
            :titulo,
            :descricao,
            :departamento,
            :responsavel,
            :assunto,
            :status
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id_usuario" => $idUsuario,
        ":id_responsavel" => $idResponsavel,
        ":titulo" => $titulo,
        ":descricao" => $descricao,
        ":departamento" => $departamento,
        ":responsavel" => $usuarioResponsavel["nome"],
        ":assunto" => $assunto,
        ":status" => $status
    ]);

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Chamado cadastrado com sucesso!",
        "redirect" => ($_SESSION["usuario_tipo"] ?? "comum") === "admin"
            ? "dashboard-admin.php"
            : "dashboard.php"
    ]);
    exit;

} catch (PDOException $erro) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao cadastrar chamado: " . $erro->getMessage()
    ]);
    exit;
}