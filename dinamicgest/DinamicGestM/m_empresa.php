<?php
/**
 * script: m_empresa.php
 * client: EPKyusho
 *
 * @version V4.11.180615
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
require 'Core.php';

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

$toke = (isset($_POST['module'])) ? $_POST['module'] : NULL;

$core = new Core();

$config = $core ->get_configuration("JCOMPANY");

if ($core -> check())
{
    switch ($_POST['flag']) {

        case "UPDATE" :
            $addedit = new OperationsBar($config);
            $addedit->set_mode("UPDATE");
            echo $addedit -> edit_item_sheet_db("spCompanyData");
            break;
        case "FILE" :
            $sheet = new ItemSheet();
            $r = $sheet -> make_all_sheet("spCompanyData",$config);
            echo $r;
            break;
        case "SAVE" :
            $addedit = new OperationsBar($config);
            $addedit->set_mode("UPDATE");
            echo $addedit -> save_item("spUpdateCompany",NULL);
            break;
    }
}
?>