import mysql.connector
import ping3
import time
import datetime

# Função para realizar o ping e atualizar o banco de dados
def ping_and_update_database():
    while True:
        # Conecta ao banco de dados MySQL
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",  # Substitua pela senha do seu banco de dados
            database="gps"
        )

        cursor = conn.cursor()

        # Inicializa as contagens de clientes online e offline
        clientes_online = 0
        clientes_offline = 0

        # Seleciona todos os IPs da tabela "servicos"
        cursor.execute("SELECT id, ip, conexao FROM servicos")

        for row in cursor.fetchall():
            id_servico = row[0]
            ip = row[1]
            conexao_anterior = row[2]

            print("Pingando o IP:", ip)

            if ip is not None and isinstance(ip, str):  # Verifica se ip é uma string válida
                # Realiza o ping no IP
                response_time = ping3.ping(ip)

                if response_time is not None:
                    print("Ping bem-sucedido. Tempo de resposta:", response_time, "ms")
                    conexao = "online"
                    clientes_online += 1
                else:
                    print("Falha no ping. O host está offline.")
                    conexao = "offline"
                    clientes_offline += 1
            else:
                print("IP inválido. Ignorando o ping.")
                conexao = "offline"
                clientes_offline += 1

            # Atualiza a coluna 'conexao' com o resultado do ping
            cursor.execute("UPDATE servicos SET conexao = %s WHERE id = %s", (conexao, id_servico))
            conn.commit()

        # Obtém a data atual
        data_atual = datetime.date.today()

        # Insere as estatísticas diárias na tabela "historico_estatisticas"
        cursor.execute("INSERT INTO historico_estatisticas (data, clientes_online, clientes_offline) VALUES (%s, %s, %s)",
                       (data_atual, clientes_online, clientes_offline))
        conn.commit()

        # Atualiza as contagens de clientes online e offline na tabela "estatisticas"
        cursor.execute("UPDATE estatisticas SET clientes_online = %s, clientes_offline = %s WHERE id = 1",
                       (clientes_online, clientes_offline))
        conn.commit()

        # Fecha a conexão com o banco de dados
        cursor.close()
        conn.close()

        # Intervalo de tempo para realizar o ping e atualização (em segundos)
        intervalo = 60  # 1 minuto
        time.sleep(intervalo)

if __name__ == '__main__':
    ping_and_update_database()
