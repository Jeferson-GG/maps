<?php
session_start();
//print_r($_SESSION);
if ((!isset($_SESSION['email']) == true) and (!isset($_SESSION['senha']) == true)) {
    unset($_SESSION['email']);
    unset($_SESSION['senha']);
    header('Location: login.html');
} ?>

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

// Função para formatar os resultados em JSON
function getClientesStatus($conn, $sql)
{
    $result = $conn->query($sql);
    $data = array('clientes_online' => 0, 'clientes_offline' => 0);

    if ($result) {
        $rowStatus = $result->fetch_assoc();
        $data['clientes_online'] = isset($rowStatus['clientes_online']) ? $rowStatus['clientes_online'] : 0;
        $data['clientes_offline'] = isset($rowStatus['clientes_offline']) ? $rowStatus['clientes_offline'] : 0;
    }

    return json_encode($data);
}

// Se for uma requisição AJAX, retornar os resultados em JSON
if (isset($_GET['ajax'])) {
    echo getClientesStatus($conn, $sqlClientesStatus);
    exit;
}

// Fechar a conexão com o banco de dados
$conn->close();
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mafredine Maps</title>
    <!-- Incluir o CSS do Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="//use.fontawesome.com/releases/v5.0.7/css/all.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">



</head>

<body>
    <div id="container-esquerdo">
        <div id="map"></div>


        <!-- Insira sua chave da API do Google Maps abaixo -->

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </div>
    <i id="toggleButton" class="fas fa-bars floating-menu"></i>
    <div id="container-direito">

        <ul>
            <li id="usuarioOn">
                <span class="online"></span>
                <span id="countOnline">
                    <?php echo $clientesOnline; ?>
                </span>
            </li>
            <li id="usuarioOff">
                <span class="offline"></span>
                <span id="countOffline">
                    <?php echo $clientesOffline; ?>
                </span>
            </li>
        </ul>
        <script>
            function atualizarValores() {
                // Fazer uma requisição AJAX para obter os valores do PHP
                $.ajax({
                    url: 'index.php?ajax=true',
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        // Atualizar os valores na página
                        $('#countOnline').text(data.clientes_online);
                        $('#countOffline').text(data.clientes_offline);
                    },
                    error: function (error) {
                        console.error('Erro ao obter dados: ', error);
                    }
                });
            }

            // Atualizar os valores a cada 5 segundos (5000 milissegundos)
            setInterval(atualizarValores, 5000);

            // Chamar a função inicialmente para exibir os valores imediatamente
            atualizarValores();
        </script>
        <div class="container-logo"><img class="logo" src="logo.png" alt=""></div>
        <nav class="navMenu">
            <a href="#sobre" onclick="activateLink(this); voltarAoContainer()">Mapa</a>
            <a href="#Dashboard" onclick="activateLink(this); navbar1()">Dashboard</a>
            <a href="sair.php" onclick="activateLink(this)">Sair</a>
            <div class="dot"></div>
        </nav>

        <hr>
        <script>
            var conteudoOriginal = document.getElementById('container-esquerdo').innerHTML;

            function navbar1() {
                // Cria um elemento iframe
                var iframe = document.createElement('iframe');

                // Define os atributos do iframe
                iframe.src = 'graficos.php';
                iframe.width = '100%';
                iframe.height = '100%';

                // Substitui a div original pelo iframe
                var containerEsquerdo = document.getElementById('container-esquerdo');
                containerEsquerdo.innerHTML = ''; // Limpa o conteúdo da div
                containerEsquerdo.appendChild(iframe);
            }
            function voltarAoContainer() {
                // Restaura o conteúdo original
                var containerEsquerdo = document.getElementById('container-esquerdo');
                containerEsquerdo.innerHTML = conteudoOriginal;

                // Se você estiver usando o Google Maps, reinicialize-o aqui
                // Exemplo fictício, ajuste conforme necessário
                initializeGoogleMaps();
            }

            // Exemplo fictício de inicialização do Google Maps
            function initializeGoogleMaps() {
                map = new google.maps.Map(document.getElementById('map'), {
                    center: { lat: -12.869433322038935, lng: -38.46949021366216 }, // Define o centro do mapa
                    zoom: 16, // Define o nível de zoom inicial
                    //center: latlng,
                    //mapTypeId: google.maps.MapTypeId.ROADMAP
                });


                // Função para acionar a função das dimensões mobile 

                function isMobileDevice() {
                    return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
                }

                var containerDiv = document.createElement('div'); // Cria a div contêiner
                containerDiv.className = 'map-search-container'; // Adiciona a classe à div

                //Função para ajustar a barra de filtro de endereço

                var input = document.createElement('input');
                input.type = 'text';
                input.id = 'pac-input';
                input.placeholder = 'Digite o local';

                input.style.marginTop = '10px';
                input.style.border = '1px solid transparent';
                input.style.borderRadius = '2px 0 0 2px';
                input.style.boxShadow = '0 2px 6px rgba(0, 0, 0, 0.3)';
                input.style.height = '29px';
                input.style.padding = '0 11px 0 13px';
                input.style.fontFamily = 'Roboto';
                input.style.fontSize = '15px';
                input.style.marginLeft = '12px';
                input.style.width = '250px';

                if (isMobileDevice()) {
                    input.style.width = '80%';
                    input.style.margin = '0%';
                }

                //Função para ajustar o botão da barra de filtro de endereço

                containerDiv.appendChild(input);

                var searchButton = document.createElement('button');
                searchButton.innerHTML = '<i class="material-icons">search</i>';
                searchButton.style.border = '1px solid transparent';
                searchButton.style.borderRadius = '0 2px 2px 0';
                searchButton.style.boxShadow = '0 2px 6px rgba(0, 0, 0, 0.3)';
                searchButton.style.height = '29px';
                searchButton.style.marginTop = '10px';
                searchButton.style.cursor = 'pointer';
                searchButton.style.verticalAlign = 'top';

                if (isMobileDevice()) {
                    searchButton.style.width = '20%';

                    searchButton.style.margin = '0%';
                }

                containerDiv.appendChild(searchButton);

                document.body.appendChild(containerDiv);

                // Adiciona um ouvinte de evento para o clique no botão
                searchButton.addEventListener('click', function () {
                    // Obtém o valor da barra de pesquisa
                    var searchValue = input.value;

                    // Faz a solicitação de geocodificação usando a API do Google Maps
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ address: searchValue, componentRestrictions: { country: 'BR' } }, function (results, status) {
                        if (status === 'OK' && results[0].geometry) {
                            // Ajusta o mapa para o local da geocodificação
                            map.fitBounds(results[0].geometry.viewport);
                        } else {
                            window.alert('Nenhum resultado encontrado para: ' + searchValue);
                        }
                    });
                });



                var inputContainer = document.createElement('div');


                inputContainer.id = 'container';
                inputContainer.style.marginTop = '10px';




                inputContainer.appendChild(input);
                inputContainer.appendChild(searchButton);

                //Função para ajustar a posição do container dos elementos da barra pesquisar endereços 

                map.controls[google.maps.ControlPosition.TOP_LEFT].push(inputContainer);
                var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

                if (isMobile) {
                    map.controls[google.maps.ControlPosition.LEFT].push(inputContainer);
                    inputContainer.id = 'container';
                    inputContainer.style.margin = '10px';
                    inputContainer.style.marginTop = '0%';


                }

                function removeFullscreenButtonOnMobile() {
                    var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

                    if (isMobile) {
                        var style = document.createElement('style');
                        style.type = 'text/css';
                        style.innerHTML = '.gm-fullscreen-control { display: none !important; }';
                        document.head.appendChild(style);
                    }
                }

                // Chama a função ao carregar a página
                window.onload = removeFullscreenButtonOnMobile;




                // Verifica se a API do Google Maps e Places está carregada corretamente
                if (google.maps && google.maps.places) {
                    // Cria um novo objeto de Autocomplete e liga-o à caixa de entrada de texto
                    var autocomplete = new google.maps.places.Autocomplete(input, {
                        types: ['geocode'],  // Permite todos os tipos de endereços geográficos
                        componentRestrictions: { country: 'BR' }  // Restringe os resultados ao Brasil
                    });

                    // Sugestões de pesquisa - Adiciona um ouvinte de evento para quando o usuário digitar na caixa de entrada
                    input.addEventListener('input', function () {
                        var inputText = input.value;

                        // Faz a solicitação de sugestões de pesquisa usando a API do Google Places
                        autocompleteService = new google.maps.places.AutocompleteService();
                        autocompleteService.getPlacePredictions({
                            input: inputText,
                            componentRestrictions: { country: 'BR' }
                        }, function (predictions, status) {
                            if (status === google.maps.places.PlacesServiceStatus.OK) {
                                // Atualiza a lista de sugestões com base nas previsões recebidas
                                updateSuggestions(predictions);
                            } else {
                                console.error('Erro ao obter previsões:', status);
                            }
                        });
                    });

                    // Adiciona uma função de sugestão de pesquisa ao seu código
                    function updateSuggestions(predictions) {
                        // Limpa as sugestões existentes
                        suggestionList.innerHTML = '';

                        // Cria e adiciona novas sugestões à lista
                        for (var i = 0; i < predictions.length; i++) {
                            var suggestion = document.createElement('div');
                            suggestion.textContent = predictions[i].description;
                            suggestion.addEventListener('click', function () {
                                input.value = this.textContent;
                                suggestionList.innerHTML = '';  // Limpa a lista de sugestões após clicar
                            });
                            suggestionList.appendChild(suggestion);
                        }

                        // Exibe a lista de sugestões
                        suggestionList.style.display = 'block';
                    }


                }



                //Funçoes da api do Google Maps Remover Locais e Icones Padrão

                // Adicione um ouvinte de eventos ao checkbox 'removerLocal'.
                document.getElementById('removerLocal').addEventListener('change', function () {
                    // Crie um estilo de mapa base vazio.
                    var mapStyles = [];

                    if (this.checked) {
                        // Se a caixa 'removerLocal' estiver marcada, adicione um estilo que oculta locais.
                        mapStyles.push({
                            featureType: 'poi',
                            elementType: 'labels',
                            stylers: [{ visibility: 'off' }]
                        });
                    }

                    // Verifique o estado do checkbox 'removerRodovia'.
                    var rodoviasCheckBox = document.getElementById('removerRodovia');
                    if (rodoviasCheckBox.checked) {
                        // Se a caixa 'removerRodovia' também estiver marcada, adicione estilos para ocultar rodovias.
                        mapStyles.push({
                            featureType: 'road',
                            elementType: 'geometry',
                            stylers: [{ visibility: 'off' }]
                        });
                        mapStyles.push({
                            featureType: 'road',
                            elementType: 'labels',
                            stylers: [{ visibility: 'off' }]
                        });
                    }

                    // Aplique os estilos ao mapa.
                    map.setOptions({ styles: mapStyles });
                });

                // Adicione um ouvinte de eventos ao checkbox 'removerRodovia'.
                document.getElementById('removerRodovia').addEventListener('change', function () {
                    // Crie um estilo de mapa base vazio.
                    var mapStyles = [];

                    if (this.checked) {
                        // Se a caixa 'removerRodovia' estiver marcada, adicione estilos para ocultar rodovias.
                        mapStyles.push({
                            featureType: 'road',
                            elementType: 'geometry',
                            stylers: [{ visibility: 'off' }]
                        });
                        mapStyles.push({
                            featureType: 'road',
                            elementType: 'labels',
                            stylers: [{ visibility: 'off' }]
                        });
                    }

                    // Verifique o estado do checkbox 'removerLocal'.
                    var localCheckBox = document.getElementById('removerLocal');
                    if (localCheckBox.checked) {
                        // Se a caixa 'removerLocal' também estiver marcada, adicione um estilo para ocultar locais.
                        mapStyles.push({
                            featureType: 'poi',
                            elementType: 'labels',
                            stylers: [{ visibility: 'off' }]
                        });
                    }

                    // Aplique os estilos ao mapa.
                    map.setOptions({ styles: mapStyles });
                });
            }
            function activateLink(link) {
                // Remove a classe 'active' de todos os links
                var links = document.querySelectorAll('.navMenu a');
                links.forEach(function (el) {
                    el.classList.remove('active');
                });

                // Adiciona a classe 'active' ao link clicado
                link.classList.add('active');
            }
        </script>

        <!-- Switches Online/Offline -->
        <div class="alternante">
            <div class="alternante-titulo">Status Conexão <span class="seta">▼</span></div>
            <div class="alternante-conteudo">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="onlineCheckbox"
                            onchange="filtrarDados()">
                        <label class="custom-control-label" for="onlineCheckbox">Online</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="offlineCheckbox"
                            onchange="filtrarDados()">
                        <label class="custom-control-label" for="offlineCheckbox">Offline</label>
                    </div>


                </div>
            </div>
        </div>


        <div class="alternante">
            <div class="alternante-titulo">Base<span class="seta">▼</span></div>
            <div class="alternante-conteudo">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <!-- Adicione checkboxes para filtrar por id_base -->
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="idBase1Checkbox"
                                onchange="filtrarDados()">
                            <label class="custom-control-label" for="idBase1Checkbox">CCR1009</label>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="idBase2Checkbox"
                                onchange="filtrarDados()">
                            <label class="custom-control-label" for="idBase2Checkbox">CCR1036+-</label>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="idBase3Checkbox"
                                onchange="filtrarDados()">
                            <label class="custom-control-label" for="idBase3Checkbox">RB1100</label>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="idBase4Checkbox"
                                onchange="filtrarDados()" checked>
                            <label class="custom-control-label" for="idBase4Checkbox">B-RAS</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alternante">
            <div class="alternante-titulo">Status Cliente <span class="seta">▼</span></div>
            <div class="alternante-conteudo">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="liberadosCheckbox" checked
                            onchange="filtrarDados()">
                        <label class="custom-control-label" for="liberadosCheckbox">Liberado</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="bloqueadosCheckbox" checked
                            onchange="filtrarDados()">
                        <label class="custom-control-label" for="bloqueadosCheckbox">Bloqueado</label>
                    </div>
                </div>
            </div>
        </div>


        <!-- Switches OLT -->
        <div class="alternante">
            <div class="alternante-titulo">Filtrar por OLT <span class="seta">▼</span></div>
            <div class="alternante-conteudo">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="ssid13Checkbox"
                            onchange="filtrarDados()" checked>
                        <label class="custom-control-label" for="ssid13Checkbox">OLT4-PART2</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="ssid12Checkbox"
                            onchange="filtrarDados()" checked>
                        <label class="custom-control-label" for="ssid12Checkbox">OLT6-LOJA 3-4 (99)</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="ssid21Checkbox"
                            onchange="filtrarDados()" checked>
                        <label class="custom-control-label" for="ssid21Checkbox">OLT7-LOJA (95)</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="ssid16Checkbox"
                            onchange="filtrarDados()" checked>
                        <label class="custom-control-label" for="ssid16Checkbox">OLT7-LOJA (97)</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="ssid17Checkbox"
                            onchange="filtrarDados()" checked>
                        <label class="custom-control-label" for="ssid17Checkbox">OLT8-STPCA</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="ignoreNullCheckbox"
                            onchange="filtrarDados()">
                        <label class="custom-control-label" for="ignoreNullCheckbox">OLT Null</label>
                    </div>
                </div>
            </div>
        </div>


        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
            integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
            integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>


        <div class="row">
            <select class="js-select2" id="ponSelect" name="ponSelect" multiple onchange="filtrarDados()">
                <option value="0/1" data-badge="">PON 0/1</option>
                <option value="0/2" data-badge="">PON 0/2</option>
                <option value="0/3" data-badge="">PON 0/3</option>
                <option value="0/4" data-badge="">PON 0/4</option>
                <option value="0/5" data-badge="">PON 0/5</option>
                <option value="0/6" data-badge="">PON 0/6</option>
                <option value="0/7" data-badge="">PON 0/7</option>
                <option value="0/8" data-badge="">PON 0/8</option>
                <option value="0/9" data-badge="">PON 0/9</option>
                <option value="0/10" data-badge="">PON 0/10</option>
                <option value="0/11" data-badge="">PON 0/11</option>
                <option value="0/12" data-badge="">PON 0/12</option>
                <option value="0/13" data-badge="">PON 0/13</option>
                <option value="0/14" data-badge="">PON 0/14</option>
                <option value="0/15" data-badge="">PON 0/15</option>
                <option value="0/16" data-badge="">PON 0/16</option>
            </select>
        </div>

        <script>

            $(".js-select2").select2({
                closeOnSelect: false,
                placeholder: "Porta PON",
                allowHtml: true,
                allowClear: true,
                tags: true // создает новые опции на лету
            });

        </script>

        <div class="alternante">
            <div class="alternante-titulo">Configurações do Mapa <span class="seta">▼</span></div>
            <div class="alternante-conteudo">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="removerLocal">
                        <label class="custom-control-label" for="removerLocal">Remover Locais</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="removerRodovia">
                        <label class="custom-control-label" for="removerRodovia">Remover Rodovias</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="alternante">
            <div class="alternante-titulo">Tema<span class="seta">▼</span></div>
            <div class="alternante-conteudo">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="toggleThemeButton"
                        onchange="alterarTextoLabel()"></input>
                    <label id="labelText" class="custom-control-label" for="toggleThemeButton">Claro</label>
                </div>
            </div>
        </div>
        <script>
            // Adiciona um ouvinte de eventos ao botão de alternância de tema
            document.getElementById('toggleThemeButton').addEventListener('click', function () {
                // Alternar a classe do corpo para ativar/desativar o tema escuro
                document.body.classList.toggle('dark-theme');
            });
            function alterarTextoLabel() {
                var checkbox = document.getElementById("toggleThemeButton");
                var label = document.querySelector('#labelText');

                if (checkbox.checked) {
                    label.textContent = 'Escuro';
                } else {
                    label.textContent = 'Claro';
                }
            }

            var icon = document.getElementById('toggleButton');
            var initialLeft = parseInt(window.getComputedStyle(icon).left, 10) || 0;

            function isMobileDevice() {
                return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
            }

            function toggleContainer() {
                var containerEsquerdo = document.getElementById('container-esquerdo');
                var containerDireito = document.getElementById('container-direito');
                var toggleButton = document.getElementById('toggleButton');

                if (containerDireito.style.display === 'none') {
                    // Se o contêiner está oculto, exibe
                    containerDireito.style.display = 'block';
                    // Atualiza o texto do botão para refletir a ação
                    toggleButton.innerText = '';
                    // Move o ícone para a posição inicial, apenas se não for dispositivo móvel
                    if (!isMobileDevice()) {
                        icon.style.left = initialLeft + 'px';
                    }
                } else {
                    // Se o contêiner está visível, oculta
                    containerDireito.style.display = 'none';
                    // Atualiza o texto do botão para refletir a ação
                    toggleButton.innerText = '';
                    // Move o ícone 35px para a esquerda, apenas se não for dispositivo móvel
                    if (!isMobileDevice()) {
                        var currentLeft = parseInt(window.getComputedStyle(icon).left, 10) || 0;
                        var newLeft = currentLeft - 35 + 'px';
                        icon.style.left = newLeft;
                    }
                }

                if (containerEsquerdo.classList.contains('fullscreen')) {
                    // Se está em tela cheia, remove a classe para retornar ao tamanho original
                    containerEsquerdo.classList.remove('fullscreen');
                } else {
                    // Se não está em tela cheia, adiciona a classe para tela cheia
                    containerEsquerdo.classList.add('fullscreen');
                }
            }




            // Adicionar um ouvinte de evento ao botão
            document.getElementById('toggleButton').addEventListener('click', toggleContainer);

            $(document).ready(function () {
                $('.alternante-titulo').click(function () {
                    // Fecha todos os outros alternantes
                    $('.alternante-titulo').not(this).parent().removeClass('aberto');
                    $('.alternante-titulo').not(this).find('.seta').removeClass('rotacionada');

                    // Abre ou fecha o alternante clicado
                    $(this).parent().toggleClass('aberto');
                    $(this).find('.seta').toggleClass('rotacionada');
                });
            });



        </script>




    </div>

    <div id="map"></div>

    <script>
        let map;
        let markers = [];

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: -12.869433322038935, lng: -38.46949021366216 }, // Define o centro do mapa
                zoom: 16, // Define o nível de zoom inicial
                //center: latlng,
                //mapTypeId: google.maps.MapTypeId.ROADMAP
            });


            // Função para acionar a função das dimensões mobile 

            function isMobileDevice() {
                return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
            }

            var containerDiv = document.createElement('div'); // Cria a div contêiner
            containerDiv.className = 'map-search-container'; // Adiciona a classe à div

            //Função para ajustar a barra de filtro de endereço

            var input = document.createElement('input');
            input.type = 'text';
            input.id = 'pac-input';
            input.placeholder = 'Digite o local';

            input.style.marginTop = '10px';
            input.style.border = '1px solid transparent';
            input.style.borderRadius = '2px 0 0 2px';
            input.style.boxShadow = '0 2px 6px rgba(0, 0, 0, 0.3)';
            input.style.height = '29px';
            input.style.padding = '0 11px 0 13px';
            input.style.fontFamily = 'Roboto';
            input.style.fontSize = '15px';
            input.style.marginLeft = '12px';
            input.style.width = '250px';

            if (isMobileDevice()) {
                input.style.width = '80%';
                input.style.margin = '0%';
            }

            //Função para ajustar o botão da barra de filtro de endereço

            containerDiv.appendChild(input);

            var searchButton = document.createElement('button');
            searchButton.innerHTML = '<i class="material-icons">search</i>';
            searchButton.style.border = '1px solid transparent';
            searchButton.style.borderRadius = '0 2px 2px 0';
            searchButton.style.boxShadow = '0 2px 6px rgba(0, 0, 0, 0.3)';
            searchButton.style.height = '29px';
            searchButton.style.marginTop = '10px';
            searchButton.style.cursor = 'pointer';
            searchButton.style.verticalAlign = 'top';

            if (isMobileDevice()) {
                searchButton.style.width = '20%';

                searchButton.style.margin = '0%';
            }

            containerDiv.appendChild(searchButton);

            document.body.appendChild(containerDiv);

            // Adiciona um ouvinte de evento para o clique no botão
            searchButton.addEventListener('click', function () {
                // Obtém o valor da barra de pesquisa
                var searchValue = input.value;

                // Faz a solicitação de geocodificação usando a API do Google Maps
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: searchValue, componentRestrictions: { country: 'BR' } }, function (results, status) {
                    if (status === 'OK' && results[0].geometry) {
                        // Ajusta o mapa para o local da geocodificação
                        map.fitBounds(results[0].geometry.viewport);
                    } else {
                        window.alert('Nenhum resultado encontrado para: ' + searchValue);
                    }
                });
            });



            var inputContainer = document.createElement('div');


            inputContainer.id = 'container';
            inputContainer.style.marginTop = '10px';




            inputContainer.appendChild(input);
            inputContainer.appendChild(searchButton);

            //Função para ajustar a posição do container dos elementos da barra pesquisar endereços 

            map.controls[google.maps.ControlPosition.TOP_LEFT].push(inputContainer);
            var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

            if (isMobile) {
                map.controls[google.maps.ControlPosition.LEFT].push(inputContainer);
                inputContainer.id = 'container';
                inputContainer.style.margin = '10px';
                inputContainer.style.marginTop = '0%';


            }

            function removeFullscreenButtonOnMobile() {
                var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

                if (isMobile) {
                    var style = document.createElement('style');
                    style.type = 'text/css';
                    style.innerHTML = '.gm-fullscreen-control { display: none !important; }';
                    document.head.appendChild(style);
                }
            }

            // Chama a função ao carregar a página
            window.onload = removeFullscreenButtonOnMobile;




            // Verifica se a API do Google Maps e Places está carregada corretamente
            if (google.maps && google.maps.places) {
                // Cria um novo objeto de Autocomplete e liga-o à caixa de entrada de texto
                var autocomplete = new google.maps.places.Autocomplete(input, {
                    types: ['geocode'],  // Permite todos os tipos de endereços geográficos
                    componentRestrictions: { country: 'BR' }  // Restringe os resultados ao Brasil
                });

                // Sugestões de pesquisa - Adiciona um ouvinte de evento para quando o usuário digitar na caixa de entrada
                input.addEventListener('input', function () {
                    var inputText = input.value;

                    // Faz a solicitação de sugestões de pesquisa usando a API do Google Places
                    autocompleteService = new google.maps.places.AutocompleteService();
                    autocompleteService.getPlacePredictions({
                        input: inputText,
                        componentRestrictions: { country: 'BR' }
                    }, function (predictions, status) {
                        if (status === google.maps.places.PlacesServiceStatus.OK) {
                            // Atualiza a lista de sugestões com base nas previsões recebidas
                            updateSuggestions(predictions);
                        } else {
                            console.error('Erro ao obter previsões:', status);
                        }
                    });
                });

                // Adiciona uma função de sugestão de pesquisa ao seu código
                function updateSuggestions(predictions) {
                    // Limpa as sugestões existentes
                    suggestionList.innerHTML = '';

                    // Cria e adiciona novas sugestões à lista
                    for (var i = 0; i < predictions.length; i++) {
                        var suggestion = document.createElement('div');
                        suggestion.textContent = predictions[i].description;
                        suggestion.addEventListener('click', function () {
                            input.value = this.textContent;
                            suggestionList.innerHTML = '';  // Limpa a lista de sugestões após clicar
                        });
                        suggestionList.appendChild(suggestion);
                    }

                    // Exibe a lista de sugestões
                    suggestionList.style.display = 'block';
                }


            }



            //Funçoes da api do Google Maps Remover Locais e Icones Padrão

            // Adicione um ouvinte de eventos ao checkbox 'removerLocal'.
            document.getElementById('removerLocal').addEventListener('change', function () {
                // Crie um estilo de mapa base vazio.
                var mapStyles = [];

                if (this.checked) {
                    // Se a caixa 'removerLocal' estiver marcada, adicione um estilo que oculta locais.
                    mapStyles.push({
                        featureType: 'poi',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }]
                    });
                }

                // Verifique o estado do checkbox 'removerRodovia'.
                var rodoviasCheckBox = document.getElementById('removerRodovia');
                if (rodoviasCheckBox.checked) {
                    // Se a caixa 'removerRodovia' também estiver marcada, adicione estilos para ocultar rodovias.
                    mapStyles.push({
                        featureType: 'road',
                        elementType: 'geometry',
                        stylers: [{ visibility: 'off' }]
                    });
                    mapStyles.push({
                        featureType: 'road',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }]
                    });
                }

                // Aplique os estilos ao mapa.
                map.setOptions({ styles: mapStyles });
            });

            // Adicione um ouvinte de eventos ao checkbox 'removerRodovia'.
            document.getElementById('removerRodovia').addEventListener('change', function () {
                // Crie um estilo de mapa base vazio.
                var mapStyles = [];

                if (this.checked) {
                    // Se a caixa 'removerRodovia' estiver marcada, adicione estilos para ocultar rodovias.
                    mapStyles.push({
                        featureType: 'road',
                        elementType: 'geometry',
                        stylers: [{ visibility: 'off' }]
                    });
                    mapStyles.push({
                        featureType: 'road',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }]
                    });
                }

                // Verifique o estado do checkbox 'removerLocal'.
                var localCheckBox = document.getElementById('removerLocal');
                if (localCheckBox.checked) {
                    // Se a caixa 'removerLocal' também estiver marcada, adicione um estilo para ocultar locais.
                    mapStyles.push({
                        featureType: 'poi',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }]
                    });
                }

                // Aplique os estilos ao mapa.
                map.setOptions({ styles: mapStyles });
            });

        }


    </script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBSBFsUOwyf5J2YruDxF_sqxzpK0Zx9ZZM&libraries=places&callback=initMap"
        async defer></script>

    <script>

        function adicionarMarcadores(dados) {
            // Limpa marcadores antigos
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            // Define os ícones para marcadores online e offline
            const iconOnline = 'marcadoron.png';
            const iconOffline = 'marcadoroff.png';

            // Crie um objeto InfoWindow
            const infoWindow = new google.maps.InfoWindow();

            // Adiciona marcadores com base nos dados
            dados.forEach(function (item) {
                // Escolhe o ícone com base no estado
                const icon = (item.conexao === 'online') ? iconOnline : iconOffline;

                // Verifica o status
                if ((item.status === 'bloqueado' && !document.getElementById('bloqueadosCheckbox').checked) ||
                    (item.status === 'liberado' && !document.getElementById('liberadosCheckbox').checked)) {
                    return; // Ignora marcadores que não atendem aos critérios de status selecionados
                }

                // Verifica o id_ssid
                const ssid13Checkbox = document.getElementById('ssid13Checkbox');
                const ssid12Checkbox = document.getElementById('ssid12Checkbox');
                const ssid21Checkbox = document.getElementById('ssid21Checkbox');
                const ssid16Checkbox = document.getElementById('ssid16Checkbox');
                const ssid17Checkbox = document.getElementById('ssid17Checkbox');
                const ignoreNullCheckbox = document.getElementById('ignoreNullCheckbox');

                if ((item.id_ssid === 13 && !ssid13Checkbox.checked) ||
                    (item.id_ssid === 12 && !ssid12Checkbox.checked) ||
                    (item.id_ssid === 21 && !ssid21Checkbox.checked) ||
                    (item.id_ssid === 16 && !ssid16Checkbox.checked) ||
                    (item.id_ssid === 17 && !ssid17Checkbox.checked) ||
                    (item.id_ssid === null && !ignoreNullCheckbox.checked)) {
                    return; // Ignora marcadores que não atendem aos critérios de id_ssid selecionados
                }

                // Verifica a PON
                const ponSelect = document.getElementById('ponSelect');
                const selectedPons = Array.from(ponSelect.selectedOptions).map(option => option.value);

                if (selectedPons.length > 0 && !selectedPons.includes(item.pon)) {
                    return; // Ignora marcadores que não atendem aos critérios de PON selecionados
                }

                // Verifica o id_base
                const idBase1Checkbox = document.getElementById('idBase1Checkbox');
                const idBase2Checkbox = document.getElementById('idBase2Checkbox');
                const idBase3Checkbox = document.getElementById('idBase3Checkbox');
                const idBase4Checkbox = document.getElementById('idBase4Checkbox');
                // Adicione mais IDs de id_base conforme necessário

                if ((item.id_base === 1 && !idBase1Checkbox.checked) ||
                    (item.id_base === 4 && !idBase2Checkbox.checked) ||
                    (item.id_base === 5 && !idBase3Checkbox.checked) ||
                    (item.id_base === 6 && !idBase4Checkbox.checked)) {
                    return; // Ignora marcadores que não atendem aos critérios de id_base selecionados
                }

                const marker = new google.maps.Marker({
                    position: { lat: parseFloat(item.latitude), lng: parseFloat(item.longitude) },
                    map: map,
                    title: item.nome,
                    icon: icon // Define o ícone do marcador
                });

                // Adiciona um ouvinte de clique ao marcador
                marker.addListener('click', function () {
                    // Crie o conteúdo personalizado do InfoWindow com informações do banco de dados
                    console.log(item);

                    const content = `
                        <strong>Base:</strong> ${item.base}<br>
                        <strong>Serial:</strong> ${item.serial}<br>
                        <strong>Usuário:</strong> ${item.usuario}<br>
                        <strong>Status:</strong> ${item.status}<br>
                        <strong>SSID:</strong> ${item.ssid}<br>
                        <strong>RXPower:</strong> ${item.rxpower}<br>
                        <strong>PON:</strong> ${item.pon}<br>
                        <strong>Verificação:</strong>${item.verificacao}<br>
                        Latitude: ${item.latitude}<br>
                        Longitude: ${item.longitude}
                        
                    `;
                    infoWindow.setContent(content);
                    infoWindow.open(map, marker); // Abre o InfoWindow associado a este marcador
                });

                markers.push(marker);
            });
        }

        function filtrarDados() {
            // Verifica o estado dos checkboxes
            const onlineCheckbox = document.getElementById('onlineCheckbox');
            const offlineCheckbox = document.getElementById('offlineCheckbox');
            const mostrarOnline = onlineCheckbox.checked;
            const mostrarOffline = offlineCheckbox.checked;

            // Verifica o estado dos checkboxes de status
            const bloqueadosCheckbox = document.getElementById('bloqueadosCheckbox');
            const liberadosCheckbox = document.getElementById('liberadosCheckbox');
            const mostrarBloqueados = bloqueadosCheckbox.checked;
            const mostrarLiberados = liberadosCheckbox.checked;

            // Verifica o estado dos checkboxes de id_ssid
            const ssid13Checkbox = document.getElementById('ssid13Checkbox');
            const ssid12Checkbox = document.getElementById('ssid12Checkbox');
            const ssid21Checkbox = document.getElementById('ssid21Checkbox');
            const ssid16Checkbox = document.getElementById('ssid16Checkbox');
            const ssid17Checkbox = document.getElementById('ssid17Checkbox');

            // Verifica o estado dos checkboxes de id_base
            const idBase1Checkbox = document.getElementById('idBase1Checkbox');
            const idBase2Checkbox = document.getElementById('idBase2Checkbox');
            const idBase3Checkbox = document.getElementById('idBase3Checkbox');
            const idBase4Checkbox = document.getElementById('idBase4Checkbox');
            // Adicione mais IDs de id_base conforme necessário


            // Limpa marcadores antigos
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            // Monta a string de consulta com base nos checkboxes marcados
            let consulta = "";

            if (mostrarOnline && mostrarOffline) {
                consulta = "ambos";
            } else if (mostrarOnline) {
                consulta = "online";
            } else if (mostrarOffline) {
                consulta = "offline";
            }

            // Faz a solicitação AJAX com a consulta
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    adicionarMarcadores(response);
                }
            };

            xhr.open("GET", `api.php?conexao=${consulta}`, true);
            xhr.send();

        }
    </script>
</body>

</html>