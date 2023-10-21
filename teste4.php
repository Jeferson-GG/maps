<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gps";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Consulta SQL para obter o número de clientes online e offline
$sqlClientesStatus = "SELECT SUM(clientes_online) AS clientes_online, SUM(clientes_offline) AS clientes_offline FROM estatisticas";
$resultClientesStatus = $conn->query($sqlClientesStatus);

// Inicializar as variáveis para armazenar os valores
$clientesOnline = 0;
$clientesOffline = 0;

// Obter os valores da consulta
if ($resultClientesStatus) {
    $rowStatus = $resultClientesStatus->fetch_assoc();
    $clientesOnline = isset($rowStatus['clientes_online']) ? $rowStatus['clientes_online'] : 0;
    $clientesOffline = isset($rowStatus['clientes_offline']) ? $rowStatus['clientes_offline'] : 0;
}

// Fechar a conexão com o banco de dados
$conn->close();
?>

<!-- Seu HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados</title>
</head>
<body>
    <ul>
        <li id="usuarioOn">
            <span class="online"></span> Online
            <span id="countOnline">
                <?php echo $clientesOnline; ?>
            </span>
        </li>
        <li id="usuarioOff">
            <span class="offline"></span> Offline
            <span id="countOffline">
                <?php echo $clientesOffline; ?>
            </span>
        </li>
    </ul>
</body>
</html>
