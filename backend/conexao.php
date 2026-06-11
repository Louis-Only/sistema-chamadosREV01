<?php
// =====================================================
// CONEXÃO COM O BANCO DE DADOS - SISTEMA DE CHAMADOS
// Banco: PostgreSQL
// Conexão: PDO
// =====================================================

$host = "localhost";
$port = "5432";
$dbname = "sistema_chamados";
$user = "postgres";
$password = "Sucodelaranja";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $erro) {
    die("Erro na conexão com o banco de dados: " . $erro->getMessage());
}