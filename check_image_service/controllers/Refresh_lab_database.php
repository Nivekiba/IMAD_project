<?php


include_once "../entities/Icon.class.php";
include_once "../utils/Constants.class.php";
include_once "../utils/Lba_comparer.class.php";
include_once "../utils/Lba_transformer.class.php";
include_once "../utils/Databases_Manager.class.php";

Database_Manager::refresh_lab_database(Constants::getImageRepository(),Constants::getLabDatabaseDir());



