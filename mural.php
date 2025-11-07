<?php
include "conexao.php"; // Aqui já tem conexão MySQL + variáveis do Cloudinary ($cloud_name, $api_key, $api_secret)
// ==========================
// Inserir novo produto
// ==========================
if(isset($_POST['cadastra'])){
    // Pegando os dados do formulário (tratamento contra SQL Injection)
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $imagem_url = ""; // Inicializa a variável que vai guardar a URL da imagem
    // --------------------------
    // Upload da imagem para Cloudinary
    // --------------------------
    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0){
        $cfile = new CURLFile($_FILES['imagem']['tmp_name'], $_FILES['imagem']['type'], $_FILES['imagem']['name']);

        $timestamp = time();
        $string_to_sign = "timestamp=$timestamp$api_secret";
        $signature = sha1($string_to_sign);

        $data = [
            'file' => $cfile,
            'timestamp' => $timestamp,
            'api_key' => $api_key,
            'signature' => $signature
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/upload");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if($response === false){ die("Erro no cURL: " . curl_error($ch)); }
        curl_close($ch);

        $result = json_decode($response, true);
        if(isset($result['secure_url'])){
            $imagem_url = $result['secure_url'];
        } else {
            die("Erro no upload: " . print_r($result, true));
        }
    }

    // ==========================
    // Inserindo no banco de dados
    // ==========================
    if($imagem_url != ""){
        $sql = "INSERT INTO produtos (nome, descricao, preco, imagem_url) VALUES ('$nome', '$descricao', $preco, '$imagem_url')";
        mysqli_query($conexao, $sql) or die("Erro ao inserir: " . mysqli_error($conexao));
    }

    // ==========================
    // REDIRECIONAMENTO
    // ==========================
    header("Location: mural.php");
    exit;
}

/* 
==================================================
COMPARAÇÃO COM O CÓDIGO DE "ANTIGOxCLOUDINARY"
==================================================
- Tabela usada: recados (nome, email, mensagem)
- Campos do formulário: nome, email, msg
- Não tem upload de imagem, nem Cloudinary
- Inserção SQL: INSERT INTO recados (nome, email, mensagem)
- Validação adicional no front-end usando jQuery Validate
- Exibição: <ul> ao invés de <div>, apenas texto, sem preço ou imagem
- Código mais simples e voltado a mensagens
==================================================
*/
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8"/>
<title>Mural de Produtos</title>
<link rel="stylesheet" href="mura.css"/>

<!--
COMPARAÇÃO: No código ANTIGO/pedidos havia jQuery + jQuery Validate
<script src="scripts/jquery.js"></script>
<script src="scripts/jquery.validate.js"></script>
<script>
$(document).ready(function() {
    $("#mural").validate({
        rules: {
            nome: { required: true, minlength: 4 },
            email: { required: true, email: true },
            msg: { required: true, minlength: 10 }
        },
        messages: {
            nome: { required: "Digite o seu nome", minlength: "O nome deve ter no mínimo 4 caracteres" },
            email: { required: "Digite o seu e-mail", email: "Digite um e-mail válido" },
            msg: { required: "Digite sua mensagem", minlength: "A mensagem deve ter no mínimo 10 caracteres" }
        }
    });
});
</script>
-->
</head>
<body>
<div id="main">
    <div id="geral">
        <div id="header">
            <h1>Mural de Produtos</h1>
        </div>

        <!-- ==========================
        FORMULÁRIO
        ========================== -->
        <div id="formulario_mural">
            <form id="mural" method="post" enctype="multipart/form-data">
                <label>Nome do produto:</label>
                <input type="text" name="nome" required/>

                <label>Descrição:</label>
                <textarea name="descricao" required></textarea>

                <label>Preço:</label>
                <input type="number" step="0.01" name="preco" required/>

                <label>Imagem:</label>
                <input type="file" name="imagem" accept="image/*" required/>

                <input type="submit" value="Cadastrar Produto" name="cadastra" class="btn"/>
            </form>
        </div>

        <!-- ==========================
        LISTA DE PRODUTOS
        ========================== -->
        <div class="produtos-container">
        <?php
        $seleciona = mysqli_query($conexao, "SELECT * FROM produtos ORDER BY id DESC");
        while($res = mysqli_fetch_assoc($seleciona)){
            echo '<div class="produto">';
            echo '<p><strong>ID:</strong> ' . $res['id'] . '</p>';
            echo '<p><strong>Nome:</strong> ' . htmlspecialchars($res['nome']) . '</p>';
            echo '<p><strong>Preço:</strong> R$ ' . number_format($res['preco'], 2, ',', '.') . '</p>';
            echo '<p><strong>Descrição:</strong> ' . nl2br(htmlspecialchars($res['descricao'])) . '</p>';
            echo '<img src="' . htmlspecialchars($res['imagem_url']) . '" alt="' . htmlspecialchars($res['nome']) . '">';
            echo '</div>';
        }

        /*
        COMPARAÇÃO: Código antigo x cloudinary
        - Exibe em <ul class="recados"> cada recado
        - Mostra nome, email e mensagem
        - Não há imagem, preço ou descrição longa
        */

        ?>
        </div>

        <div id="footer">
            <p>Mural - Cloudinary & PHP</p>
            <!-- No código anterior, o footer estava vazio -->
        </div>
    </div>
</div>
</body>
</html>      