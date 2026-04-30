<?php
/**
 * Configuration SMTP Interactive
 * Formulaire pour configurer l'envoi d'emails
 */

include_once 'includes/auth.php';
require_login();

$user = get_current_user();

// Fallback si get_current_user() échoue
if (!$user || !is_array($user)) {
    $user = array(
        'id_utilisateur' => $_SESSION['utilisateur_id'] ?? 1,
        'email' => $_SESSION['utilisateur_email'] ?? 'admin@magasin.com',
        'nom_complet' => $_SESSION['utilisateur_nom'] ?? 'Utilisateur'
    );
}

// Vérifier qu'on a au moins un email
if (empty($user['email'])) {
    die('❌ Erreur: Email utilisateur introuvable');
}

$config_file = 'config/mail_config.php';
$config_exists = file_exists($config_file);
$current_config = array();
$message = '';
$message_type = '';

// Lire la configuration actuelle
if ($config_exists) {
    $content = file_get_contents($config_file);
    
    // Parser les définitions
    preg_match("/'SMTP_HOST',\s*'([^']+)'/", $content, $m);
    $current_config['host'] = $m[1] ?? 'smtp.gmail.com';
    
    preg_match("/'SMTP_PORT',\s*(\d+)/", $content, $m);
    $current_config['port'] = $m[1] ?? 587;
    
    preg_match("/'SMTP_USER',\s*'([^']+)'/", $content, $m);
    $current_config['user'] = $m[1] ?? '';
    
    preg_match("/'SMTP_PASS',\s*'([^']+)'/", $content, $m);
    $current_config['pass'] = $m[1] ?? '';
    
    preg_match("/'MAIL_FROM',\s*'([^']+)'/", $content, $m);
    $current_config['from'] = $m[1] ?? '';
}

// Traiter le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    $host = htmlspecialchars($_POST['smtp_host'] ?? 'smtp.gmail.com');
    $port = intval($_POST['smtp_port'] ?? 587);
    $user_email = htmlspecialchars($_POST['smtp_user'] ?? '');
    $password = htmlspecialchars($_POST['smtp_pass'] ?? '');
    $from_email = htmlspecialchars($_POST['mail_from'] ?? $user_email);
    
    // Validations
    if (empty($user_email) || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email utilisateur invalide.';
        $message_type = 'danger';
    } elseif (empty($password)) {
        $message = 'Le mot de passe est obligatoire.';
        $message_type = 'danger';
    } else {
        // Générer le fichier de configuration
        $config_content = "<?php\n";
        $config_content .= "/**\n";
        $config_content .= " * Configuration SMTP pour l'envoi d'emails\n";
        $config_content .= " * Généré le: " . date('Y-m-d H:i:s') . "\n";
        $config_content .= " */\n\n";
        $config_content .= "define('MAIL_DRIVER', 'smtp');\n\n";
        $config_content .= "define('SMTP_HOST', '$host');\n";
        $config_content .= "define('SMTP_PORT', $port);\n";
        $config_content .= "define('SMTP_SECURE', 'tls'); // ou 'ssl'\n";
        $config_content .= "define('SMTP_USER', '$user_email');\n";
        $config_content .= "define('SMTP_PASS', '$password');\n";
        $config_content .= "define('MAIL_FROM', '$from_email');\n";
        $config_content .= "define('MAIL_FROM_NAME', 'Magasin Informatique');\n";
        $config_content .= "define('MAIL_DEBUG', false);\n";
        $config_content .= "\n?>\n";
        
        // Sauvegarder la configuration
        if (file_put_contents($config_file, $config_content)) {
            $message = '✅ Configuration enregistrée avec succès!';
            $message_type = 'success';
            $current_config = array(
                'host' => $host,
                'port' => $port,
                'user' => $user_email,
                'pass' => str_repeat('*', strlen($password)),
                'from' => $from_email
            );
        } else {
            $message = '❌ Erreur: Impossible d\'écrire le fichier config/mail_config.php';
            $message_type = 'danger';
        }
    }
}

// Tester la connexion SMTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_smtp'])) {
    if (!file_exists($config_file)) {
        $message = 'Configuration non trouvée. Veuillez d\'abord configurer SMTP.';
        $message_type = 'danger';
    } else {
        include_once $config_file;
        
        // Vérifier que PHPMailer est installé
        if (!file_exists('vendor/autoload.php')) {
            $message = 'PHPMailer n\'est pas installé.';
            $message_type = 'danger';
        } else {
            require_once 'vendor/autoload.php';
            
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USER;
                $mail->Password = SMTP_PASS;
                $mail->SMTPSecure = 'tls';
                $mail->Port = SMTP_PORT;
                $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
                $mail->addAddress($user['email']);
                $mail->Subject = 'Test SMTP - ' . date('Y-m-d H:i:s');
                $mail->Body = "Ceci est un test de connexion SMTP.\n\nSi vous recevez ce message, la configuration est correcte!";
                $mail->isHTML(false);
                
                if ($mail->send()) {
                    $message = '✅ Test réussi! Un email de test a été envoyé à: ' . htmlspecialchars($user['email']);
                    $message_type = 'success';
                } else {
                    $message = '❌ Erreur lors de l\'envoi: ' . htmlspecialchars($mail->ErrorInfo);
                    $message_type = 'danger';
                }
            } catch (Exception $e) {
                $message = '❌ Erreur de connexion: ' . htmlspecialchars($e->getMessage());
                $message_type = 'danger';
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
    <title>Configuration SMTP</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Navigation</h2>
            <a href="index.php">🔙 Retour</a>
            <a href="pages/parametres.php">⚙️ Paramètres</a>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>📧 Configuration SMTP</h1>
                <p>Configurez l'envoi d'emails pour les notifications</p>
            </div>

            <div class="content" style="max-width: 800px;">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>" style="margin-bottom: 25px;">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- État du système -->
                <div style="background: linear-gradient(135deg, #f8f9fa, #fff); border-radius: 12px; padding: 25px; margin-bottom: 30px; border: 1px solid #e9ecef;">
                    <h3 style="color: #333; margin-bottom: 15px; font-size: 16px;">📊 État du Système</h3>
                    
                    <div style="display: grid; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 20px;">
                                <?php echo file_exists('vendor/autoload.php') ? '✅' : '❌'; ?>
                            </span>
                            <span>PHPMailer</span>
                            <span style="color: #666; font-size: 13px;">
                                <?php echo file_exists('vendor/autoload.php') ? '(Installé)' : '(Non installé)'; ?>
                            </span>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 20px;">
                                <?php echo $config_exists ? '✅' : '⚠️'; ?>
                            </span>
                            <span>Configuration SMTP</span>
                            <span style="color: #666; font-size: 13px;">
                                <?php echo $config_exists ? '(Configurée)' : '(À configurer)'; ?>
                            </span>
                        </div>
                        
                        <?php if ($config_exists): ?>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 20px;">ℹ️</span>
                                <span>Host: <strong><?php echo htmlspecialchars($current_config['host'] ?? ''); ?></strong></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Formulaire de configuration -->
                <div style="background: linear-gradient(135deg, #fff, #f8f9fa); border-radius: 12px; padding: 25px; border: 1px solid #e9ecef; margin-bottom: 25px;">
                    <h3 style="color: #333; margin-bottom: 20px; font-size: 16px;">⚙️ Configuration SMTP</h3>
                    
                    <form method="POST">
                        <input type="hidden" name="save_config" value="1">
                        
                        <div class="form-group">
                            <label for="smtp_host">Serveur SMTP *</label>
                            <input type="text" id="smtp_host" name="smtp_host" 
                                value="<?php echo htmlspecialchars($current_config['host'] ?? 'smtp.gmail.com'); ?>" 
                                placeholder="smtp.gmail.com"
                                required>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                <strong>Gmail:</strong> smtp.gmail.com
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_port">Port SMTP *</label>
                            <input type="number" id="smtp_port" name="smtp_port" 
                                value="<?php echo $current_config['port'] ?? 587; ?>" 
                                min="1" max="65535"
                                required>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Port standard: 587 (TLS) ou 465 (SSL)
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_user">Email SMTP *</label>
                            <input type="email" id="smtp_user" name="smtp_user" 
                                value="<?php echo htmlspecialchars($current_config['user'] ?? ''); ?>" 
                                placeholder="votre.email@gmail.com"
                                required>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Votre adresse email (Gmail, etc.)
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_pass">Mot de passe app *</label>
                            <input type="password" id="smtp_pass" name="smtp_pass" 
                                placeholder="Mot de passe d'application"
                                required>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Pour Gmail: <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: #0d6efd;">Créer un mot de passe d'application</a>
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="mail_from">Email d'envoi (optionnel)</label>
                            <input type="email" id="mail_from" name="mail_from" 
                                value="<?php echo htmlspecialchars($current_config['from'] ?? ''); ?>" 
                                placeholder="noreply@magasin-informatique.fr">
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Par défaut: Email SMTP (laissez vide pour utiliser l'email ci-dessus)
                            </small>
                        </div>
                        
                        <div style="background: #cfe2ff; border-left: 5px solid #0d6efd; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; color: #084298;">
                            <strong>ℹ️ Conseil:</strong> Pour Gmail, créez un mot de passe d'application spécifique (ne mettez pas votre mot de passe principal).
                        </div>
                        
                        <div style="display: flex; gap: 12px;">
                            <button type="submit" class="btn">✅ Enregistrer la Configuration</button>
                            <a href="index.php" class="btn btn-secondary">❌ Annuler</a>
                        </div>
                    </form>
                </div>

                <!-- Test de connexion -->
                <?php if ($config_exists): ?>
                    <div style="background: linear-gradient(135deg, #fff, #f8f9fa); border-radius: 12px; padding: 25px; border: 1px solid #e9ecef;">
                        <h3 style="color: #333; margin-bottom: 20px; font-size: 16px;">🧪 Tester la Connexion</h3>
                        
                        <p style="color: #666; margin-bottom: 20px;">
                            Cliquez sur le bouton ci-dessous pour envoyer un email de test à votre adresse:
                            <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                        </p>
                        
                        <form method="POST">
                            <input type="hidden" name="test_smtp" value="1">
                            <button type="submit" class="btn" style="background: #28a745;">
                                📧 Envoyer un Email de Test
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
