import requests
import mysql.connector

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

    # Define os parâmetros para a próxima solicitação
    client_url = "https://45.234.176.132:9910/ObterClientesServicos"
    client_data = {
        "idUsuario": id_usuario,
        "sessao": sessao,
        "identificador": "IZINGPRO",
        "estadoServico": "01"
    }

    # Faz a solicitação para obter clientes e serviços
    client_response = requests.post(client_url, data=client_data, verify=False)

    # Verifica se a solicitação foi bem-sucedida
    if client_response.status_code == 200:
        client_json = client_response.json()

        # Conecta ao banco de dados MySQL
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="gps"
        )

        cursor = conn.cursor()

        # Itera sobre os dados obtidos e insere ou atualiza no banco de dados
        for service_data in client_json['dados']:
            id_servico = service_data['id_servico']

            # Verifica se o serviço possui latitude e longitude preenchidas e que não sejam vazias ou nulas
            if 'latitude' in service_data and 'longitude' in service_data and service_data['latitude'] and service_data['longitude']:
                # Define os campos a serem inseridos ou atualizados
                fields = {
                    'id_servico': id_servico,
                    'id_cliente': service_data['id_cliente'],
                    'id_base': service_data['id_base'],
                    'base': service_data['base'],
                    'id_porta': service_data['id_porta'],
                    'id_perfil': service_data['id_perfil'],
                    'serial': service_data['serial'],
                    'usuario': service_data['usuario'],
                    'senha': service_data['senha'],
                    'latitude': service_data['latitude'],
                    'longitude': service_data['longitude'],
                    'status': service_data['status']
                }

                # Query para inserir ou atualizar o serviço
                query = """
                    INSERT INTO servicos (id_servico, id_cliente, id_base, base, id_porta, id_perfil, serial, usuario, senha, latitude, longitude, status)
                    VALUES (%(id_servico)s, %(id_cliente)s, %(id_base)s, %(base)s, %(id_porta)s, %(id_perfil)s, %(serial)s, %(usuario)s, %(senha)s, %(latitude)s, %(longitude)s, %(status)s)
                    ON DUPLICATE KEY UPDATE
                    id_cliente = %(id_cliente)s,
                    id_base = %(id_base)s,
                    base = %(base)s,
                    id_porta = %(id_porta)s,
                    id_perfil = %(id_perfil)s,
                    serial = %(serial)s,
                    usuario = %(usuario)s,
                    senha = %(senha)s,
                    latitude = %(latitude)s,
                    longitude = %(longitude)s,
                    status = %(status)s
                """

                # Executa a query com os parâmetros
                cursor.execute(query, fields)

        # Commit das alterações e fechamento da conexão
        conn.commit()
        conn.close()
    else:
        print("Falha na solicitação de clientes e serviços. Código de status:", client_response.status_code)
else:
    print("Falha na autenticação. Código de status:", auth_response.status_code)
