<?php
include "conexao.php";

// Verificar se a conexão foi estabelecida
if (!$conexao) {
    die("Erro de conexão: " . mysqli_connect_error());
}

$erro = '';
$sucesso = '';

// Inserir novo pedido/recado
if(isset($_POST['cadastra'])){
    $nome  = mysqli_real_escape_string($conexao, trim($_POST['nome']));
    $email = mysqli_real_escape_string($conexao, trim($_POST['email']));
    $msg   = mysqli_real_escape_string($conexao, trim($_POST['msg']));
    
    // Validação no back-end
    if(empty($nome) || strlen($nome) < 4) {
        $erro = "Nome deve ter pelo menos 4 caracteres";
    } elseif(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido";
    } elseif(empty($msg) || strlen($msg) < 10) {
        $erro = "Mensagem deve ter pelo menos 10 caracteres";
    } else {
        $sql = "INSERT INTO recados (nome, email, mensagem) VALUES ('$nome', '$email', '$msg')";
        if(mysqli_query($conexao, $sql)) {
            $sucesso = "Mensagem publicada com sucesso!";
            // Limpar os campos do formulário
            $_POST = array();
        } else {
            $erro = "Erro ao inserir dados: " . mysqli_error($conexao);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8"/>
<title>Mural de pedidos</title>
<link rel="stylesheet" href="mural.css"/>
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
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
});
</script>
</head>
<body>
<div id="main">
<div id="geral">
<div id="header">
    <h1>Mural de pedidos</h1>
</div>

<div id="formulario_mural">
<?php if(!empty($erro)): ?>
    <div class="erro"><?php echo $erro; ?></div>
<?php endif; ?>

<?php if(!empty($sucesso)): ?>
    <div class="sucesso"><?php echo $sucesso; ?></div>
<?php endif; ?>

<form id="mural" method="post">
    <label>Nome:</label>
    <input type="text" name="nome" value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>"/><br/>
    <label>Email:</label>
    <input type="text" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"/><br/>
    <label>Mensagem:</label>
    <textarea name="msg"><?php echo isset($_POST['msg']) ? htmlspecialchars($_POST['msg']) : ''; ?></textarea><br/>
    <input type="submit" value="Publicar no Mural" name="cadastra" class="btn"/>
</form>
</div>

<?php
$seleciona = mysqli_query($conexao, "SELECT * FROM recados ORDER BY id DESC");
if($seleciona && mysqli_num_rows($seleciona) > 0) {
    while($res = mysqli_fetch_assoc($seleciona)){
        echo '<ul class="recados">';
        echo '<li><strong>ID:</strong> ' . $res['id'] . '</li>';
        echo '<li><strong>Nome:</strong> ' . htmlspecialchars($res['nome']) . '</li>';
        echo '<li><strong>Email:</strong> ' . htmlspecialchars($res['email']) . '</li>';
        echo '<li><strong>Mensagem:</strong> ' . nl2br(htmlspecialchars($res['mensagem'])) . '</li>';
        echo '</ul>';
    }
} else {
    echo '<p>Nenhum recado encontrado.</p>';
}

// Fechar a conexão
mysqli_close($conexao);
?>

<div id="footer">
</div>
</div>
</div>
</body>
</html>