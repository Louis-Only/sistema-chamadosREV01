<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

$nome = $_SESSION["usuario_nome"] ?? "";
$email = $_SESSION["usuario_email"] ?? "";
$telefone = $_SESSION["usuario_telefone"] ?? "";
$cpf = $_SESSION["usuario_cpf"] ?? "";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Sistema de Chamados</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-sistema">

    <main class="container py-5">
        <section class="card card-cadastro shadow-sm mx-auto">
            <div class="card-body p-5">

                <div class="mb-4">
                    <h1 class="titulo-formulario">Editar perfil</h1>
                    <p class="texto-sistema mb-0">
                        Atualize seus dados pessoais cadastrados no sistema.
                    </p>
                </div>

                <form id="formPerfil">
                    <div class="mb-3">
                        <label for="nomePerfil" class="form-label">Nome completo</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="nomePerfil" 
                            name="nome"
                            value="<?php echo htmlspecialchars($nome); ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="emailPerfil" class="form-label">E-mail</label>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="emailPerfil" 
                            name="email"
                            value="<?php echo htmlspecialchars($email); ?>"
                            required
                        >
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefonePerfil" class="form-label">Telefone</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="telefonePerfil" 
                                name="telefone"
                                value="<?php echo htmlspecialchars($telefone); ?>"
                                maxlength="15"
                                inputmode="numeric"
                                required
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="cpfPerfil" class="form-label">CPF</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="cpfPerfil" 
                                name="cpf"
                                value="<?php echo htmlspecialchars($cpf); ?>"
                                maxlength="14"
                                inputmode="numeric"
                                required
                            >
                        </div>
                    </div>

                    <div id="mensagemPerfil" class="mb-3"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="dashboard.php" class="btn btn-outline-dark btn-lg w-100">
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

    <script src="assets/js/validacoes.js"></script>
    <script src="assets/js/ajax.js"></script>
</body>
</html>