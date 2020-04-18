<?php
/**
 * script: m_pages.php
 * client: EPKyusho
 *
 * @version V5.00.110615
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * 
 * Stored procedures necessárias:
 * - spOPData
 * - spUpdateOP
 * - spOPFolders
 * - spChangeOPFolders
 *
 */

ini_set('display_errors', 0);
session_start();

require 'add_files.php';
require 'Pages.php';

$hp = new Pages("JSINGLEPAGES");

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

$toke = (!empty($_POST['toke'])) ? $_POST['toke'] : $_POST['module'];

$_POST['toke'] = $toke;

if ($hp -> check())
{

    switch ($_POST['flag']) {

        case "UPDATE" :
            echo $hp -> edit("spOPData",$_POST['toke'], $_POST['module']);
            break;
        case "FILE" :
            echo $hp -> make_sheet_i("spOPData", $_POST['toke'], $_POST['module'], TRUE);
            break;
        case "SAVE" :
            echo $hp -> save("spUpdateOP","spOPData",$_POST['toke'], $_POST['module']);
            break;
        case "FOLDER" :
            $folder = new GestFolders();
            echo $folder -> make_folders("spOPFolders",array($_POST['module']));
            break;
        case "CHANGE" :
            $folder = new GestFolders();
            $id=$folder->id($_POST['toke']);
            if($id)
            {
                echo $folder -> change_folder("spChangeOPFolders",array($id,$_POST['gal'],$_POST['module']));
            }

            break;
    }
}
?>