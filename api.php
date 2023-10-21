<?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gps";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Verifique se um parâmetro "conexao" foi passado na URL
$conexao = $_GET['conexao'] ?? '';

// Verifique se um parâmetro "status" foi passado na URL
$status = $_GET['status'] ?? '';

// Verifique se um parâmetro "id_ssid" foi passado na URL
$id_ssid = isset($_GET['id_ssid']) ? $_GET['id_ssid'] : '';

// Verifique se um parâmetro "id_base" foi passado na URL
$id_base = $_GET['id_base'] ?? null; // Defina como null se não estiver presente

// Verifique se um parâmetro "pon" foi passado na URL
$pon = $_GET['pon'] ?? null; // Defina como null se não estiver presente

// Consulta SQL base
$sql = "SELECT * FROM servicos WHERE 1";

// Adicione condições para o filtro de conexão
if ($conexao === 'online') {
    $sql .= " AND conexao = 'online'";
} elseif ($conexao === 'offline') {
    $sql .= " AND conexao = 'offline'";
}

// Adicione condições para o filtro de status
if ($status === 'bloqueados') {
    $sql .= " AND status = 'bloqueado'";
} elseif ($status === 'liberados') {
    $sql .= " AND status = 'liberado'";
}

// Adicione a condição para filtrar pelo id_ssid, se fornecido e não for null
if ($id_ssid !== '' && $id_ssid !== null) {
    $sql .= " AND id_ssid = ?";
}

// Adicione a condição para filtrar pelo id_base, se fornecido
if ($id_base !== null) {
    $sql .= " AND id_base = ?";
}

// Adicione a condição para filtrar pelo pon, se fornecido
if ($pon !== null) {
    $sql .= " AND pon = ?";
}

// Use prepared statement para evitar SQL injection
$stmt = $conn->prepare($sql);

// Bind dos parâmetros de id_ssid, id_base e pon, se estiverem presentes
if ($id_ssid !== '' && $id_ssid !== null) {
    $stmt->bind_param("i", $id_ssid);
}

if ($id_base !== null) {
    $stmt->bind_param("i", $id_base);
}

if ($pon !== null) {
    $stmt->bind_param("s", $pon);
}

$stmt->execute();

$result = $stmt->get_result();

// Formate os dados como um array associativo
$dados = array();
while ($row = $result->fetch_assoc()) {
    $dados[] = $row;
}

// Feche a conexão com o banco de dados
$stmt->close();
$conn->close();

// Retorna os dados como JSON
header('Content-Type: application/json');
echo json_encode($dados);
?>
