<?php
session_start();

require_once "backend/conexao.php";

// Se não tiver usuário logado, volta para o login.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

$idUsuario = $_SESSION["usuario_id"];

$nome = $_SESSION["usuario_nome"] ?? "";
$email = $_SESSION["usuario_email"] ?? "";
$telefone = $_SESSION["usuario_telefone"] ?? "";
$cpf = $_SESSION["usuario_cpf"] ?? "";
$tipoUsuario = $_SESSION["usuario_tipo"] ?? "comum";

$ehAdmin = ($tipoUsuario === "admin");

try {
    // Nesta dashboard comum, contamos somente os chamados atribuídos ao usuário logado.
    // Ou seja, chamados em que ele é o responsável pelo atendimento.
    $sqlTotal = "
        SELECT COUNT(*) AS total
        FROM chamados
        WHERE id_responsavel = :id_usuario
    ";

    $stmtTotal = $pdo->prepare($sqlTotal);
    $stmtTotal->execute([
        ":id_usuario" => $idUsuario
    ]);

    $totalChamados = $stmtTotal->fetch()["total"] ?? 0;

    // Aqui contamos somente os chamados atribuídos ao usuário que ainda estão em aberto.
    $sqlAbertos = "
        SELECT COUNT(*) AS total
        FROM chamados
        WHERE id_responsavel = :id_usuario
        AND status = 'Em aberto'
    ";

    $stmtAbertos = $pdo->prepare($sqlAbertos);
    $stmtAbertos->execute([
        ":id_usuario" => $idUsuario
    ]);

    $chamadosAbertos = $stmtAbertos->fetch()["total"] ?? 0;

} catch (PDOException $erro) {
    die("Erro ao carregar indicadores da dashboard: " . $erro->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Usuário - Sistema de Chamados</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-sistema">

    <main class="container py-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <h1 class="titulo-formulario mb-2">
                            Olá, <?php echo htmlspecialchars($nome); ?>
                        </h1>
                        <p class="texto-sistema mb-0">
                            Bem-vindo à sua área pessoal.
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <?php if ($ehAdmin): ?>
                            <a href="dashboard-admin.php" class="btn btn-dark">
                                Voltar ao painel admin
                            </a>
                        <?php endif; ?>

                        <a href="backend/logout.php" class="btn btn-outline-dark">
                            Sair
                        </a>
                    </div>
                </div>

                <div class="card border-0 rounded-4 mb-4" style="background-color: #f5f3ed;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">Meus dados</h5>
                            <a href="perfil.php" class="link-sistema">Editar</a>
                        </div>

                        <div class="row gy-3">
                            <div class="col-md-6">
                                <strong>Nome</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($nome); ?></p>
                            </div>

                            <div class="col-md-6">
                                <strong>E-mail</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($email); ?></p>
                            </div>

                            <div class="col-md-6">
                                <strong>Telefone</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($telefone); ?></p>
                            </div>

                            <div class="col-md-6">
                                <strong>CPF</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($cpf); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 h-100" style="background-color: #f5f3ed;">
                            <div class="card-body">
                                <p class="mb-2 fw-bold">Chamados abertos</p>
                                <h2 class="mb-0">
                                    <?php echo $chamadosAbertos; ?>
                                </h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 h-100" style="background-color: #f5f3ed;">
                            <div class="card-body">
                                <p class="mb-2 fw-bold">Total de chamados</p>
                                <h2 class="mb-0">
                                    <?php echo $totalChamados; ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="novo-chamado.php" class="btn btn-dark w-100 btn-lg">
                            Abrir novo chamado
                        </a>
                    </div>

                    <div class="col-md-6">
                        <a href="chamados.php" class="btn btn-outline-dark w-100 btn-lg">
                            Ver meus chamados
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>