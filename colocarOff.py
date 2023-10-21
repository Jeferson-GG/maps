import mysql.connector

# Configuração do banco de dados MySQL
db_host = "localhost"
db_user = "root"
db_password = ""
db_name = "gps"

def definir_todos_como_offline():
    connection = None
    try:
        connection = mysql.connector.connect(host=db_host, user=db_user, password=db_password, database=db_name)
        cursor = connection.cursor()
        
        # Define todas as linhas da coluna "conexao" como "offline"
        update_query = "UPDATE servicos SET conexao = 'offline'"
        cursor.execute(update_query)
        connection.commit()
        total_linhas_offline = cursor.rowcount  # Número de linhas definidas como offline
        print(f"Todas as linhas da coluna 'conexao' definidas como 'offline'. {total_linhas_offline} linhas atualizadas.")
    except mysql.connector.Error as error:
        print("Erro ao definir todas as linhas como 'offline':", error)
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()

if __name__ == "__main__":
    definir_todos_como_offline()