/* =====================================================
   VALIDAÇÕES FRONTEND - SISTEMA DE CHAMADOS
   Validações simples para evitar envio de dados incompletos.
   ===================================================== */

function validarEmail(email) {
    return email.includes("@") && email.includes(".");
}

function apenasNumeros(valor) {
    return valor.replace(/\D/g, "");
}

function validarCPF(cpf) {
    const cpfNumeros = apenasNumeros(cpf);
    return cpfNumeros.length === 11;
}

function exibirMensagem(elementoId, mensagem, tipo) {
    const elemento = document.getElementById(elementoId);

    if (!elemento) {
        return;
    }

    elemento.innerHTML = `
        <div class="alert alert-${tipo}" role="alert">
            ${mensagem}
        </div>
    `;
}

function aplicarMascaraCPF(campo) {
    let valor = apenasNumeros(campo.value).slice(0, 11);

    valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
    valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
    valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");

    campo.value = valor;
}

function aplicarMascaraTelefone(campo) {
    let valor = apenasNumeros(campo.value).slice(0, 11);

    valor = valor.replace(/^(\d{2})(\d)/g, "($1) $2");
    valor = valor.replace(/(\d{5})(\d{1,4})$/, "$1-$2");

    campo.value = valor;
}

function configurarMascaraCampo(idCampo, tipo) {
    const campo = document.getElementById(idCampo);

    if (!campo) {
        return;
    }

    // Quando a tela já abre com valor preenchido, a máscara é aplicada logo de cara.
    if (tipo === "cpf") {
        aplicarMascaraCPF(campo);
    }

    if (tipo === "telefone") {
        aplicarMascaraTelefone(campo);
    }

    // Enquanto o usuário digita, mantemos o formato visual correto.
    campo.addEventListener("input", function () {
        if (tipo === "cpf") {
            aplicarMascaraCPF(campo);
        }

        if (tipo === "telefone") {
            aplicarMascaraTelefone(campo);
        }
    });
}

// Campos da tela de cadastro
configurarMascaraCampo("cpf", "cpf");
configurarMascaraCampo("telefone", "telefone");

// Campos da tela de perfil
configurarMascaraCampo("cpfPerfil", "cpf");
configurarMascaraCampo("telefonePerfil", "telefone");

/* =====================================================
   ASSUNTOS POR DEPARTAMENTO
   Aqui deixamos os assuntos organizados conforme o departamento escolhido.
   ===================================================== */

const assuntosPorDepartamento = {
    "TI": [
        "Computador não liga",
        "Acesso ao sistema",
        "Problema de internet",
        "Instalação de software"
    ],
    "RH": [
        "Atualização cadastral",
        "Solicitação de férias",
        "Dúvida sobre benefícios",
        "Documentos e declarações"
    ],
    "Financeiro": [
        "Reembolso",
        "Nota fiscal",
        "Pagamento",
        "Prestação de contas"
    ]
};

function preencherAssuntos(campoDepartamento, campoAssunto) {
    const departamentoSelecionado = campoDepartamento.value;
    const assuntoAtual = campoAssunto.dataset.assuntoAtual || "";

    campoAssunto.innerHTML = "";

    const optionInicial = document.createElement("option");
    optionInicial.value = "";
    optionInicial.textContent = "Selecione";
    campoAssunto.appendChild(optionInicial);

    if (!departamentoSelecionado || !assuntosPorDepartamento[departamentoSelecionado]) {
        optionInicial.textContent = "Selecione primeiro o departamento";
        return;
    }

    assuntosPorDepartamento[departamentoSelecionado].forEach(function (assunto) {
        const option = document.createElement("option");
        option.value = assunto;
        option.textContent = assunto;

        if (assunto === assuntoAtual) {
            option.selected = true;
        }

        campoAssunto.appendChild(option);
    });
}

function carregarAssuntosPorDepartamento() {
    const campoDepartamento = document.getElementById("departamento");
    const campoAssunto = document.getElementById("assunto");

    if (!campoDepartamento || !campoAssunto) {
        return;
    }

    // Quando a página abre, já carregamos os assuntos do departamento atual.
    // Isso ajuda tanto na abertura quanto na futura edição de chamados.
    preencherAssuntos(campoDepartamento, campoAssunto);

    campoDepartamento.addEventListener("change", function () {
        campoAssunto.dataset.assuntoAtual = "";
        preencherAssuntos(campoDepartamento, campoAssunto);
    });
}

carregarAssuntosPorDepartamento();

/* =====================================================
   RESPONSÁVEIS POR DEPARTAMENTO
   O responsável exibido deve pertencer ao departamento escolhido.
   ===================================================== */

function configurarResponsaveisPorDepartamento() {
    const campoDepartamento = document.getElementById("departamento");
    const campoResponsavel = document.getElementById("idResponsavel");

    if (!campoDepartamento || !campoResponsavel) {
        return;
    }

    function filtrarResponsaveis() {
        const departamentoSelecionado = campoDepartamento.value;
        const responsavelAtual = campoResponsavel.dataset.responsavelAtual || "";
        const opcoesResponsavel = campoResponsavel.querySelectorAll("option[data-departamento]");

        campoResponsavel.value = "";

        if (!departamentoSelecionado) {
            campoResponsavel.disabled = true;
            campoResponsavel.options[0].textContent = "Selecione primeiro o departamento";

            opcoesResponsavel.forEach(function (option) {
                option.hidden = true;
                option.disabled = true;
            });

            return;
        }

        campoResponsavel.disabled = false;
        campoResponsavel.options[0].textContent = "Selecione o responsável pelo atendimento";

        opcoesResponsavel.forEach(function (option) {
            const departamentoUsuario = option.dataset.departamento;

            if (departamentoUsuario === departamentoSelecionado) {
                option.hidden = false;
                option.disabled = false;

                // Esta parte já deixa preparado para a tela de edição depois.
                if (responsavelAtual && option.value === responsavelAtual) {
                    option.selected = true;
                }
            } else {
                option.hidden = true;
                option.disabled = true;
            }
        });
    }

    campoDepartamento.addEventListener("change", function () {
        campoResponsavel.dataset.responsavelAtual = "";
        filtrarResponsaveis();
    });

    // Aplica o filtro assim que a tela abre.
    filtrarResponsaveis();
}

configurarResponsaveisPorDepartamento();

/* =====================================================
   CADASTRO DE USUÁRIO
   ===================================================== */

const formCadastro = document.getElementById("formCadastro");

if (formCadastro) {
    formCadastro.addEventListener("submit", function (event) {
        event.preventDefault();

        const nome = document.getElementById("nome").value.trim();
        const email = document.getElementById("emailCadastro").value.trim();
        const telefone = document.getElementById("telefone").value.trim();
        const cpf = document.getElementById("cpf").value.trim();
        const senha = document.getElementById("senhaCadastro").value.trim();
        const confirmarSenha = document.getElementById("confirmarSenha").value.trim();

        if (!nome || !email || !telefone || !cpf || !senha || !confirmarSenha) {
            exibirMensagem("mensagemCadastro", "Preencha todos os campos obrigatórios.", "danger");
            return;
        }

        if (!validarEmail(email)) {
            exibirMensagem("mensagemCadastro", "Informe um e-mail válido.", "danger");
            return;
        }

        if (!validarCPF(cpf)) {
            exibirMensagem("mensagemCadastro", "Informe um CPF válido com 11 números.", "danger");
            return;
        }

        if (senha !== confirmarSenha) {
            exibirMensagem("mensagemCadastro", "As senhas não conferem.", "danger");
            return;
        }

        const dadosCadastro = new FormData();
        dadosCadastro.append("nome", nome);
        dadosCadastro.append("email", email);
        dadosCadastro.append("telefone", telefone);
        dadosCadastro.append("cpf", cpf);
        dadosCadastro.append("senha", senha);
        dadosCadastro.append("confirmarSenha", confirmarSenha);

        if (typeof enviarCadastro === "function") {
            enviarCadastro(dadosCadastro);
        }
    });
}

/* =====================================================
   LOGIN
   ===================================================== */

const formLogin = document.getElementById("formLogin");

if (formLogin) {
    formLogin.addEventListener("submit", function (event) {
        event.preventDefault();

        const email = document.getElementById("email").value.trim();
        const senha = document.getElementById("senha").value.trim();

        if (!email || !senha) {
            exibirMensagem("mensagemLogin", "Preencha o e-mail e a senha.", "danger");
            return;
        }

        if (!validarEmail(email)) {
            exibirMensagem("mensagemLogin", "Informe um e-mail válido.", "danger");
            return;
        }

        const dadosLogin = new FormData();
        dadosLogin.append("email", email);
        dadosLogin.append("senha", senha);

        if (typeof enviarLogin === "function") {
            enviarLogin(dadosLogin);
        }
    });
}

/* =====================================================
   EDIÇÃO DE PERFIL
   ===================================================== */

const formPerfil = document.getElementById("formPerfil");

if (formPerfil) {
    formPerfil.addEventListener("submit", function (event) {
        event.preventDefault();

        const nome = document.getElementById("nomePerfil").value.trim();
        const email = document.getElementById("emailPerfil").value.trim();
        const telefone = document.getElementById("telefonePerfil").value.trim();
        const cpf = document.getElementById("cpfPerfil").value.trim();

        if (!nome || !email || !telefone || !cpf) {
            exibirMensagem("mensagemPerfil", "Preencha todos os campos obrigatórios.", "danger");
            return;
        }

        if (!validarEmail(email)) {
            exibirMensagem("mensagemPerfil", "Informe um e-mail válido.", "danger");
            return;
        }

        if (!validarCPF(cpf)) {
            exibirMensagem("mensagemPerfil", "Informe um CPF válido com 11 números.", "danger");
            return;
        }

        const dadosPerfil = new FormData();
        dadosPerfil.append("nome", nome);
        dadosPerfil.append("email", email);
        dadosPerfil.append("telefone", telefone);
        dadosPerfil.append("cpf", cpf);

        if (typeof enviarAtualizacaoPerfil === "function") {
            enviarAtualizacaoPerfil(dadosPerfil);
        }
    });
}

/* =====================================================
   CADASTRO DE CHAMADO
   ===================================================== */

const formChamado = document.getElementById("formChamado");

if (formChamado) {
    formChamado.addEventListener("submit", function (event) {
        event.preventDefault();

        const titulo = document.getElementById("titulo").value.trim();
        const descricao = document.getElementById("descricao").value.trim();
        const departamento = document.getElementById("departamento").value.trim();
        const assunto = document.getElementById("assunto").value.trim();
        const idResponsavel = document.getElementById("idResponsavel").value.trim();
        const status = document.getElementById("status").value.trim();

        if (!titulo || !descricao || !departamento || !assunto || !idResponsavel || !status) {
            exibirMensagem("mensagemChamado", "Preencha todos os campos obrigatórios.", "danger");
            return;
        }

        const dadosChamado = new FormData();
        dadosChamado.append("titulo", titulo);
        dadosChamado.append("descricao", descricao);
        dadosChamado.append("departamento", departamento);
        dadosChamado.append("assunto", assunto);
        dadosChamado.append("id_responsavel", idResponsavel);
        dadosChamado.append("status", status);

        if (typeof enviarChamado === "function") {
            enviarChamado(dadosChamado);
        }
    });
}

/* =====================================================
   EDIÇÃO DE CHAMADO
   Mantemos compatível com a tela atual.
   Depois vamos ajustar a edição para também usar id_responsavel.
   ===================================================== */

const formEditarChamado = document.getElementById("formEditarChamado");

if (formEditarChamado) {
    formEditarChamado.addEventListener("submit", function (event) {
        event.preventDefault();

        const idChamado = document.getElementById("idChamado").value.trim();
        const titulo = document.getElementById("titulo").value.trim();
        const descricao = document.getElementById("descricao").value.trim();
        const departamento = document.getElementById("departamento").value.trim();
        const assunto = document.getElementById("assunto").value.trim();
        const status = document.getElementById("status").value.trim();

        let idResponsavel = "";
        const campoIdResponsavel = document.getElementById("idResponsavel");

        if (campoIdResponsavel) {
            idResponsavel = campoIdResponsavel.value.trim();
        }

        // Compatibilidade temporária com a versão antiga da tela de edição.
        let responsavel = "";
        const campoResponsavelTexto = document.getElementById("responsavel");

        if (campoResponsavelTexto) {
            responsavel = campoResponsavelTexto.value.trim();
        }

        if (!idChamado || !titulo || !descricao || !departamento || !assunto || !status) {
            exibirMensagem("mensagemEditarChamado", "Preencha todos os campos obrigatórios.", "danger");
            return;
        }

        if (campoIdResponsavel && !idResponsavel) {
            exibirMensagem("mensagemEditarChamado", "Selecione o responsável pelo chamado.", "danger");
            return;
        }

        if (!campoIdResponsavel && !responsavel) {
            exibirMensagem("mensagemEditarChamado", "Informe o responsável pelo chamado.", "danger");
            return;
        }

        const dadosChamado = new FormData();
        dadosChamado.append("id_chamado", idChamado);
        dadosChamado.append("titulo", titulo);
        dadosChamado.append("descricao", descricao);
        dadosChamado.append("departamento", departamento);
        dadosChamado.append("assunto", assunto);
        dadosChamado.append("status", status);

        if (campoIdResponsavel) {
            dadosChamado.append("id_responsavel", idResponsavel);
        } else {
            dadosChamado.append("responsavel", responsavel);
        }

        if (typeof enviarAtualizacaoChamado === "function") {
            enviarAtualizacaoChamado(dadosChamado);
        }
    });
}

/* =====================================================
   CADASTRO DE USUÁRIO PELO ADMIN
   ===================================================== */

configurarMascaraCampo("cpfAdmin", "cpf");
configurarMascaraCampo("telefoneAdmin", "telefone");

const formCadastroAdmin = document.getElementById("formCadastroAdmin");

if (formCadastroAdmin) {
    formCadastroAdmin.addEventListener("submit", function (event) {
        event.preventDefault();

        const nome = document.getElementById("nomeAdmin").value.trim();
        const email = document.getElementById("emailAdmin").value.trim();
        const telefone = document.getElementById("telefoneAdmin").value.trim();
        const cpf = document.getElementById("cpfAdmin").value.trim();
        const departamento = document.getElementById("departamentoAdmin").value.trim();
        const cargo = document.getElementById("cargoAdmin").value.trim();
        const tipoUsuario = document.getElementById("tipoUsuarioAdmin").value.trim();
        const senha = document.getElementById("senhaAdmin").value.trim();
        const confirmarSenha = document.getElementById("confirmarSenhaAdmin").value.trim();

        if (
            !nome ||
            !email ||
            !telefone ||
            !cpf ||
            !departamento ||
            !cargo ||
            !tipoUsuario ||
            !senha ||
            !confirmarSenha
        ) {
            exibirMensagem("mensagemCadastroAdmin", "Preencha todos os campos obrigatórios.", "danger");
            return;
        }

        if (!validarEmail(email)) {
            exibirMensagem("mensagemCadastroAdmin", "Informe um e-mail válido.", "danger");
            return;
        }

        if (!validarCPF(cpf)) {
            exibirMensagem("mensagemCadastroAdmin", "Informe um CPF válido com 11 números.", "danger");
            return;
        }

        if (senha !== confirmarSenha) {
            exibirMensagem("mensagemCadastroAdmin", "As senhas não conferem.", "danger");
            return;
        }

        const dadosUsuario = new FormData();
        dadosUsuario.append("nome", nome);
        dadosUsuario.append("email", email);
        dadosUsuario.append("telefone", telefone);
        dadosUsuario.append("cpf", cpf);
        dadosUsuario.append("departamento", departamento);
        dadosUsuario.append("cargo", cargo);
        dadosUsuario.append("tipo_usuario", tipoUsuario);
        dadosUsuario.append("senha", senha);
        dadosUsuario.append("confirmarSenha", confirmarSenha);

        if (typeof enviarCadastroAdmin === "function") {
            enviarCadastroAdmin(dadosUsuario);
        }
    });
}

/* =====================================================
   EDIÇÃO DE USUÁRIO PELO ADMIN
   ===================================================== */

configurarMascaraCampo("cpfEditarAdmin", "cpf");
configurarMascaraCampo("telefoneEditarAdmin", "telefone");

const formEditarUsuarioAdmin = document.getElementById("formEditarUsuarioAdmin");

if (formEditarUsuarioAdmin) {
    formEditarUsuarioAdmin.addEventListener("submit", function (event) {
        event.preventDefault();

        const idUsuario = document.getElementById("idUsuarioEditarAdmin").value.trim();
        const nome = document.getElementById("nomeEditarAdmin").value.trim();
        const email = document.getElementById("emailEditarAdmin").value.trim();
        const telefone = document.getElementById("telefoneEditarAdmin").value.trim();
        const cpf = document.getElementById("cpfEditarAdmin").value.trim();
        const departamento = document.getElementById("departamentoEditarAdmin").value.trim();
        const cargo = document.getElementById("cargoEditarAdmin").value.trim();
        const tipoUsuario = document.getElementById("tipoUsuarioEditarAdmin").value.trim();
        const senha = document.getElementById("senhaEditarAdmin").value.trim();
        const confirmarSenha = document.getElementById("confirmarSenhaEditarAdmin").value.trim();

        if (!idUsuario || !nome || !email || !telefone || !cpf || !departamento || !cargo || !tipoUsuario) {
            exibirMensagem("mensagemEditarUsuarioAdmin", "Preencha todos os campos obrigatórios.", "danger");
            return;
        }

        if (!validarEmail(email)) {
            exibirMensagem("mensagemEditarUsuarioAdmin", "Informe um e-mail válido.", "danger");
            return;
        }

        if (!validarCPF(cpf)) {
            exibirMensagem("mensagemEditarUsuarioAdmin", "Informe um CPF válido com 11 números.", "danger");
            return;
        }

        if ((senha || confirmarSenha) && senha !== confirmarSenha) {
            exibirMensagem("mensagemEditarUsuarioAdmin", "As senhas não conferem.", "danger");
            return;
        }

        const dadosUsuario = new FormData();
        dadosUsuario.append("id_usuario", idUsuario);
        dadosUsuario.append("nome", nome);
        dadosUsuario.append("email", email);
        dadosUsuario.append("telefone", telefone);
        dadosUsuario.append("cpf", cpf);
        dadosUsuario.append("departamento", departamento);
        dadosUsuario.append("cargo", cargo);
        dadosUsuario.append("tipo_usuario", tipoUsuario);
        dadosUsuario.append("senha", senha);
        dadosUsuario.append("confirmarSenha", confirmarSenha);

        if (typeof enviarAtualizacaoUsuarioAdmin === "function") {
            enviarAtualizacaoUsuarioAdmin(dadosUsuario);
        }
    });
}
/* =====================================================
   REGISTRO DE AÇÃO DO CHAMADO
   ===================================================== */

const formAcaoChamado = document.getElementById("formAcaoChamado");

if (formAcaoChamado) {
    formAcaoChamado.addEventListener("submit", function (event) {
        event.preventDefault();

        const idChamado = document.getElementById("idChamadoAcao").value.trim();
        const descricaoAcao = document.getElementById("descricaoAcao").value.trim();
        const statusNovo = document.getElementById("statusNovoAcao").value.trim();

        if (!idChamado || !descricaoAcao || !statusNovo) {
            exibirMensagem("mensagemAcaoChamado", "Preencha todos os campos obrigatórios.", "danger");
            return;
        }

        const dadosAcao = new FormData();
        dadosAcao.append("id_chamado", idChamado);
        dadosAcao.append("descricao_acao", descricaoAcao);
        dadosAcao.append("status_novo", statusNovo);

        if (typeof enviarAcaoChamado === "function") {
            enviarAcaoChamado(dadosAcao);
        }
    });
}