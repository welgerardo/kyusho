<?php
/**
 * script: m_newsletterConfig.php
 * client: EPKyusho
 *
 * @version V4.00.281014
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

$nwsconf = new Core();

$config = $nwsconf -> json_file("JNEWSLETTERCONFIG");
$_POST['toke'] = "i:1";

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

if ($nwsconf -> check())
{
    switch ($_POST['flag']) {

        case "UPDATE" :
            $op = new OperationsBar($config);
            $op->set_mode("UPDATE");
            echo $op -> edit_item_sheet_db("spNewsletterConfigData",NULL);
            break;
        case "FILE" :
            try
            {
                $sheet = new ItemSheet();
                echo $sheet -> make_all_sheet("spNewsletterConfigData",$config);
            }
            catch(Exception $exp)
            {
                $nwsconf -> mess_alert($exp -> getMessage());
            }
            break;
        case "SAVE" :
            $op = new OperationsBar($config);
            $op->set_mode("UPDATE");
            echo $op -> save_item("spUpdateNewsletterConfig",NULL);
            break;
    }
}
?>