<?php

class Selection {
	private $selected;
	private $score;
	private $entities;
	private $status;
	private $moteur;
	
	/*
	 * Sélectionner les documents en lien avec le nom de l'appli.
	 */
	public static function select($corpus, $clustering, 
			$analysis, $nom_app, $seuil_selection, $NB_ENTITES, 
			$SYLLABES_MIN, $STR_DISTANCE,$MOTEUR) {
		return new Selection($corpus, $clustering, $analysis, explode("+", $nom_app), $seuil_selection, $NB_ENTITES, $SYLLABES_MIN, $STR_DISTANCE,$MOTEUR);
	}
	
	
	
	private function __construct($corpus, $clustering, $analysis, $nom_app, $seuil_selection, $NB_ENTITES, $SYLLABES_MIN, $STR_DISTANCE,$MOTEUR) {
		$this->selected = array();
		$this->entities = array();
		$this->status = array();
		$this->moteur = $MOTEUR;
		$nb_doc = count($corpus->get_documents());
		
		$this->score = $this->compute_phonetic_score($corpus, $clustering, $analysis, $nom_app, $SYLLABES_MIN, $STR_DISTANCE);
		
		for ($i = 0 ; $i < $nb_doc ; $i++) {
			if ($this->score->maxRow($i) >= $seuil_selection) {
				// Filtrer les documents trompeurs
				if ($this->valider_document($i, $corpus, $NB_ENTITES)) {
					$this->selected[] = $i;
					$this->status[$i] = 'VALIDÉ';
				} else {
					$this->status[$i] = 'TROMPEUR';
				}
			} else {
				// Tenter de repêcher les documents
				$this->status[$i] = 'REJETÉ';
				if ($analysis->get_validation_topic($clustering->get_classes()->get(0, $i))) {
					if ($this->repecher_document($i, $corpus, $NB_ENTITES)) {
						$this->selected[] = $i;
						$this->status[$i] = 'REPÊCHÉ';
					}
				}
			}
		}
	}
	
	private function valider_document($i, $corpus, $NB_ENTITES) {
		$domain = Tools::domain_name($corpus->get_link($i), 'top-level');
		
		$requete = $domain . "+site:en.wikipedia.org";
		$json = Tools::requete_moteur($requete,$this->moteur);
		$items = (array) $json->{'items'};
		
		// Obtenir le correspondant à la 1ère entité de confiance trouvée
		$nom_entite = '';
		$j = 0;
		$continue = true;
		while ($continue) {
			echo "\n -- nombre de valeurs wiki pour le document ".$domain."  ".count($items);
			$nom = $items[$j]->{'title'};
			$nom = ucfirst(preg_replace("/ - Wikipedia.*$/", "", $nom));
			$entity = new Entity($nom, null, 'selection');
			
			if ($entity->is_trust_entity()) {
				$nom_entite = $nom;
			}
			$j ++;
			$continue = ($nom_entite == '' && $j < $NB_ENTITES);
		}
		if ($j == $NB_ENTITES) {
			$this->entities[$i] = '';
			return false;
		} else {
			$this->entities[$i] = $entity;
			return ($this->score->maxRow($i) == 100 || !($entity->is_removable_entity()));
		}
	}
	
	private function repecher_document($i, $corpus, $NB_ENTITES) {
		$domain = Tools::domain_name($corpus->get_link($i), 'top-level');
		
		$requete = $domain . "+site:en.wikipedia.org";
		$json = Tools::requete_google($requete,$this->moteur);
		$items = (array) $json->{'items'};
		
		// Obtenir le correspondant à la 1ère entité de confiance trouvée
		$nom_entite = '';
		$j = 0;
		$continue = true;
		while ($continue) {
			$nom = $items[$j]->{'title'};
			$nom = preg_replace("/ - Wikipedia.*$/", "", $nom);
			$entity = new Entity($nom, null, 'selection');
			
			if ($entity->is_acceptable_entity()) {
				$nom_entite = $nom;
			}
			$j ++;
			$continue = ($nom_entite == '' && $j < $NB_ENTITES);
		}
		if ($j == $NB_ENTITES) {
			$this->entities[$i] = '';
			return false;
		} else {
			$this->entities[$i] = $entity;
			return true;
		}
	}
	
	private function compute_phonetic_score($corpus, $clustering, $analysis, $nom_app, $SYLLABES_MIN, $STR_DISTANCE) {
		$nb_doc = count($corpus->get_documents());
		$nb_mots_nom = count($nom_app);
		$resultat = new Matrix($nb_doc, $nb_mots_nom);
		
		for ($i = 0 ; $i < $nb_mots_nom ; $i++) {
			$nom_key = metaphone($nom_app[$i]);
			if (strlen($nom_key) >= $SYLLABES_MIN && !Tools::is_useless($nom_app[$i])) {
				for ($j = 0 ; $j < $nb_doc ; $j++) {
					if ($analysis->get_validation_topic($clustering->get_classes()->get(0, $j))) {
						$scoreMax = 0;
						$domain = Tools::domain_name($corpus->get_link($j), 'all');
						$domain_key = '';
						foreach (explode(".", $domain) as $word) {
							$domain_key = $domain_key . " " . metaphone($word);
						}
						$score = $this->compute_score($nom_key, $domain_key, $STR_DISTANCE);
						$resultat->set($j, $i, $score);
					}
				}
			}
		}
		
		return $resultat;
	}
	
	private function compute_score($nom, $titre, $STR_DISTANCE) {
		$score = 0;
		if ($STR_DISTANCE == 'levenshtein') {
			$distance = levenshtein($nom, $titre, 0, 1, 1);
			$distance = 100 * $distance/(max(strlen($nom), strlen($titre)));
			$score = max(100 - $distance, 0);
		} elseif ($STR_DISTANCE == 'similarity') {
			similar_text($nom, $titre, $score);
		} else {
			$distance = Tools::longest_common_sub_string($nom, $titre);
			$score = 100 * $distance/strlen($nom);
		}
		return $score;
	}
	
	///////// GETTERS /////////
	
	public function get_selected() {
		return $this->selected;
	}
	
	public function get_score() {
		return $this->score;
	}
	
	public function get_entity($i) {
		return $this->entities[$i];
	}
	
	public function get_status($i) {
		return $this->status[$i];
	}
}

?>
