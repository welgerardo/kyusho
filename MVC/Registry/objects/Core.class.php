<?php
/**
 * script: Core.php
 * client:petit amour
 *
 * @version V9.62.291215
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 *
 */
//require_once 'MasterConfig.php';

class Core{

    private $exp_prefix = "CORE";

    function __construct() {
        
    }

    public function clean_space($T) {

        return str_replace(" ", "-", $T);
    }

    /**
     * Valida um inteiro positivo maior que zero.
     * Função criada essencialmente para ser chamada por callback
     *
     * @param $value - qualquer valor maior que zero para validar se é inteiro
     *
     * @return boolean
     *
     */
    static function validate_int($value) {

        return filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)));
    }

    /**
     * Acessa a uma stored procedure
     *
     * @param string $call - nome da stored procedure
     * @param array $param - parametros da stored procedure
     *
     * @return array - Em caso de sucesso devolve os resultado da stored procedure
     *
     * @throws Exception
     */
    public function make_call($call, array $param = NULL) {

        $rows = NULL;
        $ERR = FALSE;
        $q_param = NULL;
        $procedure = NULL;

        try {
            $dbcon = new PDO(_PDOM, _US, _PS);
            $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            throw new Exception($ex->getMessage() . __LINE__, 1);
        }

        try {
            $numb_param = 0;

            if (is_array($param)) {
                $numb_param = count($param);
                $q_param = str_repeat(" ?,", $numb_param);
                $q_param = trim($q_param, ",");
            }

            $procedure = "CALL " . $call . "(" . $q_param . ")";

            $stmt = $dbcon->prepare($procedure);

            for ($c = 1, $p = 0; $p < $numb_param; $c++, $p++) {
                $stmt->bindValue($c, $param[$p]);
            }

            $stmt->execute();

            $rows = $stmt->fetchAll();

            $stmt->closeCursor();
        } catch (PDOException $exp) {
            $e = $exp->getMessage();
            $ERR = TRUE;
        }

        $dbcon = NULL;

        if ($ERR)
            throw new Exception($this->exp_prefix . __LINE__, 1);

        return $rows;
    }

    /**
     * Transforma e valida os campos de id enviadas via html em inteiros positivos que correspondem ás chaves primárias da base de dados.
     *
     * @param  string|int $toke
     *
     * @return int|false - o inteiro que existir um inteiro no parametro ou falso
     *
     */
    public function id($toke) {
        if (!$toke)
            return FALSE;

        $rid = FALSE;

        $id = explode(":", $toke);

        $limit = array("options" => array("min_range" => 1));

        if (isset($id[0])) {
            $rid = filter_var($id[0], FILTER_VALIDATE_INT, $limit);
        }

        if (isset($id[1])) {
            $rid = filter_var($id[1], FILTER_VALIDATE_INT, $limit);
        }


        return $rid;
    }

    /**
     * Cria uma mensagens de erro para enviar para o cliente.
     *
     * @param string $code - identificação do erro
     * @param int $mess_type - messagem a ser enviada
     *      1 - Não foi possivel realizar esta operação.
     *      2 - Já existe um item com esse nome.
     *      3 - Item inválido.
     *
     * @return string - json
     *
     */
    public function mess_alert($code, $mess_type = 1) {

        switch ($mess_type) {
            case 1 :
                return '{"alert":"Não foi possivel realizar esta operação. - ' . $code . '"}';
            case 2 :
                return '{"alert":"Já existe um item com esse nome."}';
            case 3 :
                return '{"alert":"Item inválido."}';
            default :
                return '{"alert":"Não foi possivel realizar esta operação. - ' . $code . '"}';
        }
    }

        /**
     * Manipula uma data fornecida
     *
     * @param string $date - data
     * @param boolean $show_hour - se true a data apresenta as horas, minutos e segundos
     * @param string $U - constante que define o tipo de data retornado "DATE" = 01-01-1970 00:00:00,"TIMEST" = unix timestamp,"DATEBD" = 19701230245959
     * @param boolean $fill - se true e não for fornecida a data faz retornar a data no momento, se false a funçao não retorna nada
     * @param string $S - caracter separador da data
     *
     * @return string - data no formato definido pela constante
     *
     */
    public static function make_date($date = NULL, $show_hour = 1, $U = 'DATE', $fill = 1, $S = "-") {
        
        if (empty($date) && !$fill)
            return "0000-00-00";

        if (!empty($date)) {
            $dt = date_parse($date);
            $temp = ($dt['warning_count'] > 0) ? FALSE : mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']);
        } else {
            $temp = time();
        }

        if ($temp) {

            switch ($U) {
                case 'DATE' :
                    $dat = strftime("%d$S%m$S%Y", $temp);
                    $hour = ($show_hour) ? strftime("%H:%M:%S", $temp) : "";
                    $ts = $dat . "  " . $hour;
                    break;
                case 'TIMEST' :
                    $ts = mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']);
                    break;
                case 'DATEBD' :
                    $da = strftime("%Y%m%d", $temp);
                    $ho = ($show_hour) ? strftime("%H%M%S", $temp) : "";
                    $ts = $da . $ho;
                    break;
            }

            return $ts;
        } else {
            return "0000-00-00";
        }
    }
    /**
     * Envia uma notificação
     * @param type $json
     * @return boolean
     */
    public function send_mail($subject, $message) {


        $sub = $this->text_clean_json($subject);
        $messx = $this->text_clean_json($message);


        $headers = "Return-Path:" . _FROMAIL . "\r\n";
        $headers .= "From:" . _FROMAIL . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $ass = html_entity_decode($sub, ENT_QUOTES, "UTF-8");
        $mes = html_entity_decode($messx, ENT_QUOTES, "UTF-8");



        try {
            $result = $this->make_call("spSendNotifications");
        } catch (Exception $ex) {
            return FALSE;
        }

        if (empty($result[0]['mail'])) {
            return NULL;
        } else {

            $mails = explode(",", $result[0]['mail']);

            if (!is_array($mails))
                return FALSE;

            $m = TRUE;

            foreach ($mails as $mail) {
                $m = imap_mail($mail, $ass, $mes, $headers);
            }

            return $m;
        }
    }

    /**
     * Transforma uma array em uma string com as chaves e o valores separados por virgulas. Elimina os valores duplicados
     *
     * @param array $arr2str
     *
     * @return string|false
     *
     */
    public function all_array_to_string(array $arr2str) {

        $string0 = NULL;

        foreach ($arr2str as $key => $value) {

            $string0 .= $key . "," . $value . ",";
        }

        if (!$string0)
            return FALSE;

        $string1 = trim($string0, ",");

        $arr1 = array_unique(explode(",", $string1));

        $string2 = trim(implode(",", $arr1), ",");

        return $string2;
    }

    /**
     * Cria uma senha com 8 caracteres
     *
     * @return string - objeto json
     *
     */
    public function make_pass() {

        if (!parent::check())
            return $this->mess_alert($this->exp_prefix . __LINE__);

        $sen1 = "1234567890abcdefghijlmnopqrstuvxzABCDEFGHIJLMNOPQRSTUVXZYKyk@$&-_." . md5(_NAMESITE);
        $comp = strlen($sen1) - 1;
        $sen2 = str_split($sen1, 1);
        $senha2 = $sen2[rand(1, $comp)] . $sen2[rand(1, $comp)] . $sen2[rand(1, 9)] . $sen2[rand(1, $comp)] . $sen2[rand(1, $comp)] . $sen2[rand(1, $comp)];

        $senha1 = substr(_NAMESITE, 0, 2);
        $senha = strtoupper($senha1) . $senha2;

        return '{"senha":"' . $senha . '"}';
    }

    /**
     * Limita o comprimento de um texto o um determinado numero de caracteres dado pelo parametro $size.
     * Termina o texto com reticências
     *
     * @param string $text - texto para ser cortado
     * @param int $size - numero de caracteres desejados
     *
     * @throws Exception O primeiro argumento não é texto : code 1
     * @throws Exception O valor do comprimento é inválido : code 2
     *
     * @return string
     *
     */
    protected function cut_str($text, $size) {

        if (!is_string($text))
            throw new Exception("O primeiro argumento não é texto", 1);

        if (!filter_var($size, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))))
            throw new Exception("O valor do comprimento é inválido", 2);

        if (strlen($text) > $size) {
            $string = str_split($text, ($size - 3));
            return $string[0] . "...";
        } else {
            return $text;
        }
    }

    /**
     * Limita o comprimento de um texto o um determinado numero de caracteres dado pelo parametro $size.
     *
     * @param string $text =texto
     * @param int $size =comprimento desejado
     * @param bollean $format = se TRUE mantem a formatação do texto, se FALSE devolve o texto sem formatação
     *
     * @throws Exception Não existe texto : code 1
     * @throws Exception O valor do comprimento é inválido : code 2
     *
     * @return string
     *
     */
    public function cut_text($text, $size, $format = FALSE) {
        if (!is_string($text))
            throw new Exception("Não existe texto", 1);

        if (!filter_var($size, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))))
            throw new Exception("O valor do comprimento é inválido", 2);

        $initial_text = NULL;
        $final_text = NULL;

        if ($format) {
            $initial_text = $text;
        } else {
            $i_text = strip_tags($text);
            $initial_text = preg_replace('/\x5c/', ' ', $i_text);
        }


        $text_len = strlen($initial_text);

        if ($text_len > ($size - 1)) {
            $i = $size;
            $spacer = substr($initial_text, $size, 1);

            while ($spacer != " ") {
                $spacer = substr($initial_text, $i, 1);

                if ($spacer == " ")
                    break;

                $i--;
            }

            $final_text = substr($initial_text, 0, $i);
        }
        else {
            $final_text = $initial_text;
        }

        return $final_text;
    }

    /**
     * Limpa um texto para ser gravado na base de dados.
     * Substitui as aspas simples dentro de tags pelo código unicode
     * escapa a barra de escape
     *
     * @param string $text
     *
     * @return string|NULL
     *
     */
    public static function textCleanJson($text) {
        if (!$text)
            return $text;

        $ct = preg_replace_callback('/<[^>]*>/', create_function('$matches', 'return str_replace("\'","\u0027",$matches[0]);'), $text);
        $ct = preg_replace_callback('/<[^>]*>/', create_function('$matches', 'return str_replace("\\\"," ",$matches[0]);'), $ct);
        $ct = str_replace("'", '&#39;', $ct);
        $ct = str_replace('\\', "&#92;", $ct);

        return $ct;
    }

    /**
     * Reverte a limpeza de um texto dos carcteres que nãoo podem ser utilizados em JSON
     *
     * @param string $text
     *
     * @return string
     *
     */
    protected function text_clean_json_reverse($text) {

        $ct = preg_replace_callback('/<[^>]*>/', create_function('$matches', 'return str_replace("\'","\"",$matches[0]);'), $text);
        $ct = str_replace("&#34", '"', $ct);
        $ct = preg_replace_callback('/>[^><]*</', create_function('$matches', 'return str_replace("\\\", "",$matches[0]);'), $ct);
        $ct = html_entity_decode($ct, ENT_QUOTES, "UTF-8");

        return $ct;
    }

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
    public function mgpencrypt($text, $cipher = "blowfish", $mode = "ofb") {
        #verifica se a extensão está instalada
        if (extension_loaded("mcrypt")) {

            #abre o modulo do algoritmo escolhido
            $td = mcrypt_module_open($cipher, "", $mode, "");

            if ($td) {

                #cria vetor de inicialização do tamanho possivel pelo algoritmo
                $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

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
    public function mgpdecrypt($text, $cipher = "blowfish", $mode = "ofb") {

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

    public function verify_delete_mail($tok) {

        //$tcnx = new mysqli(_HT, _US, _PS, _DB);
        //$tcnx -> set_charset("utf8");

        $res = $this->mgpdecrypt($tok);
        echo "####" . $res;
        return FALSE;
        $js = json_decode($res, TRUE);

        if ($js) {

            $qcont = $tcnx->query("SELECT mail FROM contactos WHERE id=" . $js['id']);

            if ($qcont) {

                $rcont = $qcont->fetch_array();
                return $rcont[0];
            } else {

                header("Location:" . _RURL);
            }
        } else {
            header("Location:" . _RURL);
        }
    }

    public function searchInArray($k, $t, $modulos) {

        $cp = NULL;

        preg_match_all("/($k\d+)/", $modulos, $cp, PREG_PATTERN_ORDER);
        for ($d = 0; $d < count($cp[0]); $d++) {
            $tc = explode($k, $cp[0][$d]);

            $lin = mysql_query("SELECT notas FROM $t WHERE id='$linke'");
            $lin2 = mysql_fetch_array($lin);

            if ($lin2[0] == "") {

                $c = "UPDATE $t SET notas = $selMail WHERE id='$linke'";
                $c1 = mysql_query($c);

                if ($c1) {
                    $sended = "$selMail";
                }
            } else {
                $mails = $lin2[0] . "," . $selMail;
                $d = "UPDATE newsletter SET send_to = '$mails' WHERE id='$linke'";
                $d1 = mysql_query($d);
                if ($d1) {
                    $sended .= ",$selMail";
                }
            }

            $cxp .= ",$tc[1]";
        }
        $cxp = ltrim($cxp, ",");
        return "\"$v\":\"$cxp\"";
    }

    
    /**
     * OBSOLETA
     * @param string $price preço do porduto
     * @param string $tax - valor do imposto
     * @param string $desc - valor do desconto
     * @param string $fee
     * 
     */
    public function calc_price($price, $tax, $desc, $fee, $quantity=1) {


        $v_price = ($price + $fee) * (1 + ($tax / 100));
        $total_price = ($v_price * $quantity);
        
        $this->fprice['total_price'] = number_format($total_price, 2, ',', ' ');
        $this->fprice['preco'] = number_format($v_price, 2, ',', ' ');



        if (empty($desc) || floatval($desc) <= 0) {

            $this->fprice['comdesc'] = 0;
            
        } else {

            $descont = ($price * ($desc / 100));

            $d_price = (($price - $descont) + $fee) * (1 + ($tax / 100));
            
            $total_promotional_price = ($d_price * $qtd);
            
            $this->fprice['total_promotial_price'] = number_format($total_promotional_price, 2, ',', ' ');
            $this->fprice['comdesc'] = number_format($d_price, 2, ',', ' ');
        }

        return $this->fprice;
    }
    
    public function formatPrice($price){
        
       return number_format($price, 2, ',', ' ') . "€";
    }


    /**
     * 
     * @param string $price preço do porduto
     * @param string $tax - valor do imposto em porcentagem
     * @param string $desc - valor do desconto em porcentagem
     * @param string $fee - valor de taxas em valor absoluto
     * 
     */
    public function calcPrice($price, $tax, $desc, $fee, $quantity=1) {

        if(floatval($tax) > 0){
            
            $tax_val = (1 + ($tax / 100));
            
        } else{
            
            $tax_val = 1;
        }       
        
        $this->fprice['price'] = (($price + $fee) * $tax_val);
        
        $this->fprice['total_price'] = ($this->fprice['price'] * $quantity);
        
        
        if (floatval($desc) <= 0) {

            $this->fprice['promo_price'] = 0;
            $this->fprice['total_promo_price'] = 0;
            
        } else {

            $descont = ($price * ($desc / 100));

            $this->fprice['promo_price'] = (($price - $descont) + $fee) * ($tax_val);
            
            $this->fprice['total_promo_price'] = ($this->fprice['promo_price'] * $quantity);
        }

        return $this->fprice;
    }

}
