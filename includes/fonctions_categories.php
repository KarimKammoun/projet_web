<?php

include_once dirname(__FILE__, 2) . '/config/connexion.php';

function get_all_categories() {
    global $connexion;
    $resultat = $connexion->query("SELECT * FROM categories ORDER BY nom_categorie");
    return $resultat->fetch_all(MYSQLI_ASSOC);
}

function add_category($nom_categorie, $description) {
    global $connexion;
    
    $nom_categorie = $connexion->real_escape_string($nom_categorie);
    $description = $connexion->real_escape_string($description);
    
    $check = $connexion->query("SELECT id_categorie FROM categories WHERE nom_categorie = '$nom_categorie'");
    
    if ($check->num_rows > 0) {
        return false;
    }
    
    $sql = "INSERT INTO categories (nom_categorie, description) VALUES ('$nom_categorie', '$description')";
    return $connexion->query($sql);
}

function delete_category($id_categorie) {
    global $connexion;
    
    $check = $connexion->query("SELECT id_produit FROM produits WHERE id_categorie = $id_categorie");
    
    if ($check->num_rows > 0) {
        return false;
    }
    
    $sql = "DELETE FROM categories WHERE id_categorie = $id_categorie";
    return $connexion->query($sql);
}

function get_category_by_id($id_categorie) {
    global $connexion;
    $resultat = $connexion->query("SELECT * FROM categories WHERE id_categorie = $id_categorie");
    return $resultat->fetch_assoc();
}

?>
