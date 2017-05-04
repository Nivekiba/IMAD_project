<?php

class ClusterAnalysis {
	private $entities;
	private $topic_valide;
	private $moteur_recherche;
	
	/*
	 * Analyser les topics
	 *  - $corpus, $keywords, $clustering : les étapes précédentes
	 *  - $NB_DESC : le nombre de mots de description par topic
	 *  - $NB_ENTITES : le nombre d'entités wikipédia à considérer
	 *  - $SEUIL_TOPIC : le seuil au dela duquel un topic est validé
	 */
	public static function analyse($corpus, $keywords, $clustering, $NB_DESC, $NB_ENTITES, $SEUIL_TOPIC, $CLUSTERING,$MOTEUR_RECHERCHE) {
		$k_opt = $clustering->get_k_opt();
		$dictionnaire = $corpus->get_dictionnaire();
		$documents = $corpus->get_documents();
		$NB_DOC = count($documents);
		$indices_kw = $keywords->get_indices();
		$indices_desc = $clustering->get_indices();
		$k_classes = $clustering->get_classes()->getRow(0);
		$mots = new Matrix($k_opt, $NB_DESC);
		for ($i = 0 ; $i < $k_opt ; $i++) {
			for ($j = 0 ; $j < $NB_DESC ; $j++) {
				$mots->set($i, $j, $dictionnaire[$indices_kw[$indices_desc->get($i, $j)]]);
			}
		}
		$topics = array();
		for ($i = 0 ; $i < $k_opt ; $i++) {
			$ligne = array();
			for ($j = 0 ; $j < $NB_DOC ; $j++) {
				if ($k_classes[$j] == $i) {
					$ligne[] = $documents[$j];
				}
			}
			$topics[] = $ligne;
		}
		return new ClusterAnalysis($mots, $topics, $NB_ENTITES, $SEUIL_TOPIC, $CLUSTERING,$MOTEUR_RECHERCHE);
	}
	
	
	
	private function __construct($mots, $topics, $NB_ENTITES, $SEUIL_TOPIC, $CLUSTERING,$MOTEUR_RECHERCHE) {
		$k = $mots->getNbLignes();
		$nb_mots = $mots->getNbColonnes();
		$this->entities = new Matrix($k, $nb_mots);
		$this->topic_valide = array();
		$this->moteur_recherche = $MOTEUR_RECHERCHE;
		
		for ($i = 0 ; $i < $k ; $i++) {
			$score_max = -1;
			if ($CLUSTERING) {
				for ($j = 0 ; $j < $nb_mots ; $j++) {
					// Garder l'entité ayant le meilleur score
					$entities = $this->computeEntities($mots->get($i, $j), $topics[$i], $NB_ENTITES);
					$nb_entities = count($entities);
					$entity = $entities[0];
					$score = $entity->get_score();
					for ($p = 1 ; $p < $nb_entities ; $p++) {
						if ($score < $entities[$p]->get_score()) {
							$entity = $entities[$p];
							$score = $entity->get_score();
						}
					}
				
					$this->entities->set($i, $j, $entity);
					if ($score_max == -1 || $score_max < $score) {
						$score_max = $score;
					}
				}
				
				if ($score_max >= $SEUIL_TOPIC) {
					$this->topic_valide[] = true;
				} else {
					$this->topic_valide[] = false;
				}
			} else {
				$this->topic_valide[] = true;
			}
		}
	}

	// Obtenir une liste d'entités associées à un mot
	// $documents est une liste de documents associés au topic étudié
	private function computeEntities($mot, $documents, $NB_ENTITES) {
		$requete = $mot . "+site:en.wikipedia.org";
		//$json = Tools::requete_google($requete);
		$json = Tools::requete_moteur($requete,$this->moteur_recherche);
		$items = (array) $json->{'items'};
		
		$resultat = array();
		for ($i = 0 ; $i < $NB_ENTITES ; $i++) {
			$nom = $items[$i]->{'title'};
			$nom = ucfirst(preg_replace("/ - Wikipedia.*$/", "", $nom));
			$resultat[] = new Entity($nom, $documents, 'analysis');
		}
	
		return $resultat;
	}
	
	///////// GETTERS /////////
	
	public function get_entities() {
		return $this->entities;
	}
	
	public function get_validation_topic($i) {
		return $this->topic_valide[$i];
	}
}

?>
