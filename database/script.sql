-- =====================================================
-- SCRIPT SQL - SISTEMA DE CHAMADOS
-- Banco de dados: sistema_chamados
-- Tecnologias: HTML, Bootstrap, JavaScript, AJAX, PHP, PDO e PostgreSQL
-- =====================================================

-- Observação:
-- O banco sistema_chamados foi criado manualmente no pgAdmin.
-- Caso seja necessário criar pelo script, execute o comando abaixo
-- conectado ao banco padrão "postgres":

-- CREATE DATABASE sistema_chamados;

-- Após criar o banco, conecte-se ao banco sistema_chamados
-- e execute os comandos abaixo.

-- =====================================================
-- EXTENSÃO PARA GERAR SENHAS CRIPTOGRAFADAS EM USUÁRIOS DE TESTE
-- =====================================================

CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- =====================================================
-- REMOÇÃO DAS TABELAS, CASO JÁ EXISTAM
-- A ordem é importante por causa das chaves estrangeiras.
-- =====================================================

DROP TABLE IF EXISTS chamados_acoes;
DROP TABLE IF EXISTS chamados;
DROP TABLE IF EXISTS usuarios;

-- =====================================================
-- TABELA: usuarios
-- Armazena os usuários cadastrados no sistema.
-- Inclui dados pessoais, departamento, cargo e tipo de acesso.
-- =====================================================

CREATE TABLE usuarios (
    id_usuario SERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    departamento VARCHAR(50) NOT NULL,
    cargo VARCHAR(50) NOT NULL,
    tipo_usuario VARCHAR(20) NOT NULL DEFAULT 'comum',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_departamento_usuario
        CHECK (departamento IN ('TI', 'RH', 'Financeiro')),

    CONSTRAINT chk_cargo_usuario
        CHECK (cargo IN ('Assistente', 'Técnico', 'Analista', 'Coordenador', 'Gerente')),

    CONSTRAINT chk_tipo_usuario
        CHECK (tipo_usuario IN ('comum', 'admin'))
);

-- =====================================================
-- TABELA: chamados
-- Armazena os chamados abertos pelos usuários.
-- id_usuario representa o solicitante.
-- id_responsavel representa o usuário responsável pelo atendimento.
-- =====================================================

CREATE TABLE chamados (
    id_chamado SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL,
    id_responsavel INTEGER NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    departamento VARCHAR(50) NOT NULL,
    responsavel VARCHAR(150) NOT NULL,
    assunto VARCHAR(80) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Em aberto',
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_chamados_solicitante
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_chamados_responsavel
        FOREIGN KEY (id_responsavel)
        REFERENCES usuarios(id_usuario)
        ON DELETE RESTRICT,

    CONSTRAINT chk_departamento_chamado
        CHECK (departamento IN ('TI', 'RH', 'Financeiro')),

    CONSTRAINT chk_status_chamado
        CHECK (status IN ('Em aberto', 'Em análise', 'Resolvido')),

    CONSTRAINT chk_assunto_chamado
        CHECK (
            assunto IN (
                'Computador não liga',
                'Acesso ao sistema',
                'Problema de internet',
                'Instalação de software',
                'Atualização cadastral',
                'Solicitação de férias',
                'Dúvida sobre benefícios',
                'Documentos e declarações',
                'Reembolso',
                'Nota fiscal',
                'Pagamento',
                'Prestação de contas'
            )
        )
);

-- =====================================================
-- TABELA: chamados_acoes
-- Guarda o histórico de ações realizadas em cada chamado.
-- A descrição original do chamado não é alterada.
-- Cada ação registra o que foi feito e a mudança de status.
-- =====================================================

CREATE TABLE chamados_acoes (
    id_acao SERIAL PRIMARY KEY,
    id_chamado INTEGER NOT NULL,
    id_usuario INTEGER NOT NULL,
    descricao_acao TEXT NOT NULL,
    status_anterior VARCHAR(30) NOT NULL,
    status_novo VARCHAR(30) NOT NULL,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_acoes_chamado
        FOREIGN KEY (id_chamado)
        REFERENCES chamados(id_chamado)
        ON DELETE CASCADE,

    CONSTRAINT fk_acoes_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE RESTRICT,

    CONSTRAINT chk_status_anterior_acao
        CHECK (status_anterior IN ('Em aberto', 'Em análise', 'Resolvido')),

    CONSTRAINT chk_status_novo_acao
        CHECK (status_novo IN ('Em aberto', 'Em análise', 'Resolvido'))
);

-- =====================================================
-- ÍNDICES AUXILIARES
-- Melhoram consultas por solicitante, responsável, status e histórico.
-- =====================================================

CREATE INDEX idx_chamados_solicitante 
ON chamados(id_usuario);

CREATE INDEX idx_chamados_responsavel 
ON chamados(id_responsavel);

CREATE INDEX idx_chamados_status 
ON chamados(status);

CREATE INDEX idx_chamados_departamento 
ON chamados(departamento);

CREATE INDEX idx_acoes_chamado 
ON chamados_acoes(id_chamado);

CREATE INDEX idx_acoes_usuario 
ON chamados_acoes(id_usuario);

-- =====================================================
-- USUÁRIOS DE TESTE
-- Senha padrão para todos: 123456
-- Apenas Luis Fernando será administrador.
-- =====================================================

INSERT INTO usuarios (
    nome,
    email,
    telefone,
    cpf,
    senha,
    departamento,
    cargo,
    tipo_usuario
) VALUES
(
    'Luis Fernando Guimarães Marques',
    'luis.fernando.x@live.com.pt',
    '(31) 90000-0000',
    '100.000.000-00',
    crypt('123456', gen_salt('bf')),
    'TI',
    'Gerente',
    'admin'
),
(
    'Mariana Souza',
    'mariana.souza@gmail.com',
    '(31) 90000-0001',
    '100.000.000-01',
    crypt('123456', gen_salt('bf')),
    'TI',
    'Coordenador',
    'comum'
),
(
    'Pedro Henrique',
    'pedro.henrique@gmail.com',
    '(31) 90000-0002',
    '100.000.000-02',
    crypt('123456', gen_salt('bf')),
    'TI',
    'Analista',
    'comum'
),
(
    'Camila Rocha',
    'camila.rocha@gmail.com',
    '(31) 90000-0003',
    '100.000.000-03',
    crypt('123456', gen_salt('bf')),
    'TI',
    'Técnico',
    'comum'
),
(
    'Renata Oliveira',
    'renata.oliveira@gmail.com',
    '(31) 90000-0004',
    '100.000.000-04',
    crypt('123456', gen_salt('bf')),
    'RH',
    'Gerente',
    'comum'
),
(
    'Juliana Santos',
    'juliana.santos@gmail.com',
    '(31) 90000-0005',
    '100.000.000-05',
    crypt('123456', gen_salt('bf')),
    'RH',
    'Analista',
    'comum'
),
(
    'Bruno Lima',
    'bruno.lima@gmail.com',
    '(31) 90000-0006',
    '100.000.000-06',
    crypt('123456', gen_salt('bf')),
    'RH',
    'Assistente',
    'comum'
),
(
    'Fernanda Costa',
    'fernanda.costa@gmail.com',
    '(31) 90000-0007',
    '100.000.000-07',
    crypt('123456', gen_salt('bf')),
    'Financeiro',
    'Gerente',
    'comum'
),
(
    'Rafael Almeida',
    'rafael.almeida@gmail.com',
    '(31) 90000-0008',
    '100.000.000-08',
    crypt('123456', gen_salt('bf')),
    'Financeiro',
    'Coordenador',
    'comum'
),
(
    'Aline Martins',
    'aline.martins@gmail.com',
    '(31) 90000-0009',
    '100.000.000-09',
    crypt('123456', gen_salt('bf')),
    'Financeiro',
    'Analista',
    'comum'
),
(
    'Carlos Eduardo',
    'carlos.eduardo@gmail.com',
    '(31) 90000-0010',
    '100.000.000-10',
    crypt('123456', gen_salt('bf')),
    'Financeiro',
    'Assistente',
    'comum'
);

-- =====================================================
-- CONSULTAS PARA VALIDAÇÃO
-- Estas consultas podem ser executadas após o script
-- para conferir se tudo foi criado corretamente.
-- =====================================================

-- Verificar tabelas criadas no schema public
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'public'
ORDER BY table_name;

-- Consultar usuários cadastrados
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
    nome;

-- Consultar chamados cadastrados com solicitante e responsável
SELECT 
    c.id_chamado,
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
ORDER BY c.id_chamado;

-- Consultar histórico de ações dos chamados
SELECT 
    a.id_acao,
    a.id_chamado,
    c.titulo,
    u.nome AS usuario_acao,
    a.descricao_acao,
    a.status_anterior,
    a.status_novo,
    a.data_hora
FROM chamados_acoes a
INNER JOIN chamados c 
    ON c.id_chamado = a.id_chamado
INNER JOIN usuarios u 
    ON u.id_usuario = a.id_usuario
ORDER BY a.data_hora DESC;