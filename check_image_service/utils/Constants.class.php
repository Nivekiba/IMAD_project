
<?php

class Constants
{
	//define('MIN_VALUE', '0.0');  WRONG - Works OUTSIDE of a class definition.
	//define('MAX_VALUE', '1.0');  WRONG - Works OUTSIDE of a class definition.
	const SEUIL_LAB_DISTANCE = 200;
	const LBA_DISTANCE_KL = 1;
	const LBA_DISTANCE_KC = 1;
	const LBA_DISTANCE_KH =1;
	const PERCENTAGE_RGB_VALUES = 0.1;      // RIGHT - Works INSIDE of a class definition.
	const NMAX_RGB_VALUES = 10;      // RIGHT - Works INSIDE of a class definition.
	const SOBEL_SEUIL = 0.8;
	const SEUIL_FILTRE_SHAPEPOINT = 8 ;
	const R_AREA_NUMBER_HISTOGRAM = 3 ;
	const TETA_AREA_NUMBER_HISTOGRAM = 3;
	const DUMMY_POINT_COST= 0;
	const POURCENTAGE_COULEURS_COUVERTES = 0.90;
	const MAXIMUM_SHAPE_DISTANCE = 1000;

	const SEUILSALIENCY = 0.9;
	const DILATATION_SIZE = 3;
	const N_GRABCUT_ITERATIONS = 4;
	const K_GMM_CLUSTER_NUMBER = 4;
	const NOMBRE_COULEUR_QUANTIFICATION= 12;
	const PROPORTION_POINT_VOISIN_LISSAGE= 0.2;
	const THRESHOLD_TRIMAP_INITIALISATION = 11;
	const GRABCUT_GAMMA = 50;
	public static function getSeuilSaliency(){
		return self::SEUILSALIENCY;
	}
	public static function getMaximumShapeDistance(){
		return self::MAXIMUM_SHAPE_DISTANCE;
	}
	public static function getDilationSize(){
		return self::DILATATION_SIZE;
	}
	public static function getGraphCutImageDir(){
		return getcwd() . "/../intermediary_images/graphcut_Images/";
	}
	public static function getThresholdTrimapInitialization(){
		return self::THRESHOLD_TRIMAP_INITIALISATION;
	}
	public static function getNGrabcutIterations(){
		return self::N_GRABCUT_ITERATIONS;
		
	}
	public static function getProportionPointVoisinsLissage(){
		return self::PROPORTION_POINT_VOISIN_LISSAGE;
	}
    public static function getPourcentageCouleursCouvertes()
	{
		return self::POURCENTAGE_COULEURS_COUVERTES;
		
	}
	public static function getGrabcutGamma(){
		return self::GRABCUT_GAMMA;
	}
	public static function getK_GmmClustersNumbers(){
		return self::K_GMM_CLUSTER_NUMBER;
	}
	public static function getLabDistanceKh()
	{
		return self::LBA_DISTANCE_KH;
	}
	public static function getLabDistanceKl()
	{
		return self::LBA_DISTANCE_KL;
	}
	public static function getLabDistanceKc()
	{
		return self::LBA_DISTANCE_KC;
	}
	public static function getNombreCouleursQuantification()
	{
		return self::NOMBRE_COULEUR_QUANTIFICATION;
	}
	public static function getDummyPointCost()
	{
		return self::DUMMY_POINT_COST;
	}
	public static function getTetaAreaNumberHistogram()
	{
		return self::TETA_AREA_NUMBER_HISTOGRAM;
	}
	public static function getRAreaNumberHistrogram()
	{
		return self::R_AREA_NUMBER_HISTOGRAM;
	}
	public static function getPercentageRgbValues()
	{
		return self::PERCENTAGE_RGB_VALUES;
	}
	public static function getExtentionAllowed()
	{
		return array("jpeg","jpg","png");
	}
	public static function getImageRepository()
	{
		return getcwd()."/../image_repository/";
	}
	/*public static function getShapePointDataBase()
	{
		return getcwd()."/../shapePoint_database";
	}*/

	public static function getShapePointDataBaseDir()
	{
		return getcwd()."/../databases/shapePoint_database/";
	}
	public static function getSalientShapesDataBaseDir()
	{
		return getcwd()."/../databases/salientShapes_database/";
	}
	public static function getSobelSeuil(){
		return self::SOBEL_SEUIL;
	}
	public static function getTestedImagesDir(){
		return getcwd() . "/../intermediary_images/tested_images/";
	}
	
	public static function getSalientImagesDir(){
		return getcwd() . "/../intermediary_images/salient_Images/";
	}
	public static function getQuantifiedImagesDir(){
		return getcwd() . "/../intermediary_images/quantification_HC_images/";
	}
	public static function getNivGrisImagesDir(){
		return getcwd() . "/../intermediary_images/nivgris_images/";
	}
	
	
	
	
	public static function getUsefullQauntifiedImageDir(){
		return getcwd() . "/../intermediary_images/usefull_quantified_images/";
	}
	
	public static function getSobelImagesDir(){
		return getcwd() . "/../intermediary_images/sobel_images/";
	}
	public static function getContoursImagesDir(){
		return getcwd() . "/../intermediary_images/contours_images/";
	}
	public static function getCacheDir(){
		return getcwd() . "/../intermediary_images/cache/";
	}
	
	/*public static function getLabDatabase()
	{
		return getcwd()."/../lab_database";
	}*/
	public static function getLabDatabaseDir()
	{
		return getcwd()."/../databases/lab_database/";
	}
	public static function getSeuilFiltreShapePoint(){
		return self::SEUIL_FILTRE_SHAPEPOINT;
	}
    
	public static function getNmaxRgbValues()
	{
		return self::NMAX_RGB_VALUES;
	}
	public static function getSeuilLabDistance(){
		return self::SEUIL_LAB_DISTANCE;
	}
}


?>
