<?php

include_once('Utils/Tools.class.php');
include_once('Utils/Matrix.class.php');

include_once('Entities/Corpus.class.php');
include_once('Entities/KeyWords.class.php');
include_once('Entities/Clustering.class.php');
include_once('Entities/ClusterAnalysis.class.php');
include_once('Entities/Entity.class.php');
include_once('Entities/Selection.class.php');
include_once('Entities/ExtractionDomaine.class.php');
include_once('Entities/ExtractionMeta.class.php');
include_once('Entities/ExtractionCopyright.class.php');
include_once('Entities/Extraction.class.php');
include_once  "config_file.php";
ini_set('max_execution_time', 0);


function gamekeeper(&$result,$app_name,$NBRE_DIZAINES_RESULTATS = 2,$MOTEUR = "GOOGLE",$VERSION = 'longue',$NB_MOTS_MIN = 100, $NB_MOTS_MAX = 10000,	
		$TAILLE_MOT_MIN = 3, $NB_KEYWORDS = 50, $NB_ITMAX = 100, $NB_DESC = 5, $NB_ENTITES_DESC = 3,	
$SEUIL_TOPIC = 1, $SEUIL_SELECTION = 75, $NB_ENTITES_SELECTION = 10, $SYLLABES_MIN = 2, $CLUSTERING = true, $STR_DISTANCE = 'lcs', $NB_ENTITES = 10, $NB_SYLLABES_MIN = 2,
$SEUIL_PHONETIQUE = 75 ){
	$final_result ;
	$app_name = str_replace ( " ", "+" ,$app_name);                 $nbre_test++;
	$app_name = str_replace ( "\n", "" ,$app_name);
	//$app_name = "coca"; //echo "modifié".$app_name;
	if ($VERSION == 'longue') {
		echo "\n\nGENERATION DU CORPUS";
		// Générer le corpus et créer le dictionnaire
		$corpus = Corpus::build_corpus($app_name, $NB_MOTS_MIN, $NB_MOTS_MAX, $TAILLE_MOT_MIN,$NBRE_DIZAINES_RESULTATS,$MOTEUR);
		$documents = $corpus->get_documents();
		$dictionnaire = $corpus->get_dictionnaire();
		$NB_DOC = count($documents);
		$NB_DICO = count($dictionnaire);
		// Affichage du corpus
		echo " : " . $NB_DOC . " documents trouvés.";
		foreach ($documents as $document) {
			echo "\n- " . count($document) . " : ";
			foreach ($document as $mot) {
				echo $mot . " ";
			}
		}
		
		echo "\n\nOBTENTION DES MOTS CLES (" . $NB_DICO . " mots dans le dictionnaire)";
		// Générer les coefficients tf-idf et tf, et obtenir les mots clés
		$keywords = KeyWords::get_keywords($corpus);
		$tf_idf = $keywords->get_tf_idf();
		$tf = $keywords->get_tf();
		$score_kw = $keywords->get_score()->getRow(0);
		$indices_kw = $keywords->get_indices();
		// Affichage des mots clés
		for ($i = 0; $i < $NB_KEYWORDS ; $i++) {
			echo "\n- " . $dictionnaire[$indices_kw[$i]] . " : " . $score_kw[$indices_kw[$i]];
		}
	
		echo "\n\nCLASSIFICATION";
		// Effectuer une classification et obtenir les descriptions des topics
		$clustering = Clustering::process_clustering($keywords, $NB_KEYWORDS, $NB_ITMAX, $CLUSTERING);
		$k_opt = $clustering->get_k_opt();
		$k_classes = $clustering->get_classes()->getRow(0);
		$score_desc = $clustering->get_score();
		$indices_desc = $clustering->get_indices();
		// Affichage du partitionnement
		if ($CLUSTERING) {
			echo " (k optimal : " . $k_opt . ")";
			for ($i = 0 ; $i < $NB_DOC ; $i++) {
				echo "\n- " . $k_classes[$i];
			}
			// Affichage des descriptions de topic
			echo "\nDescription des classes :";
			for ($i = 0 ; $i < $k_opt ; $i++) {
				echo "\n- " . $i . " : ";
				for ($j = 0 ; $j < $NB_DESC ; $j++) {
					echo $dictionnaire[$indices_kw[$indices_desc->get($i, $j)]] . "(" . $score_desc->get($i, $j) . ") - ";
				}
			}
		}
	
		echo "\n\nANALYSE DES CLUSTERS";
		// Analyser les clusters et obtenir les entités représentatives des descriptions
		$analysis = ClusterAnalysis::analyse($corpus, $keywords, $clustering, $NB_DESC, $NB_ENTITES_DESC, $SEUIL_TOPIC, $CLUSTERING,$MOTEUR);
		// Afficher les entités représentatives et leurs scores
		if ($CLUSTERING) {
			$entities = $analysis->get_entities();
			for ($i = 0 ; $i < $k_opt ; $i++) {
				echo "\nCluster " . $i . " : ";
				if ($analysis->get_validation_topic($i)) {
					echo "VALIDE";
				} else {
					echo "NON VALIDE";
				}
				for ($j = 0 ; $j < $NB_DESC ; $j++) {
					$entity = $entities->get($i, $j);
					echo "\n  - " . $dictionnaire[$indices_kw[$indices_desc->get($i, $j)]] . " -> " . $entity->get_name() . " (d=" . $entity->get_delta() . ",e=" . $entity->get_eps() . ",s=" . $entity->get_score() . ")";
				}
			}
		}
	
		echo "\n\nSELECTION DES DOCUMENTS";
		// Sélectionner les documents en lien avec le nom d'application
		$selection = Selection::select($corpus, $clustering,
				$analysis, $app_name, $SEUIL_SELECTION,
				$NB_ENTITES_SELECTION, $SYLLABES_MIN, $STR_DISTANCE,$MOTEUR);
		$selected = $selection->get_selected();
		$score_select = $selection->get_score();
		// Afficher les documents sélectionnés
		for ($i = 0 ; $i < $NB_DOC ; $i++) {
			echo "\n- " . $corpus->get_link($i) . " : " . $selection->get_status($i) . " (" . $score_select->maxRow($i) . " %)";
		}
	
		echo "\n\nEXTRACTION DE L'AUTEUR : ";
		// Extraire le nom de l'entité
		$extraction = Extraction::extract($corpus, $selection);
		// Affichage
		$company = $extraction->get_company();
		$score_company = $extraction->get_scores()[$company];
		if ($company == '') {
			echo "Aucune entité détectée.";
		} else {
			echo "Correspondances détectées avec :";
			foreach ($extraction->get_scores() as $company => $s) {
				echo "\n- " . $company . " (" . $s . ")";
			}
		}
		Tools::imitateCopy_Results($extraction->get_scores(),$result);		
		return $extraction->get_scores();
	} else {
		$requete = $app_name . "+site:en.wikipedia.org";
		//$json = Tools::requete_google($requete);
		$json = Tools::requete_moteur($requete,$MOTEUR); 
		$items = (array) $json->{'items'};
	
		// Calculer les scores
		$i = 0;
		$score = array();
		$entities = array();
		for ($i=0 ; $i < $NB_ENTITES ; $i++) {
			$nom = urldecode($items[$i]->{'link'});
			$nom = preg_replace("/.*\//", "", $nom);
			$nom = str_replace("_", " ", $nom);
			$entity = new Entity($nom, null, 'other');
			$score[] = calculer_score($entity, $SEUIL_PHONETIQUE, $NB_SYLLABES_MIN);
			$entities[] = $entity;
		}
	
		// Ne garder que le meilleur score
		$max = 0;
		$imax = -1;
		for ($i=0 ; $i < $NB_ENTITES ; $i++) {
			if ($score[$i] > $max) {
				$max = $score[$i];
				$imax = $i;
			}
		}
	
		// Afficher le résultat
		if ($imax == -1) {
			echo "\nPas d'entité de confiance trouvée..";
		} else {
			echo "\nCorrespondances détectées avec :";
			for ($i=0 ; $i < $NB_ENTITES ; $i++) {
				if ($score[$i] > 0) {
					echo "\n- " . $entities[$i]->get_name() . " (" . $score[$i] . ")";
				}
			}
		}
		
		// valeur à retourner
		$final_result = array();
		for ($i=0 ; $i < $NB_ENTITES ; $i++) {
			if ($score[$i] > 0) {
				$final_result[] = array($entities[$i]->get_name() => $score[$i] );
				
			}
		}
		Tools::imitateCopy_Results($final_result,$result);
		return $final_result;
		
	}
	
}

// Calculer le score d'une entité
function calculer_score($entity, $SEUIL, $NB_SYLLABES_MIN) {
	global $app_name;
	
	// Calculer le score métaphone
	$score = 0;
	foreach (explode("+", $app_name) as $mot_app) {
		$meta_app = metaphone($mot_app);
		if (!Tools::is_useless($mot_app) && strlen($meta_app) >= $NB_SYLLABES_MIN) {
			$meta_titre = '';
			foreach (explode(" ", $entity->get_name()) as $mot_titre) {
				$meta_titre = $meta_titre . " " . metaphone($mot_titre);
			}
		
			$distance = Tools::longest_common_sub_string($meta_app, $meta_titre);
			$distance *= 100/strlen($meta_app);
		
			if ($distance > $score) {
				$score = $distance;
			}
		}
	}
	
	if ($entity->is_trust_entity() && $score >= $SEUIL) {
		return $score;
	} else {
		return -1;
	}
}


