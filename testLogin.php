<?php
session_start();

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    if (!empty($email) && !empty($senha)) {
        include_once('config.php');

        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        $result = $conexao->query($sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $usuario = mysqli_fetch_assoc($result);

            if ($senha === $usuario['senha']) {
                $_SESSION['email'] = $email;
                $_SESSION['senha'] = $senha;

                // Redirecionar para a página painel.html
                header('Location:./index.php');
                exit; // Certifique-se de que a execução do script seja encerrada após o redirecionamento.
            } else {
                echo '<script>
                    var alertMessage = "Senha incorreta. Por favor, tente novamente.";
                    if (window.confirm(alertMessage)) {
                        window.location.href = "login.html";
                    } else {
                        window.location.href = "login.html"; // Redirecionamento mesmo se o usuário cancelar
                    }
                </script>';
            }
        } else {
            echo '<script>
                var alertMessage = "Usuário não encontrado. Por favor, verifique suas credenciais.";
                if (window.confirm(alertMessage)) {
                    window.location.href = "login.html";
                } else {
                    window.location.href = "login.html"; // Redirecionamento mesmo se o usuário cancelar
                }
            </script>';
        }
    } else {
        echo '<script>
            var alertMessage = "Por favor, preencha todos os campos.";
            if (window.confirm(alertMessage)) {
                window.location.href = "login.html";
            } else {
                window.location.href = "login.html"; // Redirecionamento mesmo se o usuário cancelar
            }
        </script>';
    }
} else {
    header('Location: login.html');
}
?>