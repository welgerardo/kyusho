<?php
/* SCRIPT SComuns.php V1.0 modelo ref_0001
 21-08-2012
 COPYRIGHT MANUEL GERARDO PEREIRA 2012
 TODOS OS DIREITOS RESERVADOS
 CONTACTO: GPEREIRA@MGPDINAMIC.COM
 WWW.MGPDINAMIC.COM */
ini_set('display_errors', 1);
require_once 'SCore.php';
require_once 'SConfig.php';
require_once 'Anti.php';

/*
 * metodo form_message_plus alterado em 23-09-2013 foi incluida a possibilidade de apresentar o formulario dentro de uma div escondida (zomm)
 *
 */

class SComuns extends SCore
{

    public $pagetitle;
    public $imageEnder;
    public $principal;
    public $config;
    public $content;
    public $table;
    //tabela origem dos dados
    public $link;
    //endereço para item individual

    public $seox;
    public $soc;
    private $lang;
    private $anti;
    public $lg;

    public function __construct()
    {

        $this -> lang = (isset($_GET['lang']) && in_array($_GET['lang'], $GLOBALS['LANGPAGES'])) ? $_GET['lang'] : $GLOBALS['LANGPAGES'][0];

        require_once "SLang" . strtoupper($this -> lang) . ".php";

        $this -> anti = new Anti();

        $tcnx = new mysqli(_HT, _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        $query = "SELECT * FROM " . _CONFIGTB . " WHERE id=1 LIMIT 1;";
        $query .= "SELECT * FROM " . _EMPTB . " WHERE id=1 LIMIT 1";

        if ($tcnx -> multi_query($query))
        {

            $rslt = $tcnx -> store_result();
            $this -> config = $rslt -> fetch_array();
            $rslt -> free();
            $tcnx -> next_result();
            $rslt = $tcnx -> store_result();
            $this -> principal = $rslt -> fetch_array();
            $rslt -> free();
        }
    }

    /**
     *
     * @return type
     */
    public function get_language()
    {

        return $this -> lang;
    }

    /**
     *
     * @param type $page
     * @return boolean
     */
    public function seo($page)
    {

        $rseo = array();
        $fields = NULL;

        $mode = json_decode(_SEO, TRUE);

        if (!$mode)
            return FALSE;

        $tcnx = new mysqli(_HT, _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        foreach ($mode['fields'] as $key => $value)
        {

            $fields .= $value . ",";
            $$key = $value;
        }

        $query = "SELECT " . trim($fields, ",") . " FROM " . $mode['table'] . " WHERE id='$page' LIMIT 1;";
        $query .= "SELECT * FROM redes WHERE id='Google' LIMIT 1;";
        $query .= "SELECT * FROM redes WHERE id='Facebook' LIMIT 1";

        if ($tcnx -> multi_query($query))
        {

            $rslt = $tcnx -> store_result();
            $seor = $rslt -> fetch_array();
            $rslt -> free();
            $tcnx -> next_result();
            $rslt = $tcnx -> store_result();
            $goo = $rslt -> fetch_array();
            $rslt -> free();
            $tcnx -> next_result();
            $rslt = $tcnx -> store_result();
            $face = $rslt -> fetch_array();
            $rslt -> free();
        }

        $tcnx -> close();

        $rseo['title'] = $seor[$title];
        $rseo['descri'] = $seor[$descri];
        $rseo['keywords'] = $seor[$keywords];
        $rseo['g_analytics'] = $goo['g_analytics'];
        $rseo['app_face'] = $face['app_key'];
        $rseo['adm_face'] = $face['adm_face'];

        return $rseo;
    }

    public function home()
    {

        $fields = NULL;

        $home = NULL;

        if (!$mode = json_decode(home, TRUE))
            return NULL;

        $tcnx = new mysqli(_HT, _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        foreach ($mode['fields'] as $key => $value)
        {

            $$key = $value;
        }

        $fields = implode(",", $mode['fields']);

        if (!$rh = $tcnx -> query("SELECT * FROM " . $mode['table'] . " WHERE pagina = 'home'"))
            return NULL;

        while ($hm = $rh -> fetch_array())
        {

            switch (strtolower($hm['nome'])) {
                case "banners" :
                    $home['banner'] = NULL;
                    $hx = $this -> send_images_json($hm[$mode['fields']['image']], "imgarr", "pt", 1, "data-fixed class='banner_img'");
                    foreach ($hx as $value)
                    {
                        $home['banner'] .= "<div class='slide' data-in='fade' data-out='fade'>$value</div>  ";
                    }
                    break;
                case "message" :
                    $home['message'] = $this -> send_images_json($hm[$image], "img", "pt") . "<h1>" . $hm[$text] . "</h1>";
                    break;
                case "baia 1" :
                    $home['box1']['title'] = $hm[$mode['fields']['title']];
                    $home['box1']['text'] = $hm[$mode['fields']['text']];
                    break;
                case "baia 2" :
                    $home['box2']['title'] = $hm[$mode['fields']['title']];
                    $home['box2']['text'] = $hm[$mode['fields']['text']];
                    break;
                case "video" :
                    $jv = json_decode($hm[$video], TRUE);
                    $home['video'] = $this -> make_video($jv['video'], $jv['from']);
                    break;
                case "logos" :
                    $home["logos"] = $this -> send_images_json($hm[$mode['fields']['image']], "img", "pt", 1, "class='clientesimg'");
                    break;
            }
        }

        $rh -> free();
        $tcnx -> close();

        return $home;
    }
    private function get_banner()
    {
        $home['banner'] = NULL;
        $hx = $this -> send_images_json_i($hm[$image], "imgarr", "pt", 1, "data-fixed class='banner_img'");

        foreach ($hx as $value)
        {
            $home['banner'] .= "<div class='slide' data-in='fade' data-out='fade'>$value</div>  ";
        }

    }

    public function foot_pages($pagi)
    {

        $mode = json_decode(_OTHERPAGES, TRUE);

        $tcnx = new mysqli(_HT, _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        $fields = NULL;

        foreach ($mode['fields'] as $key => $value)
        {

            $fields .= $value . ",";
            $$key = $value;
        }

        $rh = $tcnx -> query("SELECT " . trim($fields, ",") . " FROM " . $mode['table'] . " WHERE $page = '$pagi'");

        $hm = $rh -> fetch_array();

        $ft = array();

        $ft['text'] = $hm[$text];

        $ft['title'] = $hm[$title];

        $ft['image'] = $hm[$image];

        return $ft;

    }

    public function company()
    {

        $mode = json_decode(home, TRUE);

        $tcnx = new mysqli(_HT, _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        $jscomp = json_decode(empresa, TRUE);

        extract($jscomp);

        $rcomp = $tcnx -> query("SELECT * FROM " . $jscomp['table'] . " WHERE id=1 LIMIT 1");
        $comp = $rcomp -> fetch_array();

        $Q = array();

        foreach ($jscomp['fields'] as $value)
        {

            $Q[text] .= "
            <div class='oqf'>
                <p>" . $value[1] . "</p>

            </div>
            <div class='s1'>
            " . $comp[$value[0]] . "
            </div>";
        }

        $Q['image'] = $this -> send_images_json($comp[$jscomp['fields']['image'][0]], "img", $lg, 0);

        return $Q;
    }

    public function top()
    {
        $nav = NULL;

        $lgx = $this -> send_images_json($this -> principal['logotipo'], "img", "pt");

        $lg = ($lgx) ? "<a href='" . _RURL . "' class='alogo'>$lgx</a>" : "";

        $nv = json_decode(_NAV, TRUE);

        $sel = (isset($_GET['select']) && in_array($_GET['select'], $GLOBALS['WHITELIST'])) ? $_GET['select'] : "";

        foreach ($nv as $linq => $name)
        {

            $selec = ($sel === $linq) ? "selectli" : "";
            $nav .= "
                <li class='flir'>
                    <a class='fla $selec' href='" . _RURL . "$this->lang/$linq'>
                        $name
                    </a>
                </li>";
        }

        return '
            <div id="wtop_container">
                <div id="top_container">
                    <div id="logo">
                        ' . $lg . '
                    </div>
                    <div id="nav">
                        <ul class="sul">
                           ' . $nav . '
                        </ul>
                    </div>
                </div>
            </div>
        ';
    }

    public function foot()
    {

        $nav = NULL;

        $nv = json_decode(_F_NAV, TRUE);

        foreach ($nv as $linq => $name)
        {
            $nav .= "<a href='" . _RURL . "$linq' class='fo5a'>$name</a>";
        }

        return "
        <div class='foot'>
	        <div id='fo1'>
	        	<div id='social'>
                    <p>" . _F_TXTSOCIALNET . "</p>
                    <a href='https://www.facebook.com/mgpdinamic.face'><img src=" . _RURL . "imagens/lface.png alt='facebook' class='logsocial'></a>
                    <a href='https://plus.google.com/116637226690895351080' rel='publisher'><img src=" . _RURL . "imagens/icon_google+.png alt='google+' class='logsocial'></a>
                </div>
	        </div>

	        <div id='fo2'>
		        <div id='newsletter'>
		        	<p>" . _F_TXTNEWSLETTER . "</p>
					<form id='form1' name='form1' method='post' action='sendnews'>
				        <div class='fnew'>
				        	<input name='news' type='text' placeholder='seu e-mail' class='inputtxt'>
				  			<input type='submit' class='inputsub' value='" . _L_FORMSEND . "'>
				  		</div>
				        <div class='newsavs'>
				        </div>
			        </form>
		        </div>
	        </div>

	        <div id='fo3'>


	        </div>

	        <div id='fo5'>
		        <a href='mailto:" . $this -> principal['mail'] . "' class='fo5a'>" . _F_TXTMAILTO . "</a>
		        $nav
		        <br>
		        <br>
		        <a href='http://www.mgpdinamic.com' class='dev'>
		        	" . _F_TXTDEVELOP . ":
		        	<br>
		        	<img src='" . _RURL . "imagens/logo_desenv_mgp.png' alt='" . _F_TXTDEVELOP . " mgpdinamic.com'>
		        </a>
		        <br>
		        " . _F_TXTRIGHTS . "&copy;" . strftime("%Y") . " " . $this -> principal['firma'] . "
	        </div>
        </div>

";
    }

    /*
     * v2.0 23/09/2013
     * $z = se verdadeiro retorna o formulario na div zoom e exige ser iniciado por  _SENDFORMS.start();, se falso envia apenas o formulario e é iniciado por  _SENDFORMS.init();
     * $s = assunto
     * $f = filtro
     * $p = id do item
     */

    public function form_message_plus($z, $s, $f, $p)
    {

        $ro = ($s) ? "readonly=\"readonly\"" : "";
        $lg = (in_array($_GET['lang'], $GLOBALS['LANGPAGES'])) ? $_GET['lang'] : $GLOBALS['LANGPAGES'][0];

        if (!$z)
        {
            return "

            <div class='formStage'>
            	<div class='bloq'></div>
                <form method='post' action='sendmessplus' lang='$lg' data-type='$p' data-filter='$f'>
                        <table>
                            <tr>
                            <td class='formTdInput'>*" . _L_SUBJECT . ": <input type='text' name='assunto'  value='$s' $ro></td>
                            </tr>
                            <tr>
                            <td class='formTdInput'>*" . _L_NAME . ": <input type='text' name='nome' value=''></td>
                            </tr>
                            <tr>
                            <td class='formTdInput'>" . _L_FONE . ": <input type='text' name='tele' value=''></td>
                            </tr>
                            <tr>
                            <td class='formTdInput'>*e-mail: <input type='text' name='mail' value=''></td>
                            </tr>
                            <tr>
                            <td class='formTdText'>*" . _L_MESSAG . ":<br><textarea name='mess'></textarea></td>
                            </tr>
                            <tr>
                            <td ><input type='submit' value='" . _L_FORMSEND . "'></td>
                            </tr>
                            <tr>
                            <td class='formTdWarn'>*" . _L_WARNINGFORM . "</td>
                            </tr>
                        </table>
                </form>

            </div>
                    ";
        }
        else
        {

            return "

            <div id='zomm'>
                    <div class='wzomm'>
                    <img src='" . _RURL . "n_imagens/close_zomm.png' id='closezomm'>
                        <div class='zommtext'>
                        " . _PRODTEXTFORM . "
                        </div>
                        <div class='zommform'>
                            <p class='servinfo'>" . _PRODTITLEFORM . "</p>
                            $form
                        </div>
                    </div>
            </div>
           ";
        }

    }

    public function job_form()
    {

        $lg = (in_array($_GET['lang'], $GLOBALS['LANGPAGES'])) ? $_GET['lang'] : $GLOBALS['LANGPAGES'][0];

        return "

            	<iframe  id='fload' name='recframe' style='display:none'></iframe>
	            <div id='formjob'>
	            <div class='formStage'>
		            <div class='bloq'></div>
		            <p>" . _L_JOBTEXT . "</p>

		            <form enctype='multipart/form-data' id='recruta' target='recframe' action='send_job_form' method='post' lang='$lg'>
		                <table class='rectb'>
		                    <tr>
		                        <td class='formTdInput'>" . _L_JOBL . ": <input type='text' name='assunto' class='inrec' /></td>
		                    </tr>
		                    <tr>
		                        <td class='formTdInput'>" . _L_NAME . ": <input type='text' name='nome' class='inrec'/></td>
		                    </tr>
		                    <tr>
		                        <td class='formTdInput'>" . _L_FONE . ": <input type='text' name='tele' class='inrec'/></td>
		                    </tr>
		                    <tr>
		                        <td class='formTdInput'>e-mail: <input type='text' name='mail' class='inrec'/></td>
		                    </tr>
		                    <tr>
		                        <td class='formTdText'>" . _L_MESSAG . ": <br><textarea name='mess' cols='40' rows='4' class='txrec'></textarea></td>
		                    </tr>
		                    <tr>
		                        <td class='formTdInput'>" . _L_CURRI . ": <input type='file' name='curriculum' enctype='multipart/form-data' id='curr' class='inrec'></td>
		                    </tr>
		                    <tr>
		                        <td class='formTdSend'><input type='submit' value='" . _L_FORMSEND . "' class='subrec' /></td>
		                    </tr>
		                    <tr>
		                        <td class='formTdWarn'>" . _L_WARNINGFORMJOB . "</td>
		                    </tr>
		                </table>
		            </form>
		            </div>
				</div>
";
    }

    public function send_mess_plus()
    {

        $nome = $this -> anti -> verificaNome($_POST['nome']);
        $fone = $this -> anti -> verificaTexto($_POST['tele']);
        $mail = $this -> anti -> validateEmail($_POST['mail']);
        $assunto = $this -> anti -> verificaTexto($_POST['assunto']);
        $mens = $this -> anti -> verificaTexto($_POST['mess']);

        $e1 = (!$assunto) ? 1 : 0;
        $e2 = (!$nome) ? 2 : 0;
        $e4 = (!$mail) ? 4 : 0;
        $e5 = (!$mens) ? 5 : 0;
        $e6 = 0;

        if ($_POST['type'] && $_POST['filter'])
        {

            $prod = $this -> anti -> verificaTexto($_POST['type']);
            $tipo = $this -> anti -> verificaTexto($_POST['filter']);

            $e7 = (is_numeric($prod)) ? 0 : 1;
            $e8 = ($tipo !== "services" || $tipo !== "products") ? 0 : 1;

        }
        else
        {

            $prod = NULL;
            $tipo = NULL;

            $e7 = 0;
            $e8 = 0;
        }

        if ($e1 || $e2 || $e6 || $e4 || $e5 || $e7 || $e8)
        {
            return "{\"error\":[$e1,$e2,0,$e4,$e5]}";

        }

        //$assunto="Mensagem enviada no site.";
        $mens = "Solicitação de mais informações para " . html_entity_decode($assunto, ENT_QUOTES, "UTF-8") . "<br>-----------------------------------------<br>nome:$nome<br>telefone:$fone<br>e-mail:$mail<br>-----------------------------------------<br>$mens<br><br>-----------------------------------------<br>mensagem enviada através de " . _NOMESITE;

        $nome = $this -> anti -> text_clean_json($nome);
        $fone = $this -> anti -> text_clean_json($fone);
        $mail = $this -> anti -> text_clean_json($mail);
        $assunto = $this -> anti -> text_clean_json($assunto);
        $mens = $this -> anti -> text_clean_json($mens);
        $prod = $this -> anti -> text_clean_json($prod);
        $tipo = $this -> anti -> text_clean_json($tipo);

        if ($this -> sendMail('{"nome":"' . $nome . '","fone":"' . $fone . '","mail":"' . $mail . '","assunto":"' . $assunto . '","mensagem":"' . $mens . '","id_produto":"' . $prod . '","tipo_produto":"' . $tipo . '","anexo":""}'))
        {
            return "{\"result\":\"" . _L_SUCESSMESS . "\"}";
        }
        else
        {
            return "{\"result\":\"" . _L_FAILMESS . "\"}";
        }
    }

    public function send_job()
    {

        $tipo = $_FILES['curriculum']['type'];
        $anexo = $this -> anti -> verificaTexto($_FILES['curriculum']['name']);
        $nome = $this -> anti -> verificaNome($_POST['nome']);
        $fone = $this -> anti -> verificaTexto($_POST['tele']);
        $mail = $this -> anti -> validateEmail($_POST['mail']);
        $assunto = $this -> anti -> verificaTexto($_POST['assunto']);
        $mens = $this -> anti -> verificaTexto($_POST['mess']);

        $e1 = (!$assunto) ? 1 : 0;
        $e2 = (!$nome) ? 2 : 0;
        $e3 = (!$fone) ? 3 : 0;
        $e4 = (!$mail) ? 4 : 0;
        $e5 = (!$mens) ? 5 : 0;
        $e6 = ($tipo != "application/pdf" || !$anexo) ? 6 : 0;

        if ($e1 || $e2 || $e3 || $e4 || $e5 || $e6)
        {

            return "<script>parent._SENDFORMS.result={\"error\":[$e1,$e2,$e3,$e4,$e5,$e6]};parent._SENDFORMS.read();</script>";
            exit ;
        }

        $anexo = str_replace(" ", "_", $anexo);
        $anexox = $nome . "_" . $fone . "_" . $anexo . "_" . time() . ".pdf";

        $path = _ANX . $anexox;

        echo $path;
        $upanex = move_uploaded_file($_FILES['curriculum']['tmp_name'], utf8_encode($path));

        if (!$upanex)
        {

            echo "<script>parent._SENDFORMS.result={\"result\":\"" . _L_FAILJOBMESS . "\"};parent._SENDFORMS.read();</script>";
            exit ;
        }

        $mens = "nome:$nome $apelido<br>telefone:$fone<br>e-mail:$mail<br>-----------------------------------------<br>$mens<br><br>-----------------------------------------<br>mensagem enviada através de " . _NOMESITE;

        $nome = $this -> anti -> text_clean_json($nome);
        $fone = $this -> anti -> text_clean_json($fone);
        $mail = $this -> anti -> text_clean_json($mail);
        $assunto = $this -> anti -> text_clean_json($assunto);
        $mens = $this -> anti -> text_clean_json($mens);
        $anexo = $this -> anti -> text_clean_json($anexo);

        if ($this -> sendMail('{"nome":"' . $nome . '","fone":"' . $fone . '","mail":"' . $mail . '","assunto":"' . $assunto . '","mensagem":"' . $mens . '","anexo":"' . $anexox . '"}'))
        {

            echo "<script>parent._SENDFORMS.result={\"result\":\"" . _L_SUCESSJOBMESS . "\"};parent._SENDFORMS.read();</script>";
            exit ;
        }
        else
        {

            echo "<script>parent._SENDFORMS.result={\"result\":\"" . _L_FAILJOBMESS . "\"};parent._SENDFORMS.read();</script>";
            exit ;
        }
    }

    public function send_news()
    {

        $mail = $this -> anti -> validateEmail($_POST['news']);
        if (!$mail)
        {
            echo "{\"result\":\"" . _L_NWSLINVA . "\"}";
            exit ;
        }

        $tcnx = new mysqli(_HT, _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        $rslt = $tcnx -> query("SELECT mail FROM contactos WHERE mail='$mail' LIMIT 1");
        $rmail = $rslt -> fetch_array();

        if (!$rmail)
        {

            $mens = "e-mail:$mail<br>-----------------------------------------<br>$mail subescreveu a newsletter<br><br>-----------------------------------------<br>mensagem enviada através de " . _NOMESITE;

            $mail = $this -> anti -> text_clean_json($mail);
            $assunto = $this -> anti -> text_clean_json("$mail subscreveu a newsletter");
            $mens = $this -> anti -> text_clean_json($mens);

            if ($this -> sendMail('{"mail":"' . $mail . '","assunto":"' . $assunto . '","mensagem":"' . $mens . '"}'))
            {

                echo "{\"result\":\"" . _L_NWSLSUCESS . "\"}";
                exit ;
            }
            else
            {

                echo "{\"result\":\"" . _L_NWSLFAIL . "\"}";
                exit ;
            }
        }
        else
        {

            echo "{\"result\":\"" . _L_NWSLWARNNING . "\"}";
            exit ;
        }
    }

    public function contact_data()
    {

        $tcnx = new mysqli(_HT, _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        $rcont = $tcnx -> query("SELECT * FROM " . _EMPTB . " WHERE id=1 LIMIT 1");

        $cont = $rcont -> fetch_array();

        $js = json_decode(_L_CONTACTS, TRUE);
        extract($js);

        $mail = ($cont['mail']) ? "<p><span class='scontm'>$mail:<br></span>" . $cont['mail'] . "</p>" : "";
        $fone = ($cont['telefone']) ? "<p><span class='scontm'>$fone:<br></span>" . $cont['telefone'] . "</p>" : "";
        $fax = ($cont['fax']) ? "<p><span class='scontm'>fax:<br></span>" . $cont['fax'] . "</p>" : "";
        $movel1 = ($cont['telemovel1']) ? "<p><span class='scontm'>$movel:<br></span>" . $cont['telemovel1'] . "</p>" : "";
        $movel2 = ($cont['telemovel2']) ? "<p><span class='scontm'>$movel:<br></span>" . $cont['telemovel2'] . "</p>" : "";
        $movel3 = ($cont['telemovel3']) ? "<p><span class='scontm'>$movel:<br></span>" . $cont['telemovel3'] . "</p>" : "";
        $gps = ($cont['gps']) ? "<p><span class='scontm'>$gps:<br></span>" . $cont['gps'] . "</p>" : "";

        return "
            <div id='mainlocal'>
                        " . $this -> send_images_json($cont['foto_contactos'], "img", $this -> lg, 0, "class='mainlocalimg'") . "
                        <div class='compdetail'>
                            <p>
                              " . $cont['nome_comercial'] . "
                            </p>
                            <p>
                             " . $cont['morada'] . " " . $cont['complemento'] . "
                            </p>
                            <p>
                             " . $cont['freguesia'] . "
                            </p>
                            <p>
                              " . $cont['cidade'] . "
                            </p>
                            <p>
                              " . $cont['pais'] . "
                            </p>
                            $mail $fone $fax $movel1 $movel2 $movel3 $gps

                        </div>


                    </div>
        ";
    }

}
?>
