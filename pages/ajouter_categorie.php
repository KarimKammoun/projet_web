<?php
include_once '../includes/auth.php';
require_login();

include_once '../includes/fonctions_categories.php';

$message = '';
$type_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom_categorie = $_POST['nom_categorie'];
    $description = $_POST['description'];
    
    if (add_category($nom_categorie, $description)) {
        $message = 'Catégorie ajoutée avec succès!';
        $type_message = 'success';
    } else {
        $message = 'Erreur: Cette catégorie existe déjà ou une autre erreur est survenue.';
        $type_message = 'danger';
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Catégorie</title>
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
                <h1>➕ Ajouter une Catégorie</h1>
                <p>Créez une nouvelle catégorie de produits</p>
            </div>

            <div class="content" style="max-width: 600px;">
                <?php if ($message != ''): ?>
                    <div class="alert alert-<?php echo $type_message; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="nom_categorie" data-required="*">Nom de la Catégorie</label>
                        <input type="text" id="nom_categorie" name="nom_categorie" placeholder="Ex: Ordinateurs, Smartphones..." required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Décrivez cette catégorie (optionnel)"></textarea>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 30px;">
                        <button type="submit" class="btn">✅ Ajouter la Catégorie</button>
                        <a href="../index.php" class="btn btn-secondary" style="text-align: center;">❌ Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
