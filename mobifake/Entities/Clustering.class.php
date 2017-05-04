<?php

class Clustering {
	private $k;
	private $classes;
	private $distances;
	private $centres;
	private $indices;
	private $scores;
	
	/*
	 * Effectuer la classification.
	 *  - $keywords : l'étape précédente
	 *  - $NB_KEYWORDS : le nombre de mots clés
	 *  - $NB_ITMAX : le nombre maximal d'itérations pour kmeans
	 *  - $CLUSTERING : si true, le clustering a lieu, sinon non
	 */
	public static function process_clustering($keywords, $NB_KEYWORDS, $NB_ITMAX, $CLUSTERING) {
		return new Clustering($keywords, $NB_KEYWORDS, $NB_ITMAX, $CLUSTERING);
	}
	
	
	private function __construct($keywords, $NB_KEYWORDS, $NB_ITMAX, $CLUSTERING) {
		$tf_idf = $keywords->get_tf_idf();
		$tf = $keywords->get_tf();
		$indices = $keywords->get_indices();
		$NB_DOC = $tf_idf->getNbLignes();
		
		if ($CLUSTERING) {
			// Calcul du k optimal
			$t = 0;
			for ($i = 0 ; $i < $NB_DOC ; $i++) {
				for ($j = 0 ; $j < $NB_KEYWORDS ; $j++) {
					if ($tf_idf->get($i, $j) != 0) {
						$t++;
					}
				}
			}
			$this->k = ceil(($NB_DOC * $NB_KEYWORDS) / $t);
		
		
			// Classification + calcul de la description
			$X = $tf_idf->sub(0, $NB_DOC, 0, $NB_KEYWORDS);
			$this->kmeans($X, $this->k, $NB_ITMAX);
			$this->description($tf);
		} else {
			$this->k = 1;
			$this->classes = new Matrix(1, $NB_DOC);
			$this->distances = new Matrix(1, $NB_DOC);
			$this->centres = new Matrix(1, $NB_KEYWORDS);
			$this->scores = new Matrix(1, $NB_KEYWORDS);
			$this->indices = new Matrix(1, $NB_KEYWORDS);
		}
	}
	
	// Appliquer kmeans++
	private function kmeansPP($X, $k) {
		$n = $X->getNbLignes();
		$m = $X->getNbColonnes();
		$C0 = new Matrix($k, $m);
		$valeurs = Tools::zeros($n);
		for ($i = 1 ; $i < $n ; $i++) {
			$valeurs[$i] = $i;
		}
		
		// Tirer la première ligne au hasard
		$val = rand(0, $n-1);
		$C0->setRow(0, $X->getRow($val));
		$valeurs = Tools::delete($valeurs, $val);
		// Trouver les lignes suivantes
		for ($i = 1 ; $i < $k ; $i++) {
			// Calculer les probabilités
			$distance = new Matrix(1, count($valeurs));
			for ($j = 0 ; $j < $i ; $j++) {
				foreach ($valeurs as $p) {
					$distance->set(0, $p, $distance->get(0, $p) + Tools::distance_euclidienne($X->getRow($p), $C0->getRow($j), $m));
				}
			}
			$sum = $distance->sum();
			$distance->scale(100/$sum);
		
			// Obtenir la ligne suivante en suivant les probabilités
			$val = rand(0, 100);
			$proba = 0;
			foreach ($valeurs as $p) {
				if ($proba != -1 && $proba > $val) {
					$proba = -1;
					$val = $p;
				} else {
					$proba += $distance->get(0, $p);
				}
			}
			
			$C0->setRow($i, $X->getRow($val));
			$valeurs = Tools::delete($valeurs, $val);
		}
		return $C0;
	}

	// Appliquer kmeans
	private function kmeans($X, $k, $NB_ITMAX) {
		$n = $X->getNbLignes();
		$m = $X->getNbColonnes();
	
		$k_classes = new Matrix(1, $n);
		$k_distances = new Matrix(1, $n);
		$k_centres = $this->kmeansPP($X, $k);
		
		$convergence = false;
		$it = 0;
		while (!$convergence && ($it < $NB_ITMAX)) {
			// Assigner chaque observation au centre le plus proche
			$old_k_classes = clone $k_classes;
			for ($i = 0 ; $i < $n ; $i++) {
				$k_distances->set(0, $i, -1);
				for ($p = 0 ; $p < $k ; $p++) {
					$distance = Tools::distance_euclidienne($X->getRow($i), $k_centres->getRow($p), $m);
					if ($k_distances->get(0, $i) == -1 || $k_distances->get(0, $i) > $distance) {
						$k_distances->set(0, $i, $distance);
						$k_classes->set(0, $i, $p);
					}
				}
			}
			
			// Mettre à jour les centres
			for ($p = 0 ; $p < $k ; $p++) {
				$k_centres->setRow($p, Tools::zeros($m));
				$nb_points = 0;
				for ($i = 0 ; $i < $n ; $i++) {
					if ($k_classes->get(0, $i) == $p) {
						for ($j = 0 ; $j < $m ; $j++) {
							$k_centres->set($p, $j, $k_centres->get($p, $j) + $X->get($i, $j));
							$nb_points ++;
						}
					}
				}
				if ($nb_points != 0) {
					$k_centres->scaleRow($p, 1/$nb_points);
				}
			}
		
			// Déterminer si il y a eu convergence
			$convergence = $old_k_classes == $k_classes;
			$it++;
		}
		
		$this->classes = $k_classes;
		$this->distances = $k_distances;
		$this->centres = $k_centres;
	}
	
	// Calculer les mots décrivant le mieux chaque topic
	private function description($tf) {
		$NB_DOC = $tf->getNbLignes();
		$NB_DICO = $tf->getNbColonnes();
		$k = $this->k;
		$this->scores = new Matrix($k, $NB_DICO);
		$this->indices = new Matrix($k, $NB_DICO);
		
		$tf->scale(-1);
		for ($i = 0 ; $i < $k ; $i++) {
			$mat = new Matrix($NB_DOC, $NB_DICO);
			for ($j = 0 ; $j < $NB_DOC ; $j++) {
				if ($this->classes->get(0, $j) == $i) {
					$mat->setRow($j, $tf->getRow($j));
				}
			}
	
			$scores = $mat->sumCol()->getRow(0);
			$indices = array();
			for ($j = 0 ; $j < $NB_DICO ; $j++) {
				$indices[] = $j;
			}
			
			array_multisort($scores, $indices);
			$this->scores->setRow($i, $scores);
			$this->indices->setRow($i, $indices);
		}
		$tf->scale(-1);
		$this->scores->scale(-1);
	}
	
	///////// GETTERS /////////
	
	public function get_k_opt() {
		return $this->k;
	}
	
	public function get_classes() {
		return $this->classes;
	}
	
	public function get_score() {
		return $this->scores;
	}
	
	public function get_indices() {
		return $this->indices;
	}
}

?>
