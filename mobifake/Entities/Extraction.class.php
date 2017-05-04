<?php

// Extraction de l'auteur via les copyright
class Extraction {
	private $scores;
	private $company;
	
	/*
	 * Extraire le nom de l'auteur d'un ensemble de documents.
	 */
	public static function extract($corpus, $selection) {
		return new Extraction($corpus, $selection);
	}
	
	
	
	private function __construct($corpus, $selection) {
		$ex1 = ExtractionDomaine::extract($corpus, $selection);
		$ex2 = ExtractionMeta::extract($corpus, $selection);
		$ex3 = ExtractionCopyright::extract($corpus, $selection);
		$this->scores = array();
		foreach ($ex1->get_scores() as $key=>$val) {
			$key = strtolower($key);
			$key = preg_replace("/ *$/", "", $key);
			$key = preg_replace("/^ */", "", $key);
			$this->scores[$key] += $val;
		}
		foreach ($ex2->get_scores() as $key=>$val) {
			$key = strtolower($key);
			$key = preg_replace("/ *$/", "", $key);
			$key = preg_replace("/^ */", "", $key);
			$this->scores[$key] += $val;
		}
		foreach ($ex3->get_scores() as $key=>$val) {
			$key = strtolower($key);
			$key = preg_replace("/ *$/", "", $key);
			$key = preg_replace("/^ */", "", $key);
			$this->scores[$key] += $val;
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
