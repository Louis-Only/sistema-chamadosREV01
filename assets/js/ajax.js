/* =====================================================
   AJAX - SISTEMA DE CHAMADOS
   Aqui ficam as requisições que conversam com os arquivos PHP.
   ===================================================== */

function enviarCadastro(dadosCadastro) {
    const botaoCadastro = document.querySelector("#formCadastro button[type='submit']");

    if (botaoCadastro) {
        botaoCadastro.disabled = true;
        botaoCadastro.textContent = "Cadastrando...";
    }

    fetch("backend/cadastrar_usuario.php", {
        method: "POST",
        body: dadosCadastro
    })
    .then(async (response) => {
        return await response.json();
    })
    .then((resultado) => {
        if (resultado.sucesso) {
            exibirMensagem("mensagemCadastro", resultado.mensagem, "success");
            document.getElementById("formCadastro").reset();

            setTimeout(() => {
                window.location.href = "login.html";
            }, 1500);
        } else {
            exibirMensagem("mensagemCadastro", resultado.mensagem, "danger");
        }
    })
    .catch((erro) => {
        exibirMensagem("mensagemCadastro", "Erro ao enviar cadastro. Tente novamente.", "danger");
        console.error("Erro no cadastro:", erro);
    })
    .finally(() => {
        if (botaoCadastro) {
            botaoCadastro.disabled = false;
            botaoCadastro.textContent = "Cadastrar";
        }
    });
}

function enviarLogin(dadosLogin) {
    const botaoLogin = document.querySelector("#formLogin button[type='submit']");

    if (botaoLogin) {
        botaoLogin.disabled = true;
        botaoLogin.textContent = "Entrando...";
    }

    fetch("backend/login.php", {
        method: "POST",
        body: dadosLogin
    })
    .then(async (response) => {
        return await response.json();
    })
    .then((resultado) => {
        if (resultado.sucesso) {
            exibirMensagem("mensagemLogin", resultado.mensagem, "success");

            // Depois do login, direcionamos conforme o tipo do usuário.
            // Admin acessa o painel administrativo; usuário comum acessa a dashboard normal.
            setTimeout(() => {
                if (resultado.tipo_usuario === "admin") {
                    window.location.href = "dashboard-admin.php";
                } else {
                    window.location.href = "dashboard.php";
                }
            }, 1200);
        } else {
            exibirMensagem("mensagemLogin", resultado.mensagem, "danger");
        }
    })
    .catch((erro) => {
        exibirMensagem("mensagemLogin", "Erro ao realizar login. Tente novamente.", "danger");
        console.error("Erro no login:", erro);
    })
    .finally(() => {
        if (botaoLogin) {
            botaoLogin.disabled = false;
            botaoLogin.textContent = "Entrar";
        }
    });
}

function enviarAtualizacaoPerfil(dadosPerfil) {
    const botaoPerfil = document.querySelector("#formPerfil button[type='submit']");

    if (botaoPerfil) {
        botaoPerfil.disabled = true;
        botaoPerfil.textContent = "Salvando...";
    }

    fetch("backend/atualizar_usuario.php", {
        method: "POST",
        body: dadosPerfil
    })
    .then(async (response) => {
        return await response.json();
    })
    .then((resultado) => {
        if (resultado.sucesso) {
            exibirMensagem("mensagemPerfil", resultado.mensagem, "success");

            setTimeout(() => {
                window.location.href = "dashboard.php";
            }, 1200);
        } else {
            exibirMensagem("mensagemPerfil", resultado.mensagem, "danger");
        }
    })
    .catch((erro) => {
        exibirMensagem("mensagemPerfil", "Erro ao atualizar perfil. Tente novamente.", "danger");
        console.error("Erro ao atualizar perfil:", erro);
    })
    .finally(() => {
        if (botaoPerfil) {
            botaoPerfil.disabled = false;
            botaoPerfil.textContent = "Salvar alterações";
        }
    });
}

function enviarChamado(dadosChamado) {
    const botaoChamado = document.querySelector("#formChamado button[type='submit']");

    if (botaoChamado) {
        botaoChamado.disabled = true;
        botaoChamado.textContent = "Cadastrando...";
    }

    fetch("backend/cadastrar_chamado.php", {
        method: "POST",
        body: dadosChamado
    })
    .then(async (response) => {
        return await response.json();
    })
    .then((resultado) => {
        if (resultado.sucesso) {
            exibirMensagem("mensagemChamado", resultado.mensagem, "success");

            // O backend informa para onde voltar conforme o tipo do usuário.
            // Assim o admin não cai sem querer no painel comum.
            setTimeout(() => {
                window.location.href = resultado.redirect || "dashboard.php";
            }, 1200);
        } else {
            exibirMensagem("mensagemChamado", resultado.mensagem, "danger");
        }
    })
    .catch((erro) => {
        exibirMensagem("mensagemChamado", "Erro ao cadastrar chamado. Tente novamente.", "danger");
        console.error("Erro ao cadastrar chamado:", erro);
    })
    .finally(() => {
        if (botaoChamado) {
            botaoChamado.disabled = false;
            botaoChamado.textContent = "Cadastrar chamado";
        }
    });
}
function enviarAtualizacaoChamado(dadosChamado) {
    const botaoEditarChamado = document.querySelector("#formEditarChamado button[type='submit']");

    if (botaoEditarChamado) {
        botaoEditarChamado.disabled = true;
        botaoEditarChamado.textContent = "Salvando...";
    }

    fetch("backend/atualizar_chamado.php", {
        method: "POST",
        body: dadosChamado
    })
    .then(async (response) => {
        return await response.json();
    })
    .then((resultado) => {
        if (resultado.sucesso) {
            exibirMensagem("mensagemEditarChamado", resultado.mensagem, "success");

            // Depois de salvar, voltamos para a listagem de chamados.
            setTimeout(() => {
                window.location.href = "chamados.php";
            }, 1200);
        } else {
            exibirMensagem("mensagemEditarChamado", resultado.mensagem, "danger");
        }
    })
    .catch((erro) => {
        exibirMensagem("mensagemEditarChamado", "Erro ao atualizar chamado. Tente novamente.", "danger");
        console.error("Erro ao atualizar chamado:", erro);
    })
    .finally(() => {
        if (botaoEditarChamado) {
            botaoEditarChamado.disabled = false;
            botaoEditarChamado.textContent = "Salvar alterações";
        }
    });
}

function enviarCadastroAdmin(dadosUsuario) {
    const botaoCadastroAdmin = document.querySelector("#formCadastroAdmin button[type='submit']");

    if (botaoCadastroAdmin) {
        botaoCadastroAdmin.disabled = true;
        botaoCadastroAdmin.textContent = "Cadastrando...";
    }

    fetch("backend/cadastrar_usuario_admin.php", {
        method: "POST",
        body: dadosUsuario
    })
    .then(async (response) => {
        return await response.json();
    })
    .then((resultado) => {
        if (resultado.sucesso) {
            exibirMensagem("mensagemCadastroAdmin", resultado.mensagem, "success");

            // Recarrega a tela para atualizar a lista de usuários cadastrados.
            setTimeout(() => {
                window.location.href = "usuarios-admin.php";
            }, 1200);
        } else {
            exibirMensagem("mensagemCadastroAdmin", resultado.mensagem, "danger");
        }
    })
    .catch((erro) => {
        exibirMensagem("mensagemCadastroAdmin", "Erro ao cadastrar usuário. Tente novamente.", "danger");
        console.error("Erro ao cadastrar usuário pelo admin:", erro);
    })
    .finally(() => {
        if (botaoCadastroAdmin) {
            botaoCadastroAdmin.disabled = false;
            botaoCadastroAdmin.textContent = "Cadastrar usuário";
        }
    });
}

function enviarAtualizacaoUsuarioAdmin(dadosUsuario) {
    const botaoEditarUsuario = document.querySelector("#formEditarUsuarioAdmin button[type='submit']");

    if (botaoEditarUsuario) {
        botaoEditarUsuario.disabled = true;
        botaoEditarUsuario.textContent = "Salvando...";
    }

    fetch("backend/atualizar_usuario_admin.php", {
        method: "POST",
        body: dadosUsuario
    })
    .then(async (response) => {
        return await response.json();
    })
    .then((resultado) => {
        if (resultado.sucesso) {
            exibirMensagem("mensagemEditarUsuarioAdmin", resultado.mensagem, "success");

            setTimeout(() => {
                window.location.href = "usuarios-admin.php";
            }, 1200);
        } else {
            exibirMensagem("mensagemEditarUsuarioAdmin", resultado.mensagem, "danger");
        }
    })
    .catch((erro) => {
        exibirMensagem("mensagemEditarUsuarioAdmin", "Erro ao atualizar usuário. Tente novamente.", "danger");
        console.error("Erro ao atualizar usuário pelo admin:", erro);
    })
    .finally(() => {
        if (botaoEditarUsuario) {
            botaoEditarUsuario.disabled = false;
            botaoEditarUsuario.textContent = "Salvar alterações";
        }
    });
}
function enviarAcaoChamado(dadosAcao) {
    const botaoAcao = document.querySelector("#formAcaoChamado button[type='submit']");

    if (botaoAcao) {
        botaoAcao.disabled = true;
        botaoAcao.textContent = "Registrando...";
    }

    fetch("backend/registrar_acao_chamado.php", {
        method: "POST",
        body: dadosAcao
    })
    .then(async (response) => {
        return await response.json();
    })
    .then((resultado) => {
        if (resultado.sucesso) {
            exibirMensagem("mensagemAcaoChamado", resultado.mensagem, "success");

            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            exibirMensagem("mensagemAcaoChamado", resultado.mensagem, "danger");
        }
    })
    .catch((erro) => {
        exibirMensagem("mensagemAcaoChamado", "Erro ao registrar ação. Tente novamente.", "danger");
        console.error("Erro ao registrar ação:", erro);
    })
    .finally(() => {
        if (botaoAcao) {
            botaoAcao.disabled = false;
            botaoAcao.textContent = "Registrar ação";
        }
    });
}