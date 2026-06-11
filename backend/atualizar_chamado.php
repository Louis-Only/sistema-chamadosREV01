<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once "conexao.php";

// Só permitimos atualizar chamado se existir usuário logado.
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
$tipoUsuario = $_SESSION["usuario_tipo"] ?? "comum";
$ehAdmin = ($tipoUsuario === "admin");

$idChamado = trim($_POST["id_chamado"] ?? "");
$titulo = trim($_POST["titulo"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");
$departamento = trim($_POST["departamento"] ?? "");
$assunto = trim($_POST["assunto"] ?? "");
$idResponsavel = trim($_POST["id_responsavel"] ?? "");
$status = trim($_POST["status"] ?? "");

if (
    empty($idChamado) ||
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

if (!is_numeric($idChamado) || !is_numeric($idResponsavel)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Dados inválidos para atualização."
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
    // Confere se o chamado existe e se o usuário tem permissão.
    if ($ehAdmin) {
        $sqlPermissao = "
            SELECT 
                id_chamado,
                id_usuario,
                id_responsavel
            FROM chamados
            WHERE id_chamado = :id_chamado
            LIMIT 1
        ";

        $stmtPermissao = $pdo->prepare($sqlPermissao);
        $stmtPermissao->execute([
            ":id_chamado" => $idChamado
        ]);
    } else {
        $sqlPermissao = "
            SELECT 
                id_chamado,
                id_usuario,
                id_responsavel
            FROM chamados
            WHERE id_chamado = :id_chamado
            AND (
                id_usuario = :id_usuario
                OR id_responsavel = :id_usuario
            )
            LIMIT 1
        ";

        $stmtPermissao = $pdo->prepare($sqlPermissao);
        $stmtPermissao->execute([
            ":id_chamado" => $idChamado,
            ":id_usuario" => $idUsuario
        ]);
    }

    $chamadoPermitido = $stmtPermissao->fetch();

    if (!$chamadoPermitido) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Chamado não encontrado ou sem permissão para editar."
        ]);
        exit;
    }

    // Confere se o responsável existe e pertence ao mesmo departamento.
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

    if ($usuarioResponsavel["departamento"] !== $departamento) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "O responsável selecionado não pertence ao departamento do chamado."
        ]);
        exit;
    }

    // Atualiza o chamado e mantém o nome textual do responsável para facilitar leitura.
    $sqlUpdate = "
        UPDATE chamados
        SET 
            titulo = :titulo,
            descricao = :descricao,
            departamento = :departamento,
            assunto = :assunto,
            id_responsavel = :id_responsavel,
            responsavel = :responsavel,
            status = :status
        WHERE id_chamado = :id_chamado
    ";

    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([
        ":titulo" => $titulo,
        ":descricao" => $descricao,
        ":departamento" => $departamento,
        ":assunto" => $assunto,
        ":id_responsavel" => $idResponsavel,
        ":responsavel" => $usuarioResponsavel["nome"],
        ":status" => $status,
        ":id_chamado" => $idChamado
    ]);

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Chamado atualizado com sucesso!"
    ]);
    exit;

} catch (PDOException $erro) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao atualizar chamado: " . $erro->getMessage()
    ]);
    exit;
}