<?php
include_once '../includes/auth.php';
require_login();

include_once '../includes/fonctions_categories.php';
include_once '../includes/fonctions_produits.php';

$message = '';
$type_message = '';
$categories = get_all_categories();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reference = $_POST['reference'];
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
    
    if (add_product($reference, $designation, $description, $marque, $prix, $quantite, $photo, $id_categorie)) {
        $message = 'Produit ajouté avec succès!';
        $type_message = 'success';
    } else {
        $message = 'Erreur: Cette référence existe déjà ou une autre erreur est survenue.';
        $type_message = 'danger';
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit</title>
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
                <h1>➕ Ajouter un Produit</h1>
                <p>Créez un nouveau produit dans le catalogue</p>
            </div>

            <div class="content">
                <?php if ($message != ''): ?>
                    <div class="alert alert-<?php echo $type_message; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reference">Référence *</label>
                            <input type="text" id="reference" name="reference" required>
                        </div>
                        <div class="form-group">
                            <label for="designation">Désignation *</label>
                            <input type="text" id="designation" name="designation" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="marque">Marque</label>
                            <input type="text" id="marque" name="marque">
                        </div>
                        <div class="form-group">
                            <label for="id_categorie">Catégorie *</label>
                            <select id="id_categorie" name="id_categorie" required>
                                <option value="">-- Sélectionner une catégorie --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id_categorie']; ?>">
                                        <?php echo htmlspecialchars($cat['nom_categorie']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="prix">Prix (DT) *</label>
                            <input type="number" id="prix" name="prix" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="quantite">Quantité *</label>
                            <input type="number" id="quantite" name="quantite" min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="photo">Photo du Produit</label>
                        <input type="file" id="photo" name="photo" accept="image/*">
                        <small style="color: #999;">Formats acceptés: JPG, PNG, GIF</small>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 30px;">
                        <button type="submit" class="btn">✅ Ajouter le Produit</button>
                        <a href="../index.php" class="btn btn-secondary" style="text-align: center;">❌ Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
