<?php

include_once "Constants.class.php";
include_once "../entities/Graph.class.php";
include_once "../entities/Terminal.class.php";
include_once "GraphCut.class.php";


class GraphCutInitializer {
	var $alphaMatrix;
	var $kMatrix;
	var $zMatrix;
	var $matriceTrimap;
	var $tetaMeans;
	var  $tetaSigma;
	var  $gdImage;
	var  $tetaN; 
	var  $N; 
	var $K;
	var $graph;
	var $activeNodes;
	var $orphanNodes ;
	var $current_node;
	var $time;
	var $INFINITE_D = INF;
	var $K_GraphcutConstant;
	var $w;var $h;
	var $graphcut;
	var $img_name;
	var $ext;
	function __construct($kMatrix, $zMatrix, $alphaMatrix, $matriceTrimap, $tetaMeans,
			 $tetaSigma, $gdImage, $tetaN, $N, $K,$img_name,$ext) {
		$this->alphaMatrix = $alphaMatrix;
		$this->kMatrix = $kMatrix;
		$this->zMatrix = $zMatrix;
		$this->matriceTrimap = $matriceTrimap;
		$this->tetaMeans = $tetaMeans;
		$this->tetaSigma  =  $tetaSigma;
		$this->gdImage = $gdImage;
		$this->tetaN = $tetaN;
		$this->N = $N;
		$this->K = $K; 
		$this->w = count  ( $this->alphaMatrix[0] );
		$this->h = count ( $this->alphaMatrix );
		$this->img_name = $img_name;
		$this->ext=$ext;
		
		
		
	}
	
	public function computeGraphCut(){
		$this->w = count  ( $this->alphaMatrix[0] );
		$this->h = count ( $this->alphaMatrix );
		$w = $this->w ;
		$h = $this->h;
		
		$numNodes = $w*$h;
		$numEdges = 5*($w-1)*($h-1)+($w-1);
		$this->graphcut = new GraphCut($numNodes, $numEdges,$this->img_name,$this->ext);
		
		
		
		echo "\n **** initialisation du graphe";
		$this->initializeGraph();
		
		
	
		echo " \n ****calcul du max_flow";
		$flow = $this->graphcut->computeMaximumFlow(false, null);
		
		echo "\n ***** fin du calcul du maxflow : ".$flow;

		$matrice = $this->getGraphCutMatrix();
		return array("flow"=>$flow,"matriceGraphcut"=>$matrice);
		
		
				
		
	//	$initialisation = GraphCut::initializeNodeMatrix($kMatrix, $zMatrix, $this->alphaMatrix, $matriceTrimap, $tetaMeans, $tetaSigma, $gdImage, $tetaN, $N, $K);
	}
	public function getGraphCutMatrix(){
		$w = count  ( $this->alphaMatrix[0] );
		$h = count ( $this->alphaMatrix );
	    $result = array();
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			$row = array();
			for($x = 0; $x < $w; $x ++) {
				
				if($this->graphcut->getTerminal($y*$w + $x) == Terminal::FOREGROUND){		
					$row[] = 1;
				}else{
					 $row[] = 0;
				}
			}
			$result[] = $row;
		}
		//var_dump($result);
		return $result;	
	}

	

	public  function initializeGraph(){
		$w = count  ( $this->alphaMatrix[0] );
		$h = count ( $this->alphaMatrix );
		echo "\n initialisation du graph";
		// initialisation des variable
		$ZmZnEsperance = 0;
		$narc = 0;
	    											$test = 0;
		// ajout des noeud au graphe							
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {												
				
				if ($y + 1 < $h) {
					$ZmZnEsperance += pow($this->zMatrix[$y][$x]-$this->zMatrix[$y+1][$x],2);
					$narc ++;
				}
				if ($x + 1 < $w) {
					$ZmZnEsperance += pow($this->zMatrix[$y][$x]-$this->zMatrix[$y][$x+1],2);
					$narc ++;
				}
				if ($y + 1 < $h &&  $x + 1 < $w) {
					$ZmZnEsperance += pow($this->zMatrix[$y][$x]-$this->zMatrix[$y+1][$x+1],2);
					$narc ++;
				}
				if ($x - 1 >= 0 && $y+1 < $h) {
					$ZmZnEsperance += pow($this->zMatrix[$y][$x]-$this->zMatrix[$y+1][$x-1],2);
					$narc ++;
				}

			}		
		}
		
		
		// calcul de beta 
		$ZmZnEsperance = $ZmZnEsperance / $narc;
		$beta = 1 / (2 * $ZmZnEsperance);						$test = 0;
		// ajout des arcs chacun a 8 VOISINS
		// ici on utilise le fait que les arcs soient symétriques en capacité pour faire d'une pierre deux coups
		// on se contentera donc de 6 voisins au max par noeud
		$max_Ksum = 0;//utile pour le  calcul de la constante K
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				
				
				$pixelrgb = imagecolorat($this->gdImage,$x,$y);
				$cols = imagecolorsforindex($this->gdImage, $pixelrgb);
				$r = ($cols['red']);
				$g = ($cols['green']);
				$b = ($cols['blue']);
				
				
				$actual_Ksum = 0;
				if ($y + 1 < $h) {
					if ($this->alphaMatrix [$y] [$x] != $this->alphaMatrix [$y + 1] [$x]) {
						
						
						$pixelrgb = imagecolorat($this->gdImage,$x,$y+1);
						$cols = imagecolorsforindex($this->gdImage, $pixelrgb);
						$r1 = ($cols['red']);
						$g1 = ($cols['green']);
						$b1 = ($cols['blue']);
						
						
						
						
						$distance = pow ( $r - $r1, 2 ) + pow ( $g - $g1, 2 ) + pow ( $b - $b1, 2 );
						$distance = Constants::getGrabcutGamma() * exp ( - $beta * $distance );
					} else {
						$distance = 0;
					}
					$actual_Ksum += $distance;
					$n1 = $y*$this->w + $x; $n2 = ($y+1)*$this->w + $x;
					$this->graphcut->setEdgeWeight($n1,$n2,$distance);
				}
				if ($x + 1 < $w) {
					if ($this->alphaMatrix [$y] [$x] != $this->alphaMatrix [$y] [$x + 1]) {
						$pixelrgb = imagecolorat($this->gdImage,$x+1,$y);
						$cols = imagecolorsforindex($this->gdImage, $pixelrgb);
						$r1 = ($cols['red']);
						$g1 = ($cols['green']);
						$b1 = ($cols['blue']);
						
						$distance = pow ( $r - $r1, 2 ) + pow ( $g - $g1, 2 ) + pow ( $b - $b1, 2 );
						$distance = Constants::getGrabcutGamma() * exp ( - $beta * $distance );       // echo " *********************************une distance non nulle ".$x."-- ".$y;
					} else {
						$distance = 0;
					}
					$n1 = $y*$this->w + $x; $n2 = ($y)*$this->w + $x+1;
					$actual_Ksum += $distance;
					$this->graphcut->setEdgeWeight($n1,$n2,$distance);   
				}
				if ($x + 1 < $w && $y + 1 < $h) {
					if ($this->alphaMatrix [$y] [$x] != $this->alphaMatrix [$y + 1] [$x  +1  ]) {
						
						
						$pixelrgb = imagecolorat($this->gdImage,$x+1,$y+1);
						$cols = imagecolorsforindex($this->gdImage, $pixelrgb);
						$r1 = ($cols['red']);
						$g1 = ($cols['green']);
						$b1 = ($cols['blue']);
						
						
						$distance = pow ( $r - $r1, 2 ) + pow ( $g - $g1, 2 ) + pow ( $b - $b1, 2 );
						$distance = Constants::getGrabcutGamma() * exp ( - $beta * $distance );
					} else {
						$distance = 0;
					}
					$n1 = $y*$this->w + $x; $n2 = ($y+1)*$this->w + $x+1;
					$actual_Ksum += $distance;
					$this->graphcut->setEdgeWeight($n1,$n2,$distance);
				}
		
				if ($x - 1 >= 0 && $y+1 < $this->h) {
					if ($this->alphaMatrix [$y] [$x] != $this->alphaMatrix [$y+1] [$x - 1]) {
					
						
						$pixelrgb = imagecolorat($this->gdImage,$x-1,$y+1);
						$cols = imagecolorsforindex($this->gdImage, $pixelrgb);
						$r1 = ($cols['red']);
						$g1 = ($cols['green']);
						$b1 = ($cols['blue']);
						
						
						
						$distance = pow ( $r - $r1, 2 ) + pow ( $g - $g1, 2 ) + pow ( $b - $b1, 2 );
						$distance = Constants::getGrabcutGamma() * exp ( - $beta * $distance );
					} else {
						$distance = 0;
					}
					$n1 = $y*$this->w + $x; $n2 = ($y+1)*$this->w + $x-1;
					$actual_Ksum += $distance;
					$this->graphcut->setEdgeWeight($n1,$n2,$distance);
				}
				if ($actual_Ksum > $max_Ksum) {
					$max_Ksum = $actual_Ksum;
				}
			}
		}
		
	
		
		//  calcul des valeurs résiduelles des noeuds
		$K_GraphcutConstant = 1 + $max_Ksum;
		
		
		// ajout des capacités résiduelles terminales aux noueds
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				$n = $y*$w+$x;
				if ($this->matriceTrimap [$y] [$x] == "F") {
					// K pour la source
					$this->graphcut->setTerminalWeights($n,$K_GraphcutConstant,0);
					//echo "\n foreground";
				} elseif ($this->matriceTrimap [$y] [$x] == "B") {
					$this->graphcut->setTerminalWeights($n,0,$K_GraphcutConstant);
				} elseif ($this->matriceTrimap [$y] [$x] == "U") {
					// Densité gaussienne pour les deux
					
					$z = $this->zMatrix [$y] [$x];
					$k = $this->kMatrix [$y] [$x];
					
					if($this->tetaN[0][$k] != 0 && $this->tetaN[1][$k] != 0 ){
						$sigma_source = $this->tetaSigma [1] [$k];
						$means_source = $this->tetaMeans [1] [$k];
						$poids_source =     $this->tetaN [1] [$k] / $this->N;
						$sigma_sink = $this->tetaSigma [0] [$k];
						$means_sink = $this->tetaMeans [0] [$k];
						$poids_sink = $this->tetaN [0] [$k] / $this->N;
						$distance_source = $this->GaussianDensity ( $z, $sigma_source, $means_source, $poids_source );
						$distance_sink = $this->GaussianDensity ( $z, $sigma_sink, $means_sink, $poids_sink );
					    $this->graphcut->setTerminalWeights($n,$distance_source,$distance_sink);
					}elseif ($this->tetaN[0][$k] == 0  ){ // son alpha vaut 1
						$sigma_source = $this->tetaSigma [1] [$k];
						$means_source = $this->tetaMeans [1] [$k];
						$poids_source =     $this->tetaN [1] [$k] / $this->N;
						$distance_source = $this->GaussianDensity ( $z, $sigma_source, $means_source, $poids_source );
				
					    $this->graphcut->setTerminalWeights($n,$distance_source,0);
					}
					elseif ($this->tetaN[1][$k] == 0  ){ // son alpha vaut 0
						$sigma_sink= $this->tetaSigma [0] [$k];
						$means_sink = $this->tetaMeans [0] [$k];
						$poids_sink =     $this->tetaN [0] [$k] / $this->N;
						$distance_sink = $this->GaussianDensity ( $z, $sigma_sink, $means_sink, $poids_sink );
					
						$this->graphcut->setTerminalWeights($n,0,$distance_sink);
					}
				/*	echo " \n un unknow";
					echo "\n distance_source ::: ".$distance_source;
					echo "\n distance_sink ::: ".$distance_sink;
					echo "\n sigma_sink :: ".$sigma_sink;
					echo "\n means_sink :: ".$means_sink;
					echo "\n poids_sink :: ".$poids_sink;
					echo "\n sigma_source :: ".$sigma_source;
					echo "\n means_source :: ".$means_source;
					echo "\n poids_source :: ".$poids_source;
					echo "\n valeur de k ::: ".$this->kMatrix [$y] [$x];
					echo "\n valeur de cap ::: ".$cap;
						
					*/	
				}
			}
		}
		//$this->graphcut->printNodeList();
		echo " \n ***constante ". $K_GraphcutConstant;
		$this->K_GraphcutConstant = $K_GraphcutConstant;
	}
	
	public  function GaussianDensity($z, $sigma, $means, $poids) {
		if($sigma==0) return 0.5;
		return - log10 ( $poids ) + 0.5 * log10 ( $sigma ) + 0.5 * pow ( ($z - $means), 2 ) / $sigma;
	}
	
}
	













