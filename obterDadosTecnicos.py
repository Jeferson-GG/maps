import requests
import mysql.connector
import warnings
import datetime

# Ignorar avisos de solicitações não seguras
warnings.filterwarnings("ignore", message="Unverified HTTPS request")

try:
    # Define os parâmetros de autenticação
    auth_url = "https://45.234.176.132:9910/Login"
    auth_data = {
        "usuario": "silas",
        "senha": "Silas@9451#",
        "identificador": "IZINGPRO"
    }

    # Faz a solicitação de autenticação
    auth_response = requests.post(auth_url, data=auth_data, verify=False)

    # Verifica se a autenticação foi bem-sucedida
    if auth_response.status_code == 200:
        auth_json = auth_response.json()
        id_usuario = auth_json.get("id_usuario")
        sessao = auth_json.get("sessao")

        # Conecta ao banco de dados MySQL
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",  # Substitua pela senha do seu banco de dados
            database="gps"
        )

        cursor = conn.cursor()

        # Seleciona todos os serviços da tabela "servicos"
        cursor.execute("SELECT id_servico, id_cliente FROM servicos")

        for row in cursor.fetchall():
            id_servico = row[0]
            id_cliente = row[1]

            # Define os parâmetros para obter informações técnicas do serviço específico
            info_url = "https://45.234.176.132:9910/InformacoesServico"  # Substitua pela URL correta
            info_data = {
                "idUsuario": id_usuario,
                "idCliente": id_cliente,
                "idServico": id_servico,
                "sessao": sessao,
                "identificador": "IZINGPRO"
            }

            # Faz a solicitação para obter informações técnicas
            info_response = requests.post(info_url, data=info_data, verify=False)

            if info_response.status_code == 200:
                info_json = info_response.json()

                if 'dados' in info_json and 'info' in info_json:
                    mac = info_json['dados'].get('mac')
                    id_ssid = info_json['dados'].get('idSSID')
                    ssid = info_json['dados'].get('SSID')
                    equipamento = info_json['dados'].get('equipamento')
                    rxpower = info_json['info'].get('rxpower')
                    txpower = info_json['info'].get('txpower')
                    temperature = info_json['info'].get('temperature')
                    voltage = info_json['info'].get('voltage')

                    # Adicionar campos extras
                    onuid = info_json['dados'].get('onuid')
                    pon = info_json['dados'].get('pon')
                    perfil = info_json['dados'].get('perfil')

                    # Conversão da data e hora
                    data_str = info_json['dados'].get('data')
                    if data_str:
                        data_datetime = datetime.datetime.strptime(data_str, '%Y-%m-%d %H:%M:%S-%z')
                    else:
                        data_datetime = None

                    # Campos adicionais
                    ip = info_json.get('ultimoacesso', {}).get('ip')  # Alteração para extrair 'ip' corretamente
                    calledstationid = info_json['dados'].get('calledstationid')
                    nasidentifier = info_json['dados'].get('nasidentifier')

                    # Inserir ou atualizar os campos no banco de dados
                    try:
                        cursor.execute("""
                            INSERT INTO servicos (id_servico, id_cliente, id_ssid, ssid, equipamento, rxpower, txpower, temperature, voltage, onuid, pon, perfil, data, ip, calledstationid, nasidentifier, mac)
                            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                            ON DUPLICATE KEY UPDATE
                            id_ssid = VALUES(id_ssid),
                            ssid = VALUES(ssid),
                            equipamento = VALUES(equipamento),
                            rxpower = VALUES(rxpower),
                            txpower = VALUES(txpower),
                            temperature = VALUES(temperature),
                            voltage = VALUES(voltage),
                            onuid = VALUES(onuid),
                            pon = VALUES(pon),
                            perfil = VALUES(perfil),
                            data = VALUES(data),
                            ip = VALUES(ip),
                            calledstationid = VALUES(calledstationid),
                            nasidentifier = VALUES(nasidentifier),
                            mac = VALUES(mac)
                        """, (id_servico, id_cliente, id_ssid, ssid, equipamento, rxpower, txpower, temperature, voltage, onuid, pon, perfil, data_datetime, ip, calledstationid, nasidentifier, mac))

                        # Certifique-se de cometer as alterações no banco de dados
                        conn.commit()
                    except Exception as insert_error:
                        print("Erro ao inserir/atualizar dados no banco:", str(insert_error))

except Exception as e:
    print("Ocorreu um erro durante a execução:", str(e))

finally:
    # Certifique-se de fechar a conexão e o cursor, mesmo se ocorrer uma exceção
    cursor.close()
    conn.close()
