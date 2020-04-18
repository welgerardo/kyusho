<?php
/**
 * script: m_contatos.php
 * client: EPKyusho
 *
 * @version V4.21.100615
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
require 'Contacts.php';

$contato = new Contacts();
$cont_config = $contato -> get_contact_config();

if ($contato -> check())
{
    switch ($_POST['flag']) {
        case "OPENMEMBERS" :
            echo $contato -> get_open_group_members($_POST);
            break;
        case "OPTIONS" :
            echo $contato -> address_options();
            break;
        case "ADD" :
            echo $contato -> add_contact($_POST['type']);
            break;
        case "UPDATE" :
            echo $contato -> edit_contact($_POST['toke']);
            break;
        case "INFO" :
            echo $contato -> contact_data($_POST['toke']);
            break;
        case "FORNEWS" :
            echo $contato -> contact_for_module($_POST['toke']);
            break;
        case "RELATIONS" :
            echo $contato -> contact_for_module($_POST['toke'],null,null,null,"RELAT");
            break;
        case "FILE" :
        case "FILEOP" :
            echo $contato -> make_file($_POST['toke']);
            break;
        case "FOLDER" :
        case "GROUP" :
            $opc = (isset($_POST['opc'])) ? $_POST['opc'] : NULL;
            $cat = (isset($_POST['cat'])) ? $_POST['cat'] : NULL;
            echo $contato -> make_contacts_folders($opc,$cat);
            break;
        case "MODULE" :
            echo $contato -> make_contacts_modules();
            break;
        case "CHANGE" :
            $opc = (isset($_POST['opc'])) ? $_POST['opc'] : NULL;
            $cat = (isset($_POST['cat'])) ? $_POST['cat'] : NULL;
            echo $contato -> change_contacts_folders($_POST['toke'], $_POST['gal'],$opc,$cat);
            break;
        case "SUBMENU" :
            echo $contato ->goups();
            break;
        case "SAVE" :
            echo $contato -> insert_contact($_POST['filemode']);
            break;
        case "DELETE" :
            echo $contato -> delete_contact($_POST['toke']);
            break;
        case "NOTES" :
            echo $contato -> show_notes();
            break;
        case "MESSAGES" :
            $mess = new GestMessages();
            $ct = $contato -> get_contact_config();
            echo $mess -> get_messages($_POST['toke'], $ct['name']);
            break;
        case "READ" :
            echo $contato -> read_message();
            break;
        case "SEARCH" :
            $search = new GestSearch($contato -> get_contact_config());
            echo $search -> search();
            break;
        case "STATS" :
            echo $contato -> stats();
            break;
    }
}
?>