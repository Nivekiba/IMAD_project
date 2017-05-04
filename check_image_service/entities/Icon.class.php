<?php
class Icon {
	var $GD_image ;
	var $name = "" ; 
	public function __construct($GD_img,$name_){
		$this->name = $name_;
		$this->GD_image= $GD_img;
	}
	function getName() {
		return  $this->name;
	}
	function setName($name_){
		$this->name = $name_;
	}
	function getGD_image() {
		return  $this->GD_image;
	}
	function setGD_Image($GD_image){
		$this->GD_image = $GD_image;
	}
}