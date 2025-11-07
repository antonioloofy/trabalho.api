<?php
// Configurações do banco - LOJA_PRODUTOS
$host    = "localhost";
$usuario = "root";
$senha   = "";
$banco   = "loja_produtos";  // NOVO BANCO

// Conexão MySQLi
$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    die("Erro ao conectar: " . mysqli_connect_error());
}

// Suportar acentos e Ç
mysqli_set_charset($conexao, "utf8");

// ==========================================
// CONFIGURAÇÕES DO CLOUDINARY
// ==========================================

$cloud_name = "dz15z02dk";
$api_key    = "621412922471187";
$api_secret = "xkODjqP7wIDrKAO1IVJACdbepB4";

?>