<?php
class ImageNivGris{
	var $GD_img;
	
	public function __construct($GD_img){
		$this->GD_img = $GD_img;	
	}
	
	public function getNivGris($x,$y){//entre 0 et 1
		
		
		$pixelrgb = imagecolorat($this->GD_img,$x,$y);
		$cols = imagecolorsforindex($this->GD_img, $pixelrgb);
		$r = ($cols['red']);
		$g = ($cols['green']);
		$b = ($cols['blue']);
		
		$canalR =$r/255; 
		$canalG =$g/255;
		$canalB =$b/255;
		if ($canalB<=0.04045){
			$canalB= $canalB/12.92;		
		}else{
			$canalB = pow(($canalB+0.055)/1.055,2.4);		
		}
		if ($canalR<=0.04045){
			$canalR= $canalR/12.92;
		}else{
			$canalR = pow(($canalR+0.055)/1.055,2.4);
		}
		
		if ($canalG<=0.04045){
			$canalG= $canalB/12.92;
		}else{
			$canalG = pow(($canalG+0.055)/1.055,2.4);
		}		
		return abs(($canalR*0.2126+$canalG*0.7152+$canalB*0.0722));				
	}
		
	public function getw(){
		return imagesx($this->GD_img);
	}
	public function geth(){
		return imagesy($this->GD_img);
	}
	public function getGD_imgNivGris(){// entre O et 255
		$w = imagesx ( $this->GD_img );
		$h = imagesy ( $this->GD_img );
		$image = imagecreatetruecolor($w, $h);
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			echo "\n";
			for($x = 0; $x < $w; $x ++) {
				
				/*$l = $this->getNivGris($x, $y);
				if ($l<=0.0031308){
					$rgb = floor($l*12.92*255);
				}else{
					$rgb = floor((pow($l,1/2.4)*1.055-0.005)*255);
				}
				
				*/
				$pixelrgb = imagecolorat($this->GD_img,$x,$y);
				$cols = imagecolorsforindex($this->GD_img, $pixelrgb);
				$r = ($cols['red']);
				$g = ($cols['green']);
				$b = ($cols['blue']);
				
			
				//echo $r." ".$g." ".$b." -  ";
				$l = max(array($r,$g,$b)); 
				$color = imagecolorallocate($image, $l, $l, $l);
				imagesetpixel($image, $x, $y, $color);
				
			}
		}
		return $image;
		
	}
	/*public function getGD_imgNivGris(){// entre O et 255
		$w = imagesx ( $this->GD_img );
		$h = imagesy ( $this->GD_img );
		$image = imagecreatetruecolor($w, $h);
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				$l = $this->getNivGris($x, $y);
				if ($l<=0.0031308){
					$rgb = floor($l*12.92*255);
				}else{
					$rgb = floor((pow($l,1/2.4)*1.055-0.005)*255);
				}
				$color = imagecolorallocate($image, $rgb, $rgb, $rgb);
				imagesetpixel($image, $x, $y, $color);
	
			}
		}
		return $image;
	
	}*/
}