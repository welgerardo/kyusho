<?php
/**
 * script: Logex.php
 * client:petit amour
 *
 * @version V5.20.241215
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
require_once "Anti.class.php";

class Logex extends Anti {

    private $identidade;
    private $autorizacoes;
    private $quem;

    /**
     * Encripta um texto
     *
     * @param string $text
     * @param string $cipher - constante php da cifra usada. MCRYPT_BLOWFISH é usada por omissão
     * @param string $mode - constante php com o mode de encriptação. MCRYPT_MODE_OFB é usado por omissão
     *
     * @return string|false
     *
     */
    protected function mgpencrypt($text, $cipher = "blowfish", $mode = "ofb") {
        #verifica se a extensão está instalada
        if (extension_loaded("mcrypt")) {

            #abre o modulo do algoritmo escolhido
            $td = mcrypt_module_open($cipher, "", $mode, "");

            if ($td) {

                #cria vetor de inicialização do tamanho possivel pelo algoritmo
                $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);

                #cria a chave de incriptação do tamanho possivel pelo algoritmo
                $key = substr(_SOMEK, 0, mcrypt_enc_get_key_size($td));

                #inicia os buffers necesssarios
                mcrypt_generic_init($td, $key, $iv);

                #encrypta os dados
                $enc = mcrypt_generic($td, $text);

                #fecha
                mcrypt_generic_deinit($td);
                mcrypt_module_close($td);

                #caracteres que são modificados na url
                $data_64 = base64_encode($iv . $enc);
                $url_data = rawurlencode($data_64);
                $html_data = htmlspecialchars($url_data);

                return $html_data;
            } else {

                return FALSE;
            }
        } else {

            return FALSE;
        }
    }

    /**
     * Desenencripta um texto
     *
     * @param string $text - texto encriptada
     * @param string $cipher - constante php da cifra usada. MCRYPT_BLOWFISH é usada por omissão
     * @param string $mode - constante php com o mode de encriptação. MCRYPT_MODE_OFB é usado por omissão
     *
     * @return mixed|false
     *
     */
    protected function mgpdecrypt($text, $cipher = "blowfish", $mode = "ofb") {

        #verifica se a extensão está instalda
        if (extension_loaded("mcrypt")) {

            #abre o module do algoritmo escolhido
            $td = mcrypt_module_open($cipher, "", $mode, "");

            #recolhe o tamanho do vetor de inicialização que é igual a maximo permitido pelo algoritmo
            $iv_size = mcrypt_enc_get_iv_size($td);

            #decodifica o texto
            $decred = htmlspecialchars_decode($text);
            $decred2 = rawurldecode($decred);
            $dtext = base64_decode($decred2);

            #recolhe string o vetor de inicilização
            $iv = substr($dtext, 0, $iv_size);

            #verifica se o temanho do iv não é invalido
            if (strlen($iv) >= $iv_size) {

                #cria a chave usada na encriptação
                $mkey = substr(_SOMEK, 0, mcrypt_enc_get_key_size($td));

                #inicia os buffers necessarios
                mcrypt_generic_init($td, $mkey, $iv);

                #decodifica o texto
                $dec = mdecrypt_generic($td, substr($dtext, $iv_size));

                #fecha
                mcrypt_generic_deinit($td);
                mcrypt_module_close($td);

                return $dec;
            } else {

                return "error";
            }
        } else {

            return "errorfsdfsd";
        }
    }

    static function sec_session_start($session_name) {

        $secure = FALSE;
        // This stops JavaScript being able to access the session id.
        $httponly = TRUE;

        // Forces sessions to only use cookies.
        if (ini_set('session.use_only_cookies', 1) === FALSE) {
            header("Location: HTTP 1.0 404 NOT FOUND");
            exit();
        }
        // Gets current cookies params.
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
        
        // Sets the session name to the one set above.
        session_name($session_name);
        session_start();            // Start the PHP session 
        session_regenerate_id(true);    // regenerated the session, delete the old one. 
    }

    public function check() {
        $nick = (!empty($_SESSION['nick'])) ? $_SESSION['nick'] : NULL;
        $senha = (!empty($_SESSION['senha'])) ? $_SESSION['senha'] : NULL;

        try {
            $dbcon = new PDO(_PDOM, _US, _PS);
            $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exp) {

            return FALSE;
        }

        $query = "call spLogex(?,?)";
        try {
            $stmt = $dbcon->prepare($query);
            $stmt->bindValue(1, $nick, PDO::PARAM_STR);
            $stmt->bindValue(2, $senha, PDO::PARAM_STR);

            $re = $stmt->execute();
            $row = $stmt->fetchAll();
            $stmt->closeCursor();
        } catch (PDOException $exp) {
            return FALSE;
        }

        $dbnic = $row[0]['nick'];
        $dbsen = $row[0]['senha'];
        $rows = count($row);

        $dbcon = NULL;

        if (($nick === $dbnic) && ($senha === $dbsen) && ($rows === 1)) {
            $this->identidade = $dbnic;
            $this->autorizacoes = $row[0]['tipo_user'];
            $this->quem = $row[0]['id_contacto'];

            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 
     * @return boolean|int
     */
    static public function checkClientLogin() {
        
        if (!isset($_SESSION['nome']))
            return 0;

        if (!isset($_SESSION['cliente']))
            return 0;

        $id = session_id();

//        if (!$id)
//            return 0;

        $cli = $_SESSION['cliente'];

        try {
            $dbcon = new PDO(_PDOM, _US, _PS);
            $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exp) {

            return 0;
        }

        $query = "call spCheckClient(?)";

        try {
            $stmt = $dbcon->prepare($query);
            $stmt->bindValue(1, $cli, PDO::PARAM_STR);

            $re = $stmt->execute();
            $row = $stmt->fetchAll();
            $stmt->closeCursor();
            
        } catch (PDOException $exp) {
            return FALSE;
        }

        if (isset($row[0]['nivel'])) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 
     * @param type $pnick
     * @param type $psenha
     * @param type $lang
     * @return boolean|string
     */
    public function logs($pnick, $psenha, $lang) {
        
        if (!isset($_POST))
            return NULL;

        session_unset();
        @session_destroy();

        if (!class_exists("Anti"))
            return FALSE;

        
        $nick = parent::verificaNome($pnick);
        $senha = parent::verificaNome($psenha);

        if (empty($_POST['toke1']) || empty($_POST['toke2']) || !$nick || !$senha) {
            return '{"login":"erro: COD:10006323"}';
        }

        try {
            $dbcon = new PDO(_PDOM, _US, _PS);
            $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exp) {

            return FALSE;
        }

        //verifica se existe o par nome/senha
        $query = "call spSearchClient(?,?)";

        try {
            $stmt = $dbcon->prepare($query);
            $stmt->bindValue(1, $senha, PDO::PARAM_STR);
            $stmt->bindValue(2, $nick, PDO::PARAM_STR);

            $re = $stmt->execute();
            $row = $stmt->fetchAll();
            $stmt->closeCursor();
        } catch (PDOException $exp) {
            return FALSE;
        }

        if (!is_array($row) && !empty($row[0])) {
            return '{"login":"erro: COD:0001"}';
        }

        if (count($row) !== 1) {
            return '{"login":"erro: COD:1001"}';
        }
        
        //o par nome/senha existe
        $check = $row[0];

        if (isset($check['cliente']) && isset($check['nivel_acesso']) && isset($check['nome'])) {
            
            //id do cliente
            if (empty($check['cliente']))
                return '{"login":"erro: COD:1003"}';

            if (empty($check['nome']))
                return '{"login":"erro: COD:1003"}';

            if ($check['nivel_acesso'] > 5 && $check['nivel_acesso'] <= 0)
                return '{"login":"erro: COD:1003"}';


            session_start();
            session_regenerate_id(true);

            $_SESSION['nome'] = $check['nome'];
            $_SESSION['cliente'] = $check['cliente'];

            $op1 = md5($check['cliente']);
            $op2 = md5($check['nivel_acesso']);
            $op3 = md5($check['nome']);

            $text = '{"op1":"' . $op1 . '","op2":"' . $op2 . '","op3":"' . $op3 . '"}';

            $cookie_value = $this->mgpencrypt($text);
            $cookie_expire = time() + 60 * 60 * 24 * 30;

            setrawcookie("opmode", $cookie_value, $cookie_expire, "/", "", "", TRUE);
            
            $user_browser = $_SERVER['HTTP_USER_AGENT'];

            $query2 = "call spClientLog(?,?)";

            $id = session_id();
            
            try {
                $stmt2 = $dbcon->prepare($query2);
                $stmt2->bindValue(1, $id, PDO::PARAM_STR);
                $stmt2->bindValue(2, $_SESSION['cliente'], PDO::PARAM_STR);

                $re = $stmt2->execute();
                $result = $stmt2->fetchAll();
                $stmt->closeCursor();
                
            } catch (PDOException $exp) {
                return FALSE;
            }

            if (isset($result[0]['status']) && $result[0]['status'] === "ok") {
                if (isset($result[0]['toke']) && $result[0]['toke'] === $check['cliente']) {
                    return '{"status":true,"code":"' . $check['nome'] . '"}';
                }
            } else {
                return '{"login":"erro: COD:1003"}';
            }
        } else {
            return '{"login":"erro: COD:1004"}';
        }
    }
    
    
     public function checkValidSession() {        

        if (!isset($_COOKIE['opmode']))
            return FALSE;

        if (!isset($_COOKIE['opmode']))
            return FALSE;

        $opcookie = $this->mgpdecrypt($_COOKIE['opmode']);

        if (!$opdec = json_decode($opcookie, TRUE))
            return FALSE;

        if (md5($_SESSION['cliente']) === $opdec['op1']) {
            return $_SESSION['cliente'];
        } else {
            return FALSE;
        }
    }

}

?>