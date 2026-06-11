<?php
session_start();

require_once "backend/conexao.php";

// Só entra aqui quem estiver logado.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

// Esta página é exclusiva para administradores.
if (($_SESSION["usuario_tipo"] ?? "") !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$idUsuario = $_SESSION["usuario_id"];

$nome = $_SESSION["usuario_nome"] ?? "";
$email = $_SESSION["usuario_email"] ?? "";
$telefone = $_SESSION["usuario_telefone"] ?? "";
$cpf = $_SESSION["usuario_cpf"] ?? "";
$departamento = $_SESSION["usuario_departamento"] ?? "";
$cargo = $_SESSION["usuario_cargo"] ?? "";
$tipoUsuario = $_SESSION["usuario_tipo"] ?? "";

try {
    // O admin visualiza indicadores gerais do sistema.
    $sqlTotal = "SELECT COUNT(*) AS total FROM chamados";
    $totalChamados = $pdo->query($sqlTotal)->fetch()["total"] ?? 0;

    $sqlAbertos = "SELECT COUNT(*) AS total FROM chamados WHERE status = 'Em aberto'";
    $chamadosAbertos = $pdo->query($sqlAbertos)->fetch()["total"] ?? 0;

    $sqlAnalise = "SELECT COUNT(*) AS total FROM chamados WHERE status = 'Em análise'";
    $chamadosAnalise = $pdo->query($sqlAnalise)->fetch()["total"] ?? 0;

    $sqlResolvidos = "SELECT COUNT(*) AS total FROM chamados WHERE status = 'Resolvido'";
    $chamadosResolvidos = $pdo->query($sqlResolvidos)->fetch()["total"] ?? 0;

    // Também mostramos quantos chamados estão atribuídos ao admin logado.
    $sqlAtribuidos = "
        SELECT COUNT(*) AS total
        FROM chamados
        WHERE id_responsavel = :id_responsavel
    ";

    $stmtAtribuidos = $pdo->prepare($sqlAtribuidos);
    $stmtAtribuidos->execute([
        ":id_responsavel" => $idUsuario
    ]);

    $chamadosAtribuidos = $stmtAtribuidos->fetch()["total"] ?? 0;

} catch (PDOException $erro) {
    die("Erro ao carregar painel administrativo: " . $erro->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Sistema de Chamados</title>

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
                            Painel administrativo
                        </h1>
                        <p class="texto-sistema mb-0">
                            Olá, <?php echo htmlspecialchars($nome); ?>. Você está acessando o sistema como administrador.
                        </p>
                    </div>

                    <a href="backend/logout.php" class="btn btn-outline-dark">
                        Sair
                    </a>
                </div>

                <div class="card border-0 rounded-4 mb-4" style="background-color: #f5f3ed;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">Dados do administrador</h5>
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

                            <div class="col-md-4">
                                <strong>Departamento</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($departamento); ?></p>
                            </div>

                            <div class="col-md-4">
                                <strong>Cargo</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($cargo); ?></p>
                            </div>

                            <div class="col-md-4">
                                <strong>Tipo de acesso</strong>
                                <p class="mb-0 text-uppercase fw-bold"><?php echo htmlspecialchars($tipoUsuario); ?></p>
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
                    <div class="col-md-3">
                        <div class="card border-0 rounded-4 h-100" style="background-color: #f5f3ed;">
                            <div class="card-body">
                                <p class="mb-2 fw-bold">Total</p>
                                <h2 class="mb-0"><?php echo $totalChamados; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 rounded-4 h-100" style="background-color: #fff3cd;">
                            <div class="card-body">
                                <p class="mb-2 fw-bold">Em aberto</p>
                                <h2 class="mb-0"><?php echo $chamadosAbertos; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 rounded-4 h-100" style="background-color: #dbeafe;">
                            <div class="card-body">
                                <p class="mb-2 fw-bold">Em análise</p>
                                <h2 class="mb-0"><?php echo $chamadosAnalise; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 rounded-4 h-100" style="background-color: #dcfce7;">
                            <div class="card-body">
                                <p class="mb-2 fw-bold">Resolvidos</p>
                                <h2 class="mb-0"><?php echo $chamadosResolvidos; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 rounded-4 mb-4" style="background-color: #f5f3ed;">
                    <div class="card-body">
                        <p class="mb-2 fw-bold">Chamados atribuídos a mim</p>
                        <h2 class="mb-0"><?php echo $chamadosAtribuidos; ?></h2>
                    </div>
                </div>

                <div class="row g-3">
    <div class="col-md-3">
        <a href="novo-chamado.php" class="btn btn-dark w-100 btn-lg">
            Abrir chamado
        </a>
    </div>

    <div class="col-md-3">
        <a href="gerenciar-chamados.php" class="btn btn-outline-dark w-100 btn-lg">
            Gerenciar chamados
        </a>
    </div>

    <div class="col-md-3">
        <a href="usuarios-admin.php" class="btn btn-outline-dark w-100 btn-lg">
            Administrar usuários
        </a>
    </div>

    <div class="col-md-3">
        <a href="dashboard.php" class="btn btn-outline-dark w-100 btn-lg">
            Ver painel comum
        </a>
    </div>
</div>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
