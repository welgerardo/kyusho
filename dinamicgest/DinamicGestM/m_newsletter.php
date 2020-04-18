<?php
/**
 * script: m_newsletter.php
 * client: EPKyusho
 *
 * @version V4.01.080615
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * 
 * Stored procedures necessárias:
 * - spNewsletterFolders
 * - spChangeNewsletterFolders
 * - spDeleteNewsletter
 * 
 * 
 */

ini_set('display_errors', 0);
session_start();

require 'add_files.php';
require_once 'Newsletter.php';

$news = new Newsletter("JNEWSLETTER");
$news_config = $news -> get_config();

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

if ($news -> check())
{
    switch ($_POST['flag']) {
        case "ADD" :
            echo $news -> add();
            break;
        case "SEND" :
            echo $news -> send_newsletter($_POST);
            break;
        case "FILE" :
        case "FILEOP" :
            echo $news -> make_sheet($_POST['toke']);
            break;
        case "FOLDER" :
            $folder = new GestFolders();
            echo $folder -> make_folders("spNewsletterFolders");
            break;
        case "CHANGE" :
            $folder = new GestFolders();
            echo $folder -> change_folder("spChangeNewsletterFolders",array($_POST['toke'], $_POST['gal']));
            break;
        case "GROUP" :
            echo $news -> groups();
            break;
        case "SAVE" :
            echo $news -> save();
            break;
        case "CLONE" :
            echo $news -> newsletter_selected($_POST['toke']);
            break;
        case "NOTES" :
            try
            {
                $notes = new GestNotes();
                echo $notes -> show_notes($news_config['notes'], $_POST['toke']);
            } 
            catch (Exception $ex) 
            {
                echo $news->mess_alert($ex->getMessage());
            }
            break;
        case "ITENSTAT" :
            echo $news -> ind_stats($_POST['toke']);
            break;
        case "SEARCH" :
            echo $news -> search();
            break;
        case "DELETE" :
            $addedit = new OperationsBar($news_config);
            echo $addedit -> delete_item("spDeleteNewsletter",$_POST['toke']);
            break;
    }

}else
{
    echo '{"alert":"Não foi possivel realizar esta operação. CODE:003"}';
    exit ;
}
?>