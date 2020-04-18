<?php
/*
 * master config dinamicShop
 * 
 * 
 * 
 */
//GLOBAIS
date_default_timezone_set('Europe/Lisbon');
setlocale(LC_ALL, array('pt_PT.UTF-8','pt_PT@euro','pt_PT','portuguese'));


$NOW = date("YmdHis");
$LANGPAGES = array("pt","en");//ISO 639-1 Language Codes
$LANGFLAGS = array("pt"=>"imagens/port.png","en"=>"imagens/ing.png","fr"=>"imagens/fran.png","es"=>"imagens/spai.png");

$FLAGSMODE= array("ADD","ADDK","UPDATE","CLONE","SAVE","DELETE","DELETEK","CHANGE","CHANGEK","FOLDER","MESSAGES","OPTIONS","FILE","FILEOP","INFO","SUBMENU","NOTES","GALLERYF","GALLERY","SEARCH","ORDER","SAVEORDER","MODULE","FORNEWS","FORMODULE","GROUP","SEND","SENDK","PASS","READ");

define('_RURL', 'http://kyusho.pt/');
define('_RPATH', '/var/www/vhosts/kyusho.pt/httpdocs/');

define('_LC','localhost');
define('_DB','comercmgp211com17739_kyusho');//dinamicshop
define('_US', 'comer_mainuserk');//
define('_PS', 'q@lcF163');//
define('_SOMEK',md5("socket"._RURL));

define('_LOCTIME','Europe/Lisbon');//define a hora local

define('_MFILE','/var/www/vhosts/kyusho.pt/DinamicGestC/');
define('_IMAGEPATH', _RPATH.'galeria/');
define('_VIDEOPATH', _RPATH.'videos/');


define("_NAMESITE", "kyusho.pt");


define("_IMAGEURL", _RURL."galeria");
define("_VIDEOURL", _RURL."videos");




define('_IMAGE', _RPATH.'imagens/');
define('_ANEXOSURL', _RPATH.'anexos/');



define('_SOCIALPUB','www.mgpdinamic.com/face.php');//endereço de dinamicNews para publicar nas redes sociais

?>