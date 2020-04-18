<?php
/**
 * script: m_clientes.php
 * client: EPKyusho
 *
 * @version V4.50.100615
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

$client = new Clients();
$config = $client->get_contact_config();

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

if ($client -> check())
{
    switch ($_POST['flag']) {

        case "OPTIONS" :
            echo $client -> address_options();
            break;
        case "ADD" :
            echo $client -> add_client();
            break;
        case "UPDATE" :
            echo $client -> edit_client($_POST['toke']);
            break;
        case "INFO" :
            echo $client -> contact_data($_POST['toke']);
            break;
        case "FILE" :
        case "FILEOP" :
            echo $client -> client_sheet($_POST['toke']);
            break;
        case "FOLDER" :
            echo $client -> client_folders();
            break;
        case "MODULE" :
            echo $client -> make_contacts_modules();
            break;
        case "FORNEWS" :
            echo $client -> contact_for_module($_POST['toke']);
            break;
        case "RELATIONS" :
            echo $client -> contact_for_module($_POST['toke'],null,null,null,"RELAT");
            break;
        case "CHANGE" :
            echo $client -> change_client_folders($_POST['toke'], $_POST['gal']);
            break;
            break;
        case "SAVE" :
            echo $client -> insert_client();
            break;
        case "DELETE" :
            echo $client -> delete_client($_POST['toke']);
            break;
        case "PASS" :
            echo $client -> make_pass();
            break;
        case "NOTES" :
            echo $client -> show_notes();
            break;
        case "MESSAGES" :
            $mess = new GestMessages();
            echo $mess -> get_messages($_POST['toke'], $client -> cont_config['name']);
            break;
    }
}
?>