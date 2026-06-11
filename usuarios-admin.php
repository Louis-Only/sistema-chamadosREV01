<?php
session_start();

require_once "backend/conexao.php";

// Somente usuários logados podem acessar.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

// Esta tela é exclusiva para administradores.
if (($_SESSION["usuario_tipo"] ?? "") !== "admin") {
    header("Location: dashboard.php");
    exit;
}

try {
    // Lista os usuários já cadastrados para facilitar a conferência do admin.
    $sql = "
        SELECT 
            id_usuario,
            nome,
            email,
            telefone,
            cpf,
            departamento,
            cargo,
            tipo_usuario,
            data_cadastro
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

    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll();

} catch (PDOException $erro) {
    die("Erro ao listar usuários: " . $erro->getMessage());
}

function mascararCPF($cpf)
{
    if (empty($cpf)) {
        return "";
    }

    return "***.***.***-**";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração de Usuários - Sistema de Chamados</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-sistema">

    <main class="container py-5">
        <section class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <h1 class="titulo-formulario mb-2">Administração de usuários</h1>
                        <p class="texto-sistema mb-0">
                            Cadastre novos usuários e defina o tipo de acesso no sistema.
                        </p>
                    </div>

                    <a href="dashboard-admin.php" class="btn btn-outline-dark">
                        Voltar ao painel admin
                    </a>
                </div>

                <div class="card border-0 rounded-4 mb-4" style="background-color: #f5f3ed;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Cadastrar novo usuário</h5>

                        <form id="formCadastroAdmin">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nomeAdmin" class="form-label">Nome completo</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="nomeAdmin" 
                                        name="nome"
                                        placeholder="Nome do usuário"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="emailAdmin" class="form-label">E-mail</label>
                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        id="emailAdmin" 
                                        name="email"
                                        placeholder="usuario@gmail.com"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="telefoneAdmin" class="form-label">Telefone</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="telefoneAdmin" 
                                        name="telefone"
                                        placeholder="(31) 90000-0000"
                                        maxlength="15"
                                        inputmode="numeric"
                                        required
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="cpfAdmin" class="form-label">CPF</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="cpfAdmin" 
                                        name="cpf"
                                        placeholder="000.000.000-00"
                                        maxlength="14"
                                        inputmode="numeric"
                                        required
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="tipoUsuarioAdmin" class="form-label">Tipo de acesso</label>
                                    <select class="form-select" id="tipoUsuarioAdmin" name="tipo_usuario" required>
                                        <option value="comum" selected>Usuário comum</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="departamentoAdmin" class="form-label">Departamento</label>
                                    <select class="form-select" id="departamentoAdmin" name="departamento" required>
                                        <option value="">Selecione</option>
                                        <option value="TI">TI</option>
                                        <option value="RH">RH</option>
                                        <option value="Financeiro">Financeiro</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="cargoAdmin" class="form-label">Cargo</label>
                                    <select class="form-select" id="cargoAdmin" name="cargo" required>
                                        <option value="">Selecione</option>
                                        <option value="Gerente">Gerente</option>
                                        <option value="Coordenador">Coordenador</option>
                                        <option value="Analista">Analista</option>
                                        <option value="Técnico">Técnico</option>
                                        <option value="Assistente">Assistente</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="senhaAdmin" class="form-label">Senha inicial</label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="senhaAdmin" 
                                        name="senha"
                                        placeholder="Digite uma senha inicial"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirmarSenhaAdmin" class="form-label">Confirmar senha</label>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="confirmarSenhaAdmin" 
                                    name="confirmarSenha"
                                    placeholder="Confirme a senha inicial"
                                    required
                                >
                            </div>

                            <div id="mensagemCadastroAdmin" class="mb-3"></div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark btn-lg">
                                    Cadastrar usuário
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <h5 class="fw-bold mb-3">Usuários cadastrados</h5>

                <?php if (count($usuarios) === 0): ?>
                    <div class="alert alert-info">
                        Nenhum usuário cadastrado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Departamento</th>
                                    <th>Cargo</th>
                                    <th>Tipo</th>
                                    <th>CPF</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario["id_usuario"]); ?></td>
                                        <td><?php echo htmlspecialchars($usuario["nome"]); ?></td>
                                        <td><?php echo htmlspecialchars($usuario["email"]); ?></td>
                                        <td><?php echo htmlspecialchars($usuario["departamento"]); ?></td>
                                        <td><?php echo htmlspecialchars($usuario["cargo"]); ?></td>
                                        <td>
                                            <?php if ($usuario["tipo_usuario"] === "admin"): ?>
                                                <span class="badge bg-dark">ADMIN</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">COMUM</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo mascararCPF($usuario["cpf"]); ?></td>
                                        <td>
                                            <a 
                                                href="editar-usuario-admin.php?id=<?php echo $usuario["id_usuario"]; ?>" 
                                                class="btn btn-outline-dark btn-sm"
                                            >
                                                Editar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/ajax.js"></script>
    <script src="assets/js/validacoes.js"></script>
</body>
</html>