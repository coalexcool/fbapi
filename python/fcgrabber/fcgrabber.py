import os
import requests
import pandas as pd
import json
from pprint import pprint
from credentials import cookies

BASE = "https://www.fantacalcio.it/api/v1/Excel/prices/107/1"

headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36"}

# Crea una cartella temp nella directory corrente
temp_dir = "api/player/fcgrabber/temp"
if not os.path.exists(temp_dir):
    os.makedirs(temp_dir)

# Percorso completo del file
file_path = os.path.join(temp_dir, 'prices.xlsx')

# Effettua la richiesta e salva il file Excel nella cartella temp
rq = requests.get(BASE, headers=headers, timeout=10, cookies=cookies)

if rq.status_code == 200:
    with open(file_path, 'wb') as file:
        file.write(rq.content)
    print(f"File scaricato correttamente e salvato in '{file_path}'.")
    
    # Carica il file Excel usando pandas, saltando la prima riga
    df = pd.read_excel(file_path, skiprows=1)
    
    # Rinomina le colonne come richiesto
    df = df.rename(columns={
        "Id": "id_fantacalcio",
        "R": "role",
        "RM": "role_name",
        "Nome": "name",
        "Nazione": "league",
        "Squadra": "team",
        "Qt. A": "qta",
        "Qt.I": "qti",
        "Diff.": "diff",
        "Qt.A M": "qtam",
        "Qt.I M": "qtim",
        "Diff.M": "diffm",
        "FVM": "fvm",
        "FVM M": "fvmm"
    })
    
    # Converti il dataframe in JSON
    json_data = df.to_json(orient='records', force_ascii=False)
    
    data = json.loads(json_data)

    # Mostra il risultato JSON
    for x in data:
        pprint(x)
else:
    print(f"Errore durante la richiesta: {rq.status_code}")
