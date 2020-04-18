<?php
/**
 * script: MasterConfig.php
 * client: plf
 *
 * @version V4.10.10062015
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 *
 */
//GLOBAIS
date_default_timezone_set('Europe/Lisbon');
setlocale(LC_ALL, array('pt_PT.UTF-8','pt_PT@euro','pt_PT','portuguese'));


$NOW = date("YmdHis");
$LANGPAGES = array("pt", "en","fr","de");//ISO 639-1 Language Codes
$LANGFLAGS = array("pt"=>"imagens/flags/Portugal.png");

$FLAGSMODE= array("ADD","ADDK","UPDATE","CLONE","SAVE","DELETE","DELETEK","CHANGE","CHANGEK","FOLDER","MESSAGES","OPTIONS","FILE","FILEOP","INFO","SUBMENU","NOTES","GALLERYF","GALLERY","SEARCH","ORDER","SAVEORDER","MODULE","FORNEWS","FORMODULE","GROUP","SEND","SENDK","PASS","READ","CLOSEGROUP","ITENSTAT","STATS","SPECIALP","OTHP","RELATIONS");

//GLOBAIS
$WHITELIST = array("_CURSO", "_CURSOS", "_EVENTOS", "_EVENTO", "blog", "_POST","contatos","portfolio");
$FILTERLIST = array("services","products");//lista de filtros de mensagens


define('_RURL', 'http://localhost/kyusho/');
define('_RPATH', 'D:\xampp\htdocs\kyusho\\');

/*define('_LC','donsdarte.com');
define('_DB', 'donsdart_dinamicgest');
define('_US', 'donsdart_mgerard');
define('_PS', '1968@bcl_MGL');*/

//define("_PDOM","mysql:host=localhost;dbname=dinamicgest2;charset=utf8");

define("_PDOM","mysql:host=localhost;dbname=kyushomgpdinamic;charset=UTF8");
define('_LC','127.0.0.1');
define('_DB', 'kyushomgpdinamic');
define('_US', 'root');
define('_PS', '');
define('_SOMEK',md5("socket"._RURL));

define("_LOC","pt_PT");
define('_LOCTIME','Europe/Lisbon');//define a hora local

//define('_MFILE','/media/gerardo/DATA/xampp/hinc/DinamicGest-v5/MGPMaster.json');//caminho para o arquivo Master.json
define('_MFILE','D:\xampp\htdocs\kyusho\DinamicGestC\EPKMaster.json');

define('_IMAGEPATH', _RPATH.'galeria\\');
define('_VIDEOPATH', _RPATH.'videos/');


define("_NAMESITE", "mgpdinamic.com");


define("_IMAGEURL", _RURL."galeria");
define("_VIDEOURL", _RURL."videos");




define('_IMAGE', _RPATH.'imagens/');
define('_ANEXOSURL', _RPATH.'anexos/');



define('_SOCIALPUB','www.mgpdinamic.com/face.php');//endereço de dinamicNews para publicar nas redes sociais

?>