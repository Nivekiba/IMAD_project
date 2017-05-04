<?php

include_once "../entities/Icon.class.php";
include_once "Constants.class.php";
include_once "Lba_comparer.class.php";
class Lba_transformer {
	public static function function_image($x){
		if ($x > 6/29){
			return pow($x, 1/3);
		}else{
			return  (1/3)*pow((29/6),2)*$x+ 4/29;
		}
	}
	public static function xyz_format($rgb_format){
		$x = (0.618)*$rgb_format["r"]+(0.177)*$rgb_format["g"]+(0.205)*$rgb_format["b"];
		$y = (0.299)*$rgb_format["r"]+(0.587)*$rgb_format["g"]+(0.114)*$rgb_format["b"];
		$z = (0.056)*$rgb_format["g"]+(0.994)*$rgb_format["b"];
		return  array("x" => $x,"y"=>$y,"z"=>$z);
	}
	public static function rgb_to_lab_format($rgb_format){
		$xyz_format = Lba_transformer::xyz_format($rgb_format);
		$Y=$xyz_format["x"]/100;
		$X = $xyz_format["y"]/95.047;
		$Z = $xyz_format["z"]/108.883;
		$l = 116* (Lba_transformer::function_image($Y)) - 16;
		$a = 500*(Lba_transformer::function_image($X)-Lba_transformer::function_image($Y));
		$b = 200*(Lba_transformer::function_image($Y)-Lba_transformer::function_image($Z));
		return  array("l" => $l,"a"=>$a,"b"=>$b);
	
	}
	public static function get_distinct_rgb_values_occurences($Icon) {
		$img = $Icon->getGD_image();
		$distinct_triplets_rgb = array ();
		$w = imagesx ( $img );
		$h = imagesy ( $img );
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
			
				$pixelrgb = imagecolorat($img,$x,$y);
				$cols = imagecolorsforindex($img, $pixelrgb);
				$r = ($cols['red']);
				$g = ($cols['green']);
				$b = ($cols['blue']);
				
				
				$find = 0;
				$i = 0;
				foreach ( $distinct_triplets_rgb as $triplet_actuel ) {
					if ($triplet_actuel ["r"] == $r && $triplet_actuel ["g"] == $g && $triplet_actuel ["b"] == $b) {
						$n = $triplet_actuel ["n"];
						$triplet_actuel ["n"] = $n + 1;
						$find = 1;
						$distinct_triplets_rgb [$i] = $triplet_actuel;
						break;
					}
					$i ++;
				}
				if ($find == 0) {
					// echo "find ".$find;
					$rgb_array = array (
							"r" => $r,
							"g" => $g,
							"b" => $b,
							"n" => 1 
					);
					$distinct_triplets_rgb [] = $rgb_array;
				}
			}
		}
		return $distinct_triplets_rgb;
	}
	public static function filter_distinct_rgb_values_occurences($distinct_rgb_values_occurences1,
		$percentage=Constants::PERCENTAGE_RGB_VALUES,
		$nmax = Constants::NMAX_RGB_VALUES){
		$fusion2 = array();
	
		foreach($distinct_rgb_values_occurences1 as $k => $v) {
			$fusion2[$k] = $v["n"];
		}
	
		array_multisort($fusion2, SORT_DESC,$distinct_rgb_values_occurences1);
	
		$n = sizeof($distinct_rgb_values_occurences1);
		$m = floor ($n*$percentage);
		$result = array();
		for($y=0;$y<$m;$y++){
			$result[]= $distinct_rgb_values_occurences1[$y];
			if ($y>$nmax) break;
		}
		return $result;
	}
	public static function get_distinct_lba_for_distinct_rgb($distinct_rgb_values){
		$result = array();
		foreach ($distinct_rgb_values as $distinct_rgb_actuel){
			$rgb = array("r" => $distinct_rgb_actuel["r"],"g"=>$distinct_rgb_actuel["g"],"b"=> $distinct_rgb_actuel["b"]);
			$lba = Lba_transformer::rgb_to_lab_format($rgb);
			$result[] = array("l"=>$lba["l"],"b"=>$lba["b"],"a"=>$lba["a"],"n"=>$distinct_rgb_actuel["n"]);
			
		}
		return $result;
	}
}