<?php
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
