<?php
/* SCRIPT Newsletter.php V3.10
 11-06-2014
 COPYRIGHT MANUEL GERARDO PEREIRA 2014
 TODOS OS DIREITOS RESERVADOS
 CONTACTO: GPEREIRA@MGPDINAMIC.COM
 WWW.MGPDINAMIC.COM
 *
 * inicio : 11-06-2014
 *
 * última modificação : 11-06-2014
 *
 * */

ini_set('display_errors', 1);
require_once 'Core.php';
require_once 'Grupos.php';

class Newsletter extends Core
{

    #array do objecto json
    private $config;

    function __construct($JS = NULL)
    {

        parent::__construct();

        $js = ($JS) ? $JS : "JNEWSLETTER";

        $this -> config = $this -> json_file($js);

    }

    public function get_config()
    {
        return $this -> config;
    }

    public function get_item2insert($item_id)
    {
        if (!$id = parent::id($item_id))
            parent::mess_alert("NWL" . __LINE__);

        $table = !empty($this -> config['table']) ? $this -> config['table'] : NULL;
        $name = !empty($this -> config['name']) ? $this -> config['name'] : NULL;
        $id_db = !empty($this -> config['admin']['private']['toke']['db']) ? $this -> config['admin']['private']['toke']['db'] : NULL;
        $image_db = !empty($this -> config['admin']['private']['icon']['db']) ? $this -> config['admin']['private']['icon']['db'] : NULL;
        $status_db = !empty($this -> config['admin']['private']['status']['db']) ? $this -> config['admin']['private']['status']['db'] : NULL;
        $export = is_array($this -> config['export']) ? $this -> config['export'] : NULL;

        if (!$table || !$name || !$id_db || !$image_db || !$export)
            parent::mess_alert("NWL" . __LINE__);

        $itens = NULL;

        $img_gest = new GestImage();

        foreach ($export as $ex_k => $ex_v)
        {

            $ar_columns = NULL;
            if (is_array($ex_v['columns']))
                $columns = implode(",", array_filter($ex_v['columns']));

            if (!preg_match("/$id_db/", $columns))
                $columns .= "," . $id_db;

            if (!preg_match("/$image_db/", $columns))
                $columns .= "," . $image_db;

            if (!preg_match("/$status_db/", $columns))
                $columns .= "," . $status_db;

            $param = NULL;

            if (is_array($ex_v['param']))
            {
                foreach ($ex_v['param'] as $value)
                {
                    if (!preg_match("/$value/", $columns))
                        $columns .= "," . $value;

                    $param .= '/$item[' . $value . ']';
                }
            }

            if (!$result = logex::$tcnx -> query("SELECT $columns FROM $table WHERE $id_db=$id LIMIT 1"))
                continue;

            $item = $result -> fetch_array();

            if (empty($item[0]))
                continue;

            $img = $img_gest -> send_images_json($item[$image_db], "img", $ex_k, FALSE);

            $text = NULL;

            eval("\$param = \"$param\";");

            $link = NULL;
            if ($item[$status_db] == "online")
                $link = "<a class='newsanchor' href='" . $export[$ex_k]['url'] . str_replace(" ", "-", $param) . "/****#substitui_por_outra_coisa#****'>$ex_v[anchor_text]</a>";

            unset($ex_v['columns'][0]);

            foreach ($ex_v['columns'] as $col)
            {
                if ($col != $id_db || $col != $image_db)
                    $text .= "<div>$item[$col]</div>";
            }

            $itens .= "
                <div class='nwslblok'  id='___dataid:$id' data-ob='___dataob:$name'>
                    <div class='newstitle'>$item[0]</div>
                    <div class='nwslblokint'>
                    $img<br><br>                   
                    $text
                    </div>
                    <div class='newsbottom'>
                    $link
                    </div>
                </div>
                
                <br>
                ";

        }

        return $itens;
    }

    /**
     * filtra e envia os contactos de um grupo para enviar a newsletter
     * @return type json
     */
    public function groups()
    {

        if (!parent::check())
            return parent::mess_alert("NWL" . __LINE__);

        if (!isset($_POST['flag']) && $_POST['flag'] !== "GROUP")
            return parent::mess_alert("NWL" . __LINE__);

        if (!$group_id = parent::id($_POST['toke']))
            return parent::mess_alert("NWL" . __LINE__);

        #CONTATOS PARA QUEM FOI ENVIADA A NEWSLETTER
        $news_sended = NULL;

        #CHECKBOX SELECIONADA
        $chb = 1;

        #JSON INDIVIDUAL CONTATO
        $contc = NULL;

        #NEWSLETTER
        if ($newsletter_id = $this -> id($_POST['letter']))
        {
            $ver2 = logex::$tcnx -> query("SELECT membros_enviada  FROM envio_newsletter WHERE newsletter_id=$newsletter_id AND grupo_id = $group_id");
            $ver3 = $ver2 -> fetch_array();

            $news_sended = array_unique(explode(",", $ver3[0]));
        }

        #CONTATOS
        $group = new Grupos();
        $group_result = $group -> make_file($group_id, FALSE);

        while ($members = $group_result -> fetch_array())
        {

            if ($members[3])
            {

                if (is_array($news_sended) && in_array($members[0], $news_sended))
                {

                    $chb = 0;
                }

                $contx = ($members[1] || $members[2]) ? "$members[1] $members[2]" : $members[3];

                $contc .= '{"name":"' . $contx . '","id":' . $members[0] . ',"check":' . $chb . '},';
            }
        }

        return '{"contacts":[' . rtrim($contc, ",") . ']}';

    }

    private function config_send()
    {
        #CONFIGURAÇÕES DE ENVIO DA NEWSLETTER
        if (!$nwsconf = parent::json_file("JNEWSLETTERCONFIG"))
            return FALSE;

        if (!$config_data_result = logex::$tcnx -> query("SELECT * FROM " . $nwsconf['table'] . " WHERE " . $nwsconf['admin']['private']['toke']['db'] . "=1"))
            return FALSE;

        $config_data = $config_data_result -> fetch_array();

        //endereços de e-mail que servirão para enviar a newsletter aos membros dos grupos
        $send_data['send'] = explode(",", $config_data[$nwsconf['fields']['mails']['send_mail']['db']]);

        //numero de e-mails a enviar por endereço de email
        $send_data['numb'] = $config_data[$nwsconf['admin']['public']['numb']['db']];

        //enderço de email de resposa em caso de erro
        $send_data['return'] = $config_data[$nwsconf['fields']['mails']['return_mail']['db']];

        return $send_data;

    }

    private function newsletter_data($newsletter_id)
    {

        if (!$id = parent::id($newsletter_id))
            return FALSE;
        $toke = $this -> config['admin']['private']['toke']['db'];

        if (!$result = logex::$tcnx -> query("SELECT * FROM " . $this -> config['table'] . " WHERE " . $toke . "=$id LIMIT 1"))
            return FALSE;

        $nws = $result -> fetch_array();

        $nws_data['title'] = $nws[$this -> config['fields']['comuns']['title']['db']];
        $nws_data['content'] = $nws[$this -> config['fields']['comuns']['content']['db']];
        $nws_data['sended_list'] = $nws[$this -> config['fields']['comuns']['list']['db']];

        return $nws_data;

    }

    /**
     * Envia newsletter
     * @return boolean|string
     */
    public function send_newsletter(array $values)
    {
        if (!parent::check())
            return parent::mess_alert("NWL" . __LINE__);

        if (!isset($values['flag']) && $values['flag'] !== "SEND" && !isset($values['dados']) && !isset($values['linque']))
            return parent::mess_alert("NWL" . __LINE__);

        #CONFIGURAÇÕES DA NEWSLETTER
        if (!$send_data = $this -> config_send())
            return parent::mess_alert("NWL" . __LINE__);

        #NEWSLETTER
        if (!$newsletter_id = parent::id($values['linque']))
            return parent::mess_alert("Tem que escolher uma newsletter.");

        if (!$nws_data = $this -> newsletter_data($newsletter_id))
            return parent::mess_alert("NWL" . __LINE__);

        if (empty($values['dados']))
            return parent::mess_alert("Não tem contactos selecionados.");

        #ID CONTACTOS PARA ENVIAR
        $contact = new Contacts();
        $contacts = $contact -> query_contacts_group($values['dados']);
        $contacts_notes_table = $contact -> get_notes_table();
        unset($contact);

        //lista de endereços de e-mails enviados
        $sended_mail_list = NULL;
        //numero de envios falhados
        $fail = 0;
        //contador de emails
        $c = 1;
        //contador de endereços de email
        $e = 0;

        #envia a newsletter
        if (!$contacts)
            return parent::mess_alert("NWL" . __LINE__);

        #hora de inicio de envio
        $tm = time();

        while ($mailx = $contacts -> fetch_array())
        {
            //se não existir mais nenhum endereço de envio
            if (!$send_data['send'][$e])
                break;

            #CABEÇALHOS DE ENVIO
            $headers = 'Return-Path:' . $send_data['return'] . "\r\n";
            $headers .= 'From:' . $send_data['send'][$e] . "\r\n";
            $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";

            if (!$corpo = $this -> make_newsletter($nws_data['content'], $mailx[0], $mailx[1]))
                return '{"error":"Não foi possivel configurar a newsletter."}';

            $mai = 1;
            // imap_mail($mailx[1], $send_data['return'], $corpo, $headers);

            if (!$mai)
            {
                $fail++;
            }
            else
            {
                $sended_mail_list .= "<br>" . $mailx[1];
                $sended_contacts_id[] = $mailx[0];
            }

            if ($c < $send_data['numb'])
            {
                $c++;
            }
            else
            {
                $c = 1;
                $e++;
            }
        }

        if (!count($sended_contacts_id))
            return '{"error":"A newsletter nao foi enviada"}';

        $group_id = parent::id($values['grupo']);

        $o_group = new Grupos();

        $group_name = $o_group -> get_group_name($group_id);

        unset($o_group);

        $relatorio = "
							
								RELATÓRIO DE ENVIO DE NEWSLETTER
								------------------------------------------------------------	
								
								id newsletter: <b>$newsletter_id</b>
								
								titulo newsletter:  <b>" . $nws_data['title'] . "</b>
								
								começo de envio: <b>" . date("d-m-Y H:i:s", $tm) . "</b>
								
								fim de envio: <b>" . date("d-m-Y H:i:s", time()) . "</b>
								
								enviada para o grupo: <b>" . $group_name . "</b>
								
								numero de membros do grupo: <b></b>
								
								numero de membros selecionados: <b>" . (count($sended_contacts_id) + $fail) . "</b>
								
								numero de newsletter enviadas com sucesso: <b>" . count($sended_id) . "</b>
								
								numero de newsletter que falhou o envio: <b>$fail</b>
								
								____________________________________
								
								enviada com sucesso para os e-mails:
								____________________________________
								
								$sended_mail_list 
							";

        #guarda envio na tabela de notas e recebe o id
        $note = new GestNotes();
        $note_id = $note -> insert_note('newsletter', $relatorio, $nws_data['title']);

        $reg_contact_note = null;
        foreach ($sended_contacts_id as $value)
        {
            if (!empty($note_id) && !empty($value))
                $reg_contact_note .= "($note_id,$value),";
        }

        $note -> register_note($contacts_notes_table, trim($reg_contact_note, ","));
        $note -> register_note($this -> config['admin']['private']['notes']['db'], "($note_id,$newsletter_id)");

        $modules = $this -> json_file();

        //procura os resultados que condizem com o padrão e guarda numa array
        preg_match_all("/___dataid:(\d+)/", $corpo, $fix, PREG_PATTERN_ORDER);
        preg_match_all("/___dataob:([A-Za-z0-9_]+)/", $corpo, $fox, PREG_PATTERN_ORDER);
        
        var_dump($fix);
        
        
        foreach ($modules as $m_value)
        {
            if(!isset($m_value['name']))
                continue;
            
            for ($ce = 0; $ce < count($fox[1]); $ce++)
            {var_dump($m_value['name']);
                if ($m_value['name'] != $fox[1][$ce])
                    continue;
                
                $item_id = $fix[1][$ce];
                unset($fix[1][$ce]);
                
                if(preg_match("/[^0-9]/", $item_id))
                    continue;
                
                $note -> register_note($m_value['admin']['private']['notes']['db'], "($note_id,$item_id)");
                
                if(!count($fix[1]))
                    break 2;
                
            }
        }

        unset($note);
        #lista de id de todos os contactos
        $sended_members = implode(",", $sended_contacts_id);

        #atualiza a tabela de newsletter
        if (!logex::$tcnx -> query("
                                     INSERT INTO envio_newsletter  
                                     (newsletter_id,grupo_id,membros_enviada) 
                                     VALUES 
                                     ($newsletter_id,$group_id,'$sended_members' ) 
                                     ON DUPLICATE KEY UPDATE membros_enviada = '$sended_members'
                                     "))
            return parent::mess_alert("NWL" . __LINE__);
            

        return '{"enviados":[' . $enve . ']}';

    }

    /**
     *
     * cria 1 newsletter
     *
     * @param string $content conteudo da news letter
     * @param int $rid id do contacto que vai receber a newsletter
     * @param string $mail mail de quem vai receber a newsletter
     *
     * @return boolean | string HTML
     *
     */

    private function make_newsletter($content, $rid = NULL, $mail = NULL)
    {
        if (!$content)
            return parent::mess_alert("NWL" . __LINE__);

        $enc_text = '{"id":' . $rid . ',"email":"' . md5($mail) . '"}';

        $dmail = $this -> mgpencrypt($enc_text);

        return "
<!doctype html>
<html>
<head>
<meta http-equiv='X-UA-Compatible' content='IE=9' >
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<style type='text/css'>
body{background-color:#fff}
img{border:none}
a{font-size:9px;fonte-weigth:bold;color:#666;texte-decoration:none}
#ncontainer{font-family:arial;width:600px;margin-left:auto;margin-right:auto;background-color:#fff}
#nrodape{width:600px;height:40px;padding-top:10px; padding-bottom:10px;margin-bottom:10px;margin-top:20px;font-size:9px;clear: both;color:#666;text-align:center}
.news1{float: left;width: 100%;}
.news1 img{margin:  0px 4px 5px 0px;max-width: 196px;float: left}
.news2{float: left;width: 100%;}
.news2 img{margin:  0px 0px 5px 4px;max-width: 196px;float: right}
.news3{float: left;width: 200px;margin-left: 5px}
.news3_1{float: left;width: 395px;}
.news3 img{margin:  0px 4px 5px 0px;max-width: 196px;float: left}
.news4{float: right;width: 200px;margin-left: 5px}
.news4_1{float: right;width: 395px;}
.news4 img{margin:  0px 4px 5px 0px;max-width: 196px;float: right}
.news5{width: 100%;margin:5px 0px}
.news5 img{width: 100%;margin: 3px 0px}
.newstitle{font-size:22px;font-weight:bold;margin-left:5px}
.newsbottom{width:100%;height:2px;clear:both;}
.newscabec{height:90px; width:600px;text-align:center;overflow:hidden}
.contenttab{width:100%;height: 200px;margin-bottom:5px;}
.contenttdum{width:250px;text-align:center;vertical-align: top}
.contentimg{width:225px;margin-right:10px;}
.contenttddois{width:430px;font-size:12px;color:#555;text-align:justify;padding: 5px;vertical-align: top}
.contentp{font-size:14px;color:#555}
.contenta{font-size: 8px;color: #818181;float: right;text-decoration: none}
</style>
</head>
<body>
<div id='ncontainer'>
$content
<div id='nrodape'>
Visite o nosso site: <a href='" . _RURL . "' target='_blank'>" . _RURL . "</a> <br>
Todos os direitos reservados&copy;" . date("Y") . _NAMESITE . "<br>
Se não deseja manter a subscrição da nossa newsletter <a href='localhost/mgpdinamic.com/remove/$dmail' target='_blank'> clique aqui: REMOVER</a>
</div>
</div>
</body>
</html>
";

    }

    /**
     * Solicita uma cópia da newsletter para exibir aquando do envio.
     * @return boolean|string HTML
     */
    public function newsletter_selected($newsletter_id)
    {
        if (!parent::check())
            return parent::mess_alert("NWL" . __LINE__);

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            return parent::mess_alert("NWL" . __LINE__);

        if (!$id = parent::id($newsletter_id))
            return parent::mess_alert("NWL" . __LINE__);

        $toke = $this -> config['admin']['private']['toke']['db'];
        $content = $this -> config['fields']['comuns']['content']['db'];

        if (!$nws_result = logex::$tcnx -> query("SELECT $content  FROM " . $this -> config['table'] . " WHERE $toke=$id LIMIT 1"))
            return parent::mess_alert("NWL" . __LINE__);

        $newsletter = $nws_result -> fetch_array();

        return $this -> make_newsletter($newsletter[$content]);

    }

    /**
     * ficha de criação de uma nova newsletter
     *
     * @return string HTML
     */
    public function add()
    {
        if (parent::check())
        {

            if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            {

                return FALSE;
            }

            $foto = NULL;

            if (isset($this -> config['require']['newsletterconfig']))
            {

                $conf = parent::json_file($this -> config['require']['newsletterconfig']);

                if ($conf)
                {

                    $b2 = logex::$tcnx -> query("SELECT " . $conf['fields']['manag']['header']['db'] . " FROM " . $conf['table'] . " WHERE id='1'");
                    $b3 = $b2 -> fetch_array();

                    $foto = ($b3[$conf['fields']['manag']['header']['db']]) ? $this -> send_images_json($b3[$conf['fields']['manag']['header']['db']], "img", "pt", FALSE, NULL) : "";
                }
            }

            $jcont = $this -> config;
            extract($jcont['fields']['manag']);

            $C[$date['db']] = $this -> make_date();

            #MANAG
            $manag_data[0]["DB"] = $C;
            $manag_data[0]["JS"] = $jcont['fields']['manag'];
            $mag = $this -> comun_fields_divEVO($manag_data, "ADD");

            #COMUNS
            $D[$this -> config['fields']['comuns']['content']['db']] = $foto;

            $comuns_data[0]["DB"] = $D;
            $comuns_data[0]["JS"] = $jcont['fields']['comuns'];
            $comuns = $this -> comun_fields_divEVO($comuns_data, "ADD");

            return $this -> make_edit_file(NULL, NULL, $this -> form_action(), $mag . $comuns, "ADD");
        }
    }

    /**
     * guarda uma nova newsletter
     * @return boolean|json
     */
    public function save()
    {
        if (!parent::check())
            parent::mess_alert("NWL" . __LINE__);

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            parent::mess_alert("NWL" . __LINE__);

        $tit = $this -> validate_name($_POST['comuns_title']);

        if (empty($tit))
            return '{"alert":"Tem que definir um titulo."}';

        $op_bar = new OperationsBar($this -> config);

        return $op_bar -> save_item();

    }

    /**
     * cria a ficha de uma newsletter
     * @return boolean|string HTML
     */
    public function make_file()
    {
        if (parent::check())
        {

            if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            {

                return FALSE;
            }

            $js = $this -> config;
            extract($js['fields']);
            extract($manag);
            extract($comuns);

            $id = (isset($_POST['toke'])) ? $this -> id($_POST['toke']) : FALSE;
            if (!$id)
            {

                return "{\"alert\":\"Não foi possivel realizar esta operação. CODE:002\"}";
            }

            $RF = logex::$tcnx -> query("SELECT * FROM " . $js['table'] . " WHERE " . $toke['db'] . "=$id LIMIT 1");
            $CT = $RF -> fetch_array();

            $RF -> free();

            #MANAGEMENT
            $CT[$date['db']] = (isset($CT[$date['db']])) ? $this -> make_date($CT[$date['db']]) : NULL;
            $CT[$date_send['db']] = (isset($CT[$date_send['db']])) ? $this -> make_date($CT[$date_send['db']]) : NULL;
            $CT[$folder['db']] = (isset($CT[$folder['db']])) ? $CT[$folder['db']] : NULL;

            if ($_POST['flag'] === "FILE")
            {

                $dfile = $this -> make_data_file($id, $CT[$folder['db']], $CT[$title['db']], NULL, array(
                    "Ficha",
                    "Notas"
                ), NULL);
            }
            else
            {

                $dfile = NULL;
            }

            return $dfile['header'] . "
<div class='dv200'>

</div>
<div class='dv570xx200'>
<p class='p5'><span class='sp12FFM'>" . $title['name'] . ":</span><span>" . $CT[$title['db']] . "</span></p>
<p class='p5'><span class='sp12FFM'>" . $date['name'] . ":</span><span>" . $CT[$date['db']] . "</span></p>
<p class='p5'><span class='sp12FFM'>" . $date_send['name'] . ":</span><span>" . $CT[$date_send['db']] . "</span></p>

</div>

" . $this -> make_box(array("Newsletter" => $this -> make_newsletter($CT[$content['db']]))) . "
<div class='rodape'></div>

" . $dfile['footer'];
        }
    }

    /**
     * Cria a página para envio da newsletter
     * @return boolean|string HTML
     */
    public function select_newsletter()
    {
        if (!parent::check())
            return parent::mess_alert("NWL" . __LINE__);

        if (!isset($_POST['flag']) && $_POST['flag'] !== "FORNEWS")
            return parent::mess_alert("NWL" . __LINE__);

        return "
<div id='wnewsletter'>
<form action='" . $this -> form_action() . "' method='post'>

<div id='hnewsletter'>
<div class='dv95pL00'>
<span class='sp15b'>Enviar newsletter</span>
</div>
<div class='infonwl'>
<span>enviar newsletter:</span>
<span class='sp15b' id='letterto'></span>
<br><br>
<span>para o grupo:</span>
<span class='sp15b' id='groupto'></span>
</div>
<input type='hidden' name='lettertosend' value='' id='lettertosend'>
<div class='dv50x50' id='sendbt'>
</div>
</div>

<div id='pastaGrupos'>

</div>
<div id='wcontatos' >
<p id='selctall'>
<input type='checkbox' value='1' > Selecionar todos
</p>
<div id='contactos'>
</div>
</div>
<div id='pastaNews'>

</div>
<div id='letters'>
</div>
</form>
</div>
";

    }

    /**
     * apaga 1 newslletter
     * @return boolean
     */
    public function delete()
    {
        if (parent::check())
        {

            $id = $this -> id($_POST['toke']);

            if (isset($_POST['flag']) && $_POST['flag'] === "DELETE" && $id)
            {

                extract($this -> config['fields']['manag']);

                return $this -> delete_iten($folder['db'], $this -> config['table'], $toke['db'], $id);

            }
            else
            {

                return '{"alert":"Não foi possivel realizar esta operação. CODE:002"}';
            }
        }
    }

    /**
     * cria as notas referentes a uma newsletter
     * @return boolean || string HTML
     */
    public function show_notes()
    {
        if (parent::check())
        {

            $id = $this -> id($_POST['toke']);

            if (isset($_POST['flag']) && $_POST['flag'] === "NOTES" && $id)
            {

                return $this -> do_notes($this -> config['fields']['manag']['notes']['db'], $this -> config['table'], "id", $id);

            }
            else
            {

                return FALSE;
            }
        }
    }

}
?>
