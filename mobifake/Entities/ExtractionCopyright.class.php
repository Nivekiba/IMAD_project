<?php

// Extraction de l'auteur via les copyright
class ExtractionCopyright {
	private $scores;
	private $company;
	
	/*
	 * Extraire le nom de l'auteur d'un ensemble de documents.
	 */
	public static function extract($corpus, $selection) {
		return new ExtractionCopyright($corpus, $selection);
	}
	
	
	
	private function __construct($corpus, $selection) {
		$this->scores = array();
		$selected = $selection->get_selected();
		
		foreach ($selected as $i) {
			$link = $corpus->get_link($i);
			$content = Tools::http_get($link);
			$fenetres = $this->calculer_fenetres($content);
			foreach ($fenetres as $fenetre) {
				$name = $this->trouver_nom($fenetre);
				if ($name != '') {
					$this->scores[$name] ++;
				}
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
	
	private function calculer_fenetres($content) {
		$resultat = array();
		$content = preg_replace("/&copy;/", "©", $content);
		$content = preg_replace("/copyrights?/i", "©", $content);
		$content = strip_tags($content);
		$match = preg_match_all("/(.*©.*)/", $content, $fenetre);
		$nb_match = count($fenetre[0]);
		for ($i = 0 ; $i < $nb_match ; $i++) {
			$echantillon = $fenetre[1][$i];
			
			$echantillon = preg_replace("/\s+/", " ", $echantillon);
			$echantillon = preg_replace("/ ?[|,]/", ".", $echantillon);
			$resultat[] = $echantillon;
		}
		return $resultat;
	}
	
	private function trouver_nom($fenetre) {
		$resultat = '';
		if (preg_match_all("/© ?20\d\d ?- ?20\d\d (.+)\./U", $fenetre, $tags)) {
			$resultat = $tags[1][0];
		} else if (preg_match_all("/© ?20\d\d (.+)\./U", $fenetre, $tags)) {
			$resultat = $tags[1][0];
		} else if (preg_match_all("/© ?(.+)\./U", $fenetre, $tags)) {
			$resultat = $tags[1][0];
		} else if (preg_match_all("/© ?20\d\d ?- ?20\d\d (.+)$/U", $fenetre, $tags)) {
			$resultat = $tags[1][0];
		} else if (preg_match_all("/© ?20\d\d (.+)$/U", $fenetre, $tags)) {
			$resultat = $tags[1][0];
		} else if (preg_match_all("/© ?(.+) ?-? 20\d\d/U", $fenetre, $tags)) {
			$resultat = $tags[1][0];
		}
		return $resultat;
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
