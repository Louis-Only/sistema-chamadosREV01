# Instruções para executar o Sistema de Chamados

Este documento explica como baixar e executar o projeto **Sistema de Chamados** a partir do GitHub.

O sistema foi desenvolvido com:

- HTML5
- CSS
- Bootstrap
- JavaScript
- AJAX
- PHP
- PostgreSQL
- PDO

---

## 1. Baixar o projeto pelo GitHub

Acesse o repositório no GitHub:

```text
https://github.com/Louis-Only/sistema-chamadosREV01
```

Clique em:

```text
Code > Download ZIP
```

Depois extraia o arquivo `.zip` em uma pasta do computador.

Exemplo de caminho:

```text
C:\Users\SeuUsuario\Documents\sistema-chamadosREV01
```

Também é possível clonar o projeto pelo Git, caso o Git esteja instalado:

```bash
git clone https://github.com/Louis-Only/sistema-chamadosREV01.git
```

---

## 2. Programas necessários

Antes de executar o sistema, instale ou confirme que possui:

1. **XAMPP**, para usar o PHP localmente.
2. **PostgreSQL**, para o banco de dados.
3. **pgAdmin**, opcional, para executar o script SQL com interface gráfica.
4. Navegador web, como Google Chrome ou Microsoft Edge.

---

## 3. Criar o banco de dados

Abra o **pgAdmin** e crie um banco de dados com o nome:

```text
sistema_chamados
```

Depois selecione o banco criado e abra o **Query Tool**.

Execute o script SQL localizado em:

```text
database/script.sql
```

Esse script cria as tabelas necessárias:

```text
usuarios
chamados
chamados_acoes
```

Além disso, o script já cria usuários de teste para facilitar a validação do sistema.

---

## 4. Configurar a conexão com o banco

Abra o arquivo:

```text
backend/conexao.php
```

Confira se os dados de conexão estão de acordo com o PostgreSQL instalado na máquina.

Exemplo:

```php
$host = "localhost";
$porta = "5432";
$banco = "sistema_chamados";
$usuario = "postgres";
$senha = "sua_senha_aqui";
```

Altere o valor de `$senha` para a senha configurada no PostgreSQL local.

---

## 5. Verificar extensão do PostgreSQL no PHP

No terminal, execute:

```powershell
C:\xampp\php\php.exe -m | findstr pgsql
```

O retorno esperado é:

```text
pdo_pgsql
pgsql
```

Se esses módulos não aparecerem, é necessário habilitar as extensões `pgsql` e `pdo_pgsql` no arquivo `php.ini` do XAMPP.

---

## 6. Executar o servidor PHP local

Abra o terminal dentro da pasta raiz do projeto.

Exemplo:

```powershell
cd "C:\Users\SeuUsuario\Documents\sistema-chamadosREV01"
```

Depois execute:

```powershell
C:\xampp\php\php.exe -S localhost:8000
```

Se estiver correto, aparecerá uma mensagem parecida com:

```text
PHP 8.2.12 Development Server (http://localhost:8000) started
```

Mantenha esse terminal aberto enquanto estiver usando o sistema.

---

## 7. Acessar o sistema no navegador

Abra o navegador e acesse:

```text
http://localhost:8000/index.html
```

Para testar a conexão com o banco, acesse:

```text
http://localhost:8000/backend/testar_conexao.php
```

Se estiver tudo certo, será exibida a mensagem:

```text
Conexão realizada com sucesso!
```

---

## 8. Usuários de teste

O script SQL cria usuários de teste com senha padrão:

```text
123456
```

Usuário administrador:

```text
E-mail: luis.fernando.x@live.com.pt
Senha: 123456
Tipo: admin
```

Usuários comuns também são criados para os departamentos de TI, RH e Financeiro. Todos utilizam a senha padrão `123456`, salvo alteração posterior.

---

## 9. Principais telas do sistema

O sistema possui as seguintes telas principais:

```text
index.html
```

Tela inicial com opções de login e cadastro.

```text
login.html
```

Tela de autenticação do usuário.

```text
cadastro.html
```

Tela de cadastro de usuário.

```text
dashboard.php
```

Painel comum do usuário logado.

```text
dashboard-admin.php
```

Painel administrativo.

```text
novo-chamado.php
```

Tela de abertura de chamado.

```text
chamados.php
```

Tela de chamados atribuídos ao usuário logado.

```text
gerenciar-chamados.php
```

Tela administrativa para visualizar todos os chamados.

```text
editar-chamado.php
```

Tela de acompanhamento do chamado, registro de ações e atualização de status.

```text
usuarios-admin.php
```

Tela administrativa para cadastrar e listar usuários.

```text
editar-usuario-admin.php
```

Tela administrativa para editar dados, cargo, departamento e tipo de acesso dos usuários.

---

## 10. Observações importantes

O sistema **não roda diretamente pelo link do GitHub**, pois utiliza PHP e PostgreSQL.

O GitHub armazena o código-fonte, mas a execução deve ser feita localmente com:

```text
PHP + PostgreSQL
```

O GitHub Pages não executa PHP nem banco de dados.

---

## 11. Problemas comuns

### Erro 404 ao abrir a página

Verifique se o servidor PHP foi iniciado dentro da pasta correta do projeto.

Correto:

```powershell
cd "C:\Users\SeuUsuario\Documents\sistema-chamadosREV01"
C:\xampp\php\php.exe -S localhost:8000
```

Depois acesse:

```text
http://localhost:8000/index.html
```

---

### Erro de conexão com banco

Confira:

1. Se o PostgreSQL está em execução.
2. Se o banco `sistema_chamados` existe.
3. Se o script `database/script.sql` foi executado.
4. Se a senha em `backend/conexao.php` está correta.
5. Se as extensões `pdo_pgsql` e `pgsql` estão habilitadas no PHP.

---

### Porta 8000 ocupada

Se a porta 8000 estiver em uso, execute em outra porta:

```powershell
C:\xampp\php\php.exe -S localhost:8080
```

Depois acesse:

```text
http://localhost:8080/index.html
```

---

## 12. Estrutura resumida do projeto

```text
sistema-chamados/
│
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── ajax.js
│       └── validacoes.js
│
├── backend/
│   ├── conexao.php
│   ├── login.php
│   ├── logout.php
│   ├── cadastrar_usuario.php
│   ├── atualizar_usuario.php
│   ├── cadastrar_chamado.php
│   ├── atualizar_chamado.php
│   ├── registrar_acao_chamado.php
│   └── testar_conexao.php
│
├── database/
│   └── script.sql
│
├── docs/
│   └── Relatorio_Final_Sistema_Chamados_ABNT.docx
│
├── index.html
├── login.html
├── cadastro.html
├── dashboard.php
├── dashboard-admin.php
├── novo-chamado.php
├── chamados.php
├── gerenciar-chamados.php
├── editar-chamado.php
├── perfil.php
├── usuarios-admin.php
└── editar-usuario-admin.php
```

---

## 13. Resumo do funcionamento

O usuário pode se cadastrar, realizar login, visualizar seus dados, abrir chamados e acompanhar chamados atribuídos a ele.

O administrador possui acesso a um painel próprio, no qual pode visualizar indicadores gerais, gerenciar todos os chamados, cadastrar usuários, editar usuários e acessar a visão comum do sistema.

Cada chamado possui solicitante, responsável, departamento, assunto, status e data de criação. O atendimento é registrado em uma tabela separada de histórico, preservando a descrição original do chamado.
