<?php

class KeyWords {
	private $tf_idf;
	private $tf;
	private $scores;
	private $indices;
	
	/*
	 * Obtenir les mots clés du corpus.
	 *  - $corpus : l'étape précédente
	 */
	public static function get_keywords($corpus) {
		return new KeyWords($corpus->get_documents(), $corpus->get_dictionnaire());
	}
	
	
	private function __construct($documents, $dictionnaire) {
		$NB_DICO = count($dictionnaire);
		
		// Calculer les coeffs tf-idf et tf
		$result = Tools::vector_space($documents, $dictionnaire, 'both');
		$this->tf_idf = $result[0];
		$this->tf = $result[1];
		
		// Calculer les scores de chaque mot
		$this->scores = $this->tf_idf->maxCol();
		$this->scores->scale(-1);
		$scores = $this->scores->getRow(0);
		$this->scores->scale(-1);

		// Classer les mots par pertinence de classification
		$this->indices = array();
		for ($i = 0 ; $i < $NB_DICO ; $i++) {
			$this->indices[] = $i;
		}
		array_multisort($scores, $this->indices, $dictionnaire);
		$this->tf_idf = $this->tf_idf->concatenateCol($this->indices);
		$this->tf = $this->tf->concatenateCol($this->indices);
	}
	
	///////// GETTERS /////////
	
	public function get_indices() {
		return $this->indices;
	}
	
	public function get_tf() {
		return $this->tf;
	}
	
	public function get_tf_idf() {
		return $this->tf_idf;
	}
	
	public function get_score() {
		return $this->scores;
	}
}

?>
