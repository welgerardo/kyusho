<?php
/**
 * script: m_portfolios.php
 * client: EPKyusho
 *
 * @version V1.00.130615
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 *
 */

ini_set('display_errors', 1);
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



$products = new Products("JEVENTOS");
$pcopnfig = $products -> get_product_config();

if ($products -> check())
{
    switch ($_POST['flag']) {

        case "ORDER" :
            $order = new GestOrder($pcopnfig);
            echo $order -> make_order("spOrderPortfolios",NULL);
            break;
        case "SAVEORDER" :
            $order = new GestOrder($pcopnfig);
            echo $order -> save_new_order();
            break;
        case "ADD" :
            $addedit = new OperationsBar($pcopnfig);
            echo $addedit -> add_item_sheet();
            break;
        case "UPDATE" :
            echo $products -> edit_item("spPortfolioData",$_POST['toke']);
            break;
        case "FILE" :
        case "FILEOP" :
            echo $products -> make_sheet("spPortfolioData", $_POST['toke']);
            break;
        case "CHANGE" :
            $folder = new GestFolders();
            echo $folder -> change_folder("spChangePortfoliosFolders",array($_POST['toke'], $_POST['gal']));
            break;
        case "FOLDER" :
            $folder = new GestFolders();
            echo $folder -> make_folders("spPortfoliosFolders");
            break;
        case "MODULE" :
            $folder = new GestFolders("MODULE");
            echo $folder -> make_folders("spPortfoliosFolders");
            break;
        case "FORNEWS" :
            $nwsl = new Newsletter("JEVENTOS");
            echo $nwsl -> get_item2insert($_POST['toke']);
            break;
        case "SAVE" :
            $addedit = new OperationsBar($pcopnfig);
            echo $addedit -> save_item("spUpdatePortfolio","spInsertPortfolio");
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
            echo $addedit -> delete_item("spDeletePortfolio",$_POST['toke']);
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

<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

