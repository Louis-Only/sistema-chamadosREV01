<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once "conexao.php";

// Só usuário logado pode registrar ação.
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
$descricaoAcao = trim($_POST["descricao_acao"] ?? "");
$statusNovo = trim($_POST["status_novo"] ?? "");

if (empty($idChamado) || empty($descricaoAcao) || empty($statusNovo)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha todos os campos obrigatórios."
    ]);
    exit;
}

if (!is_numeric($idChamado)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Chamado inválido."
    ]);
    exit;
}

$statusPermitidos = ["Em aberto", "Em análise", "Resolvido"];

if (!in_array($statusNovo, $statusPermitidos)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Status inválido."
    ]);
    exit;
}

try {
    // Primeiro verificamos se o usuário pode atuar neste chamado.
    if ($ehAdmin) {
        $sqlChamado = "
            SELECT 
                id_chamado,
                id_usuario,
                id_responsavel,
                status
            FROM chamados
            WHERE id_chamado = :id_chamado
            LIMIT 1
        ";

        $stmtChamado = $pdo->prepare($sqlChamado);
        $stmtChamado->execute([
            ":id_chamado" => $idChamado
        ]);
    } else {
        $sqlChamado = "
            SELECT 
                id_chamado,
                id_usuario,
                id_responsavel,
                status
            FROM chamados
            WHERE id_chamado = :id_chamado
            AND (
                id_usuario = :id_usuario
                OR id_responsavel = :id_usuario
            )
            LIMIT 1
        ";

        $stmtChamado = $pdo->prepare($sqlChamado);
        $stmtChamado->execute([
            ":id_chamado" => $idChamado,
            ":id_usuario" => $idUsuario
        ]);
    }

    $chamado = $stmtChamado->fetch();

    if (!$chamado) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Chamado não encontrado ou sem permissão para registrar ação."
        ]);
        exit;
    }

    $statusAnterior = $chamado["status"];

    // Usamos transação para garantir que a ação e o status sejam atualizados juntos.
    $pdo->beginTransaction();

    $sqlAcao = "
        INSERT INTO chamados_acoes (
            id_chamado,
            id_usuario,
            descricao_acao,
            status_anterior,
            status_novo
        ) VALUES (
            :id_chamado,
            :id_usuario,
            :descricao_acao,
            :status_anterior,
            :status_novo
        )
    ";

    $stmtAcao = $pdo->prepare($sqlAcao);
    $stmtAcao->execute([
        ":id_chamado" => $idChamado,
        ":id_usuario" => $idUsuario,
        ":descricao_acao" => $descricaoAcao,
        ":status_anterior" => $statusAnterior,
        ":status_novo" => $statusNovo
    ]);

    $sqlAtualizaStatus = "
        UPDATE chamados
        SET status = :status_novo
        WHERE id_chamado = :id_chamado
    ";

    $stmtStatus = $pdo->prepare($sqlAtualizaStatus);
    $stmtStatus->execute([
        ":status_novo" => $statusNovo,
        ":id_chamado" => $idChamado
    ]);

    $pdo->commit();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Ação registrada com sucesso!"
    ]);
    exit;

} catch (PDOException $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao registrar ação: " . $erro->getMessage()
    ]);
    exit;
}