<?php

//Extraction de l'auteur via le nom de domaine
class ExtractionDomaine {
	private $scores;
	private $company;
	
	/*
	 * Extraire le nom de l'auteur d'un ensemble de documents.
	 */
	public static function extract($corpus, $selection) {
		return new ExtractionDomaine($corpus, $selection);
	}
	
	
	
	private function __construct($corpus, $selection) {
		$this->scores = array();
		
		$selected = $selection->get_selected();
		foreach ($selected as $i) {
			// Obtenir le correspondant à la 1ère entité de confiance trouvée
			if ($selection->get_entity($i) != '') {
				$nom = $selection->get_entity($i)->get_name();
				$this->scores[$nom] ++;
			}
		}
		
		$this->company = '';
		$score_max = 0;
		foreach ($this->scores as $c => $s) {
			if ($s > $score_max) {
				$score_max = $s;
				$this->company = $c;
			}
		}
	}
	
	///////// GETTERS /////////
	
	public function get_scores() {
		return $this->scores;
	}
	
	public function get_company() {
		return $this->company;
	}
}

?>
