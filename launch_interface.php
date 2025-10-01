<?php
// launch_interface.php
header('Content-Type: text/html; charset=utf-8');

$python_script = "C:/wamp64/www/AppTweet/InterfaceProj.py";

echo "<h3>🔍 Recherche de Python...</h3>";

// Test direct avec where (commande Windows)
echo "<p>Test de la commande 'where python' : ";
$where_result = shell_exec('where python 2>&1');
echo htmlspecialchars($where_result ?: "Non trouvé") . "</p>";

// Test avec python --version
echo "<p>Test de 'python --version' : ";
$version_result = shell_exec('python --version 2>&1');
echo htmlspecialchars($version_result ?: "Échec") . "</p>";

// Essayer avec le chemin complet le plus courant
echo "<p>Tentative avec chemin complet : ";
$python_exe = "C:/Users/" . get_current_user() . "/AppData/Local/Microsoft/WindowsApps/python.exe";
if (file_exists($python_exe)) {
    $test = shell_exec('"' . $python_exe . '" --version 2>&1');
    echo htmlspecialchars($test ?: "Échec") . "</p>";
} else {
    echo "Fichier non trouvé : $python_exe</p>";
}

echo "<h3>🚀 Lancement de l'application...</h3>";

// Méthode la plus fiable : créer un shortcut
$shortcut_content = '[InternetShortcut]
URL=file:///C:/wamp64/www/AppTweet/InterfaceProj.py
IconIndex=0';

$shortcut_file = "C:/wamp64/www/AppTweet/launch_app.url";
if (file_put_contents($shortcut_file, $shortcut_content)) {
    echo "<p>✓ Shortcut créé : <a href='launch_app.url' target='_blank'>Cliquez ici pour lancer l'application</a></p>";
}

echo "<p><strong>Instructions :</strong></p>";
echo "<ol>";
echo "<li><a href='launch_app.url' target='_blank'>Cliquez sur ce lien</a> et choisissez 'Ouvrir avec Python'</li>";
echo "<li>OU retournez dans le dossier et double-cliquez sur InterfaceProj.py</li>";
echo "<li>OU ouvrez un terminal dans C:\\wamp64\\www\\AppTweet\\ et tapez : python InterfaceProj.py</li>";
echo "</ol>";

echo "<script>
setTimeout(() => {
    if(confirm('Fermer cet onglet et lancer l\\'application manuellement ?')) {
        window.close();
    }
}, 3000);
</script>";
?>