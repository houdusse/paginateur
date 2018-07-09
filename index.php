<?php
// Recuperation du numéro de page à afficher, si pas de numéro page = 1, si numero > nombre de page page = dernière
// Recuperation du nombre de ligne par page à afficher
if(isset($_GET['lignes'])) {
$lignesParPage = $_GET['lignes'];
} else {
	$lignesParPage = 10;
}

// Recuperation du numéro de page à afficher
if(isset($_GET['page'])) {
$page = $_GET['page'];
} else {
	$page = 1;
}
echo $page;



try {
$db = new PDO("mysql:host=localhost;dbname=pagination", "root", "");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
	die('Erreur : ' .$e->getMessage());
}

$sql = 'SELECT COUNT(*) as nombreTotalLignes FROM livredor';
$reponse = $db->query($sql);
$donnees = $reponse->fetch();
$nombreTotalLignes = $donnees['nombreTotalLignes'];
// Determination du nombre de pages (count(*)/lignesParPage)
$nombreDePages = ceil($nombreTotalLignes / $lignesParPage);

// Vérification que le numéro de page à afficher est <= à nombreDePages sinon selection dernière page
$page = ($page > $nombreDePages) ? $nombreDePages : $page;



// LIMIT = lignesParPage
// OFFSET = (numeroPage -1) * lignesParPage
// Determination OFFSET et LIMIT pour requête SQL à venir
$limit = $lignesParPage;
$offset = ($page -1) * $lignesParPage;  
// Préparation de la requête SQL pour récupérer les données à afficher
try {
	 $sql2 = "SELECT id, pseudo, message FROM livredor LIMIT $limit OFFSET $offset ";
 	$req = $db->prepare($sql2);
 	$req->execute();
 	$retour = array();
 	while($donnees2 = $req->fetch(PDO::FETCH_ASSOC)) {
 		$retour[] = $donnees2; 
 	}
}
catch(PDOException $e) {
	die('Erreur : ' .$e->getMessage());
}	


// lecture du tableau pour mise en forme
     echo '<table width="400" border="1px solid black" align="center" cellpadding="0" cellspacing="0">' ."\n";
     // Lecture des lignes du tableau
     foreach($retour as $ligneTableau) {
     	echo '<tr>' ."\n";
     		// lecture des colonnes de cette ligne
     		foreach($ligneTableau as $colonnesTableau) {
     			echo '<td>'. $colonnesTableau .'</td>';
     		}
     	echo '</tr>' ."\n";
     }
     echo '</table><br /><br />';

// Gestion liens "Suivant" et "Précédent"
$pagePrecedente = ($page > 1) ? $page -1 : 0;
$precedente = '<a href="index.php?page='. "$pagePrecedente" ."&lignes=$lignesParPage\">" .'Précédente</a>';
// si la page est la première 0 on n'affiche pas précedente
$precedente = ($page != 1)? $precedente : " ";

$pageSuivante = $page + 1;
$suivante = '<a href="index.php?page='. "$pageSuivante" ."&lignes=$lignesParPage\">" .'Suivante</a>';// si la page est la dernière (nombreDePages)  on n'affiche pas précedente
$suivante = ($pageSuivante <= $nombreDePages )? $suivante : "";

echo $precedente;
echo $suivante;

// Gestion des blocks de pages (barre de navigation)
$tailleBlock = 5; // Nombre de page par block
$nombreLignesParBlock = $tailleBlock * $lignesParPage;
$nombreBlock = ceil($nombreTotalLignes / $nombreLignesParBlock);
$blockCourant = ceil($page * $lignesParPage / $nombreLignesParBlock) ;
$debutBlockCourant = ($tailleBlock * ($blockCourant - 1)) +1 ;
$finBlockCourant = $debutBlockCourant + $tailleBlock -1 ;
echo '<br/>';
for($i = $debutBlockCourant; $i <= $finBlockCourant; $i++) {
	if($i <= $nombreDePages) {
		$menu = '<a href="index.php?page=' .$i .'&lignes=' .$lignesParPage .'">' .$i .'</a>';
	}
	if($i == $page) {
		$menu = '[' . $i .']';
	}
	echo $menu;
}
// var_dump($tailleBlock, $nombreBlock, $blockCourant, $debutBlockCourant, $finBlockCourant);
?>