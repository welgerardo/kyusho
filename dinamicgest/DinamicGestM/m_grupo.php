<?php
/**
 * script: m_grupo.php
 * client: EPKyusho
 *
 * @version V4.01.030615
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * 
 * Stored procedures necessárias:
 * - spGroupsFolders
 * - spChangeGroupsFolders
 * - spCloseGroupFolders
 * - spDeleteGroup
 *
 */

ini_set('display_errors', 0);
session_start();

require 'add_files.php';
require 'Grupos.php';

$gpx = new Grupos();

$contact_group = new ContactsGroup();

$group_config = $gpx -> get_config();

if ($gpx -> check())
{
    switch ($_POST['flag']) {
        case "OPENMEMBERS" :
            echo $contact_group -> get_open_group_members($_POST);
            break;
        case "GROUP" :
            echo $contact_group -> get_group_options($_POST['index']);
            break;
        case "ADDOPEN" :
            echo $gpx -> add("OPEN");
            break;
        case "ADDCLOSE" :
            echo $gpx -> add("CLOSE");
            break;
        case "UPDATE" :
            echo $gpx -> edit($_POST['toke']);
            break;
        case "FILE" :
        case "FILEOP" :
            echo $gpx -> make_file($_POST['toke']);
            break;
        case "FOLDER" :
            $folder = new GestFolders();
            echo $folder -> make_folders("spGroupsFolders");
            break;
        case "CHANGE" :
            $folder = new GestFolders();
            echo $folder -> change_folder("spChangeGroupsFolders",array($_POST['toke'], $_POST['gal']));
            break;
        case "CLOSEGROUP" :
            $folder = new GestFolders();
            echo $folder -> make_folders("spCloseGroupFolders");
            break;
        case "SAVE" :
            echo $gpx -> save($_POST);
            break;
        case "DELETE" :            
            $delete = new OperationsBar($group_config);
            echo $delete -> delete_item("spDeleteGroup", $_POST['toke']);
            break;
        case "NOTES" :
            echo $gpx -> show_notes();
            break;
    }
}
?>