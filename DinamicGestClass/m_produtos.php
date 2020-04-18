<?php
/* SCRIPT m_produtos V2.00
  27-60-2014
  COPYRIGHT MANUEL GERARDO PEREIRA 2014
  TODOS OS DIREITOS RESERVADOS
  CONTACTO: GPEREIRA@MGPDINAMIC.COM
  WWW.MGPDINAMIC.COM 
  site:kyusho.pt 
 */
ini_set('display_errors',0);
session_start();

require '/var/www/vhosts/kyusho.pt/DinamicGestC/MasterConfig.php';
require_once '/var/www/vhosts/kyusho.pt/DinamicGestC/Products.php';
require '/var/www/vhosts/kyusho.pt/DinamicGestC/KeyWords.php';

if (isset($_POST['flag'])) {

    if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE'])) {

        echo '{"alert":"Não foi possivel realizar esta operação. CODE:001"}';
        exit;
    }
} else {

    $_POST['flag'] = FALSE;
    echo '{"alert":"Não foi possivel realizar esta operação. CODE:002"}';
    exit;
}

$toke = (isset($_POST['module'])) ? $_POST['module'] : NULL;

switch ($toke){
    case "portfolio":
        $js = "JSEMINARIOS";
        break;
    case "workshop":
        $js = "JCURSOS";
        break;
}

$products = new Products($js);
$pcopnfig = $products->get_product_config();
$key = new KeyWords();


if ($products->check()) {

    switch ($_POST['flag']) {

        case "ORDER":
            echo $products->order();
            break;
        case "ADD":
            echo $products->make_product(NULL);
            break;
        case "UPDATE":
            echo $products->edit();
            break;
        case "SAVEORDER":
            echo $products->ordering();
            break;
        case "FILE":
        case "FILEOP":
            echo $products->make_file();
            break;
        case "FOLDER":
        case "CHANGE":
        case "MODULE":
            echo $products->make_folders($pcopnfig);
            break;
        case "FORNEWS":
            echo $products->send_for_newsletter();
            break;
        case "SAVE":
            echo $products->save_product();
            break;
        case "NOTES":
            echo $products->show_notes();
            break;
        case "MESSAGES":
            echo $products->show_messages($_POST['toke'], $pcopnfig['name']);
            break;
        case "DELETE":
            echo $products->delete_product();
            break;
        case "SEARCH":
            echo $products->search($pcopnfig['search'], $pcopnfig['table']);
            break;
        case "ADDK":
            echo $key->add_words();
            break;
        case "CHANGEK":
            echo $key->change_category();
            break;
        case "DELETEK":
            echo $key->delete_word();
            break;
        case "SENDK":
            echo $key->send_words();
            break;
    
    }
}
?>