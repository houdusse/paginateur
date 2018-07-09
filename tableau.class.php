<?php

class Tableau {

	// Propriétées
	protected $lignesParPage; // Paramètrage du nombre de lignes par page à afficher 
	protected $page; // Numéro de la page en cours
	protected $db; // Connexion PDO utilisée par l'objet
	protected $sql1; // Requête sql de type count utiliser pour connaître de nombre d'enregistrements
	protected $sql2; // Requête sql de type SELECT pour selectionner les enregistrements de la page
	protected $nombreTotalDeLignes; // résultat de sql1
	protected $nombreDePages; // $nombreTotalDeLignes / $ligne = nombre de pages nécessaires pour afficher l'intégralité du document
	protected $limit; // Est égale au paramètre $ligne et limite la remonté à une page de n lignes 
	protected $offset; // Défine le debut de l'affichage de la page en cours
	protected $TableauRetour = array(); // Tableau à deux dimension qui contient l'ensemble des lignes de la page
	protected $boutonPageSuivante; // le href du bouton suivant
	protected $boutonPagePrecedente; // le href du bouton précédent
	protected $moiMeme; // adresse dynamique de la page http://127.0.0.1/xxxx/xxxxxxxx/xxxxx/xxxx.php

	// Membres pour gestion des blocks
	protected $tailleBlock; // Nombre de page par block
	protected $nombreLignesParBlock; // Est égale à $tailleBlock * $lignes
	protected $nombreBlock; // Nombre total de block pour afficher l'intégralité du fichier
	protected $blockCourant; // Numéro du block en cours
	protected $debutBlockCourant; // Numéro de la première page du block courant
	protected $finBlockCourant; // Numéro de la dernière page du block courant
	protected $menu = array(); 

	

	public function __construct($db, $sql1, $sql2) {
		// Récupération des infos de l'URL si elles sont présente
		//
		//

		// Récupération du nombre du lignes par page
		if (isset($_GET['lignes'])) {
			$this->lignesParPage = $_GET['lignes'];
			if(is_numeric($this->lignesParPage)) {
				$this->lignesParPage = floor($this->lignesParPage); // lignesParPage est un entier
				$this->lignesParPage = abs($this->lignesParPage); // lignesParPage est positif
			} else {
				$this->lignesParPage = 10;  // Valeur par défaut 
			} 
		} else {
			$this->lignesParPage = 10;  // Valeur par défaut 
		}

		// Récupération du numéro de page
		if(isset($_GET['page'])) {
			$this->page = $_GET['page'];
			if(is_numeric($this->page)) {
				$this->page = floor($this->page);
				$this->page = abs($this->page);
			} else {
				$this->page = 1; // Page par défaut
			}
		} else {
			$this->page = 1; // Page par défaut
		}

		$this->db = $db;
		/************************************************************************************/
		/*** Recherche du nombre total de ligne par une requête (SQL1) count(*) from .... ***/
		/************************************************************************************/
		$this->sql1 = $sql1;
		$this->reponse = $this->db->query($sql1);
		$this->donnees = $this->reponse->fetch();
		$this->nombreTotalLignes = $this->donnees['nombreTotalLignes'];

		// Determination du nombre de pages (count(*)/lignesParPage)
		$this->nombreDePages = ceil($this->nombreTotalLignes / $this->lignesParPage);

		// Vérification que le numéro de page à afficher est <= à nombreDePages sinon selection dernière page
		$this->page = ($this->page > $this->nombreDePages) ? $this->nombreDePages : $this->page;
		
		/*************************************************************************/
		/*** Détermination des valeur de LIMIT et OFFSET pour la requête $sql2 ***/
		/*************************************************************************/  
		$this->sql2 = $sql2;
		$this->limit = $this->lignesParPage;
		$this->offset = ($this->page -1) * $this->lignesParPage;  
		$this->sql2 =  $this->sql2 .' LIMIT ' .$this->limit .' OFFSET ' .$this->offset;

		

		// Exécution de la requête sql2 pour récupérer les lignes a afficher dans la page demandée
		try {
 			$this->req = $db->prepare($this->sql2);
 			$this->req->execute();
 			$this->retour = array();
 			while($this->donnees2 = $this->req->fetch(PDO::FETCH_ASSOC)) {
 				$this->retour[] = $this->donnees2; 
 			}
		}
		catch(PDOException $e) {
			die('Erreur : ' .$e->getMessage());
		}	

		// Gestion liens "Suivant" et "Précédent"
		$this->moiMeme = 'http://' . $_SERVER['SERVER_NAME'] .$_SERVER['PHP_SELF'];
		// Gestion bouton precedent
		$this->pagePrecedente = ($this->page > 1) ? $this->page -1 : 0;
		$this->precedente = 'href="' .$this->moiMeme .'?page='. "$this->pagePrecedente" ."&lignes=$this->lignesParPage\"";
		// si la page est la première 0 on n'affiche pas précedente
		$this->precedente = ($this->page != 1)? $this->precedente : " ";

		// Gestion bouton suivant
		$this->pageSuivante = $this->page + 1;
		$this->suivante = 'href="' .$this->moiMeme .'?page='. "$this->pageSuivante" ."&lignes=$this->lignesParPage\"";
		// si la page est la dernière (nombreDePages)  on n'affiche pas précedente
		$this->suivante = ($this->pageSuivante <= $this->nombreDePages )? $this->suivante : "";


		// Enorme gestion des blocks de pages
		// Gestion des blocks de pages (barre de navigation)
		$this->tailleBlock = 5; // Nombre de page par block
		$this->nombreLignesParBlock = $this->tailleBlock * $this->lignesParPage;
		$this->nombreBlock = ceil($this->nombreTotalLignes / $this->nombreLignesParBlock);
		$this->blockCourant = ceil($this->page * $this->lignesParPage / $this->nombreLignesParBlock) ;
		$this->debutBlockCourant = ($this->tailleBlock * ($this->blockCourant - 1)) +1 ;
		$this->finBlockCourant = $this->debutBlockCourant + $this->tailleBlock -1 ;
		for($i = $this->debutBlockCourant; $i <= $this->finBlockCourant; $i++) {
			if($i <= $this->nombreDePages) {
				$this->menu[] = 'href="index.php?page=' .$i .'&lignes=' .$this->lignesParPage .'"' .$i .'</a>';
			}
			if($i == $this->page) {
				$this->menu[] = '[' . $i .']';
			}
		}


	}

	// Ensemble des méthodes pour gérer le retour de l'objet

	// Retour du tableau principal pour affichage par l'appelant
	public function getRetour() {
		return $this->retour;
	}

	// retour du href="xxxxxx" pour gérer les liens suivants
	public function getSuivante() {
		return $this->suivante;
	}

	// retour du href="xxxxxx" pour gérer les liens précédent
	public function getprecedente() {
		return $this->precedente;
	}

	// retourne un tableau avec l'ensemble des href="xxxxx" pour constituer le block  des pages
	public function getMenu() {
		return $this->menu;
	}

}


?>