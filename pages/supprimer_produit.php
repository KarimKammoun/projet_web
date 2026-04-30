<?php
include_once '../includes/auth.php';
require_login();

include_once '../includes/fonctions_produits.php';

$message = '';
$type_message = '';
$id_produit = intval($_GET['id']);
$produit = get_product_by_id($id_produit);

if (!$produit) {
    $message = 'Produit introuvable.';
    $type_message = 'danger';
} elseif ($produit['quantite'] > 0) {
    $message = 'Impossible de supprimer: la quantité en stock doit être 0.';
    $type_message = 'danger';
} else {
    if (delete_product($id_produit)) {
        header('Location: ../index.php');
        exit;
    } else {
        $message = 'Erreur lors de la suppression du produit.';
        $type_message = 'danger';
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un Produit</title>
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
                <h1>⚠️ Suppression de Produit</h1>
                <p>Cette action est irréversible</p>
            </div>

            <div class="content" style="max-width: 600px;">
                <?php if ($message != ''): ?>
                    <div class="alert alert-<?php echo $type_message; ?>" style="<?php echo $type_message === 'danger' ? 'border-left: 5px solid #dc3545;' : ''; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <a href="../index.php" class="btn btn-secondary" style="display: inline-block; margin-top: 20px;">🔙 Retour à la liste</a>
            </div>
        </div>
    </div>
</body>
</html>
