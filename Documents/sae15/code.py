import pandas as pd
from datetime import datetime

ENTREE_BRUTE = "donnees_reseau_brutes.csv"
SORTIE_MD = "rapport_reseau.md"
SORTIE_CSV = "rapport_reseau_tableur.csv"

def traiter_donnees_reseau(chemin_entree: str):
    # Lecture des données brutes (adapter aux colonnes réelles)
    df = pd.read_csv(chemin_entree)

    # Exemple : filtrage des colonnes utiles
    df_filtre = df[["timestamp", "ip_source", "ip_dest", "debit", "erreurs"]]

    # Agrégation pour un CSV exploitable
    resume = (
        df_filtre
        .groupby("ip_source", as_index=False)
        .agg(
            debit_moyen=("debit", "mean"),
            erreurs_total=("erreurs", "sum")
        )
    )

    # Quelques indicateurs globaux pour le rapport Markdown
    nb_lignes = len(df_filtre)
    debit_moyen_global = df_filtre["debit"].mean()
    return df_filtre, resume, nb_lignes, debit_moyen_global

def generer_markdown(df_filtre, resume, nb_lignes, debit_moyen_global, chemin_md: str):
    now = datetime.now().strftime("%Y-%m-%d %H:%M")

    contenu = []
    contenu.append("# Rapport d'analyse réseau\n")
    contenu.append(f"Date de génération : {now}\n")
    contenu.append(f"- Nombre d'enregistrements : **{nb_lignes}**\n")
    contenu.append(f"- Débit moyen global : **{debit_moyen_global:.2f}**\n")

    contenu.append("\n## Aperçu des données brutes (5 premières lignes)\n")
    contenu.append(df_filtre.head().to_markdown(index=False))

    contenu.append("\n## Résumé par adresse IP source\n")
    contenu.append(resume.head(10).to_markdown(index=False))

    with open(chemin_md, "w", encoding="utf-8") as f:
        f.write("\n".join(contenu))

def exporter_csv(resume, chemin_csv: str):
    resume.to_csv(chemin_csv, index=False)

if __name__ == "__main__":
    df_filtre, resume, nb_lignes, debit_moyen_global = traiter_donnees_reseau(ENTREE_BRUTE)
    generer_markdown(df_filtre, resume, nb_lignes, debit_moyen_global, SORTIE_MD)  # [web:6][web:9]
    exporter_csv(resume, SORTIE_CSV)  # [web:6][web:12]
