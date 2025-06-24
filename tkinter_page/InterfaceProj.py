from tkinter import *
import tkinter as tk
from tkinter import messagebox ,PhotoImage, ttk
from PIL import Image, ImageTk, ImageFilter
import pandas as pd
import json
from datetime import datetime
from googletrans import Translator
from textblob import TextBlob
import os
import webbrowser
import time
import matplotlib.pyplot as plt
from deep_translator import GoogleTranslator
import time
import itertools
import sys

# Chargement des deux fichiers CSV
df_sentiment140 = pd.read_csv(
    r'C:\Users\SOUMI\OneDrive\Bureau\xamp\htdocs\AppTweet\data\sentiment140_preprocessed.csv',
    skiprows=1,
    names=["sentiment", "text", "cleaned_text"],
    dtype={0: int},
    low_memory=False
)

df_neutre = pd.read_csv(
      r'C:\Users\SOUMI\OneDrive\Bureau\xamp\htdocs\AppTweet\data\tweets_neutral1.csv',
      names=["sentiment", "text", "cleaned_text"],skiprows=1)

# Initialiser le traducteur

#print(df_neutre.head())  # Affiche les 5 premières lignes pour vérifier les donnée
import re  # Ajout pour la gestion des expressions régulières
import sys
import io
from googletrans import Translator
import warnings

# Configuration de l'encodage UTF-8 pour la console
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

# Désactivation des warnings inutiles
warnings.filterwarnings("ignore", category=RuntimeWarning)
import sys
from textblob import TextBlob
import warnings
from deep_translator import GoogleTranslator

# Configuration pour éviter les problèmes d'encodage
sys.stdout.reconfigure(encoding='utf-8')
warnings.filterwarnings("ignore")

def detect_and_translate(user_input):
    """Fonction améliorée de détection et traduction"""
    if not user_input or not user_input.strip():
        return user_input
    
    text = user_input.strip()
    
    try:
        # D'abord essayer de traduire la phrase complète
        try:
            full_translation = GoogleTranslator(source='auto', target='en').translate(text)
            if full_translation and full_translation.lower() != text.lower():
                return full_translation.lower()
        except Exception as e:
            print(f"Erreur traduction complète: {e}")
        
        # Si la traduction complète échoue ou ne change pas le texte
        # Essayer une approche mot-à-mot plus robuste
        words = text.split()
        if len(words) > 1:
            translated_words = []
            for word in words:
                try:
                    # Traduire chaque mot individuellement
                    translated_word = GoogleTranslator(source='auto', target='en').translate(word)
                    if translated_word and translated_word.lower() != word.lower():
                        translated_words.append(translated_word.lower())
                    else:
                        translated_words.append(word.lower())
                except Exception as e:
                    print(f"Erreur traduction mot '{word}': {e}")
                    translated_words.append(word.lower())
            
            # Reconstruire la phrase traduite
            translated_text = ' '.join(translated_words)
            
            # Vérifier si la traduction mot-à-mot a fonctionné
            if translated_text.lower() != text.lower():
                return translated_text
            else:
                # Si aucune traduction n'a fonctionné, retourner le texte original en minuscules
                return text.lower()
        
        # Cas d'un seul mot
        return text.lower()
    
    except Exception as e:
        print(f"Erreur générale dans detect_and_translate: {e}")
        return text.lower()

def chercher_tweets(mot_cle_original):
    # Traduction du mot-clé
    mot_cle = detect_and_translate(mot_cle_original).strip().lower()
    print(f"Mot-clé original: {mot_cle_original}, traduit: {mot_cle}")
    
    # Création du motif regex adapté aux mots simples et composés
    if len(mot_cle.split()) > 1:  # Si c'est une expression composée
        # Option 1: Recherche de la phrase exacte
        pattern_exact = fr"\b{re.escape(mot_cle)}\b"
        
        # Option 2: Recherche de tous les mots (peu importe l'ordre et la distance)
        mots = [re.escape(m) for m in mot_cle.split()]  # Décommenté cette ligne
        pattern_mots = r'(?=.*\b' + r'\b)(?=.*\b'.join(mots) + r'\b)'  # Décommenté cette ligne
        
        # On combine les deux options avec OR (|)
        pattern = f"({pattern_exact}|{pattern_mots})"
    else:  # Mot simple
        pattern = fr"\b{re.escape(mot_cle)}\b"
    
    # Recherche insensible à la casse
    flags = re.IGNORECASE
    
    # Application aux deux dataframes
    mask_sentiment140 = (
        df_sentiment140['cleaned_text']
        .str.contains(pattern, regex=True, flags=flags, na=False)
    )
    
    mask_neutre = (
        df_neutre['cleaned_text']
        .str.contains(pattern, regex=True, flags=flags, na=False)
    )
   
    # Combiner les résultats
    resultats_sentiment140 = df_sentiment140[mask_sentiment140]
    resultats_neutre = df_neutre[mask_neutre]
    resultats = pd.concat([resultats_sentiment140, resultats_neutre])
    
    # Analyse des résultats (identique à votre version originale)
    total_tweets = len(resultats)
    date_analysis = datetime.now().strftime('%Y-%m-%d')
    sentiments = resultats['sentiment'].value_counts()
    
    # Construction du résultat JSON
    data = {
        "search_term": mot_cle_original,
        "translated_term": mot_cle,
        "total_tweets": total_tweets if total_tweets > 0 else 0,  # Éviter division par zéro
        "date_analysis": date_analysis,
        "sentiment_distribution": {
            "positive": round((sentiments.get(1, 0)*100)/max(total_tweets, 1), 2),
            "neutral": round((sentiments.get(2, 0)*100)/max(total_tweets, 1), 2),
            "negative": round((sentiments.get(0, 0)*100)/max(total_tweets, 1), 2)
        },
        "top_tweets": {
            "positive": resultats[resultats['sentiment'] == 1]['text'].head(2).tolist(),
            "negative": resultats[resultats['sentiment'] == 0]['text'].head(2).tolist(),
            "neutre": resultats[resultats['sentiment'] == 2]['text'].head(2).tolist()
        }
    }
    
    # Sauvegarde des résultats
    with open('resultats_recherche.json', 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    
    # Mise à jour de l'historique (identique à votre version originale)
    historique_path = 'historique_recherches.json'
    historique = []
    
    if os.path.exists(historique_path):
        try:
            with open(historique_path, 'r', encoding='utf-8') as f:
                historique = json.load(f)
        except json.JSONDecodeError:
            pass
    
    historique.append({
        "search_term": mot_cle_original,
        "date": date_analysis,
        "translated_term": mot_cle
    })
    
    with open(historique_path, 'w', encoding='utf-8') as f:
        json.dump(historique, f, ensure_ascii=False, indent=2)
    
    return data


# === Classe Clavier Virtuel ===
class VirtualKeyboard:
    def __init__(self, root, target_entry):
        self.root = root
        self.target_entry = target_entry
        self.keyboard_window = None
        self.shift_active = False
        # Stocker les placeholders disponibles
        self.placeholder_texts = {
            "french": "Entrez un mot clé...",
            "english": "Enter a keyword..."
        }
        # Variable pour suivre l'état du placeholder
        self.placeholder_active = True

    # Création des différents claviers (inchangée)
    def create_english_keyboard(self):
        self._create_keyboard([
            ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', '=', '←'],
            ['q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', '[', ']', '\\'],
            ['a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', ';', "'", 'Enter'],
            ['z', 'x', 'c', 'v', 'b', 'n', 'm', ',', '.', '/', '↑'],
            ['Space', 'Shift', 'Delete', 'Close']
        ], "English Keyboard")

    def create_french_keyboard(self):
        self._create_keyboard([
            ['&', 'é', '"', "'", '(', '-', 'è', '_', 'ç', 'à', ')', '=', '←'],
            ['a', 'z', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', '^', '$'],
            ['q', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'ù', '*', 'Enter'],
            ['w', 'x', 'c', 'v', 'b', 'n', ',', ';', ':', '!', '↑'],
            ['Espace', 'Maj', 'Effacer', 'Fermer']
        ], "Clavier Français")

    def create_arabic_keyboard(self):
        self._create_keyboard([
            ['ذ', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', '=', '←'],
            ['ض', 'ص', 'ث', 'ق', 'ف', 'غ', 'ع', 'ه', 'خ', 'ح', 'ج', 'د', '\\'],
            ['ش', 'س', 'ي', 'ب', 'ل', 'ا', 'ت', 'ن', 'م', 'ك', 'ط', 'Enter'],
            ['ئ', 'ء', 'ؤ', 'ر', 'لا', 'ى', 'ة', 'و', 'ز', 'ظ', '↑'],
            ['مسافة', 'Shift', 'مسح', 'إغلاق']
        ], "لوحة المفاتيح العربية")

    def show_virtual_keyboard(self, event=None):
        entry_x = self.target_entry.winfo_rootx()
        entry_y = self.target_entry.winfo_rooty() + self.target_entry.winfo_height()
        langue = selected_lang.get()

        if langue == "french":
            self.create_french_keyboard()
        elif langue == "english":
            self.create_english_keyboard()
        elif langue == "arabic":
            self.create_arabic_keyboard()

        self.keyboard_window.geometry(f"600x250+{entry_x}+{entry_y}")
        
        # Vérifier si le placeholder est actif quand on ouvre le clavier
        current_text = self.target_entry.get()
        for placeholder in self.placeholder_texts.values():
            if current_text == placeholder:
                self.placeholder_active = True
                break
        else:
            self.placeholder_active = False

    def _create_keyboard(self, layout, title):
        if self.keyboard_window:
            self.keyboard_window.destroy()
        self.keyboard_window = Toplevel(self.root)
        self.keyboard_window.title(title)

        for row in layout:
            frame = Frame(self.keyboard_window)
            frame.pack()
            for key in row:
                action = lambda k=key: self.insert_text(k)
                if key.lower() in ['space', 'espace', 'مسافة']:
                    btn = Button(frame, text=key, width=10, command=lambda: self.insert_text(' '))
                elif key.lower() in ['shift', 'maj']:
                    btn = Button(frame, text=key, width=5, command=self.toggle_shift)
                elif key.lower() in ['delete', 'effacer', 'مسح']:
                    btn = Button(frame, text=key, width=5, command=self.clear_text)
                elif key.lower() in ['close', 'fermer', 'إغلاق']:
                    btn = Button(frame, text=key, width=5, command=self.keyboard_window.destroy)
                elif key == '←':
                    btn = Button(frame, text=key, width=3, command=self.backspace)
                elif key == '↑':
                    btn = Button(frame, text=key, width=3, command=self.toggle_shift)
                elif key == 'Enter':
                    btn = Button(frame, text=key, width=5, command=lambda: self.insert_text('\n'))
                else:
                    btn = Button(frame, text=key, width=3, command=action)
                btn.pack(side=LEFT, padx=1, pady=1)

    def insert_text(self, char):
        """Insère un texte dans le champ Entry et gère la suppression du texte par défaut."""
        # Vérifier si le placeholder est actif
        current_text = self.target_entry.get()
        
        # Vérifier si le texte actuel est un placeholder
        is_placeholder = False
        for placeholder in self.placeholder_texts.values():
            if current_text == placeholder:
                is_placeholder = True
                break
        
        # Si c'est le premier caractère tapé et qu'il y a un placeholder
        if is_placeholder or self.placeholder_active:
            self.target_entry.delete(0, END)
            self.target_entry.config(fg='black')
            self.placeholder_active = False
        
        # Si shift est activé, mettre la lettre en majuscule
        if self.shift_active and char.isalpha():
            char = char.upper()
            self.shift_active = False

        # Insérer le caractère dans le champ Entry à la position du curseur
        current_pos = self.target_entry.index(INSERT)
        self.target_entry.insert(current_pos, char)

    def toggle_shift(self):
        self.shift_active = not self.shift_active

    def clear_text(self):
        self.target_entry.delete(0, END)
        self.placeholder_active = True  # Réinitialiser l'état du placeholder

    def backspace(self):
        current_pos = self.target_entry.index(INSERT)
        if current_pos > 0:
            self.target_entry.delete(current_pos - 1, current_pos)
            # Si le champ est vide après suppression, considérer que le placeholder pourrait être réactivé
            if len(self.target_entry.get()) == 0:
                self.placeholder_active = True


# Fonction pour gérer le focus sur le champ de recherche
def on_entry_click(event):
    if mot.get() in placeholder_texts.values():
        mot.delete(0, END)
        mot.config(fg='black')
        # Informer également le clavier virtuel que le placeholder n'est plus actif
        virtual_keyboard.placeholder_active = False

def on_focusout(event):
    if mot.get() == "":
        mot.insert(0, placeholder_texts[current_lang])
        mot.config(fg='grey')
        # Informer le clavier virtuel que le placeholder est actif
        virtual_keyboard.placeholder_active = True


#   Interface clvier
fen = Tk()
fen.title("welcome")
fen.geometry(f"{fen.winfo_screenwidth()}x{fen.winfo_screenheight()}")

translations = {
    'french': {
        'title': "Bienvenue! Rejoignez-nous et découvrez ce que le monde pense !!",
        'search': "Recherche par un mot clé",
        'validate': "Valider",
        'cancel': "Annuler",
        'quit': "Quitter",
        'warning': "Veuillez entrer un mot clé !",
        'success': "Vous avez saisi : ",
        'attention': "Attention",
        'success_title': "Succès"
    },
    'english': {
        'title': "     Welcome! Join us and discover what the world thinks!!    ",
        'search': "Search by keyword",
        'validate': " Validate ",
        'cancel': "  Cancel  ",
        'quit': "   Exit   ",
        'warning': "Please enter a keyword!",
        'success': "You entered: ",
        'attention': "Warning",
        'success_title': "Success"
    }
}

current_lang = 'english'
texte_complet = translations[current_lang]['title']
index_lettre = 0
x_pos = 10
y_pos = 10
direction = 12
animation_finie = False
animation_task = None

def animer():
    global animation_task
    # Annule toute tâche existante
    if animation_task:
        fen.after_cancel(animation_task)
    # Réinitialise la position
    canvas.coords(oiseau_id, 10, y_pos)
    # Lance une nouvelle animation
    animation_task = fen.after(100, animer_cycle)

def animer_cycle():
    global x_pos, index_lettre, animation_finie, animation_task
    if index_lettre < len(texte_complet):
        index_lettre += 1
        portion = texte_complet[:index_lettre]
        canvas.itemconfig(texte_id, text=portion)
        x_pos += direction
        canvas.coords(oiseau_id, x_pos, y_pos - 8)
        animation_task = fen.after(100, animer_cycle)
    elif not animation_finie:
        animation_finie = True
        continuer_mouvement()

def continuer_mouvement():
    global x_pos, animation_task
    if x_pos < fen.winfo_width() + 4:
        x_pos += direction
        canvas.coords(oiseau_id, x_pos, y_pos)
        animation_task = fen.after(100, continuer_mouvement)
    else:
        animation_task = None  # Marque la fin de l'animation

def annuler():
    mot.delete(0, END)
    mot.focus()
import threading
import time
import sys
from itertools import cycle

def aucour():
    # Codes ANSI pour le texte bleu
    BLUE = "\033[34m"
    RESET = "\033[0m"
    
    # Caractères du cercle animé
    frames = ['◜', '◝', '◞', '◟']
    
    # Variable pour contrôler l'animation
    global loading_active
    loading_active = True
    
    for frame in cycle(frames):
        if not loading_active:
            break
        # Affiche en bleu avec retour au début de la ligne
        sys.stdout.write(f'\r{BLUE}{frame}{RESET} Chargement en cours...')
        sys.stdout.flush()
        time.sleep(0.2)
    
    # Effacer la ligne quand c'est fini
    sys.stdout.write('\r' + ' ' * 30 + '\r')
    sys.stdout.flush()

def valider():
    global loading_active
    
    valeur = mot.get().strip()
    
    if not valeur or valeur == "Enter a keyword...":
        messagebox.showwarning("Attention", "Please enter a valid keyword.")
        return
    if valeur == "Enter un mot clé...":
        messagebox.showwarning("Attention", "Veuillez entrer un mot-clé valide.")
        return
    
    try:
        # Désactiver le bouton pendant le traitement
        V.config(state=tk.DISABLED)
        fen.update_idletasks()  # Force la mise à jour de l'interface
        
        # Démarrer l'animation dans un thread séparé
        loading_thread = threading.Thread(target=aucour, daemon=True)
        loading_active = True
        loading_thread.start()
        
        # Recherche des tweets (cette partie bloque l'interface)
        data = chercher_tweets(valeur)
        
        # Arrêter l'animation
        loading_active = False
        loading_thread.join()
        
        # Ouvrir le navigateur
        webbrowser.open("http://localhost/APPTWEET/site_php/twitter_feedback_feature.php")
        
    finally:
        # Réactiver le bouton
        V.config(state=tk.NORMAL)
        loading_active = False
        """
        # Génération du graphique
        if generate_chart(data):
            # Attendre que le fichier soit complètement écrit
            for _ in range(5):  # Essayer pendant 5 secondes max
                if os.path.exists("graphe_resulta.png"):
                    time.sleep(0.5)  # Pause supplémentaire pour être sûr
                    webbrowser.open("http://localhost/APPTWEET/site_php/twitter_feedback_feature.php")
                    break
                time.sleep(1)
            else:
                messagebox.showerror("Erreur", "Le graphique n'a pas été généré")
        else:
            messagebox.showerror("Erreur", "Échec de la génération du graphique")
        """   
    

def generate_chart(data):
    """Génère et sauvegarde le graphique des sentiments de manière fiable"""
    try:
        # Vérification des données
        if not data or not data.get('sentiment_distribution'):
            print("Erreur: Données de sentiment manquantes")
            return False

        # Extraction des valeurs avec vérification
        sentiments = data['sentiment_distribution']
        pos = float(sentiments.get('positive', 0))
        neg = float(sentiments.get('negative', 0))
        ntr = float(sentiments.get('neutral', 0))

        # Configuration du graphique
        plt.figure(figsize=(10, 6))
        categories = ["Positif", "Négatif", "Neutre"]
        values = [pos, neg, ntr]
        colors = ['#4fc3f7', '#2e86c1', '#9E9E9E']  # bleu, bleu foncé, Gris

        bars = plt.bar(categories, values, color=colors)
        
        # Personnalisation
        plt.title(f"Analyse des sentiments pour: {data.get('search_term', '')}")
        plt.xlabel("Catégories de sentiments")
        plt.ylabel("Pourcentage (%)")
        plt.ylim(0, 100)  # Échelle fixe de 0 à 100%

        # Ajout des valeurs sur les barres
        for bar in bars:
            height = bar.get_height()
            plt.text(bar.get_x() + bar.get_width()/2, height,
                    f'{height:.1f}%', ha='center', va='bottom')

        # Sauvegarde avec vérification
        output_path = os.path.abspath("graphe_resulta.png")
        plt.savefig(output_path, dpi=100, bbox_inches='tight')
        plt.close()
        
        # Vérification que le fichier a bien été créé
        if os.path.exists(output_path):
            print(f"Graphique sauvegardé avec succès à: {output_path}")
            return True
        else:
            print("Erreur: Le fichier graphique n'a pas été créé")
            return False

    except Exception as e:
        print(f"Erreur critique dans generate_chart(): {str(e)}")
        return False

def quitter():
    fen.quit()
placeholder_texts = {
    "french": "Entrez un mot clé...",
    "english": "Enter a keyword..."
}
def changer_langue(langue):
    global current_lang, texte_complet, index_lettre, animation_finie, x_pos,direction
    current_lang = langue
    lblmot.config(text=translations[current_lang]['search'])
    V.config(text=translations[current_lang]['validate'])
    A.config(text=translations[current_lang]['cancel'])
    Q.config(text=translations[current_lang]['quit'])
    texte_complet = translations[current_lang]['title']
    index_lettre = 0
    animation_finie = False
    direction=12
    x_pos = 10
    canvas.itemconfig(texte_id, anchor=NW)
    canvas.itemconfig(texte_id, text="")
    canvas.coords(oiseau_id, x_pos, y_pos)
    mot.delete(0, END)
    mot.insert(0, placeholder_texts[current_lang])
    mot.config(fg='grey')
    animer()
    continuer_mouvement()

def on_entry_click(event):
        if mot.get() == placeholder_texts[current_lang]:
            mot.delete(0, END)
            mot.config(fg='black')

def on_focusout(event):
        if mot.get() == "":
            mot.insert(0, placeholder_texts[current_lang])
            mot.config(fg='grey')

image = Image.open("C:\\Users\\SOUMI\\OneDrive\\Bureau\\xamp\\htdocs\\AppTweet\\images\\img.png")
blurred_image = image.filter(ImageFilter.GaussianBlur(radius=3))
photo = ImageTk.PhotoImage(blurred_image)
label = Label(fen, image=photo)
label.pack()

canvas = Canvas(fen, width=fen.winfo_screenwidth() // 2 + 10, height=fen.winfo_screenheight() // 15, bg="white", highlightthickness=0)
canvas.place(relx=0.5, rely=0.1, anchor="center")

Q = Button(fen, text=translations[current_lang]['quit'], fg="black", bg="red", command=quitter)
Q.place(x=1480, y=0)

selected_lang = StringVar(value='english')
lang_menu = OptionMenu(fen, selected_lang, 'english', 'french', command=changer_langue)
lang_menu.place(x=0, y=0)

cadre_width = fen.winfo_screenwidth() // 4
cadre_height = fen.winfo_screenheight() // 3
Frame(fen, bg="black").place(relx=0.39, rely=0.30, width=cadre_width, height=cadre_height)
Frame(fen, bg="black").place(relx=0.5, rely=0.5, anchor="center", width=cadre_width + 10, height=cadre_height + 10)
cadre = Frame(fen, bg="#A9A9A9")
cadre.place(relx=0.5, rely=0.5, anchor="center", width=cadre_width, height=cadre_height)

lblmot = Label(cadre, text=translations[current_lang]['search'], font=("Comic Sans MS", 12, "underline"))
lblmot.place(relx=0.5, rely=0.2, anchor="center")



# Label pour l'instruction
lblmot = Label(cadre, text=translations[current_lang]['search'], font=("Comic Sans MS", 12, "underline"))
lblmot.place(relx=0.5, rely=0.2, anchor="center")

# Nouveau frame pour l'entrée + bouton image
entry_frame = tk.Frame(cadre, bg="white")
entry_frame.place(relx=0.5, rely=0.5, anchor="center")  # Placé au centre du cadre

# Champ de saisie (mot-clé)
mot = tk.Entry(entry_frame, font=("Helvetica", 13), width=20, bd=0, fg='grey')
mot.grid(row=0, column=0, sticky="w", padx=(0, 0))
mot.insert(0, "Enter a keyword...")


V = Button(cadre, text=translations[current_lang]['validate'], fg="black", bg="#1DA1F2", command=valider)
A = Button(cadre, text=translations[current_lang]['cancel'], fg="black", bg="#1DA1F2", command=annuler)
V.place(relx=0.35, rely=0.8, anchor="center")
A.place(relx=0.65, rely=0.8, anchor="center")

# === Intégration Clavier Virtuel ===
virtual_keyboard = VirtualKeyboard(fen, mot)

keyboard_menu = OptionMenu(fen, StringVar(value="Keyboard"), "Français", "English", "العربية",
                           command=lambda lang: {
                               "Français": virtual_keyboard.create_french_keyboard,
                               "English": virtual_keyboard.create_english_keyboard,
                               "العربية": virtual_keyboard.create_arabic_keyboard
                           }[lang]())


# === Création d’un menu contextuel pour le clavier ===
keyboard_menu_popup = Menu(fen, tearoff=0)
keyboard_menu_popup.add_command(label="Français", command=virtual_keyboard.create_french_keyboard)
keyboard_menu_popup.add_command(label="English", command=virtual_keyboard.create_english_keyboard)
keyboard_menu_popup.add_command(label="العربية", command=virtual_keyboard.create_arabic_keyboard)


# === Bouton image qui affiche le menu de clavier ===
img_button = PhotoImage(file="images/keyboard.png")
btn_keyboard = Button(cadre, image=img_button)



def show_keyboard_menu_direct():
    x = cadre.winfo_rootx()+300
    y = cadre.winfo_rooty() + cadre.winfo_height()-150
    keyboard_menu_popup.tk_popup(x, y)



# Pour le bouton image au début
btn_keyboard.config(command=show_keyboard_menu_direct)


# Bouton image du clavier (à droite de l'entry)
keyboard_image = tk.PhotoImage(file="images/keyboard.png")  # Assure-toi du bon chemin et format
keyboard_button = tk.Button(entry_frame, image=keyboard_image, command=show_keyboard_menu_direct, bd=0, bg="white", activebackground="white")
keyboard_button.image = keyboard_image  # Préserve l'image
keyboard_button.grid(row=0, column=1, padx=(5, 5))

# Pour le bouton à droite de l'entrée
keyboard_button.config(command=show_keyboard_menu_direct)


texte_id = canvas.create_text(10, y_pos, text="", font=("Comic Sans MS", 18, "bold"), fill="Black", anchor=NW)
img = Image.open("C:\\Users\\SOUMI\\OneDrive\\Bureau\\xamp\\htdocs\\AppTweet\\images\\111.jfif").resize((60, 60), Image.LANCZOS)
oiseau_img = ImageTk.PhotoImage(img)
oiseau_id = canvas.create_image(x_pos, y_pos, image=oiseau_img, anchor=NW)
mot.bind("<FocusIn>", on_entry_click)  # Appeler on_entry_click lors du focus
mot.bind("<FocusOut>", on_focusout)  # Appeler on_focusout lors de la sortie du focus
# Ajouter le champ de saisie à la fenêtre
mot.grid()


animer()
fen.mainloop()
 