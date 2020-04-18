<?php
/**
 * script: KeyWords.php
 * client: EPKyusho
 *
 * @version V3.01.280115
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 *
 */

ini_set('display_errors', 1);

class KeyWords extends Core{

    public function send_words() {


        $aQueryK = "SELECT * FROM key_words ORDER BY categoria ASC";
        $rkw=Logex::$tcnx->query($aQueryK);

        $w = NULL;

        while ($categ = $rkw->fetch_array()) {

            $w .=",\"$categ[categoria]\":[$categ[palavra]]";
        }

        return "{" . ltrim($w, ",") . "}";
    }

    /*
     * adiciona uma palavra a uma lista
     */

    public function add_words() {

        $s=(isset($_POST['pasta']))? $_POST['pasta'] : $_POST['para'];
        $l=$_POST['word'];

        $ws = array_filter(array_map('trim', explode(",", $l)));

        //busca a lista de palavras

        $query =Logex::$tcnx->query("SELECT palavra FROM key_words WHERE categoria = '$s'");
        $result = $query->fetch_array();

        //se a pasta existir
        if ($result) {
            //cria uma array com a lista das palavras
            $arr = explode(",", $result['palavra']);

            $ar = array_merge($arr,$ws);
            $ar = array_filter(array_unique($ar));
            //transforma a array em uma string
            $wr = implode(",", $ar);

           Logex::$tcnx->query("UPDATE key_words SET palavra = '$wr' WHERE categoria = '$s'");
        }

        //se a pasta não existir
        else {

                //retira elementos duplicados na array
                $ar = array_filter(array_unique($ws));
                //ordena a array alfabeticamente
                sort($ar);
                 $wr = implode(",", $ar);

           Logex::$tcnx->query("INSERT INTO key_words (categoria,palavra) VALUES ('$s','$wr')");
        }

        if(isset($_POST['pasta']))
        return $this->send_words();

        if(isset($_POST['para']))
            return $this->delete_word();
    }

    /*
     * apaga uma palavra da lista
     */

    public function delete_word() {

         $p=(isset($_POST['pasta']))? $_POST['pasta'] : $_POST['de'];
         $v=$_POST['word'];

        //busca a lista de palavras

        $query =Logex::$tcnx->query("SELECT palavra FROM key_words WHERE categoria = '$p'");
        $result = $query->fetch_array();

        //se a pasta existir
        if ($result) {
            //cria uma array com a lista das palavras
            $ar = explode(",", $result['palavra']);


            //procura a chave do elemento a retirar
            $a = array_search("\"" . $v . "\"", $ar);

            //retira o elemento na array
            unset($ar[$a]);

            //ordena a array alfabeticamente
            sort($ar);

            //conta os elementos da array
            $c = count($ar);


            //se a array estiver vazia apaga a pasta
            if (!$c) {

               Logex::$tcnx->query("DELETE FROM key_words WHERE categoria = '$p'");
            } else {

                //transforma a array em uma string
                $wr = implode(",", $ar);
               Logex::$tcnx->query("UPDATE key_words SET palavra = '$wr' WHERE categoria = '$p'");
            }
        }


        return $this->send_words();
    }

    /*
     * muda a palavra de lista
     */

    public function change_category() {

        if($_POST['de']!=$_POST['para']){


      return  $this->add_words();

      }
    }

}

?>
