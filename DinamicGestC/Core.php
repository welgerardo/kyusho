<?php

/**
 * script: Core.php
 * client:mgpdinamic
 *
 * @version V9.23.18062015
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
ini_set("default_charset", "UTF-8");

ini_set("memory_limit", "64M");
ini_set("upload_max_filesize", "16M");

require 'Logex.php';

class Core extends Logex
{

    private $exp_prefix = "CORE";

    function __construct()
    {

    }
    
    public function clean_space($T){

    return str_replace(" ", "-", $T);
}

    /**
     * Define o atributo action de um formulário apartir de  $_SERVER['REQUEST_URI']
     *
     * @throws Exception - REQUEST_URI não definido : code 1
     * @throws Exception - Não foi possivel definir um valor para action : code 2
     *
     * @return string - valor do atributo action
     *
     */
    public function form_action()
    {
        if (!isset($_SERVER['REQUEST_URI']))
            throw new Exception("REQUEST_URI não definido", 1);

        $action = NULL;
        $req    = NULL;

        $req    = explode("/", $_SERVER['REQUEST_URI']);
        $action = ltrim($req[count($req) - 1], " /");

        if (!$action)
            throw new Exception("Não foi possivel definir um valor para action", 1);

        return $action;
    }
    
    /**
     * Obtem o array de configuração de um modulo. Este método foi criado para ser utilizado em modulos que fazem acesso direto ao Core.
     * 
     * @param type $a_key - chave do array que pretendo que seja devolvido.
     *
     * @return array - associativo com todo o arquivo de configuração se $a_key for falso
     * @return array - associativo com elemento da array com a chave passada em $a_key
     */
    public function get_configuration($a_key=NULL)
    {
        if (!parent::check())
            $this->mess_alert($this->exp_prefix . __LINE__, 1);
        
        try
        {
            return $this->json_file($a_key);
        }
        catch (Exception $ex)
        {
           return $this->mess_alert($this->exp_prefix . __LINE__, 1);
        }
    }
    /**
     * Lê o arquivo de configuração Master.json de cada site e converte-o num array.
     * Se for passada, como parametro, a chave de algum elemento do array principal, apenas esse elemento é devolvido.
     * No caso de não se passado nenhum parametro devolve todo array de configuração.
     *
     * @param string $a_key - chave do array que pretendo que seja devolvido.
     *
     * @throws Exception - Não foi possivel ler o arquivo : code 1
     * @throws Exception - Não possivel decodificar o objeto json para array associativo : code 2
     *
     * @return array - associativo com todo o arquivo de configuração se $a_key for falso
     * @return array - associativo com elemento da array com a chave passada em $a_key
     *
     */
    protected function json_file($a_key = FALSE)
    {
        if (!parent::check())
            throw new Exception($this->exp_prefix . __LINE__, 1);
        
        if (!$master_file = file_get_contents(_MFILE))
            throw new Exception("Impossivel configurar json", 1);

        if (!$master_array = json_decode($master_file, TRUE))
            throw new Exception("Impossivel configurar json", 2);

        if (!$a_key)
            return $master_array;

        if (isset($master_array[$a_key]))
            return $master_array[$a_key];

        trigger_error("Impossivel configuarar json. CORE" . __LINE__, E_USER_ERROR);
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
    static function validate_int($value)
    {

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
    protected function make_call($call, array $param = NULL)
    {

        $rows      = NULL;
        $ERR       = FALSE;
        $q_param   = NULL;
        $procedure = NULL;

        try
        {
            $dbcon = new PDO(_PDOM, _US, _PS);
            $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage() . __LINE__, 1);
        }

        try
        {
            $numb_param = 0;

            if (is_array($param))
            {
                $numb_param = count($param);
                $q_param    = str_repeat(" ?,", $numb_param);
                $q_param    = trim($q_param, ",");
            }

            $procedure = "CALL " . $call . "(" . $q_param . ")";

            $stmt = $dbcon->prepare($procedure);

            for ($c = 1, $p = 0; $p < $numb_param; $c++, $p++)
            {
                $stmt->bindValue($c, $param[$p]);
            }

            $stmt->execute();

            $rows = $stmt->fetchAll();

            $stmt->closeCursor();
        }
        catch (PDOException $exp)
        {
            $e   = $exp->getMessage();
            $ERR = TRUE;
        }

        $dbcon = NULL;

        if ($ERR)
            throw new Exception($this->exp_prefix . __LINE__, 1);

        return $rows;
    }

    /**
     * !obsoleta
     * @param type $query
     * @return type
     */
    public function select($query)
    {
        $pat1 = '/select(.*)(((([0-9a-z\$_]+)\.([0-9a-z\$_]+))([ ,]*))+)[ ]+from/i';
        $pat2 = '/([0-9a-z\$_]+)\.([0-9a-z\$_]+)/i';

        $replacement = '$0 as \'$0\'';

        preg_match($pat1, $query, $mat);

        $prr = preg_replace($pat2, $replacement, $mat[0]);

        $pp = preg_replace($pat1, $prr, $query);

        $q_item = Logex::$tcnx->query($pp);

        return $q_item;
    }

    /*
     * !obsoleta
     */

    public function db_cols($col)
    {
        $pat = "/([0-9a-z\$_]+)\.([0-9a-z\$_]+)/i";

        if (!preg_match_all($pat, $col, $matches, PREG_SET_ORDER))
            return FALSE;

        return $matches[0][2];
    }

    /**
     * Transforma e valida os campos de id enviadas via html em inteiros positivos que correspondem ás chaves primárias da base de dados.
     *
     * @param  string|int $toke
     *
     * @return int|false - o inteiro que existir um inteiro no parametro ou falso
     *
     */
    public function id($toke)
    {
        if (!$toke)
            return FALSE;

        $id = FALSE;

        $id = explode(":", $toke);

        $limit = array("options" => array("min_range" => 1));

        if (isset($id[1]))
        {
            $id = filter_var($id[1], FILTER_VALIDATE_INT, $limit);
        }

        if (isset($id[0]))
        {
            $id = filter_var($id[0], FILTER_VALIDATE_INT, $limit);
        }


        return $id;
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
    public function mess_alert($code, $mess_type = 1)
    {

        switch ($mess_type)
        {
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
     * Transforma uma array em uma string com as chaves e o valores separados por virgulas. Elimina os valores duplicados
     *
     * @param array $arr2str
     *
     * @return string|false
     *
     */
    public function all_array_to_string(array $arr2str)
    {

        $string0 = NULL;

        foreach ($arr2str as $key => $value)
        {

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
    public function make_pass()
    {

        if (!parent::check())
            return $this->mess_alert($this->exp_prefix . __LINE__);

        $sen1   = "1234567890abcdefghijlmnopqrstuvxzABCDEFGHIJLMNOPQRSTUVXZYKyk@$&-_." . md5(_NAMESITE);
        $comp   = strlen($sen1) - 1;
        $sen2   = str_split($sen1, 1);
        $senha2 = $sen2[rand(1, $comp)] . $sen2[rand(1, $comp)] . $sen2[rand(1, 9)] . $sen2[rand(1, $comp)] . $sen2[rand(1, $comp)] . $sen2[rand(1, $comp)];

        $senha1 = substr(_NAMESITE, 0, 2);
        $senha  = strtoupper($senha1) . $senha2;

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
    protected function cut_str($text, $size)
    {

        if (!is_string($text))
            throw new Exception("O primeiro argumento não é texto", 1);

        if (!filter_var($size, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))))
            throw new Exception("O valor do comprimento é inválido", 2);

        if (strlen($text) > $size)
        {
            $string = str_split($text, ($size - 3));
            return $string[0] . "...";
        }
        else
        {
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
    protected function cut_text($text, $size, $format = FALSE)
    {
        if (!is_string($text))
            throw new Exception("Não existe texto", 1);

        if (!filter_var($size, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))))
            throw new Exception("O valor do comprimento é inválido", 2);

        $initial_text = NULL;
        $final_text   = NULL;

        if ($format)
        {
            $initial_text = $text;
        }
        else
        {
            $i_text       = strip_tags($text);
            $initial_text = preg_replace('/\x5c/', ' ', $i_text);
        }


        $text_len = strlen($initial_text);

        if ($text_len > ($size - 1))
        {
            $i      = $size;
            $spacer = substr($initial_text, $size, 1);

            while ($spacer != " ")
            {
                $spacer = substr($initial_text, $i, 1);

                if ($spacer == " ")
                    break;

                $i--;
            }

            $final_text = substr($initial_text, 0, $i);
        }
        else
        {
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
    protected static function text_clean_json($text)
    {
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
    protected function text_clean_json_reverse($text)
    {

        $ct = preg_replace_callback('/<[^>]*>/', create_function('$matches', 'return str_replace("\'","\"",$matches[0]);'), $text);
        $ct = str_replace("&#34", '"', $ct);
        $ct = preg_replace_callback('/>[^><]*</', create_function('$matches', 'return str_replace("\\\", "",$matches[0]);'), $ct);
        $ct = html_entity_decode($ct, ENT_QUOTES, "UTF-8");

        return $ct;
    }

    /**
     * Cria um titulo ou nome unico apartir de dois campos da base de dados
     *
     * @param string $column_names - nome das colunas na base de dados separados por virgulas
     * @param array $db_result - array associativo onde as chaves são os nomes das colunas
     *
     * @throws Exception "array esperada" : code 1
     *
     * @return string|null
     *
     */
    protected function make_title($column_names, $db_result)
    {
        /* TODO
         * verificar se $column_names com regex para se correspondem ao esperado
         * trocar explode() por preg_spilt()
         */

        if (!is_array($column_names))
            throw new Exception("array esperada", 1);

        if (!is_array($db_result))
            throw new Exception("array esperada", 1);

        $title = NULL;

        $db['main']    = explode(",", $column_names['db']);
        $db['options'] = explode(",", $column_names['options']);

        foreach ($db as $db_value)
        {
            if ($title)
                break;

            if (is_array($db_value))
            {
                foreach ($db_value as $value)
                {
                    if (isset($db_result[$value]))
                        $title .= $db_result[$value] . " ";
                }
            } else
            {
                if (isset($db_result[$column_names['db']]))
                    $title = $db_result[$column_names['db']];
            }
        }

        return $title;
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
    public function mgpencrypt($text, $cipher = "blowfish", $mode = "ofb")
    {

        if (!parent::check())
            return $this->mess_alert($this->exp_prefix . __LINE__);

        #verifica se a extensão está instalada
        if (extension_loaded("mcrypt"))
        {

            #abre o modulo do algoritmo escolhido
            $td = mcrypt_module_open($cipher, "", $mode, "");

            if ($td)
            {

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
                $data_64   = base64_encode($iv . $enc);
                $url_data  = urlencode($data_64);
                $html_data = htmlspecialchars($url_data);

                return $html_data;
            }
            else
            {

                return FALSE;
            }
        }
        else
        {

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
    public function mgpdecrypt($text, $cipher = "blowfish", $mode = "ofb")
    {

        if (!parent::check())
            return $this->mess_alert($this->exp_prefix . __LINE__);

        #verifica se a extensão está instalda
        if (extension_loaded("mcrypt"))
        {

            #abre o module do algoritmo escolhido
            $td = mcrypt_module_open($cipher, "", $mode, "");

            #recolhe o tamanho do vetor de inicialização que é igual a maximo permitido pelo algoritmo
            $iv_size = mcrypt_enc_get_iv_size($td);

            #decodifica o texto
            $dtext = base64_decode($text);

            #recolhe string o vetor de inicilização
            $iv = substr($dtext, 0, $iv_size);

            #verifica se o temanho do iv não é invalido
            if (strlen($iv) >= $iv_size)
            {

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
            }
            else
            {

                return "error";
            }
        }
        else
        {

            return "errorfsdfsd";
        }
    }

    public function verify_delete_mail($tok)
    {

        //$tcnx = new mysqli(_HT, _US, _PS, _DB);
        //$tcnx -> set_charset("utf8");

        $res = $this->mgpdecrypt($tok);
        echo "####" . $res;
        return FALSE;
        $js  = json_decode($res, TRUE);

        if ($js)
        {

            $qcont = $tcnx->query("SELECT mail FROM contactos WHERE id=" . $js['id']);

            if ($qcont)
            {

                $rcont = $qcont->fetch_array();
                return $rcont[0];
            }
            else
            {

                header("Location:" . _RURL);
            }
        }
        else
        {
            header("Location:" . _RURL);
        }
    }

    /**
     * @param array $jsn objeto json
     * @param array $db_row resultado da pesquisa na base de dados
     */
    private function set_topics($jsn, $db_row)
    {

        foreach ($jsn['db'] as $topic => $topic_text)
        {

            $title_attibutes = $this->set_attr(' class="txa_topic" ', $jsn['att_inside']);
            $text_attributes = $this->set_attr(' class="diveditable_topic" ', $jsn['att_inside']);

            $title = (isset($db_row[$topic])) ? $db_row[$topic] : "";
            $text  = (isset($db_row[$topic_text])) ? $db_row[$topic_text] : "";

            $f .= '
                <textarea name="' . $topic . '" ' . $title_attibutes . ' rows="4">' . $title . '</textarea>
                <div id="' . $topic_text . '" ' . $text_attributes . ' contentEditable="true">' . $text . '</div>
            ';

            $title = NULL;
            $text  = NULL;
        }

        return $f;
    }

    /**
     * !obsoleta?
     * envia item para criação da newsletter
     *
     * @param string $N name
     * @param string $F picture
     * @param string $D description
     * @param string $C class css
     * @param int $I id
     * @param string $T table
     *
     * @return string HTML
     */
    public function for_newsletter($N, $F, $D, $C, $I, $T)
    {
        if (!parent::check())
            return $this->mess_alert($this->exp_prefix . __LINE__);

        $t = NULL;

        //cria o conteudo da noticia, junta texto, fotos e video numa string html.
        switch ($C)
        {

            case "news1" :
                $t  = "<div class='news1'>$F $D</div>";
                break;
            case "news2" :
                $t  = "<div class='news2'>$F $D</div>";
                break;
            case "news5" :
                $t  = "<div class='news5'>$F</div>$D";
                break;
            case "news3" :
                $t  = "<div class='news3'>$F</div><div class='news3_1'>$D</div>";
                break;
            case "news4" :
                $t1 = "<div class='news4'>$F</div><div class='news4_1'>$D</div>";
                break;
            case "news6" :
                $t  = "$D<div class='news5'>$F</div>";
                break;
            default :
                $t  = "<div class='news5'>$F</div>$D";
                break;
        }

        return '
		<div class="noticialt"  id="___dataid:' . $I . '" data-ob="___dataob:' . $T . '">
                    <p class="newstitle">
                        ' . $N . '
                    </p>
                        ' . $t . '
                </div>
                <div class="newsbottom">
                </div>
                <br>
				';
    }

    /**
     *
     * @param type $toke
     * @param type $config_ob
     * @param type $ret_type
     * @return type
     */
    public function for_module($toke, $config_ob, $ret_type = NULL)
    {

        if (!$this->check())
            return $this->mess_alert($this->exp_prefix . __LINE__);

        if (!$config = $this->json_file($config_ob))
            return $this->mess_alert($this->exp_prefix . __LINE__);

        if (!$toke)
            return NULL;

        if (preg_match("/i:[0-9]+/", $toke))
        {
            if (!$id = $this->id($toke))
                return $this->mess_alert($this->exp_prefix . __LINE__);
        } elseif (!preg_match("/([^0-9,])/", $toke))
        {
            $id = $toke;
        }
        else
        {
            return NULL;
        }

        $type    = $config['name'];
        $table   = $config['table'];
        $icon    = $config['admin']['private']['icon'];
        $indenti = ($config['admin']['private']['identifier']['db']) ? $config['admin']['private']['identifier']['db'] : $config['admin']['private']['identifier']['options'];
        $ctoke   = $config['admin']['private']['toke'];

        if (!$rprod = Logex::$tcnx->query("SELECT $ctoke as id,$icon as image, $indenti as name FROM $table WHERE $ctoke IN ($id)"))
            return $this->mess_alert($this->exp_prefix . __LINE__);
        $cont  = NULL;
        while ($prod  = $rprod->fetch_assoc())
        {

            try
            {
                $o_img = new GestImage();
                $img   = $o_img->send_images_json($prod["image"], "src", FALSE, 0);
            }
            catch (Exception $exp)
            {
                $img = "imagens/sem_photo.png";
            }

            $name = $prod["name"];
            $id   = $prod["id"];

            if ($ret_type == "SHEET")
            {
                $cont .= <<<EOF
         <div class="modhalfdiv"  >
             <div class="moddivimg" data-id='{$id}'>
               <img src="{$img}" data-id="{$id}" class="modimg">
               <p>{$name}</p>
             </div>
         </div>
EOF;
            }
            else
            {
                $cont .= <<<EOF
         <div class="dvB terco">
            <img class="ig15A" data-action="delthis" src="imagens/minidel.png" draggable="false">
            <div class="wterco">
                <img src="{$img}" class="modimg">
                <p>{$name}</p>
                <input type="hidden" name="mod_{$type}_id[{$id}]" value="{$id}">
            </div>
         </div>
EOF;
            }
        }
        return $cont;
    }

    /**
     * Cria uma array com os dados para exportação de um item
     *
     * @uses Logex::check()
     * @uses Core::id()
     * @uses GestImage::send_images_json();
     *
     *
     * @param string $item_id id do item
     * @param array $config objeto de configuração
     *
     * @return array para cada item do obejeto de exportação com os seguinte campos: id, link, title, text, anchor_text
     *
     */
    protected function export_item($item_id, $config)
    {
        if (!parent::check())
            throw new Exception($this->exp_prefix . __LINE__);

        $export_item = NULL;

        if (!$id = $this->id($item_id))
            throw new Exception($this->exp_prefix . __LINE__);

        //tabela onde estão guardados os dados
        $table = !empty($config['table']) ? $config['table'] : NULL;

        //coluna id
        $id_db = !empty($config['admin']['private']['toke']) ? $config['admin']['private']['toke'] : NULL;

        //coluna imagem
        $image_db = !empty($config['admin']['private']['icon']) ? $config['admin']['private']['icon'] : NULL;

        //coluna do identificador do item
        $identifier_db = !empty($config['admin']['private']['identifier']['db']) ? $config['admin']['private']['identifier']['db'] : NULL;

        //columa do estado do item
        $status_db = !empty($config['admin']['public']['status']['db']) ? $config['admin']['public']['status']['db'] : NULL;

        //objeto de configuração da exportação do item
        $export = is_array($config['export']) ? $config['export'] : NULL;

        if (!$table || !$id_db || !$image_db || !$export)
            throw new Exception($this->exp_prefix . __LINE__);

        $img_gest = new GestImage();
        $dbcon    = new PDO(_PDOM, _US, _PS);


        foreach ($export as $ex_k => $ex_v)
        {
            $paramx = NULL;

            //define as chaves da array associativa devolvida pela pesquisa na base de dados
            $columns = NULL;

            if (is_array($ex_v['columns']))
                $columns = implode(",", array_map(create_function('$matches', 'return "$matches as \'$matches\'"; '), $ex_v['columns']));

            if (!preg_match("/$id_db/", $columns))
                $columns .= ", $id_db as '$id_db'";

            if (!preg_match("/$identifier_db/", $columns))
                $columns .= ", $identifier_db as '$identifier_db'";

            if (!preg_match("/$image_db/", $columns))
                $columns .= ", $image_db as '$image_db'";

            if (!preg_match("/$status_db/", $columns))
                $columns .= ", $status_db as '$status_db'";

            $param = NULL;

            if (is_array($ex_v['param']))
            {
                foreach ($ex_v['param'] as $value)
                {
                    if (!preg_match("/$value/", $columns))
                        $columns .= ", $value as '$value'";

                    $param .= '/$item[' . $value . ']';
                }
            }


            if (!$result = $dbcon->query("SELECT $columns FROM $table WHERE $id_db=$id LIMIT 1"))
                continue;

            $item = $result->fetch();

            if (empty($item))
                continue;

            foreach ($ex_v['param'] as $value)
            {

                $paramx .= "/" . str_replace(" ", "-", $item[$value]);
            }

            $export_item[$ex_k]['img']         = NULL;
            $export_item[$ex_k]['link']        = NULL;
            $export_item[$ex_k]['title']       = NULL;
            $export_item[$ex_k]['text']        = NULL;
            $export_item[$ex_k]['anchor_text'] = NULL;

            //imagem
            if (!empty($item[$image_db]))
            {

                try
                {
                    $export_item[$ex_k]['img'] = $img_gest->send_images_json($item[$image_db], "src", $ex_k, FALSE, NULL);
                }
                catch (Exception $ex)
                {
                    $export_item[$ex_k]['img'] = NULL;
                }
            }


            //link
            //****#substitui_por_outra_coisa#**** para substuir algo que identifique a newsletter nas estatisticas de tráfego
            if ($item[$status_db] == "online")
                $export_item[$ex_k]['link'] = $ex_v['url'] . $paramx . "?****#substitui_por_outra_coisa#****";

            //texto da ancora
            $export_item[$ex_k]['anchor_text'] = $ex_v['anchor_text'];

            //titulo
            $export_item[$ex_k]['title'] = $item[$identifier_db];

            //texto
            unset($ex_v['columns'][$identifier_db]);
            foreach ($ex_v['columns'] as $col)
            {
                if ($col != $id_db || $col != $image_db)
                    $export_item[$ex_k]['text'] .= $item[$col];
            }
        }

        return $export_item;
    }

    /**
     * Cria e atualiza o sitemap do site
     *
     */
    public function update_sitemap()
    {

        $pages = NULL;
        $url   = NULL;

        $sitemap = $this->json_file("JSITEMAP");

        foreach ($sitemap['pages'] as $sitepages)
        {

            foreach ($GLOBALS['LANGPAGES'] as $lang)
            {

                if (!empty($sitepages[$lang]['page']))
                {

                    $pages .= "
                               <url>
                                 <loc>" . _RURL . $sitepages[$lang]['page'] . "</loc>
                                 <changefreq>weekly</changefreq>
                               </url>";

                    if ($sitepages['table'] && $sitepages[$lang]['fields'])
                    {

                        $rsl = Logex::$tcnx->query("SELECT " . implode(",", $sitepages[$lang]['fields']) . " FROM " . $sitepages['table'] . " WHERE estado='online'");
                        while ($tl  = $rsl->fetch_array())
                        {

                            foreach ($sitepages[$lang]['fields'] as $value)
                            {

                                $ux = ($tl[$value]) ? $tl[$value] : $value;
                                $url .= "/" . $ux;
                            }

                            $pages .= "
                               <url>
                                 <loc>" . _RURL . $sitepages[$lang]['subpage'] . $url . "</loc>
			                     <changefreq>weekly</changefreq>
                               </url>
			            ";

                            $url = "";
                        }
                    }
                }
            }
        }

        $xm      = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
        	               <urlset  xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
                                   xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\"
                                   xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">
                               <url>
                                  <loc>" . _RURL . "</loc>
                                  <changefreq>weekly</changefreq>
                               </url>
                               $pages
                               </urlset>

                ";
        $arquivo = fopen($sitemap['path'] . "Sitemap.xml", "w+b");
        fwrite($arquivo, $xm);
    }

    /**
     * Calcula o preço final de um produto
     * valor = preço do produto
     * iva = id do iva
     * retorna o preço como uma string
     */
    public function coreCalcPrice($valor, $iva, $tab_imp)
    {
        $biva2  = "SELECT * FROM $tab_imp WHERE id='$iva'";
        $bivaq2 = mysql_query($biva2);
        $bivar2 = mysql_fetch_array($bivaq2);
        $preo   = $valor * (1 + ($bivar2['valor'] / 100));
        $preo   = number_format($preo, 2, ',', ' ') . "â‚¬";

        return $preo;
    }

    /**
     * !obsoleta?
     * Substitui as aspas simples por um valor hexa em uma string
     *
     * @param string $text
     *
     * @return string
     *
     */
    public function rplc($text)
    {
        $s = str_replace("%", "&#037;", $text);
        return str_replace("'", "&#039;", $s);
    }

    public function searchInArray($k, $t, $modulos)
    {

        $cp = NULL;

        preg_match_all("/($k\d+)/", $modulos, $cp, PREG_PATTERN_ORDER);
        for ($d = 0; $d < count($cp[0]); $d++)
        {
            $tc = explode($k, $cp[0][$d]);

            $lin  = mysql_query("SELECT notas FROM $t WHERE id='$linke'");
            $lin2 = mysql_fetch_array($lin);

            if ($lin2[0] == "")
            {

                $c  = "UPDATE $t SET notas = $selMail WHERE id='$linke'";
                $c1 = mysql_query($c);

                if ($c1)
                {
                    $sended = "$selMail";
                }
            }
            else
            {
                $mails = $lin2[0] . "," . $selMail;
                $d     = "UPDATE newsletter SET send_to = '$mails' WHERE id='$linke'";
                $d1    = mysql_query($d);
                if ($d1)
                {
                    $sended .= ",$selMail";
                }
            }

            $cxp .= ",$tc[1]";
        }
        $cxp = ltrim($cxp, ",");
        return "\"$v\":\"$cxp\"";
    }

    /**
     * Envia para o cliente todas as opções de uma datalist com target
     * $T = tabela
     * $C1 = campos db
     */
    protected function optionsDis($T, $C1)
    {

        $objx = NULL;
        $lv   = NULL;

        try
        {
            $dbcon = new PDO(_PDOM, _US, _PS);
            $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $exp)
        {

            $err = $exp->getMessage();
        }

        foreach ($C1 as $obj => $arr)
        {

            foreach ($arr as $key => $value)
            {

                $objx = NULL;

                $query1 = "SELECT DISTINCT $key FROM $T WHERE $key<>''";

                foreach ($dbcon->query($query1) as $name_value)
                {
                    try
                    {
                        $query2 = "SELECT DISTINCT $value FROM $T WHERE  $key=? AND $value<>''";

                        $stmt2 = $dbcon->prepare($query2);
                        $stmt2->bindValue(1, $name_value[0]);

                        $stmt2->execute();

                        $ob_value = $stmt2->fetchAll();

                        $stmt2->closeCursor();

                        $options = NULL;

                        foreach ($ob_value as $value_value)
                        {

                            if ($value_value[0])
                                $options .= ',"' . $value_value[0] . '"';
                        }

                        $options = ltrim($options, ",");
                        $objx .= "\"$name_value[0]\":[" . $options . "],";
                    }
                    catch (PDOException $exp)
                    {
                        $err = $exp->getMessage();
                    }
                }

                $lv .= '"' . $obj . '":{' . rtrim($objx, ",") . '},';
            }
        }

        $dbcon = NULL;
        return '{"result":{' . rtrim($lv, ",") . '}}';
    }

}

/**
 * Ficha de apresentação de um item
 *
 * __DinamicGest: table,submenu,admin,content, midia,export__
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 1.0
 * @since 08/10/2014
 * @license Todos os direitos reservados
 */
class ItemSheet extends Core
{

    /**
     * @var GestImage $image - Instância a classe
     * @var GestVideo $video - Instância a clase
     * @var string $ce_code - código da classe nas mensagens de erro
     *
     */
    private $image;
    private $video;
    private static $ce_code = "IST";

    function __construct()
    {
        parent::__construct();

        $this->image = new GestImage();
        $this->video = new GestVideo();
    }

    /**
     * Cria a ficha de apresentação de um item
     * Esta função faz a pesquisa dos dados do item na base de dados
     *
     * @uses Core::make_call()
     *
     * @param string $procedure - nome stored procedure para retirar os dados do item da base de dados
     * @param array $config_data - array de configuração do item
     * @param array $procedure_params - parametros da stored procedure
     *
     * @throws Exception code 1
     *
     * @return sring - estrutura HTML com a ficha de apresentação do item ou mensagem de erro
     *
     */
    public function make_all_sheet($procedure, array $config_data, array $procedure_params = NULL)
    {

        if (!parent::check())
            throw new Exception(ItemSheet::$ce_code . __LINE__, 1);

        $item_content = NULL;

        try
        {
            $rows = parent::make_call($procedure, $procedure_params);

            if (isset($rows[0]))
                $item_content = $rows[0];
        }
        catch (Exception $exp)
        {
            throw new Exception(ItemSheet::$ce_code . __LINE__, 1);
        }

        if (!$item_content)
            throw new Exception(ItemSheet::$ce_code . __LINE__, 1);

        $adm_block = $this->make_sheet($config_data, $item_content);

        return $adm_block;
    }

    /**
     * Cria a ficha, mas o resultado da pesquisa na base de dados tem que ser fornecido
     * Se o $content for fornecido não é criado nenhum conteúdo para a ficha , e a ficha é devolvida com os dados
     * administrativos e o conteúdo fornecido em $content
     *
     * __DinamicGest: submenu,admin,content, midia,export__
     *
     * @uses GestImage::send_images_json
     *
     * @param array $config_data - objeto json de configuração no modulo
     * @param array $item_content - resultado da pesquisa na base de dados
     * @param string $content - string html com o conteudo da ficha (caso haja necessidade de manipular os dados)
     *
     * @throws Exception code 1
     *
     * @return sring - estrutura HTML com a ficha de apresentação do item ou json com mensagem de erro
     *
     */
    public function make_sheet(array $config_data, array $item_content, $content = NULL)
    {

        if (!parent::check())
            throw new Exception(ItemSheet::$ce_code . __LINE__, 1);

        $private = $config_data['admin']['private'];
        $public  = $config_data['admin']['public'];

        $db_toke   = $private['toke'];
        $db_status = $public['status']['db'];
        $db_folder = $public['folder']['db'];
        $db_icon   = $private['icon'];

        if (!isset($item_content[$db_toke]))
            throw new Exception(ItemSheet::$ce_code . __LINE__, 1);

        if (!$id = parent::id($item_content[$db_toke]))
            throw new Exception(ItemSheet::$ce_code . __LINE__, 1);

        #class css do indicador do nome e do estado do item. Por defeito está offline
        $back_color = "backred";

        #LINKS
        $links = NULL;

        if ((isset($item_content[$db_status]) && $item_content[$db_status] == "online"))
        {
            $back_color = "backgreen";

            if (is_array($config_data['export']))
                $links = $this->make_links($config_data['export'], $item_content);
        }

        #SUBMENU
        $sub_menu = NULL;

        if (is_array($config_data['submenu']))
        {
            $sub_menu = $this->make_sub_menu($config_data['submenu']);
        }

        #TITULO
        $title = $this->make_title($private['identifier'], $item_content);

        #PASTA
        $folder = (!empty($item_content[$db_folder])) ? $item_content[$db_folder] : "";

        #IMAGEM NO TOPO ESQUERDO DA FICHA
        $icon = "<div class='fichaicon'></div>";

        if (!empty($item_content[$db_icon]))
        {
            try
            {
                $i    = $this->image->send_images_json($item_content[$db_icon], "img", NULL, 0, 'class="i180"');
                $icon = "<div class='fichaicon'>$i</div>";
            }
            catch (Exception $exp)
            {
                $icon = "<div class='fichaicon'></div>";
            }
        }

        #BLOCO NO TOPO DIREITO DA FICHA
        $adm = $this->make_sheet_adm_block($config_data['admin']['public'], $item_content, $links);

        if (!$content)
        {
            if (isset($config_data['content']))
                $content = $this->make_sheet_content($config_data['content'], $item_content);

            if (isset($config_data['midia']))
                $content .= $this->make_sheet_content($config_data['midia'], $item_content);
        }

        if ($_POST['flag'] === "FILE")
        {

            return "
                    <div id='itenfile' data-id='i:$id' data-pasta='$folder'>
                        <div id='topficha'>
                        <div id='titficha'>
                            <span id='file_title' class='$back_color'>
                                [$id] $title
                            </span>
                        </div>
                        <div id='fichanav'>
                            <ul>
                                $sub_menu
                            </ul>
                        </div>
                        </div>
                        <div id='fpalco'>
                            <div class='fpalcoheader'>
                            	$icon
                            	$adm
                            </div>
                            $content
                        </div>
                        <div class='rodape'></div>
                    </div>";
        }
        else
        {

            return "<div class='fpalcoheader'>" . $icon . $adm . "</div>" . $content;
        }
    }

    /**
     * Cria os links para vizualização do item.
     * Como primeiro parametro deve ser passado um array com a seguinte configuração:
     * __array(
     *          "codigo_idioma0" => array(
     *                                      "url" => "htpp:www.exemplo.com/...",
     *                                      "param" =>array("param1","param2",...,"paramN")
     *                                     ),
     *          ...,
     *          "codigo_idiomaN"...
     *       )__
     *
     * As chaves de $params*  devem corresponder aos valores de _codigo_idioma[param]_
     *
     * __DinamicGest : export__
     *
     * @param array $link_config - objecto que define os links.
     * @param array $params_value - array onde as chaves são os valores de $link
     *
     * @return false|null|string HTML
     *
     */
    public function make_links(array $link_config, array $params_value)
    {
        $links = NULL;

        if (!is_array($link_config) || !is_array($params_value))
            return NULL;

        foreach ($GLOBALS['LANGPAGES'] as $value)
        {
            $params = NULL;

            if (is_array($link_config[$value]['param']))
            {
                foreach ($link_config[$value]['param'] as $par)
                {
                    if (isset($params_value[$par]))
                        $params .= "/" . str_replace(" ", "-", $params_value[$par]);
                }
            }

            $links .= "
                <p class='p5'>
                    <span class='sp12FFM'>
                        link:
                    </span>
                    <span style='-webkit-user-select:text'>
                        <a data-action='anchor' href='" . $link_config[$value]['url'] . $params . "' target='_blank' >
                            " . $link_config[$value]['url'] . $params . "
                        </a>
                    </span>
                </p>";
        }

        return $links;
    }

    /**
     * Cria o submenu da ficha
     *
     * __DinamicGest: submenu__
     *
     * @param array $submenu_config - array de configuração do submenu
     *
     * @return string - estrura HTML do submenu
     *
     */
    private function make_sub_menu(array $submenu_config)
    {
        $sub_menu = NULL;

        foreach ($submenu_config as $value)
        {
            if (!$sub_menu)
            {
                $sub_menu = '<li class="lisubmenusel" id="' . strtolower($value) . '">' . $value . '</li>';
            }
            else
            {
                $sub_menu .= '<li class="lisubmenu" id="' . strtolower($value) . '">' . $value . '</li>';
            }
        }

        return $sub_menu;
    }

    /**
     * Cria bloco do topo da ficha com dados administrativos
     *
     * __DinamicGest: admin[public]__
     *
     * @uses GDate::make_date
     *
     * @param array $adm_config - array de configuração dos dados administrativos publicos.
     * @param array $db_result - array com o resultado da pesquisa na base de dados.
     * @param string $others - outros dados html que sejam manipulados por outras funções como, por exemplo, os links.
     *
     * @return string - estrutura HTML com os dados administrativos
     *
     */
    private function make_sheet_adm_block(array $adm_config, array $db_result, $others)
    {

        $data_adm = NULL;

        foreach ($adm_config as $key => $obj)
        {
            $letter_color = NULL;
            $value        = NULL;

            if (!is_array($obj))
                continue;

            if (!$db = $obj['db'])
                continue;

            switch ($key)
            {
                case 'date' :
                case 'date_act' :
                    $value        = (isset($db_result[$db])) ? GDate::make_date($db_result[$db], $obj['options']['mode']['hour'], "DATE", $obj['options']['mode']['fill']) : NULL;
                    break;
                case 'status' :
                    $value        = (isset($db_result[$db])) ? $db_result[$db] : "offline";
                    $letter_color = ($value == "online") ? "green" : "red";
                    break;
                case 'folder' :
                    $value        = FALSE;
                    break;
                default :
                    if (isset($db_result[$db]))
                    {
                        $value = $db_result[$db];
                    }
                    break;
            }

            if ($value)
                $data_adm .= "
                        <p>
                            <span>
                                " . $obj['name'] . ":
                            </span>
                            <span style='color:$letter_color'>
                            " . $value . "
                            </span>
                        </p>
                        ";
        }

        return "
                <div class='admmainbox'>
                    $data_adm
                    $others
                </div>
            ";
    }

    /**
     * Cria o conteúdo da ficha de apresentação de um item
     *
     * __DinamicGest: content, midia__
     *
     * @uses GestImage::make_photo_gallery_json, GDate::make_date, GestVideo::make_video_json
     *
     * @param array $config_obj - array de configuração (objecto content e midia do objeto de configuração)
     * @param array $db_result - array com o resultado de pesquisa na base de dados
     * @param boolean $multi - se verdadeiro retorna o conteudo em todos os idiomas, de falso apenas no idioma nativo (valor por omissão)
     *
     * @return string - estrutura HTML do conteúdo
     *
     */
    public function make_sheet_content(array $config_obj, array $db_result, $multi = FALSE)
    {
        $content = NULL;

        foreach ($config_obj as $key => $value)
        {
            $boxes = NULL;

            if (!$multi)
                if (in_array($key, $GLOBALS['LANGPAGES']) && $key != $GLOBALS['LANGPAGES'][0])
                    continue;

            if (!is_array($value))
                continue;

            foreach ($value as $kf => $field)
            {
                if (!is_array($field))
                    continue;

                if (!$db = $field['db'])
                    continue;

                if (!isset($field["type"]))
                    continue;

                switch (substr($field["type"], 2))
                {
                    case 'DATE' :
                        $db_result[$db] = GDate::make_date($db_result[$db], $field['options']['mode']['hour'], "DATE", $field['options']['mode']['fill']);
                        $boxes .= $this->make_sheet_line($field, $db_result);
                        break;
                    case 'TOPICS' :
                        $topic          = new GestTopics();
                        $r_topic        = $topic->make_topics($field['options'], $db_result[$db], NULL, "sheet");
                        $boxes .= $this->make_box(array($field['name'] => $r_topic));
                        break;
                    case 'DATALIST' :
                    case "INPUT" :
                    case "SELECT" :
                        $boxes .= $this->make_sheet_line($field, $db_result);
                        break;
                    case 'IMAGE' :
                        try
                        {
                            $img = $this->image->make_photo_gallery_json($db_result[$db], NULL, NULL, TRUE, FALSE);
                        }
                        catch (exception $exp)
                        {
                            $img = NULL;
                        }

                        $p_image                 = NULL;
                        $p_image[$field['name']] = $img;
                        $boxes .= $this->make_box($p_image, "fichabox", "fileadmblock");
                        break;
                    case 'VIDEO' :
                        $p_video                 = $this->video->make_video_json($db_result[$db]);
                        if ($p_video)
                            $boxes .= $this->make_box(array($field['name'] => $p_video), "fichabox", "fileadmblock");
                        break;
                    case "NEDITDIV" :
                    case "EDITDIV" :
                    case "TEXTAREA" :
                        $boxes .= $this->make_ibox($field, $db_result);
                        break;
                    case "BOX RL" :
                        if (isset($Pvalue['related_object']))
                        {
                            $s = explode(",", $CT[$Pvalue['db']]);

                            for ($es = 0; $es < count($s); $es++)
                            {
                                $pds .= $this->forModule($s[$es], constant($Pvalue['related_object']), FALSE);
                            }
                            $prel[$Pvalue['name']] = $pds;
                            $pds                   = "";
                        }
                        break;
                    case "CHECKB" :
                        $ch                      = new ElementCheckBox();
                        if (empty($field['db']))
                            $field['db']             = "ch_" . $field['name'];
                        $db_result[$field['db']] = $ch->chk_extract_sheet($field, $db_result);
                        $boxes .= $this->make_sheet_line($field, $db_result);
                        break;
                }
            }

            $boxes = (!empty($boxes)) ? $boxes : "";

            $content .= $boxes;
        }

        return "<div class='wlang'>$content</div>";
    }

    /**
     * Cria uma linha com os dados de um campo para apresentar na ficha de apresentação de um item
     *
     * @uses Anti::validateEmail
     *
     * @param array $line_config - array com o nome do campo da base da dados[db], tipo de linha["data-type"], e nome[name]
     * @param array $db_result - resultado da pesquisa na base de dados
     *
     * @return string - estrutura HTML com uma linha
     *
     */
    protected function make_sheet_line(array $line_config, array $db_result)
    {

        $att = NULL;
        $con = NULL;

        $short = ($line_config["type"][0] == "S") ? "slf" : "";

        if (!$db = $line_config['db'])
            return FALSE;

        if (!isset($db_result[$db]))
            return FALSE;

        if (parent::validateEmail($db_result[$db]))
        {
            $con = "<a href='mailto:" . $db_result[$db] . "'>" . $db_result[$db] . "</a>";
        }

        #regex retirada de https://gist.github.com/dperini/729294
        elseif ((preg_match("%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu", $db_result[$db])))
        {
            $con = "<a href='" . $db_result[$db] . "' target='_blank'>" . $db_result[$db] . "</a>";
        }
        else
        {
            $con = $db_result[$db];
        }

        $ret = <<<EOF
                <div class='linefile {$short}'>
                    <span class='linefiletitle'>
                        {$line_config['name']}:
                    </span>
                    <p class='linefiletext'>
                    {$con}
                    </p>
                </div>
EOF;

        return $ret;
    }

    /**
     * Cria uma caixa com os dados de um campo para apresentar na ficha de apresentação de um item
     *
     * @param array $box_config - nomes e conteudos das caixas
     *
     * @return string HTML
     */
    public function make_box(array $box_config)
    {

        $box = NULL;

        foreach ($box_config as $key => $value)
        {

            $box .= '
              <div class="fileadmblock">
                <p class="boxestitle">
                    ' . $key . '
                </p>
                <div class="fichabox">
                    ' . $value . '
                </div>
            </div>

                ';
        }

        return $box;
    }

    /**
     * Cria uma caixa com os dados de um campo para apresentar na ficha de apresentação de um item
     *
     * @param array $B - nomes e contudos das caixas
     *
     * @return string HTML
     */
    public function make_ibox(array $box_config, array $db_value)
    {

        $box              = NULL;
        $value            = NULL;
        $type             = NULL;
        $database_content = NULL;
        $name             = NULL;

        if (isset($box_config['options']['data-type']))
            $type = $box_config['options']['data-type'];

        if (isset($db_value[$box_config['db']]))
            $database_content = $db_value[$box_config['db']];

        switch ($type)
        {
            case 'colors' :
                $value = $this->make_colors($database_content);
                break;
            default :
                $value = $database_content;
                break;
        }

        if (isset($box_config['name']))
            $name = $box_config['name'];

        $box = <<<EOF

              <div class="fileadmblock">
                <p class="boxestitle">
                    {$name}
                </p>
                <div class="fichabox">
                    {$value}
                </div>
            </div>

EOF;
        return $box;
    }

    /**
     *
     * @param type $colors
     * @return type
     */
    private function make_colors($colors)
    {
        if (empty($colors))
            return NULL;

        $single_color = explode(",", $colors);

        if (!array($single_color))
            return NULL;

        $result = NULL;

        foreach ($single_color as $color)
        {
            $colorx = NULL;

            if (preg_match("/^[ ]*(#[a-f0-9]{3,6})[ ]*$/i", $color, $match))
            {
                $c = strlen($match[1]);

                if ($c > 4 && $c < 7)
                {
                    $colorx = substr($match[1], 0, 4);
                }
                else
                {
                    $colorx = $match[1];
                }

                if ($colorx)
                    $result .= <<<EOF
                    <div class='sheetcolors' style='background-color:{$colorx}'></div>
EOF;
            }
        }

        return $result;
    }

    /**
     *
     * @param type $ID
     * @return boolean
     */
    public function validate_file_input($ID)
    {

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
        {

            trigger_error("Flag invalida", E_USER_WARNING);
            return FALSE;
        }

        $ide = $this->id($ID);

        if (!$ide)
        {

            trigger_error("Id invalido", E_USER_WARNING);
            return FALSE;
        }

        return $ide;
    }

}

/**
 * Ficha de apresentação de um item
 *
 * __DinamicGest: table,submenu,admin,content, midia,export__
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 1.5
 * @since 13/06/2015
 * @license Todos os direitos reservados
 */
class OperationsBar extends Core
{

    private $confi;
    private $all_config;
    private $table;
    private $images;
    private $exp_prefix = "OPB";
    private $op_mode    = NULL;

    function __construct($OB)
    {

        parent::__construct();

        //configuração que serve para depois ser manipulada
        $this->confi = $OB;

        //guarda a configuração original completa
        $this->all_config = $OB;

        $this->table = $OB['table'];

        $this->images = new GestImage();
    }

    /**
     * Cria uma ficha para adicionar um novo item na base de dados
     *
     * @param array $predefined - valores pré-definidos para iniciar uma ficha
     *
     * @return string - estrutura HTML da ficha
     *
     */
    public function add_item_sheet(array $predefined = NULL)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $id     = NULL;
        $folder = NULL;

        //para manter compatibilidade
        $this->op_mode = "ADD";

        $add = $this->make_sheet_blocks($predefined);

        return $this->make_sheet($id, $folder, $add);
    }

    /**
     *
     */
    public function set_mode($mode)
    {
        if ($mode != "ADD" && $mode != "UPDATE" && $mode != "CLONE")
            $this->op_mode = FALSE;

        $this->op_mode = $mode;
    }

    /**
     * Pesquisa item na base de dados para criar ficha de edição
     *
     * @param string $procedure - nome da stored procedure
     * @param array  $param - parametros da stored procedure
     *
     *
     * @returns string - devolve uma página html com uma formulário para alterar os dados do item
     *
     */
    public function edit_item_sheet_db($procedure, array $param = NULL)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        try
        {
            $rows = parent::make_call($procedure, $param);

            $item_content = $rows[0];
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        switch ($this->op_mode)
        {
            case 'UPDATE' :
                $ret = $this->edit_item_sheet($item_content);
                break;
            case 'CLONE' :
                $ret = $this->clone_item_sheet($item_content);
                break;
            default :
                $ret = parent::mess_alert($this->exp_prefix . __LINE__);
                break;
        }
        return $ret;
    }

    /**
     * Cria uma ficha para editar um item
     *
     * @param array $query_result - array associativa resultado da pesquisa na base de dados
     *
     * @return string estrutura html da ficha
     *
     */
    public function edit_item_sheet(array $query_result)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        @$folder = $query_result[$this->confi['admin']['public']['folder']['db']];

        $id = $query_result[$this->confi['admin']['private']['toke']];

        try
        {
            $edit = $this->make_sheet_blocks($query_result);
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage());
        }

        return $this->make_sheet($id, $folder, $edit);
    }

    /**
     * Cria uma ficha para editar um item
     *
     * @param array $query_result - array associativa resultado da pesquisa na base de dados
     *
     * @return string estrutura html da ficha
     *
     */
    public function clone_item_sheet(array $query_result)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $query_result[$this->confi['admin']['private']['toke']] = "";

        foreach ($this->confi['clone'] as $value)
        {
            $query_result[$value] = "";
        }

        @$folder = $query_result[$this->confi['admin']['public']['folder']['db']];

        $id = $query_result[$this->confi['admin']['private']['toke']];

        try
        {
            $edit = $this->make_sheet_blocks($query_result);
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        return $this->make_sheet($id, $folder, $edit);
    }

    /**
     *
     * cria ficha de adição ou edição
     *
     * @param int $I - id do item que a ficha representa
     * @param string $F - nome da pasta do item
     * @param string $C - conteúdo (todos os blocos com campos de introdução de dados)
     *
     * @return string estrutura HTML da ficha
     */
    private function make_sheet($I, $F, $C)
    {
        try
        {
            $sheet = ' <div id="mainEdition" data-id="i:' . $I . '" data-pasta="' . $F . '">
                            <form method="post" action="' . $this->form_action() . '" id="mgpmainform">
                                <div class="dataedit" >
                                    <div class="dv95pL00">
                                        <input type="hidden" id="identidade" name="toke" value="' . $I . '">
                                        <input type="hidden" name="filemode" value="' . $this->op_mode . '">
                                        <span class="sp15b">[' . $I . '] </span>
                                    </div>
                                    <div class="dataeditwrapper">
                                        ' . $C . '
                                    </div><!--fim wrapper-->
                                </div>
                                <div class="rodape"></div>
                            </form>
                        </div>
                    ';
        }
        catch (Exception $exp)
        {
            $sheet = parent::mess_alert($this->exp_prefix . __LINE__);
        }

        return $sheet;
    }

    /**
     * Define os campos de introdução de um item.
     * Esta função é importante para manter as consistência entre os dados da ficha e os
     * dados para salvar, essencialmente no que diz respeito aos nomes dos campos.
     *
     * @return array - blocos e respectivos campos da ficha do item.
     */
    private function block_fields()
    {
        if (isset($this->confi['admin']['private']))
        {
            //campos que precisem de ser definidos pelo utilizador mas não devam ser mostrados nas fichas
            foreach ($this->confi['admin']['private'] as $key => $value)
            {
                if (isset($this->confi['admin']['private'][$key]['type']))
                {
                    $this->confi['admin']['public'][$key] = $value;
                }
            }
        }

        unset($this->confi['admin']['private']);

        return array($this->confi['admin'], $this->confi['content'], $this->confi['midia']);
    }

    public function make_icon($OBK)
    {

        if (key_exists($OBK, $GLOBALS['LANGFLAGS']))
        {

            $ic = $GLOBALS['LANGFLAGS'][$OBK];

            $icon = (preg_match("#<[ ]*img(.*)[ ]*>#i", $ic)) ? '[ ' . $ic . ' ]' : '[ <img src="' . $ic . '" class="ig10M"> ]';

            return $icon;
        }

        return NULL;
    }

    /**
     * Cria os campos de introdução de dados numa ficha de item
     * agrupa-os em blocos.
     *
     * @param array $DB - resultado da pesquisa do item na base de dados
     *
     * @return string - estrutura HTML com os campos e blocos
     *
     */
    private function make_sheet_blocks($DB)
    {

        $block   = NULL;
        $b_title = NULL;

        $all = $this->block_fields();

        $oimage    = new GestImage();
        $input     = new ElementInput();
        $data_list = new ElementDatalist();

        foreach ($all as $vob)
        {

            $b_title = NULL;

            if (!is_array($vob))
                continue;

            foreach ($vob as $karr => $varr)
            {
                $div = NULL;

                if (!is_array($varr))
                    continue;

                $icon = $this->make_icon($karr);

                $data_list->make_datalist_options($varr, $this->table, $DB);

                foreach ($varr as $key => $value)
                {

                    if (empty($value['type']))
                        continue;

                    if (!is_array($value['db']))
                        $dbvalue = (isset($DB[$value['db']])) ? $DB[$value['db']] : "";

                    $name = $karr . "_" . $key;

                    $field = NULL;

                    switch (substr($value["type"], 2))
                    {
                        case "DATE" :
                            $ddate['type']    = $value['type'];
                            $ddate['options'] = $value['options']['att'];
                            $field            = $input->make_input($ddate, GDate::make_date($dbvalue, $value['options']['mode']['hour'], "DATE", $value['options']['mode']['fill']), $name);
                            break;
                        case "DATALIST" :
                            $field            = $data_list->data_list($dbvalue, $value['options'], $name, $key);
                            break;
                        case "SELECT" :
                            $selc             = new ElemetSelect();
                            $field            = $selc->make_select($name, $dbvalue, $value['options']);
                            break;
                        case "INPUT" :
                        case "TEXTAREA" :
                        case "EDITDIV" :
                        case "NEDITDIV" :
                        case "DSEO" :
                        case "TSEO" :
                        case "ISEO" :
                            $field            = $input->make_input($value, $dbvalue, $name);
                            break;
                        case "TOPICS" :
                            $topic            = new GestTopics();
                            $field            = $topic->make_topics($value['options'], $dbvalue, $name);
                            break;
                        case "RADIOB" :
                            $radiob           = new ElementRadioButton();
                            $field            = $radiob->make_radiob($value['options'], $dbvalue, $name);
                            break;
                        case "CHECKB" :
                            $checkb           = new ElementCheckBox();
                            $field            = $checkb->make_checkb($value, $DB, $name);
                            break;
                        case "VIDEO" :
                            $ovideo           = new GestVideo();
                            $field            = $ovideo->make_video_insert($dbvalue);
                            break;
                        case "IMAGE" :
                            try
                            {
                                $captions = ($value['options']['captions']) ? TRUE : FALSE;

                                $image = $oimage->make_photo_gallery_json($dbvalue, $value['options']['image_name'], $value['options']['captions'], $captions);
                                unset($value['options']['image_name']);
                                unset($value['options']['captions']);

                                $field = $input->make_input($value, $image, $name);
                            }
                            catch (Exception $exp)
                            {
                                $field = $input->make_input($value, "", $name);
                            }
                            break;
                        default :
                            $field = '';
                            break;
                    }

                    if ($field)
                    {
                        $div .= $this->wrap_sheet_field($field, $icon, $value, $key);
                        $field = "";
                    }

                    try
                    {
                        @$b_tiltle = "<div>" . $this->make_block_title($varr['name'], $icon) . "<div>" . $div . "</div></div>";
                    }
                    catch (Exception $exp)
                    {
                        throw new Exception($this->exp_prefix . __LINE__, 1);
                    }
                }//end 3
                $block .= $b_tiltle;
            }//end 2
        }
        return $block;
    }

    /**
     * Embrulha o elemento HTMl para introdução de dados e acrescento texto e ajuda sobre o campo. 
     * Se o valor da propriedade "text" do objeto de configuração estiver dedinida para "MGPTABLE" serão apresentados icons que permitem
     * criar uma tabela para formatação do campo. Deve ser usada apenas em DIVEDIT
     *
     * @param string $FIELD - elemento HTML para introdução de dados
     * @param string $ICON - elemento HTML img com o icon do campo
     * @param array $OB - array com a cofiguração do campo
     * @param string $KEY - chave da array pai da array de configuração
     *
     * @return string - estrutura HTML com um campo da ficha
     *
     */
    private function wrap_sheet_field($FIELD, $ICON, $OB, $KEY)
    {

        if (!empty($OB['name']) && $FIELD)
        {

            $size = (isset($OB['type']) && $OB['type'][0] === "S") ? " wshort" : "";
            $text = (isset($OB['text'])) ? $OB['text'] : "";
            $help = (isset($OB['help'])) ? $OB['help'] : "";
            
            if($text === "MGPTABLE")
                    $text  ="<img src='imagens/add_col.png' data-action='addcol'> <img src='imagens/add_row.png' data-action='addrow'> <img src='imagens/del_col.png' data-action='delcol'> <img src='imagens/del_row.png' data-action='delrow'> <img src='imagens/del_table.png' data-action='deltable'>";

            if ($KEY)
            {

                $id     = "id='w_$KEY'";
                $id_dsp = "id='dsp_$KEY'";
            }
            else
            {

                $id     = "";
                $id_dsp = "";

                trigger_error("Key na definido em warp_add_field para o objeto $OB[name]", E_USER_WARNING);
            }

            return "
                <div class='wrapaddfield $size' $id draggable='false'>

                    <div class='wrapaddfielddiv'>
                        <img src='imagens/help_icon.png' data-tip='$help' class='helpicon'>
                         $OB[name] $ICON
                    </div>
                    <span class='addfieldtext'>$text</span>
                    <span class='wfielddisplay' $id_dsp ></span>
                        $FIELD
                </div>";
        }
        else
        {
            trigger_error('Objeto com "name" indefinido', E_USER_WARNING);
        }
    }

    /**
     *
     * Cria a barra com o nome de um bloco
     *
     * @param string $MODE - define operação a realizar (ADD = adição | UPDATE = edição)
     * @param string $NAME - nome do bloco
     * @param string $ICON - elemento HTML img com o icon do bloco
     *
     * @return string - estrutura HTML com a barra com o nome de um bloco
     *
     */
    private function make_block_title($NAME, $ICON)
    {
        if (!$this->op_mode)
            throw new Exception($this->exp_prefix . __LINE__, 1);

        switch ($this->op_mode)
        {

            case "ADD" :
                $mcss = "sectitG";
                break;
            case "UPDATE" :
                $mcss = "sectitY";
                break;
            case "CLONE" :
                $mcss = "sectitB";
                break;
        }

        $bar = <<<EOF
              <p class="{$mcss}">
              	<span class="sp15b">{$ICON}  {$NAME}</span>
              	<img src="imagens/folderon.png" class="igop" data-type="hideshow">
              </p>
EOF;

        return $bar;
    }

    /**
     * Grava os dados de um item na base de dados, atualiza o sitemap e publica o item nas redes sociais selecionadas (se existir essa possibilidade);
     * Se não existir uma das stored procedures o parametro deve ser NULL;
     *
     * @param string $update_procedure - nome da stored procedure para atualizar o item na base de dados.
     * @param string $insert_procedure - nome da stored procedure para inserir um item na base de dados
     *
     * @return string - objeto json com os dados do item gravado (em caso de sucesso) ou com uma mensagem de erro (caso de falha)
     *
     */
    public function save_item($update_procedure, $insert_procedure)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!isset($_POST))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $save_mode = ($_POST['filemode'] == "UPDATE" || $_POST['filemode'] == "ADD" || $_POST['filemode'] == "CLONE") ? $_POST['filemode'] : FALSE;

        if (!$save_mode)
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $identy = (isset($_POST['toke'])) ? parent::id($_POST['toke']) : FALSE;

        $sts = (isset($_POST['public_status']) && $_POST['public_status'] === "online") ? TRUE : FALSE;

        $_POST['public_folder'] = (isset($_POST['public_folder'])) ? parent::validate_name($_POST['public_folder']) : NULL;

        $_POST['public_date_act'] = "";
        $_POST['public_date']     = "";

        if ($save_mode == "UPDATE" && $identy)
            unset($_POST['public_date']);

        $query = NULL;

        $query = $this->make_query();

        if (isset($query["errormgp"]))
            return $query["errormgp"];

        if (!$query[$this->table])
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (($save_mode == "ADD" || $save_mode == "CLONE") && !$identy)
        {
            if (!$insert_procedure)
                return parent::mess_alert($this->exp_prefix . __LINE__);

            $q         = $query[$this->table];
            $procedure = $insert_procedure . "(?)";
        }

        if ($save_mode == "UPDATE" && $identy)
        {
            if (!$update_procedure)
                return parent::mess_alert($this->exp_prefix . __LINE__);

            $q         = $query[$this->table];
            $procedure = $update_procedure . "(?, ?)";
        }

        $rows = NULL;

        try
        {
            $dbcon = new PDO(_PDOM, _US, _PS);
            $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $dbcon->prepare("CALL " . $procedure);

            $s2 = $stmt->bindParam(1, $q, PDO::PARAM_STR);

            if ($identy)
                $s1 = $stmt->bindValue(2, $identy, PDO::PARAM_INT);

            $rt = $stmt->execute();

            $rows = $stmt->fetchAll();

            $stmt->closeCursor();
        }
        catch (PDOException $exp)
        {

            $err = $exp->getMessage();
        }

        $dbcon = NULL;

        if (!$ret = json_decode($rows[0]['ret'], TRUE))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (isset($ret['mgp_error']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (empty($ret['id']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $id = $ret['id'];

        if (isset($_POST['public_publish']) && $sts == "online" && $this->all_config && $id)
        {
            $gsocial = new GestSocial();
            $gsocial->social_publish($_POST['public_publish'], $this->all_config, $id);
        }

        #ATUALIZA O SITEMAP
        //$this -> update_sitemap();

        return '{"result":["' . $ret['pasta'] . '","' . $id . '"]}';
    }

    /**
     *
     */
    public function db_error_message($message)
    {
        if (preg_match("/Duplicate entry '(.+)' for key '(.+)'/i", $message, $match))
        {
            $all = $this->block_fields();

            foreach ($all as $data_blocks)
            {

                if (!is_array($data_blocks))
                    continue;

                foreach ($data_blocks as $block_name => $block_itens)
                {

                    if (!is_array($block_itens))
                        continue;

                    foreach ($block_itens as $item_key => $field)
                    {

                        if (!is_array($field))
                            continue;

                        preg_match("/([0-9,a-z,A-Z\$_]+)\.([0-9,a-z,A-Z\$_]+)/i", $field['db'], $matches);

                        if ($match[2] === $matches[2])
                            return '{"errormess":"' . $field['name'] . ' \'' . $match[1] . '\' já existe."}';
                    }
                }
            }
        }
    }

    /**
     * !Obsoleto
     *
     * Cria a query para adição de um item na base de dados
     *
     * @return string
     *
     */
    public function save_add()
    {

        $_POST['public_date_act'] = "";
        $_POST['public_date']     = "";

        $order_index    = NULL;
        $order_index_db = $this->confi['admin']['private']['order'];

        if (!empty($order_index_db))
        {
            $ni          = Logex::$tcnx->query("SELECT MAX($order_index_db) as IO FROM " . $this->table);
            $n           = $ni->fetch_array();
            $order_index = ", $order_index_db=" . ($n['IO'] + 1);
        }

        return "INSERT INTO " . $this->table . " SET " . rtrim($this->make_query(), ",") . $order_index;
    }

    /**
     *
     * !Obsoleto
     *
     * cria uma query para atualizar um dado na base de dados
     *
     * @param int $iD - id do item a atualizar
     *
     * @return string
     *
     */
    public function save_edit($ID)
    {
        unset($_POST['public_date']);
        $_POST['public_date_act'] = "";

        $toke  = $this->confi['admin']['private']['toke'];
        echo "<pre>";
        var_dump($_POST);
        print_r($this->make_query());
        echo "</pre>";
        return FALSE;
        $query = $this->make_query();

        if (!isset($query[$this->table]))
            return FALSE;

        return "UPDATE " . $this->table . " SET " . rtrim($query[$this->table], ",") . " WHERE " . $toke . "=$ID";
    }

    /**
     * Cria sequência de campo = valor para a query na base de dados
     *
     * @return string
     *
     */
    public function make_query()
    {
        unset($_POST['module']);
        unset($_POST['flag']);
        unset($_POST['filemode']);

        $values = $_POST;

        $all = $this->block_fields();

        $query    = array();
        $required = FALSE;

        foreach ($all as $data_blocks)
        {

            if (!is_array($data_blocks))
                continue;

            foreach ($data_blocks as $block_name => $block_itens)
            {

                if (!is_array($block_itens))
                    continue;

                foreach ($block_itens as $item_key => $field)
                {

                    if (!is_array($field))
                        continue;

                    $namex = $block_name . "_" . $item_key;

                    $item_type = substr($field["type"], 2);

                    /* ATENÇÂO não deixa passar campos sem nome ou nulos no arquivo json. ver checkbox */
                    if (!preg_match_all("/([0-9,a-z,A-Z\$_]+)\.([0-9,a-z,A-Z\$_]+)/i", $field['db'], $matches, PREG_SET_ORDER))
                    {
                        continue;
                    }
                    else
                    {
                        if (!isset($query[$matches[0][1]]))
                        {
                            @$query[$matches[0][1]] = "";
                        }
                    }

                    if ((isset($values[$namex]) && $values[$namex] === "") && (isset($field['default']) && $field['default'] !== ""))
                        $field['db'] = $field['default'];

                    $val = NULL;

                    switch ($item_type)
                    {
                        case
                        'DATE' :
                            if (!empty($field['db']) && !is_array($field['db']) && isset($values[$namex]))
                            {
                                $val = GDate::make_date($values[$namex], $field['options']['mode']['hour'], "DATEBD", $field['options']['mode']['fill']);
                                $query[$matches[0][1]] .= $matches[0][0] . "='" . $val . "',";
                            }
                            break;
                        case 'IMAGE' :
                            if (!empty($field['db']))
                            {
                                $val = $this->images->make_json_img_capt($field['options'], $values);

                                $query[$matches[0][1]] .= $matches[0][0] . "='" . $val . "',";
                            }
                            break;
                        case 'VIDEO' :
                            if (!empty($field['db']))
                            {
                                $video = new GestVideo();
                                $val   = $video->get_video();

                                $query[$matches[0][1]] .= $matches[0][0] . "='" . $val . "',";
                            }
                            break;
                        case 'CHECKB' :
                            $checkb = new ElementCheckBox();
                            try
                            {
                                $ckval = $checkb->chk_extract_values($values, $field, $namex);

                                $query[$matches[0][1]] .= $ckval;
                            }
                            catch (Exception $exp)
                            {
                                $required .= $exp->getMessage() . ",";
                            }
                            break;
                        case 'TOPICS' :
                            $topic     = new GestTopics();
                            $topic_val = $topic->save_topics($namex);
                            $val       = $topic_val;
                            break;
                        default :
                            if (!empty($field['db']) && !is_array($field['db']) && isset($values[$namex]))
                            {
                                if (!empty($field['required']) && $values[$namex] === "")
                                {
                                    $required .= '"' . $namex . '":1,';
                                }
                                else
                                {
                                    try
                                    {
                                        if (isset($field['options']['data-length']))
                                        {
                                            try
                                            {
                                                $val = parent::cut_text($values[$namex], $field['options']['data-length'], 1);
                                            }
                                            catch (Exception $ex)
                                            {
                                                $val = "";
                                            }
                                        }
                                        else
                                        {
                                            $val = $values[$namex];
                                        }


                                        $query[$matches[0][1]] .= $matches[0][0] . "='" . parent::text_clean_json($val) . "',";
                                    }
                                    catch (Exception $exp)
                                    {
                                        $required .= $exp->getMessage() . ",";
                                    }
                                }
                            }
                            break;
                    }
                }
            }
        }

        if ($required)
        {
            $query['errormgp'] = '{"error":{' . trim($required, ",") . "}}";
        }
        else
        {
            foreach ($query as $key => $value)
            {
                $query[$key] = trim($value, ",");
            }
        }

        return $query;
    }

    /**
     * Apaga um item na base de dados
     *
     * @uses Core::id, Logex::check(), Logex::$tcnx
     *
     * @param string $procedure - nome da procedure a utilizar. Pode ser apenas o nome , ou o nome com todos os parametros.
     * @param string $item_id - id do item a apagar
     *
     * @return boolean | json
     *
     */
    public function delete_item($procedure, $item_id = NULL)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = parent::id($item_id))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$procedure)
            return parent::mess_alert($this->exp_prefix . __LINE__);
        try
        {
            $result       = parent::make_call($procedure, array($id));
            $item_content = $result[0];
        }
        catch (Exception $ex)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        if (!$ret = json_decode($item_content['ret'], TRUE))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (isset($ret['mgp_error']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = $ret['id'])
            return parent::mess_alert($this->exp_prefix . __LINE__);

        return '{"result":["' . $ret['pasta'] . '","' . $id . '"]}';
    }

}

class ElementInput extends Core
{

    /**
     * Cria campos de introdução de dados com elmentos html input type text, textarea, div editable
     *
     * @param array $OB - array com a configuração do campo
     * @param string $DB - valor do campo na base de dados
     * @param string $NM - nome do elemento html;
     *
     * @return string - elemento html
     */
    public function make_input($OB, $DB, $NM)
    {

        $inpt = NULL;

        if (!isset($OB['type']))
            return $inpt;

        $attributes = NULL;
        $type       = NULL;

        $contenteditable = ($OB['type'] == "S_EDITDIV" || $OB['type'] == "L_EDITDIV") ? "contenteditable=true" : "";

        if (!empty($OB['options']) && is_array($OB['options']))
        {

            unset($OB['options']['value']);
            unset($OB['options']['class']);
            unset($OB['options']['id']);
            unset($OB['options']['name']);

            $type = "type='text'";

            if (isset($OB['options']['type']))
            {
                switch ($OB['options']['type'])
                {
                    case 'url' :
                        $type = "type='url'";
                        break;
                    case 'email' :
                        $type = "type='email'";
                        break;
                    default :
                        $type = "type='text'";
                        break;
                }
            }

            unset($OB['options']['type']);

            foreach ($OB['options'] as $key => $value)
            {

                $attributes .= $key . "='" . $value . "' ";
            }
        }

        $default = (empty($OB['default'])) ? NULL : $OB['default'];

        $field_value = ($DB) ? $DB : $default;

        $req = (!empty($OB['required'])) ? "required" : NULL;

        switch ($OB['type'])
        {
            case "S_INPUT" :
            case "L_INPUT" :
            case "S_DATE" :
            case "L_DATE" :
                $inpt = "<input class='editfield' $type value='$field_value' name='$NM' id='$NM'  $attributes $req />";
                break;
            case "S_ISEO" :
            case "L_ISEO" :
                $inpt = "<input class='editfield seo' $type value='$field_value' name='$NM' id='$NM'  $attributes $req />";
                break;
            case "S_EDITDIV" :
            case "L_EDITDIV" :
            case "S_NEDITDIV" :
            case "L_NEDITDIV" :
            case "S_IMAGE" :
            case "L_IMAGE" :
                $inpt = "<div  $contenteditable class='editbox' id='$NM' $attributes $req >$field_value</div>";
                break;
            case "S_DSEO" :
            case "L_DSEO" :
                $inpt = "<div  contenteditable=true class='editbox seo' id='$NM' $attributes $req >$field_value</div>";
                break;
            case "S_TEXTAREA" :
            case "L_TEXTAREA" :
                $inpt = "<textarea class='editbox' id='$NM' name='$NM' $attributes $req >$field_value</textarea>";
                break;
            case "S_TSEO" :
            case "L_TSEO" :
                $inpt = "<textarea class='editbox seo' id='$NM' name='$NM' $attributes $req >$field_value</textarea>";
                break;
        }

        return $inpt;
    }

}

class ElemetSelect extends Core
{

    /**
     * Cria uma tag HTML select
     * o objeto json de configuração tem o seguinte esquema
     *
     *      {"dynamic":{ "table":"", "condition":"", "values":{"texto":"valor" - o valor e o valor do elemento e pode ser}},
     *       "static"{"values":{"texto":"valor" - o valor e o valor do elemento e pode ser}},
     *        "target" : "um valor para o atributo data-target"
     *
     *      o objeto dynamic é usado para extrair as opções de uma tabela da base de dados
     *      o objeto static é usado para dados pre-definidos
     *
     * Os podem ser utilizdos em simultaneo
     *
     * @uses ElementSelect::make_select_options()
     *
     *
     * @param string $name - nome da tag select
     * @param string $value - valor predefinido da tag
     * @param array $config - array com a configuração das opções
     *
     * @return string - tag HTML select
     *
     */
    public function make_select($name, $value, array $config = NULL)
    {

        $more_options = NULL;
        $target       = NULL;
        $def          = NULL;

        $options = $this->make_select_options($config);

        if (!empty($config['target']))
            $target = 'data-target="' . $config['target'] . '"';

        $default_options = (!empty($config['options']['default'])) ? $config['options']['default'] : NULL;

        $def = ($value) ? $value : $default_options;

        if ($config && is_array($options))
        {
            foreach ($options as $k_op => $v_op)
            {
                $selec = ($k_op == $def) ? "selected='selected'" : "";

                $more_options .= "<option value='$k_op' $selec>$v_op</option>";
            }
        }

        $first_option = ($default_options != NULL) ? "<option value='' >---------------------</option>" : NULL;

        return '<select class="iselectform"  name="' . $name . '" id="' . $name . '" ' . $target . '>' . $first_option . $more_options . '</select>';
    }

    /**
     * Cria as opções para a tag select
     *
     * @uses Core::all_array_to_string(), Logex::$tcnx
     *
     * @param array $conf_op - array com a configuração das opções
     *
     * @return array - opções com a chave a representar o valor da opção select e  o valor o a representar o texto
     */
    private function make_select_options(array $conf_op)
    {

        $dy_opt = array();

        if (!empty($conf_op['dynamic']))
        {
            $dynamic = $conf_op['dynamic'];

            if (!empty($dynamic['table']) && !empty($dynamic['values']))
            {
                $fields = parent::all_array_to_string($dynamic['values']);

                $f = explode(",", $fields);

                if (is_array($f))
                {
                    $x = array_map(create_function('$matches', 'return "$matches as \'$matches\'"; '), $f);

                    $fieldsx = implode(",", $x);
                }

                $dbcon = new PDO(_PDOM, _US, _PS);

                $query = $dbcon->query("SELECT DISTINCT $fieldsx FROM $dynamic[table]  $dynamic[condition] ORDER BY $fields ASC");

                $f_result = $query->fetchAll();

                if (is_array($f_result))
                {
                    foreach ($f_result as $result)
                    {
                        foreach ($dynamic['values'] as $key => $val)
                        {
                            $op_value = (!empty($result[$val])) ? $result[$val] : "";
                            $op_text  = (!empty($result[$key])) ? $result[$key] : "";

                            @$dy_opt[$op_value] = $op_text;
                        }
                    }
                }
            }
        }

        if (isset($conf_op['static']) && !empty($conf_op['static']['values']))
        {
            foreach ($conf_op['static']['values'] as $s_key => $s_val)
            {
                $dy_opt[$s_val] = $s_key;
            }
        }

        natcasesort($dy_opt);

        return $dy_opt;
    }

}

/**
 * Manipula imagems. Cria objeto json para guardar na tabela de dados, decodifica para inserir em página html, faz upload de imagem para o servidor e redimensiona.
 * Cria um objeto este objeto json para guardar as imagens em uma coluna de uma tabela na base de dados:
 * photos : { photo : [ { "photo" : " " , "idioma1" : " " , ... , "idiomaN" : " " }, ... ] }
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 1.0
 * @since 08/10/2014
 * @license Todos os direitos reservados
 *
 */
class GestVideo extends Core
{

    /**
     * @var string $ce_code - código da classe nas mensagens de erro
     *
     */
    private static $ce_code = "VID";

    /**
     * cria objeto json para guardar dados de video na base de dados
     * @return json object
     */
    public function get_video()
    {

        $from = NULL;
        $vid  = NULL;
        $pos  = NULL;

        if (!empty($_POST['embvideo']) || !empty($_POST['filevideo']))
        {

            //verifica que tipo de video foi enviado, se embeded, se do arquivo
            if ($_POST['embvideo'])
            {

                $from = "embeded";
                $vid  = $_POST['embvideo'];
            }
            if ($_POST['filevideo'])
            {

                $from = "fromfile";
                $vid  = $_POST['filevideo'];
            }

            $pos = ($_POST['videopos'] == "baixo") ? "baixo" : "cima";

            return '{"video":"' . self::text_clean_json($vid) . '","from":"embeded","pos":"' . $pos . '"}';
        }
        else
        {
            return "";
        }
    }

    /**
     * Cria na ficha de edição um espaço para inserir videos
     *
     * @uses ElemetSelect::make_select()
     *
     * @param string $V - objeto de configuração dos campos de video
     *
     * @return string - HTML com os campos de inserção de dados.
     *
     */
    public function make_video_insert($V, $select_active = FALSE)
    {
        $video = json_decode($V, TRUE);

        $local_video_name = NULL;
        $emb_video        = NULL;
        $select           = NULL;

        $cima  = ($video['pos'] === "cima") ? "checked='checked'" : $baixo = "checked='checked'";

        //verifica o tipo de video inserido
        if ($video["from"] == "fromfile")
            $local_video_name = $video["video"];

        if ($video["from"] == "embeded")
            $emb_video = $video["video"];

        if ($select_active)
        {
            $select_video = array();

            $select_video['dynamic']['table']     = "video_galeria";
            $select_video['dynamic']['values']    = array("nome_video" => "nome_video");
            $select_video['dynamic']['condition'] = NULL;
            $select_video['static']               = NULL;

            $tag_select = new ElemetSelect;

            $select = "<div class='video_division'>
                          <p class='video_title'>
                               Arquivo
                          </p>
                          " . $tag_select->make_select('filevideo', $local_video_name, $select_video) . "
                       </div>";
        }

        $pos = new ElementRadioButton();

        return "
            <div id='video_insert'>
                <div class='video_division'>
                    <p class='video_title'>Posição</p>
                    " . $pos->make_radiob(array("cima" => "cima", "baixo" => "baixo"), $video['pos'], "videopos") . "
                </div>
                <div class='video_division'>
                    <p class='video_title'>Incorporar</p>
                    <textarea name='embvideo' class='editbox'>$emb_video</textarea>
                </div>
                $select
            </div>
            <div class='rodape' ></div>
                ";
    }

    /**
     * cria a reprodução de um video para inserir numa pagina html
     * @param string $V = video
     * @param string $M = mode de video
     * @param string $C = class css
     * @param int $W = width
     * @param int $H = height
     */
    public function make_video($V, $M, $C = "newsvideo", $W = 600, $H = 409)
    {
        if ($V)
        {
            if ($M == "embeded")
            {

                return "<div class='$C'>$V</div>";
            }
            if ($M == "fromfile")
            {

                $pt = urlencode(_VIDEOURL);

                return '
                        <div class="' . $C . '">
                            <object width="' . $W . '" height="' . $H . '">
                                <param name="wmode" value="transparent"></param>
                                <param name="movie" value="http://fpdownload.adobe.com/strobe/FlashMediaPlayback.swf"></param>
                                <param name="flashvars" value="src=' . $pt . $V . '"></param>
                                <param name="allowFullScreen" value="true"></param>
                                <param name="allowscriptaccess" value="always"></param>
                                <embed src="http://fpdownload.adobe.com/strobe/FlashMediaPlayback.swf" type="application/x-shockwave-flash" wmode="transparent" allowscriptaccess="always" allowfullscreen="true" width="' . $W . '" height="' . $H . '" flashvars="src=' . $pt . $V . '"></embed>
                            </object>
                        </div>';
            }
        }
    }

    /*
     * cria a reprodução de um video para inserir numa pagina html
     * $V = resultado da pesquisa na base de dados
     * $C = class css
     * $W = width
     * $H = height
     */

    public function make_video_json($V, $C = "newsvideo", $W = 600, $H = 409)
    {

        $j_video = json_decode($V, TRUE);
        if (!empty($j_video))
        {
            if ($j_video['from'] == "embeded")
            {

                return "<div class='$C'>" . $j_video['video'] . "</div>";
            }
            if ($j_video['from'] == "fromfile")
            {

                $pt = urlencode(_VIDEOURL);

                return '
                        <div class="' . $C . '">
                            <object width="' . $W . '" height="' . $H . '">
                                <param name="wmode" value="transparent"></param>
                                <param name="movie" value="http://fpdownload.adobe.com/strobe/FlashMediaPlayback.swf"></param>
                                <param name="flashvars" value="src=' . $pt . $j_video['video'] . '"></param>
                                <param name="allowFullScreen" value="true"></param>
                                <param name="allowscriptaccess" value="always"></param>
                                <embed src="http://fpdownload.adobe.com/strobe/FlashMediaPlayback.swf" type="application/x-shockwave-flash" wmode="transparent" allowscriptaccess="always" allowfullscreen="true" width="' . $W . '" height="' . $H . '" flashvars="src=' . $pt . $j_video['video'] . '"></embed>
                            </object>
                        </div>';
            }
        }
        else
        {
            return FALSE;
        }
    }

}

/**
 * Manipula imagems. Cria objeto json para guardar na tabela de dados, decodifica para inserir em página html, faz upload de imagem para o servidor e redimensiona.
 * Cria um objeto este objeto json para guardar as imagens em uma coluna de uma tabela na base de dados:
 * photos : { photo : [ { "photo" : " " , "idioma1" : " " , ... , "idiomaN" : " " }, ... ] }
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 1.20
 * @since 11/06/2015
 * @license Todos os direitos reservados
 *
 */
class GestImage extends Core
{

    /**
     * @var string $ce_code - código da classe nas mensagens de erro
     *
     */
    private static $ce_code = "IMG";

    public function __construct()
    {
        ini_set("memory_limit", "64M");
        ini_set("upload_max_filesize", "16M");
        parent::__construct();
    }

    /**
     * Manipula o objeto json de armazenamento de fotos na base de dados.
     * Recebe o objeto json guardado na tabela da base de dados e transforma numa coleção de endereços de imagem ou elementos html img
     *
     * @param array $pictures_object = objeto json com as imagens e as legendas armazenado na base de dados
     * @param string $mode = pode ter os valores
     *                  "src" para devolver apenas o endereço da imagem
     *                  "img" para devolver a tag completa
     *                  "arrimg" para devolver uma string com varias imgens envoltas em tag img
     *                  "arr" para devolver uma array de array javascript "["endereço1", "legendas1"],...,["endereçoN", "legendasN"]"
     * @param srting $captions_lang = idioma
     * @param boolean $collection = se FALSE só retorna a primeira foto
     * @param string $html_attributes = atributos html
     *
     * @throws Exception code 1
     *
     * @return string - coleção de endereços de imagens ou elementos html img
     *
     */
    public function send_images_json($pictures_object, $mode, $captions_lang, $collection = 1, $html_attributes = NULL)
    {

        if (!$pictures_object)
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        $pictures = json_decode($pictures_object, TRUE);

        if ($pictures && is_array($pictures) && is_array($pictures['photos']))
        {
            $result = NULL;

            foreach ($pictures['photos'] as $value)
            {
                switch ($mode)
                {
                    case "img":
                        if (!empty($value['photo']))
                        {
                            $captions = (isset($captions_lang)) ? trim($value[$captions_lang], '"') : "";
                            $result .= "<img src=" . $value['photo'] . " alt='" . $captions . "' title='" . $captions . "' $html_attributes>";
                        }
                        break;
                    case "src":
                        if (!empty($value['photo']))
                        {
                            $result .= $value['photo'];
                        }
                        break;
                    case "imgarr" :
                        $captions = (isset($captions_lang)) ? trim($value[$captions_lang], '"') : "";
                        $result[] = "<img src=" . $value['photo'] . " alt='" . $captions . "' title='" . $captions . "' $html_attributes>";
                        break;
                    case "arr":
                        $captions = (isset($captions_lang)) ? trim($value[$captions_lang], '"') : "";
                        $comma    = ($result) ? "," : "";
                        $result .= $comma . '["' . $value['photo'] . '" ,"' . $captions . '"]';
                        break;
                    default:
                        break;
                }


                if (!$collection)
                    return trim($result, ",");
            }

            return $result;
        }

        if ($mode === "img" && $pictures_object)
            return "<img src=" . $pictures_object . "  $html_attributes>";

        return NULL;
    }

    /**
     * Cria um objeto json para guardar os imagens na base de dados.
     * Retorna este objeto:
     * {"photos" : [{"photos":"" , "mobile":"" , "mini":"", "lang1":"", ... ,"langN":""},..]}
     *
     * @uses Core::text_clean_json();
     *
     * @param array $config - array com 2 elementos ['image_name'] = nome de $_POST com o nome da imagem e ['captions'] = nome das legendas.
     * Normalmente o elemento options do objeto.
     *
     * @param array $client_images - nome das imagens e legendas. As chaves devem ser os valores da array config.
     *  $client_images[$config['image_name']] e $client_images[$config['captions]] ($_POST)
     *
     * @return null|string - objeto json
     *
     */
    public function make_json_img_capt(array $config, $client_images)
    {

        if (!is_array($client_images) || empty($client_images))
            return NULL;

        //objecto json de uma imagem
        $img_object = NULL;

        //array com o nome das imagens
        $images = NULL;

        //nome dos $_POST de legendas
        $captions_name = NULL;

        if (!isset($client_images) || empty($config['image_name']))
            return NULL;

        $img_name      = $config['image_name'];
        $captions_name = $config['captions'];

        if (!isset($client_images[$img_name]))
            return NULL;

        $images = $client_images[$img_name];

        if (!is_array($images))
            return NULL;

        foreach ($images as $img)
        {

            $object_element = NULL;

            if (empty($img))
                continue;

            $img_url = parse_url($img);

            //Se for uma url completa
            if (parent::validate_url($img) && isset($img_url['host']))
            {
                $s_name                   = $img;
                $object_element['photo']  = $s_name;
                $object_element['mobile'] = "";
                $object_element['mini']   = "";
            }
            else
            {
                //grava vários tamanhos das imagens
                $name                    = $img;
                $object_element['photo'] = _IMAGEURL . "/" . $img;
                $object_elemen['mobile'] = _IMAGEURL . "/mob_" . $name;
                $object_element['mini']  = _IMAGEURL . "/min_" . $name;
            }

            foreach ($GLOBALS['LANGPAGES'] as $lang)
            {
                $captions = NULL;

                if (isset($client_images[$lang . "_" . $captions_name][$img]))
                    $captions = $client_images[$lang . "_" . $captions_name][$img];

                @$object_element[$lang] .= $captions;
            }

            $img_object['photos'][] = $object_element;
        }

        return json_encode($img_object, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Cria uma imagem por omissão para um item sem imagem
     *
     * @uses MasterConfig::_RAWIMAGE, MasterConfig::_FONT, MasterConfig::_IMAGEPATH, MasterConfig::_IMAGEURL
     *
     * @param string $nome - nome do item
     * @param string $ref - referencia do item
     *
     * @return false|string - nome da imagem
     */
    public function createImage($nome, $ref = "0000")
    {

        $w = explode(" ", $nome);

        $im = ImageCreateFromPNG(_RAWIMAGE);

        $font = _FONT;

        $text_color = imagecolorallocate($im, 10, 10, 10);

        imagettftext($im, 10, 0, 5, 20, $text_color, $font, $ref);

        if (isset($w[0]))
        {
            imagettftext($im, 16, 0, 5, 45, $text_color, $font, $w[0]);
        }
        if (isset($w[1]))
        {
            imagettftext($im, 16, 0, 5, 65, $text_color, $font, $w[1]);
        }
        if (isset($w[2]))
        {
            imagettftext($im, 16, 0, 5, 85, $text_color, $font, $w[2]);
        }
        if (isset($w[3]))
        {
            imagettftext($im, 16, 0, 5, 105, $text_color, $font, $w[3]);
        }
        if (isset($w[4]))
        {
            imagettftext($im, 16, 0, 5, 125, $text_color, $font, $w[4]);
        }
        if (isset($w[5]))
        {
            imagettftext($im, 16, 0, 5, 145, $text_color, $font, $w[5]);
        }

        $path = _IMAGEPATH . $GLOBALS['NOW'] . ".png";

        $a = imagepng($im, $path);

        if (!$a)
        {
            return false;
        }
        else
        {
            return _IMAGEURL . $GLOBALS['NOW'] . ".png";
        }
    }

    /**
     * Cria imagens embrulhadas em uma DIV que podem ser usadas como fotogaleria. As imagens podem ser acompanhadas por legendas.
     * Pode incluir campos e botão de apagar para usar em fichas de edição.
     * Este metodo repete as mesmas configurações do função javascript imgallery_with_captions do arquivo mgp_nucleogest.
     * Esta função javascript tambem pode manipular as DIV criadas por este metodo.
     *
     * @param array $images_object     - objeto json com as imagens e as legendas armazenado na base de dados
     * @param string $input_image_name - nome do campo input das fotos
     * @param string $captions_name    - nome do campo input das legendas
     * @param boolean $with_captions   - se verdadeiro cria as fotos com legendas
     * @param boolean $allow_edit      - se verdadeiro formata campos input e apresenta botão para apagar
     *
     * @throws Exception code 1
     *
     * @return string HTML
     */
    public function make_photo_gallery_json($images_object, $input_image_name = "foto", $captions_name = "legenda", $with_captions = TRUE, $allow_edit = TRUE)
    {

        if (empty($images_object))
            return NULL;

        if (!$images_object = json_decode($images_object, TRUE))
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        if (!isset($images_object['photos']) && !is_array($images_object['photos']))
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        $name              = $input_image_name . "[]";
        $index             = 0;
        $html_image_blocks = NULL;

        foreach ($images_object['photos'] as $image)
        {

            if (!is_array($image))
                continue;

            $capt        = NULL;
            $del         = NULL;
            $input_image = NULL;
            $only_read   = "readonly";
            $img         = $image['photo'];

            $img_url = parse_url($image['photo']);

            $image_name = ($img_url['host'] == _RURL) ? array_pop(explode("/", $image['photo'])) : $image['photo'];

            if ($allow_edit)
            {

                $del         = '<img src="imagens/minidel.png" class="ig15A">';
                $input_image = '<input type="hidden" id="' . $image_name . '" name="' . $name . '" value="' . $image_name . '"/>';
                $only_read   = NULL;
            }

            if ($with_captions)
            {

                if (!$allow_edit)
                {

                    $capt .= "
                       <p draggable='false' class='dv98pC'>
                       " . $image[$GLOBALS['LANGPAGES'][0]] . "
                       </p>";
                }
                else
                {
                    foreach ($GLOBALS['LANGPAGES'] as $value)
                    {
                        $capt .= "
                       <p draggable='false' class='dv98pC'>
                       <img src='" . $GLOBALS['LANGFLAGS'][$value] . "' class='ig20M' draggable='false'>
                       <input type='text' name='" . $value . "_" . $captions_name . "[" . $image_name . "]' value='" . $image[$value] . "' class='tx90px35pxFF' draggable='false' $only_read >
                       </p>";
                    }
                }
            }

            $html_image_blocks .= "
                        <div data-index='$index' class='dvB' draggable='false'>
                            $del
                            <img src='" . $img . "' class='ig150xp150x'>
                            $input_image
                            $capt
                        </div>
                ";

            $index++;
        }

        return $html_image_blocks;
    }

    /**
     * Envi um elemento HTML img. Verifica se já é uma tag html ou apenas o endereço da imagem.
     *
     * @param string $s_img - nome da imagem ou tag img completa
     *
     * @return false|string - tag html img
     *
     */
    protected function make_html_img($s_img, $classe = NULL, $alt = NULL)
    {

        if (isset($s_img) && !empty($s_img))
        {

            return (preg_match("#<[ ]*img(.*)[ ]*>#i", $s_img)) ? $s_img : ' <img src="' . $s_img . '" class="' . $classe . '" alt="' . $alt . '"> ';
        }
        else
        {

            return FALSE;
        }
    }

    /**
     * Envia json com as imagens solicitadas e filtradas
     *
     * @uses Logex::$tcnx, Anti::validate_name()
     *
     * @return string - json com a lista de imagens
     */
    public function image_list()
    {
        if (!parent::check())
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        $pasta = (isset($_POST['pasta']) && $this->validate_name($_POST['pasta'])) ? $_POST['pasta'] : "";

        try
        {
            $dbcon = new PDO(_PDOM, _US, _PS);

            $sttm = $dbcon->prepare("SELECT id,nome,mini,pasta FROM foto_galeria WHERE pasta=? ORDER BY id ASC");

            $sttm->bindValue(1, $pasta, PDO::PARAM_STR);

            $sttm->execute();

            $result = $sttm->fetchAll();
        }
        catch (PDOException $ex)
        {
            throw new Exception(GestImage::$ce_code . __LINE__, 1);
        }

        $images = "";

        foreach ($result as $image)
        {
            $images .= ',["i:' . $image['id'] . '","' . $this->cut_str($image['nome'], 20) . '","' . _IMAGEURL . '/' . $image['mini'] . '","' . $image['pasta'] . '"]';
        }

        return '{"images":[' . ltrim($images, ",") . ']}';
    }

    /**
     * Faz o upload e guarda os dados da imagem na base de dados
     *
     * @uses Logex::$tcnx, Anti::validate_name(), GestImage::image_list(), GestImage::redimensiona
     *
     * @throws Exception code 1
     * @throws Exception code 2
     * @throws Exception code 3
     *
     * @return string - json com a lista das imagens
     */
    public function upload_image()
    {

        if (!$_FILES['foto'])
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        $photo = $_FILES['foto'];

        if ($photo['size'] < 10)
            throw new Exception(GestImage::$ce_code . __LINE__ . $photo['size'], 1);

        $name = str_replace(" ", "_", $photo['name']);

        $image = $photo['tmp_name'];

        //se exif não estiver instalodo usa o gd
        if (function_exists('exif_imagetype'))
        {
            if (!$typeimg = exif_imagetype($image))
                throw new Exception(GestImage::$ce_code . __LINE__, 3);

            if ($typeimg <> 1 && $typeimg <> 2 && $typeimg <> 3)
                throw new Exception(GestImage::$ce_code . __LINE__, 3);
        }
        else
        {
            if (!$typeimg = getimagesize($image))
                throw new Exception(GestImage::$ce_code . __LINE__, 3);

            if ($typeimg[2] <> IMAGETYPE_GIF && $typeimg[2] <> IMAGETYPE_JPEG && $typeimg[2] <> IMAGETYPE_JPEG2000 && $typeimg[2] <> IMAGETYPE_PNG)
                throw new Exception(GestImage::$ce_code . __LINE__, 3);
        }

        //TODO - otimizar
        $folder = (isset($_POST['pasta']) && parent::validate_name($_POST['pasta'])) ? $_POST['pasta'] : "";
        try
        {
            $r_check_name = parent::make_call("spCheckImageName", array($name));

            if (isset($r_check_name[0]['nome']))
            {
                $check_name = $r_check_name[0]['nome'];
            }
            else
            {
                $check_name = FALSE;
            }
        }
        catch (Exception $ex)
        {
            throw new Exception(GestImage::$ce_code . __LINE__, 3);
        }

        if ($check_name)
            throw new Exception(GestImage::$ce_code . __LINE__, 2);

        $path = _IMAGEPATH . $name;

        if (!move_uploaded_file($image, $path))
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        try
        {
            $mini_pic = $this->redimensdiona($path, "mini_" . $name, 100, 100);
            $this->redimensdiona($path, "min_" . $name, 400, 400);
            $this->redimensdiona($path, "mob_" . $name, 1000, 1000);
        }
        catch (Exception $exp)
        {
            $mini_pic = "";
        }

        try
        {


            $params[] = $name;
            $params[] = $folder;
            $params[] = $mini_pic;

            $result = parent::make_call("spInsertImage", $params);
            $s      = "FDSF";
            if (!$result || empty($result[0]))
            {
                unlink($path);
                throw new Exception(GestImage::$ce_code . __LINE__, 1);
            }


            if (!$mess = json_decode($result[0]['ret'], TRUE))
            {
                unlink($path);
                throw new Exception(GestImage::$ce_code . __LINE__, 1);
            }

            if (isset($mess['mgp_error']))
            {
                unlink($path);
                throw new Exception(GestImage::$ce_code . __LINE__, 1);
            }
        }
        catch (Exception $ex)
        {
            unlink($path);
            throw new Exception(GestImage::$ce_code . __LINE__, 1);
        }

        try
        {
            return $this->image_list();
        }
        catch (Exception $exp)
        {
            throw new Exception(GestImage::$ce_code . __LINE__, 1);
        }
    }

    /**
     * Redimensiona uma imagem
     *
     * @param string $imagem - caminho para uma imagem
     * @param string $name - nome para salvar a imagem redimensionada.
     * @param int $w - largura da nova imagem
     * @param int $h - altura da nova imagem
     *
     * @return string - nome da imagem
     *
     */
    private function redimensdiona($imagem, $name, $w, $h)
    {

        //criamos uma nova imagem ( que vai ser a redimensionada) a partir da imagem original
        if (function_exists('exif_imagetype'))
        {
            if (!$tipoimg = exif_imagetype($imagem))
                throw new Exception(GestImage::$ce_code . __LINE__, 3);
        } else
        {
            if (!$typeimg = getimagesize($imagem))
                throw new Exception(GestImage::$ce_code . __LINE__, 3);

            $tipoimg = $typeimg[2];
        }

        switch ($tipoimg)
        {
            case IMAGETYPE_JPEG :
                $imagem_orig = ImageCreateFromJPEG($imagem);
                break;
            case IMAGETYPE_PNG :
                $imagem_orig = ImageCreateFromPNG($imagem);
                break;
            case IMAGETYPE_GIF :
                $imagem_orig = ImageCreateFromGIF($imagem);
                break;
        }
        //pegamos a altura e a largura da imagem original
        $maxw = $w;
        $maxh = $h;

        $width  = imagesx($imagem_orig);
        $height = imagesy($imagem_orig);
        $scale  = min($maxw / $width, $maxh / $height);

        if ($scale < 1)
        {
            $largura = floor($scale * $width);
            $altura  = floor($scale * $height);
        }
        else
        {
            $largura = $width;
            $altura  = $height;
        }

        if (!$imagem_fin = imagecreatetruecolor($largura, $altura))
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        imagealphablending($imagem_fin, false);
        imagesavealpha($imagem_fin, true);

        //copiamos o conteudo da imagem original e passamos para o espaco reservado a redimencao
        if (!imagecopyresized($imagem_fin, $imagem_orig, 0, 0, 0, 0, $largura, $altura, $width, $height))
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        $path = _IMAGEPATH . $name;

        //Salva a imagem
        switch ($tipoimg)
        {
            case IMAGETYPE_JPEG :
                $a = imagejpeg($imagem_fin, $path);
                break;
            case IMAGETYPE_PNG :
                $a = imagepng($imagem_fin, $path);
                break;
            case IMAGETYPE_GIF :
                $a = imagegif($imagem_fin, $path);
                break;
        }

        //Libera a Memoria
        ImageDestroy($imagem_orig);
        ImageDestroy($imagem_fin);

        if (!$a || !$imagem_fin)
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        return $name;
    }

    /**
     * Apaga uma imagem na base de dados
     *
     * @param string $db_table - nome da tabela que guarda as imagens
     * @param string $id_column - nome da coluna primary key
     * @param string $folder_column - nome da coluna de pasta
     * @param string $image_id - valor da imagem na columa primary key
     *
     * @return boolean | json
     *
     */
    public function delete_image($db_table, $id_column, $folder_column, $image_id)
    {

        if (!parent::check())
            return parent::mess_alert("GIM" . __LINE__);

        $id;

        if (!$id = $this->id($image_id))
            return parent::mess_alert("GIM" . __LINE__);

        $rfold   = Logex::$tcnx->query("SELECT $folder_column FROM $db_table WHERE $id_column = $id");
        $folders = $rfold->fetch_array();

        $del = Logex::$tcnx->query("DELETE FROM $db_table WHERE $id_column = $id ");

        if (!$del)
        {

            return FALSE;
        }
        else
        {

            return '{"result":["' . $folders[0] . '","' . $id . '"]}';
        }
    }

}

/**
 * Esta classe faz a gestão das pastas de cada modulo.
 * Permite a criação de pasta com 1 ou 0 nivel de profundidade.
 * Esta classe exige a definição de 2 mysql store procedure que devem retornar 6 campos(toke, nome, nome_2, icon, folder, status).
 *
 * Para criar pastas simples sem itens deve ser passada a string "SIMPLE" como argumento.
 *
 * Para criar pasta para modulos com uma imagem como icon deve ser passada a string "MODULE" como argumento
 *
 * Com qualquer outro valor, a classe tentará criar pasta com itens dentro.
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 3.10.030615
 * @since 03/06/2015
 * @license Todos os direitos reservados
 */
class GestFolders extends Core
{

    /**
     * Objeto de criação de imagens . Classe GestImage
     *
     * @var object = NULL
     */
    private $image          = NULL;

    /**
     * Define o tipo de pastas que retorna
     * Por omissão as pastas são criadas com itens e podem ser abertas, se falso as pastas são criadas sem itens
     *
     * @var boolean = FALSE
     */
    private $simple_folders = FALSE;

    /**
     * Define o estado do item po omissão
     *
     * @var string ="offline"
     */
    private $default_status = "offline";

    /**
     * Código de erro da classe
     *
     * @var string ="FLD"
     */
    private $exp_prefix = "FLD";

    /**
     * Construtor da classe. O argumento define o tipo de pastas a serem criadas.
     *
     * Para criar pastas simples sem itens deve ser passada a string "SIMPLE" como argumento.
     *
     * Para criar pasta para modulos com uma imagem como icon deve ser passada a string "MODULE" como argumento
     *
     * Com qualquer outro valor, a classe tentará criar pasta com itens dentro.
     *
     * @param string - define o tipo de pastas a ser criado
     *
     */
    function __construct($type = NULL)
    {
        parent::__construct();

        if ($type === "SIMPLE")
        {
            $this->simple_folders = TRUE;
        }
        elseif ($type === "MODULE")
        {
            $this->image = new GestImage();
        }
        else
        {
            $this->simple_folders = FALSE;
        }
    }

    /**
     * Altera o valor por omissão do estado do item. O estado por omissão é offline.
     * Se o parametro do for "TRUE" então esse valor passa para online
     *
     * @param boolean $online
     *
     */
    public function set_status($online = FALSE)
    {
        if ($online)
            $this->default_status = "online";
    }

    /**
     * Cria pastas sem itens dentro
     *
     * @uses Logex::check()
     * @uses Core::mess_alert()
     * @uses Core::make_call()
     * @uses GestFolders::simple_folders_itens
     * @uses GestFolder::get_folders_itens
     *
     * @param string $procedure - nome da stored procedure
     * @param array $param - parametros da stored procedure
     *
     * @return string - objeto json com a lista de pastas
     *
     */
    public function make_folders($procedure, array $param = NULL)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $rows    = NULL;
        $folders = NULL;

        try
        {
            $rows = parent::make_call($procedure, $param);
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        if (!is_array($rows))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if ($this->simple_folders)
        {
            $folders = $this->simple_folders_itens($rows);
        }
        else
        {
            $folders = $this->get_folders_itens($rows);
        }

        return $folders;
    }

    /**
     * Cria pastas sem itens
     *
     * @param array $itens - nome das pastas
     *
     * @return string - representa um objeto json {"sfolder":["folder1","folder2",...,"folderN"]}
     *
     */
    private function simple_folders_itens(array $itens)
    {
        $rfold = array();

        foreach ($itens as $fold)
        {
            $rfold[] = '"' . $fold['folder'] . '"';
        }

        $rfold = array_unique($rfold);

        $folders = implode(",", $rfold);

        return '{"sfolder":[' . $folders . ']}';
    }

    /**
     * Cria os itens de cada pasta
     *
     * @uses GestImage::send_images_json()
     *
     * @param mysqli_result $q_item - resultado da pesquisa na base de dados
     *
     * @return string - objetos json com uma array de objetos {"nome_pasta":[{"id":"","status":"","name":"","image":""},...] , ...}
     *
     */
    private function get_folders_itens(array $q_item)
    {

        $folder = NULL;

        foreach ($q_item as $r_item)
        {
            $jname = (empty($r_item["name"]) || $r_item["name"] === " ") ? $r_item["name_2"] : $r_item["name"];

            $jstatus = (!empty($r_item["status"])) ? $r_item["status"] : $this->default_status;

            $jimg = (is_a($this->image, "GestImage") && !empty($r_item["icon"])) ? $this->image->send_images_json($r_item["icon"], "src", NULL, 0, NULL) : NULL;

            $key_name = $r_item['folder'];

            $folder[$key_name][] = array("id" => $r_item["toke"], "status" => $jstatus, "name" => $jname, "image" => $jimg);
        }

        if (is_array($folder))
            return json_encode($folder);
    }

    /**
     * Muda um item de pasta
     *
     * @param string $call - nome da stored procedure a ser chamada.
     * @param array $param - array com os paramentros da stored procedure. O item 0 deve ser o id e o item 1 a nova pasta
     *
     * @uses Logex::check()
     * @uses Core::mess_alert()
     * @uses Core::id()
     * @uses Anti::validate_name()
     * @uses Core::make_call()
     * @uses GestFolders::simple_folders_itens
     * @uses GestFolder::get_folders_itens
     *
     * @return string - objeto json atualizado para construir a lista de pasta
     *
     */
    public function change_folder($call, array $param)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = $this->id($param[0]))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $param[0] = $id;

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $n_folder = parent::validate_name($param[1]);

        $param[1] = $n_folder;

        $rows = NULL;

        try
        {
            $rows = parent::make_call($call, $param);
        }
        catch (PDOException $exp)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        if ($this->simple_folders)
            return '{"sfolder":["' . $n_folder . '","i:' . $id . '"],"folders":' . $this->simple_folders_itens($rows) . '}';

        return '{"result":["' . $n_folder . '","i:' . $id . '"],"folders":' . $this->get_folders_itens($rows) . '}';
    }

}

/**
 * Ficha de apresentação de um item
 *
 * __DinamicGest: table,submenu,admin,content, midia,export__
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 1.0
 * @since 08/10/2014
 * @license Todos os direitos reservados
 */
class ElementDatalist extends Core
{

    /**
     * Cria a opçoes de uma datalist
     *
     * @param array $config- array de configuração. Corresponde a normalmente aos objetos "content" e "admin", ou seja um elemento de nivel 2 do Master.json.
     * @param string $table - nome da tabela da base de dados de onde são retiradas as opções
     * @param array $target_values - valor do target (resultado de pesquisa na base de dados)
     *
     */
    public function make_datalist_options(&$config, $table, &$target_value)
    {

        foreach ($config as $key => $value)
        {

            $targets = array();
            $opt     = array();
            $cond    = NULL;

            if (empty($value["type"]) || substr($value["type"], 2) != "DATALIST")
                continue;

            $options = $value['options'];

            if (array_key_exists($key, $targets))
            {
                $target_db = $config[$targets[$key]]['db'];

                $ini_value = (isset($target_value[$target_db])) ? $target_value[$target_db] : "";

                $cond = "AND $target_db<>'' AND $target_db ='$ini_value'";
            }

            if (!empty($options['target']))
            {

                $targets[$options['target']] = $key;
            }

            $tablex = explode(".", $value['db']);

            $tcnx = new mysqli(_LC, _US, _PS, _DB);
            $tcnx->set_charset("utf8");
            $tcnx->real_query("SELECT DISTINCT $value[db] FROM  " . $tablex[0] . "  WHERE $value[db]<>'' $cond ORDER BY  $value[db] ASC");

            do
            {

                if ($rslt = $tcnx->store_result())
                {
                    while ($cQuery = $rslt->fetch_array())
                    {
                        if (!empty($cQuery[0]))
                            $opt[] = $cQuery[0];
                    }

                    $rslt->free_result();
                }
            } while ($tcnx->more_results() && $tcnx->next_result());

            $tcnx->close();

            $all_options = (is_array($options['values'])) ? array_unique(array_merge($opt, $options['values'])) : $opt;

            $config[$key]['options']['values'] = $all_options;
        }
    }

    /**
     * Simula uma datalist
     * @param string $value - valor do campo input
     * @param array $options - propriedade options do objeto
     * @param string $name - nome do campo input
     * @param $key - nome do objeto (chave)
     *
     * @return string HTML
     *
     */
    public function data_list($value, $options, $name, $key = NULL)
    {

        $parag = "";

        if (!empty($options['values']) && is_array($options['values']))
        {

            natcasesort($options['values']);

            foreach ($options['values'] as $o_value)
            {
                $parag .= '<p>' . $o_value . '</p>';
            }
        }

        $tgt = (!empty($options['target'])) ? "data-target='" . $options['target'] . "'" : "";

        return "
            <div>
                <input value='$value' type='text' " . $tgt . " name='$name' class='editfield' data-action='listItem' id='$name'/>
                <div class='dvlist' id='$key'>
                    " . $parag . "
                </div>
            </div>

        ";
    }

}

class ElementRadioButton extends Core
{
    /*
     * cria elementos html radiobutton
     * $OB objeto com chaves valores
     * $DB valor da base de dados, valor selecionado
     * $NM nome
     */

    public function make_radiob($OB, $DB, $NM)
    {

        $buttons = NULL;

        if (!is_array($OB))
        {

            return FALSE;
        }

        $c = 0;

        foreach ($OB as $rad => $radio)
        {

            if (empty($DB) && !$c)
            {

                $selected = "backyellow";
                $check    = 'checked="checked"';
            }
            else
            {

                $selected = NULL;
                $check    = NULL;

                if ($rad == $DB)
                {

                    $selected = "backyellow";
                    $check    = 'checked="checked"';
                }
            }

            $buttons .= '
                <label class="smanag ' . $selected . '" id="' . $NM . '">
                    ' . $radio . '
                    <input type="radio" name="' . $NM . '" value="' . $rad . '" ' . $check . '  class="rmanag">
                </label> ';

            $c++;
        }

        return "<div class='wrapb'>" . $buttons . "</div>";
    }

}

//TODO
class ElementCheckBox extends Core
{

    /**
     * @var string $ce_code - código da classe nas mensagens de erro
     *
     */
    private static $ce_code = "IMG";

    /**
     * Cria várias checkboxes. Os nomes correspondem aos valores.
     *
     * @param json $options_values - objeto json com pares nome da opção : valor da opção
     * @param strin $dbvalue - valor da coluna na base de dados
     * @param string $name - nome do campo input
     */
    public function make_checkb($config, $dbvalue, $name)
    {
        $checks  = NULL;
        $def     = NULL;
        $options = NULL;

        if (!is_array($config['options']))
            return FALSE;

        if (!is_array($config['options']['boxes']))
            return FALSE;

        $req = (!empty($config['required'])) ? "data-required=true" : NULL;

        $def = array();
        if (isset($config['default']))
            $def = explode(",", $config['default']);


        foreach ($config['options']['boxes'] as $Kchk => $Vchk)
        {
            $selected = NULL;
            $chks     = NULL;

            if (isset($dbvalue[$Vchk]) || in_array($Vchk, $def))
            {
                $selected = "backyellow";
                $chks     = 'checked';
            }

            if (!empty($options['as_array']))
            {
                $box_name  = $name . "[]";
                $box_value = $Kchk;
            }
            else
            {
                $box_name  = $name . '_' . $Kchk;
                $box_value = "1";
            }

            $checks .= <<<EOT
                <label  class="smanag  {$selected}" id="{$name}_{$Kchk}" >
                    {$Kchk}
                    <input type="checkbox" name="{$box_name}" value="{$box_value}" {$chks} class="rmanag">
                </label>
EOT;
        }

        return "<div id='$name' class='wrapb' $req>" . $checks . "</div>";
    }

    /**
     * Retorna os valores da checkbox para gravar na base de dados.
     * Retorna os valores numa string dos valores separados por virgulas
     *
     * @param array|sting input_field - array $_POST
     * @param array $field - array de configuração do campo
     * @param string &namex - nome do campo
     *
     * @throws caso o campo seja obrigatório e não tiver nenhuma seleção lança uma exepção
     *
     * @return string valores separados por virgulas
     *
     */
    public function chk_extract_values($input_field, array $field, $namex)
    {
        if (empty($input_field))
            return FALSE;

        $options = $field['options'];

        if (!is_array($options))
            return FALSE;

        $query       = NULL;
        $value       = NULL;
        $count_value = 0;

        if (!$options['as_array'])
        {
            foreach ($options['boxes'] as $chkkey => $chkvalue)
            {
                $value = NULL;
                if (!empty($chkvalue))
                {
                    $post_key = urlencode($namex . "_" . $chkkey);
                    $value    = (!empty($input_field[$post_key]) || !empty($input_field[$namex . "_" . $chkkey])) ? 1 : 0;
                    $query .= $chkvalue . "=" . $value . ",";

                    if ($value)
                        $count_value++;
                }
            }
        } else
        {
            foreach ($options['boxes'] as $chkkey => $chkvalue)
            {
                if (!empty($chkvalue))
                {
                    $value = (!empty($input_field[$namex][$chkvalue])) ? $input_field[$namex][$chkvalue] : "";
                    $query .= $chkvalue . "=" . $value . ",";
                    if ($value)
                        $count_value++;
                }
            }
        }

        if (!empty($field['required']) && !$count_value)
            throw new Exception('"' . $namex . '":1', 1);

        return ltrim($query, ",");
    }

    public function chk_extract_sheet(array $config, array $db_values)
    {

        $result = NULL;

        if (isset($config['options']['boxes']))
        {
            foreach ($config['options']['boxes'] as $key => $value)
            {
                if ($db_values[$value])
                    $result .= " " . $key . ",";
            }
        }

        $result = trim($result, ",");

        return $result;
    }

}

class GDate extends Core
{

    /**
     * Manipula uma data fornecida
     *
     * @param string $date - data
     * @param boolean $show_hour - se true a data apresenta as horas, minutos e segundos
     * @param string $U - constante que define o tipo de data retornado "DATE" = 01-01-1970 00:00:00,"TIMEST" = unix timestamp,"DATEBD" = 19701230245959
     * @param boolean $fill - se true e não for fornecida a data faz retornar a data no momento, se false a funçao não retorna nada
     * @param strong $S - caracter separador da data
     *
     * @return string - data no formato definido pela constante
     *
     */
    public static function make_date($date = NULL, $show_hour = 1, $U = 'DATE', $fill = 1, $S = "-")
    {
        if (empty($date) && !$fill)
            return "0000-00-00";

        if (!empty($date))
        {
            $dt   = date_parse($date);
            $temp = ($dt['warning_count'] > 0) ? FALSE : mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']);
        }
        else
        {
            $temp = time();
        }

        if ($temp)
        {

            switch ($U)
            {
                case 'DATE' :
                    $dat  = strftime("%d$S%m$S%Y", $temp);
                    $hour = ($show_hour) ? strftime("%H:%M:%S", $temp) : "";
                    $ts   = $dat . "  " . $hour;
                    break;
                case 'TIMEST' :
                    $ts   = mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']);
                    break;
                case 'DATEBD' :
                    $da   = strftime("%Y%m%d", $temp);
                    $ho   = ($show_hour) ? strftime("%H%M%S", $temp) : "";
                    $ts   = $da . $ho;
                    break;
            }

            return $ts;
        }
        else
        {
            return "0000-00-00";
        }
    }

    /*
     *
     */

    public function datiAR($dias, $mesR, $sAno)
    {

        $ano  = array();
        $year = strftime("%Y");
        for ($i = 0; $i < 108; $i++)
        {
            $ano[$i] = $year - $i;
        }
        $meses = array('Jan' => "01", 'Fev' => "02", 'Mar' => "03", 'Abr' => "04", 'Mai' => "05", 'Jun' => "06", 'Jul' => "07", 'Ago' => "08", 'Set' => "09", 'Out' => "10", 'Nov' => "10", 'Dez' => "12");
        $dia   = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31");

        if (strlen($dias) == 0)
        {
            foreach ($dia as $numbDia)
            {
                $days .= "<option value=$numbDia>$numbDia</option>";
            }
        }
        if (strlen($dias) != 0)
        {
            foreach ($dia as $numbDia)
            {
                if ($dias == $numbDia)
                {
                    $days .= "<option value=$numbDia selected>$numbDia</option>";
                }
                else
                {
                    $days .= "<option value=$numbDia>$numbDia</option>";
                }
            }
        }

        if ($mesR == 0)
        {
            foreach ($meses as $nomeMes => $valor)
            {
                $mounth .= "<option value='$valor'>$nomeMes</option>";
            }
        }
        else
        {
            foreach ($meses as $nomeMes => $valor)
            {
                if ($mesR == $valor)
                {
                    $mounth .= "<option value='$valor' selected>$nomeMes</option>";
                }
                else
                {
                    $mounth .= "<option value='$valor'>$nomeMes</option>";
                }
            }
        }

        if ($sAno == 0)
        {
            for ($f = 0; $f < 109; $f++)
            {
                $year .= "<option value=$ano[$f]>$ano[$f]</option>";
            }
        }
        if ($sAno != 0)
        {
            foreach ($ano as $valor)
            {
                if ($sAno == $valor)
                {
                    $year .= "<option value=$valor selected>$valor</option>";
                }
                else
                {
                    $year .= "<option value=$valor >$valor</option>";
                }
            }
        }

        return "<select name='dia[]' id='dia' size='1' class='sl60'>
    <option value=''> -- </option>
    $days
    </select>
     /
    <select name='mes[]' id='mes' size='1' class='sl60'>
    <option value=''> -- </option>
    $mounth
    </select>
     /
    <select name='ano[]' id='ano' size='1' class='sl75'>
    <option value=''> ---- </option>
    $year
    </select>
";
    }

    public function dati($dias, $mesR, $sAno)
    {

        $ano  = array();
        $year = strftime("%Y");
        for ($i = 0; $i < 108; $i++)
        {
            $ano[$i] = $year - $i;
        }
        $meses = array('Jan' => "01", 'Fev' => "02", 'Mar' => "03", 'Abr' => "04", 'Mai' => "05", 'Jun' => "06", 'Jul' => "07", 'Ago' => "08", 'Set' => "09", 'Out' => "10", 'Nov' => "10", 'Dez' => "12");
        $dia   = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31");

        if (strlen($dias) == 0)
        {
            foreach ($dia as $numbDia)
            {
                $days .= "<option value=$numbDia>$numbDia</option>";
            }
        }
        if (strlen($dias) != 0)
        {
            foreach ($dia as $numbDia)
            {
                if ($dias == $numbDia)
                {
                    $days .= "<option value=$numbDia selected>$numbDia</option>";
                }
                else
                {
                    $days .= "<option value=$numbDia>$numbDia</option>";
                }
            }
        }

        if ($mesR == 0)
        {
            foreach ($meses as $nomeMes => $valor)
            {
                $mounth .= "<option value='$valor'>$nomeMes</option>";
            }
        }
        else
        {
            foreach ($meses as $nomeMes => $valor)
            {
                if ($mesR == $valor)
                {
                    $mounth .= "<option value='$valor' selected>$nomeMes</option>";
                }
                else
                {
                    $mounth .= "<option value='$valor'>$nomeMes</option>";
                }
            }
        }

        if ($sAno == 0)
        {
            for ($f = 0; $f < 109; $f++)
            {
                $year .= "<option value=$ano[$f]>$ano[$f]</option>";
            }
        }
        if ($sAno != 0)
        {
            foreach ($ano as $valor)
            {
                if ($sAno == $valor)
                {
                    $year .= "<option value=$valor selected>$valor</option>";
                }
                else
                {
                    $year .= "<option value=$valor >$valor</option>";
                }
            }
        }

        return "<select name='dia' id='dia' size='1' class='sl60'>
    <option value=''> -- </option>
    $days
    </select>
     /
    <select name='mes' id='mes' size='1' class='sl60'>
    <option value=''> -- </option>
    $mounth
    </select>
     /
    <select name='ano' id='ano' size='1' class='sl75'>
    <option value=''> ---- </option>
    $year
    </select>
";
    }

}

class config_validation
{
    /*
     * abre o arquivo json
     */

    public function json_file($JS)
    {

        if ($obj = file_get_contents('/var/www/vhosts/kyusho.pt/DinamicGestC/Master.json'))
        {

            $r_obj = json_decode($obj, TRUE);

            $this->debug_json_ob($r_obj[$JS]);

            return $r_obj[$JS];
        }
        else
        {

            return FALSE;
        }
    }

    private function debug_json_ob($OB)
    {

        $ob_fields = array("admin", "fields", "midia", "export", "search", "links");

        foreach ($OB as $k_ob => $v_ob)
        {

            if (!in_array($k_ob, $ob_fields))
                trigger_error("Objeto json mal configurado. ", E_USER_ERROR);

            switch ($k_ob)
            {
                case 'admin' :
                    $this->debug_admin($v_ob);
                    break;

                default :
                    break;
            }
        }
    }

    private function debug_admin($OB)
    {

        if (!isset($OB['private']) && !is_array($OB['private']))
            trigger_error("Objeto adim[private] mal configurado. ", E_USER_ERROR);

        if (!isset($OB['public']) && !is_array($OB['public']))
            trigger_error("Objeto adim[private] mal configurado. ", E_USER_ERROR);

        $priv_fields = array("toke", "icon", "identifier", "notes", "pubish", "order", "folder", "status");
        $pub_fields  = array("date", "date_act");

        foreach ($priv_fields as $k_pv => $v_pv)
        {

            if (array_key_exists($v_pv, $OB['private']))
                unset($priv_fields[$k_pv]);
        }

        foreach ($pub_fields as $k_pu => $v_pu)
        {

            if (array_key_exists($v_pu, $OB['public']))
                unset($pub_fields[$k_pu]);
        }

        if (count($priv_fields))
            trigger_error("Campos obrigatorios do objeto adim[private] mal configurado. ", E_USER_ERROR);

        if (count($pub_fields))
            trigger_error("Campos obrigatorios do objeto adim[private] mal configurado. ", E_USER_ERROR);
    }

}

class GestSocial extends Core
{

    private $exp_prefix = "GSOC";
    
    /**
     * cria os botões para as redes sociais. Está função corta o texto para ficar com o tamnho de 90 caracteres
     * 
     * @param string $url = link para a página
     * @param string $turl = link curto para ser usado no twitter e redes com limitações de caracteres
     * @param string $txt = texto descritivo
     * @param string $img = imagem     * 
     * @param boolean$ slk = se TRUE mostra o botão linkedin
     * 
     * @return string html e javascrip com os botões para as seguintes redes socias: facebook, twitter, google+, linkedin
     * 
     * 
     */
    public function social_buttons($url, $turl, $txt, $img, $slk) {

        $lkd = ($slk) ? "<div class='butpin'><script src='//platform.linkedin.com/in.js' type='text/javascript'></script><script type='IN/Share' data-url='" . $url . "'></script></div>" : "";
        
        try
        {
            $text = parent::cut_text($txt, 90, FALSE);
        }
        catch (Exception $ex)
        {
            $text = NULL;
        }
        
        

        return "<div class='isocial'><div class='butface'><div class='fb-like' data-href='" . $url . "' data-send='true' data-width='100' data-show-faces='false'  data-layout=\"button_count\"></div></div><div class='butpin'><a href='http://pinterest.com/pin/create/button/?url=" . $url . "&media=" . $img . "&description=" . $txt . "' class='pin-it-button' count-layout='none'><img border='0' src='//assets.pinterest.com/images/PinExt.png' title='Pin It' /></a></div><div class='butgmais'><div class='g-plusone' data-size='medium' data-annotation='none' ></div><script type='text/javascript'>window.___gcfg = {lang: 'pt-PT'};(function() {var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;po.src = 'https://apis.google.com/js/plusone.js';var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);})();</script></div><div class='butpin'><a href='https://twitter.com/share?url=" . $turl . "' class='twitter-share-button' data-lang='en' data-url='" . $turl . "' data-counturl='" . $turl . "' data-text='$text' data-count='none'>Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='//platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');</script></div>$lkd</div>";
    }

    /**
     * @param array $list com o nome da redes sociais onde publicar
     * @param array $config array de configuração da exportação do item
     * @param string id do item
     *
     */
    public function social_publish($list, $config, $id)
    {

        if (!is_array($list))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        foreach ($list as $value)
        {
            switch (strtolower($value))
            {
                case "facebook" :
                    $ret_mess = $this->send_facebook($id, $config);
                    break;
            }
        }

        return $ret_mess;
    }

    /**
     * publica post no facebook
     * @param string $item_id id do item a publicar
     * @param array $config array de configuração do modulo
     *
     */
    private function send_facebook($item_id, $config)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = parent::id($item_id))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $data = parent::export_item($id, $config);

        if (!$data)
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $pt = $GLOBALS['LANGPAGES'][0];

        $mensagem = $data[$pt]['title'];

        $descricao = $data[$pt]['title'] . " \n " . parent::cut_text($data[$pt]['text'], 150);

        $imagem = $data[$pt]['img'];

        $nome = $data[$pt]['title'];

        $caption = $data[$pt]['anchor_text'];

        $tm = $GLOBALS['NOW'];

        $linke = str_replace("****#substitui_por_outra_coisa#****", "fb=" . $tm, $data[$pt]['link']);

        try
        {
            $net_data = $this->network_data("facebook");

            $page_id = $net_data['user_id'];

            $query = array("message" => $mensagem, "link" => $linke, "picture" => $imagem, "name" => $nome, "description" => $descricao, "caption" => $caption, "access_token" => $net_data['token']);

            $url = "https://graph.facebook.com/v2.2/$page_id/feed";

            $resp = $this->social_connect($url, "POST", $query);

            if ($post_message = json_decode($resp, TRUE))
            {
                $post_id     = $post_message['id'];
                $post_status = "publicado com sucesso.";
            }
            else
            {
                $post_id     = "";
                $post_status = "não foi possivel publicar.";
                $post_error  = "erro: <b>$resp</b>";
            }

            $pub_date = date("d-m-Y H:i:s", time());

            $relatorio = <<<EOF

					RELATÓRIO DE PUBLICAÇÃO NO FACEBOOK
					------------------------------------------------------------

					id post: <b>{$post_id}</b>

					id página : <b>{$page_id}</b>

					fb : <b>{$tm}</b>

					titulo post:  <b>{$nome}</b>

					data de publicação: <b>{$pub_date}</b>

					idioma: <b>{$pt}</b>

					estado: <b>{$post_status}</b>

					{$post_error}

EOF;

            $note    = new GestNotes();
            $note_id = $note->insert_note("facebook", $relatorio, "Publicação no Facebook");
            $note->register_note($config['notes'], "($note_id,$item_id)");

            return TRUE;
        }
        catch (Exception $exp)
        {
            //throw new Exception($exp -> getMessage() . "-" . __LINE__, 1);

            return FALSE;
        }
    }

    /**
     * Procura o id de um usuário do facebook
     *
     * @param string $url - url da página do utilizador
     *
     * @throws Exception code 1
     *
     * @return Number
     */
    public function find_facebook_id($url)
    {

        if (!$url)
            throw new Exception($this->exp_prefix . __LINE__, 1);

        if ($face_url = parse_url($url))
        {
            if (($face_url['host'] != "www.facebook.com") && ($face_url['host'] != "facebook.com"))
                throw new Exception($this->exp_prefix . __LINE__, 1);

            if (!empty($face_url['query']))
            {
                $face_id = "/id=(([0-9])+)/";
                preg_match($face_id, $face_url['query'], $matches);

                if (!empty($matches[1]))
                    return $matches[1];
            }

            $break_path = explode("/", urldecode($face_url['path']));

            $face_name = ($break_path[1] == "pages") ? end($break_path) : $break_path[1];

            try
            {
                $responsex = $this->social_connect("https://graph.facebook.com/" . $face_name . "?fields=id,name,picture");
            }
            catch (Exception $exp)
            {
                try
                {
                    $dtoken = $this->network_data("facebook");
                    $token  = "access_token=" . $dtoken['token'];
                }
                catch (Exception $exp)
                {
                    throw new Exception($exp->getMessage() . "-" . __LINE__, 1);
                }

                if (!$responsex = $this->social_connect("https://graph.facebook.com/search?q=" . $face_name . "&type=page&" . $token))
                    throw new Exception($exp->getMessage() . __LINE__, 1);
            }

            if (!$facebook_data = json_decode($responsex, TRUE))
                throw new Exception($this->exp_prefix . __LINE__, 1);

            if (!empty($facebook_data['data'][0]['id']))
            {
                $rd = $facebook_data['data'][0]['id'];
            }
            elseif (!empty($facebook_data['id']))
            {
                $rd = $facebook_data['id'];
            }
            else
            {
                throw new Exception($this->exp_prefix . __LINE__, 1);
            }

            if (!$rd)
                throw new Exception($this->exp_prefix . __LINE__, 1);

            return $rd;
        } else
        {
            throw new Exception($this->exp_prefix . __LINE__, 1);
        }
    }

    /**
     * @throws exception
     */
    public function facebook_image($user_id)
    {
        /* if (!is_int($user_id))
          return FALSE; */

        $infor = array();

        $url = "https://graph.facebook.com/$user_id/picture?redirect=0&height=200&type=normal&width=200";

        $url2 = "https://graph.facebook.com/$user_id";

        try
        {
            $fetch_face = $this->social_connect($url);

            if (!$face_image = json_decode($fetch_face, TRUE))
                throw new Exception($this->exp_prefix . __LINE__, 1);

            $infor['image'] = $face_image['data']['url'];
        }
        catch (Exception $exp)
        {
            throw new Exception($exp->getMessage(), 1);
        }

        try
        {
            $rfetch_face = $this->social_connect($url2);

            $infor['dados'] = $rfetch_face;

            if (!$fetch_face = json_decode($rfetch_face, TRUE))
                throw new Exception($this->exp_prefix . __LINE__, 1);

            if (isset($fetch_face['name']))
                $infor['nome'] = $fetch_face['name'];

            if (isset($fetch_face['first_name']))
                $infor['nome'] = $fetch_face['first_name'];

            if (isset($fetch_face['last_name']))
                $infor['apelido'] = $fetch_face['last_name'];

            if (isset($fetch_face['gender']))
            {
                if ($fetch_face['gender'] == "male")
                {
                    $infor['sexo'] = "masculino";
                }
                elseif ($fetch_face['gender'] == "female")
                {
                    $infor['sexo'] = "feminino";
                }
                else
                {
                    $infor['sexo'] = "";
                }
            }

            if (isset($fetch_face['founded']))
                $infor['nasc'] = $fetch_face['founded'];

            if (isset($fetch_face['website']))
                $infor['site'] = $fetch_face['website'];

            if (isset($fetch_face['phone']))
                $infor['fone'] = $fetch_face['phone'];

            if (isset($fetch_face['location']['country']))
                $infor['pais'] = $fetch_face['location']['country'];

            if (isset($fetch_face['location']['city']))
                $infor['cidade'] = $fetch_face['location']['city'];

            if (isset($fetch_face['location']['street']))
                $infor['rua'] = $fetch_face['location']['street'];

            if (isset($fetch_face['location']['zip']))
                $infor['cp'] = $fetch_face['location']['zip'];
        }
        catch (Exception $exp)
        {
            throw new Exception($exp->getMessage(), 1);
        }

        return $infor;
    }

    /**
     * devolve dados para acesso a uma rede social
     *
     * @param string $network - identificador da rede na base de dados
     *
     * @throws Exception code 1
     *
     * @return string
     */
    private function network_data($network)
    {
        if (!$rslt = Logex::$tcnx->query("SELECT client_id,token FROM redes WHERE nome='$network'"))
            throw new Exception($this->exp_prefix . __LINE__, 1);

        if (!$t_face = $rslt->fetch_array())
            throw new Exception($this->exp_prefix . __LINE__, 1);

        $data['user_id'] = $t_face[0];
        $data['token']   = $t_face[1];

        return $data;
    }

    /**
     * faz uma conexão com uma rede social
     *
     * @param string $url ;
     * @param string $type - tipo de requisição POST, GET, ...
     * @param array $post_param - parametros da query numa requisição tipo POST
     *
     * @throws Exception code 1
     *
     * @return string
     */
    public function social_connect($url, $type = "GET", $post_param = NULL)
    {
        //inicia curl
        if (!$ch2 = curl_init())
            throw new Exception($this->exp_prefix . __LINE__, 1);

        //TODO validar a url
        //define a url
        curl_setopt($ch2, CURLOPT_URL, $url);

        if ($type === "POST")
        {
            if (is_array($post_param))
                $query_string = http_build_query($post_param);

            curl_setopt($ch2, CURLOPT_POST, 1);

            curl_setopt($ch2, CURLOPT_POSTFIELDS, $query_string);
        }

        //se o código de for >= 400 passa a resposta em texto
        curl_setopt($ch2, CURLOPT_FAILONERROR, 1);

        //passa o retorno como uma string para guardar numa variavel
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);

        //tempo de espera à espera de conexão
        curl_setopt($ch2, CURLOPT_TIMEOUT, 10);

        //não verifica o cerfificado SSL
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, 0);

        //não verifica nome comun no certificado SSL
        curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch2);

        if (curl_errno($ch2))
        {
            $mess = curl_error($ch2);
        }
        elseif (!$response)
        {
            $mess = "sem resposta";
        }
        else
        {
            $mess = $response;
        }

        curl_close($ch2);

        return $mess;
    }

}

class GestXML extends Core
{

    /**
     * cria e atualiza o sitemap do site
     */
    public function update_sitemap()
    {

        $pages = NULL;
        $url   = NULL;

        $sitemap = parent::json_file("JSITEMAP");

        foreach ($sitemap['pages'] as $sitepages)
        {

            foreach ($GLOBALS['LANGPAGES'] as $lang)
            {

                if (isset($sitepages[$lang]['page']) && $sitepages[$lang]['page'])
                {

                    $pages .= "
                               <url>
                                 <loc>" . _RURL . $sitepages[$lang]['page'] . "</loc>
                                 <changefreq>weekly</changefreq>
                               </url>";

                    if ($sitepages['table'])
                    {

                        $rsl = Logex::$tcnx->query("SELECT " . implode(",", $sitepages[$lang]['fields']) . " FROM " . $sitepages['table'] . " WHERE estado='online'");
                        while ($tl  = $rsl->fetch_array())
                        {

                            foreach ($sitepages[$lang]['fields'] as $value)
                            {

                                $ux = ($tl[$value]) ? $tl[$value] : $value;
                                $url .= "/" . $ux;
                            }

                            $pages .= "
                               <url>
                                 <loc>" . _RURL . $sitepages[$lang]['subpage'] . $url . "</loc>
                                 <changefreq>weekly</changefreq>
                               </url>
                        ";

                            $url = "";
                        }
                        $rsl->free_result();
                    }
                }
            }
        }

        $xm      = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
                           <urlset  xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
                                   xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\"
                                   xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">
                               <url>
                                  <loc>" . _RURL . "</loc>
                                  <changefreq>weekly</changefreq>
                               </url>
                               $pages
                               </urlset>

                ";
        $arquivo = fopen($sitemap['path'] . "Sitemap.xml", "w+b");
        fwrite($arquivo, $xm);
    }

}

/**
 * Esta classe faz a gestão das notas.
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 5.00.080615
 * @since 08/06/2015
 * @license Todos os direitos reservados
 */
class GestNotes extends Core
{

    /**
     * Código de erro da classe
     * @var string
     */
    private $exp_prefix = "GNT";
    private $n_dbcon;

    public function __construct()
    {
        parent::__construct();

        try
        {
            $this->n_dbcon = new PDO(_PDOM, _US, _PS);
            $this->n_dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $exc)
        {
            $this->n_dbcon = FALSE;
        }
    }

    /**
     * Guarda um nota na tabela de notas da base de dados
     *
     * @param string $type - uma categoria para a nota
     * @param string $content - conteudo da nota
     * @param string $title - titulo da nota
     *
     * @return int|NULL id da ultima inserção ou NULL no caso de insucesso
     */
    public function insert_note($type, $content, $title)
    {
        $params[] = $type;
        $params[] = $content;
        $params[] = $title;
        $params[] = $GLOBALS['NOW'];

        try
        {
            $result = parent::make_call("spInsertNote", $params);
        }
        catch (Exception $ex)
        {
            return NULL;
        }

        if (empty($result[0]['toke']))
            return NULL;

        return $result[0]['toke'];
    }

    /**
     * Cria conjunto de notas para apresentação
     *
     * @throws Exception
     *
     * @param string - $table nome da tabela
     * @param string $item_id - identificação do item
     *
     *
     * @return string HTML|json Caso existam notas retorna uma string html com as notas configuradas
     *                          ou caso não existam notas um objeto json a informar que não exitem notas.
     */
    public function show_notes($table, $item_id)
    {
        if (!parent::check())
            throw new Exception($this->exp_prefix . __LINE__);

        if (!$id = parent::id($item_id))
            throw new Exception($this->exp_prefix . __LINE__);

        $notes = NULL;

        if (!$this->n_dbcon)
            throw new Exception($this->exp_prefix . __LINE__);

        $query = "SELECT nota_id FROM " . $table . " WHERE item_id = ?";

        $sttm = $this->n_dbcon->prepare($query);

        $sttm->bindParam(1, $id, PDO::PARAM_INT);

        if (!$sttm->execute())
            throw new Exception($this->exp_prefix . __LINE__);

        $results = $sttm->fetchAll();

        if (!is_array($results))
            throw new Exception($this->exp_prefix . __LINE__);

        foreach ($results as $note)
        {
            $note = $this->make_notes($note[0]);

            if ($note)
                $notes .= $note;
        }

        $return = ($notes) ? $notes : '{"result":"Não existem notas"}';

        return $return;
    }

    /**
     * Regista uma nota na tabela de notas do no respectivo item.
     * A forma de inserção de uma nota permite a inserção em lote.
     * O parametro $item deve ser SQL válido.
     *
     * @param string $table - nome da table onde registar a nota
     * @param string $item - string SQL do tipo (id_nota, id_item),...,(id_nota, id_item)
     *
     * @return bollean
     *
     */
    public function register_note($table, $item)
    {
        $params[] = $table;
        $params[] = $item;

        try
        {
            $result = parent::make_call("spRegisterNote", $params);
        }
        catch (Exception $ex)
        {
            return FALSE;
        }


        if (empty($result[0]['toke']))
            return FALSE;

        return TRUE;
    }

    /**
     * Cria uma nota para apresentação
     *
     * @param string $notes - identificador único de uma nota
     *
     * @return null|string HTML Em caso de sucesso retorna um string de elemetos html que configurão um note ou NULL em caso de insucesso
     */
    public function make_notes($notes)
    {
        if (!parent::check())
            return NULL;

        if (!$this->n_dbcon)
            return NULL;

        $id = parent::id($notes);

        if (!$id)
            return NULL;

        $query = "SELECT data,titulo,conteudo FROM nota WHERE id_nota = ? ORDER BY data DESC";

        $sttm = $this->n_dbcon->prepare($query);

        $sttm->bindParam(1, $id, PDO::PARAM_INT);

        if (!$sttm->execute())
            throw new Exception($this->exp_prefix . __LINE__);

        $result = $sttm->fetch();

        if (!is_array($result))
            throw new Exception($this->exp_prefix . __LINE__);

        $date = new GDate();


        if (!empty($result['titulo']))
        {
            $date    = (empty($result['data'])) ? "" : $date->make_date($result['data'], 1, "DATE", 1, "-");
            $title   = $result['titulo'];
            $content = (isset($result['conteudo'])) ? nl2br($result['conteudo']) : "";

            return "<div class='dvBf'>
                   <p class='p150' data-action='note'>
                   <span>$date</span>
                   <span class='spm10'>$title</span>
                   </p>
                   <div class='dvBm'>
                   $content
                   </div>
                   </div>";
        }
        else
        {
            return NULL;
        }
    }

}

/**
 * Esta classe faz a gestão da ordenação de itens.
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 5.00.080615
 * @since 08/06/2015
 * @license Todos os direitos reservados
 */
class GestOrder extends Core
{

    /**
     * Nome da tabela da base de dados do modulo
     * @var string
     */
    private $table;

    /**
     * Nome da coluna da base de dados onde é guradado o indice de ordenação
     *
     * @var string
     */
    private $order_index;

    /**
     * Nome da columa da base de dados do indetificador unico do item no modulo
     *
     * @var string
     */
    private $toke;

    /**
     * Código de erro da classe
     * @var srting
     */
    private $exp_prefix = "GOR";

    /**
     *
     * @param type $config - objeto de configuração do modulo
     */
    public function __construct($config)
    {
        parent::__construct();

        $this->table       = $config['table'];
        $this->order_index = (!empty($config['admin']['public']['order']['db'])) ? $config['admin']['public']['order']['db'] : $config['admin']['private']['order'];
        $this->toke        = $config['admin']['private']['toke'];
    }

    /**
     * Cria um objecto json para iniciar o modulo de ordenacão
     *
     * @uses Logex::check()
     * @uses Core::mess_alert()
     * @uses Core::make_call()
     * @uses GestImage::send_images_json()
     *
     * @param string $procedure - Nome da stored procedure para ordenação
     * @param array $param - parametros da stored procedure
     *
     * @return json
     *
     */
    public function make_order($procedure, array $param = NULL)
    {
        if (!parent::check())
            parent::mess_alert($this->exp_prefix . __LINE__);

        if (!is_string($procedure))
            parent::mess_alert($this->exp_prefix . __LINE__);

        try
        {
            $result = parent::make_call($procedure, $param);
        }
        catch (Exception $ex)
        {
            return parent::mess_alert($ex->getMessage());
        }

        if (is_array($result))
        {
            $or = NULL;

            $images = new GestImage();

            foreach ($result as $od)
            {
                try
                {
                    $img  = (!empty($od['icon'])) ? '"' . $images->send_images_json($od['icon'], "src", NULL, 0, NULL) . '"' : '""';
                    $name = (!empty($od['nome_1'])) ? $od['nome_1'] : $od['nome_2'];
                    $or .= ',["' . $od['toke'] . '",' . $img . ',"' . $name . '"]';
                }
                catch (Exception $ex)
                {
                    return parent::mess_alert($ex->getMessage());
                }
            }

            return '{"result":[' . ltrim($or, ",") . ']}';
        }
        else
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }
    }

    /**
     * Guarda a nova ordenação
     *
     * @uses Logex::check()
     * @uses Core::mess_alert()
     * @uses Core::id()
     *
     * @return object Json
     */
    public function save_new_order()
    {

        if (!$this->check())
            parent::mess_alert($this->exp_prefix . __LINE__);

        $oindex = $_POST['iorder'];

        if (empty($oindex))
            parent::mess_alert($this->exp_prefix . __LINE__);

        if (!is_array($oindex))
            parent::mess_alert($this->exp_prefix . __LINE__);

        if (!is_string($this->toke))
            parent::mess_alert($this->exp_prefix . __LINE__);

        if (!is_string($this->table))
            parent::mess_alert($this->exp_prefix . __LINE__);

        if (!is_string($this->order_index))
            parent::mess_alert($this->exp_prefix . __LINE__);

        try
        {
            $dbcon = new PDO(_PDOM, _US, _PS);
        }
        catch (Exception $ex)
        {
            parent::mess_alert($ex->getMessage());
        }

        $query = "UPDATE $this->table  SET  $this->order_index =? WHERE $this->toke =?";


        $stttm = $dbcon->prepare($query);

        $io = array_reverse($oindex);
        $n  = count($io);

        for ($f = 0; $f < $n; $f++)
        {
            $id = parent::id($io[$f]);

            if ($id)
            {
                $stttm->execute(array($f, $id));
                $stttm->closeCursor();
            }
        }

        $dbcon = NULL;

        return '{"order":1}';
    }

}

/**
 * Esta classe faz a gestão da pesquisa nos modulos.
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 5.00.080615
 * @since 08/06/2015
 * @license Todos os direitos reservados
 */
class GestSearch extends Core
{

    private $search;
    private $table;

    public function __construct($config)
    {
        parent::__construct();

        $this->search = FALSE;

        if (!empty($config['search']))
        {
            $this->search = $config['search'];
            $this->table  = $config['table'];
        }
    }

    /**
     * Pesquisa termos e frases numa tabela da base de dados.
     *
     * @uses Logex::check(), Logex::$tcnx, Anti::validate_name(), Core::mess_alert()
     *
     * @return string json
     */
    public function search()
    {
        if (!parent::check())
            return parent::mess_alert("SCH" . __LINE__);

        $r     = NULL;
        $query = NULL;

        if (!is_array($this->search))
            return parent::mess_alert("SCH" . __LINE__);

        if (!is_array($this->search['query']))
            return parent::mess_alert("SCH" . __LINE__);

        foreach ($this->search ['query'] as $key => $value)
        {
            if (is_array($value))
            {
                $sub_query = NULL;

                foreach ($value as $field)
                {
                    if (!isset($_POST[$key]))
                        continue;

                    $post_value = $this->validate_name($_POST[$key]);

                    if (!$post_value)
                        continue;

                    if (!$sub_query)
                    {
                        $sub_query = " " . $field . " LIKE '%" . $post_value . "%'";
                    }
                    else
                    {
                        $sub_query .= " OR " . $field . " LIKE '%" . $post_value . "%'";
                    }
                }

                if ($sub_query)
                {
                    if ($query)
                    {
                        $query .= " AND (" . $sub_query . ") ";
                    }
                    else
                    {
                        $query = " (" . $sub_query . ") ";
                    }
                }
            }
            else
            {

                if (!empty($_POST[$key]))
                {
                    if ($query)
                    {
                        if ($_POST[$key] && $this->validate_name($_POST[$key]))
                            $query .= " AND " . $value . " LIKE '%" . $_POST[$key] . "%'";
                    } else
                    {
                        if ($_POST[$key] && $this->validate_name($_POST[$key]))
                            $query = " " . $value . " LIKE '%" . $_POST[$key] . "%'";
                    }
                }
            }
        }

        if ($query)
        {
            $order = (!empty($this->search['order'])) ? " ORDER BY " . $this->search['order'] . " ASC" : "";

            $query = "SELECT " . $this->search['toke'] . "," . $this->search['fields'] . " FROM " . $this->table . " WHERE " . $query . $order;

            $dbcon = new PDO(_PDOM, _US, _PS);
            $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $dbcon->query($query);

            $result = $stmt->fetchAll();

            foreach ($result as $cResg)
            {
                $r .= ',{"id":"' . $cResg[0] . '","fields":["' . $cResg[1] . '","' . $cResg[2] . '","' . $cResg[3] . '"]}';
            }

            $dbcon = NULL;

            return '{"result":[' . ltrim($r, ",") . ']}';
        }
        else
        {

            return '{"result":[]}';
        }
    }

}

/**
 * Esta classe faz a gestão da mensagens enviadas através do site.
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 5.00.080615
 * @since 08/06/2015
 * @license Todos os direitos reservados
 */
class GestMessages extends Core
{

    //objeto que gere as datas
    private $date;
    private $exp_prefix = "GMS";

    public function __construct()
    {
        parent::__construct();

        $this->date = new GDate();
    }

    /**
     * Retorna a mensagens de um item
     *
     * @param string $item_id - valor da chave primaria ou identificação do item
     * @param string $module_name - nome do modulo do item
     */
    public function get_messages($item_id, $module_name)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $id = $this->id($item_id);

        if (!$id || empty($module_name))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $condition = " mensagens.id_item=" . $id . " AND mensagens.filtro='" . $module_name . "'";

        if ($module_name === "contacts")
            $condition = " mensagens.id_contacto=" . $id;

        return $this->fetch_messages($condition);
    }

    private function fetch_messages($condition)
    {
        if ($condition)
            $cond = "WHERE " . $condition;

        $query = "
            SELECT mensagens.id,mensagens.data,mensagens.assunto,mensagens.flag,contactos.nome,contactos.mail,mensagens.pasta
            FROM contactos
            INNER JOIN mensagens
            ON mensagens.id_contacto=contactos.id
            $cond
            ORDER BY mensagens.data DESC";

        if (!$dbcon = new PDO(_PDOM, _US, _PS))
            return parent::mess_alert("GMS" . __LINE__);

        $message = NULL;

        $result = $dbcon->query($query);

        if ($result)
        {
            foreach ($result as $mess)
            {

                $mess_date = $this->date->make_date($mess[1]);

                $message .= html_entity_decode('["' . $mess[0] . '","' . $mess_date . '","' . $mess[2] . '","' . $mess[3] . '","' . $mess[4] . '","' . $mess[5] . '"],');
            }
        }


        return '{"result":[' . trim($message, ",") . ']}';
    }

    /**
     * Mostra as mensagens
     *
     * @param string $idx
     * @param string $filter
     *
     */
    public function show_messages($idx = NULL, $filter = NULL)
    {
        if (!parent::check())
            return parent::mess_alert("GMS" . __LINE__);

        $cond   = NULL;
        $mess   = NULL;
        $folder = NULL;
        $id     = parent::id($idx);

        $fields = array("subject" => "mensagens.assunto", "date" => "mensagens.data", "mail" => "contactos.mail", "name" => "contactos.nome");

        if (isset($_POST['pasta']))
        {
            $folder = parent::validate_name(urldecode($_POST['pasta']));
            $cond .= ($folder) ? " AND mensagens.pasta='$folder'" : "AND mensagens.pasta=''";
        }

        if (!empty($_POST['op']) && array_key_exists($_POST['op'], $fields))
        {
            $cond .= "AND  " . $fields[$_POST['op']] . " LIKE '" . $_POST['valor'] . "%'";
        }
        else
        {
            foreach ($fields as $key => $value)
            {
                if (!empty($_POST[$key]) && $this->validate_name($_POST[$key]))
                    $cond .= " AND " . $value . " LIKE '%" . $_POST[$key] . "%'";
            }
        }

        if ($id && $filter)
        {
            $cond .= ($filter === "contacts") ? " AND mensagens.id_contacto=" . $id : " AND mensagens.id_item=" . $id . " AND mensagens.filtro='" . $filter . "'";
        }

        if ($cond)
        {
            $cond = trim($cond);
            $cond = "WHERE " . substr($cond, strpos($cond, "AND") + 3);
        }

        $query = "SELECT mensagens.id as 'toke',mensagens.data as 'data',mensagens.assunto as 'assunto',mensagens.flag as 'flag' ,contactos.nome as 'nome' ,contactos.mail as 'mail',mensagens.pasta as 'pasta' FROM contactos LEFT JOIN mensagens ON contactos.id=mensagens.id_contacto $cond  ORDER BY mensagens.data DESC";

        $dbcon = new PDO(_PDOM, _US, _PS);

        foreach ($dbcon->query($query) as $message)
        {
            $mess .= html_entity_decode('["' . $message['toke'] . '","' . $this->date->make_date($message['data']) . '","' . $message['assunto'] . '","' . $message['flag'] . '","' . $message['nome'] . '","' . $message['mail'] . '"],');
        }

        return '{"result":[' . trim($mess, ",") . ']}';
    }

    /**
     * Apaga uma mensagem
     *
     * @param string $id_message - identificador unico da mensagem
     *
     * @return type
     */
    public function del_message($id_message)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = $this->id($id_message))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        try
        {
            $result = parent::make_call("spDeleteMessage", array($id));
        }
        catch (Exception $ex)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        if (empty($result[0]['ret']))
            return parent::mess_alert($this->exp_prefix . __LINE__);


        $ret = json_decode($result[0]['ret'], TRUE);

        if (!empty($ret))
        {
            if (isset($ret['mgp_error']))
                return parent::mess_alert($this->exp_prefix . __LINE__);

            if (!isset($ret['id']))
                return parent::mess_alert($this->exp_prefix . __LINE__);

            $rid = parent::id($ret['id']);

            if (!$rid)
                return parent::mess_alert($this->exp_prefix . __LINE__);

            return '{"result":' . $rid . '}';
        }
        else
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }
    }

    /**
     * Envia uma mensagem para leitura
     *
     */
    public function read_message()
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $message = NULL;
        $nome    = NULL;
        $mail    = NULL;
        $assunto = NULL;
        $texto   = NULL;
        $data    = NULL;
        $anexo   = NULL;

        $id = $this->id($_POST['toke']);

        if ($id)
        {
            try
            {
                $rows = parent::make_call("spReadMessage", array($id));

                if (isset($rows[0]))
                    $message = $rows[0];
            }
            catch (Exception $exp)
            {

            }
        }

        if ($message)
        {
            $nome    = $message['nome'] . " " . $message['apelido'];
            $mail    = $message['mail'];
            $assunto = $message['assunto'];
            $texto   = nl2br($message['texto']);
            $data    = $this->date->make_date($message['data']);
            $anexo   = $message['anexo'];
        }

        $mensagem = '{"result":["' . $id . '","' . $nome . '","' . $mail . '","' . $assunto . '","' . $texto . '","' . $data . '","' . $anexo . '","' . _ANEXOSURL . '"]}';

        return html_entity_decode($mensagem);
    }

}

class ElementList extends Core
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Cria uma linha com os dados de um campo para apresentar na ficha de apresentação de um item
     *
     * @param array $obj_config - array com o nome do campo da base da dados[db], tipo de linha["data-type"], e nome[name]
     * @param array $db_result - resultado da pesquisa na base de dados
     *
     * @return string - estrutura HTML com uma linha
     *
     */
    protected function make_sheet_list(array $obj_config, array $db_result)
    {

        $query = "SELECT FROM WHERE =";

        Logex::$tcnx->query($query);

        $short = ($line_config["type"][0] == "S") ? "slf" : "";

        if (parent::validateEmail($db_result[$line_config['db']]))
        {
            $con = "<a href='mailto:" . $db_result[$line_config['db']] . "'>" . $db_result[$line_config['db']] . "</a>";
        }

        #regex retirada de https://gist.github.com/dperini/729294
        elseif ((preg_match("%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu", $db_result[$line_config['db']])))
        {
            $att = 'data-action="anchor" style="cursor:pointer;" data-target="' . $db_result[$line_config['db']] . '"';
            $con = $db_result[$line_config['db']];
        }
        else
        {
            $con = $db_result[$line_config['db']];
        }

        return "
                            <div class='linefile $short'>
                                <span class='linefiletitle'>
                                    " . $line_config['name'] . ":
                                </span>
                                <p class='linefiletext' $att>
                                    " . $con . "
                                </p>
                            </div>";
    }

    /**
     * Cria campos de introdução de dados com elmentos html input type text, textarea, div editable
     *
     * @param array $OB - array com a configuração do campo
     * @param string $DB - valor do campo na base de dados
     * @param string $NM - nome do elemento html;
     *
     * @return string - elemento html
     */
    public function make_list(array $conf_obj, $db_value, $element_name)
    {

    }

}

class GestTopics extends Core
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Cria os tópicos. Cada tópico é constituido por um conjunto composto por suas div editáveis
     *
     * @param array $config - objeto de configuração do topico
     * @param string $database - valor guardado na base de dados
     * @param string $name - nome dos campos
     * @param string $type - define se o pedido é para a ficha de ediçao(edit) ou de vizualização(sheet)
     *
     * @return string HTML
     *
     */
    public function make_topics(array $config, $database, $name = NULL, $type = "edit")
    {
        $n_topics = (isset($config["quantity"])) ? $config["quantity"] : 6;

        $db_topic = json_decode($database, TRUE);

        $topics = NULL;
        $title  = NULL;
        $text   = NULL;

        for ($c = 1; $c <= $n_topics; $c++)
        {
            if (is_array($db_topic))
            {
                $title = (isset($db_topic["topic_" . $c]["title"])) ? $db_topic["topic_" . $c]["title"] : "";
                $text  = (isset($db_topic["topic_" . $c]["text"])) ? $db_topic["topic_" . $c]["text"] : "";
            }

            if ($type == "edit")
            {
                $topics .= "<div class='topic_title' contenteditable=true id='" . $name . "_tt_" . $c . "' >" . $title . "</div><div class='topic_text' contenteditable=true id= '" . $name . "_tx_" . $c . "'>" . $text . "</div>";
            }

            if ($type == "sheet")
            {
                if ($title)
                    $topics .= "<div class='sheet_topic_title'>" . $title . "</div>";
                if ($text)
                    $topics .= "<div class='sheet_topic_text' >" . $text . "</div>";
            }
        }

        return $topics;
    }

    /**
     * cria objeto json para guardar os topicos na base de dados
     *
     * @return string json
     *
     */
    public function save_topics($name)
    {
        $counter = 1;
        $Ar      = $_POST;

        foreach ($Ar as $value)
        {
            if (isset($Ar[$name . "_tt_" . $counter]) || isset($Ar[$name . "_tx_" . $counter]))
            {
                $topics["topic_" . $counter]["title"] = parent::text_clean_json($Ar[$name . "_tt_" . $counter]);
                $topics["topic_" . $counter]["text"]  = parent::text_clean_json($Ar[$name . "_tx_" . $counter]);

                unset($Ar[$name . "_tt_" . $counter]);
                unset($Ar[$name . "_tx_" . $counter]);

                $counter++;
            }
        }

        return json_encode($topics, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }

}
