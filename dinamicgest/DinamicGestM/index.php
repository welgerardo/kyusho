<?php
/**
 * script: loginadm.php
 * client: EPKyusho
 *
 * @version V4.10.210515
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
$code = FALSE;
$code = filter_input(INPUT_POST, 'toke3', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1000, 'max_range' => 9999)));

if (!empty($_SESSION['codigo']))
{
    $scode =$_SESSION['codigo'];


    session_unset();
    session_destroy();

    if ($code == $scode)
    {

        require 'add_files.php';
        require_once 'Anti.php';



        if (!class_exists("Anti"))
        {
            echo '{"login":"Erro: COD:3001 - Por favor, recarregue a página."}';
            exit ;
        }

        $anti = new Anti();

        $nick = $_POST['toke1'];
        $senha = $_POST['toke2'];

        $upx = FALSE;

        if (empty($nick) || empty($senha))
        {
            echo '{"login":"Erro: COD:3002 - Por favor, recarregue a página."}';
            exit ;
        }

        try {
            $dbcon = new PDO(_PDOM, _US, _PS);
            $dbcon -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $exp)
        {
            echo '{"login":"Erro: COD:2001 - Por favor, recarregue a página."}';
            exit ;
        }

        $query = "CALL spLogex(?,?)";
        try
        {
            $stmt = $dbcon -> prepare($query);
            $stmt -> bindValue(1, $nick, PDO::PARAM_STR);
            $stmt -> bindValue(2, $senha, PDO::PARAM_STR);

            $re = $stmt -> execute();
            $row = $stmt -> fetchAll();
            $stmt -> closeCursor();

        }
        catch(PDOException $exp)
        {
            $er = $exp -> getMessage();
            echo '{"login":"Erro: COD:2002 - Por favor, recarregue a página."}';
            exit ;
        }

        if (!$row)
        {
            echo '{"login":"Erro: COD:2003  - Por favor, recarregue a página."}';
            exit ;
        }

        if (count($row) !== 1)
        {
            echo '{"login":"Erro: COD:2004  - Por favor, recarregue a página."}';
            exit ;
        }

        if ($nick === $row[0]['nick'] && $senha === $row[0]['senha'] && $row[0]['id_contacto'] > -1)
        {
            session_start();
            session_regenerate_id(true);

            $_SESSION['nick'] = $nick;
            $_SESSION['senha'] = $senha;

            try {
                $query2 = "UPDATE colaboradores SET sessao=? WHERE id_contacto=?";

                $sm = $dbcon -> prepare($query2);
                $sm -> bindValue(1, session_id());
                $sm -> bindValue(2, $row[0]['id_contacto']);

                $upx = $sm -> execute();

            } catch(PDOException $exp) {
                echo '{"login":"Erro: COD:2005  - Por favor, recarregue a página."}';
                exit ;
            }

            $dbcon = NULL;

            if ($upx) {
                echo '{"level":"' . $row[0]['tipo_user'] . '"}';
                exit ;
            } else {
                echo '{"login":"Erro: COD:1003  - Por favor, recarregue a página."}';
                exit ;
            }

        }
        else
        {
            echo '{"login":"Dados inválidos  - Por favor, recarregue a página."}';
            exit ;
        }
    }
    else
    {
        $x =900;
        unset($_SESSION['codigo']);
        echo '{"login":"Dados inválidos - Por favor, recarregue a página."}';
        exit ;
    }
}
else
{
    echo '{"login":"Erro: COD:1006 - Por favor, recarregue a página."}';
    exit ;
}
?>