<?php

class Corpus {
	private $documents;
	private $dictionnaire;
	private $titres;
	private $links;
	
	/*
	 * Construire le corpus
	 *  - $nom : le nom de l'application
	 *  - $NB_MOTS_DOC : le nombre min de mots pour qu'un document entre dans le corpus
	 */
	public static function build_corpus($nom, $NB_MOTS_MIN, $NB_MOTS_MAX, $TAILLE_MOT_MIN,$NBRE_DIZAINES_RESULTATS,$MOTEUR) {
		return new Corpus($nom, $NB_MOTS_MIN, $NB_MOTS_MAX, $TAILLE_MOT_MIN,$NBRE_DIZAINES_RESULTATS,$MOTEUR);
	}
	
	
	
	private function __construct($nom, $nombre_mots_min, $nombre_mots_max, $taille_mot_min, $nbre_dizaines,$moteur) {
		$this->documents = array();
		$this->dictionnaire = array();
		$this->links = array();
		$this->titres = array();
		//$requete = Tools::requete_google($nom,$nbre_dizaines);
		//$items = (array) $requete->{'items'};
		$items =  Tools::requete_moteur_nbre($nom,$moteur,$nbre_dizaines);$i=0;
		foreach ($items as $item) {echo "\n document i= ".$i++;
			$title = Tools::traiter_chaine($item->{'title'});
			$desc = Tools::traiter_chaine($item->{'snippet'});
			$content = Tools::traiter_chaine(strip_tags(Tools::http_get($item->{'link'})));
			$doc = array_merge($title, $desc, $content); 
			if (count($doc) >= $nombre_mots_min && count($doc) <= $nombre_mots_max) { 
				$this->documents[] = $doc;
				$this->links[] = $item->{'link'};
				$this->titres[] = $title;
				foreach (array_merge($title, $desc) as $mot) {
					if (strlen($mot) >= $taille_mot_min && !in_array($mot, $this->dictionnaire)) {
						$this->dictionnaire[] = $mot;
					}
				}
			}
			//var_dump($content);
		}
	}	
	///////// GETTERS /////////
	
	public function get_documents() {
		return $this->documents;
	}
	
	public function get_dictionnaire() {
		return $this->dictionnaire;
	}
	
	public function get_link($i) {
		return $this->links[$i];
	}
	
	public function get_titre($i) {
		return $this->titres[$i];
	}
	
	public function get_taille($i) {
		return count($this->documents[$i]);
	}
}

?>
