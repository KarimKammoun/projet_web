<?php
include_once 'includes/auth.php';
require_login();

include_once 'includes/fonctions_categories.php';
include_once 'includes/fonctions_produits.php';

$user = get_current_user();
$categories = get_all_categories();
$id_categorie_active = 1;

if (isset($_GET['cat'])) {
    $id_categorie_active = intval($_GET['cat']);
}

$produits = get_products_by_category($id_categorie_active);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magasin Informatique - Gestion de Stock</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Menu Principal</h2>
            <?php foreach ($categories as $cat): ?>
                <a href="index.php?cat=<?php echo $cat['id_categorie']; ?>" 
                   class="<?php echo ($cat['id_categorie'] == $id_categorie_active) ? 'active' : ''; ?>">
                    📦 <?php echo htmlspecialchars($cat['nom_categorie']); ?>
                </a>
            <?php endforeach; ?>
            <hr style="margin: 25px 0; border: none; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="pages/ajouter_categorie.php" class="btn" style="width: 100%; text-align: center; display: block; margin-bottom: 10px;">➕ Nouvelle Catégorie</a>
            <a href="pages/ajouter_produit.php" class="btn btn-success" style="width: 100%; text-align: center; display: block; margin-bottom: 10px;">➕ Nouveau Produit</a>
            <a href="pages/parametres.php" class="btn btn-secondary" style="width: 100%; text-align: center; display: block;">⚙️ Paramètres</a>
        </div>

        <div class="main-content">
            <div class="header">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h1>💻 Magasin Informatique</h1>
                        <p>Système de Gestion du Stock</p>
                    </div>
                    <div style="text-align: right;">
                        <p style="color: #666; font-size: 14px; margin: 0 0 8px 0;">👤 Connecté en tant que:</p>
                        <p style="color: #0d6efd; font-weight: 700; margin: 0 0 10px 0; font-size: 15px;">
                            <?php 
                            if ($user && is_array($user) && isset($user['nom_complet'])) {
                                echo htmlspecialchars($user['nom_complet']);
                            } else {
                                echo htmlspecialchars($_SESSION['utilisateur_nom'] ?? 'Utilisateur');
                            }
                            ?>
                        </p>
                        <a href="logout.php" class="btn btn-danger" style="padding: 8px 14px; font-size: 12px;">🚪 Déconnexion</a>
                    </div>
                </div>
            </div>

            <div class="content">
                <?php 
                $categorie = get_category_by_id($id_categorie_active);
                if ($categorie): 
                ?>
                    <h2 class="section-title"><?php echo htmlspecialchars($categorie['nom_categorie']); ?></h2>
                    <p style="color: #666; margin-bottom: 20px; font-size: 15px;"><?php echo htmlspecialchars($categorie['description']); ?></p>

                    <div class="search-box">
                        <form method="GET" style="display: flex; gap: 12px; flex: 1; min-width: 300px;">
                            <input type="hidden" name="cat" value="<?php echo $id_categorie_active; ?>">
                            <input type="text" name="search" placeholder="🔍 Rechercher par désignation..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" class="btn">Rechercher</button>
                        </form>
                        <select name="tri" id="triSelect" style="padding: 12px 16px; border: 2px solid #dee2e6; border-radius: 8px; font-size: 15px; background-color: white; cursor: pointer; transition: all 0.3s ease;" onchange="trierProduits(this.value)">
                            <option value="">Trier par...</option>
                            <option value="designation">Désignation (A-Z)</option>
                            <option value="prix">Prix (croissant)</option>
                            <option value="marque">Marque</option>
                        </select>
                    </div>

                    <?php 
                    if (isset($_GET['search']) && $_GET['search'] != '') {
                        $produits = search_products($id_categorie_active, $_GET['search']);
                    } elseif (isset($_GET['tri']) && $_GET['tri'] != '') {
                        $produits = get_products_sorted($id_categorie_active, $_GET['tri']);
                    }
                    ?>

                    <?php if (count($produits) > 0): ?>
                        <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Photo</th>
                                    <th style="width: 120px;">Référence</th>
                                    <th>Désignation</th>
                                    <th style="width: 150px;">Marque</th>
                                    <th style="width: 100px;">Prix</th>
                                    <th style="width: 80px;">Stock</th>
                                    <th style="width: 220px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produits as $produit): ?>
                                    <tr>
                                        <td style="text-align: center;">
                                            <?php if ($produit['photo']): ?>
                                                <?php 
                                                    // Vérifier si c'est une URL (http/https) ou un chemin local
                                                    $src = (strpos($produit['photo'], 'http') === 0) ? 
                                                           $produit['photo'] : 
                                                           'images/produits/' . htmlspecialchars($produit['photo']);
                                                ?>
                                                <img src="<?php echo $src; ?>" 
                                                     alt="<?php echo htmlspecialchars($produit['designation']); ?>" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                            <?php else: ?>
                                                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #e9ecef, #f8f9fa); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">N/A</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($produit['reference']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($produit['designation']); ?></td>
                                        <td><?php echo htmlspecialchars($produit['marque']); ?></td>
                                        <td><span style="color: var(--danger); font-weight: 700; font-size: 16px;"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> DT</span></td>
                                        <td>
                                            <span style="display: inline-block; padding: 6px 12px; border-radius: 6px; font-weight: 600; <?php echo $produit['quantite'] > 0 ? 'background-color: #d1f0e4; color: #0f5132;' : 'background-color: #f8d7da; color: #842029;'; ?>">
                                                <?php echo $produit['quantite']; ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="pages/modifier_produit.php?id=<?php echo $produit['id_produit']; ?>" class="btn" style="padding: 8px 14px; font-size: 13px; margin: 2px;">✏️ Modifier</a>
                                            <a href="pages/stock.php?id=<?php echo $produit['id_produit']; ?>" class="btn btn-success" style="padding: 8px 14px; font-size: 13px; margin: 2px;">📦 Stock</a>
                                            <?php if ($produit['quantite'] == 0): ?>
                                                <a href="pages/supprimer_produit.php?id=<?php echo $produit['id_produit']; ?>" class="btn btn-danger" style="padding: 8px 14px; font-size: 13px; margin: 2px;" onclick="return confirm('⚠️ Confirmer la suppression ?')">🗑️ Supprimer</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 60px 40px; background: linear-gradient(135deg, #f8f9fa, #fff); border-radius: 12px; border: 2px dashed #dee2e6;">
                            <div style="font-size: 48px; margin-bottom: 15px;">📭</div>
                            <h3 style="color: #666; margin-bottom: 8px;">Aucun produit trouvé</h3>
                            <p style="color: #999; font-size: 14px;">Aucun produit ne correspond à votre recherche ou cette catégorie est vide.</p>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function trierProduits(valeur) {
            if (valeur) {
                window.location.href = "index.php?cat=<?php echo $id_categorie_active; ?>&tri=" + valeur;
            }
        }
    </script>
</body>
</html>
