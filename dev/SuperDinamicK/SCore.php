<?php

/* SCRIPT SCore.php V1.8
  16-08-2013
  COPYRIGHT MANUEL GERARDO PEREIRA 2013
  TODOS OS DIREITOS RESERVADOS
  CONTACTO: GPEREIRA@MGPDINAMIC.COM
  WWW.MGPDINAMIC.COM */
ini_set('display_errors', 1);
require_once 'SConfig.php';
require_once 'SMessages.php';
require_once 'Anti.php';
/*
 * 8/10/2013 introduzidos os metodos de criptografia mgpencrypt e mgpdecrypt
 *
 */
class SCore {

    private $dat;
    private $anti;

    public function __construct() {

    }

    /*
     * retira aspas no inicio e no fim da string
     */

    public function Strin($s) {

        $S = ltrim($s, '"');
        $S = rtrim($S, '"');
        return $S;
    }

    /*
     * limita o comprimento de um texto e retira-lhe a formatação
     * $T=texto
     * $L=comprimento desejado
     * $F=true para manter a formatação
     */

    public function cut_text($T, $L, $F) {

        if ($T) {
            if ($F) {

                $conte = $T;
            } else {

                $conte = strip_tags($T);
                $conte = preg_replace('/\x5c/', ' ', $conte);
            }

            $len = strlen($conte);

            if ($len > ($L - 1)) {
                $var = $conte;
                $i = $L;
                $spacer = substr($var, $i, 1);
                while ($spacer != " ") {
                    $spacer = substr($var, $i, 1);
                    if ($spacer == " ") {
                        break;
                    }
                    $i++;
                }

                $cont = substr($var, 0, $i) . "...";
            } else {
                $cont = $conte;
            }

            return $cont;
        }
    }
public function clean_space($T){

    return str_replace(" ", "-", $T);
}
////////////////////////////////////////////////////////////////////////////////

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
            throw new Exception("IMG" . __LINE__, 1);

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

                if ($mode === "imgarr")
                {
                    $captions = (isset($captions_lang)) ? trim($value[$captions_lang], '"') : "";
                    $result[] = "<img src=" . $value['photo'] . " alt='" . $captions . "' title='" . $captions . "' $html_attributes>";
                }

                if ($mode === "arr")
                {
                    $captions = (isset($captions_lang)) ? trim($value[$captions_lang], '"') : "";
                    $comma = ($result) ? ","  : "";
                    $result .= $comma.'["' . $value['photo'] . '" ,"' . $captions . '"]';
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
     * cria fotogaleria de imgens que usam legenda como atributo alt
     * $F = array javascript  de fotos
     * $A = atributos html
     * $N = se FALSE só retorna a primeira foto
     * $T = pode ter dois valores "src" para devolver apenas o endereço da imagem ou "img" para devolver a tag completa, "arr" para uma array javascript, "imgarr" para uma array php com img html
     * $L = idioma
     */
    public function send_images_json_i($F, $T, $L, $N = 1, $A = NULL) {

        $h = NULL;
        $incr = 0;

        if ($F) {

            $pic = json_decode($F, TRUE);

            if ($pic && is_array($pic) && is_array($pic['photos'])) {

                foreach ($pic['photos'] as $value) {

                    if ($T === "img") {

                        $c = (isset($L)) ? trim($value[$L], '"') : "";
                        $h .= "<img src=" . $value['photo'] . " alt='" . $c . "' title='" . $c . "' $A>";
                    }
                    if ($T === "imgarr") {

                        $c = (isset($L)) ? trim($value[$L], '"') : "";
                        $h[$incr++] = "<img src=" . $value['photo'] . " alt='" . $c . "' title='" . $c . "' $A>";
                    }
                    if ($T === "arr") {

                        $c = (isset($L)) ? trim($value[$L], '"') : "";
                        $h .= '["' . $value['photo'] . '" ,"' . $c . '"],' ;
                    }

                    if ($T == "src") {

                        $h .= $value['photo'];
                    }

                    if (!$N)
                        return $h;
                }

                if ($T === "arr") {

                   $h = trim($h,",");
                }

                return $h;
            }
            if ($T === "img" && $F) {

                return "<img src=" . $F . "  $A>";
            } else {
                return NULL;
                //return trim($F,'"');
            }
        }
        return NULL;
    }
    /*
     * transforma a string de imagens da base de dados
     * em elemento html img
     * $FN = string da base de dados com as fotos
     * $AT = atributos html a colocar
     */

    public function makeImagesCol($FN, $AT) {
        //manipula as fotos da noticia transformando a array numa string html

        $fotosMural = array();
        $at = ($AT) ? $AT : "";
        if ($FN) {
            $fotosMural = explode(",", $FN);
            $len = count($fotosMural);
            if ($len > 0) {
                for ($i = 0; $i < $len; $i++) {
                    $fts .= "<img src='" . $this->Strin($fotosMural[$i]) . "' $at>";
                }
            }
        }
        return $fts;
    }

    /*
     * cria a fotogaleria da ficha
     * $F = array javascript  de fotos
     * $A = atributos html
     */

    public function makePhotoGalleryFicha($FN, $A) {
        //limpa marcação de array javascript
        $F = str_replace('["', '', $FN);
        $F = str_replace('"]', '', $F);
        $fotosMural = array();
        if ($FN) {
            $fotosMural = explode(",", $F);
            $len = count($fotosMural);
            if ($len > 0) {

                for ($i = 0; $i < $len; $i++) {
                    $fot = str_replace("mini", "", $fotosMural[$i]);
                    $h .= $this->makeImagesCol($fot, $A);
                }
                return $h;
            }
        }
    }

    /*
     * cria fotogaleria de imgens que usam legenda como atributo alt
     * $F = array javascript  de fotos
     * $A = atributos html
     * $N = se TRUE só retorna a primeira foto
     * $T = pode ter dois valores "src" para devolver apenas o endereço da imagem ou "img" para devolver a tag completa
     * $NL = numero que representa a lingua da legenda
     */

    public function makePhotoGalleryWC($FN, $A, $N, $T, $NL) {

        preg_match_all('|("[^\[\]]+")|', $FN, $out, PREG_PATTERN_ORDER);

        if ($FN) {
            if ($N) {

                $i = explode(",", $out[0][0]);

                if ($T == "img") {
                    return "<img src=" . $i[0] . " alt='" . $this->Strin($i[$NL]) . "' $A>";
                }
                if ($T == "src") {
                    return $this->Strin($F);
                }
            } else {
                foreach ($out[0] as $images) {

                    $img = explode(",", $images);

                    if ($T == "img") {
                        $h .= "<img src=" . $img[0] . " alt=" . $img[$NL] . "  $A>";
                    }
                    if ($T == "src") {
                        $h .= "," . $img[0];
                    }
                }
            }
            return $h;
        }
    }

    /*
     * retorna o nome do pais
     */

    public function nomePais($id) {
        $aQuery = "SELECT pais FROM paises WHERE iso ='$id'";
        $bQuery = mysql_query($aQuery);
        $c = mysql_fetch_array($bQuery);
        return $c[0];
    }

    /*
     * retorna o nome da cidade
     */

    public function nomeCidade($id) {
        $aQuery = "SELECT cidade FROM cidades WHERE id ='$id'";
        $bQuery = mysql_query($aQuery);
        $c = mysql_fetch_array($bQuery);
        return $c[0];
    }

    public function searchCombo($campo, $tabela, $valor) {
        $aQuery = "SELECT DISTINCT $campo FROM  $tabela WHERE $campo <> '' ORDER BY $campo ASC";
        $bQuery = mysql_query($aQuery);

        while ($cQuery = mysql_fetch_array($bQuery)) {
            $text = ($campo == "cidade") ? $this->nomeCidade($cQuery[0]) : $cQuery[0];
            if ($cQuery[0] == $valor) {
                echo "<option value='$valor' selected='selected' >$text</option>";
            } else {
                echo "<option value='$cQuery[0]' >$text</option>";
            }
        }
    }

/////////////////////////////////////////////REDES SOCIAIS//////////////////////////////////////////////////////////////////////////
    /*
     * cria os botões para as redes sociais
     * $url = link para a página
     * $turl = link curto para ser usado no twitter e redes com limitações de caracteres
     * $txt = texto descritivo
     * $img = imagem
     * $slk = se TRUE mostra o botão linkedin
     */
    public function social_buttons($url, $turl, $txt, $img, $slk) {

        $lkd = ($slk) ? "<div class='butpin'>
						<script src='//platform.linkedin.com/in.js' type='text/javascript'></script>
						<script type='IN/Share' data-url='" . $url . "'></script>
					</div>" : "";

        return "

 		<div class='isocial'>

					<div class='butface'>
						<div class='fb-like' data-href='" . $url . "' data-send='true' data-width='100' data-show-faces='false'  data-layout=\"button_count\">
						</div>
					</div>
					<div class='butpin'>
						<a href='http://pinterest.com/pin/create/button/?url=" . $url . "&media=" . $img . "&description=" . $txt . "' class='pin-it-button' count-layout='none'><img border='0' src='//assets.pinterest.com/images/PinExt.png' title='Pin It' /></a>

					</div>
					<div class='butgmais'>
						<div class='g-plusone' data-size='medium' data-annotation='none' ></div>
						<script type='text/javascript'>
						  window.___gcfg = {lang: 'pt-PT'};

						  (function() {
						    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
						    po.src = 'https://apis.google.com/js/plusone.js';
						    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
						  })();
						</script>
					</div>
					<div class='butpin'>
							<a href='https://twitter.com/share?url=" . $turl . "' class='twitter-share-button' data-lang='en' data-url='" . $turl . "' data-counturl='" . $turl . "' data-text='" . $this->cut_text($txt, 90, FALSE) . "' data-count='none'>Tweet</a>
							<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='//platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');</script>
						</div>
						$lkd
				</div>

 	";
    }

////////////////////////////////////////////TRATAMENTO DE MENSAGENS////////////////////////////////////////////////////////////////
    public function saveContact() {

        $tcnx = new mysqli(_HT, _US, _PS, _DB);
        $tcnx->set_charset("utf8");

        $idx = NULL;

        $query = "SELECT id,mail FROM " . _CONTCTB . " WHERE mail='" . $this->dat['mail'] . "' LIMIT 1;";
        $query .= "SELECT mail FROM " . _EMPTB . " WHERE id=1 LIMIT 1";

        if ($tcnx->multi_query($query)) {

            $rslt = $tcnx->store_result();
            $resultMail = $rslt->fetch_array();
            $rslt->free();
            $tcnx->next_result();
            $rslt = $tcnx->store_result();
            $tMail = $rslt->fetch_array();
            $this->dat['sendMail'] = $tMail[0];
            $rslt->free();
        }

        $tfields = json_decode(_MESSFIELDS, TRUE);

        if (!$resultMail['id']) {

            $fld = "";
            $vld = "";

            foreach ($tfields as $key => $value) {
                if(isset($this->dat[$key])){
                $fld .= $value . ", ";
                $vld .= "'" . $this->dat[$key] . "',";}
            }

            $queryGuardaContacto = $tcnx->query("INSERT INTO " . _CONTCTB . " ( $fld categoria,imagem,pasta,data) VALUES ( $vld 'pessoa','" . _CONTCIMG . "','" . _CONTCFOLDER . "'," . $GLOBALS['NOW'] . ")");

            if ($queryGuardaContacto) {

                $idx = ($tcnx->insert_id) ? $tcnx->insert_id : FALSE;
            }
        } else {

            $fld = "";

            foreach ($tfields as $key => $value) {

                if (isset($this->dat[$key])) {

                    $fld .= $value . "='" . $this->dat[$key] . "',";
                }
            }

            $tcnx->query("UPDATE " . _CONTCTB . " SET $fld data_act=" . $GLOBALS['NOW'] . "  WHERE id='$resultMail[id]'");

            $idx = $resultMail['id'];
        }

        $tcnx->close();

        if ($this->saveMessage($idx)) {

            return TRUE;
        } else {

            return FALSE;
        }
    }

    private function saveMessage($id) {

        $tcnx = new mysqli(_HT, _US, _PS, _DB);
        $tcnx->set_charset("utf8");

        $qMensagem = "INSERT INTO " . _MESSTB . " (id_contacto,id_item,filtro,data,assunto,texto,anexo) VALUES ('$id','" . $this->dat['id_produto'] . "','" . $this->dat['tipo_produto'] . "','" . $GLOBALS['NOW'] . "','" . $this->dat['assunto'] . "','" . $this->dat['mensagem'] . "','" . $this->dat['anexo'] . "')";
        $qMensagem2 = $tcnx->query($qMensagem);

        if ($qMensagem2) {

            return TRUE;
        } else {

            return FALSE;
        }

        $tcnx->close();
    }

    public function sendMail($json) {


        $this->dat = json_decode($json, TRUE);

        if ($this->saveContact()) {

            $headers = "Return-Path:" . $this->dat['sendMail'] . "\r\n";
            $headers .= "From:" . $this->dat['sendMail'] . "\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "MIME-Version: 1.0\r\n";

            $ass = html_entity_decode($this->dat['assunto'], ENT_QUOTES, "UTF-8");
            $mes = html_entity_decode($this->dat['mensagem'], ENT_QUOTES, "UTF-8");

            $m = imap_mail($this->dat['sendMail'], $ass, $mes, $headers);

            if ($m) {

                return TRUE;

            } else {

                return FALSE;
            }
        } else {

            return FALSE;
        }
    }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
     * manipula a data
     * $D = data
     * $S = separador
     * $H = true mostra horas
     * $U = unix timestamp
     */
    public function make_date($D = NULL, $H = 1, $U = 'DATE', $S = "-") {

        if ($D) {

            $dt = date_parse($D);

            $temp = ($dt['warning_count'] > 0) ? NULL : mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']);
        } else {

            $temp = time();
        }

        if ($temp) {

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
        } else {
            return NULL;
        }
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  public function make_video($V, $M, $C = "newsvideo", $W = 600, $H = 409) {

        if ($V) {
            if ($M == "embeded") {

                return "<div class='$C'>$V</div>";
            }
            if ($M == "fromfile") {

                $pt = urlencode(_VIDEOURL);

                return '
                        <div class="'.$C.'">
                            <object width="'.$W.'" height="'.$H.'">
                                <param name="wmode" value="transparent"></param>
                                <param name="movie" value="http://fpdownload.adobe.com/strobe/FlashMediaPlayback.swf"></param>
                                <param name="flashvars" value="src='.$pt.$V.'"></param>
                                <param name="allowFullScreen" value="true"></param>
                                <param name="allowscriptaccess" value="always"></param>
                                <embed src="http://fpdownload.adobe.com/strobe/FlashMediaPlayback.swf" type="application/x-shockwave-flash" wmode="transparent" allowscriptaccess="always" allowfullscreen="true" width="'.$W.'" height="'.$H.'" flashvars="src='.$pt.$V.'"></embed>
                            </object>
                        </div>';
            }
        }
    }
public function mgpencrypt($text,$cipher="blowfish",$mode="ofb"){

    if(extension_loaded("mcrypt")){

        $td = mcrypt_module_open($cipher, "", $mode, "");

        if($td){

                $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_DEV_RANDOM);

                $key = substr(md5(sha1(_SOMEK)), 0, mcrypt_enc_get_key_size($td));

                mcrypt_generic_init($td, $key, $iv);

                $enc = mcrypt_generic($td, $text);

                mcrypt_generic_deinit($td);
                mcrypt_module_close($td);

                return base64_encode($iv.$enc);

        } else {

            return FALSE;
        }


    } else {

        return FALSE;
    }
}

public function mgpdecrypt($text,$cipher="blowfish",$mode="ofb"){

    if(extension_loaded("mcrypt")){

    $td = mcrypt_module_open($cipher, "", $mode, "");

    $iv_size = mcrypt_enc_get_iv_size($td);

    $dtext = base64_decode($text);

    $iv = substr($dtext, 0,$iv_size);

    $mkey = substr(md5(sha1(_SOMEK)), 0, mcrypt_enc_get_key_size($td));

    mcrypt_generic_init($td, $mkey, $iv);

    $dec = mcrypt_generic($td, substr($dtext, $iv_size));

    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);

    return $dec;

    } else {

        return FALSE;
    }
}

public function verify_delete_mail($tok){
    try{




    $tcnx = new mysqli(_HT, _US, _PS, _DB);
    $tcnx -> set_charset("utf8");

    $res = $this->mgpdecrypt($tok, "_RURL");
    $js = json_decode($res,TRUE);
    try{

        $qcont = $tcnx -> query("SELECT mail FROM contactos WHERE id=".$js['id']);

    $rcont = $qcont -> fetch_array();


    } catch(Exception $e){

    return "erro";
}






    return $rcont[0];
    throw new Exception();

} catch(Exception $e){

    return $e;
}

}

public function unsubscrive_newsletter($tok){

    $tcnx = new mysqli(_HT, _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

    $res = $this->mgpdecrypt($tok, "_RURL");

    $js = json_decode($res,TRUE);

    $qcont = $tcnx -> query("SELECT mail FROM contactos WHERE id=".$js['id']);

    $rcont = $qcont -> fetch_array();

    if(md5($rcont[0])===$js['email']){

       if( $tcnx -> query("UPDATE contactos SET send_news=0 WHERE id=".$js['id'])){

         return  '{"data":"'._L_REMOVEMESS.'"}';
       }



    }
}
protected function define_sql_conditions($cond_obj){

    if(is_array($cond_obj)){

        $condition = NULL;

        foreach ($cond_obj as $key => $value) {

            if($condition){

                $condition .= " AND ". $key ."='". $value ."' ";

            } else{

                $condition = " WHERE ". $key ."='". $value ."' ";
            }

        }

        return $condition;

    } else {

        return FALSE;
    }
}
protected function define_sql_order($order_obj){

    if(is_array($order_obj)){

        $order = NULL;

        foreach ($order_obj as $key => $value) {

            if($order){

                $order .= " , " . $key ." ". $value ." ";

            } else{

                $order = " ORDER BY ". $key ." ". $value ." ";
            }

        }

        return $order;

    } else {

        return FALSE;
    }
}
}

?>
