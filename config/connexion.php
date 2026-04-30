<?php

$serveur = "localhost";
$utilisateur = "root";
$motdepasse = "";
$base_donnees = "magasin_informatique";

$connexion = new mysqli($serveur, $utilisateur, $motdepasse, $base_donnees);

if ($connexion->connect_error) {
    die("Erreur de connexion: " . $connexion->connect_error);
}

$connexion->set_charset("utf8mb4");

/**
 * Initialisation automatique du compte administrateur
 * Exécuté une seule fois au premier accès
 */
function initialize_admin($connexion) {
    // Vérifier si l'admin existe déjà
    $check_sql = "SELECT id_utilisateur FROM utilisateurs WHERE email = 'admin@magasin.com' LIMIT 1";
    $result = $connexion->query($check_sql);
    
    if ($result && $result->num_rows > 0) {
        return; // L'admin existe déjà
    }
    
    // Créer l'admin avec mot de passe hashé
    $password = 'Admin@123';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    $insert_sql = "INSERT INTO utilisateurs (email, mot_de_passe, nom_complet, seuil_notification, notifications_activees) 
                   VALUES ('admin@magasin.com', ?, 'Propriétaire Magasin', 5, 1)";
    
    $stmt = $connexion->prepare($insert_sql);
    if ($stmt) {
        $stmt->bind_param('s', $hash);
        $stmt->execute();
        $stmt->close();
    }
}

// Initialiser l'admin automatiquement
initialize_admin($connexion);

?>
