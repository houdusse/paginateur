<?php
use shoudusse\paginateur\Tableau;

require_once 'tableau.class.php';
$sql1 = 'SELECT COUNT(*) AS nombreTotalLignes FROM livredor';
$sql2 ='SELECT id, pseudo, message FROM livredor ';
// Création de la connexion à la base de données
try {
$db = new PDO("mysql:host=localhost;dbname=pagination;charset=utf8", "root", "");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
	die('Erreur : ' .$e->getMessage());
}

$montableau = new Tableau($db, $sql1, $sql2);
$resultat = $montableau->getRetour(); // Tableau résultat correspondant à la page demandée
$suiv = $montableau->getSuivante(); // Url de la page suivante
$prec = $montableau->getPrecedente(); // Url de la page précédente
$menu = $montableau->getMenu();
var_dump($resultat, $suiv, $prec, $menu);  
echo '<br>';
var_dump($_SERVER);





?>