<?php
class Google_objet {
	public $items = array ();
}
class Item {
	public $title = '';
	public $link = '';
	public $snippet = '';
}

class Tools {
	
	private static $uselessWords = array('app', 'application');
	
	/*
	 * Faire une requête google à partir d'un mot
	 */
	public static function requete_moteur ($mot_cle,$moteur){
		if($moteur=="GOOGLE"){
			return Tools::requete_google($mot_cle);
		}elseif ($moteur=="BINK"){
			return Tools::requete_bink($mot_cle);
		}
	}
	public static function requete_moteur_nbre ($mot_cle,$moteur,$nbre_dizaines){
		if($moteur=="GOOGLE"){
			return Tools::requete_google_nbre($mot_cle,$nbre_dizaines);
		}elseif ($moteur=="BINK"){
			return Tools::requete_bink_nbre($mot_cle,$nbre_dizaines);
		}
	}
	public static function requete_google_nbre($mot_cle,$nombre_dizaines) {
		$items = array();
		for($i = 0; $i < $nombre_dizaines; $i ++ ){
			$google_result = null; $start = $i*10+1;
			$filename = "Saves_google/" . $mot_cle . "_".$start.".txt";           echo "\n recherche ".$mot_cle."_".$start;
			if (!file_exists($filename) || (file_exists($filename) && filesize($filename)<3000)) {
				$requete = "https://www.googleapis.com/customsearch/v1"
						. "?key=AIzaSyAZ15KfKhEtI35tipE1p_VEMfwTQedmEKI" // put your google API there c
						. "&cx=008737521736637413879:-y9h8xwzw0a"  // put your google Custum Search key there
						. "&q=". $mot_cle
						."&num=10"
						."&start=" .$start
						. "&filter=1";
				
				$google_result = self::http_get($requete);
				$handle = fopen($filename, "w");
				chmod($filename, 0777);
				fwrite($handle, $google_result);
				fclose($handle);
																		echo "*GOOGLE*";
				if( filesize($filename)> 3000 ){
					Tools::increment_count(getcwd().'/google_count');
				}
																		 
				
			} else {
		
				$handle = fopen($filename, "r");
				chmod($filename, 0777);
				$google_result = fread($handle, filesize($filename));
				fclose($handle);								            echo "\n*PREENREGISTRE*";
			}
			$result =  json_decode($google_result);                                             echo "\n requete : ". self::http_get($requete)."  \n \n google result \n $google_result \n";
			Tools::imitateMerge($items,$new_item = (array) $result->{'items'});                 //var_dump($items);
			
		}
		return $items;
	}
		
		
	public static function requete_google($mot_cle) {
		$google_result = null;
		$filename = "Saves_google/" . $mot_cle . "_1.txt";
		if (!file_exists($filename) || (file_exists($filename) && filesize($filename)<3000)) {
			
			$google_result = self::http_get("https://www.googleapis.com/customsearch/v1"
					//. "?key=AIzaSyDUdl9NUi6_ULzvVcK_q3FnSx24NKyxeUg" // ma clé API google
						//. "?key=AIzaSyAZ15KfKhEtI35tipE1p_VEMfwTQedmEKI" // put your google API there c
						//. "&cx=008737521736637413879:-y9h8xwzw0a"  // put your google Custum Search key there
						//. "&cx=008408235190647698657:azdshyko21i"  // clé d'alain moteur
						. "?key=AIzaSyDzgiZOnJ0AGajl69ifncAcOMfzHrd29x8" // clé d'Alain api
						. "&cx=002117393071396567475:ymlai2ghh4w"  // clé d'alain moteur
					. "&q=" . $mot_cle
					. "&filter=1");
			$handle = fopen($filename, "w");
			chmod($filename, 0777);
			fwrite($handle, $google_result);
			fclose($handle);
			if( filesize($filename)> 3000 ){     
				Tools::increment_count(getcwd().'/google_count');
			}																									echo "*GOOGLE*";
		} else {	
			$handle = fopen($filename, "r");
			$google_result = fread($handle, filesize($filename));
			fclose($handle);
		}
		
		return json_decode($google_result);
	}
	
	/*
	 * Savoir si un mot du nom de l'application est inutile.
	 */
	public static function is_useless($mot) {
		return in_array($mot, self::$uselessWords);
	}
	
	/*
	 * Faire une requête http sur une url
	 */
	public static function http_get($url) {
															$debut_http_get  = round(microtime(true)*1000);
		$ch = curl_init();  

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$output = curl_exec($ch);

		curl_close($ch);
															$fin_http_get  = round(microtime(true)*1000);
															//echo "\n \n ********************+++++++++++++******* temps curl".($fin_http_get-$debut_http_get);
		return $output;
	}
	
	/*
	 * Obtenir le nom de domaine à partir d'une url
	 * $type : 'all' -> www.example.com
	 	   'top-level' -> example.com
	 	   'domain' -> example
	 */
	public static function domain_name($url, $type) {
		$domain = parse_url($url, PHP_URL_HOST);
		$top = '(com|org|net|es|fr|ca|co\.uk|gov|com\.pk|org\.uk)';
		
		if ($type == 'top-level') {
			$domain = preg_replace("/^[a-zA-Z0-9-_]*\.(([a-zA-Z0-9-_]*\.)+" . $top . "*)$/", "$1", $domain);
		} elseif ($type == 'domain') {
			$domain = preg_replace("/^([a-zA-Z0-9-_]*\.)?([a-zA-Z0-9-_]*\.)+" . $top . "*$/", "$2", $domain);
			$domain = substr($domain, 0, strlen($domain)-1);
		}
		
		return $domain;
	}
	
	/*
	 * Connaître la taille de la plus grande sous chaine commune
	 */
	public function longest_common_sub_string($w1, $w2) {
		// S'assurer que le mot le plus grand est le 1er
		if (strlen($w1) < strlen($w2)) {
			$aux = $w1;
			$w1 = $w2;
			$w2 = $aux;
		}
		$length1 = strlen($w1);
		$length2 = strlen($w2);
		$result = min($length1, $length2);
	
		$continue = $result != 0;
		while ($continue) {
			// Vérifier si il existe un substring de $w1 de taille $result_length dans $w2
			$nb_sub = $length2 - $result + 1;
			$test = false;
			for ($i = 0 ; $i < $nb_sub ; $i++) {
				$sub = substr($w2, $i, $result);
				$test = $test || substr_count($w1, $sub) > 0;
			}
		
			$result --;
			$continue = !($test) && $result > -1;
		}
	
		return $result+1;
	}
	
	/*
	 * Obtenir la modélisation vector space de l'ensemble des documents
	 * sur l'ensemble des mots du dictionnaire.
	 *  - $documents : une liste de documents
	 *  - $dictionnaire : une liste de mots
	 *  - $type : 'tf-idf', 'tf', ou 'both'
	 *    Si le type est both, alors le résultat sera un array contenant
	 *    la matrice tf-idf en 0 et tf en 1.
	 */
	public static function vector_space($documents, $dictionnaire, $type) {
		$NB_DOC = count($documents);
		$NB_DICO = count($dictionnaire);
		
		$tf_idf = new Matrix($NB_DOC, $NB_DICO);
		$tf = new Matrix($NB_DOC, $NB_DICO);
		for ($i = 0 ; $i < $NB_DICO ; $i++) {
			$idf = 0;
			for ($j = 0 ; $j < $NB_DOC ; $j++) {
				$nb_occurrences = self::nb_occurrences($dictionnaire[$i], $documents[$j]);
				if ($nb_occurrences != 0) {
					$idf++;
				}
				$f = $nb_occurrences/count($documents[$j]);
				$tf_idf->set($j, $i, $f);
				$tf->set($j, $i, $f);
			}
			$idf = log($NB_DOC / $idf);
			$tf_idf->scaleCol($i, $idf);
		}
		
		if ($type == 'tf-idf') {
			return $tf_idf;
		} elseif ($type == 'tf') {
			return $tf;
		} else {
			return array($tf_idf, $tf);
		}
	}
	
	/*
	 * Compter le nombre d'occurrences d'un mot dans un document.
	 */
	public static function nb_occurrences($mot, $document) {
		$nb_occurrences = 0;
		foreach($document as $m) {
			if ($mot == $m) {
				$nb_occurrences++;
			}
		}
		return $nb_occurrences;
	}
	
	function wd_remove_accents($str)
	{
		$str = htmlentities($str, ENT_NOQUOTES, 'utf-8');

		$str = preg_replace('/&([a-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);/', '\1', $str);
		$str = preg_replace('/&[^;]+;/', '', $str);

		return $str;
	}
	
	/*
	 * Traiter une string.
	 * Le résultat est un array de mots traités.
	 */
	public static function traiter_chaine($chaine) {
		$chaine_traitee = strtolower($chaine);
		$chaine_traitee = Tools::wd_remove_accents($chaine_traitee);
		$chaine_traitee = preg_replace("/[^a-z0-9 '-]+/", " ", $chaine_traitee);
		$chaine_traitee = preg_replace("/  +/", " ", $chaine_traitee);
		$chaine_traitee = trim($chaine_traitee);
		$chaine_splitee = explode(" ", $chaine_traitee);
		$resultat = array();
		foreach ($chaine_splitee as $w) {
			if (preg_match("/[a-z]/", $w) && !in_array($w, self::$stopWords)) {
				$resultat[] = $w;
			}
		}
		return $resultat;
	}
	
	private static $stopWords = array('a','able','about','above','abroad','according',
		'accordingly','across','actually','adj','after','afterwards','again',
		'against','ago','ahead','ain\'t','all','allow','allows','almost','alone',
		'along','alongside','already','also','although','always','am','amid',
		'amidst','among','amongst','an','and','another','any','anybody','anyhow',
		'anyone','anything','anyway','anyways','anywhere','apart','appear',
		'appreciate','appropriate','are','aren\'t','around','as','a\'s','aside',
		'ask','asking','associated','at','available','away','awfully','b','back',
		'backward','backwards','be','became','because','become','becomes','becoming',
		'been','before','beforehand','begin','behind','being','believe','below',
		'beside','besides','best','better','between','beyond','both','brief','but',
		'by','c','came','can','cannot','cant','can\'t','caption','cause','causes',
		'certain','certainly','changes','clearly','c\'mon','co','co.','com','come',
		'comes','concerning','consequently','consider','considering','contain',
		'containing','contains','corresponding','could','couldn\'t','course','c\'s',
		'currently','d','dare','daren\'t','definitely','described','despite','did',
		'didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t',
		'down','downwards','during','e','each','edu','eg','eight','eighty','either',
		'else','elsewhere','end','ending','enough','entirely','especially','et','etc',
		'even','ever','evermore','every','everybody','everyone','everything','everywhere',
		'ex','exactly','example','except','f','fairly','far','farther','few','fewer',
		'fifth','first','five','followed','following','follows','for','forever',
		'former','formerly','forth','forward','found','four','from','further','furthermore',
		'g','get','gets','getting','given','gives','go','goes','going','gone','got',
		'gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t',
		'have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her',
		'here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s',
		'hi','him','himself','his','hither','hopefully','how','howbeit','however','http', 'https',
		'hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc',
		'inc.','indeed','indicate','indicated','indicates','inner','inside','insofar',
		'instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself',
		'i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last',
		'lately','later','latter','latterly','least','less','lest','let','let\'s','like',
		'liked','likely','likewise','little','look','looking','looks','low','lower','ltd',
		'm','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean',
		'meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more',
		'moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n',
		'name','namely','nbsp','nd','near','nearly','necessary','need','needn\'t','needs',
		'neither','never','neverf','neverless','nevertheless','new','next','nine','ninety',
		'no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not',
		'nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off',
		'often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto',
		'opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours',
		'ourselves','out','outside','over','overall','own','p','particular','particularly',
		'past','per','perhaps','placed','please','plus','possible','presumably','probably',
		'provided','provides','q','que','quite','qv','r','rather','rd','re','really',
		'reasonably','recent','recently','regarding','regardless','regards','relatively',
		'respectively','right','round','s','said','same','saw','say','saying','says',
		'second','secondly','see','seeing','seem','seemed','seeming','seems','seen',
		'self','selves','sensible','sent','serious','seriously','seven','several','shall',
		'shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six',
		'so','some','somebody','someday','somehow','someone','something','sometime',
		'sometimes','somewhat','somewhere','soon','sorry','specified','specify',
		'specifying','still','sub','such','sup','sure','t','take','taken','taking','tell',
		'tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s',
		'that\'ve','the','their','theirs','them','themselves','then','thence','there',
		'thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re',
		'theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll',
		'they\'re','they\'ve','thing','things','think','third','thirty','this','thorough',
		'thoroughly','those','though','three','through','throughout','thru','thus','till',
		'to','together','too','took','toward','towards','tried','tries','truly','try',
		'trying','t\'s','twice','two','u','un','under','underneath','undoing',
		'unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards',
		'us','use','used','useful','uses','using','usually','v','value','various','versus',
		'very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d',
		'welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what',
		'whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where',
		'whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever',
		'whether','which','whichever','while','whilst','whither','who','who\'d','whoever',
		'whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish',
		'with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes',
		'yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves',
		'you\'ve','z','zero');
	
	/*
	 * Calculer la distance cosinus entre deux vecteurs de taille m.
	 */
	public static function distance_cosinus($l1, $l2, $m) {
		$numerateur = 0;
		$norm1 = 0;
		$norm2 = 0;
		for ($i = 0 ; $i < $m ; $i++) {
			$numerateur += $l1[$i] * $l2[$i];
			$norm1 += $l1[$i]^2;
			$norm2 += $l2[$i]^2;
		}
		$norm1 = sqrt($norm1);
		$norm2 = sqrt($norm2);
		return $numerateur / ($norm1 * $norm2);
	}


	/*
	 * Calculer la distance euclidienne entre deux vecteurs de taille m.
	 */
	public static function distance_euclidienne($l1, $l2, $m) {
		$resultat = 0;
		for ($i = 0 ; $i < $m ; $i++) {
			$resultat += pow($l1[$i] - $l2[$i], 2);
		}
		return sqrt($resultat);
	}

	/*
	 * Créer un vecteur nul de taille m.
	 */
	public static function zeros($m) {
		$resultat = array();
		for ($i = 0 ; $i < $m ; $i++) {
			$resultat[] = 0;
		}
		return $resultat;
	}

	/*
	 * Supprimer un élément d'un vecteur.
	 */
	public static function delete($V, $elt) {
		$resultat = array();
		foreach ($V as $e) {
			if ($e != $elt) {
				$resultat[] = $e;
			}
		}
		return $resultat;
	}
	/*
	 * Fusionner deux chaines
	 */
	public static function imitateMerge(&$array1, &$array2) {
		foreach($array2 as $i) {
			$array1[] = $i;
		}
	}
	public static function imitateCopy_Results(&$array_1,&$array_2){
		foreach ($array_1 as $company => $s) {
			$array_2[$company] = $s;
		}
	}
	public static function bink_get_content($query, $start) {
		$accountKey = 'bink account key';////////////////// put your bink account key there
		$ServiceRootURL = 'https://api.datamarket.azure.com/Bing/Search/';
		$WebSearchURL = $ServiceRootURL . 'Web?$top=10&$skip=' . $start . '&$format=json&Query=';
		$context = stream_context_create ( array (
				'http' => array (
						'request_fulluri' => true,
						'header' => "Authorization: Basic " . base64_encode ( $accountKey . ":" . $accountKey )
				)
		) );
		$request = $WebSearchURL . urlencode ( '\'' . $query . '\'' );
		return file_get_contents ( $request, 0, $context );
	}
	
	public static function transform_to_google_object($bink_object) {
		$google_object = new Google_objet ();
		foreach ( $bink_object->d->results as $result ) {
			$item = new Item ();
			$item->title = $result->Title;
			$item->link = $result->Url;
			$item->snippet = $result->Description;
			$google_object->items [] = $item;
		}
		return $google_object;
	}
	public static function requete_bink_nbre($mot_cle, $nombre_dizaines) {
		$items = array ();
		for($i = 0; $i < $nombre_dizaines; $i ++) {
			$bink_result = null;
			$start = $i * 10 + 1;
			$filename = "Saves_bink/" . $mot_cle . "_" . $start . ".txt";
			if (! file_exists ( $filename ) || (file_exists ( $filename ) && filesize ( $filename ) < 10)) {
				
				$bink_result = Tools::bink_get_content ( $mot_cle, $start );
				$handle = fopen ( $filename, "w" );
				chmod($filename, 0777);
				fwrite ( $handle, $bink_result );
				fclose ( $handle );
				if( filesize($filename)> 10 ){
					Tools::increment_count(getcwd().'/bink_count');
				}																							echo "*BINK*";
			} else {
	
				$handle = fopen ( $filename, "r" );
				chmod($filename, 0777);
				$bink_result = fread ( $handle, filesize ( $filename ) );
				fclose ( $handle );
				echo "\n*PREENREGISTRE*";
			}
			$bink_result_object = json_decode ( $bink_result );
	
			$google_result_object = Tools::transform_to_google_object ( $bink_result_object );
	
			Tools::imitateMerge ( $items, $new_item = ( array ) $google_result_object->{'items'} ); // var_dump($items);
		} var_dump($items);
		return $items;
	}
	public static function requete_bink($mot_cle) {
		$bink_result = null;
		$filename = "Saves_bink/" . $mot_cle . "_1.txt";
		if (! file_exists ( $filename ) || (file_exists ( $filename ) && filesize ( $filename ) < 10)) {
			
			$bink_result = Tools::bink_get_content ( $mot_cle, 1 );
			$handle = fopen ( $filename, "w" );
			chmod($filename, 0777);
			fwrite ( $handle, $bink_result );
			fclose ( $handle );
			if( filesize($filename)> 10 ){
				Tools::increment_count(getcwd().'/bink_count');
			}
																																									echo "*BINK*";
		} else {
			$handle = fopen ( $filename, "r" );
			chmod($filename, 0777);
			$bink_result = fread ( $handle, filesize ( $filename ) );
			fclose ( $handle );
			echo "*BINK_PREENREGISTREE*";
		}
	
		$bink_result_object = json_decode ( $bink_result );
		$google_result_object = Tools::transform_to_google_object ( $bink_result_object );         var_dump($bink_result_object); var_dump($google_result_object);
		return $google_result_object;
	}
	public static function increment_count($file){
		$monfichier = fopen($file, 'r+');
		//chmod($filename, 0777);
		$pages_vues = fgets($monfichier); // On lit la première ligne (nombre de pages vues)	
		$pages_vues += 1; // On augmente de 1 ce nombre de pages vues
		fseek($monfichier, 0); // On remet le curseur au début du fichier	
		fputs($monfichier, $pages_vues); // On écrit le nouveau nombre de pages vues
		fclose($monfichier);
	}
	//requete_bink_nbre( $_POST ["searchText"],2);
}


?>
