<?php
session_start();

require_once "backend/conexao.php";

// Só usuário logado pode acessar.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

$idUsuario = $_SESSION["usuario_id"];
$tipoUsuario = $_SESSION["usuario_tipo"] ?? "comum";
$ehAdmin = ($tipoUsuario === "admin");

// Identifica de qual tela o usuário veio.
// origem=admin  -> volta para gerenciar-chamados.php
// origem=comum  -> volta para chamados.php
$origem = $_GET["origem"] ?? "comum";

if ($origem === "admin" && $ehAdmin) {
    $linkVoltar = "gerenciar-chamados.php";
} else {
    $linkVoltar = "chamados.php";
}

$idChamado = $_GET["id"] ?? "";

if (empty($idChamado) || !is_numeric($idChamado)) {
    header("Location: " . $linkVoltar);
    exit;
}

try {
    // Admin pode ver qualquer chamado.
    // Usuário comum só pode ver se abriu o chamado ou se ele é o responsável.
    if ($ehAdmin) {
        $sqlChamado = "
            SELECT 
                c.id_chamado,
                c.id_usuario,
                c.id_responsavel,
                c.titulo,
                c.descricao,
                c.departamento,
                c.assunto,
                c.status,
                c.data_hora,
                solicitante.nome AS nome_solicitante,
                responsavel.nome AS nome_responsavel
            FROM chamados c
            INNER JOIN usuarios solicitante
                ON solicitante.id_usuario = c.id_usuario
            INNER JOIN usuarios responsavel
                ON responsavel.id_usuario = c.id_responsavel
            WHERE c.id_chamado = :id_chamado
            LIMIT 1
        ";

        $stmtChamado = $pdo->prepare($sqlChamado);
        $stmtChamado->execute([
            ":id_chamado" => $idChamado
        ]);
    } else {
        $sqlChamado = "
            SELECT 
                c.id_chamado,
                c.id_usuario,
                c.id_responsavel,
                c.titulo,
                c.descricao,
                c.departamento,
                c.assunto,
                c.status,
                c.data_hora,
                solicitante.nome AS nome_solicitante,
                responsavel.nome AS nome_responsavel
            FROM chamados c
            INNER JOIN usuarios solicitante
                ON solicitante.id_usuario = c.id_usuario
            INNER JOIN usuarios responsavel
                ON responsavel.id_usuario = c.id_responsavel
            WHERE c.id_chamado = :id_chamado
            AND (
                c.id_usuario = :id_usuario
                OR c.id_responsavel = :id_usuario
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
        die("Chamado não encontrado ou você não possui permissão para visualizá-lo.");
    }

    // Busca o histórico de ações do chamado.
    $sqlAcoes = "
        SELECT 
            a.id_acao,
            a.descricao_acao,
            a.status_anterior,
            a.status_novo,
            a.data_hora,
            u.nome AS nome_usuario
        FROM chamados_acoes a
        INNER JOIN usuarios u 
            ON u.id_usuario = a.id_usuario
        WHERE a.id_chamado = :id_chamado
        ORDER BY a.data_hora DESC
    ";

    $stmtAcoes = $pdo->prepare($sqlAcoes);
    $stmtAcoes->execute([
        ":id_chamado" => $idChamado
    ]);

    $acoes = $stmtAcoes->fetchAll();

} catch (PDOException $erro) {
    die("Erro ao carregar chamado: " . $erro->getMessage());
}

function formatarData($data)
{
    if (empty($data)) {
        return "";
    }

    return date("d/m/Y H:i", strtotime($data));
}

function classeStatus($status)
{
    if ($status === "Resolvido") {
        return "status-resolvido";
    }

    if ($status === "Em análise") {
        return "status-analise";
    }

    return "status-aberto";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Chamado - Sistema de Chamados</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-sistema">

    <main class="container py-5">
        <section class="card shadow-sm border-0 rounded-4 mx-auto" style="max-width: 1000px;">
            <div class="card-body p-4 p-md-5">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <h1 class="titulo-formulario mb-2">
                            Ver chamado #<?php echo str_pad($chamado["id_chamado"], 3, "0", STR_PAD_LEFT); ?>
                        </h1>
                        <p class="texto-sistema mb-0">
                            Acompanhe os dados do chamado e registre as ações realizadas.
                        </p>
                    </div>

                    <a href="<?php echo $linkVoltar; ?>" class="btn btn-outline-dark">
                        Voltar
                    </a>
                </div>

                <div class="card border-0 rounded-4 mb-4" style="background-color: #f5f3ed;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                            <div>
                                <h4 class="fw-bold mb-2">
                                    <?php echo htmlspecialchars($chamado["titulo"]); ?>
                                </h4>
                                <p class="mb-0 text-muted">
                                    Aberto em <?php echo formatarData($chamado["data_hora"]); ?>
                                </p>
                            </div>

                            <span class="status-chamado <?php echo classeStatus($chamado["status"]); ?>">
                                <?php echo htmlspecialchars($chamado["status"]); ?>
                            </span>
                        </div>

                        <div class="row gy-3 mb-4">
                            <div class="col-md-6">
                                <strong>Solicitante</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($chamado["nome_solicitante"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <strong>Responsável</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($chamado["nome_responsavel"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <strong>Departamento</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($chamado["departamento"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <strong>Assunto</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($chamado["assunto"]); ?></p>
                            </div>
                        </div>

                        <div>
                            <strong>Descrição original do chamado</strong>
                            <div class="mt-2 p-3 bg-white border rounded-3">
                                <?php echo nl2br(htmlspecialchars($chamado["descricao"])); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 rounded-4 mb-4" style="background-color: #f5f3ed;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Registrar ação no chamado</h5>

                        <form id="formAcaoChamado">
                            <input 
                                type="hidden" 
                                id="idChamadoAcao" 
                                name="id_chamado" 
                                value="<?php echo htmlspecialchars($chamado["id_chamado"]); ?>"
                            >

                            <div class="mb-3">
                                <label for="descricaoAcao" class="form-label">O que foi feito?</label>
                                <textarea 
                                    class="form-control" 
                                    id="descricaoAcao" 
                                    name="descricao_acao"
                                    rows="4"
                                    placeholder="Descreva a ação realizada no atendimento..."
                                    required
                                ></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="statusNovoAcao" class="form-label">Novo status</label>
                                <select class="form-select" id="statusNovoAcao" name="status_novo" required>
                                    <option value="">Selecione</option>
                                    <option value="Em aberto" <?php echo $chamado["status"] === "Em aberto" ? "selected" : ""; ?>>Em aberto</option>
                                    <option value="Em análise" <?php echo $chamado["status"] === "Em análise" ? "selected" : ""; ?>>Em análise</option>
                                    <option value="Resolvido" <?php echo $chamado["status"] === "Resolvido" ? "selected" : ""; ?>>Resolvido</option>
                                </select>
                            </div>

                            <div id="mensagemAcaoChamado" class="mb-3"></div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark btn-lg">
                                    Registrar ação
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div>
                    <h5 class="fw-bold mb-3">Histórico de ações</h5>

                    <?php if (count($acoes) === 0): ?>
                        <div class="alert alert-info mb-0">
                            Nenhuma ação registrada neste chamado até o momento.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($acoes as $acao): ?>
                                <article class="card-chamado">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-2">
                                        <div>
                                            <strong><?php echo htmlspecialchars($acao["nome_usuario"]); ?></strong>
                                            <p class="mb-0 text-muted">
                                                <?php echo formatarData($acao["data_hora"]); ?>
                                            </p>
                                        </div>

                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($acao["status_anterior"]); ?>
                                            →
                                            <?php echo htmlspecialchars($acao["status_novo"]); ?>
                                        </span>
                                    </div>

                                    <p class="mb-0">
                                        <?php echo nl2br(htmlspecialchars($acao["descricao_acao"])); ?>
                                    </p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/ajax.js"></script>
    <script src="assets/js/validacoes.js"></script>
</body>
</html>