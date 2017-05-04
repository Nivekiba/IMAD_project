<?php

class Matrix {
	private $nb_lignes;
	private $nb_colonnes;
	private $content;
	
	public function getNbLignes() {
		return $this->nb_lignes;
	}
	
	public function getNbColonnes() {
		return $this->nb_colonnes;
	}
	
	public function __construct($nb_lignes, $nb_colonnes) {
		$this->nb_lignes = $nb_lignes;
		$this->nb_colonnes = $nb_colonnes;
		$this->content = array();
		$this->setZero();
	}
	
	// Set a cell
	public function set($i, $j, $val) {
		$this->content[$i][$j] = $val;
	}
	
	// Get a cell
	public function get($i, $j) {
		return $this->content[$i][$j];
	}
	
	// Get a row
	public function getRow($i) {
		$row = array();
		for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
			$row[] = $this->get($i, $j);
		}
		return $row;
	}
	
	// Set a row
	public function setRow($i, $row) {
		for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
			$this->set($i, $j, $row[$j]);
		}
	}
	
	// Fill the matrix with zeros
	public function setZero() {
		for ($i = 0 ; $i < $this->nb_lignes ; $i++) {
			for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
				$this->set($i, $j, 0);
			}
		}
	}
	
	// Multiply a column with a scalar
	public function scaleCol($j, $scalar) {
		for ($i = 0 ; $i < $this->nb_lignes ; $i++) {
			$this->content[$i][$j] *= $scalar;
		}
	}
	
	// Multiply a row with a scalar
	public function scaleRow($i, $scalar) {
		for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
			$this->content[$i][$j] *= $scalar;
		}
	}
	
	// Multiply the matrix with a scalar
	public function scale($scalar) {
		for ($i = 0 ; $i < $this->nb_lignes ; $i++) {
			for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
				$this->set($i, $j, $scalar * $this->get($i, $j));
			}
		}
	}
	
	// Get the maximum for each column
	public function maxCol() {
		$max = new Matrix (1, $this->nb_colonnes);
		for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
			$valMax = $this->get(0, $j);
			for ($i = 1 ; $i < $this->nb_lignes ; $i++) {
				if ($valMax < $this->get($i, $j)) {
					$valMax = $this->get($i, $j);
				}
			}
			$max->set(0, $j, $valMax);
		}
		return $max;
	}
	
	// Get the maximum of a row
	public function maxRow($i) {
		$max = 0;
		for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
			if ($max < $this->get($i, $j)) {
				$max = $this->get($i, $j);
			}
		}
		return $max;
	}
	
	// Concatenate some matrix columns  // attention permutations
	public function concatenateCol($indices) {
		$nb_colonnes = count($indices);
		$resultat = new Matrix($this->nb_lignes, $nb_colonnes);
		for ($i = 0 ; $i < $this->nb_lignes ; $i++) {
			for ($j = 0 ; $j < $nb_colonnes ; $j++) {
				$resultat->set($i, $j, $this->get($i, $indices[$j]));
			}
		}
		return $resultat;
	}
	
	// Get the sum of all elements
	public function sum() {
		$sum = 0;
		for ($i = 0 ; $i < $this->nb_lignes ; $i++) {
			for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
				$sum += $this->get($i, $j);
			}
		}
		return $sum;
	}
	
	// Get the sum of all columns
	public function sumCol() {
		$sum = new Matrix(1, $this->nb_colonnes);
		for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
			$val = 0;
			for ($i = 0 ; $i < $this->nb_lignes ; $i++) {
				$val += $this->get($i, $j);
			}
			$sum->set(0, $j, $val);
		}
		return $sum;
	}
	
	// Get the transposed matrix
	public function transpose() {
		$resultat = new Matrix($this->nb_colonnes, $this->nb_lignes);
		for ($i = 0 ; $i < $this->nb_lignes ; $i++) {
			for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
				$resultat->set($j, $i, $this->get($i, $j));
			}
		}
		return $resultat;
	}
	
	// print the matrix
	public function show() {
		for ($i = 0 ; $i < $this->nb_lignes ; $i++) {
			echo "\n";
			for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
				echo $this->get($i, $j) . " ";
			}
		}
	}
	
	// Build a sub matrix
	public function sub($i, $di, $j, $dj) {
		$resultat = new Matrix($di, $dj);
		for ($x = 0 ; $x < $di ; $x++) {
			for ($y = 0 ; $y < $dj ; $y++) {
				$resultat->set($x, $y, $this->get($i+$x, $j+$y));
			}
		}
		return $resultat;
	}
	
	public function normRow($i) {
		$resultat = 0;
		for ($j = 0 ; $j < $this->nb_colonnes ; $j++) {
			$resultat += pow($this->get($i, $j), 2);
		}
		return sqrt($resultat);
	}
}

?>
