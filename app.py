import os
import pandas as pd
import matplotlib
import matplotlib.pyplot as plt
from flask import Flask, render_template, request

# Forzar Matplotlib a usar el backend 'Agg' para evitar problemas en Flask
matplotlib.use('Agg')

app = Flask(__name__)
UPLOAD_FOLDER = "uploads"
app.config["UPLOAD_FOLDER"] = UPLOAD_FOLDER

# Asegúrate de que los directorios 'static' y 'uploads' existan
os.makedirs("static", exist_ok=True)
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

@app.route("/modulo")
def modulo():
    return render_template("modulo.html")

@app.route("/quiz")
def quiz():
    return render_template("quiz.html")

@app.route("/", methods=["GET", "POST"])
def index():
    graph_files = []  # Lista de gráficos generados
    table_html = None  # Inicializa variable para almacenar la tabla HTML

    if request.method == "POST":
        file = request.files["file"]
        if file:
            filepath = os.path.join(app.config["UPLOAD_FOLDER"], file.filename)
            file.save(filepath)

            # Leer y procesar el archivo CSV para mostrarlo como tabla
            df = read_csv_with_fallback(filepath)
            if df is not None and not df.empty:
                # Limitar a las primeras 50 filas
                table_html = df.head(100).to_html(classes="table table-striped", index=False)
            
            # Generar gráficos con el archivo
            generate_graphs(filepath)
            graph_files = [f for f in os.listdir("static") if f.endswith(".png")]

    return render_template("index.html", graph_files=graph_files, table_html=table_html)



def read_csv_with_fallback(filepath):
    """
    Función para leer archivos CSV con soporte para diferentes codificaciones y separadores.
    """
    try:
        return pd.read_csv(filepath, encoding='utf-8', sep=',')
    except Exception as e:
        print(f"Error al leer CSV con UTF-8: {e}")
        try:
            return pd.read_csv(filepath, encoding='latin-1', sep=';')
        except Exception as e2:
            print(f"Error al leer CSV con Latin-1: {e2}")
            return None

def generate_graphs(filepath):
    """
    Genera gráficos basados en las columnas del archivo CSV.
    """
    df = read_csv_with_fallback(filepath)
    if df is None:
        print("No se pudo leer el archivo CSV.")
        return

    # Si no hay suficientes datos
    if df.empty:
        print("El archivo CSV está vacío.")
        return

    # Crear gráficos dinámicamente basados en las columnas
    try:
        # Excluir columnas con valores únicos (como ID)
        relevant_columns = [col for col in df.columns 
                            if df[col].nunique() > 1 and df[col].nunique() < len(df)]

        # Gráfico de barras para la primera columna categórica relevante encontrada
        for column in relevant_columns:
            if df[column].dtype == 'object' or df[column].nunique() < 20:  # Columna categórica
                plt.figure(figsize=(8, 6))
                df[column].value_counts().plot(kind='bar', color='skyblue')
                plt.title(f"Frecuencia de {column}")
                plt.xlabel(column)
                plt.ylabel("Cantidad")
                plt.xticks(rotation=45)
                plt.tight_layout()
                plt.savefig(f"static/graphic_bar.png")
                plt.close()
                print(f"Gráfico de barras generado para la columna '{column}'")
                break

        # Gráfico de líneas para las columnas numéricas
        numeric_columns = df.select_dtypes(include='number')
        if not numeric_columns.empty:
            plt.figure(figsize=(8, 6))
            numeric_columns.plot()
            plt.title("Gráfico de líneas de datos numéricos")
            plt.tight_layout()
            plt.savefig("static/numeric_lines.png")
            plt.close()
            print("Gráfico de líneas generado para columnas numéricas.")

        # Gráfico de pie (torta) para la primera columna numérica o categórica
        if not numeric_columns.empty:
            # Utilizamos la primera columna numérica para el gráfico de torta
            numeric_data = numeric_columns.iloc[:, 0].value_counts()
            plt.figure(figsize=(8, 6))
            numeric_data.plot(kind='pie', autopct='%1.1f%%', startangle=90, colors=plt.cm.Paired.colors)
            plt.title(f"Distribución de {numeric_columns.columns[0]}")
            plt.ylabel('')  # Para eliminar el label que aparece en el gráfico
            plt.tight_layout()
            plt.savefig("static/pie_chart.png")
            plt.close()
            print(f"Gráfico de pie generado para la columna '{numeric_columns.columns[0]}'.")

        # Si solo hay columnas categóricas, se puede hacer un gráfico de pie similar
        if relevant_columns and df[relevant_columns[0]].dtype == 'object':
            categorical_data = df[relevant_columns[0]].value_counts()
            plt.figure(figsize=(8, 6))
            categorical_data.plot(kind='pie', autopct='%1.1f%%', startangle=90, colors=plt.cm.Paired.colors)
            plt.title(f"Distribución de {relevant_columns[0]}")
            plt.ylabel('')
            plt.tight_layout()
            plt.savefig("static/pie_chart_categorical.png")
            plt.close()
            print(f"Gráfico de pie generado para la columna categórica '{relevant_columns[0]}'.")

    except Exception as e:
        print(f"Error al generar gráficos: {e}")

if __name__ == "__main__":
    app.run(debug=True)
