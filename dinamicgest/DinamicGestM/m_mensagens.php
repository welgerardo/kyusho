<?php
/**
 * script: m_mensagens.php
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
 * 
 *
 */

ini_set('display_errors', 0);
session_start();

require 'add_files.php';
require 'Core.php';

$messx = new GestMessages();

$JMessages['table'] = "mensagens";
$JMessages['admin']['public']['folder']['db'] = "pasta";
$JMessages['admin']['private']['toke'] = "id";
$JMessages['admin']['private']['identifier']['db'] = "assunto";

if (isset($_POST['flag']))
{
    if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
    {
        echo '{"alert":"Não foi possivel realizar esta operação. CODE:MS001"}';
        exit ;
    }
}
else
{
    $_POST['flag'] = FALSE;
    echo '{"alert":"Não foi possivel realizar esta operação. CODE:MS002"}';
    exit ;
}

if ($messx -> check())
{
    switch ($_POST['flag']) {

        case "DELETE" :
            echo $messx -> del_message($_POST['toke']);
            break;
        case "READ" :
            echo $messx -> read_message();
            break;
        case "SEARCH" :
        case "FILE" :
            echo $messx -> show_messages();
            break;
        case "FOLDER" :
            $folder = new GestFolders("SIMPLE");
            echo $folder -> make_folders("spMessagesFolders");
            break;
        case "CHANGE" :
            $folder = new GestFolders("SIMPLE");
            echo $folder -> change_folder("spChangeMessagesFolders",array($_POST['toke'], $_POST['gal']));
            break;
    }
}
?>