<?php
$host = '127.0.0.1';
$usuario = 'root';
$senha = '';
$banco = 'gps';

$conexao = new mysqli($host, $usuario, $senha, $banco);

function executarConsulta($conexao, $sql)
{
    $resultado = $conexao->query($sql);
    $dados = [];

    while ($row = $resultado->fetch_assoc()) {
        $dados[] = $row;
    }

    return $dados;
}

$sqlCoordenadas = "SELECT latitude, longitude FROM coordenadas";
$coordenadas = executarConsulta($conexao, $sqlCoordenadas);

$sqlEstatisticas = "SELECT clientes_online, clientes_offline FROM estatisticas";
$estatisticas = executarConsulta($conexao, $sqlEstatisticas);

$sqlHistoricoEstatisticas = "SELECT data, clientes_online, clientes_offline FROM historico_estatisticas";
$historicoEstatisticas = executarConsulta($conexao, $sqlHistoricoEstatisticas);

$sqlStatusServicos = "SELECT status, COUNT(*) as quantidade FROM servicos GROUP BY status";
$statusServicos = executarConsulta($conexao, $sqlStatusServicos);

$sqlDistribuicaoServicos = "SELECT base, COUNT(*) as quantidade FROM servicos GROUP BY base";
$distribuicaoServicos = executarConsulta($conexao, $sqlDistribuicaoServicos);

$sqlDistribuicaoPerfis = "SELECT perfil, COUNT(*) as quantidade FROM servicos GROUP BY perfil";
$distribuicaoPerfis = executarConsulta($conexao, $sqlDistribuicaoPerfis);

$sqlDistribuicaoEquipamentos = "SELECT equipamento, COUNT(*) as quantidade FROM servicos GROUP BY equipamento";
$distribuicaoEquipamentos = executarConsulta($conexao, $sqlDistribuicaoEquipamentos);

$sqlMediaRxPower = "SELECT perfil, AVG(rxpower) as media_rxpower FROM servicos GROUP BY perfil";
$mediaRxPower = executarConsulta($conexao, $sqlMediaRxPower);

$sqlDistribuicaoConexao = "SELECT conexao, COUNT(*) as quantidade FROM servicos GROUP BY conexao";
$distribuicaoConexao = executarConsulta($conexao, $sqlDistribuicaoConexao);

$sqlDistribuicaoSSIDs = "SELECT ssid, COUNT(*) as quantidade FROM servicos GROUP BY ssid";
$distribuicaoSSIDs = executarConsulta($conexao, $sqlDistribuicaoSSIDs);

$conexao->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Gráficos</title>
    <style>
        body {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            padding: 20px;
        }

        .card {
            width: 40%;
            /* Alterado para ocupar a largura total em dispositivos móveis */
            margin: 10px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        @media (max-width: 768px) {

            /* Quando a largura da tela for 768 pixels ou menos (dispositivos móveis) */
            .card {
                width: 100%;
                /* Ocupar 100% do espaço em dispositivos móveis */
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Gráfico de Estatísticas Online/Offline (Tipo: Pie) -->
    <div class="card">
        <h3>Estatísticas Online/Offline</h3>
        <canvas id="graficoEstatisticas"></canvas>
    </div>

    <div class="card">
        <h3>Status de Serviços</h3>
        <canvas id="graficoStatusServicos"></canvas>
    </div>
    <!-- Gráfico de Histórico de Estatísticas (Tipo: Line) -->
    <div class="card">
        <h3>Histórico de Estatísticas</h3>
        <canvas id="graficoHistoricoEstatisticas" width="800" height="400"></canvas>
    </div>

    <!-- Gráfico de Distribuição de Serviços por Base (Tipo: Bar) -->
    <div class="card">
        <h3>Distribuição de Serviços por Base</h3>
        <canvas id="graficoDistribuicaoServicos" width="400" height="200"></canvas>
    </div>

    <!-- Gráfico de Distribuição de Perfis de Serviços (Tipo: Bar) -->
    <div class="card">
        <h3>Distribuição de Perfis de Serviços</h3>
        <canvas id="graficoDistribuicaoPerfis" width="400" height="200"></canvas>
    </div>

    <!-- Gráfico de Distribuição de Equipamentos (Tipo: Bar) -->
    <div class="card">
        <h3>Distribuição de Equipamentos</h3>
        <canvas id="graficoDistribuicaoEquipamentos" width="400" height="200"></canvas>
    </div>

    <!-- Gráfico de Média de RxPower por Perfil (Tipo: Bar) -->
    <div class="card">
        <h3>Média de RxPower por Perfil</h3>
        <canvas id="graficoMediaRxPower" width="400" height="200"></canvas>
    </div>

    <!-- Gráfico de Distribuição de SSIDs (Tipo: Bar) -->
    <div class="card">
        <h3>Distribuição de SSIDs</h3>
        <canvas id="graficoDistribuicaoSSIDs" width="400" height="200"></canvas>
    </div>

    <!-- Gráfico de Coordenadas (Tipo: Scatter) -->
    <div class="card">
        <h3>Coordenadas</h3>
        <canvas id="graficoCoordenadas" width="400" height="400"></canvas>
    </div>


    <script>
        function criarGraficoDispersao(canvasId, dados) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Coordenadas',
                        data: dados,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        pointRadius: 5
                    }]
                }
            });
        }

        function criarGraficoPizza(canvasId, labels, dados) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: dados,
                        backgroundColor: ['green', 'red']
                    }]
                }
            });
        }

        function criarGraficoLinha(canvasId, labels, dados) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Clientes Online',
                        data: dados,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        x: [{
                            type: 'time',
                            time: {
                                unit: 'day',
                                tooltipFormat: 'll'
                            }
                        }],
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function criarGraficoDispersao(canvasId, dados) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Coordenadas',
                        data: dados,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        pointRadius: 5
                    }]
                }
            });
        }
        // Função para criar um gráfico de barras
        function criarGraficoBarras(canvasId, labels, dados) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Quantidade',
                        data: dados,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        criarGraficoDispersao('graficoCoordenadas', <?php echo json_encode($coordenadas); ?>);



        // Formatando dados para o gráfico de linha
        var historicoLabels = <?php echo json_encode(array_column($historicoEstatisticas, 'data')); ?>;
        var historicoClientesOnline = <?php echo json_encode(array_column($historicoEstatisticas, 'clientes_online')); ?>;
        criarGraficoBarras('graficoHistoricoEstatisticas', historicoLabels, historicoClientesOnline);
        criarGraficoBarras('graficoDistribuicaoPerfis', <?php echo json_encode(array_column($distribuicaoPerfis, 'perfil')); ?>, <?php echo json_encode(array_column($distribuicaoPerfis, 'quantidade')); ?>);
        criarGraficoDispersao('graficoDistribuicaoEquipamentos', <?php echo json_encode(array_column($distribuicaoEquipamentos, 'equipamento')); ?>, <?php echo json_encode(array_column($distribuicaoEquipamentos, 'quantidade')); ?>);
        criarGraficoLinha('graficoMediaRxPower', <?php echo json_encode(array_column($mediaRxPower, 'perfil')); ?>, <?php echo json_encode(array_column($mediaRxPower, 'media_rxpower')); ?>);
        criarGraficoBarras('graficoDistribuicaoSSIDs', <?php echo json_encode(array_column($distribuicaoSSIDs, 'ssid')); ?>, <?php echo json_encode(array_column($distribuicaoSSIDs, 'quantidade')); ?>);
        criarGraficoPizza('graficoStatusServicos', ['Online', 'Offline'], <?php echo json_encode([$statusServicos[0]['quantidade'], $statusServicos[1]['quantidade']]); ?>);
        criarGraficoBarras('graficoDistribuicaoServicos', <?php echo json_encode(array_column($distribuicaoServicos, 'base')); ?>, <?php echo json_encode(array_column($distribuicaoServicos, 'quantidade')); ?>);
        criarGraficoPizza('graficoEstatisticas', ['Online', 'Offline'], <?php echo json_encode([$estatisticas[0]['clientes_online'], $estatisticas[0]['clientes_offline']]); ?>);

        // Adicione os outros gráficos aqui chamando as funções apropriadas
    </script>
</body>

</html>