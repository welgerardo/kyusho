<?php
/**
 * script: m_produtos.php
 * client: EPKyusho
 *
 * @version V4.00.281014
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 *
 */

ini_set('display_errors', 0);
session_start();

require 'add_files.php';
require_once 'Products.php';
require 'KeyWords.php';
require_once 'Newsletter.php';

if (isset($_POST['flag']))
{
    if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
    {
        echo '{"alert":"Não foi possivel realizar esta operação. CODE:001"}';
        exit ;
    }
}
else
{
    $_POST['flag'] = FALSE;
    echo '{"alert":"Não foi possivel realizar esta operação. CODE:002"}';
    exit ;
}

$toke = (isset($_POST['module'])) ? $_POST['module'] : NULL;
$key = new KeyWords();

switch ($toke) {
    case "products" :
        $js = "JPRODUTOS";
        break;
    case "services" :
        $js = "JSERVICOS";
        break;
    case "portfolio" :
        $js = "JPORTFOLIO";
        break;
}

if (!$js)
{
    echo '{"alert":"Não foi possivel realizar esta operação. CODE:003"}';
    exit ;
}

$products = new Products($js);
$pcopnfig = $products -> get_product_config();

if ($products -> check())
{
    switch ($_POST['flag']) {

        case "ORDER" :
            $order = new GestOrder($pcopnfig);
            echo $order -> make_order("spOrderProduct",NULL);
            break;
        case "ADD" :
            $addedit = new OperationsBar($pcopnfig);
            echo $addedit -> add_item_sheet();
            break;
        case "UPDATE" :
            echo $products -> edit_item($_POST['toke']);
            break;
        case "CLONE" :
            echo $products -> clone_item($_POST['toke']);
            break;
        case "SAVEORDER" :
            $order = new GestOrder($pcopnfig);
            echo $order -> save_new_order();
            break;
        case "FILE" :
        case "FILEOP" :
            echo $products -> make_sheet($_POST['toke']);
            break;
        case "CHANGE" :
            $folder = new GestFolders();
            echo $folder -> change_folder("spChangeProductFolder",array($_POST['toke'], $_POST['gal']));
            break;
        case "FOLDER" :
            $folder = new GestFolders();
            echo $folder -> make_folders("spProductFolders");
            break;
        case "MODULE" :
            $folder = new GestFolders("MODULE");
            echo $folder -> make_folders("spProductFolders");
            break;
        case "SPECIALP" :
            $folder = new GestFolders("MODULE");
            echo $folder -> make_folders("spProductSpecialP");
            break;
        case "FORNEWS" :
            $nwsl = new Newsletter($js);
            echo $nwsl -> get_item2insert($_POST['toke']);
            break;
        case "OTHP":
            echo $products->for_module($_POST['toke'],$js);
            break;
        case "SAVE" :
            $addedit = new OperationsBar($pcopnfig);
            echo $addedit -> save_item("spUpdateProduct","spInsertProduct");
            break;
        case "NOTES" :
            echo $products -> show_notes($_POST['toke']);
            break;
        case "MESSAGES" :
            $mess = new GestMessages();
            echo $mess -> get_messages($_POST['toke'], $pcopnfig['name']);
            break;
        case "DELETE" :
            $addedit = new OperationsBar($pcopnfig);
            echo $addedit -> delete_item("spDeleteProduct",$_POST['toke']);
            break;
        case "SEARCH" :
            $search = new GestSearch($pcopnfig);
            echo $search -> search();
            break;
        case "ADDK" :
            echo $key -> add_words();
            break;
        case "CHANGEK" :
            echo $key -> change_category();
            break;
        case "DELETEK" :
            echo $key -> delete_word();
            break;
        case "SENDK" :
            echo $key -> send_words();
            break;
        case "STATS" :
            echo $products-> stats();
            break;
    }
    }

?>