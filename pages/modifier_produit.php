<?php
include_once '../includes/auth.php';
require_login();

include_once '../includes/fonctions_categories.php';
include_once '../includes/fonctions_produits.php';

$message = '';
$type_message = '';
$id_produit = intval($_GET['id']);
$produit = get_product_by_id($id_produit);
$categories = get_all_categories();

if (!$produit) {
    $message = 'Produit introuvable.';
    $type_message = 'danger';
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $designation = $_POST['designation'];
        $description = $_POST['description'];
        $marque = $_POST['marque'];
        $prix = $_POST['prix'];
        $quantite = $_POST['quantite'];
        $id_categorie = $_POST['id_categorie'];
        
        $photo = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $nom_fichier = time() . '_' . basename($_FILES['photo']['name']);
            $cible = '../images/produits/' . $nom_fichier;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $cible)) {
                $photo = $nom_fichier;
            }
        }
        
        if (update_product($id_produit, $designation, $description, $marque, $prix, $quantite, $photo, $id_categorie)) {
            $produit = get_product_by_id($id_produit);
            $message = 'Produit modifié avec succès!';
            $type_message = 'success';
        } else {
            $message = 'Erreur lors de la modification.';
            $type_message = 'danger';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Produit</title>
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
                <h1>✏️ Modifier le Produit</h1>
                <p>Mettez à jour les informations du produit</p>
            </div>

            <div class="content">
                <?php if ($message != ''): ?>
                    <div class="alert alert-<?php echo $type_message; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($produit): ?>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="reference">Référence (Non modifiable)</label>
                                <input type="text" id="reference" value="<?php echo htmlspecialchars($produit['reference']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="designation">Désignation *</label>
                                <input type="text" id="designation" name="designation" value="<?php echo htmlspecialchars($produit['designation']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?php echo htmlspecialchars($produit['description']); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="marque">Marque</label>
                                <input type="text" id="marque" name="marque" value="<?php echo htmlspecialchars($produit['marque']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="id_categorie">Catégorie *</label>
                                <select id="id_categorie" name="id_categorie" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id_categorie']; ?>" 
                                            <?php echo ($cat['id_categorie'] == $produit['id_categorie']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['nom_categorie']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="prix">Prix (DT) *</label>
                                <input type="number" id="prix" name="prix" step="0.01" min="0.01" value="<?php echo $produit['prix']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="quantite">Quantité *</label>
                                <input type="number" id="quantite" name="quantite" min="0" value="<?php echo $produit['quantite']; ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="photo">Photo du Produit</label>
                            <?php if ($produit['photo']): ?>
                                <p>Photo actuelle: <img src="../images/produits/<?php echo htmlspecialchars($produit['photo']); ?>" 
                                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px;"></p>
                            <?php endif; ?>
                            <input type="file" id="photo" name="photo" accept="image/*">
                            <small style="color: #999;">Laissez vide pour garder la photo actuelle</small>
                        </div>

                        <div style="display: flex; gap: 12px; margin-top: 30px;">
                            <button type="submit" class="btn">✅ Modifier le Produit</button>
                            <a href="../index.php" class="btn btn-secondary" style="text-align: center;">❌ Annuler</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
