<?php
class ShapePoint {
	var $x ;
	var $y ;
	var $histogram;
	
	public function __construct($x,$y){
		$this->x = $x;
		$this->y =  $y;
	}
	function getX() {
		return  $this->x;
	}
	function setX($x){
		$this->x = $x;
	}
	function getY() {
		return  $this->y;
	}
	function setY($y){
		$this->y = $y;
	}	
	function getHistogram() {
		return  $this->histogram;
	}
	function setHistogram($y){
		$this->histogram = $y;
	}
}