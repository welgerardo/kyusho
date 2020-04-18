<?php
/**
 * script: m_news.php
 * client: EPKyusho
 *
 * @version V5.01.030615
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
require 'News.php';
require_once 'Newsletter.php';
require 'KeyWords.php';

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

$news = new News();
$key = new KeyWords();

if ($news -> check())
{
    switch ($_POST['flag']) {

        case "ADD" :
            $addedit = new OperationsBar($news -> get_config());
            echo $addedit -> add_item_sheet();
            break;
        case "UPDATE" :
            echo $news -> edit_news($_POST['toke']);
            break;
        case "FILE" :
        case "FILEOP" :
            echo $news -> make_file();
            break;
        case "CHANGE" :
            $folder = new GestFolders();
            echo $folder -> change_folder("spChangeNewsFolder", array($_POST['toke'], $_POST['gal']));
            break;
        case "FOLDER" :
            $folder = new GestFolders();
            echo $folder -> make_folders("spNewsFolders");
            break;
        case "MODULE" :
            $folder = new GestFolders("MODULE");
            echo $folder -> make_folders("spNewsFolders");
            break;
        case "FORNEWS" :
            $nwsl = new Newsletter("JNEWS");
            echo $nwsl -> get_item2insert($_POST['toke']);
            break;
        case "SAVE" :
            $addedit = new OperationsBar($news -> get_config());
            echo $addedit -> save_item("spUpdateNews", "spInsertNews");
            break;
        case "NOTES" :
            echo $news -> show_notes();
            break;
        case "DELETE" :
            $addedit = new OperationsBar($news -> get_config());
            echo $addedit -> delete_item("spDeleteNews", $_POST['toke']);
            break;
        case "SEARCH" :
            $search = new GestSearch($news -> get_config());
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
    }

}
?>