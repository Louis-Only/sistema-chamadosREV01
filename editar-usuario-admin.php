<?php
session_start();

require_once "backend/conexao.php";

// Esta tela é exclusiva para administrador logado.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

if (($_SESSION["usuario_tipo"] ?? "") !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$idUsuarioEditado = $_GET["id"] ?? "";

if (empty($idUsuarioEditado) || !is_numeric($idUsuarioEditado)) {
    header("Location: usuarios-admin.php");
    exit;
}

try {
    $sql = "
        SELECT 
            id_usuario,
            nome,
            email,
            telefone,
            cpf,
            departamento,
            cargo,
            tipo_usuario
        FROM usuarios
        WHERE id_usuario = :id_usuario
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id_usuario" => $idUsuarioEditado
    ]);

    $usuario = $stmt->fetch();

    if (!$usuario) {
        die("Usuário não encontrado.");
    }

} catch (PDOException $erro) {
    die("Erro ao buscar usuário: " . $erro->getMessage());
}

$editandoProprioUsuario = ((int) $usuario["id_usuario"] === (int) $_SESSION["usuario_id"]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Sistema de Chamados</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-sistema">

    <main class="container py-5">
        <section class="card shadow-sm border-0 rounded-4 mx-auto" style="max-width: 900px;">
            <div class="card-body p-4 p-md-5">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <h1 class="titulo-formulario mb-2">Editar usuário</h1>
                        <p class="texto-sistema mb-0">
                            Atualize os dados, cargo, departamento e tipo de acesso do usuário.
                        </p>
                    </div>

                    <a href="usuarios-admin.php" class="btn btn-outline-dark">
                        Voltar
                    </a>
                </div>

                <?php if ($editandoProprioUsuario): ?>
                    <div class="alert alert-warning">
                        Você está editando o próprio usuário administrador. Por segurança, o tipo de acesso será mantido como administrador.
                    </div>
                <?php endif; ?>

                <form id="formEditarUsuarioAdmin">
                    <input 
                        type="hidden" 
                        id="idUsuarioEditarAdmin" 
                        name="id_usuario"
                        value="<?php echo htmlspecialchars($usuario["id_usuario"]); ?>"
                    >

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nomeEditarAdmin" class="form-label">Nome completo</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="nomeEditarAdmin" 
                                name="nome"
                                value="<?php echo htmlspecialchars($usuario["nome"]); ?>"
                                required
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="emailEditarAdmin" class="form-label">E-mail</label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="emailEditarAdmin" 
                                name="email"
                                value="<?php echo htmlspecialchars($usuario["email"]); ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="telefoneEditarAdmin" class="form-label">Telefone</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="telefoneEditarAdmin" 
                                name="telefone"
                                value="<?php echo htmlspecialchars($usuario["telefone"]); ?>"
                                maxlength="15"
                                inputmode="numeric"
                                required
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="cpfEditarAdmin" class="form-label">CPF</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="cpfEditarAdmin" 
                                name="cpf"
                                value="<?php echo htmlspecialchars($usuario["cpf"]); ?>"
                                maxlength="14"
                                inputmode="numeric"
                                required
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="tipoUsuarioEditarAdmin" class="form-label">Tipo de acesso</label>
                            <select 
                                class="form-select" 
                                id="tipoUsuarioEditarAdmin" 
                                name="tipo_usuario"
                                <?php echo $editandoProprioUsuario ? "disabled" : ""; ?>
                                required
                            >
                                <option value="comum" <?php echo $usuario["tipo_usuario"] === "comum" ? "selected" : ""; ?>>
                                    Usuário comum
                                </option>
                                <option value="admin" <?php echo $usuario["tipo_usuario"] === "admin" ? "selected" : ""; ?>>
                                    Administrador
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="departamentoEditarAdmin" class="form-label">Departamento</label>
                            <select class="form-select" id="departamentoEditarAdmin" name="departamento" required>
                                <option value="">Selecione</option>
                                <option value="TI" <?php echo $usuario["departamento"] === "TI" ? "selected" : ""; ?>>TI</option>
                                <option value="RH" <?php echo $usuario["departamento"] === "RH" ? "selected" : ""; ?>>RH</option>
                                <option value="Financeiro" <?php echo $usuario["departamento"] === "Financeiro" ? "selected" : ""; ?>>Financeiro</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="cargoEditarAdmin" class="form-label">Cargo</label>
                            <select class="form-select" id="cargoEditarAdmin" name="cargo" required>
                                <option value="">Selecione</option>
                                <option value="Gerente" <?php echo $usuario["cargo"] === "Gerente" ? "selected" : ""; ?>>Gerente</option>
                                <option value="Coordenador" <?php echo $usuario["cargo"] === "Coordenador" ? "selected" : ""; ?>>Coordenador</option>
                                <option value="Analista" <?php echo $usuario["cargo"] === "Analista" ? "selected" : ""; ?>>Analista</option>
                                <option value="Técnico" <?php echo $usuario["cargo"] === "Técnico" ? "selected" : ""; ?>>Técnico</option>
                                <option value="Assistente" <?php echo $usuario["cargo"] === "Assistente" ? "selected" : ""; ?>>Assistente</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <p class="texto-sistema">
                        Preencha os campos abaixo somente se desejar alterar a senha do usuário.
                    </p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="senhaEditarAdmin" class="form-label">Nova senha</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="senhaEditarAdmin" 
                                name="senha"
                                placeholder="Nova senha"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="confirmarSenhaEditarAdmin" class="form-label">Confirmar nova senha</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="confirmarSenhaEditarAdmin" 
                                name="confirmarSenha"
                                placeholder="Confirme a nova senha"
                            >
                        </div>
                    </div>

                    <div id="mensagemEditarUsuarioAdmin" class="mb-3"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="usuarios-admin.php" class="btn btn-outline-dark btn-lg w-100">
                                Cancelar
                            </a>
                        </div>

                        <div class="col-md-6">
                            <button type="submit" class="btn btn-dark btn-lg w-100">
                                Salvar alterações
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