<?php



include_once "../utils/Constants.class.php";
include_once "../utils/ShapePointDatabaseManager.class.php";
ShapePointDatabaseManager::refresh_shapePoint_database(Constants::getImageRepository(),Constants::getShapePointDataBaseDir());



