<?php
/**
 * Service d'envoi d'emails
 * Supporte PHPMailer et mail() PHP natif
 */

// Charger la configuration si elle n'existe pas
if (!defined('MAIL_DRIVER')) {
    $mail_config_path = dirname(__FILE__) . '/../config/mail_config.php';
    if (file_exists($mail_config_path)) {
        require_once $mail_config_path;
    } else {
        // Configuration par défaut
        define('MAIL_DRIVER', 'php');
        define('SMTP_HOST', 'smtp.gmail.com');
        define('SMTP_PORT', 587);
        define('SMTP_SECURE', 'tls');
        define('SMTP_USER', '');
        define('SMTP_PASS', '');
        define('MAIL_FROM', 'noreply@magasin.fr');
        define('MAIL_FROM_NAME', 'Magasin Informatique');
        define('MAIL_DEBUG', false);
    }
}

class EmailService {
    private static $instance = null;
    
    /**
     * Envoyer un email
     */
    public static function send($to, $subject, $body, $isHtml = false) {
        // Vérifier si PHPMailer est disponible
        if (self::isPhpMailerInstalled()) {
            return self::sendWithPhpMailer($to, $subject, $body, $isHtml);
        } else {
            return self::sendWithPhp($to, $subject, $body, $isHtml);
        }
    }
    
    /**
     * Envoyer avec PHPMailer
     */
    private static function sendWithPhpMailer($to, $subject, $body, $isHtml = false) {
        try {
            // Charger PHPMailer avec le bon chemin
            $vendor_path = dirname(__FILE__) . '/../vendor/autoload.php';
            if (!file_exists($vendor_path)) {
                throw new Exception("PHPMailer non installé: $vendor_path");
            }
            require_once $vendor_path;
            
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuration du serveur
            if (MAIL_DEBUG) {
                $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
            }
            
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            
            // Paramètres de l'email
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->isHTML($isHtml);
            
            // Si HTML, ajouter une version texte
            if ($isHtml) {
                $mail->AltBody = strip_tags($body);
            }
            
            // Envoyer
            return $mail->send();
            
        } catch (Exception $e) {
            error_log("Erreur PHPMailer: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envoyer avec mail() PHP natif
     */
    private static function sendWithPhp($to, $subject, $body, $isHtml = false) {
        $headers = "From: " . MAIL_FROM . " <" . MAIL_FROM . ">\r\n";
        $headers .= "Reply-To: " . MAIL_FROM . "\r\n";
        
        if ($isHtml) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        
        return mail($to, $subject, $body, $headers);
    }
    
    /**
     * Vérifier si PHPMailer est installé
     */
    private static function isPhpMailerInstalled() {
        return file_exists(dirname(__FILE__) . '/../vendor/autoload.php');
    }
}

?>
