<?php

/*******************************************************
 SCRIPT Core.php V7.5
 inicio 11-06-2014
 COPYRIGHT MANUEL GERARDO PEREIRA 2014
 TODOS OS DIREITOS RESERVADOS
 CONTACTO: GPEREIRA@MGPDINAMIC.COM
 WWW.MGPDINAMIC.COM

 ultima alteração : 11-06-2014
 *****************************************************/

ini_set('display_errors', 1);
ini_set("default_charset", "UTF-8");

require 'logex.php';

class Core extends logex
{

    function __construct()
    {

        parent::__construct();
    }

    /*
     * limpa  $_SERVER['REQUEST_URI'] para o atributo action do formulario
     */

    public function form_action()
    {

        $r = explode("/", $_SERVER['REQUEST_URI']);
        $re = $r[count($r) - 1];
        return ltrim($re, " /");
    }

    public function json_file($JS=FALSE)
    {

        if (!$obj = file_get_contents(_MFILE . 'Master.json'))
            trigger_error("Impossivel configuarar json. CORE".__LINE__,E_USER_ERROR);
        
        if(!$r_obj = json_decode($obj, TRUE))
            trigger_error("Impossivel configuarar json. CORE".__LINE__,E_USER_ERROR);
        
        if(!$JS)
           return $r_obj;
            
        if(isset($r_obj[$JS]))
            return $r_obj[$JS];
        
        trigger_error("Impossivel configuarar json. CORE".__LINE__,E_USER_ERROR);

        
    }
    /**
     * valida um inteiro por callback
     * 
     * @param $value - qualquer valor para validar se é inteiro
     * 
     * @return boolean
     * 
     */
    static function validate_int($value){
        
        return filter_var($value, FILTER_VALIDATE_INT,array("options"=>array("min_range"=>1)));
    }
    /*
     * limpa o id
     */

    public function id($I)
    {
        if ($I)
        {
            $id = explode(":", $I);
            $id = (isset($id[1])) ? filter_var($id[1], FILTER_VALIDATE_INT) : filter_var($id[0], FILTER_VALIDATE_INT);

            return $id;

        }
        else
        {

            return FALSE;
        }
    }

    /*
     * substitui o espaço por um traço
     */
    public function clean_space($T)
    {

        return str_replace(" ", "-", $T);
    }

    /**
     * Cria uma mensagem de erro
     *
     * @param string $code - identificação do erro
     * @param int $mess - messagem a ser enviada
     *
     * @return string - json
     */
    public function mess_alert($code, $mess = 1)
    {

        switch ($mess) {
            case 1 :
                return '{"alert":"Não foi possivel realizar esta operação. - ' . $code . '"}';
                break;

            default :
                return '{"alert":"Não foi possivel realizar esta operação. - ' . $code . '"}';
                break;
        }

    }

    /*
     * transforma id na forma id:xxx
     * numa em uma string
     *
     */

    public function makeIdArray($F)
    {

        $fts = "";
        $n = count($F);
        for ($i = 0; $i < $n; $i++)
        {
            $R = explode("i:", $F[$i]);
            if ($F[$i])
            {
                $fts .= ",$R[1]";
            }
        }
        $fts = ltrim($fts, ",");
        return $fts;
    }

    /**
     * Cria uma senha
     *
     * @return string - objeto json
     *
     */
    public function make_pass()
    {

        $sen1 = "1234567890abcdefghijlmnopqrstuvxzABCDEFGHIJLMNOPQRSTUVXZYKyk@$&-_." . md5(_NAMESITE);
        $comp = strlen($sen1) - 1;
        $sen2 = str_split($sen1, 1);
        $senha2 = $sen2[rand(1, $comp)] . $sen2[rand(1, $comp)] . $sen2[rand(1, 9)] . $sen2[rand(1, $comp)] . $sen2[rand(1, $comp)] . $sen2[rand(1, $comp)];

        $senha1 = substr(_NAMESITE, 0, 2);
        $senha = strtoupper($senha1) . $senha2;

        return '{"senha":"' . $senha . '"}';
    }

    /**
     * Transforma uma array em uma string com as chaves e o valores separados por virgulas. Elimina os valores duplicados
     *
     * @param array $arr2str
     *
     * @return string
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

    /////////////////////METODOS PARA TEXTO////////////////////////////////////////////////////////////

    /*
     * limita o comprimento de um texto
     * $S=texto
     * $C=comprimento desejado
     */
    public function cut_str($S, $C)
    {
        if (strlen($S) > $C)
        {
            $str = str_split($S, ($C - 3));
            return $str[0] . "...";
        }
        else
        {
            return $S;
        }
    }

    /*
     * limita o comprimento de um texto e retira-lhe a formataçao
     * $T=texto
     * $L=comprimento desejado
     * $F=true para manter a formatação
     */

    public function cut_text($T, $L, $F = false)
    {

        if ($T)
        {
            if ($F)
            {

                $conte = $T;
            }
            else
            {

                $conte = strip_tags($T);
                $conte = preg_replace('/\x5c/', ' ', $conte);
            }

            $len = strlen($conte);

            if ($len > ($L - 1))
            {
                $var = $conte;
                $i = $L;
                $spacer = substr($var, $i, 1);
                while ($spacer != " ")
                {
                    $spacer = substr($var, $i, 1);
                    if ($spacer == " ")
                    {
                        break;
                    }
                    $i++;
                }

                $cont = substr($var, 0, $i) . "...";
            }
            else
            {
                $cont = $conte;
            }

            return $cont;
        }
    }

    /*
     * limpa um texto dos carcteres que não podem ser utilizados em JSON
     * $T = texto
     */

    protected static function text_clean_json($T)
    {

        $ct = preg_replace_callback('/<[^>]*>/', create_function('$matches', 'return str_replace("\"","\'",$matches[0]);'), $T);
        $ct = str_replace('"', "&#34", $ct);
        $ct = preg_replace_callback('/>[^><]*</', create_function('$matches', 'return str_replace("\\\", "",$matches[0]);'), $ct);

        return $ct;
    }

    /*
     * reverte a limpeza de um texto dos carcteres que nÃ£o podem ser utilizados em JSON
     * $T = texto
     */

    public function text_clean_json_reverse($T)
    {

        $ct = preg_replace_callback('/<[^>]*>/', create_function('$matches', 'return str_replace("\'","\"",$matches[0]);'), $T);
        $ct = str_replace("&#34", '"', $ct);
        $ct = preg_replace_callback('/>[^><]*</', create_function('$matches', 'return str_replace("\\\", "",$matches[0]);'), $ct);
        $ct = html_entity_decode($ct, ENT_QUOTES, "UTF-8");
        return $ct;
    }

    protected function make_title($JS, $DB)
    {

        $title = NULL;

        $db = explode(",", $JS['db']);

        if (is_array($db))
        {

            foreach ($db as $value)
            {
                if (isset($DB[$value]))
                    $title .= $DB[$value] . " ";

            }

        }
        else
        {

            $title = $DB[$db['db']];
        }

        return $title;
    }

    /////////////////////////////METODOS MANIPULAÇÂO SQL///////////////////////////////////////////////
    /*
     * cria string com condiÃ§Ãµes de pesquisa sql
     * C = array associativo com as condiÃ§Ãµes
     */
    public function sql_conditions($C)
    {

        $n = 0;
        $cond = NULL;

        foreach ($C as $key => $value)
        {

            if (!$n)
            {

                $cond .= "WHERE " . $key . "='" . $value . "'";
            }
            else
            {

                $cond .= " AND " . $key . "='" . $value . "'";
                $n = 1;
            }
        }

        return $cond;
    }

    //////////////////////////////METODOS VIDEO////////////////////////////////////////////////////////

    ////////////////////////////////////////////METODOS ENCRIPTAÇÃO////////////////////////////////////////
    public function mgpencrypt($text, $cipher = MCRYPT_BLOWFISH, $mode = MCRYPT_MODE_OFB)
    {

        if (extension_loaded("mcrypt"))
        {

            $td = mcrypt_module_open($cipher, '', $mode, '');

            if ($td)
            {

                $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);

                $key = substr(md5(sha1(_SOMEK)), 0, mcrypt_enc_get_key_size($td));

                mcrypt_generic_init($td, $key, $iv);

                $enc = mcrypt_generic($td, $text);

                mcrypt_generic_deinit($td);
                mcrypt_module_close($td);

                return base64_encode($iv . $enc);

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

    public function mgpdecrypt($text, $key, $cipher = "blowfish", $mode = "ofb")
    {

        if (extension_loaded("mcrypt"))
        {

            $td = mcrypt_module_open($cipher, "", $mode, "");

            $iv_size = mcrypt_enc_get_iv_size($td);

            $dtext = base64_decode($text);

            $iv = substr($dtext, 0, $iv_size);

            $mkey = substr(md5($key), 0, mcrypt_enc_get_key_size($td));

            mcrypt_generic_init($td, $mkey, $iv);

            $dec = mcrypt_generic($td, substr($dtext, $iv_size));

            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);

            return $dec;

        }
        else
        {

            return FALSE;
        }
    }

    //////////////////////////////////METODOS PARA PASTAS//////////////////////////////////////////////

    ///////////////////////////METODOS MENSAGENS///////////////////////////////////////////////////////

    ///////////////////////////METODOS IMAGENS////////////////////////////////////////////////////////

    //////////////////////////////////METODOS DE ORDENAÇÃO/////////////////////////////////////////////

    //////////////////////////////////METODOS DE PROCURA///////////////////////////////////////////////

    //////////////////////////////////GERAÇÃO HTML/////////////////////////////////////////////////////

    /**
     *
     * @param array $C
     * @return string
     *
     * Cria um bloco de dados para inserir numa ficha de edição ou adição de um item
     * esses blocos são definidos nos objeto json de configuração dos modulo e podem
     * dividir o modulo em dados de gestão, dados comuns, dados particulares da cada idioma.
     *
     * $C : array que pode ter os seguintes elementos:
     *  [DB] = resultdo da pesquisa na base de dados
     *  [JS] = bloco do objeto json de configuração que agrupa parte dos dados do modulo
     *  [ICON] = icon do bloco de dados
     *  [SUFIX] = sufixo do nome e id das tag html - normalmente usado para idiomas
     *  [CSS] = estilos da camada que embrulha o elemento html
     */
    protected function comun_fields_divEVO($C, $M)
    {

        if (self::check())
        {

            switch ($M) {

                case "ADD" :
                    $mcss = "sectitG";
                    break;
                case "UPDATE" :
                    $mcss = "sectitY";
                    break;
                case "CLONE" :
                    $mcss = "";
                    break;
            }

            $div = NULL;

            foreach ($C as $arr)
            {

                #define o sufixo
                $sufix = (isset($arr['SUFIX'])) ? $arr['SUFIX'] : "";

                # define icon do conjunto
                $icon = (isset($arr["ICON"])) ? $this -> make_html_img($arr["ICON"], "ig10M") : NULL;

                $tit = NULL;

                if (!empty($arr["JS"]['name']))
                    $tit = '
                        <p class="' . $mcss . '">
                            <span class="sp15b">' . $icon . '  ' . $arr["JS"]['name'] . '</span>
                            <img src="../imagens/folderon.png" class="igop" data-type="hideshow">
                        </p>';

                #estilos da div que embrulha o elemento html
                $css = (!empty($arr["CSS"]) && !empty($arr["CSS"])) ? $arr["CSS"] : 'class="edit_label"';

                foreach ($arr["JS"] as $key => $value)
                {

                    #cria os elementos de cada conjunto
                    if (!empty($value['type']))
                    {

                        $name = $key . $sufix;

                        $inlabel = (!empty($value['label_inside'])) ? $value['label_inside'] : NULL;

                        if (!is_array($value['db']))
                            $dbvalue = (isset($arr['DB'][$value['db']])) ? $arr['DB'][$value['db']] : "";

                        $mainCSS = $this -> set_attr($css, $value['att_outside']);

                        $f = NULL;

                        switch ($value['type']) {

                            case "DATALIST" :
                                $f = $this -> data_list($dbvalue, $value['options'], $name, $value['target']);
                                break;
                            case "SELECT" :
                                $f = $this -> set_select($name, $dbvalue, $value);
                                break;
                            case "INPUT" :
                                $f = $this -> set_input($name, $dbvalue, $value);
                                break;
                            case "PARAG" :
                                $f = '<p ' . $this -> set_attr(' class="tx75px22xLFF" ', $value['att_inside']) . '>' . $dbvalue . '</p>';
                                break;
                            case "DIV" :
                                $f = '<div id="' . $name . '" ' . $this -> set_attr(' class="tx75px22xLFF" ', $value['att_inside']) . '>' . $dbvalue . '</div>';
                                break;
                            case "TEXTAREA" :
                                $f = '<textarea name="' . $name . '" ' . $this -> set_attr(' class="txa" ', $value['att_inside']) . '>' . $dbvalue . '</textarea>';
                                break;
                            case "TOPICS" :
                                $f = $this -> set_topics($value, $arr['DB']);
                                break;
                            case "RADIOB" :
                                $f = $this -> set_radiob($value, $dbvalue, $name);
                                break;
                            case "CHECKB" :
                                $f = $this -> set_checkb($value, $dbvalue, $name);
                                break;
                            default :
                                $f = '';
                                break;
                        }

                        if ($f)
                        {
                            $div .= '                                    
                                    <div ' . $mainCSS . '>
                                        <label>' . $icon . ' ' . $value['name'] . $inlabel . '</label>' . $f . '
                                    </div>';
                            $f = "";
                        }
                    }
                }
            }

            return $tit . "<div class='hideshow'> $div </div>";
        }
    }

    /**
     *
     * cria os atributos html do objecto.
     *
     * @param type $FV
     * @param type $SV
     * @return string
     */
    private function set_attr($FV, $SV)
    {

        $ret = (preg_match("#class=['\"]{1}([^'\"](.*)[^'\"])['\"]{1}#i", $SV)) ? $SV : $FV . ' ' . $SV;

        return $ret;
    }

    /*
     * @param array $jsn objeto json
     * @param array $db_row resultado da pesquisa na base de dados
     */
    private function set_topics($jsn, $db_row)
    {

        foreach ($jsn['db'] as $topic => $topic_text)
        {

            $title_attibutes = $this -> set_attr(' class="txa_topic" ', $jsn['att_inside']);
            $text_attributes = $this -> set_attr(' class="diveditable_topic" ', $jsn['att_inside']);

            $title = (isset($db_row[$topic])) ? $db_row[$topic] : "";
            $text = (isset($db_row[$topic_text])) ? $db_row[$topic_text] : "";

            $f .= '
                <textarea name="' . $topic . '" ' . $title_attibutes . ' rows="4">' . $title . '</textarea>
                <div id="' . $topic_text . '" ' . $text_attributes . ' contentEditable="true">' . $text . '</div>
            ';

            $title = NULL;
            $text = NULL;
        }

        return $f;
    }

    /**
     *
     * @param type $CT
     * @param type $T
     * @return string
     *
     * cria dados da ficha  individual
     */
    protected function file_data($CT, $T = NULL)
    {
        if (self::check())
        {
            $tr = NULL;
            $flds = array();

            foreach ($CT as $arr)
            {

                foreach ($arr[0] as $value)
                {

                    if (is_array($value) && isset($value['db']))
                    {
                        if (filter_var($arr[1][$value['db']], FILTER_VALIDATE_EMAIL))
                        {
                            $f = '<a href="mailto:' . $arr[1][$value['db']] . '">' . $arr[1][$value['db']] . '</a>';
                        }
                        elseif (filter_var($arr[1][$value['db']], FILTER_VALIDATE_URL))
                        {
                            $f = '<span data-action="anchor" data-target="' . $arr[1][$value['db']] . '" >' . $arr[1][$value['db']] . '</span>';
                        }
                        else
                        {
                            $f = $arr[1][$value['db']];
                        }

                        $mns[] = $value['name'];
                        $flds[] = $f;
                    }
                }

                $half = ceil(count($mns) / 2);

                for ($x = 0; $x < $half; $x++)
                {

                    $tr .= '
			
			<tr>
			        <td class="td20r">' . $mns[$x] . ':</td>
			        <td class="td30l">' . $flds[$x] . '</td>
			        <td class="td20r">' . $mns[$x + $half] . ':</td>
			        <td class="td30l" >' . $flds[$x + $half] . '</td>
		        </tr>
		
		';
                }
                unset($mns);
                unset($flds);
            }

            return '
	
						<div class="dv800px150x">		
							<p class="p15">
					        	' . $T . '
					        </p>	        
					        <table>	
						       ' . $tr . '</table>    	
				    	</div>';
        }
    }

    ////////////////////////////METODOS REDES SOCIAIS//////////////////////////////////////////////////////////////

    /**
     *
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
        if ($this -> check())
        {

            $t = NULL;

            //cria o conteudo da noticia, junta texto, fotos e video numa string html.
            switch ($C) {

                case "news1" :
                    $t = "<div class='news1'>$F $D</div>";
                    break;
                case "news2" :
                    $t = "<div class='news2'>$F $D</div>";
                    break;
                case "news5" :
                    $t = "<div class='news5'>$F</div>$D";
                    break;
                case "news3" :
                    $t = "<div class='news3'>$F</div><div class='news3_1'>$D</div>";
                    break;
                case "news4" :
                    $t1 = "<div class='news4'>$F</div><div class='news4_1'>$D</div>";
                    break;
                case "news6" :
                    $t = "$D<div class='news5'>$F</div>";
                    break;
                default :
                    $t = "<div class='news5'>$F</div>$D";
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
    }

    /**
     * cria e atualiza o sitemap do site
     */
    public function update_sitemap()
    {

        $pages = NULL;
        $url = NULL;

        $sitemap = $this -> json_file("JSITEMAP");

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

                        $rsl = logex::$tcnx -> query("SELECT " . implode(",", $sitepages[$lang]['fields']) . " FROM " . $sitepages['table'] . " WHERE estado='online'");
                        while ($tl = $rsl -> fetch_array())
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

        $xm = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
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

    ////////////////////////////METODOS PARA NOTAS/////////////////////////////////////////////////////

    ////////////////METODOS PARA SELECT OPTIONS////////////////////////////////////////////////////////

    /*
     * calcula o preÃ§o final de um produto
     * valor = preÃ§o do produto
     * iva = id do iva
     * retorna o preÃ§o como uma string
     */

    public function coreCalcPrice($valor, $iva, $tab_imp)
    {
        $biva2 = "SELECT * FROM $tab_imp WHERE id='$iva'";
        $bivaq2 = mysql_query($biva2);
        $bivar2 = mysql_fetch_array($bivaq2);
        $preo = $valor * (1 + ($bivar2['valor'] / 100));
        $preo = number_format($preo, 2, ',', ' ') . "â‚¬";

        return $preo;
    }

    /*
     * cria os dados de gestÃ£o
     * $I=id do item
     * $P=pasta do item
     * $D=data de criaÃ§Ã£o do item
     * $DA=data de actalizaÃ§Ã£o do item
     */

    public function managementDataSimple($I, $P, $D, $DA)
    {

        //se existir ID estamos no modo de ediÃ§Ã£o
        $id = ($I) ? "i:$I" : $id = "";

        $data = ($D) ? $D : $this -> date;

        return "
    <div id='dados_gestao' class='dv830xC'>
    <div class='dv95pL00'><span class='sp15b'>Dados de gestÃ£o</span></div>
    <table class='tb810pC'>
        <tr>
            <td class='td270px30x11FF'>
                <p class='p10'>pasta</p>
                <input type='text' value='$P' id='pasta' name='pasta' class='tx95px22xFFLRC'/>
                <input type='hidden' value='$id' name='id' id='identidade'>
            </td>
            <td class='td270px30x11FF'>
                <p class='p10'>inserido em</p>
                <p class='p95RC'>$data</p>
            </td>
            <td class='td270px30x11FF'>
                <p class='p10'>actulalizado em</p>
                <p class='p95RC'>$DA</p>
            </td>
        </tr>
        
    </table>
    </div>";
    }

    /*
     * substitui as aspas simples por um valor hexa em uma string
     */

    public function rplc($stri)
    {
        $s = str_replace("%", "&#037;", $stri);
        return str_replace("'", "&#039;", $s);
    }

    public function searchInArray($k, $t, $modulos)
    {

        $cp = NULL;

        preg_match_all("/($k\d+)/", $modulos, $cp, PREG_PATTERN_ORDER);
        for ($d = 0; $d < count($cp[0]); $d++)
        {
            $tc = explode($k, $cp[0][$d]);

            $lin = mysql_query("SELECT notas FROM $t WHERE id='$linke'");
            $lin2 = mysql_fetch_array($lin);

            if ($lin2[0] == "")
            {

                $c = "UPDATE $t SET notas = $selMail WHERE id='$linke'";
                $c1 = mysql_query($c);

                if ($c1)
                {
                    $sended = "$selMail";
                }
            }
            else
            {
                $mails = $lin2[0] . "," . $selMail;
                $d = "UPDATE newsletter SET send_to = '$mails' WHERE id='$linke'";
                $d1 = mysql_query($d);
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

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /*
     *
     * $T = tabela
     * $C1 = campos db
     */

    protected function optionsDis($T, $C1)
    {

        $objx = NULL;
        $lv = NULL;

        foreach ($C1 as $obj => $arr)
        {

            foreach ($arr as $key => $value)
            {

                $objx = NULL;
                $qa = logex::$tcnx -> query("SELECT DISTINCT $key FROM $T WHERE $key<>''");

                while ($pq = $qa -> fetch_array())
                {

                    $options = NULL;
                    $bQuery = logex::$tcnx -> query("SELECT DISTINCT $value FROM $T WHERE  $key='$pq[0]' AND $value<>''");

                    while ($cQuery = $bQuery -> fetch_array())
                    {

                        $options .= ',"' . $cQuery[0] . '"';
                    }

                    $options = ltrim($options, ",");

                    $objx .= "\"$pq[0]\":[" . $options . "],";
                }
            }

            $lv .= '"' . $obj . '":{' . rtrim($objx, ",") . '},';
        }
        return '{"result":{' . rtrim($lv, ",") . '}}';
    }

}

/**
 *
 */
class ItemSheet extends Core
{

    private $image;
    private $video;

    function __construct()
    {

        parent::__construct();

        $this -> image = new GestImage;
        $this -> video = new GestVideo;

    }

    /**
     * Cria a ficha de apresentação de um item e já faz a pesquisa na base de dados
     *
     * @param string $IDE - id do item
     * @param array $JS - array de configuração do item
     *
     * @return sring - estrutura HTML com a ficha de apresentação do item
     *
     */
    public function make_all_sheet($IDE, array $JS)
    {

        if (!parent::check())
            return '{"alert":"Não tem permissão para realizar esta operação. CODE:E003"}';

        $id = $this -> validate_file_input($IDE);

        if (!$id)
            return '{"alert":"Não foi possivel realizar esta operação. CODE:MF002"}';

        #PROCURA DADOS NA TABELA CONTACTOS

        $idx = $JS['admin']['private']['toke']['db'];

        $RF = logex::$tcnx -> query("SELECT * FROM " . $JS['table'] . " WHERE $idx=$id LIMIT 1");
        $CT = $RF -> fetch_array();

        $RF -> free();

        $adm_block = $this -> make_sheet($JS, $CT);

        return $adm_block;
    }

    /**
     * Cria a ficha, mas o resultado da pesquisa na base de dados tem que ser fornecido
     * Se o $content for fornecido não é criado nenhum conteúdo para a ficha , apenas os
     * dados administrativo são fornecidos.
     *
     * @param array $JS - objeto json de configuração no modulo
     * @param array $DB - resultado da pesquisa na base de dados
     * @param $content - string html com o conteudo da ficha (caso haja necessidade de manipular os dados)
     *
     * @return sring - estrutura HTML com a ficha de apresentação do item
     *
     */

    public function make_sheet(array $JS, array $DB, $content = NULL)
    {

        if (!parent::check())
            return '{"alert":"Não tem permissão para realizar esta operação. CODE:E003"}';

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
        {

            return FALSE;
        }

        extract($JS['admin']['private'], EXTR_PREFIX_ALL, "adm");

        $id = parent::id($DB[$adm_toke['db']]);

        if (!$id)
            return FALSE;

        $item_status = "offline";

        #class css do indicador do nome e do estado do item. Por defeito está offline
        $back_color = "backred";

        $links = NULL;

        $p_data = $DB;

        if (isset($adm_status) && (isset($p_data[$adm_status['db']]) && $p_data[$adm_status['db']] == "online"))
        {

            $item_status = $p_data[$adm_status['db']];

            $back_color = "backgreen";

            if (!empty($JS['link']))
                $links = $this -> make_links($JS['link'], $p_data);

        }

        $sub_menu = NULL;

        if (isset($JS['submenu']))
        {

            $sub_menu = $this -> make_sub_menu($JS['submenu']);
        }

        $title = $this -> make_title($adm_identifier, $p_data);

        $folder = (!empty($p_data[$adm_folder['db']])) ? $p_data[$adm_folder['db']] : "";

        #imagen do quadrado do top da ficha
        @$icon = $this -> make_sheet_icon($p_data[$adm_icon['db']]);

        $adm = $this -> make_sheet_adm_block($JS['admin']['public'], $p_data, $links);

        if (!$content)
        {

            if (isset($JS['fields']))
                $content = $this -> make_sheet_content($JS['fields'], $p_data);

            if (isset($JS['midia']))
                $content .= $this -> make_sheet_content($JS['midia'], $p_data);
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
                        $icon
                        $adm
                        $content
                        </div>
                        <div class='rodape'></div>
                    </div>";
        }
        else
        {

            return $icon . $adm . $content;
        }

    }

    /**
     * Cria os links para vizualização do item
     *
     * @param array $link_config -objecto que define os links
     * @param array $DB - resultado da pesquisa na base de dados
     *
     * @return string HTML
     *
     */
    public function make_links(array $link_config, array $db_result)
    {

        $links = NULL;

        if (!is_array($link_config) || !is_array($db_result))
            return FALSE;

        foreach ($GLOBALS['LANGPAGES'] as $value)
        {

            $params = NULL;

            if (!empty($link_config[$value]['param']))
            {
                foreach ($link_config[$value]['param'] as $par)
                {
                    if ($db_result[$par])
                        $params .= "/" . $db_result[$par];
                }
            }

            $links .= "
            <p class='p5'>
                <span class='sp12FFM'>
                    link:
                </span>
                <span style='-webkit-user-select:text'>
                    <span data-action='anchor' data-target='" . $link_config[$value]['url'] . $params . "' >
                        " . $link_config[$value]['url'] . $params . "
                    </span>
                </span>
            </p>";
        }

        return $links;
    }

    /**
     * Cria o submenu da ficha
     *
     * @param array $submenu_config - array de configuração do submenu
     *
     * @return string - estrura HTML do submenu
     *
     */
    private function make_sub_menu(array $submenu_config)
    {

        if (!is_array($submenu_config))
            return FALSE;

        $sub_menu = NULL;

        foreach ($submenu_config as $value)
        {

            if (!$sub_menu)
            {

                $sub_menu = '<li class="lisubmenu" id="' . strtolower($value) . '">' . $value . '</li>';

            }
            else
            {

                $sub_menu .= '<li class="lisubmenusel" id="' . strtolower($value) . '">' . $value . '</li>';
            }

        }

        return $sub_menu;
    }

    /**
     * Cria a imagem do topo da ficha (imagem de apresentação do item)
     *
     * @param json $db_image - resultado da pesquisa do campo de imagens na base de dados
     *
     * @return string - estrutura HTML com a imagem.
     */
    private function make_sheet_icon($db_image = NULL)
    {

        $p_icon = NULL;

        if ($db_image)
            $p_icon = $this -> image -> send_images_json($db_image, "img", NULL, 0, 'class="i180"');

        return "
            <div class='fichaicon'>
                $p_icon      
            </div>";
    }

    /**
     * Cria bloco do topo da ficha com dados administrativos
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
            $value = NULL;

            switch ($key) {

                case 'date' :
                case 'date_act' :
                    $value = (isset($db_result[$obj['db']])) ? GDate::make_date($db_result[$obj['db']], $obj['options']['mode']['hour'], "DATE", $obj['options']['mode']['fill']) : NULL;
                    break;
                case 'status' :
                    $value = (isset($db_result[$obj['db']])) ? $db_result[$obj['db']] : "offline";
                    $letter_color = ($value == "online") ? "verde" : "red";
                    break;
                default :
                    if (isset($obj['db']) && isset($db_result[$obj['db']]))
                    {
                        $value = $db_result[$obj['db']];
                    }
                    break;
            }

            if ($value)
                $data_adm .= "
                        <p>
                            <span>
                                " . $obj['name'] . ":
                            </span>
                            <span class='$letter_color'>
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
     * @param array $config_ob - array de configuração (objecto fields do objeto de configuração)
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

                if (!isset($field["type"]))
                    continue;

                switch(substr($field["type"],2)) {
                    case 'DATE' :
                        $db_result[$field['db']] = GDate::make_date($db_result[$field['db']], $field['options']['mode']['hour'], "DATE", $field['options']['mode']['fill']);
                        $boxes .= $this -> make_sheet_line($field, $db_result);
                        break;
                    case 'DATALIST' :
                    case "INPUT" :
                    case "SELECT" :
                        $boxes .= $this -> make_sheet_line($field, $db_result);
                        break;
                    case 'IMAGE' :
                        $p_image = NULL;
                        $p_image[$field['name']] = $this -> image -> make_photo_gallery_json($db_result[$field['db']], NULL, NULL, TRUE, FALSE);
                        $boxes .= $this -> make_box($p_image, "fichabox", "fileadmblock");
                        break;
                    case 'VIDEO' :
                        $p_video = $this -> video -> make_video_json($db_result[$field['db']]);
                        if ($p_video)
                            $boxes .= $this -> make_box(array($field['name'] => $p_video), "fichabox", "fileadmblock");
                        break;
                    case "EDITDIV" :
                    case "TEXTAREA" :
                        $boxes .= $this -> make_box(array($field['name'] => $db_result[$field['db']]), "fichabox", "fileadmblock");
                        break;
                    case "BOX RL" :
                        if (isset($Pvalue['related_object']))
                        {

                            $s = explode(",", $CT[$Pvalue['db']]);

                            for ($es = 0; $es < count($s); $es++)
                            {

                                $pds .= $this -> forModule($s[$es], constant($Pvalue['related_object']), FALSE);
                            }
                            $prel[$Pvalue['name']] = $pds;
                            $pds = "";
                        }
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
     *Cria uma caixa com os dados de um campo para apresentar na ficha de apresentação de um item
     *
     * @param array $B - nomes e contudos das caixas
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

    public function validate_file_input($ID)
    {

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
        {

            trigger_error("Flag invalida", E_USER_WARNING);
            return FALSE;
        }

        $ide = $this -> id($ID);

        if (!$ide)
        {

            trigger_error("Id invalido", E_USER_WARNING);
            return FALSE;
        }

        return $ide;
    }

}

class OperationsBar extends Core
{

    private $confi;
    private $table;

    private $images;

    function __construct($OB)
    {

        parent::__construct();

        $this -> confi = $OB;
        $this -> table = $OB['table'];

        $this -> images = new GestImage();
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
            return '{"alert":"Não tem permissão para realizar esta operação. CODE:E003"}';

        $id = NULL;
        $folder = NULL;

        $add = $this -> make_sheet_blocks($predefined, "ADD");

        return $this -> make_sheet($id, $folder, $add, "ADD");

    }

    /**
     * Pesquisa item na base de dados para criar ficha de edição
     *
     * @param string $id_item - id do item a pesquisar
     *
     */
    public function edit_item_sheet_db($id_item)
    {

        $id = parent::id($id_item);

        if (!$id)
            return '{"alert":"Não foi possivel realizar esta operação. CODE:E002"}';

        $idx = $this -> confi['admin']['private']['toke']['db'];

        $q_edit = logex::$tcnx -> query("SELECT * FROM " . $this -> table . " WHERE " . $idx . " = " . $id . " LIMIT 1");

        return $this -> edit_item_sheet($q_edit -> fetch_array());
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
            return '{"alert":"Não tem permissão para realizar esta operação. CODE:E003"}';

        @$folder = $query_result[$this -> confi['admin']['private']['folder']['db']];

        $id = $query_result[$this -> confi['admin']['private']['toke']['db']];

        $edit = $this -> make_sheet_blocks($query_result, "UPDATE");

        return $this -> make_sheet($id, $folder, $edit, "UPDATE");

    }

    /**
     *
     * cria ficha de adição ou edição
     *
     * @param int $I - id do item que a ficha representa
     * @param string $F - nome da pasta do item
     * @param string $C - conteúdo (todos os blocos com campos de introdução de dados)
     * @param string $M - modo ou operação a realizar com os dados do item (ADD = adicção | UPDATE = edição)
     *
     * @return string estrutura HTML da ficha
     */
    private function make_sheet($I, $F, $C, $M)
    {

        return '
                        <div id="mainEdition" data-id="i:' . $I . '" data-pasta="' . $F . '">                           
                            <form method="post" action="' . $this -> form_action() . '">                        
                                <div class="dataedit" >
                                    <div class="dv95pL00">
                                        <input type="hidden" id="identidade" name="toke" value="' . $I . '">
                                        <input type="hidden" name="filemode" value="' . $M . '">
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

    /**
     * Define os campos de introdução de um item.
     * Esta função é importante para manter as consistência entre os dados da ficha e os
     * dados para salvar, essencialmente no que diz respeito aos nomes dos campos.
     *
     * @return array - blocos e respectivos campos da ficha do item.
     */
    private function block_fields()
    {

        $this -> confi['admin']['public'] = array_merge($this -> confi['admin']['private'], $this -> confi['admin']['public']);

        unset($this -> confi['admin']['private']);

        return array(
            $this -> confi['admin'],
            $this -> confi['fields'],
            $this -> confi['midia']
        );
    }

    /**
     * Cria os campos de introdução de dados numa ficha de item
     * agrupa-os em blocos.
     *
     * @param array $DB - resultado da peequisa do item na base de dados
     * @param string $MODE - tipo de operação a realizar com os dados (ADD = adicção | UPDATE = edição)
     *
     * @return string - estrutura HTML com os campos e blocos
     *
     */
    private function make_sheet_blocks($DB, $MODE)
    {

        $block = NULL;
        $b_title = NULL;

        $all = $this -> block_fields();

        $oimage = new GestImage();
        $input = new ElementInput();
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

                $icon = $oimage -> make_icon($karr);

                $data_list -> make_datalist_options($varr, $this -> table, $DB);

                foreach ($varr as $key => $value)
                {

                    if (empty($value['type']))
                        continue;

                    if (!is_array($value['db']))
                        $dbvalue = (isset($DB[$value['db']])) ? $DB[$value['db']] : "";

                    $name = $karr . "_" . $key;

                    $field = NULL;

                    switch (substr($value["type"], 2)) {
                        case "DATE" :
                            $ddate['type'] = $value['type'];
                            $ddate['options'] = $value['options']['att'];
                            $field = $input -> make_input($ddate, GDate::make_date($dbvalue, $value['options']['mode']['hour'], "DATE", $value['options']['mode']['fill']), $name);
                            break;
                        case "DATALIST" :
                            $field = $data_list -> data_list($dbvalue, $value['options'], $name, $key);
                            break;
                        case "SELECT" :
                            $selc = new ElemetSelect();
                            $field = $selc -> make_select($name, $dbvalue, $value['options']);
                            break;
                        case "INPUT" :
                        case "TEXTAREA" :
                        case "EDITDIV" :
                        case "NEDITDIV" :
                        case "DSEO" :
                        case "TSEO" :
                        case "ISEO" :
                            $field = $input -> make_input($value, $dbvalue, $name);
                            break;
                        case "TOPICS" :
                            $field = $this -> set_topics($value, $arr['DB']);
                            break;
                        case "RADIOB" :
                            $radiob = new ElementRadioButton();
                            $field = $radiob -> make_radiob($value['options'], $dbvalue, $name);
                            break;
                        case "CHECKB" :
                            $checkb = new ElementCheckBox();
                            $field = $checkb -> make_checkb($value['options'], $dbvalue, $name);
                            break;
                        case "VIDEO" :
                            $ovideo = new GestVideo();
                            $field = $ovideo -> make_video_insert($dbvalue);
                            break;
                        case "IMAGE" :
                            $image = $oimage -> make_photo_gallery_json($dbvalue, $value['options']['image_name'], $value['options']['captions']);
                            unset($value['options']['image_name']);
                            unset($value['options']['captions']);

                            $field = $input -> make_input($value, $image, $name);

                            break;
                        default :
                            $field = '';
                            break;
                    }

                    if ($field)
                    {
                        $div .= $this -> wrap_sheet_field($field, $icon, $value, $key);
                        $field = "";
                    }

                    @$b_tiltle = "<div>" . $this -> make_block_title($MODE, $varr['name'], $icon) . "<div>" . $div . "</div></div>";

                }//end 3
                $block .= $b_tiltle;
            }//end 2

        }
        return $block;
    }

    /**
     * Embrulha o elemento HTMl para introdução de dados e acrescento texto e ajuda sobre o campo
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

            if ($KEY)
            {

                $id = "id='w_$KEY'";
                $id_dsp = "id='dsp_$KEY'";

            }
            else
            {

                $id = "";
                $id_dsp = "";

                trigger_error("Key na definido em warp_add_field para o objeto $OB[name]", E_USER_WARNING);
            }

            return "                                    
                <div class='wrapaddfield $size' $id draggable='false'>
                    
                    <div class='wrapaddfielddiv'>
                        <img src='../imagens/help_icon.png' title='$help' class='helpicon'> 
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
    private function make_block_title($MODE, $NAME, $ICON)
    {

        switch ($MODE) {

            case "ADD" :
                $mcss = "sectitG";
                break;
            case "UPDATE" :
                $mcss = "sectitY";
                break;
            case "CLONE" :
                $mcss = "";
                break;
        }

        return '
                        <p class="' . $mcss . '">
                            <span class="sp15b">' . $ICON . '  ' . $NAME . '</span>
                            <img src="../imagens/folderon.png" class="igop" data-type="hideshow">
                        </p>';
    }

    /**
     *Grava os dados da ficha na base de dados e atualiza o sitemap
     *
     * @return string - objeto json com os dados do item gravado (em caso de sucesso) ou com uma mensagem de erro (caso de falha)
     *
     */
    public function save_item()
    {

        if (!parent::check())
            return '{"alert":"Não tem permissão para realizar esta operação. - OBSI1"}';

        if (!isset($_POST))
            return '{"alert":"Não tem permissão para realizar esta operação. - OBSI2"}';

        $save_mode = ($_POST['filemode'] == "UPDATE" || $_POST['filemode'] == "ADD") ? $_POST['filemode'] : FALSE;

        if (!$save_mode)
            return '{"alert":"Não tem permissão para realizar esta operação. - OBSI3"}';

        $identy = (isset($_POST['toke'])) ? parent::id($_POST['toke']) : FALSE;

        $sts = (isset($_POST['public_status']) && $_POST['public_status'] === "online") ? TRUE : FALSE;

        $_POST['public_folder'] = (isset($_POST['public_folder'])) ? parent::validate_name($_POST['public_folder']) : NULL;

        $query = NULL;

        if ($save_mode == "ADD" && !$identy)
        {

            $query = $this -> save_add();
        }

        if ($save_mode == "UPDATE" && $identy)
        {

            $query = $this -> save_edit($identy);
        }

        if (!$query)
            return '{"alert":"Não tem permissão para realizar esta operação. - OBSI4"}';

        $rslt = logex::$tcnx -> query($query);

        if (!$rslt)
            return '{"alert":"Por favor, tente de novo.\\nSe não conseguir realizar a operação, entre em contato com\\n a assistencia técnica e informe este numero: ' . logex::$tcnx -> errno . '"}';

        #PRODUTO COM O MESMO NOME
        if (logex::$tcnx -> errno == 1062)
        {

            return '{"alert":"Já existe um item  com esse nome."}';
        }

        $id = (logex::$tcnx -> insert_id) ? logex::$tcnx -> insert_id : $identy;

        if (isset($_POST['public_publish']) && $sts == "online")
        {

            //GestSocial::publish($_POST['public_publish']);
        }

        #ATUALIZA O SITEMAP

        return '{"result":["' . $_POST['public_folder'] . '","' . $id . '"]}';

    }

    /**
     * cria a query para adição de um item na base de dados
     *
     * @return string
     *
     */
    public function save_add()
    {

        $_POST['public_date_act'] = "";
        $_POST['public_date'] = "";

        $order_index = NULL;
        $order_index_db = $this -> confi['admin']['private']['order']['db'];

        if (!empty($order_index_db))
        {

            $ni = logex::$tcnx -> query("SELECT MAX($order_index_db) as IO FROM " . $this -> table);
            $n = $ni -> fetch_array();
            $order_index = ", $order_index_db=" . ($n['IO'] + 1);
        }

        return "INSERT INTO " . $this -> table . " SET " . rtrim($this -> make_query(), ",") . $order_index;
    }

    /**
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

        $toke = $this -> confi['admin']['private']['toke']['db'];
        
        return "UPDATE " . $this -> table . " SET " . rtrim($this -> make_query(), ",") . " WHERE " . $toke . "=$ID";
    }

    /**
     * Cria sequência de campo = valor para a query na base de dados
     *
     * @return string
     *
     */
    protected function make_query()
    {
        unset($_POST['module']);
        unset($_POST['flag']);
        unset($_POST['filemode']);
        
        array_map(array(
            "Core",
            "text_clean_json"
        ), $_POST);

        $all = $this -> block_fields();

        $query = NULL;

        foreach ($all as $v_all)
        {

            if (!is_array($v_all))
                continue;

            foreach ($v_all as $k_ob => $v_ob)
            {

                if (!is_array($v_ob))
                    continue;

                foreach ($v_ob as $key => $field)
                {

                    if (!is_array($field))
                        continue;

                    if (empty($field['db']))
                        continue;

                    $namex = $k_ob . "_" . $key;

                    switch(substr($field["type"], 2)) {
                        case 'DATE' :
                            if (!is_array($field['db']) && isset($_POST[$namex]))
                            {
                                $query .= $field['db'] . "='" . GDate::make_date($_POST[$namex], $field['options']['mode']['hour'], "DATEBD", $field['options']['mode']['fill']) . "',";
                            }
                            break;
                        case 'IMAGE' :
                            $imgs = $this -> images -> make_json_img_capt($field['options']);
                            $query .= $field['db'] . "='" . logex::$tcnx -> real_escape_string($imgs) . "',";
                            break;
                        case 'VIDEO' :
                            $query .= $field['db'] . "='" . logex::$tcnx -> real_escape_string(GestVideo::get_video()) . "',";
                            break;
                        default :
                            $rt = isset($_POST[$namex]);
                            if (!is_array($field['db']) && isset($_POST[$namex]))
                            {
                                $query .= " $field[db] ='" . logex::$tcnx -> real_escape_string($_POST[$namex]) . "',";
                            }
                            break;
                    }

                }
            }
        }

        return trim($query, ",");
    }

    /**
     * Apaga um item na base de dados
     *
     * @param int $I - id do item a apagar
     *
     * @return boolean | json
     *
     */
    public function delete_item($I)
    {

        if (!parent::check())
            return '{"alert":"Não tem permissão para realizar esta operação. OP' . __LINE__ . '"}';

        $id = $this -> id($I);

        if (!$id)
            return '{"alert":"Não possivel realizar esta operação. OP' . __LINE__ . '"}';

        $folder = $this -> confi['admin']['private']['folder']['db'];
        $toke = $this -> confi['admin']['private']['toke']['db'];

        $rfold = logex::$tcnx -> query("SELECT $folder FROM $this->table WHERE $toke = $id");
        $folders = $rfold -> fetch_array();

        $del = logex::$tcnx -> query("DELETE FROM $this->table WHERE $toke = $id ");

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

        $attributes = NULL;

        $contenteditable = ($OB['type'] == "S_EDITDIV" || $OB['type'] == "L_EDITDIV") ? "contenteditable=true" : "";

        if (!empty($OB['options']) && is_array($OB['options']))
        {

            unset($OB['options']['value']);
            unset($OB['options']['class']);
            unset($OB['options']['id']);
            unset($OB['options']['name']);
            unset($OB['options']['type']);

            foreach ($OB['options'] as $key => $value)
            {

                $attributes .= $key . "='" . $value . "' ";

            }
        }

        switch($OB['type']) {
            case "S_INPUT" :
            case "L_INPUT" :
            case "S_DATE" :
            case "L_DATE" :
                $inpt = "<input class='editfield' type='text' value='$DB' name='$NM' id='$NM'  $attributes  />";
                break;
            case "S_ISEO" :
            case "L_ISEO" :
                $inpt = "<input class='editfield seo' type='text' value='$DB' name='$NM' id='$NM'  $attributes  />";
                break;
            case "S_EDITDIV" :
            case "L_EDITDIV" :
            case "S_NEDITDIV" :
            case "L_NEDITDIV" :
            case "S_IMAGE" :
            case "L_IMAGE" :
                $inpt = "<div  $contenteditable class='editbox' id='$NM' $attributes >$DB</div>";
                break;
            case "S_DSEO" :
            case "L_DSEO" :
                $inpt = "<div  contenteditable=true class='editbox seo' id='$NM' $attributes >$DB</div>";
                break;
            case "S_TEXTAREA" :
            case "L_TEXTAREA" :
                $inpt = "<textarea class='editbox' id='$NM' name='$NM' $attributes >$DB</textarea>";
                break;
            case "S_TSEO" :
            case "L_TSEO" :
                $inpt = "<textarea class='editbox SEO' id='$NM' name='$NM' $attributes >$DB</textarea>";
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
        $target = NULL;

        $options = $this -> make_select_options($config);

        if (!empty($config['target']))
            $target = 'data-target="' . $config['target'] . '"';

        if ($config && is_array($options))
        {

            foreach ($options as $k_op => $v_op)
            {

                $selec = ($k_op == $value) ? "selected='selected'" : "";

                $more_options .= "<option value='$k_op' $selec>$v_op</option>";
            }
        }

        $first_option = ($value) ? "<option value='' >---------------------</option>" : "<option value='' selected='selected'>---------------------</option>";

        return '<select class="iselectform"  name="' . $name . '" id="' . $name . '" ' . $target . '>' . $first_option . $more_options . '</select>';
    }

    /**
     * Cria as opções para a tag select
     *
     * @param array $config - array com a configuração das opções
     *
     * @return array - opções com a chave a representar o valor e o valor o texto
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

                $query = logex::$tcnx -> query("SELECT DISTINCT $fields FROM $dynamic[table]  $dynamic[condition] ORDER BY $fields ASC");

                while ($result = $query -> fetch_array())
                {

                    foreach ($dynamic['values'] as $key => $val)
                    {

                        $dy_opt[$result[$val]] = $result[$key];
                    }
                }

                $query -> free();

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

class GestVideo extends Core
{

    /**
     * cria objeto json para guardar dados de video na base de dados
     * @return json object
     */
    public function get_video()
    {

        $from = NULL;
        $vid = NULL;
        $pos = NULL;

        if (!empty($_POST['embvideo']) || !empty($_POST['filevideo']))
        {

            //verifica que tipo de video foi enviado, se embeded, se do arquivo
            if ($_POST['embvideo'])
            {

                $from = "embeded";
                $vid = $_POST['embvideo'];
            }
            if ($_POST['filevideo'])
            {

                $from = "fromfile";
                $vid = $_POST['filevideo'];
            }

            $pos = ($_POST['videopos'] == "baixo") ? "baixo" : "cima";

            return '{"video":"' . self::text_clean_json($vid) . '","from":"' . $from . '","pos":"' . $pos . '"}';
        }
        else
        {
            return "";
        }
    }

    /*
     * cria um espaÃ§o para inserir videos
     * $P = posiÃ§Ã£o do video
     * $M = modo, que pode ser do arquivo ou incorporado
     * $V = video
     * $C = class css
     *
     */

    public function make_video_insert($V)
    {

        $video = json_decode($V, TRUE);

        $local_video_name = NULL;
        $emb_video = NULL;

        $cima = ($video['pos'] === "cima") ? "checked='checked'" : $baixo = "checked='checked'";

        //verifica o tipo de video inserido
        if ($video["from"] == "fromfile")
        {

            $local_video_name = $video["video"];
        }
        if ($video["from"] == "embeded")
        {

            $emb_video = $video["video"];
        }

        $select_video = array();

        $select_video['dynamic']['table'] = "video_galeria";
        $select_video['dynamic']['values'] = array("nome_video" => "nome_video");
        $select_video['dynamic']['condition'] = NULL;
        $select_video['static'] = NULL;

        $tag_select = new ElemetSelect;

        $select = $tag_select -> make_select('filevideo', $local_video_name, $select_video);

        return "
        <div id='video_insert'>
                    <div class='video_division'>
                        <p class='video_title'>
                            Posição
                        </p>
                        <p>
                            <input type='radio' name='videopos' value='cima' $cima class='rd11'> cima 
            </p>
            <p> 
                            <input type='radio' name='videopos' value='baixo' $baixo class='rd11'> baixo
            </p>
                    </div>
                    <div class='video_division'>
                        <p class='video_title'>
                            Incorporar
                        </p>
                        <textarea name='embvideo' class='ta100px100pxFF'>$emb_video</textarea>
                    </div>
                    <div class='video_division'>
                        <p class='video_title'>
                            Arquivo
                        </p>
                        $select 
                </div>    
            <div class='rodape' ></div> 
                ";
    }

    /*
     *
     */

    public function make_video_insert_json($P)
    {

        $cima = ($P === "cima") ? "checked='checked'" : $baixo = "checked='checked'";

        //verifica o tipo de video inserido
        if ($M == "fromfile")
        {

            $vid = $V;
            $f = "";
        }
        if ($M == "embeded")
        {

            $vid = "";
            $f = $V;
        }

        return "
        <div  class='dv270xRC'>
        
            <div class='dv95pL00'>
                <span class='sp15b'>
                    Video
                </span>
            </div>  
                    
            <table class='tb97pC '>
            <tr>
                    <td class='$C'>
                        Posição
                    </td>
                </tr>
                <tr>
                    <td class='td75pLFF00'>
                        <p>
                            <input type='radio' name='videopos' value='cima' $cima class='rd11'> cima 
                        </p>
                        <p> 
                            <input type='radio' name='videopos' value='baixo' $baixo class='rd11'> baixo
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <td class='$C'>
                        Incorporar
                    </td>
                </tr>
                
                <tr>
                    <td class='td75pLFF00'>
                        <textarea name='embvideo' value='$f' class='ta100px100pxFF'>$f</textarea>
                    </td>
                </tr>
                
                <tr>
                    <td class='$C'>
                        Arquivo
                    </td>
                </tr>
                
                <tr>
                    <td class='td75pLFF00'>
                        <select class='sl100x' name='filevideo'>
                            <option ></option>
                            " . $this -> escComBox('nome_video', 'video_galeria', $vid) . " 
                        </select>
                    </td>
               </tr>
            
            </table>
            <div class='rodape' >
            </div>
        </div>
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

class GestImage extends Core
{

    /**
     * Manipula o objeto json de armazenamento de fotos na base de dados
     *
     * @param array $pictures_object = objeto json com as imagens e as legendas armazenado na base de dados
     * @param string $mode = pode ter dois valores "src" para devolver apenas o endereço da imagem ou "img" para devolver a tag completa
     * @param srting $captions_lang = idioma
     * @param boolean $collection = se FALSE só retorna a primeira foto
     * @param string $html_attributes = atributos html
     *
     * @return string - coleção de endereços de imagens ou elementos html img
     *
     */
    public function send_images_json($pictures_object, $mode, $captions_lang, $collection = 1, $html_attributes = NULL)
    {

        if (!$pictures_object)
            return NULL;

        $pictures = json_decode($pictures_object, TRUE);

        if ($pictures && is_array($pictures) && is_array($pictures['photos']))
        {

            $result = NULL;

            foreach ($pictures['photos'] as $value)
            {

                if ($mode === "img" && !empty($value['photo']))
                {

                    $captions = (isset($captions_lang)) ? trim($value[$captions_lang], '"') : "";
                    $result .= "<img src=" . $value['photo'] . " alt='" . $captions . "' title='" . $captions . "' $html_attributes>";
                }

                if ($mode == "src" && !empty($value['photo']))
                    $result .= $value['photo'];

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
     * Cria um objeto json para gurdar os imagens na base de dados
     *
     * @param array $config - array com 2 elementos ['image_name'] = nome de $_POST com o nome da imagem e ['captions'] = nome das legendas
     *
     * @return boolean || string objeto json
     *
     */
    public function make_json_img_capt($config)
    {

        //objecto json de uma imagem
        $img_object = NULL;
        //array com o nome das imagens
        $images = NULL;
        //nome dos $_POST de legendas
        $captions_name = NULL;

        if (!isset($_POST) || empty($config['image_name']))
            return NULL;

        $img_name = $config['image_name'];
        $captions_name = $config['captions'];
        
        if(!isset($_POST[$img_name]))
            return NULL;

        $images = $_POST[$img_name];

        //para arrays com indices diferentes de valores inteiros
        if (!empty($config['key']))
            $images = $_POST[$img_name][$config['key']];

        if (!is_array($images))
            return NULL;

        foreach ($images as $img)
        {

            $object_element = NULL;

            if (empty($img))
                continue;

            $object_element .= '"photo":"' . self::text_clean_json($img) . '",';

            foreach ($GLOBALS['LANGPAGES'] as $lang)
            {

                $captions = NULL;

                if (isset($_POST[$lang . "_" . $captions_name][$img]))
                    $captions = $_POST[$lang . "_" . $captions_name][$img];

                $object_element .= '"' . $lang . '":"' . self::text_clean_json($captions) . '",';
            }

            $img_object .= ",{" . rtrim($object_element, ",") . "}";
        }

        return '{"photos":[' . trim($img_object, ",") . ']}';

    }

    /**
     * cria um logo para um item sem imagem
     * @param string $nome nome do item
     * @param string $ref referencia do iten
     * @return boolean
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
     * Cria imagens embrulhadas em uma DIV que podem ser usadas como fotogaleria. As imagens podem ser acompanhadas por legendas. Pode incluir campos
     * e botão de apagar para usar em fichas de edição. Este metodo repete as mesmas configurações do função javascript imgallery_with_captions do arquivo mgp_nucleogest.
     * Esta função javascript tambem pode manipular as DIV criadas por este metodo.
     *
     * @param array $images_object = objeto json com as imagens e as legendas armazenado na base de dados
     * @param string $input_image_name - nome do campo input das fotos
     * @param string $captions_name nome do campo input das legendas
     * @param boolean $with_captions se verdadeiro cria as fotos com legendas
     * @param boolean $allow_edit se verdadeiro formata campos input e apresenta botão para apagar
     *
     * @return string HTML
     */
    public function make_photo_gallery_json($images_object, $input_image_name = "foto", $captions_name = "legenda", $with_captions = TRUE, $allow_edit = TRUE)
    {

        if (empty($images_object))
            return NULL;

        if (!$images_object = json_decode($images_object, TRUE))
            return FALSE;

        if (!is_array($images_object['photos']))
            return FALSE;

        $name = $input_image_name . "[]";
        $index = 0;
        $html_image_blocks = NULL;

        foreach ($images_object['photos'] as $image)
        {

            if (!is_array($image))
                continue;

            $capt = NULL;
            $del = NULL;
            $input_image = NULL;
            $only_read = "readonly";

            if ($allow_edit)
            {

                $del = "<img src='imagens/minidel.png' class='ig15A'>";
                $input_image = "<input type='hidden' id='" . $image['photo'] . "' name='" . $name . "' value='" . $image['photo'] . "'/>";
                $only_read = NULL;
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
                       <input type='text' name='" . $value . "_" . $captions_name . "[" . $image['photo'] . "]' value='" . $image[$value] . "' class='tx90px35pxFF' draggable='false' $only_read >
                       </p>";
                    }
                }
            }

            $html_image_blocks .= "
                        <div data-index='$index' class='dvB' draggable='false'>
                            $del
                            <img src='" . $image['photo'] . "' class='ig150xp150x'>
                            $input_image
                            $capt
                        </div>
                ";

            $index++;
        }

        return $html_image_blocks;

    }

    /*
     *@param string $s_img nome da imagem ou tag img completa
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

    /*
     * envia json com as imagens solicitadas e filtradas
     * $p=nome da pasta
     * $o=opção de pesquisa
     * $v=valor da opção de pesquisa
     */
    public function image_list()
    {
        if (parent::check())
        {

            $pasta = (isset($_POST['pasta']) && $this -> validate_name($_POST['pasta'])) ? $_POST['pasta'] : "";

            $rslt = logex::$tcnx -> query("SELECT id,nome,mini,pasta FROM foto_galeria WHERE pasta='$pasta' ORDER BY id ASC");

            $images = "";

            while ($image = $rslt -> fetch_array())
            {
                $images .= ',["i:' . $image['id'] . '","' . $this -> cut_str($image['nome'], 20) . '","' . _IMAGEURL . '/' . $image['mini'] . '","' . $image['pasta'] . '"]';
            }
            return '{"images":[' . ltrim($images, ",") . ']}';
        }
    }

    /*
     * faz o upload e guarda os dados da imagem na base de dados
     */
    public function upload_image()
    {

        $photo = $_FILES['foto'];

        $name = str_replace(" ", "_", $photo['name']);
        $image = $photo['tmp_name'];
        $typeimg = exif_imagetype($image);

        $folder = (isset($_POST['pasta']) && $this -> validate_name($_POST['pasta'])) ? $_POST['pasta'] : "";

        if ($typeimg <> 1 && $typeimg <> 2 && $typeimg <> 3)
        {

            return '{"error":"ficheiro ineadequado"}';
        }

        $r_check_name = logex::$tcnx -> query("SELECT nome FROM foto_galeria WHERE nome='$name'");
        $check_name = $r_check_name -> fetch_array();

        if ($check_name[0] == $name)
        {

            return '{"error":"ficheiro já existe"}';
        }

        $path = _IMAGEPATH . $name;

        $up_file = move_uploaded_file($image, $path);

        if (!$up_file)
        {

            return '{"error":"erro de gravação"}';

        }

        if ($photo['size'] > 10)
        {

            $main_pic = $name;
            $mini_pic = $this -> redimensdiona($path, "mini_" . $name, 100, 100);
        }

        if (logex::$tcnx -> query("INSERT INTO foto_galeria (nome,pasta,mini) VALUES ('$main_pic','$folder','$mini_pic')"))
        {

            return $this -> image_list();
        }
        else
        {

            return '{"error":"erro de gravação"}';
        }

    }

    /*
     * redimensiona uma imagem
     */
    private function redimensdiona($imagem, $name, $w, $h)
    {

        //criamos uma nova imagem ( que vai ser a redimensionada) a partir da imagem original
        $tipoimg = exif_imagetype($imagem);
        switch($tipoimg) {
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
        $width = imagesx($imagem_orig);
        $height = imagesy($imagem_orig);
        $scale = min($maxw / $width, $maxh / $height);
        if ($scale < 1)
        {
            $largura = floor($scale * $width);
            $altura = floor($scale * $height);
        }
        else
        {
            $largura = $width;
            $altura = $height;
        }
        $imagem_fin = imagecreatetruecolor($largura, $altura) or die();
        imagealphablending($imagem_fin, false);
        imagesavealpha($imagem_fin, true);

        //copiamos o conteudo da imagem original e passamos para o espaco reservado a redimencao
        imagecopyresized($imagem_fin, $imagem_orig, 0, 0, 0, 0, $largura, $altura, $width, $height);
        $path = _IMAGEPATH . $name;
        //Salva a imagem
        switch($tipoimg) {
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
        {
            return false;
        }
        else
        {
            return $name;
        }
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

        if (!$id = $this -> id($image_id))
            return parent::mess_alert("GIM" . __LINE__);

        $rfold = logex::$tcnx -> query("SELECT $folder_column FROM $db_table WHERE $id_column = $id");
        $folders = $rfold -> fetch_array();

        $del = logex::$tcnx -> query("DELETE FROM $db_table WHERE $id_column = $id ");

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

class GestFolders extends Core
{

    private $image;

    private $table;
    private $icon;
    private $folder;
    private $toke;
    private $status;
    private $identifier;
    private $query;

    private $folder_query = NULL;

    /**
     * @param array $config_ob - configuração do objeto
     */
    function __construct(array $config_ob)
    {

        parent::__construct();

        $this -> table = $config_ob['table'];

        if (!empty($config_ob['admin']['private']['folder']['db']))
        {

            $this -> folder = $config_ob['admin']['private']['folder']['db'];
        }

        if (!empty($config_ob['admin']['private']['toke']['db']))
        {

            $this -> toke = $config_ob['admin']['private']['toke']['db'];
            $this -> query = "," . $this -> toke;
        }

        if (!empty($config_ob['admin']['private']['status']['db']))
        {

            $this -> status = $config_ob['admin']['private']['status']['db'];
            $this -> query .= "," . $this -> status;
        }

        if (!empty($config_ob['admin']['private']['icon']['db']))
        {

            $this -> icon = $config_ob['admin']['private']['icon']['db'];
            $this -> query .= "," . $this -> icon;
        }

        $this -> identifier['main'] = NULL;
        $this -> identifier['opt'] = NULL;

        if (!empty($config_ob['admin']['private']['identifier']))
        {

            if (!empty($config_ob['admin']['private']['identifier']['db']))
            {

                $this -> identifier['main'] = $config_ob['admin']['private']['identifier']['db'];
                $this -> query .= "," . $this -> identifier['main'];
            }

            if (!empty($config_ob['admin']['private']['identifier']['options']))
            {

                $this -> identifier['opt'] = $config_ob['admin']['private']['identifier']['options'];
                $this -> query .= "," . $this -> identifier['opt'];
            }
        }

        $this -> image = new GestImage();
    }

    /**
     * Define uma pesquisa de pastas personalizada na base de dados
     *
     * @param string $query - pesquisa na base de dados
     *
     */
    public function set_folder_query($query)
    {

        $this -> folder_query = $query;
    }

    /**
     * Pesquisa na base de dados os itens de uma pasta
     *
     * @param string $folder - nome da pasta
     *
     * @return string - todos os elementos e todas as pastas
     *
     */
    protected function set_item_query($folder)
    {

        $order = NULL;
        $i_main = NULL;
        $i_opt = NULL;

        $tquery = trim($this -> query, ",");

        if (!$tquery)
            return '"' . $folder . '":[],';

        if ($this -> identifier['main'])
            $i_main = $this -> identifier['main'];

        if ($this -> identifier['opt'])
            $i_opt = "," . $this -> identifier['opt'];

        $order = "ORDER BY " . trim($i_main . $i_opt) . " ASC";

        $item_query = "SELECT $tquery FROM $this->table WHERE $this->folder='$folder' $order";

        if (!$q_item = logex::$tcnx -> query($item_query))
            return parent::mess_alert("GFD" . __LINE__);

        return $this -> get_folders_itens($q_item, $folder);
    }

    /**
     * Procura as pasta na base de dados
     *
     * @return resource - objeto mysqli
     */
    private function get_folders()
    {

        if (!$this -> folder_query)
            $this -> folder_query = "SELECT DISTINCT $this->folder FROM $this->table ORDER BY $this->folder ASC";

        return logex::$tcnx -> query($this -> folder_query);
    }

    /**
     * Cria pastas sem itens dentro
     *
     * @return string - objeto json
     *
     */
    public function make_simple_folders()
    {

        if (!parent::check())
            parent::mess_alert("GFD" . __LINE__);

        if (!$folder_rslt = $this -> get_folders())
            parent::mess_alert("GFD" . __LINE__);

        $rfold = NULL;

        while ($fold = $folder_rslt -> fetch_array())
        {

            if (!$rfold)
            {

                $rfold = '"' . $fold[0] . '"';

            }
            else
            {

                $rfold .= ',"' . $fold[0] . '"';
            }
        }

        return '{"sfolder":[' . $rfold . ']}';

    }

    /**
     * Crias as pasta
     *
     * @return string - objeto json com uma array de objetos {"id":"","status":"","name":"","image":""}
     *
     */
    public function make_folders()
    {

        if (!parent::check())
            parent::mess_alert("GFD" . __LINE__);

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            parent::mess_alert("GFD" . __LINE__);

        $rs = NULL;

        $folder_rslt = $this -> get_folders();

        while ($fold = $folder_rslt -> fetch_array())
        {

            $rs .= $this -> set_item_query($fold[0]);
        }

        return '{' . trim($rs, ",") . '}';
    }

    /**
     * Cria os itens de cada pasta
     *
     * @param mysqli_result $q_item - resultado da pesquisa na base de dados
     * @param string $folder - nome da pasta
     *
     * @return string string - objetos json com uma array de objetos "nome_pasta":[{"id":"","status":"","name":"","image":""},...] , ...
     *
     */
    protected function get_folders_itens(mysqli_result $q_item, $folder)
    {

        $item = NULL;

        while ($r_item = $q_item -> fetch_array())
        {

            $jname = $this -> item_name($r_item);

            $jstatus = (!empty($r_item[$this -> status])) ? $r_item[$this -> status] : "offline";

            $jimg = (!empty($r_item[$this -> icon])) ? '"' . $this -> image -> send_images_json($r_item[$this -> icon], "src", NULL, 0, NULL) . '"' : '""';

            $item .= '{"id":"' . $r_item[$this -> toke] . '","status":"' . $jstatus . '","name":"' . $jname . '","image":' . $jimg . '},';
        }

        if (!$item)
            return NULL;

        return '"' . $folder . '":[' . trim($item, ",") . '],';
    }

    /**
     * Cria a identificação de cada item da pasta
     *
     * @param array $db_item - resultado da pesqisa do item na base de dados
     *
     * @return string
     *
     */
    private function item_name(array $item)
    {

        $main_name = NULL;

        if (!empty($this -> identifier['main']))
        {

            $names = explode(",", $this -> identifier['main']);

            foreach ($names as $value)
            {
                if (!empty($item[$value]))
                    $main_name .= $item[$value] . " ";
            }

            if ($main_name)
                return $main_name;

        }

        if (!empty($this -> identifier['opt']))
        {

            $op_names = explode(",", $this -> identifier['opt']);

            foreach ($op_names as $value)
            {
                if (!empty($item[$value]))
                    $main_name .= $item[$value] . " ";
            }

            return $main_name;
        }

        return "[ " . $item[$this -> toke] . " ]";

    }

    /**
     * Muda um item de pasta
     *
     * @param string $item_id - valor do item na coluna primary key.
     * @param string $new_folder - nome da nova pasta.
     * @param string $mode - tipo de pasta devolvidas. "ALL" devolve pasta com itens (por omissão), "SIMPLE" devolve pastas vazias.
     *
     * @return string - objeto json atualizado para construir a lista de pasta
     *
     */
    public function change_folder($item_id, $new_folder, $mode = "ALL")
    {

        if (!$id = $this -> id($item_id))
            parent::mess_alert("GFD" . __LINE__);

        $n_folder = parent::validate_name($new_folder);

        if (!logex::$tcnx -> query("UPDATE $this->table SET $this->folder='$n_folder' WHERE $this->toke=$id"))
            parent::mess_alert("GFD" . __LINE__);

        if ($mode == "SIMPLE")
            return '{"sfolder":["' . $n_folder . '","i:' . $id . '"],"folders":' . $this -> make_simple_folders() . '}';

        return '{"result":["' . $n_folder . '","i:' . $id . '"],"folders":' . $this -> make_folders() . '}';
    }

}

class ElementDatalist extends Core
{
    /**
     * Cria a opçoes de uma datalist
     *
     * @param array $config- array de configuração
     * @param sting $table - nome da tabela da base de dados de onde são retiradas as opções
     * @param array $target_values - valor do target (resultado de pesquisa na base de dados)
     */
    public function make_datalist_options(&$config , $table, &$target_value )
    {
       
        
        foreach ($config  as $key => $value)
        {

            $targets = array();
            $opt = array();
            $all_options = NULL;
            $cond = NULL;
            
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

            if(!$rslt = logex::$tcnx -> query("SELECT DISTINCT $value[db] FROM  $table  WHERE $value[db]<>'' $cond ORDER BY  $value[db] ASC"))
                return FALSE;

            while ($cQuery = $rslt -> fetch_array())
            {

                if (!empty($cQuery[0]))
                {
                    $opt[] = $cQuery[0];
                }
            }

            mysqli_free_result($rslt);

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

    public function data_list($value,$options, $name, $key = NULL)
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
                <input value='$value' type='text' " . $tgt . " name='$name' class='tx75px22xLFF' data-action='listItem' id='$name'/>
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
                $check = 'checked="checked"';

            }
            else
            {

                $selected = NULL;
                $check = NULL;

                if ($rad == $DB)
                {

                    $selected = "backyellow";
                    $check = 'checked="checked"';
                }
            }

            $buttons .= '
                <div class="smanag ' . $selected . '" id="' . $NM . '">
                    ' . $radio . '
                    <input type="radio" name="' . $NM . '" value="' . $rad . '" ' . $check . '  class="rmanag">
                </div> ';

            $c++;
        }

        return "<div class='wrapb'>" . $buttons . "</div>";

    }

}

class ElementCheckBox extends Core
{

    public function make_checkb($OB, $dbvalue, $NM)
    {

        $checks = NULL;

        if (!is_array($OB))
        {

            trigger_error("OB não é uma array", E_USER_WARNING);

            return FALSE;
        }

        foreach ($OB as $chk)
        {

            $selected = NULL;
            $chks = NULL;

            if ($dbvalue == 1)
            {

                $selected = "backyellow";
                $chks = 'checked="checked"';
            }

            $checks .= '
                <div  class="smanag ' . $selected . '" id="' . $NM . '">
                    ' . $chk . '
                    <input type="checkbox" name="' . $NM . '[]" value="' . $chk . '" ' . $chks . ' class="rmanag">
                </div> ';

        }

        return "<div class='wrapb'>" . $checks . "</div>";
    }

}

class GDate extends Core
{

    /**
     * Manipula uma data fornecida
     *
     * @param string $D - data
     * @param boolean $H - se true a data apresenta as horas, minutos e segundos
     * @param string $U - constante que define o tipo de data retornado "DATE" = 01-01-1970 00:00:00,"TIMEST" = unix timestamp,"DATEBD" = 19701230245959
     * @param boolean $F = se true e não for fornecida a data faz retornar a data no momento, se false a funçao não retorna nada
     * @param strong $S - caracter separador da data
     *
     * @return string - data no formato definido pela constante
     *
     */
    public static function make_date($D = NULL, $H = 1, $U = 'DATE', $F = 1, $S = "-")
    {

        if (!empty($D))
        {

            $dt = date_parse($D);

            $temp = ($dt['warning_count'] > 0) ? NULL : mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']);

        }
        else
        {

            $temp = (!$F) ? FALSE : time();
        }

        if ($temp)
        {

            switch ($U) {
                case 'DATE' :
                    $dat = strftime("%d$S%m$S%Y", $temp);
                    $hour = ($H) ? strftime("%H:%M:%S", $temp) : "";
                    $ts = $dat . "  " . $hour;
                    break;
                case 'TIMEST' :
                    $ts = mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']);
                    break;
                case 'DATEBD' :
                    $da = strftime("%Y%m%d", $temp);
                    $ho = ($H) ? strftime("%H%M%S", $temp) : "";
                    $ts = $da . $ho;
                    break;
            }

            return $ts;

        }
        else
        {

            return NULL;
        }
    }

    /*
     *
     */

    public function datiAR($dias, $mesR, $sAno)
    {

        $ano = array();
        $year = strftime("%Y");
        for ($i = 0; $i < 108; $i++)
        {
            $ano[$i] = $year - $i;
        }
        $meses = array(
            'Jan' => "01",
            'Fev' => "02",
            'Mar' => "03",
            'Abr' => "04",
            'Mai' => "05",
            'Jun' => "06",
            'Jul' => "07",
            'Ago' => "08",
            'Set' => "09",
            'Out' => "10",
            'Nov' => "10",
            'Dez' => "12"
        );
        $dia = array(
            "01",
            "02",
            "03",
            "04",
            "05",
            "06",
            "07",
            "08",
            "09",
            "10",
            "11",
            "12",
            "13",
            "14",
            "15",
            "16",
            "17",
            "18",
            "19",
            "20",
            "21",
            "22",
            "23",
            "24",
            "25",
            "26",
            "27",
            "28",
            "29",
            "30",
            "31"
        );

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

        $ano = array();
        $year = strftime("%Y");
        for ($i = 0; $i < 108; $i++)
        {
            $ano[$i] = $year - $i;
        }
        $meses = array(
            'Jan' => "01",
            'Fev' => "02",
            'Mar' => "03",
            'Abr' => "04",
            'Mai' => "05",
            'Jun' => "06",
            'Jul' => "07",
            'Ago' => "08",
            'Set' => "09",
            'Out' => "10",
            'Nov' => "10",
            'Dez' => "12"
        );
        $dia = array(
            "01",
            "02",
            "03",
            "04",
            "05",
            "06",
            "07",
            "08",
            "09",
            "10",
            "11",
            "12",
            "13",
            "14",
            "15",
            "16",
            "17",
            "18",
            "19",
            "20",
            "21",
            "22",
            "23",
            "24",
            "25",
            "26",
            "27",
            "28",
            "29",
            "30",
            "31"
        );

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

            $this -> debug_json_ob($r_obj[$JS]);

            return $r_obj[$JS];

        }
        else
        {

            return FALSE;
        }
    }

    private function debug_json_ob($OB)
    {

        $ob_fields = array(
            "admin",
            "fields",
            "midia",
            "export",
            "search",
            "links"
        );

        foreach ($OB as $k_ob => $v_ob)
        {

            if (!in_array($k_ob, $ob_fields))
                trigger_error("Objeto json mal configurado. ", E_USER_ERROR);

            switch ($k_ob) {
                case 'admin' :
                    $this -> debug_admin($v_ob);
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

        $priv_fields = array(
            "toke",
            "icon",
            "identifier",
            "notes",
            "pubish",
            "order",
            "folder",
            "status"
        );
        $pub_fields = array(
            "date",
            "date_act"
        );

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
    /*
     * @ array LIST com o nome da redes sociais onde publicar
     *
     */
    public function social_publish($LIST)
    {

        if (!is_array($LIST))
            trigger_error("Lista de publicação invalida. ", E_USER_WARNING);

        foreach ($LIST as $value)
        {

            switch(strtolower($value)) {
                case "facebook" :
                    $this -> send_facebook($I, $J, $L);
                    break;
            }
        }

        /*foreach ($GLOBALS['LANGPAGES'] as $lang) {

         if (is_array($this -> ajs['export'][$lang])) {

         $id_note = parent::send_social($id, $this -> ajs['export'][$lang], $lang);

         if ($id_note) {

         logex::$tcnx -> query("UPDATE " . $this -> ajs['table'] . " SET notas = CONCAT_WS(',',notas,'$id_note') WHERE " . $toke['db'] . "=" . $id);
         }

         }
         }*/
    }

    /*
     * publica a noticia nas redes sociais
     * I = id do iten
     * J = objeto json
     * L = idioma
     */
    public function send_facebook($I, $J, $L)
    {
        if (parent::check())
        {

            $id = parent::id($I);

            if ($id)
            {

                extract($J);

                $rslt = logex::$tcnx -> query("SELECT client_id FROM redes WHERE id='facebook'");
                $faceid = $rslt -> fetch_array();

                $rslt = logex::$tcnx -> query('SELECT ' . $toke . ',' . $image . ',' . $title . ',' . $text . ' FROM ' . $table . ' WHERE ' . $toke . '=' . $id . ' LIMIT 1');

                $itens = $rslt -> fetch_array();

                $tit = $itens[$title];

                if ($tit)
                {

                    $mensagem = $itens[$title] . " \n " . $this -> cut_text($itens[$text], 150);
                    $descricao = $itens[$title] . " \n " . $this -> cut_text($itens[$text], 150);
                    $imagem = $this -> send_images_json($itens[$image], "src", NULL, 0);
                    $nome = $tit;
                    $linke = _RURL . $link . "/" . $itens[$toke] . "/" . $this -> clean_space($itens[$title]);

                    $mystring = "flag=feed&mensa=" . $mensagem . "&imagem=" . $imagem . "&id=" . $faceid[0] . "&descri=" . $descricao . "&link=" . $linke . "&nome=" . $nome;
                    $ch2 = curl_init();

                    curl_setopt($ch2, CURLOPT_URL, _SOCIALPUB);
                    curl_setopt($ch2, CURLOPT_POST, 1);
                    curl_setopt($ch2, CURLOPT_POSTFIELDS, $mystring);
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);

                    $post_message = curl_exec($ch2);

                    $report = ($post_message) ? "Mensagem para o idioma $L:<br>" . $post_message : "Publicado com sucesso no idioma $L.";

                    return $this -> insert_note("facebook", $report, $itens[$toke], "Publicação no Facebook");
                }
            }
        }
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
        $url = NULL;

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

                        $rsl = logex::$tcnx -> query("SELECT " . implode(",", $sitepages[$lang]['fields']) . " FROM " . $sitepages['table'] . " WHERE estado='online'");
                        while ($tl = $rsl -> fetch_array())
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

        $xm = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
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

class GestNotes
{
    /**
     * Guarda um nota na base de dados
     * 
     * @param string $type - uma categoria para a nota
     * @param string $content - conteudo da nota
     * @param string $title - titulo da nota
     * 
     * @return int id da ultima inserção
     */
    public function insert_note($type, $content,  $title)
    {
        
        $r_type = logex::$tcnx->real_escape_string($type);
        $r_content = logex::$tcnx->real_escape_string($content);
        $r_title = logex::$tcnx->real_escape_string($title);
        
        if(!logex::$tcnx -> query("INSERT INTO nota (tipo,conteudo,titulo,data) VALUES ('$r_type','$r_content','$r_title'," . $GLOBALS['NOW'] . ")"))
            return FALSE;
            
        return logex::$tcnx -> insert_id;
    }

    /**
     *
     * envia as notas
     *
     * @param string $notes_field nome do campo de notas
     * @param string $table nome da tabela
     * @param string $id_field nome do campo de identificação
     * @param int $id identificação do item
     * @return string HTML
     */
    public function do_notes($notes_field, $table, $id_field, $id)
    {
        if ($this -> check())
        {

            $id = $this -> id($id);
            $n = NULL;

            $q = logex::$tcnx -> query("SELECT $notes_field FROM " . $table . " WHERE $id_field = '$id'");
            $r = $q -> fetch_array();
            $n = trim($r[0], ",");
            $n = explode(",", $n);
            $n = array_reverse($n);
            $n = implode(",", $n);

            return $this -> make_notes($n);
        }
    }
    /**
     * Regista uma nota no respectivo item
     * 
     * @param string $table - nome da table onde registar a nota
     * @param string $items - string do tipo (id_nota, id_item),...,(id_nota, id_item)
     * 
     * @return bollean
     * 
     */
    public function register_note($table, $item){
 var_dump("INSERT INTO $table (nota_id,item_id) VALUES $item")   ;    
        if(!logex::$tcnx->query("INSERT INTO $table (nota_id,item_id) VALUES $item"))
            return FALSE;
        
        return TRUE;
        
    }
    /**
     * cria as notas
     * @param string $notes id das notas separados por virgulas
     * @return boolean || string HTML
     */
    public function make_notes($notes)
    {
        if ($this -> check())
        {
            if (!$notes)
            {

                return FALSE;
            }
            $y = NULL;

            $qq = logex::$tcnx -> query("SELECT data_envio,nome,relatorio,filtro FROM notas WHERE id IN(" . $notes . ") ORDER BY data_envio DESC");

            while ($rr = $qq -> fetch_array())
            {

                switch ($rr[3]) {
                    case "facebook" :
                        $y .= "<div class='dvBf'>
                                          <p class='p150'>
                             <span>" . $this -> date -> make_date($rr[0]) . "</span>
                             <span class='spm10'>" . $rr[1] . "</span>              
                          </p>
                                          <div class='dvBm'>
                                                " . nl2br($rr[2]) . "
                                            </div>
                                       </div>";
                        break;
                    case "newsletter" :
                        $y .= " <div class='dvBf'>
                                            <p class='p150'>
                                                <span>" . $this -> date -> make_date($rr[0]) . "</span>
                                                <span class='spm10'>Foi enviada a newsletter \"$rr[1]\"</span>              
                                            </p>
                                            <div class='dvBm'>
                                                " . nl2br($rr[2]) . "
                                            </div>
                                        </div>";
                        break;
                }
            }

            return $y . "<div class='rodape'></div>";
        }
    }

    public function notes($k, $m)
    {

        $cp = NULL;
        $cx = NULL;
        $tx = NULL;
        $p = "/($k\d+)/";
        $rt = preg_match_all($p, $m, $cp, PREG_PATTERN_ORDER);

        for ($c = 0; $c < count($cp[0]); $c++)
        {
            $tx = explode($k, $cp[0][$c]);
            $cx .= ",$tx[1]";
        }
        if ($rt > 0)
        {
            return $cx . "%%%" . $rt;
        }
        else
        {
            return false;
        }
    }

}

class GestOrder extends Core
{

    private $table;
    private $image;
    private $name;
    private $order_index;
    private $toke;

    public function __construct($config)
    {

        parent::__construct();

        $this -> table = $config['table'];
        $this -> image = $config['admin']['private']['icon']['db'];
        $this -> name = $config['admin']['private']['identifier']['db'];
        $this -> order_index = $config['admin']['private']['order']['db'];
        $this -> toke = $config['admin']['private']['toke']['db'];

    }

    /**
     * Cria um objecto json para iniciar o modulo de ordenacão
     *
     * @param string $ID campo de identificação da bd
     * @param string $IM campo de imagens da bd
     * @param string $NA campo de nome da bd
     * @param string $TB nome da tabela
     * @param string $OI campo da bd que controla a ordenação
     *
     * @return object Json
     */
    public function make_order()
    {
        if (parent::check())
        {

            $q = logex::$tcnx -> query("SELECT $this->toke,$this->image,$this->name FROM $this->table ORDER BY $this->order_index DESC");

            $or = NULL;

            $images = new GestImage();

            while ($od = $q -> fetch_array())
            {

                $img = ($od[1]) ? '"' . $images -> send_images_json($od[1], "src", NULL, 0, NULL) . '"' : '""';
                $or .= ",[\"$od[0]\",$img,\"$od[2]\"]";
            }

            return '{"result":[' . ltrim($or, ",") . ']}';
        }
    }

    /**
     * Guarda a nova ordenação
     *
     * @return object Json
     */
    public function save_new_order()
    {
        if ($this -> check())
        {

            $io = array_reverse($_POST['iorder']);
            $n = count($io);

            for ($f = 0; $f < $n; $f++)
            {
                logex::$tcnx -> query("UPDATE " . $this -> table . " SET " . $this -> order_index . "='$f' WHERE " . $this -> toke . "=" . $io[$f]);
            }

            return "{\"order\":1}";
        }
    }

}

class GestSearch extends Core
{

    private $search;

    private $table;

    public function __construct($config)
    {

        parent::__construct();

        $this -> search = FALSE;

        if (!empty($config['search']))
        {

            $this -> search = $config['search'];
            $this -> table = $config['table'];
        }

    }

    /**
     *
     * @param array $ob
     * @param string $table nome da tabele ade busca
     * @return string json
     */
    public function search()
    {
        if (!parent::check())
            return '{"alert":"Não foi possivel realizar esta operação. - GSCH' . __LINE__ . '"}';

        $r = NULL;
        $query = NULL;

        foreach ($this->search ['query'] as $key => $value)
        {

            if (is_array($value))
            {

                $sub_query = NULL;

                foreach ($value as $field)
                {

                    if (!isset($_POST[$key]))
                        continue;

                    $post_value = $this -> validate_name($_POST[$key]);

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

                        if ($_POST[$key] && $this -> validate_name($_POST[$key]))
                            $query .= " AND " . $value . " LIKE '%" . $_POST[$key] . "%'";
                    }
                    else
                    {

                        if ($_POST[$key] && $this -> validate_name($_POST[$key]))
                            $query = " " . $value . " LIKE '%" . $_POST[$key] . "%'";
                    }
                }
            }
        }

        if ($query)
        {

            $order = (isset($this -> search['order']) && $this -> search['order']) ? " ORDER BY " . $this -> search['order'] . " ASC" : "";

            $query = "SELECT " . $this -> search['toke'] . "," . $this -> search['fields'] . " FROM " . $this -> table . " WHERE " . $query . $order;

            $rslt = logex::$tcnx -> query($query);

            while ($cResg = $rslt -> fetch_array())
            {

                $r .= ',{"id":"' . $cResg[0] . '","fields":["' . $cResg[1] . '","' . $cResg[2] . '","' . $cResg[3] . '"]}';
            }

            return '{"result":[' . ltrim($r, ",") . ']}';
        }
        else
        {

            return '{"result":[]}';
        }

    }

}

class GestMessages extends Core
{

    //objeto que gere as datas
    private $date;

    public function __construct()
    {

        parent::__construct();

        $this -> date = new GDate();
    }

    public function get_messages($item_id, $module_name)
    {

        if (!parent::check())
            return parent::mess_alert("GMS" . __LINE__);

        $id = $this -> id($item_id);

        if (!$id || empty($module_name))
            return parent::mess_alert("GMS" . __LINE__);

        $condition = " mensagens.id_item=" . $id . " AND mensagens.filtro='" . $module_name . "'";

        if ($module_name === "contacts")
            $condition = " mensagens.id_contacto=" . $id;

        return $this -> fetch_messages($condition);

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

        if (!$result = logex::$tcnx -> query($query))
            return parent::mess_alert("GMS" . __LINE__);

        $message = NULL;

        while ($mess = $result -> fetch_array())
        {

            $mess_date = $this -> date -> make_date($mess[1]);

            $message .= html_entity_decode('["' . $mess[0] . '","' . $mess_date . '","' . $mess[2] . '","' . $mess[3] . '","' . $mess[4] . '","' . $mess[5] . '"],');
        }

        return '{"result":[' . trim($message, ",") . ']}';

    }

    public function show_messages($idx = NULL, $filter = NULL)
    {

        if (!parent::check())
            return parent::mess_alert("GMS" . __LINE__);

        $cond = NULL;

        $id = $this -> id($idx);

        $fields = array(
            "subject" => "mensagens.assunto",
            "date" => "mensagens.data",
            "mail" => "contactos.mail",
            "name" => "contactos.nome"
        );

        if (isset($_POST['pasta']))
        {

            $folder = $this -> validate_name(urldecode($_POST['pasta']));
            $cond = ($folder) ? "AND mensagens.pasta='$folder'" : "AND mensagens.pasta=''";

        }

        if (isset($_POST['op']) && $_POST['op'] && array_key_exists($_POST['op'], $fields))
        {

            $cond = "AND  " . $fields[$_POST['op']] . " LIKE '" . $_POST['valor'] . "%'";

        }
        else
        {

            foreach ($fields as $key => $value)
            {

                if (isset($_POST[$key]))
                {
                    if ($cond)
                    {
                        if ($_POST[$key] && $this -> validate_name($_POST[$key]))
                        {

                            $cond .= " AND " . $value . " LIKE '%" . $_POST[$key] . "%'";
                        }
                    }
                    else
                    {
                        if ($_POST[$key] && $this -> validate_name($_POST[$key]))
                            $cond = " AND " . $value . " LIKE '%" . $_POST[$key] . "%'";
                    }
                }
            }
        }
        if ($id && $filter)
        {

            $cond = ($filter === "contacts") ? "AND mensagens.id_contacto=" . $id : "AND mensagens.id_item=" . $id . " AND mensagens.filtro='" . $filter . "'";
        }

        $query = "SELECT mensagens.id,mensagens.data,mensagens.assunto,mensagens.flag,contactos.nome,contactos.mail,mensagens.pasta FROM contactos,mensagens WHERE mensagens.id_contacto=contactos.id $cond  ORDER BY mensagens.data DESC";

        $bd = logex::$tcnx -> query($query);

        $mess = NULL;

        while ($result = $bd -> fetch_array())
        {

            $mess .= html_entity_decode('["' . $result[0] . '","' . $this -> date -> make_date($result[1]) . '","' . $result[2] . '","' . $result[3] . '","' . $result[4] . '","' . $result[5] . '"],');
        }

        return '{"result":[' . trim($mess, ",") . ']}';

    }

    public function del_message($id_message)
    {

        if (!parent::check())
            return parent::mess_alert("GMS" . __LINE__);

        if (!$id = $this -> id($id_message))
            return parent::mess_alert("GMS" . __LINE__);

        if (!logex::$tcnx -> query("DELETE FROM mensagens  WHERE id='$id'"))
            return parent::mess_alert("GMS" . __LINE__);

        return '{"result":' . $id . '}';
    }

    public function read_message()
    {
        if ($this -> check())
        {

            $id = $this -> id($_POST['toke']);

            if ($id)
            {

                logex::$tcnx -> query("UPDATE mensagens SET flag='s' WHERE id='$id'");

                $query = "SELECT contactos.nome, contactos.apelido,contactos.mail,mensagens.assunto,mensagens.texto,mensagens.data,mensagens.anexo,contactos.empresa,contactos.id FROM contactos,mensagens WHERE mensagens.id_contacto=contactos.id AND mensagens.id='$id'";

                $bd = logex::$tcnx -> query($query);
                $result = $bd -> fetch_array();

                $nome = $result[0] . " " . $result[1];

                $mensagem = '{"result":["' . $id . '","' . $nome . '","' . $result[2] . '","' . $result[3] . '","' . nl2br($result[4]) . '","' . $this -> date -> make_date($result[5]) . '","' . $result[6] . '","' . _ANEXOSURL . '"]}';

                return html_entity_decode($mensagem);
            }
        }
    }

}
