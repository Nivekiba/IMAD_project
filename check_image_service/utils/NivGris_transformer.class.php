<?php
include_once "../entities/ImageNivGris.class.php";
include_once "../entities/ShapePoint.class.php";
include_once "Constants.class.php";
class NivGris_transformer{
	public static function transform_to_GDgray($GD_img){
		 $imgNivGris = new ImageNivGris($GD_img);
		 return $imgNivGris->getGD_imgNivGris();		 	
	}
	public static function get_contours_from_GD_color_IMG($GD_img){
		$img_sobel = NivGris_transformer::get_sobel_transform($GD_img);
		return NivGris_transformer::get_contours($img_sobel);		
	}
	public static function get_sobel_transform($GD_img
			 ,$image_name="niv_gris_test",$ext="png",$seuil = Constants::SOBEL_SEUIL){
		$imgGDNivGris = NivGris_transformer::transform_to_GDgray($GD_img);	
		// pour le test de visualisation
		Database_Manager::produce_image($imgGDNivGris,Constants::getNivGrisImagesDir() , $image_name,$ext);
		//  fin du test de visualisation
		$h = imagesy ($imgGDNivGris);
		$w = imagesx($imgGDNivGris);
		$finalSobelImage = imagecreatetruecolor($w, $h);
		$histogramme = array();
		for($i = 0; $i <= 255; $i ++) {
			$histogramme[$i] = 0;
		}
		/// application de la matrice de sobel
		for($y = 1; $y < $h-1; $y ++) {
			for($x = 1; $x < $w-1; $x ++) {
				$Gx=0;
				$Gy=0;
				//echo "\n sous matrice actuelle";
				for($j = 0; $j <= 2; $j++) {
					//echo "\n  ";
					for($i = 0; $i <= 2; $i++) {
						$color = imagecolorat ($imgGDNivGris, $x-$i+1, $y -$j+1);
						$rgb = ($color >> 16) & 0xFF;
						$g = ($color >> 8) & 0xFF;
						$b = $color & 0xFF;
						//echo $rgb."  ".$g."  ".$b." ,";
						$Gx=$Gx+$rgb * NivGris_transformer::matriceXSobel($i, $j);
						$Gy=$Gy+$rgb * NivGris_transformer::matriceYSobel($i, $j);
					}
				}
				$Gxy = min(array(floor(abs($Gx) + abs($Gy)) ,  255));	
				
				//echo "valeur du laplacien". $Gxy;
				$color = imagecolorallocate($finalSobelImage, $Gxy, $Gxy, $Gxy);
				imagesetpixel($finalSobelImage, $x, $y, $color);
					
				$histogramme[$Gxy]	= 	$histogramme[$Gxy]+1;
				
			}
		}
		//calcul du niveau seuil  
		$niveau_seuill = 180;
		$som = array_sum($histogramme);
		for($niveau_seuil = 1; $niveau_seuil <= 255; $niveau_seuil ++) {
			$histogramme[$niveau_seuil] = $histogramme[$niveau_seuil]+$histogramme[$niveau_seuil-1] ;
			if ($histogramme[$niveau_seuil]>($seuil*$som)){
				$niveau_seuill = $niveau_seuil;
				break;
			}
		}
		//echo "niveau seuil".$niveau_seuill;
		//modification de l'image finale en binaire
		for($y = 1; $y < $h-1; $y ++) {
			for($x = 1; $x < $w-1; $x ++) {
		
		    	$color = imagecolorat ($finalSobelImage, $x, $y);
				$rgb = ($color >> 16) & 0xFF;
					
				
				if($rgb>=$niveau_seuill){
					$color = imagecolorallocate($finalSobelImage, 255, 255, 255);
				}else{
					$color = imagecolorallocate($finalSobelImage, 0, 0, 0);
				}
				
				imagesetpixel($finalSobelImage, $x, $y, $color);
				$histogramme[$Gxy]	= 	$histogramme[$Gxy]+1;
		
			}
		}	
		return $finalSobelImage;
	}
	public static function get_contours($imgGDNivGris_sobel){
		$contours = array();
		$h = imagesy($imgGDNivGris_sobel);
		$w = imagesx($imgGDNivGris_sobel);
		for($y = 1; $y < $h-1; $y ++) {
			for($x = 1; $x < $w-1; $x ++) {
				$color = imagecolorat ($imgGDNivGris_sobel, $x, $y);
				$rgb = ($color >> 16) & 0xFF;
				if($rgb==255){
					$shapePoint = new ShapePoint($x, $y);
					$contours[] = $shapePoint;
				}	
			}
		}
		return NivGris_transformer::filter_contours($contours);
	}
	public static function filter_contours($contours){
		
		$result = array();
		$finalResult = array();
		foreach ($contours as $shapePoint){
			$result[]= $shapePoint;
		}
	
		while(count($result)!=0){
			$shapePointActuel = $result[0];$shapePointToDel = array();
			for($i = 1; $i < count($result); $i ++) {
				if((  abs($shapePointActuel->getX()-$result[$i]->getX())  +
					  abs($shapePointActuel->getY()-$result[$i]->getY())  
		            )< Constants::getSeuilFiltreShapePoint()) {
		            	$shapePointToDel[] = $result[$i];	
				}			
			}
			
				$shapePointToDel[] = $result[0];
				$finalResult[] = $result[0];
				$result=NivGris_transformer::my_array_diff($result, $shapePointToDel);	
			
		}	
		return $finalResult;	
	}
	public static function getHardCoreContoursImage($HardCoreContours,$heigth,$weigth){
		$hardCoreContoursImage = imagecreatetruecolor($weigth, $heigth);$trouve = false;
		for($y = 1; $y < $heigth; $y ++) {
			for($x = 1; $x < $weigth; $x ++) {
				foreach ($HardCoreContours as $contour){
					if($contour->getX()==$x && $contour->getY()==$y){
						$color = imagecolorallocate($hardCoreContoursImage, 255, 255, 255);
						imagesetpixel($hardCoreContoursImage, $x, $y, $color);
						$trouve = true; break;
					}			
				}
				if(!$trouve){
					$color = imagecolorallocate($hardCoreContoursImage, 0, 0, 0);
					imagesetpixel($hardCoreContoursImage, $x, $y, $color);
				}
				$trouve=false;
			}
		}
		return $hardCoreContoursImage;
		
	}
	public static function my_array_diff($shapePointList, $shapePointToDel){
		
		foreach ($shapePointToDel as $shapepoint){
			$n=count($shapePointList);
			for($i = 0; $i < $n; $i ++) {
				if ($shapepoint->getX()==$shapePointList[$i]->getX()
					&& $shapepoint->getX()==$shapePointList[$i]->getX()){
					array_splice($shapePointList,$i,1);
					break;
				}
			}
		}
		return $shapePointList;
	}
	public static function matriceXSobel($i,$j){
		$Mx = array(array(-1,0,1),
					array(-2,0,2),
					array(-1,0,1));
		return $Mx[$i][$j];		
	}
	public static function matriceYSobel($i,$j){
		$Mx = array(array(-1,-2,-1),
				array(0,0,0),
				array(1,2,1));
		return $Mx[$i][$j];
	}
}