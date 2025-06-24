<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Vérification de mPDF
if (!class_exists('Mpdf\Mpdf')) {
    die("ERREUR : La bibliothèque mPDF n'est pas installée. Exécutez 'composer require mpdf/mpdf'");
}

// Chemins unifiés
$json_file = "C:/Users/SOUMI/OneDrive/Bureau/xamp/htdocs/AppTweet/resultats_recherche.json";
$image_file = "C:/Users/SOUMI/OneDrive/Bureau/xamp/htdocs/AppTweet/graphe_resulta.png";

// Définir la page active
$page = isset($_GET['page']) ? $_GET['page'] : 'accueil';

// Type de graphique par défaut et traitement du changement de type
// Correction 1 : Chemin par défaut cohérent avec le type par défaut
$graph_type = isset($_GET['graph_type']) ? $_GET['graph_type'] : 'bar';
$image_file = "C:/Users/SOUMI/OneDrive/Bureau/xamp/htdocs/AppTweet/graphe_".$graph_type.".png";

// Correction 2 : Charger les données AVANT de générer le graphique
$data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : null;

// Correction 3 : Générer le graphique si les données existent
if ($data !== null) {
    require_once 'C:/Users/SOUMI/OneDrive/Bureau/xamp/htdocs/AppTweet/site_php/generate_graph.php';
    generateGraph($graph_type, $data);
}
// Traitement de soumission du feedback
if (isset($_POST['submit_feedback'])) {
    // Récupération des données du formulaire
    $feedback_name = htmlspecialchars($_POST['feedback_name'] ?? '');
    $feedback_email = htmlspecialchars($_POST['feedback_email'] ?? '');
    $feedback_comment = htmlspecialchars($_POST['feedback_comment'] ?? '');
    $feedback_rating = (int)($_POST['feedback_rating'] ?? '0');
    
    // Chemin du fichier pour stocker les feedbacks
    $feedback_file = "C:/Users/SOUMI/OneDrive/Bureau/xamp/htdocs/AppTweet/feedbacks.json";
    
    // Création d'un nouvel élément de feedback
    $new_feedback = [
        'name' => $feedback_name,
        'email' => $feedback_email,
        'comment' => $feedback_comment,
        'rating' => $feedback_rating,
        'date' => date('Y-m-d H:i:s')
    ];
    
    // Récupération des feedbacks existants ou création d'un nouveau tableau
    if (file_exists($feedback_file)) {
        $feedbacks = json_decode(file_get_contents($feedback_file), true);
        if (!is_array($feedbacks)) {
            $feedbacks = [];
        }
    } else {
        $feedbacks = [];
    }
    
    // Ajout du nouveau feedback
    $feedbacks[] = $new_feedback;
    
    // Enregistrement dans le fichier JSON
    if (file_put_contents($feedback_file, json_encode($feedbacks, JSON_PRETTY_PRINT))) {
        $feedback_message = "Merci pour votre feedback ! Vos commentaires ont été enregistrés.";
    } else {
        $feedback_message = "Erreur: Impossible d'enregistrer votre feedback.";
    }
}

// Traitement de l'export PDF
if (isset($_POST['export_pdf'])) {
    // Charger les données JSON
    if (!file_exists($json_file)) {
        die("Erreur : Fichier JSON introuvable.");
    }
    
    $json_content = file_get_contents($json_file);
    if ($json_content === false) {
        die("Erreur : Impossible de lire le fichier JSON.");
    }
    
    $data = json_decode($json_content, true);
    if ($data === null) {
        die("Erreur : Format JSON invalide.");
    }

    // Créer le contenu HTML pour le PDF avec un style plus proche de la page principale
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            /* Styles améliorés pour correspondre à la page principale */
            body { 
                font-family: "Courier New", Courier, monospace; 
                margin: 20px; 
                background-color: #f5f5f5;
                color: #333;
            }
            
            .container {
                width: 95%;
                margin: 0 auto;
                background-color: white;
                border: 2px solid #1DA1F2;
                border-radius: 8px;
                padding: 20px;
            }
            
            h1 { 
                color: #1DA1F2; 
                text-align: center;
                border-bottom: 2px dotted #1DA1F2;
                padding-bottom: 10px;
                font-size: 24px;
            }
            
            h2 {
                color: #0D8ECF;
                border-left: 5px solid #1DA1F2;
                padding-left: 10px;
                font-size: 18px;
                margin-top: 20px;
            }
            
            .info-box {
                background-color: #e8f5fe;
                border-left: 5px solid #1DA1F2;
                padding: 10px;
                margin: 10px 0;
            }
            
            .graph-img {
                display: block;
                width: 100%;
                height: auto;
                line-height: 0;
                max-width: 80%;
                border: 2px solid #1DA1F2;
                border-radius: 5px;
                padding: 5px;
                display: block;
                margin: 20px auto;
                background-color: white;
            }
            
            table { 
                border-collapse: collapse; 
                width: 80%; 
                margin: 20px auto;
                border: 2px solid #1DA1F2;
                box-shadow: 3px 3px 0 rgba(0,0,0,0.1);
            }
            
            th, td { 
                border: 1px solid #1DA1F2; 
                padding: 10px; 
                text-align: left; 
            }
            
            th { 
                background-color: #1DA1F2; 
                color: white;
                font-weight: bold;
            }
            
            tr:nth-child(even) {
                background-color: #f2f9fe;
            }
            
            ul { 
                list-style-type: none;
                padding: 0;
                margin: 15px 0;
            }
            
            li {
                border-bottom: 1px dotted #1DA1F2;
                padding: 10px 5px;
                margin-bottom: 5px;
                background-color: #f8f8f8;
                border-left: 3px solid #1DA1F2;
                padding-left: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Analyse de : '.htmlspecialchars($data['search_term'] ?? 'Non spécifié').'</h1>
            
            <div class="info-box">
                <p><strong>Date de l\'analyse :</strong> '.htmlspecialchars($data['date_analysis'] ?? 'Non spécifiée').'</p>
                <p><strong>Tweets analysés :</strong> '.htmlspecialchars($data['total_tweets'] ?? '0').'</p>
            </div>';
    
    // Ajouter l'image si elle existe
    if (file_exists($image_file)) {
        $image_data = base64_encode(file_get_contents($image_file));
        $html .= '<img src="data:image/png;base64,'.$image_data.'" alt="Graphique" class="graph-img">';
    }
    
    // Tableau des sentiments
    $html .= '
            <h2>Distribution des Sentiments</h2>
            <table>
                <tr>
                    <th>Sentiment</th>
                    <th>Pourcentage</th>
                </tr>
                <tr>
                    <td>Positif</td>
                    <td>'.htmlspecialchars($data['sentiment_distribution']['positive'] ?? '0').'%</td>
                </tr>
                <tr>
                    <td>Neutre</td>
                    <td>'.htmlspecialchars($data['sentiment_distribution']['neutral'] ?? '0').'%</td>
                </tr>
                <tr>
                    <td>Négatif</td>
                    <td>'.htmlspecialchars($data['sentiment_distribution']['negative'] ?? '0').'%</td>
                </tr>
            </table>';
    
    // Tweets positifs
    $html .= '<h2>Top Tweets Positifs</h2><ul>';
    foreach ($data['top_tweets']['positive'] as $tweet) {
        $html .= '<li>'.htmlspecialchars($tweet).'</li>';
    }
    $html .= '</ul>';
    
    // Tweets négatifs
    $html .= '<h2>Top Tweets Négatifs</h2><ul>';
    foreach ($data['top_tweets']['negative'] as $tweet) {
        $html .= '<li>'.htmlspecialchars($tweet).'</li>';
    }
    $html .= '</ul>';

    // Tweets neutres
    $html .= '<h2>Top Tweets Neutres</h2><ul>';
    foreach ($data['top_tweets']['neutre'] as $tweet) {
        $html .= '<li>'.htmlspecialchars($tweet).'</li>';
    }
    $html .= '</ul>';

    $html .= '</div></body></html>';
    
    try {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'courier',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
        ]);
        
        $mpdf->SetTitle('Rapport d\'analyse de sentiments - '.htmlspecialchars($data['search_term'] ?? 'Analyse'));
        $mpdf->WriteHTML($html);
        $mpdf->Output('rapport_sentiments_'.date('Y-m-d').'.pdf', 'D');
        exit;
    } catch (Exception $e) {
        die("Erreur lors de la génération du PDF : ".$e->getMessage());
    }
}

// Affichage normal de la page
$data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse de Sentiments Twitter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Style rétro inspiré de l'ancien Twitter */
        body { 
            font-family: "Courier New", Courier, monospace;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        
        /* Bannière d'accueil */
        .welcome-banner {
            background-color: #fff;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            margin-bottom: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            color: #1DA1F2;
            border-bottom: 2px solid #1DA1F2;
        }
        
        /* Barre de navigation */
        .navbar {
            background-color: #1DA1F2;
            overflow: visible;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 100;
            position: relative;
        }
        
        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
            max-width: 1200px;
            margin: 0 auto;
            height:50px;
        }
        
        .nav-buttons {
            display:flex;
            align-items:center;
            height:100%;
            margin-left:auto;
            gap:5px;
        }
        
        .pdf-button, .graph-button, .feedback-button {
            background-color: transparent;
            color: white;
            border: none;
            padding: 0px 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: flex;
            align-items:center;
            min-height:50px;
            justify-content: center;
            height:100%;
            font-size:16px;
        }
        /* Ajoutez ceci pour les icônes dans les boutons */
        .pdf-button i, .graph-button i, .feedback-button i {
            font-size: 20px; /* Taille d'icône uniforme */
        }
        .pdf-button:hover, .graph-button:hover, .feedback-button:hover {
            background-color: #0D8ECF;
        }
        
        /* Logo Twitter */
        .twitter-logo {
            font-size: 28px;
            margin-right: 10px;
        }
        
        /* Style du menu de graphiques */
        .graph-menu-container, .feedback-menu-container {
            position: relative;
            display: flex;
            align-items:center;
            height : 100%;
        }
        
        /* Menu Graphique */
        .graph-menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0px;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            border-radius: 4px;
            z-index: 1100;
            padding: 8px 0;
            border: 1px solid #ddd;
            display: none;
            overflow: visible;
        }

/* Formulaire Feedback */
        .feedback-form {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 500px;
            background-color: white;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
            z-index: 1000;
            padding: 25px;
            display: none;
        }
        
        .feedback-form {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 500px;
            z-index: 1000;
            display: none;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        .graph-menu-dropdown.show, .feedback-form.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        .graph-option {
            display: flex;
            align-items: center;
            width: 80%;
            text-align: left;
            padding: 10px 16px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            align-items: center;
            gap: 10px;
            color: #333;
            font-size: 14px;
            text-decoration: none;
        }
        
        .graph-option:hover {
            background-color: #f8f8f8;
        }
        
        .graph-option i {
            color: #1DA1F2;
            width: 20px;
            text-align: center;
            font-size:16px;

        }
        
        /* Container principal */
        .container {
            width: 90%;
            max-width: 1000px;
            margin: 20px auto;
            background-color: white;
            border: 2px solid #1DA1F2;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 { 
            color: #1DA1F2;
            text-align: center;
            border-bottom: 2px dotted #1DA1F2;
            padding-bottom: 10px;
            font-size: 24px;
        }
        
        h2 {
            color: #0D8ECF;
            border-left: 5px solid #1DA1F2;
            padding-left: 10px;
            font-size: 18px;
            margin-top: 20px;
        }
        
        /* Style de l'image */
        .graph-img {
            max-width: 60%;
            border: 2px solid #1DA1F2;
            border-radius: 5px;
            padding: 5px;
            display: block;
            margin: 20px auto 20px 10%;
            background-color: white;
            box-shadow: 0 0 5px rgba(29, 161, 242, 0.3);
        }
        
        /* Style du tableau */
        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 80%;
            border: 2px solid #1DA1F2;
            box-shadow: 3px 3px 0 rgba(0,0,0,0.1);
        }
        
        th, td {
            border: 1px solid #1DA1F2;
            padding: 10px;
            text-align: left;
        }
        
        th {
            background-color: #1DA1F2;
            color: white;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background-color: #f2f9fe;
        }
        
        /* Style des listes */
        ul {
            list-style-type: none;
            padding: 0;
            margin: 15px 0;
        }
        
        li {
            border-bottom: 1px dotted #1DA1F2;
            padding: 10px 5px;
            margin-bottom: 5px;
            background-color: #f8f8f8;
            border-left: 3px solid #1DA1F2;
            padding-left: 10px;
        }
        
        li:hover {
            background-color: #e8f5fe;
        }
        
        /* Search box */
        .search-box {
            background-color: #e8f5fe;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            max-width: 500px;
            margin: 20px auto;
            box-shadow: 0 2px 10px rgba(29, 161, 242, 0.2);
            border: 2px solid #1DA1F2;
        }
        
        .search-input {
            padding: 10px;
            width: 80%;
            margin-bottom: 15px;
            border: 1px solid #1DA1F2;
            border-radius: 3px;
            font-family: "Courier New", Courier, monospace;
        }
        
        .search-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .search-button {
            background-color: #1DA1F2;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
            box-shadow: 2px 2px 0 #0D8ECF;
        }
        
        .search-button:hover {
            background-color: #0D8ECF;
            transform: translateY(1px);
            box-shadow: 1px 1px 0 #0D8ECF;
        }
        
        /* Message d'erreur */
        .error-msg {
            color: #1DA1F2;
            text-align: center;
            padding: 20px;
            border: 1px dashed #1DA1F2;
            margin: 20px auto;
            width: 80%;
        }
        
        /* Info box */
        .info-box {
            background-color: #e8f5fe;
            border-left: 5px solid #1DA1F2;
            padding: 10px;
            margin: 10px 0;
        }
        
        /* Pour le debugging */
        .debug-info {
            background: #ffeb3b;
            color: #333;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            display: none; /* Caché par défaut */
        }
        
        /* Styles pour le formulaire de feedback */
        .feedback-header {
            text-align: center;
            color: #1DA1F2;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .feedback-form-group {
            margin-bottom: 15px;
        }
        
        .feedback-form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-size: 12px;
        }
        
        .feedback-form-group input, 
        .feedback-form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: "Courier New", Courier, monospace;
        }
        
        .feedback-form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .feedback-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            margin: 15px 0;
        }
        
        .feedback-rating input {
            display: none;
        }
        
        .feedback-rating label {
            color: #ddd;
            font-size: 20px;
            padding: 0 3px;
            cursor: pointer;
        }
        
        .feedback-rating label:hover,
        .feedback-rating label:hover ~ label,
        .feedback-rating input:checked ~ label {
            color: #1DA1F2;
        }
        
        .feedback-submit {
            width: 100%;
            background-color: #1DA1F2;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .feedback-submit:hover {
            background-color: #0D8ECF;
        }
        
        .feedback-message {
            text-align: center;
            margin-top: 10px;
            color: #1DA1F2;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="welcome-banner">
        Welcome! Join us and discover what the world thinks!!
    </div>

    <div class="navbar">
        <div class="navbar-container">
            <!-- Logo Twitter à gauche -->
            <div class="twitter-logo">
                <i class="fab fa-twitter" style="color: white;"></i>
            </div>
            
            <div class="nav-buttons">
                <!-- Bouton de feedback avec l'icône de l'oiseau Twitter -->
                <div class="feedback-menu-container">
                    <button id="feedbackButton" class="feedback-button" title="feedback">
                        <i class="fab fa-twitter"></i>
                    </button>
                    <div class="modal-overlay" id="feedbackOverlay"></div>
                    <div id="feedbackForm" class="feedback-form">
                        <div class="feedback-header">Write a review</div>
                        <form method="post">
                            <div class="feedback-form-group">
                                <label for="feedback_name">Name</label>
                                <input type="text" id="feedback_name" name="feedback_name" required>
                            </div>
                            <div class="feedback-form-group">
                                <label for="feedback_email">Email</label>
                                <input type="email" id="feedback_email" name="feedback_email" required>
                            </div>
                            <div class="feedback-form-group">
                                <label for="feedback_comment">Comment</label>
                                <textarea id="feedback_comment" name="feedback_comment" required></textarea>
                            </div>
                            <div class="feedback-rating">
                                <input type="radio" id="star5" name="feedback_rating" value="5" required>
                                <label for="star5" title="5 oiseaux"><i class="fab fa-twitter"></i></label>
                                <input type="radio" id="star4" name="feedback_rating" value="4">
                                <label for="star4" title="4 oiseaux"><i class="fab fa-twitter"></i></label>
                                <input type="radio" id="star3" name="feedback_rating" value="3">
                                <label for="star3" title="3 oiseaux"><i class="fab fa-twitter"></i></label>
                                <input type="radio" id="star2" name="feedback_rating" value="2">
                                <label for="star2" title="2 oiseaux"><i class="fab fa-twitter"></i></label>
                                <input type="radio" id="star1" name="feedback_rating" value="1">
                                <label for="star1" title="1 oiseau"><i class="fab fa-twitter"></i></label>
                            </div>
                            <button type="submit" name="submit_feedback" class="feedback-submit">Send</button>
                            <?php if (isset($feedback_message)): ?>
                                <div class="feedback-message"><?= $feedback_message ?></div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Bouton du menu graphique -->
                <div class="graph-menu-container">
                    <button id="graphMenuButton" class="graph-button" title="Types of charts" >
                        <i class="fas fa-chart-bar"></i>
                    </button>
                    <div id="graphMenuDropdown" class="graph-menu-dropdown">
                        <a href="?graph_type=bar&page=<?= $page ?>" class="graph-option">
                            <i class="fas fa-chart-bar"></i> Bar
                        </a>
                        <a href="?graph_type=line&page=<?= $page ?>" class="graph-option">
                            <i class="fas fa-chart-line"></i> Line
                        </a>
                        <a href="?graph_type=pie&page=<?= $page ?>" class="graph-option">
                            <i class="fas fa-chart-pie"></i> Sectors
                        </a>
                        <a href="?graph_type=ring&page=<?= $page ?>" class="graph-option">
                            <i class="fas fa-circle-notch"></i> Ring
                        </a>
                    </div>
                </div>
                
                <!-- Bouton d'export PDF -->
                <form method="post" style="margin: 0;">
                    <button type="submit" name="export_pdf" class="pdf-button" title="Exporter en PDF">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Information de débogage (pour tester) -->
    <div id="debugInfo" class="debug-info"></div>

    <div class="container">
        <?php if ($page == 'accueil' && !$data): ?>
            <div class="search-box">
                <h3>Search by keyword</h3>
                <input type="text" class="search-input" placeholder="Enter keywords..." value="">
                <div class="search-buttons">
                    <button class="search-button">Validate</button>
                    <button class="search-button">Cancel</button>
                </div>
            </div>
        <?php elseif ($data !== null): ?>
            <div class="info-box">
                <p><strong>Analysis of        :</strong> <?= htmlspecialchars($data['search_term'] ?? 'Non spécifié') ?></p>
                <p><strong>Date of analysis :</strong> <?= htmlspecialchars($data['date_analysis'] ?? 'Non spécifiée') ?></p>
                <p><strong>Analyzed Tweets  :</strong> <?= htmlspecialchars($data['total_tweets'] ?? '0') ?></p>
                <p><strong>Current chart type:</strong> <?= ucfirst(htmlspecialchars($graph_type)) ?></p>
            </div>
            
            <?php if (file_exists($image_file)): ?>
                <img class="graph-img" src="data:image/png;base64,<?= base64_encode(file_get_contents($image_file)) ?>" alt="Graphique des résultats">
            <?php endif; ?>
            
            <h2>Distribution of Feelings</h2>
            <table>
                <tr>
                    <th>Feelings</th>
                    <th>Percentage</th>
                </tr>
                <tr>
                    <td>Positive</td>
                    <td><?= htmlspecialchars($data['sentiment_distribution']['positive'] ?? '0') ?>%</td>
                </tr>
                <tr>
                    <td>Neutral</td>
                    <td><?= htmlspecialchars($data['sentiment_distribution']['neutral'] ?? '0') ?>%</td>
                </tr>
                <tr>
                    <td>Negative</td>
                    <td><?= htmlspecialchars($data['sentiment_distribution']['negative'] ?? '0') ?>%</td>
                </tr>
            </table>
            
            <h2>Top Positive Tweets</h2>
            <ul>
                <?php foreach ($data['top_tweets']['positive'] as $tweet): ?>
                    <li><?= htmlspecialchars($tweet) ?></li>
                <?php endforeach; ?>
            </ul>
            
            <h2>Top Negative Tweets </h2>
            <ul>
                <?php foreach ($data['top_tweets']['negative'] as $tweet): ?>
                    <li><?= htmlspecialchars($tweet) ?></li>
                <?php endforeach; ?>
            </ul>

            <h2>Top Neutral Tweets </h2>
            <ul>
                <?php foreach ($data['top_tweets']['neutre'] as $tweet): ?>
                    <li><?= htmlspecialchars($tweet) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="error-msg">
                <p>No analytical data available.</p>
                <p>Please search to generate results.</p>
            </div>
        <?php endif; ?>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Références aux éléments
    const graphBtn = document.getElementById('graphMenuButton');
    const graphMenu = document.getElementById('graphMenuDropdown');
    const feedbackBtn = document.getElementById('feedbackButton');
    const feedbackForm = document.getElementById('feedbackForm');
    const feedbackOverlay = document.getElementById('feedbackOverlay');
    
    // Fonction pour fermer tous les menus
    function closeAllMenus() {
        graphMenu.classList.remove('show');
        feedbackForm.classList.remove('show');
        feedbackOverlay.style.display = 'none';
    }
    
    // Gestion du menu graphique (version permettant la coexistence)
    graphBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        graphMenu.classList.toggle('show');
        
        // Ferme l'overlay si ouvert mais garde le feedback visible
        if(feedbackOverlay.style.display === 'block') {
            feedbackOverlay.style.display = 'none';
        }
    });
    
    // Gestion du feedback (version permettant la coexistence)
    feedbackBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        feedbackForm.classList.toggle('show');
        feedbackOverlay.style.display = 
            feedbackForm.classList.contains('show') ? 'block' : 'none';
    });
    
    // Fermeture en cliquant sur l'overlay
    feedbackOverlay.addEventListener('click', closeAllMenus);
    
    // Fermer les menus quand on clique ailleurs
    document.addEventListener('click', function(e) {
        // Ne se ferme que si on ne clique pas sur un élément du menu
        if (!e.target.closest('.graph-menu-container') && 
            !e.target.closest('.feedback-menu-container')) {
            closeAllMenus();
        }
    });
    
    // Empêcher la fermeture quand on clique dans les menus
    graphMenu.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    feedbackForm.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Fermer avec la touche Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllMenus();
        }
    });
    
    // Fermer le menu graphique quand on choisit une option
    document.querySelectorAll('.graph-option').forEach(option => {
    option.addEventListener('click', function() {
        // CONSERVEZ cette ligne qui ferme le menu
        graphMenu.classList.remove('show');
        
        // AJOUTEZ cette ligne pour forcer le rechargement
        window.location.href = this.href;
    });
    });
});
<?php 
$feedbacks = json_decode(file_get_contents('C:/Users/SOUMI/OneDrive/Bureau/xamp/htdocs/AppTweet/feedbacks.json'), true);
$lastFeedback = end($feedbacks);
?>
</script>
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
<script>
    // Vérifiez que le fichier feedbacks.json existe et est lisible
    <?php
    $feedback_file = 'C:/Users/SOUMI/OneDrive/Bureau/xamp/htdocs/AppTweet/feedbacks.json';
    $feedbacks = file_exists($feedback_file) ? json_decode(file_get_contents($feedback_file), true) : [];
    $lastFeedback = !empty($feedbacks) ? end($feedbacks) : null;
    ?>
    
    <?php if ($lastFeedback): ?>
        const lastEntry = <?php echo json_encode($lastFeedback, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        document.write("email avec succes");
        const PUBLIC_KEY = "UYVY9zDxMKA0GJgAL";

        // Initialisation après vérification que emailjs est chargé
        if (typeof emailjs !== 'undefined') {
            emailjs.init(PUBLIC_KEY);
            
            const rating = parseInt(lastEntry.rating) || 0;
            
            if (rating > 3) {
                emailjs.send('service_a3a91we', 'template_rjqnytf', {
                    name: lastEntry.name,
                    email: lastEntry.email
                }, PUBLIC_KEY)
                .then(() => console.log('✅ Email envoyé'))
                .catch(console.error);
            } else {
                emailjs.send('service_a3a91we', 'template_2cwa0r8', {
                    name: lastEntry.name,
                    email: lastEntry.email
                }, PUBLIC_KEY)
                .then(() => console.log('✅ Email envoyé (rating bas)'))
                .catch(console.error);
            }
        } else {
            console.error("EmailJS n'est pas chargé");
        }
    <?php else: ?>
        console.warn("Aucun feedback trouvé");
    <?php endif; ?>
</script>
</body>
</html>