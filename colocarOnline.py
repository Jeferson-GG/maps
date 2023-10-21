import requests
import mysql.connector
import urllib3

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# Configuração do servidor e endpoint
base_url = "https://45.234.176.132:9910/"
login_endpoint = "Login"
historico_endpoint = "ObterClientesServicosAcessos"

# Dados de autenticação e identificação
usuario = "silas"
senha = "Silas@9451#"
identificador = "IZINGPRO"

# Configuração do banco de dados MySQL
db_host = "localhost"
db_user = "root"
db_password = ""
db_name = "gps"

# Variável para rastrear o número total de linhas atualizadas para "online"
total_linhas_atualizadas = 0

# Variável para rastrear a quantidade de serviços com o mesmo ID da API
quantidade_servicos_com_mesmo_id = 0

# Variável para rastrear o número total de linhas definidas como "offline"
total_linhas_offline = 0

# Variável para rastrear o número total de linhas definidas como "online"
total_linhas_online = 0

# Função para fazer login e obter a sessão e idUsuario
def fazer_login():
    login_data = {
        'usuario': usuario,
        'senha': senha,
        'identificador': identificador
    }

    response = requests.post(base_url + login_endpoint, data=login_data, verify=False)
    
    if response.status_code == 200:
        data = response.json()
        if data["resultado"]:
            return data["sessao"], data["id_usuario"]
        else:
            print("Falha ao fazer login:", data["msg"])
    else:
        print("Erro ao fazer login. Código de status:", response.status_code)

# Função para definir todas as linhas da coluna "conexão" como "offline" no banco de dados MySQL
def definir_todos_como_offline():
    global total_linhas_offline  # Define a variável como global
    connection = None  # Inicializa a conexão como None
    try:
        connection = mysql.connector.connect(host=db_host, user=db_user, password=db_password, database=db_name)
        cursor = connection.cursor()
        
        # Define todas as linhas da coluna "conexão" como "offline"
        update_query = "UPDATE servicos SET conexao = 'offline'"
        cursor.execute(update_query)
        connection.commit()
        total_linhas_offline = cursor.rowcount  # Número de linhas definidas como offline
        print(f"Todas as linhas da coluna 'conexão' definidas como 'offline'. {total_linhas_offline} linhas atualizadas.")
    except mysql.connector.Error as error:
        print("Erro ao definir todas as linhas como 'offline':", error)
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()

# Função para listar histórico de conexão de um serviço
def listar_historico_conexao(sessao, idUsuario):
    historico_data = {
        'sessao': sessao,
        'idUsuario': idUsuario,
        'identificador': identificador,
        'status': "on"
    }

    response = requests.post(base_url + historico_endpoint, data=historico_data, verify=False)
    
    if response.status_code == 200:
        data = response.json()
        if data["resultado"]:
            return data["dados"]
        else:
            print("Falha ao obter histórico de conexão:", data["msg"])
    else:
        print("Erro ao obter histórico de conexão. Código de status:", response.status_code)

# Função para verificar e definir a coluna conexão como "online" no banco de dados MySQL
def verificar_e_atualizar_status_conexao(id_servico):
    global total_linhas_online  # Define a variável como global
    connection = None  # Inicializa a conexão como None
    rows_updated = 0  # Variável para rastrear o número de linhas atualizadas
    try:
        connection = mysql.connector.connect(host=db_host, user=db_user, password=db_password, database=db_name)
        cursor = connection.cursor()
        
        # Verifica se o id_servico está presente na tabela
        select_query = "SELECT id_servico FROM servicos WHERE id_servico = %s"
        cursor.execute(select_query, (id_servico,))
        result = cursor.fetchone()
        
        if result:
            # Define a coluna conexão como "online" para o id_servico encontrado
            update_query = "UPDATE servicos SET conexao = 'online' WHERE id_servico = %s"
            cursor.execute(update_query, (id_servico,))
            connection.commit()
            rows_updated = cursor.rowcount  # Número de linhas atualizadas
            total_linhas_online += rows_updated  # Atualiza o contador total de linhas online
            print("Coluna conexão definida como 'online' para id_servico:", id_servico)
        else:
            print("Serviço não encontrado na tabela. Não atualizou a coluna conexão para id_servico:", id_servico)
    except mysql.connector.Error as error:
        print("Erro ao verificar/definir a coluna conexão:", error)
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()
    return rows_updated  # Retorna o número de linhas atualizadas

if __name__ == "__main__":

    definir_todos_como_offline()  # Define todas as linhas como "offline" antes de iniciar
    
    sessao, idUsuario = fazer_login()
    if sessao:
        print("Sessão iniciada com sucesso")
        historico = listar_historico_conexao(sessao, idUsuario)
        if historico:
            print("Histórico de conexão:")
            for conexao in historico:
                id_servico = conexao["id_servico"]
                print(f"Id_Serviço: {id_servico}, Status de Conexão: online")
                
                # Verifica e atualiza o status de conexão no banco de dados MySQL
                rows_updated = verificar_e_atualizar_status_conexao(id_servico)
                total_linhas_atualizadas += rows_updated  # Atualiza o contador total de linhas atualizadas
                
                # Verifica se o id_servico existe na tabela servicos
                connection = mysql.connector.connect(host=db_host, user=db_user, password=db_password, database=db_name)
                cursor = connection.cursor()
                select_query = "SELECT id_servico FROM servicos WHERE id_servico = %s"
                cursor.execute(select_query, (id_servico,))
                result = cursor.fetchone()
                if result:
                    quantidade_servicos_com_mesmo_id += 1
                connection.close()
            
            print(f"Total de {total_linhas_atualizadas} linhas atualizadas para 'online'.")
            print(f"Total de {total_linhas_online} linhas definidas como 'online'.")
            print(f"Quantidade de serviços com o mesmo ID da API: {quantidade_servicos_com_mesmo_id}")
        else:
            print("Falha ao obter histórico de conexão.")
    else:
        print("Falha ao iniciar sessão.")