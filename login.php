<?php
include_once 'includes/auth.php';

// Si l'utilisateur est déjà connecté, le rediriger
require_logout();

$message = '';
$type_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = authenticate($email, $password);
    
    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $message = $result['message'];
        $type_message = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Magasin Informatique</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideDown 0.5s ease;
        }
        
        .login-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .login-header p {
            margin: 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 5px solid;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            animation: fadeIn 0.4s ease;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
            border-left-color: #dc3545;
        }
        
        .alert-success {
            background-color: #d1f0e4;
            color: #0f5132;
            border-left-color: #198754;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            font-family: inherit;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background-color: #f9f9ff;
        }
        
        .form-group input::placeholder {
            color: #999;
        }
        
        .login-btn {
            width: 100%;
            padding: 13px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-footer {
            padding: 20px 30px;
            background-color: #f8f9fa;
            text-align: center;
            font-size: 13px;
            color: #666;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        
        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
                margin: 20px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
            
            .login-footer {
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>💻 Magasin Informatique</h1>
            <p>Gestion du Stock - Espace Propriétaire</p>
        </div>
        
        <div class="login-body">
            <?php if ($message != ''): ?>
                <div class="alert alert-<?php echo $type_message; ?>">
                    <?php echo ($type_message === 'danger') ? '✕' : '✓'; ?>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">📧 Email</label>
                    <input type="email" id="email" name="email" placeholder="Votre email de propriétaire" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                </div>
                
                <button type="submit" class="login-btn">🔓 Se Connecter</button>
                
                <div class="forgot-password">
                    <p style="color: #999; font-size: 12px; margin-bottom: 8px;">
                        Données de test:<br>
                        Email: <strong>admin@magasin.com</strong><br>
                        Mot de passe: <strong>Admin@123</strong>
                    </p>
                </div>
            </form>
        </div>
        
        <div class="login-footer">
            🔐 Connexion sécurisée - Accès réservé au propriétaire
        </div>
    </div>
</body>
</html>
