<?php

// Extraction de l'auteur via les metadonnées
class ExtractionMeta {
	private $scores;
	private $company;
	
	/*
	 * Extraire le nom de l'auteur d'un ensemble de documents.
	 */
	public static function extract($corpus, $selection) {
		return new ExtractionMeta($corpus, $selection);
	}
	
	
	
	private function __construct($corpus, $selection) {
		$this->scores = array();
		
		$selected = $selection->get_selected();
		foreach ($selected as $i) {
			$link = $corpus->get_link($i);
			$content = Tools::http_get($link);
			$match = preg_match_all('/<meta.*name=["\']author["\'].*>/', $content, $metas);
			if ($match) {
				$match = preg_match('/.*content=["\'](.*)["\'].*/', $metas[0][0], $author);
				if (match && $author[1] != '') {
					$this->scores[$author[1]] ++;
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
	
	///////// GETTERS /////////
	
	public function get_scores() {
		return $this->scores;
	}
	
	public function get_company() {
		return $this->company;
	}
}

?>
