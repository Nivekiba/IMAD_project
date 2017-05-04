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



ini_set('max_execution_time', 0);

/////////////////////////////////// PARAMETRES DE L'ALGORITHME ///////////////////////////////////
//$MOTEUR_RECHERCHE = "BINK";
$MOTEUR_RECHERCHE = "GOOGLE";
$NBRE_DIZAINES_RESULTATS = 2; // le nombre de dizaines du résultats de la recherche google ( du corpus final ) 
$VERSION = 'longue';		// La version de l'algo : 'courte' ou 'longue'

// Version longue
$NB_MOTS_MIN = 100;		// Nombre de mots minimum pour qu'un document soit accepté dans le corpus
$NB_MOTS_MAX = 10000;		// Nombre de mots maximum pour qu'un document soit accepté dans le corpus
$TAILLE_MOT_MIN = 3;		// Nombre de lettres minimum pour qu'un mot soit accepté dans le dictionnaire
$NB_KEYWORDS = 50;		// Nombre de mots clés (max) conservés pour effectuer la classification
$NB_ITMAX = 100;		// Nombre max d'itérations pour kmeans
$NB_DESC = 3;			// Nombre de mots pour décrire un topic (plus on en met, plus on a de recherches google à faire...)
$NB_ENTITES_DESC = 3;		// Nombre d'entités wiki étudiées pour chaque mot de la description d'un topic
$SEUIL_TOPIC = 1;		// Seuil au dela duquel un topic est considéré comme valide
$SEUIL_SELECTION = 75;		// Seuil au dela duquel un document est considéré comme phonétiquement valide (entre 0 et 100)
$NB_ENTITES_SELECTION = 10;	// Nombre d'entitées wiki étudiées pour chaque document lors de la sélection (filtrage anti-trompeurs & repêchage)
$SYLLABES_MIN = 2;		// Nombre minimum de syllabes minimum pour affecter un score phonétique à un mot du nom de l'app (en dessous, le score reste à 0)
$CLUSTERING = false;		// Si on effectue la classification ou non
$STR_DISTANCE = 'lcs';		// La distance de string choisie : 'similarity', 'levenshtein', ou 'lcs' (longest-common-substring)

// Version courte
$NB_ENTITES = 10;		// Nombre d'entités wiki étudiées
$NB_SYLLABES_MIN = 2;		// Nombre de syllabes minimum pour étudier un mot du nom de l'application
$SEUIL_PHONETIQUE = 75;		// Seuil à partir duquel on accepte une entité wiki (phonétiquement parlant)

/////////////////////////////////// OBTENTION DU NOM DE L'APP ///////////////////////////////////

/*
include_once  "AppNameExtractor.php";
//$app_name = "n7";
//$app_name= "orange cache";

$app_name = str_replace ( " ", "+" ,$app_name);
$fin_script  = round(microtime(true)*1000);
echo ' \n nom à rechercher : '.$app_name;
echo "\n resultat  ".($fin_script-$debut_script)."\n";
echo 'extraction directory' . ': ' . TailleDossier(getcwd().'/extraction_directory_'.$apk_name.'/') . ' bytes';
*/

////////// /////////////////////// GENERATION DU CORPUS /////////////////////////////////////////
	// Si espace, mettre un +
	
/*
 * LISTE DES CATEGORIES DEJÀ TESTEES. 
 * pays_dev_ge_poly (pour le formattage , à refaire)
 * pays_dev_ge_npoly (pour le formattage , à refaire) 
 * 
 */
	
// PREPARATION DES GROUPES DE CATEGORIES DE TEST.
$fichiers_test = array("pays_dev_ge_poly.txt","pays_dev_ge_npoly.txt",
					"pays_dev_pe_poly.txt","pays_dev_pe_npoly.txt",
					"pays_em_ge_poly.txt","pays_em_ge_npoly.txt",
					"pays_em_pe_poly.txt","pays_em_pe_npoly.txt",
					"edit_app_poly.txt","edit_app_npoly.txt",
					"pays_dev_ge_poly_com.txt","pays_dev_ge_npoly_com.txt",
					"pays_dev_pe_poly_com.txt","pays_dev_pe_npoly_com.txt",
					"pays_em_ge_poly_com.txt","pays_em_ge_npoly_com.txt",
					"pays_em_pe_poly_com.txt","pays_em_pe_npoly_com.txt",
					"edit_app_poly_com.txt","edit_app_npoly_com.txt","negatifs.tx");
$pays_dev = array("pays_dev_ge_poly.txt","pays_dev_ge_npoly.txt",
					"pays_dev_pe_poly.txt","pays_dev_pe_npoly.txt");
$pays_em = array("pays_em_ge_poly.txt","pays_em_ge_npoly.txt",
					"pays_em_pe_poly.txt","pays_em_pe_npoly.txt");

$pays_dev_ge_poly = array("pays_dev_ge_poly.txt");
$pays_dev_ge_npoly = array("pays_dev_ge_npoly.txt");
$pays_dev_pe_poly  = array("pays_dev_pe_poly.txt");


// DEBUT DE LA GRANDE BOUCLE DE TEST

//$ensemble_test = $pays_dev_pe_poly;
$ensemble_test = array($_POST["test_file_name"]);
$numero_nom_for_test = $_POST["key"];
//echo "-----------".$ensemble_test;
$boucle_fichier = 0;
foreach ($ensemble_test as $fichier){                                      $boucle_fichier++;
	$lines = file(getcwd().'/tested/'.$fichier);
	$items_app_names = array();                                            $nbre_test = 0;
	foreach ($lines as $lineNumber => $lineContent) {
		$result = array();                                                 
		$app_name = str_replace ( " ", "+" ,$lineContent);                 $nbre_test++;
		$app_name = str_replace ( "\n", "" ,$app_name);
		echo "\n nom de l'app : /".$app_name."/";                                             $debut_script_nom  = round(microtime(true)*1000); 
	    gamekeeper($result,$app_name,$NBRE_DIZAINES_RESULTATS,$MOTEUR = $MOTEUR_RECHERCHE);   $fin_script_nom  = round(microtime(true)*1000); 
		$items_app_names[$app_name] = array();                                                echo "\n temps pour ce nom  ".($fin_script_nom-$debut_script_nom)."\n";
		Tools::imitateCopy_Results($result, $items_app_names[$app_name])	;	  //if($nbre_test>=1)   break;                 
	}
	$string = json_encode ( $items_app_names,JSON_PRETTY_PRINT);            
	unlink(getcwd().'/results_'.$MOTEUR_RECHERCHE.'/'.$fichier);
	$fichier_result = fopen(getcwd().'/results_'.$MOTEUR_RECHERCHE.'/'.$fichier, 'w');
	chmod(getcwd().'/results_'.$MOTEUR_RECHERCHE.'/'.$fichier, 0777);
	fputs($fichier_result, $string);
	fclose($fichier_result);
																  //if($boucle_fichier>=1)   break; 	
}



//gamekeeper($app_name);












function gamekeeper(&$result,$app_name,$NBRE_DIZAINES_RESULTATS = 1,$MOTEUR = "GOOGLE",$VERSION = 'longue',$NB_MOTS_MIN = 100, $NB_MOTS_MAX = 10000,	
		$TAILLE_MOT_MIN = 3, $NB_KEYWORDS = 50, $NB_ITMAX = 100, $NB_DESC = 3, $NB_ENTITES_DESC = 3,	
$SEUIL_TOPIC = 1, $SEUIL_SELECTION = 75, $NB_ENTITES_SELECTION = 10, $SYLLABES_MIN = 2, $CLUSTERING = false, $STR_DISTANCE = 'lcs', $NB_ENTITES = 10, $NB_SYLLABES_MIN = 2,
$SEUIL_PHONETIQUE = 75 ){
	$final_result ;
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
		//return $extraction->get_scores();
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


