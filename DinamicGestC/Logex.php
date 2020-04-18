<?php
/**
 * script: Logex.php
 * client: EPKyusho
 *
 * @version V5.03.080615
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
require_once "Anti.php";

class Logex extends Anti {

    private $identidade;
    private $autorizacoes;
    private $quem;

    public function check() {
        $nick = (!empty($_SESSION['nick'])) ? $_SESSION['nick'] : NULL;
        $senha = (!empty($_SESSION['senha'])) ? $_SESSION['senha'] : NULL;

        try {
            $dbcon = new PDO(_PDOM, _US, _PS);
            $dbcon -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exp) {

            return FALSE;
        }

        $query = "call spLogex(?,?)";
        try {
            $stmt = $dbcon -> prepare($query);
            $stmt -> bindValue(1, $nick, PDO::PARAM_STR);
            $stmt -> bindValue(2, $senha, PDO::PARAM_STR);

            $re = $stmt -> execute();
            $row = $stmt -> fetchAll();
            $stmt -> closeCursor();

        } catch(PDOException $exp) {
            return FALSE;
        }

        $dbnic = $row[0]['nick'];
        $dbsen = $row[0]['senha'];
        $rows = count($row);

        $dbcon = NULL;

        if (($nick === $dbnic) && ($senha === $dbsen) && ($rows === 1)) {
            $this -> identidade = $dbnic;
            $this -> autorizacoes = $row[0]['tipo_user'];
            $this -> quem = $row[0]['id_contacto'];

            return TRUE;
        } else {
            return FALSE;
        }
    }

}
?>