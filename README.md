# 🐦 TweetAnalytics — Analyse Multilingue des Tweets (Basée sur Kaggle)

**TweetAnalytics** est une application web permettant d’analyser des tweets en **anglais, français et arabe**, à partir d’un **dataset Kaggle**.  
Le but du projet est de fournir une plateforme simple pour :

- analyser les sentiments  
- afficher des statistiques  
- visualiser les tendances  
- exporter un rapport PDF professionnel  
- collecter du feedback depuis un formulaire EmailJS  

> ⚠️ **Note :**  
> La version actuelle utilise **des données Kaggle** (et non pas l’API Twitter).  
> Une future mise à jour intégrera l’API Twitter pour du *quasi temps réel*.

🎥 **Démonstration vidéo :**  
👉 https://www.linkedin.com/posts/soumya-laaouina_python-webdevelopment-innovation-activity-7344332805759254528-FrMw

---

## 🌟 Fonctionnalités principales

### 🗃️ 1. Analyse des tweets depuis un dataset Kaggle
- Chargement d’un fichier CSV contenant des tweets
- Filtrage selon un mot-clé
- Support multilingue EN / FR / AR

---

### 😊 2. Analyse de sentiment
Classification automatique :
- **Positif**
- **Négatif**
- **Neutre**

Basée sur :
- TextBlob  
- NLTK  
- Prétraitement linguistique multilingue

---

### 📊 3. Dashboard et Visualisations
- graphiques statistiques (pie chart, bar chart…)  
- tendances globales  
- fréquence des mots  

---

### 📄 4. Export PDF professionnel
📄 Génère un PDF contenant :
- résumé  
- statistiques  
- graphiques  
- exemples de tweets filtrés

---

### 💬 5. Feedback utilisateur (EmailJS)
Formulaire intégré permettant d’envoyer des retours directement par email.

---

## 🛠️ Stack Technique

### Backend
- Python  
- Pandas  
- NLTK  
- TextBlob  
- FPDF / ReportLab

### Frontend
- HTML  
- CSS  
- PHP  

### Données
- Dataset Kaggle (Tweets CSV)  
👉 *(Import local au lieu du temps réel)*

---

## 🧱 Architecture

```mermaid
flowchart TB
    A[Interface Web<br>HTML/CSS/PHP] --> B{Mot-clé utilisateur}
    B --> C[Dataset Kaggle<br>CSV Files]
    C --> D[Prétraitement<br>Pandas / NLP]
    D --> E[Analyse Sentiment<br>TextBlob / NLTK]
    E --> F[Visualisation<br>Charts & Stats]
    E --> G[Export PDF]
    A --> H[EmailJS<br>Feedback]
