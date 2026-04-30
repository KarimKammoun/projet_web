<?php

include_once dirname(__FILE__, 2) . '/config/connexion.php';

function get_products_by_category($id_categorie) {
    global $connexion;
    $id_categorie = intval($id_categorie);
    $resultat = $connexion->query("SELECT * FROM produits WHERE id_categorie = $id_categorie ORDER BY designation");
    return $resultat->fetch_all(MYSQLI_ASSOC);
}

function get_product_by_id($id_produit) {
    global $connexion;
    $id_produit = intval($id_produit);
    $resultat = $connexion->query("SELECT * FROM produits WHERE id_produit = $id_produit");
    return $resultat->fetch_assoc();
}

function get_product_by_reference($reference) {
    global $connexion;
    $reference = $connexion->real_escape_string($reference);
    $resultat = $connexion->query("SELECT * FROM produits WHERE reference = '$reference'");
    return $resultat->fetch_assoc();
}

function add_product($reference, $designation, $description, $marque, $prix, $quantite, $photo, $id_categorie) {
    global $connexion;
    
    $reference = $connexion->real_escape_string($reference);
    $designation = $connexion->real_escape_string($designation);
    $description = $connexion->real_escape_string($description);
    $marque = $connexion->real_escape_string($marque);
    $prix = floatval($prix);
    $quantite = intval($quantite);
    $photo = $connexion->real_escape_string($photo);
    $id_categorie = intval($id_categorie);
    
    if (get_product_by_reference($reference) !== null) {
        return false;
    }
    
    $sql = "INSERT INTO produits (reference, designation, description, marque, prix, quantite, photo, id_categorie) 
            VALUES ('$reference', '$designation', '$description', '$marque', $prix, $quantite, '$photo', $id_categorie)";
    return $connexion->query($sql);
}

function update_product($id_produit, $designation, $description, $marque, $prix, $quantite, $photo, $id_categorie) {
    global $connexion;
    
    $id_produit = intval($id_produit);
    $designation = $connexion->real_escape_string($designation);
    $description = $connexion->real_escape_string($description);
    $marque = $connexion->real_escape_string($marque);
    $prix = floatval($prix);
    $quantite = intval($quantite);
    $photo = $connexion->real_escape_string($photo);
    $id_categorie = intval($id_categorie);
    
    $produit = get_product_by_id($id_produit);
    if (!$produit) {
        return false;
    }
    
    $sql = "UPDATE produits SET designation = '$designation', description = '$description', marque = '$marque', prix = $prix, quantite = $quantite, id_categorie = $id_categorie";
    
    if ($photo != '') {
        $sql .= ", photo = '$photo'";
    }
    
    $sql .= " WHERE id_produit = $id_produit";
    return $connexion->query($sql);
}

function delete_product($id_produit) {
    global $connexion;
    
    $id_produit = intval($id_produit);
    $produit = get_product_by_id($id_produit);
    
    if (!$produit || $produit['quantite'] > 0) {
        return false;
    }
    
    $sql = "DELETE FROM produits WHERE id_produit = $id_produit";
    return $connexion->query($sql);
}

function update_stock($id_produit, $quantite) {
    global $connexion;
    
    $id_produit = intval($id_produit);
    $quantite = intval($quantite);
    $produit = get_product_by_id($id_produit);
    
    if (!$produit || $quantite < 0) {
        return false;
    }
    
    $sql = "UPDATE produits SET quantite = $quantite WHERE id_produit = $id_produit";
    return $connexion->query($sql);
}

function search_products($id_categorie, $designation) {
    global $connexion;
    
    $id_categorie = intval($id_categorie);
    $designation = $connexion->real_escape_string($designation);
    
    $sql = "SELECT * FROM produits WHERE id_categorie = $id_categorie AND designation LIKE '%$designation%' ORDER BY designation";
    $resultat = $connexion->query($sql);
    return $resultat->fetch_all(MYSQLI_ASSOC);
}

function get_products_sorted($id_categorie, $tri) {
    global $connexion;
    
    $id_categorie = intval($id_categorie);
    $ordre = '';
    
    if ($tri == 'prix') {
        $ordre = 'ORDER BY prix ASC';
    } elseif ($tri == 'marque') {
        $ordre = 'ORDER BY marque ASC';
    } else {
        $ordre = 'ORDER BY designation ASC';
    }
    
    $sql = "SELECT * FROM produits WHERE id_categorie = $id_categorie $ordre";
    $resultat = $connexion->query($sql);
    return $resultat->fetch_all(MYSQLI_ASSOC);
}

?>
