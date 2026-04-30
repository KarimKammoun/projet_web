<?php
/**
 * Gestion de l'authentification et des sessions
 */

if (!isset($_SESSION)) {
    session_start();
}

/**
 * Vérifier si l'utilisateur est connecté
 */
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['utilisateur_id']) && !empty($_SESSION['utilisateur_id']);
    }
}

/**
 * Obtenir l'utilisateur actuel
 */
if (!function_exists('get_current_user')) {
    function get_current_user() {
        if (!is_logged_in()) {
            return null;
        }
        
        include_once dirname(__FILE__) . '/../config/connexion.php';
        
        if (!isset($connexion) || $connexion === null) {
            return null;
        }
        
        $user_id = $_SESSION['utilisateur_id'];
        
        if (empty($user_id)) {
            return null;
        }
        
        $sql = "SELECT * FROM utilisateurs WHERE id_utilisateur = ?";
        $stmt = $connexion->prepare($sql);
        
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param('i', $user_id);
        
        if (!$stmt->execute()) {
            return null;
        }
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && is_array($user)) {
            return $user;
        }
        
        return null;
    }
}

/**
 * Authentifier un utilisateur
 */
if (!function_exists('authenticate')) {
    function authenticate($email, $password) {
        include_once dirname(__FILE__) . '/../config/connexion.php';
        
        $email = trim($email);
        $sql = "SELECT id_utilisateur, email, mot_de_passe, nom_complet FROM utilisateurs WHERE email = ?";
        $stmt = $connexion->prepare($sql);
        
        if (!$stmt) {
            return array('success' => false, 'message' => 'Erreur de préparation de la requête.');
        }
        
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $utilisateur = $result->fetch_assoc();
        
        if ($utilisateur && password_verify($password, $utilisateur['mot_de_passe'])) {
            $_SESSION['utilisateur_id'] = $utilisateur['id_utilisateur'];
            $_SESSION['utilisateur_email'] = $utilisateur['email'];
            $_SESSION['utilisateur_nom'] = $utilisateur['nom_complet'];
            return array('success' => true, 'message' => 'Connexion réussie.');
        }
        
        return array('success' => false, 'message' => 'Email ou mot de passe incorrect.');
    }
}

/**
 * Déconnecter l'utilisateur
 */
if (!function_exists('logout')) {
    function logout() {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

/**
 * Redirection si non authentifié
 */
if (!function_exists('require_login')) {
    function require_login() {
        if (!is_logged_in()) {
            header('Location: login.php');
            exit;
        }
    }
}

/**
 * Redirection si déjà authentifié
 */
if (!function_exists('require_logout')) {
    function require_logout() {
        if (is_logged_in()) {
            header('Location: index.php');
            exit;
        }
    }
}

/**
 * Mettre à jour l'email de l'utilisateur
 */
if (!function_exists('update_user_email')) {
    function update_user_email($user_id, $new_email) {
        include_once dirname(__FILE__) . '/../config/connexion.php';
        
        $new_email = trim($new_email);
        
        // Vérifier que l'email n'existe pas déjà
        $check_sql = "SELECT id_utilisateur FROM utilisateurs WHERE email = ? AND id_utilisateur != ?";
        $stmt = $connexion->prepare($check_sql);
        
        if (!$stmt) {
            return array('success' => false, 'message' => 'Erreur de préparation de la requête.');
        }
        
        $stmt->bind_param('si', $new_email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return array('success' => false, 'message' => 'Cet email est déjà utilisé.');
        }
        
        // Mettre à jour l'email
        $update_sql = "UPDATE utilisateurs SET email = ? WHERE id_utilisateur = ?";
        $stmt = $connexion->prepare($update_sql);
        
        if (!$stmt) {
            return array('success' => false, 'message' => 'Erreur de préparation de la requête.');
        }
        
        $stmt->bind_param('si', $new_email, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['utilisateur_email'] = $new_email;
            return array('success' => true, 'message' => 'Email modifié avec succès.');
        } else {
            return array('success' => false, 'message' => 'Erreur lors de la mise à jour.');
        }
    }
}

/**
 * Mettre à jour le mot de passe de l'utilisateur
 */
if (!function_exists('update_user_password')) {
    function update_user_password($user_id, $old_password, $new_password) {
        include_once dirname(__FILE__) . '/../config/connexion.php';
        
        // Vérifier l'ancien mot de passe
        $sql = "SELECT mot_de_passe FROM utilisateurs WHERE id_utilisateur = ?";
        $stmt = $connexion->prepare($sql);
        
        if (!$stmt) {
            return array('success' => false, 'message' => 'Erreur de préparation de la requête.');
        }
        
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user || !password_verify($old_password, $user['mot_de_passe'])) {
            return array('success' => false, 'message' => 'Ancien mot de passe incorrect.');
        }
        
        // Valider le nouveau mot de passe (au moins 8 caractères)
        if (strlen($new_password) < 8) {
            return array('success' => false, 'message' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
        }
        
        // Hash le nouveau mot de passe
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        // Mettre à jour le mot de passe
        $update_sql = "UPDATE utilisateurs SET mot_de_passe = ? WHERE id_utilisateur = ?";
        $stmt = $connexion->prepare($update_sql);
        
        if (!$stmt) {
            return array('success' => false, 'message' => 'Erreur de préparation de la requête.');
        }
        
        $stmt->bind_param('si', $new_hash, $user_id);
        
        if ($stmt->execute()) {
            return array('success' => true, 'message' => 'Mot de passe modifié avec succès.');
        } else {
            return array('success' => false, 'message' => 'Erreur lors de la mise à jour.');
        }
    }
}

/**
 * Mettre à jour le seuil de notification
 */
if (!function_exists('update_notification_threshold')) {
    function update_notification_threshold($user_id, $threshold, $enable = 1) {
        include_once dirname(__FILE__) . '/../config/connexion.php';
        
        $threshold = (int) $threshold;
        if ($threshold < 0) $threshold = 0;
        if ($threshold > 1000) $threshold = 1000;
        
        $enable = (bool) $enable ? 1 : 0;
        
        $update_sql = "UPDATE utilisateurs SET seuil_notification = ?, notifications_activees = ? WHERE id_utilisateur = ?";
        $stmt = $connexion->prepare($update_sql);
        
        if (!$stmt) {
            return array('success' => false, 'message' => 'Erreur de préparation de la requête.');
        }
        
        $stmt->bind_param('iii', $threshold, $enable, $user_id);
        
        if ($stmt->execute()) {
            return array('success' => true, 'message' => 'Paramètres de notification mis à jour.');
        } else {
            return array('success' => false, 'message' => 'Erreur lors de la mise à jour.');
        }
    }
}

/**
 * Envoyer une notification par email pour stock bas
 */
if (!function_exists('send_stock_notification')) {
    function send_stock_notification($user_email, $product_name, $current_stock, $threshold) {
        // Charger le service d'email
        if (!class_exists('EmailService')) {
            include_once dirname(__FILE__) . '/email_service.php';
        }
        
        $subject = "🚨 Alerte Stock Bas - " . $product_name;
        
        $body = "Bonjour,\n\n";
        $body .= "⚠️ ALERTE STOCK BAS\n\n";
        $body .= "Le produit '$product_name' a un stock en dessous du seuil défini.\n\n";
        $body .= "Détails:\n";
        $body .= "- Produit: $product_name\n";
        $body .= "- Stock actuel: $current_stock\n";
        $body .= "- Seuil d'alerte: $threshold\n\n";
        $body .= "Veuillez réapprovisionner ce produit dès que possible.\n\n";
        $body .= "Cordialement,\nGestion de Stock - Magasin Informatique";
        
        return EmailService::send($user_email, $subject, $body, false);
    }
}

/**
 * Vérifier les produits avec stock bas et envoyer notifications
 */
if (!function_exists('check_and_notify_low_stock')) {
    function check_and_notify_low_stock() {
        include_once dirname(__FILE__) . '/../config/connexion.php';
        
        // Récupérer tous les utilisateurs avec notifications activées
        $sql = "SELECT id_utilisateur, email, seuil_notification FROM utilisateurs 
                WHERE notifications_activees = 1 AND seuil_notification > 0";
        
        $result = $connexion->query($sql);
        
        if (!$result) {
            return false;
        }
        
        while ($user = $result->fetch_assoc()) {
            // Vérifier les produits de cet utilisateur avec stock bas
            $product_sql = "SELECT id_produit, designation, quantite_stock 
                           FROM produits 
                           WHERE quantite_stock < ? AND quantite_stock > 0";
            
            $stmt = $connexion->prepare($product_sql);
            if (!$stmt) continue;
            
            $threshold = $user['seuil_notification'];
            $stmt->bind_param('i', $threshold);
            $stmt->execute();
            $products = $stmt->get_result();
            
            // Envoyer une notification pour chaque produit
            while ($product = $products->fetch_assoc()) {
                send_stock_notification(
                    $user['email'],
                    $product['designation'],
                    $product['quantite_stock'],
                    $threshold
                );
            }
        }
        
        return true;
    }
}
?>

