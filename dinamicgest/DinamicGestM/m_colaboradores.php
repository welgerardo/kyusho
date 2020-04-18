<?php
/**
 * script: m_colaboradores.php
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

$colaborador = new Employees();

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

if ($colaborador -> check())
{
    switch ($_POST['flag']) {

        case "OPTIONS" :
            echo $colaborador -> address_options();
            break;
        case "ADD" :
            echo $colaborador -> add_employee();
            break;
        case "UPDATE" :
            echo $colaborador -> edit_employee($_POST['toke']);
            break;
        case "INFO" :
            echo $colaborador -> contact_data($_POST['toke']);
            break;
        case "MODULE" :
            echo $colaborador -> make_contacts_modules();
            break;
        case "FORNEWS" :
            echo $colaborador -> contact_for_module($_POST['toke']);
            break;
        case "RELATIONS" :
            echo $colaborador -> contact_for_module($_POST['toke'],null,null,null,"RELAT");
            break;
        case "FILE" :
        case "FILEOP" :
            echo $colaborador -> make_emp_sheet($_POST['toke']);
            break;
        case "FOLDER" :
            echo $colaborador -> make_employees_folders();
            break;
        case "CHANGE" :
            echo $colaborador -> change_employees_folders($_POST['toke'], $_POST['gal']);
            break;
            break;
        case "SAVE" :
            echo $colaborador -> insert_employee();
            break;
        case "DELETE" :
            echo $colaborador -> delete_employee($_POST['toke']);
            break;
        case "ORDER" :
            $order = new GestOrder($colaborador -> get_emp_config());
            echo $order -> make_order("spOrderColb",NULL);
            break;
        case "SAVEORDER" :
            $order = new GestOrder($colaborador -> get_emp_config());
            echo $order -> save_new_order();
            break;
        case "PASS" :
            echo $colaborador -> make_pass();
            break;
        case "NOTES" :
            echo $colaborador -> show_notes();
            break;
        case "MESSAGES" :
            $mess = new GestMessages();
            echo $mess -> get_messages($_POST['toke'], $colaborador -> cont_config['name']);
            break;
    }
}
?>