<?php
session_start();

require_once "backend/conexao.php";

// Só usuário logado pode acessar.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

$idUsuarioLogado = $_SESSION["usuario_id"];
$tipoUsuario = $_SESSION["usuario_tipo"] ?? "comum";
$ehAdmin = ($tipoUsuario === "admin");

try {
    // Tela pessoal:
    // mostra somente os chamados atribuídos ao usuário logado como responsável.
    // Assim, o usuário vê os chamados que precisa atender.
    $sql = "
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
            solicitante.nome AS solicitante,
            responsavel.nome AS responsavel
        FROM chamados c
        INNER JOIN usuarios solicitante
            ON solicitante.id_usuario = c.id_usuario
        INNER JOIN usuarios responsavel
            ON responsavel.id_usuario = c.id_responsavel
        WHERE c.id_responsavel = :id_usuario
        ORDER BY c.data_hora DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id_usuario" => $idUsuarioLogado
    ]);

    $chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $erro) {
    die("Erro ao carregar chamados: " . $erro->getMessage());
}

function classeStatus($status)
{
    if ($status === "Resolvido") {
        return "bg-success-subtle text-success-emphasis";
    }

    if ($status === "Em análise") {
        return "bg-primary-subtle text-primary-emphasis";
    }

    return "bg-warning-subtle text-warning-emphasis";
}

function formatarData($data)
{
    if (empty($data)) {
        return "";
    }

    return date("d/m/Y H:i", strtotime($data));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus chamados - Sistema de Chamados</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-sistema">

    <main class="container py-4 py-md-5">
        <section class="card shadow-sm border-0 rounded-4 mx-auto">
            <div class="card-body p-4 p-md-5">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <h1 class="titulo-formulario mb-2">Meus chamados</h1>
                        <p class="texto-sistema mb-0">
                            Chamados atribuídos a você para atendimento.
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
    <?php if ($ehAdmin): ?>
        <a href="dashboard-admin.php" class="btn btn-dark">
            Painel admin
        </a>

        <a href="dashboard.php" class="btn btn-outline-dark">
            Acesso comum
        </a>
    <?php else: ?>
        <a href="dashboard.php" class="btn btn-outline-dark">
            Voltar
        </a>
    <?php endif; ?>

    <a href="novo-chamado.php" class="btn btn-dark">
        + Novo
    </a>
</div>
                    </div>
                </div>

                <?php if (empty($chamados)): ?>
                    <div class="alert alert-light border">
                        Nenhum chamado atribuído a você até o momento.
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-4">
                        <?php foreach ($chamados as $chamado): ?>
                            <article class="border rounded-4 p-4 bg-white shadow-sm">

                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                    <div>
                                        <span class="badge rounded-pill text-bg-secondary mb-2">
                                            Atribuído a mim
                                        </span>

                                        <h2 class="h3 mb-2">
                                            #<?php echo str_pad($chamado["id_chamado"], 3, "0", STR_PAD_LEFT); ?>
                                            —
                                            <?php echo htmlspecialchars($chamado["titulo"]); ?>
                                        </h2>

                                        <p class="mb-0 text-muted">
                                            <?php echo nl2br(htmlspecialchars($chamado["descricao"])); ?>
                                        </p>
                                    </div>

                                    <span class="badge rounded-pill px-3 py-2 <?php echo classeStatus($chamado["status"]); ?>">
                                        <?php echo htmlspecialchars($chamado["status"]); ?>
                                    </span>
                                </div>

                                <div class="row gy-2 mb-3">
                                    <div class="col-md-3">
                                        <strong>Solicitante:</strong>
                                        <div><?php echo htmlspecialchars($chamado["solicitante"]); ?></div>
                                    </div>

                                    <div class="col-md-3">
                                        <strong>Departamento:</strong>
                                        <div><?php echo htmlspecialchars($chamado["departamento"]); ?></div>
                                    </div>

                                    <div class="col-md-3">
                                        <strong>Assunto:</strong>
                                        <div><?php echo htmlspecialchars($chamado["assunto"]); ?></div>
                                    </div>

                                    <div class="col-md-3">
                                        <strong>Resp.:</strong>
                                        <div><?php echo htmlspecialchars($chamado["responsavel"]); ?></div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div class="text-muted">
                                        <strong>Data:</strong>
                                        <?php echo formatarData($chamado["data_hora"]); ?>
                                    </div>

                                    <a 
                                        href="editar-chamado.php?id=<?php echo $chamado["id_chamado"]; ?>&origem=comum" 
                                        class="btn btn-outline-dark"
                                    >
                                        Ver chamado
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </main>

</body>
</html>