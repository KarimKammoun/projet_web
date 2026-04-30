<?php
include_once '../includes/auth.php';
require_login();

$user = get_current_user();

// Vérifier que $user est un array valide
if (!$user || !is_array($user)) {
    $user = array(
        'id_utilisateur' => $_SESSION['utilisateur_id'],
        'email' => $_SESSION['utilisateur_email'] ?? 'Email indisponible',
        'nom_complet' => $_SESSION['utilisateur_nom'] ?? 'Utilisateur',
        'date_creation' => date('Y-m-d H:i:s'),
        'seuil_notification' => 5,
        'notifications_activees' => 1
    );
} else {
    // S'assurer que les colonnes de notification existent
    if (!isset($user['seuil_notification'])) {
        $user['seuil_notification'] = 5;
    }
    if (!isset($user['notifications_activees'])) {
        $user['notifications_activees'] = 1;
    }
}

$message = '';
$type_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Changement d'email
    if (isset($_POST['action']) && $_POST['action'] === 'email') {
        $new_email = $_POST['new_email'] ?? '';
        
        if (empty($new_email)) {
            $message = 'Veuillez entrer un email.';
            $type_message = 'danger';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Email invalide.';
            $type_message = 'danger';
        } else {
            $result = update_user_email($user['id_utilisateur'], $new_email);
            $message = $result['message'];
            $type_message = $result['success'] ? 'success' : 'danger';
            
            if ($result['success']) {
                $user_updated = get_current_user();
                if ($user_updated && is_array($user_updated)) {
                    $user = $user_updated;
                }
            }
        }
    }
    
    // Changement de mot de passe
    if (isset($_POST['action']) && $_POST['action'] === 'password') {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            $message = 'Tous les champs sont obligatoires.';
            $type_message = 'danger';
        } elseif ($new_password !== $confirm_password) {
            $message = 'Les nouveaux mots de passe ne correspondent pas.';
            $type_message = 'danger';
        } else {
            $result = update_user_password($user['id_utilisateur'], $old_password, $new_password);
            $message = $result['message'];
            $type_message = $result['success'] ? 'success' : 'danger';
        }
    }
    
    // Changement des paramètres de notification
    if (isset($_POST['action']) && $_POST['action'] === 'notification') {
        $threshold = $_POST['seuil_notification'] ?? 5;
        $enable = isset($_POST['notifications_activees']) ? 1 : 0;
        
        $threshold = (int) $threshold;
        if ($threshold < 0 || empty($threshold)) {
            $threshold = 5;
        }
        if ($threshold > 1000) {
            $threshold = 1000;
        }
        
        $result = update_notification_threshold($user['id_utilisateur'], $threshold, $enable);
        $message = $result['message'];
        $type_message = $result['success'] ? 'success' : 'danger';
        
        if ($result['success']) {
            $user_updated = get_current_user();
            if ($user_updated && is_array($user_updated)) {
                $user = $user_updated;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Magasin Informatique</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Navigation</h2>
            <a href="../index.php">🔙 Retour</a>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>⚙️ Paramètres du Compte</h1>
                <p>Modifiez votre email et votre mot de passe</p>
            </div>

            <div class="content" style="max-width: 700px;">
                <?php if ($message != ''): ?>
                    <div class="alert alert-<?php echo $type_message; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Informations du compte -->
                <div style="background: linear-gradient(135deg, #f8f9fa, #fff); border-radius: 12px; padding: 25px; margin-bottom: 30px; border: 1px solid #e9ecef;">
                    <h3 style="color: #333; margin-bottom: 15px; font-size: 16px;">📋 Informations du Compte</h3>
                    <div style="display: grid; gap: 15px;">
                        <div>
                            <p style="color: #666; font-size: 13px; margin: 0 0 5px 0;">👤 Nom complet</p>
                            <p style="color: #333; font-weight: 600; font-size: 15px; margin: 0;">
                                <?php echo htmlspecialchars($user['nom_complet']); ?>
                            </p>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 13px; margin: 0 0 5px 0;">📧 Email actuel</p>
                            <p style="color: #0d6efd; font-weight: 600; font-size: 15px; margin: 0;">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 13px; margin: 0 0 5px 0;">📅 Date d'inscription</p>
                            <p style="color: #333; font-size: 15px; margin: 0;">
                                <?php echo date('d/m/Y à H:i', strtotime($user['date_creation'])); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Changement d'email -->
                <div style="background: linear-gradient(135deg, #fff, #f8f9fa); border-radius: 12px; padding: 25px; margin-bottom: 25px; border: 1px solid #e9ecef;">
                    <h3 style="color: #333; margin-bottom: 20px; font-size: 16px;">📧 Changer l'Email</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="email">
                        <div class="form-group">
                            <label for="new_email">Nouvel Email *</label>
                            <input type="email" id="new_email" name="new_email" placeholder="Entrez votre nouvel email" required>
                        </div>
                        <button type="submit" class="btn" style="padding: 11px 24px;">✅ Mettre à jour l'Email</button>
                    </form>
                </div>

                <!-- Changement de mot de passe -->
                <div style="background: linear-gradient(135deg, #fff, #f8f9fa); border-radius: 12px; padding: 25px; border: 1px solid #e9ecef;">
                    <h3 style="color: #333; margin-bottom: 20px; font-size: 16px;">🔐 Changer le Mot de Passe</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="password">
                        <div class="form-group">
                            <label for="old_password">Ancien Mot de Passe *</label>
                            <input type="password" id="old_password" name="old_password" placeholder="Entrez votre mot de passe actuel" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Nouveau Mot de Passe *</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Au moins 8 caractères" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le Mot de Passe *</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez le nouveau mot de passe" required>
                        </div>
                        <div style="display: flex; gap: 12px; margin-top: 20px;">
                            <button type="submit" class="btn">✅ Mettre à jour le Mot de Passe</button>
                            <a href="../index.php" class="btn btn-secondary" style="text-align: center;">❌ Annuler</a>
                        </div>
                    </form>
                </div>

                <!-- Paramètres de notifications -->
                <div style="background: linear-gradient(135deg, #fff, #f8f9fa); border-radius: 12px; padding: 25px; border: 1px solid #e9ecef; margin-top: 25px;">
                    <h3 style="color: #333; margin-bottom: 20px; font-size: 16px;">🔔 Paramètres de Notifications</h3>
                    <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                        Recevez une notification par email quand un produit a un stock inférieur à votre seuil.
                    </p>
                    <form method="POST">
                        <input type="hidden" name="action" value="notification">
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="notifications_activees" 
                                    <?php echo (isset($user['notifications_activees']) && $user['notifications_activees'] == 1) ? 'checked' : ''; ?>
                                    style="width: 20px; height: 20px; cursor: pointer;">
                                <span style="color: #333; font-weight: 500;">🔊 Activer les notifications par email</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label for="seuil_notification">📊 Seuil d'alerte (quantité minimum)</label>
                            <input type="number" 
                                id="seuil_notification" 
                                name="seuil_notification" 
                                min="0" 
                                max="1000" 
                                value="<?php echo isset($user['seuil_notification']) && is_numeric($user['seuil_notification']) ? htmlspecialchars($user['seuil_notification']) : 5; ?>"
                                placeholder="5"
                                style="font-size: 18px; padding: 12px;">
                            <small style="color: #666; display: block; margin-top: 8px;">
                                Vous recevrez une alerte email si un produit a une quantité inférieure à cette valeur.
                            </small>
                        </div>
                        
                        <div style="background-color: #cfe2ff; border-left: 5px solid #0d6efd; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; color: #084298;">
                            <strong>ℹ️ Exemple:</strong> Si vous fixez le seuil à 5, vous recevrez une notification quand un produit aura 4, 3, 2, 1 ou 0 unités en stock.
                        </div>
                        
                        <button type="submit" class="btn" style="padding: 11px 24px;">✅ Enregistrer les Paramètres</button>
                    </form>
                </div>

                <!-- Configuration SMTP -->
                <div style="background: linear-gradient(135deg, #fff, #f8f9fa); border-radius: 12px; padding: 25px; border: 1px solid #e9ecef; margin-top: 25px;">
                    <h3 style="color: #333; margin-bottom: 15px; font-size: 16px;">📧 Configuration SMTP</h3>
                    <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                        Gérez la configuration de votre serveur SMTP pour l'envoi d'emails.
                    </p>
                    <a href="../config_smtp.php" class="btn" style="padding: 11px 24px; text-decoration: none; display: inline-block;">⚙️ Configurer SMTP</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
