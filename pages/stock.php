<?php
include_once '../includes/auth.php';
require_login();

include_once '../includes/fonctions_produits.php';
include_once '../includes/email_service.php';

$message = '';
$type_message = '';
$id_produit = intval($_GET['id']);
$produit = get_product_by_id($id_produit);

if (!$produit) {
    $message = 'Produit introuvable.';
    $type_message = 'danger';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $produit) {
    $quantite = intval($_POST['quantite']);
    
    if (update_stock($id_produit, $quantite)) {
        $produit = get_product_by_id($id_produit);
        $message = 'Stock mis à jour avec succès!';
        $type_message = 'success';
        
        // Récupérer les paramètres de notification du propriétaire (admin avec id = 1)
        include_once '../config/connexion.php';
        $admin_sql = "SELECT email, seuil_notification, notifications_activees FROM utilisateurs WHERE id_utilisateur = 1 LIMIT 1";
        $admin_result = $connexion->query($admin_sql);
        
        if ($admin_result && $admin_result->num_rows > 0) {
            $admin = $admin_result->fetch_assoc();
            
            $owner_email = $admin['email'] ?? 'admin@magasin.com';
            $owner_threshold = intval($admin['seuil_notification'] ?? 5);
            $notifications_enabled = intval($admin['notifications_activees'] ?? 1);
            
            // Envoyer notification au propriétaire si les conditions sont réunies
            if ($notifications_enabled == 1 && $quantite < $owner_threshold && $quantite > 0) {
                try {
                    $subject = "⚠️ Alerte Stock Bas - " . htmlspecialchars($produit['designation']);
                    $body = "Bonjour,\n\n";
                    $body .= "⚠️ ALERTE STOCK BAS\n\n";
                    $body .= "Un produit a été mis à jour et est maintenant en dessous du seuil défini.\n\n";
                    $body .= "Détails:\n";
                    $body .= "- Produit: " . htmlspecialchars($produit['designation']) . "\n";
                    $body .= "- Référence: " . htmlspecialchars($produit['reference']) . "\n";
                    $body .= "- Stock actuel: $quantite\n";
                    $body .= "- Seuil d'alerte: $owner_threshold\n\n";
                    $body .= "Veuillez réapprovisionner ce produit dès que possible.\n\n";
                    $body .= "Cordialement,\nSystème de Gestion de Stock";
                    
                    $send_result = EmailService::send($owner_email, $subject, $body, false);
                    
                    if ($send_result) {
                        $message .= " 📧 Notification envoyée au propriétaire";
                    } else {
                        $message .= " ⚠️ Notification non envoyée (vérifiez la configuration SMTP)";
                    }
                } catch (Exception $e) {
                    error_log("Erreur envoi email: " . $e->getMessage());
                }
            }
        }
    } else {
        $message = 'Erreur lors de la mise à jour du stock.';
        $type_message = 'danger';
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer le Stock</title>
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
                <h1>📦 Gestion du Stock</h1>
                <p>Mettez à jour la quantité en stock</p>
            </div>

            <div class="content">
                <?php if ($message != ''): ?>
                    <div class="alert alert-<?php echo $type_message; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($produit): ?>
                    <div class="produit-card">
                        <?php if ($produit['photo']): ?>
                            <img src="../images/produits/<?php echo htmlspecialchars($produit['photo']); ?>" 
                                 class="produit-image">
                        <?php else: ?>
                            <div class="no-image">Pas d'image</div>
                        <?php endif; ?>
                        
                        <div class="produit-info">
                            <h3><?php echo htmlspecialchars($produit['designation']); ?></h3>
                            <p><strong>Référence:</strong> <?php echo htmlspecialchars($produit['reference']); ?></p>
                            <p><strong>Marque:</strong> <?php echo htmlspecialchars($produit['marque']); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($produit['description']); ?></p>
                            <div class="produit-price"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> DT</div>
                        </div>
                    </div>

                    <form method="POST" style="margin-top: 30px;">
                        <div class="form-group">
                            <label for="quantite"><strong>Quantité en stock actuellement: <?php echo $produit['quantite']; ?></strong></label>
                            <input type="number" id="quantite" name="quantite" min="0" value="<?php echo $produit['quantite']; ?>" required>
                        </div>

                        <div style="display: flex; gap: 12px; margin-top: 30px;">
                            <button type="submit" class="btn btn-success">✅ Mettre à jour le Stock</button>
                            <a href="../index.php" class="btn btn-secondary" style="text-align: center;">❌ Annuler</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
