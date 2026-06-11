<?php
session_start();

require_once "backend/conexao.php";

// Esta tela só pode ser acessada por usuário logado.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

// O retorno respeita o tipo de usuário logado.
// Assim, se for admin, ao cancelar volta para o painel administrativo.
$tipoUsuario = $_SESSION["usuario_tipo"] ?? "comum";
$linkRetorno = ($tipoUsuario === "admin") ? "dashboard-admin.php" : "dashboard.php";

try {
    // Buscamos os usuários cadastrados para montar a lista de responsáveis.
    // A filtragem por departamento será feita no JavaScript, usando o data-departamento.
    $sqlUsuarios = "
        SELECT 
            id_usuario,
            nome,
            departamento,
            cargo
        FROM usuarios
        ORDER BY 
            departamento,
            CASE 
                WHEN cargo = 'Gerente' THEN 1
                WHEN cargo = 'Coordenador' THEN 2
                WHEN cargo = 'Analista' THEN 3
                WHEN cargo = 'Técnico' THEN 4
                WHEN cargo = 'Assistente' THEN 5
                ELSE 6
            END,
            nome
    ";

    $stmtUsuarios = $pdo->query($sqlUsuarios);
    $usuariosResponsaveis = $stmtUsuarios->fetchAll();

} catch (PDOException $erro) {
    die("Erro ao carregar usuários responsáveis: " . $erro->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir Chamado - Sistema de Chamados</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-sistema">

    <main class="container py-5">
        <section class="card shadow-sm border-0 rounded-4 mx-auto" style="max-width: 900px;">
            <div class="card-body p-4 p-md-5">

                <div class="mb-4">
                    <h1 class="titulo-formulario">Abrir chamado</h1>
                    <p class="texto-sistema mb-0">
                        Preencha os dados para registrar um novo chamado.
                    </p>
                </div>

                <form id="formChamado">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="titulo" 
                            name="titulo"
                            placeholder="Resumo do problema"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea 
                            class="form-control" 
                            id="descricao" 
                            name="descricao"
                            rows="4"
                            placeholder="Descreva o chamado em detalhes..."
                            required
                        ></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="departamento" class="form-label">Departamento</label>
                            <select class="form-select" id="departamento" name="departamento" required>
                                <option value="">Selecione</option>
                                <option value="TI">TI</option>
                                <option value="RH">RH</option>
                                <option value="Financeiro">Financeiro</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="assunto" class="form-label">Assunto</label>
                            <select class="form-select" id="assunto" name="assunto" required>
                                <option value="">Selecione primeiro o departamento</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="idResponsavel" class="form-label">Responsável</label>
                        <select 
                            class="form-select" 
                            id="idResponsavel" 
                            name="id_responsavel" 
                            required
                            disabled
                        >
                            <option value="">Selecione primeiro o departamento</option>

                            <?php foreach ($usuariosResponsaveis as $usuario): ?>
                                <option 
                                    value="<?php echo htmlspecialchars($usuario["id_usuario"]); ?>"
                                    data-departamento="<?php echo htmlspecialchars($usuario["departamento"]); ?>"
                                >
                                    <?php 
                                        echo htmlspecialchars(
                                            $usuario["nome"] . 
                                            " — " . 
                                            $usuario["cargo"]
                                        ); 
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Em aberto" selected>Em aberto</option>
                            <option value="Em análise">Em análise</option>
                            <option value="Resolvido">Resolvido</option>
                        </select>
                    </div>

                    <p class="texto-sistema mb-4">
                        Ao criar um novo chamado, o status inicial recomendado é “Em aberto”.
                    </p>

                    <div id="mensagemChamado" class="mb-3"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="<?php echo $linkRetorno; ?>" class="btn btn-outline-dark btn-lg w-100">
                                Cancelar
                            </a>
                        </div>

                        <div class="col-md-6">
                            <button type="submit" class="btn btn-dark btn-lg w-100">
                                Cadastrar chamado
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/ajax.js"></script>
    <script src="assets/js/validacoes.js"></script>
</body>
</html>