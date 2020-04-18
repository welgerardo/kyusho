<?php
/**
 * script: m_imagens.php
 * client: EPKyusho
 *
 * @version V4.02.110615
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * 
 * Stored procedure necessárias:
 * - spImagesFolders
 * - spChangeImageFolder
 * - spDeleteImage
 * 
 */
session_start();
ini_set('display_errors', 0);
ini_set("memory_limit", "64M");
ini_set("upload_max_filesize", "16M");


require 'add_files.php';
require_once 'Core.php';

$JIMG['table'] = "foto_galeria";
$JIMG['admin']['public']['folder']['db'] = "pasta";
$JIMG['admin']['private']['toke'] = "id";
$JIMG['admin']['public']['status']['db'] = "";
$JIMG['admin']['private']['identifier']['db'] = "nome";
$JIMG['admin']['private']['identifier']['options'] = "";

$imagem = new GestImage();

if (isset($_POST['flag']))
{
    if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
    {
        echo '{"alert":"Não foi possivel realizar esta operação. CODE:IG001"}';
        exit ;
    }
}
else
{
    $_POST['flag'] = FALSE;
    echo '{"alert":"Não foi possivel realizar esta operação. CODE:IG002"}';
    exit ;
}

if ($imagem -> check())
{
    switch ($_POST['flag']) {

        case "GALLERY" :
            echo $imagem -> image_list();
            break;
        case "CHANGE" :
            $folder = new GestFolders("SIMPLE");
            echo $folder -> change_folder("spChangeImageFolder",array($_POST['toke'], $_POST['gal']));
            break;
        case "GALLERYF" :
            $folder = new GestFolders();
            echo $folder -> make_folders("spImagesFolders");
            break;
        case "FOLDER" :
            $folder = new GestFolders("SIMPLE");
            echo $folder -> make_folders("spImagesFolders");
            break;
        case "DELETE" :
            $op = new OperationsBar($JIMG);
            echo $op -> delete_item("spDeleteImage", $_POST['toke']);
            break;
        case "ADD" :
            try
            {
                echo $imagem -> upload_image();
                exit;
            }
            catch(Exception $exp)
            {
                
                switch($exp->getcode()) {
                    case 1 :
                        echo '{"error":"erro de gravação.'.$exp->getMessage().'"}';
                        break;
                    case 2 :
                        echo '{"error":"ficheiro já existe"}';
                        break;
                    case 3 :
                        echo '{"error":"ficheiro ineadequado"}';
                        break;
                    default:
                        echo '{"error":"'.$exp->getcode().'"}';
                        break;
                }
            }
            break;
    }
}
?>