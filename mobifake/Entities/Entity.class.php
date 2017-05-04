<?php

class Entity {
	private $categories;
	private $name;
	private $abs;
	private $eps;
	private $delta;
	private $score;
	
	private static $textBook = array('Company', 'TradeName', 'VideoGames', 'VideoGame',
		'AndroidGames', 'BadaGames', 'ComputerGame', 'Game', 'IOSGames', 'IPadGames',
		'MaxOSXGames', 'WindowsPhoneGames', 'Business', 'Website', 'Software',
		'Video_game_franchises', 'Media_franchises', 'Newspaper', 'NewsWebsites');
	
	private static $rmBook = array('Video_hosting', 'Social_networking_websites',
		'Newspaper', 'Financial_news_agencies', 'Business_newspapers',
		'Wikipedia', 'NewsWebsites', 'Mobile_device_management_software',
		'Mobile_software_distribution_platforms');
	
	private static $okBook = array('DRM_for_Windows', 'DRM_for_OS_X');
	
	/*
	 * Créer une entité wikipédia.
	 *  - $nom : le nom de l'entité
	 *  - $documents : les documents reliés au mot étudié
	 *  - $type : 'analysis' ou 'selection'
	 */
	public function __construct($nom, $documents, $type) {
		$this->name = $nom;
		$this->computeCategories();
		if ($type == 'analysis') {
			$this->computeAbstract();
			$this->computeScore($documents);
		}
	}
	
	private function computeAbstract() {
		$format = 'json';
		$query = 'SELECT ?abstract
			WHERE {
				?x rdfs:label "' . $this->name . '"@en .
				?x dbo:abstract ?abstract .
				FILTER langMatches(lang(?abstract), \'en\').
			}';
		$url = 'http://dbpedia.org/sparql?query=' . urlencode($query) . '&format=' . $format;
		$json = json_decode(Tools::http_get($url));
		$abstracts = (array) $json->{'results'}->{'bindings'};
		
		$this->abs = Tools::traiter_chaine($abstracts[0]->{'abstract'}->{'value'});
	}
	
	private function computeCategories() {
		$format = 'json';
		$query = 'SELECT ?type
			WHERE {
				?x rdfs:label "' . $this->name . '"@en .
				?x rdf:type ?type .
			}';
		$url = 'http://dbpedia.org/sparql?query=' . urlencode($query) . '&format=' . $format;
		$json = json_decode(Tools::http_get($url));
		$categories = (array) $json->{'results'}->{'bindings'};
	
		$this->categories = array();
		foreach ($categories as $categorie) {
			$string = $categorie->{'type'}->{'value'};
			$string = preg_replace("/\d/", "", $string);
			$string = preg_replace("/.*\//", "", $string);
			$string = preg_replace("/.*#/", "", $string);
			$string = preg_replace("/.*:/", "", $string);
			if (!in_array($string, $this->categories)) {
				$this->categories[] = $string;
			}
		}
		
		$query = 'SELECT ?subject
			WHERE {
				?x rdfs:label "' . $this->name . '"@en .
				?x dct:subject ?subject .
			}';
		$url = 'http://dbpedia.org/sparql?query=' . urlencode($query) . '&format=' . $format;
		$json = json_decode(Tools::http_get($url));
		$categories = (array) $json->{'results'}->{'bindings'};
	
		foreach ($categories as $categorie) {
			$string = $categorie->{'subject'}->{'value'};
			$string = preg_replace("/.*:/", "", $string);
			if (!in_array($string, $this->categories)) {
				$this->categories[] = $string;
			}
		}
	}
	
	private function computeScore($documents) {
		// Calculer delta
		$this->delta = $this->computeDelta();
		
		// Calculer eps (moyenne des distances cosinus entre l'abstract et les documents du topic)
		$this->eps = $this->computeEps($documents);
		//$this->eps = 1;
		
		// Calculer le score final
		$this->score = $this->delta + $this->eps;
		//echo " - eps=" . $this->eps . ", delta=" . $this->delta . ", s=" . $this->score . " (" . $this->name . ")\n";
	}
	
	private function computeDelta() {
		if ($this->is_trust_entity()) {
			$delta = 1;
		} else {
			$delta = 0;
		}
		return $delta;
	}
	
	private function computeEps($documents) {
		array_unshift($documents, $this->abs);
		$n = count($documents);
		
		// Construire le dictionnaire des mots apparaissant dans l'abstract
		$dictionnaire = array();
		foreach ($this->abs as $m) {
			if (!in_array($m, $dictionnaire)) {
				$dictionnaire[] = $m;
			}
		}
		$m = count($dictionnaire);
		
		// Construire les coefficients tf-idf de l'abstract et des documents
		$tf_idf = Tools::vector_space($documents, $dictionnaire, 'tf-idf');
		
		// Calculer la distance moyenne
		$distance = 0;
		$norm_abs = $tf_idf->normRow(0);
		for ($i = 1 ; $i < $n ; $i++) {
			$dist = 0;
			for ($j = 0 ; $j < $m ; $j++) {
				$dist += $tf_idf->get(0, $j) * $tf_idf->get($i, $j);
			}
			$distance += $dist/($norm_abs * $tf_idf->normRow($i));
		}
		return $distance/($n-1);
	}
	
	public function is_trust_entity() {
		$result = false;
		foreach ($this->categories as $cat) {
			if (in_array($cat, self::$textBook)) {
				$result = true;
			}
		}
		return $result;
	}
	
	public function is_removable_entity() {
		$result = false;
		foreach ($this->categories as $cat) {
			if (in_array($cat, self::$rmBook)) {
				$result = true;
			}
		}
		return $result;
	}
	
	public function is_acceptable_entity() {
		$result = false;
		foreach ($this->categories as $cat) {
			if (in_array($cat, self::$okBook)) {
				$result = true;
			}
		}
		return $result;
	}
	
	///////// GETTERS /////////
	
	public function get_name() {
		return $this->name;
	}
	
	public function get_abstract() {
		return $this->abs;
	}
	
	public function get_categories() {
		return $this->categories;
	}
	
	public function get_score() {
		return $this->score;
	}
	
	public function get_eps() {
		return $this->eps;
	}
	
	public function get_delta() {
		return $this->delta;
	}
}

?>
