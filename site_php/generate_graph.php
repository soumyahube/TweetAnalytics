<?php
function generateGraph($type, $data) {
    // Chemin de sortie
    $outputDir = __DIR__ . '/../graphe/';

    // Crée le dossier s'il n'existe pas (avec droits d'écriture)
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    $outputFile = $outputDir . "graphe_" . $type . ".png";
    
    // Dimensions
    $width = 800;
    $height = 500;
    
    // Création de l'image
    $image = imagecreatetruecolor($width, $height);
    
    // Couleurs personnalisées selon vos préférences
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $darkGray = imagecolorallocate($image, 100, 100, 100);
    
    // Vos couleurs préférées (bleu clair, bleu foncé, gris)
    $colors = [
        'positive' => imagecolorallocate($image, 0x4f, 0xc3, 0xf7), // #4fc3f7
        'neutral' => imagecolorallocate($image, 0x9E, 0x9E, 0x9E),  // #9E9E9E
        'negative' => imagecolorallocate($image, 0x2e, 0x86, 0xc1)  // #2e86c1
    ];
    
    // Remplissage du fond
    imagefill($image, 0, 0, $white);
    
    // Données
    $values = [
        'positive' => $data['sentiment_distribution']['positive'],
        'neutral' => $data['sentiment_distribution']['neutral'],
        'negative' => $data['sentiment_distribution']['negative']
    ];
    
    // Police (vérifiez le chemin)
    $font = 'C:/Windows/Fonts/arial.ttf';
    if (!file_exists($font)) {
        $font = 'arial.ttf'; // Essaye dans le même dossier
    }
    
    try {
        // Labels
        $labels = [
            'positive' => 'Positive',
            'neutral' => 'Neutral', 
            'negative' => 'Negative'
        ];
        
        switch($type) {
            case 'bar':
                // Paramètres des barres (style original)
                $barWidth = 60;  // Largeur des barres réduite
                $startX = 150;
                $maxHeight = 300;
                $baseY = 400;
                
                // Couleurs spécifiques pour les barres seulement
                $barColors = [
                    imagecolorallocate($image, 0x4f, 0xc3, 0xf7), // Bleu clair (#4fc3f7)
                    imagecolorallocate($image, 0x9E, 0x9E, 0x9E),  // Gris (#9E9E9E)
                    imagecolorallocate($image, 0x2e, 0x86, 0xc1)  // Bleu foncé (#2e86c1)
                ];
                
                // Axes simplifiés
                imageline($image, 100, $baseY, 700, $baseY, $black); // Axe X
                imageline($image, 100, $baseY, 100, 100, $black);    // Axe Y
                
                // Barres - CORRECTION : conversion en entiers
                foreach (array_values($values) as $index => $value) {
                    $barHeight = ($value / 100) * $maxHeight;
                    $x1 = (int)($startX + ($index * 120)); // Espacement réduit entre barres
                    $y1 = (int)($baseY - $barHeight);      // CONVERSION EN ENTIER
                    $x2 = (int)($x1 + $barWidth);
                    $y2 = (int)$baseY;                     // CONVERSION EN ENTIER
                    
                    // Barre simple sans effet d'ombre
                    imagefilledrectangle($image, $x1, $y1, $x2, $y2, $barColors[$index]);
                    imagerectangle($image, $x1, $y1, $x2, $y2, $black); // Contour noir
                    
                    // Étiquettes
                    if (function_exists('imagettftext')) {
                        // Valeur au-dessus de la barre
                        $text = $value.'%';
                        $textX = (int)($x1 + ($barWidth/2) - 10); // CONVERSION EN ENTIER
                        $textY = (int)($y1 - 10);                 // CONVERSION EN ENTIER
                        imagettftext($image, 10, 0, $textX, $textY, $black, $font, $text);
                        
                        // Label en dessous
                        $label = ['Positive', 'Neutral', 'Negative'][$index];
                        $labelX = (int)($x1 + 5);                 // CONVERSION EN ENTIER
                        $labelY = (int)($baseY + 20);             // CONVERSION EN ENTIER
                        imagettftext($image, 10, 0, $labelX, $labelY, $black, $font, $label);
                    }
                }
                break;
                
            case 'pie':
            case 'ring':
                // Camembert ou Anneau avec vos couleurs
                $centerX = (int)($width / 2);
                $centerY = (int)($height / 2);
                $radius = 150;
                $innerRadius = ($type === 'ring') ? 80 : 0;
                $total = array_sum($values);
                $startAngle = 0;
                
                // Dessin des sections
                foreach ($values as $key => $value) {
                    if ($value == 0) continue;
                    
                    $endAngle = $startAngle + (360 * ($value / $total));
                    
                    // Section principale
                    imagefilledarc($image, $centerX, $centerY, $radius*2, $radius*2, 
                                  (int)$startAngle, (int)$endAngle, $colors[$key], IMG_ARC_PIE);
                    
                    // Pour l'anneau
                    if ($type === 'ring') {
                        imagefilledarc($image, $centerX, $centerY, $innerRadius*2, $innerRadius*2, 
                                      0, 360, $white, IMG_ARC_PIE);
                    }
                    
                    // Légende
                    $midAngle = ($startAngle + $endAngle) / 2;
                    $labelRadius = ($radius + $innerRadius) / 2;
                    $labelX = (int)($centerX + cos(deg2rad($midAngle)) * ($labelRadius + 30));
                    $labelY = (int)($centerY + sin(deg2rad($midAngle)) * ($labelRadius + 30));
                    
                    if (function_exists('imagettftext')) {
                        $text = sprintf("%s (%.1f%%)", $labels[$key], $value);
                        $textWidth = imagettfbbox(12, 0, $font, $text);
                        $textX = (int)($labelX - ($textWidth[4]/2));
                        imagettftext($image, 12, 0, $textX, $labelY, $black, $font, $text);
                    }
                    
                    $startAngle = $endAngle;
                }
                
                // Contour
                imagearc($image, $centerX, $centerY, $radius*2, $radius*2, 0, 360, $black);
                if ($type === 'ring') {
                    imagearc($image, $centerX, $centerY, $innerRadius*2, $innerRadius*2, 0, 360, $black);
                }
                break;
                
            case 'line':
                // Graphique en courbes avec vos couleurs
                $startX = 150;
                $endX = 650;
                $baseY = 400;
                $maxHeight = 250;
                
                // Axes
                imageline($image, $startX, $baseY, $endX, $baseY, $black);
                imageline($image, $startX, $baseY, $startX, 140, $black);
                
                // Graduations
                for ($i = 0; $i <= 100; $i += 20) {
                    $y = (int)($baseY - ($i / 100 * $maxHeight));
                    imageline($image, $startX - 5, $y, $startX, $y, $black);
                    if (function_exists('imagettftext')) {
                        imagettftext($image, 10, 0, $startX - 40, $y + 5, $black, $font, $i.'%');
                    }
                }
                
                // Points et ligne
                $pointValues = array_values($values);
                $pointCount = count($pointValues);
                $step = ($endX - $startX) / ($pointCount - 1);
                
                $points = [];
                foreach ($pointValues as $index => $value) {
                    $x = (int)($startX + ($index * $step));
                    $y = (int)($baseY - ($value / 100 * $maxHeight));
                    $points[] = ['x' => $x, 'y' => $y, 'value' => $value, 'color' => array_values($colors)[$index]];
                }
                
                // Ligne bleu clair (#4fc3f7)
                if (count($points) > 1) {
                    imagesetthickness($image, 3);
                    $prevPoint = $points[0];
                    for ($i = 1; $i < count($points); $i++) {
                        imageline($image, $prevPoint['x'], $prevPoint['y'], 
                                 $points[$i]['x'], $points[$i]['y'], $colors['positive']);
                        $prevPoint = $points[$i];
                    }
                }
                
                // Points
                foreach ($points as $index => $point) {
                    imagefilledellipse($image, $point['x'], $point['y'], 12, 12, $point['color']);
                    imageellipse($image, $point['x'], $point['y'], 12, 12, $black);
                    
                    if (function_exists('imagettftext')) {
                        // Valeur
                        imagettftext($image, 12, 0, $point['x'] - 15, $point['y'] - 20, $black, $font, $point['value'].'%');
                        // Label
                        imagettftext($image, 12, 0, $point['x'] - 20, $baseY + 30, $black, $font, array_keys($values)[$index]);
                    }
                }
                break;
        }
        
        // Titre et informations
        if (function_exists('imagettftext')) {
            imagettftext($image, 16, 0, 20, 30, $black, $font, "Sentiment Analysis: ". htmlspecialchars($data['search_term'] ?? 'Not specified'));
            imagettftext($image, 12, 0, 20, 55, $darkGray, $font, "Total tweets analyzed: ".$data['total_tweets']);
            
            // Légende avec vos couleurs
            $legendX = 600;
            $legendY = 80;
            foreach ($labels as $key => $label) {
                imagefilledrectangle($image, $legendX, $legendY, $legendX + 20, $legendY + 20, $colors[$key]);
                imagerectangle($image, $legendX, $legendY, $legendX + 20, $legendY + 20, $black);
                imagettftext($image, 12, 0, $legendX + 30, $legendY + 15, $black, $font, $label);
                $legendY += 30;
            }
        }
        
        // Bordure
        imagerectangle($image, 0, 0, $width-1, $height-1, $darkGray);
        
        // Sauvegarde
        if (!imagepng($image, $outputFile)) {
            throw new Exception("Erreur lors de l'enregistrement de l'image");
        }
        
        imagedestroy($image);
        
        if (!file_exists($outputFile)) {
            throw new Exception("Le fichier image n'a pas été créé");
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Erreur dans generateGraph(): ".$e->getMessage());
        if (isset($image)) imagedestroy($image);
        
        // Image d'erreur simple
        $errorImage = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($errorImage, 255, 200, 200);
        $textColor = imagecolorallocate($errorImage, 255, 0, 0);
        imagefill($errorImage, 0, 0, $bgColor);
        imagestring($errorImage, 5, 10, 10, "Erreur: ".$e->getMessage(), $textColor);
        imagepng($errorImage, $outputFile);
        imagedestroy($errorImage);
        
        return false;
    }
}
?>