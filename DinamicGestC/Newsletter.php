<?php
/**
 * script: Newsletter.php
 * client: EPKyusho
 *
 * @version V4.00.080615
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
require_once 'Core.php';
require_once 'Grupos.php';
require_once 'MasterConfig.php';

/**
 * Esta classe faz a gestão das criação e envio das newsletters.
 *
 * Stored procedures necessárias:
 * - spNewsletterData
 * - spContactsNewsletterSend
 * - spNewsletterConfigData - comun com a class NewsletterConfig
 * - spInsertNewsletter
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version V4.00.080615
 * @since 08/06/2015
 * @license Todos os direitos reservados
 */
class Newsletter extends Core
{
    #array do objecto json

    private $config;
    private $exp_prefix = "NWL";

    /**
     *
     * @param type $JS
     */
    function __construct($JS = NULL)
    {
        parent::__construct();

        $js = ($JS) ? $JS : "JNEWSLETTER";

        $this->config = $this->json_file($js);
    }

    /**
     * Retorna a array de configuração do modulo
     *
     * @return array
     */
    public function get_config()
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        return $this->config;
    }

    /**
     * Cria a ficha de uma newsletter
     *
     * @param string $idx - identificador único da newsletter
     *
     * @return string html com a newsletter
     */
    public function make_sheet($idx)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = parent::id($idx))
            return parent::mess_alert($this->exp_prefix . __LINE__);
        try
        {
            $sheet = new ItemSheet();
            return $sheet->make_all_sheet("spNewsletterData", $this->config, array($id));
        } catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage());
        }
    }

    /**
     * Envia um item para inserir numa newsletter no mesmo numero de idiomas que estão definidos no objeto export.
     * A função iterage nos elementos da array export, retira os dados da base de dados e devolve uma string html
     *
     * @uses Logex::check()
     * @uses Core::mess_alert()
     * @uses Core::id()
     * @uses Core::export_item()
     *
     * @param int $item_id - identificador único do item na tabela da base de dados
     * @param boolean $edition - se TRUE (valor por omissão) o iten é envido dentro de uma div que permiter ser eliminado com mais facilidade. Esta opção deve ser usuado em operaçõe de inserção ou atualização de um item. Se o valor for falso o item é enviado sem a div.
     *
     * @return string HTML
     *
     */
    public function get_item2insert($item_id, $edition = 1)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = parent::id($item_id))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!is_array($this->config['export']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (empty($this->config['name']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $export = $this->config['export'];

        $name = $this->config['name'];

        $itens = NULL;

        //chaves da array $export
        $key = array_keys($export);

        foreach ($key as $ex_k)
        {
            //retira os dados do banco de dados e devolve-os configurados numa array
            $data = parent::export_item($id, $this->config);

            $link = NULL;
            $item = NULL;

            //cria o link
            if (!empty($data[$ex_k]['link']))
            {
                $aurl = $data[$ex_k]['link'];

                $atext = NULL;

                if (!empty($data[$ex_k]['anchor_text']))
                    $atext = $data[$ex_k]['anchor_text'];

                $link = '<a target="_blank" href="' . $aurl . '" style="display:block;width:90%;text-align:center;padding:5px;font-size:20px;color:#FFC;background-color:#222;margin:15px auto;">' . $atext . '</a>';
            }

            $img = NULL;

            #imagem
            if (!empty($data[$ex_k]['img']))
            {
                $img = '<img src="' . $data[$ex_k]['img'] . '" style="width:100%;margin:3px 0;">';
            }

            $title = NULL;
            $text = NULL;

            #titulo
            if (!empty($data[$ex_k]['title']))
                $title = $data[$ex_k]['title'];

            #texto
            if (!empty($data[$ex_k]['text']))
                $text = $data[$ex_k]['text'];

            if ($img || $title || $text)
            {
                $item = '<article class="nwslblok"  id="___dataid:' . $name . '__' . $id . '" data-ob="___dataob:' . $name . '__' . $id . '" style="width: 600px;margin: auto;padding-top: 10px;padding-bottom: 10px;text-align: justify;margin: 30px auto;" contenteditable="false">' . $img . '<header contenteditable="true"><h1>' . $title . '</h1></header><section style="width: 600px;margin: 5px 0; font-size:16px; text-align: justify;" contenteditable="true">' . $text . '</section><footer style="width:100%;height:2px;clear:both;margin:20px 0">' . $link . '</footer></article>';

                if ($edition)
                {
                    $itens .= '<div class="edtion_box" contenteditable="false"><img src="imagens/minidel.png" class="ig15A" data-action="delthis">' . $item . '</div>';
                } else
                {
                    $itens .= $item;
                }
            }
        }

        return $itens;
    }

    /**
     * Guarda uma nova newsletter.
     *
     * @return json
     *
     */
    public function save()
    {
        if (!parent::check())
            parent::mess_alert($this->exp_prefix . __LINE__);

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            parent::mess_alert($this->exp_prefix . __LINE__);

        if (empty($_POST['comuns_title']))
            return '{"alert":"Tem que definir um titulo."}';

        $p = $_REQUEST['comuns_content'];
        $contt = NULL;

        //desabilita o disparo de erros, ou seja, impede a lançamento de avisos
        libxml_use_internal_errors(true);

        //cria um novo objeto
        $doc = new DOMDocument;

        //carrega o conteudo da newsletter. mb_convert_encoding para forçar codificação correta para utf8
        $h = $doc->loadHTML(mb_convert_encoding($p, 'HTML-ENTITIES', 'UTF-8'));

        //retem o cabeçalho da news letter
        $head = $doc->getElementById("newsletter_header");

        if ($head)
            $header = $doc->saveHTML($head);

        if ($h)
        {
            //cria um novo objeto xpath que é usado para navegar em documentos html
            $docp = new DOMXPath($doc);

            //procuro pelos itens inseridos e que irão fazer parte da versão final da newsletter
            $ele = $docp->query('//article[@class="nwslblok"]');
        }

        //guardo o contéudo dos nodelist que fazem parte da versão final da newsletter numa string
        foreach ($ele as $cont)
        {
            $contt .= $doc->saveHTML($cont);
        }

        //limpa o buffer de erros
        libxml_clear_errors();

        $content = $header . $contt;

        $_POST['comuns_content'] = '<div id="ncontainer">' . $content . '</div>';

        $op_bar = new OperationsBar($this->config);

        return $op_bar->save_item(NULL, "spInsertNewsletter");
    }

    /**
     * filtra e envia os contactos de um grupo para enviar a newsletter
     *
     * @return type json
     */
    public function groups()
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!isset($_POST['flag']) && $_POST['flag'] !== "GROUP")
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$group_id = parent::id($_POST['toke']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        #CONTATOS PARA QUEM FOI ENVIADA A NEWSLETTER
        $news_sended = array();

        #CHECKBOX SELECIONADA
        $chb = 1;

        #JSON INDIVIDUAL CONTATO
        $contc = NULL;

        #NEWSLETTER
        if ($newsletter_id = $this->id($_POST['letter']))
        {
            try
            {
                $result = parent::make_call("spContactsNewsletterSend", array(
                            $newsletter_id,
                            $group_id
                ));

                if (isset($result[0]))
                {
                    $newsl = explode(",", $result[0][0]);
                    $news_sended = array_unique($newsl);
                }
            } catch (Exception $ex)
            {
                
            }
        }

        #CONTATOS
        $group = new Grupos();
        $group_result = $group->make_file($group_id, FALSE);

        foreach ($group_result as $members)
        {
            if ($members[3])
            {
                if (is_array($news_sended) && in_array($members[0], $news_sended))
                {
                    $chb = 0;
                } else
                {
                    $chb = 1;
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
            throw new Exception($this->exp_prefix . __LINE__, 1);

        try
        {
            $result = parent::make_call("spNewsletterConfigData", NULL);
        } catch (Exception $ex)
        {
            throw new Exception($this->exp_prefix . __LINE__, 1);
        }


        if (!$result)
            throw new Exception($this->exp_prefix . __LINE__, 1);

        if (!$config_data = $result[0])
            throw new Exception($this->exp_prefix . __LINE__, 1);

        //endereços de e-mail que servirão para enviar a newsletter aos membros dos grupos
        $send_data['send'] = explode(",", $config_data[$nwsconf['content']['mails']['send_mail']['db']]);

        //numero de e-mails a enviar por endereço de email
        $send_data['numb'] = $config_data[$nwsconf['admin']['public']['numb']['db']];

        //enderço de email de resposa em caso de erro
        $send_data['return'] = $config_data[$nwsconf['content']['mails']['return_mail']['db']];

        if (empty($send_data))
            throw new Exception($this->exp_prefix . __LINE__, 1);

        return $send_data;
    }

    private function newsletter_data($newsletter_id)
    {

        if (!$id = parent::id($newsletter_id))
            throw new Exception($this->exp_prefix . __LINE__, 1);
        try
        {
            $result = parent::make_call("spNewsletterData", array($id));
        } catch (Exception $ex)
        {
            throw new Exception($this->exp_prefix . __LINE__, 1);
        }

        if (!$result)
            throw new Exception($this->exp_prefix . __LINE__, 1);

        if (!$nws = $result[0])
            throw new Exception($this->exp_prefix . __LINE__, 1);

        $nws_data['toke'] = $id;
        $nws_data['title'] = $nws[$this->config['content']['comuns']['title']['db']];
        $nws_data['content'] = $nws[$this->config['content']['comuns']['content']['db']];

        if (empty($nws_data))
            throw new Exception($this->exp_prefix . __LINE__, 1);

        return $nws_data;
    }

    //FIXME
    /**
     * Envia newsletter
     *
     * @see Logex::check()
     * @see Core::json_file()
     * @see Newsletter::newsletter_data()
     * @see Newsletter::config_send()
     * @see Newsletter::make_newsletter()
     * @see Contacts::query_contacts_group()
     * @see Contacts::get_notes_table()
     *
     * @param array $values - requisição do browser com o id da newsletter, id do grupo, id dos contactos para enviar a newsletter
     *
     * @return boolean|string
     */
    public function send_newsletter(array $values)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!isset($values['flag']) && $values['flag'] !== "SEND" && !isset($values['dados']) && !isset($values['linque']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (empty($values['dados']))
            return parent::mess_alert("Não tem contactos selecionados.");

        #NEWSLETTER
        try
        {
            $nws_data = $this->newsletter_data($values['linque']);
            $newsletter_id = $nws_data['toke'];
        } catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage());
        }

        #CONFIGURAÇÕES DA NEWSLETTER
        try
        {
            $send_data = $this->config_send();
        } catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage());
        }

        #ID CONTACTOS PARA ENVIAR
        //FIXME criar uma procedure para retirar esses dados para não ter que ir aos contactos
        try
        {
            $contact = new Contacts();
            $contacts = $contact->query_contacts_group($values['dados']);
            $contacts_notes_table = $contact->get_notes_table();
            unset($contact);
        } catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage());
        }

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

        #hora de inicio de envio e identificador de envio da newsletter
        $tm = time();

        foreach ($contacts as $mailx)
        {
            //se não existir mais nenhum endereço de envio
            if (!$send_data['send'][$e])
                break;

            #CABEÇALHOS DE ENVIO
            $headers = 'Return-Path:' . $send_data['return'] . "\r\n";
            $headers .= 'From:' . $send_data['send'][$e] . "\r\n";
            $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";

            if (!$corpo = $this->make_newsletter($nws_data['content'], $tm, $mailx))
                return '{"error":"Não foi possivel configurar a newsletter."}';

            $mai = imap_mail($mailx[1], $nws_data['title'], $corpo, $headers);

            if (!$mai)
            {
                $fail++;
            } else
            {
                $sended_mail_list .= "<br>" . $mailx[1];
                $sended_contacts_id[] = $mailx[0];
            }

            if ($c < $send_data['numb'])
            {
                $c++;
            } else
            {
                $c = 1;
                $e++;
            }
        }

        if (!count($sended_contacts_id))
            return '{"error":"A newsletter nao foi enviada."}';

        $group_id = parent::id($values['grupo']);

        $o_group = new Grupos();

        $group_name = $o_group->get_group_name($group_id);

        $group_notes_table = $o_group->get_notes_table();

        unset($o_group);

        $title = $nws_data['title'];

        $start_date = date("d-m-Y H:i:s", $tm);

        $end_date = date("d-m-Y H:i:s", time());

        $sel_members = (count($sended_contacts_id) + $fail);

        $sended = count($sended_contacts_id);

        $params[] = $newsletter_id;
        $params[] = $group_id;
        $params[] = $tm;
        $params[] = GDate::make_date($start_date, 1, "DATEBD");
        $params[] = GDate::make_date($end_date, 1, "DATEBD");
        $params[] = $sended;
        $params[] = $fail;
        $params[] = $sel_members;

        try
        {
            parent::make_call("spSaveReport", $params);
        } catch (Exception $ex)
        {
            
        }

        $relatorio = <<<EOF
            RELATÓRIO DE ENVIO DE NEWSLETTER
            ------------------------------------------------------------

            id newsletter: <b>$newsletter_id</b>

            mid newsletter : <b>$tm</b>
                
            titulo newsletter:  <b>$title</b>

            começo de envio: <b>$start_date</b>

            fim de envio: <b>$end_date</b>

            enviada para o grupo: <b>$group_name</b>

            numero de membros do grupo: <b></b>

            numero de membros selecionados: <b>{$sel_members}</b>

            numero de newsletter enviadas com sucesso: <b>$sended</b>

            numero de newsletter que falhou o envio: <b>$fail</b>

            ____________________________________

            enviada com sucesso para os e-mails:
            ____________________________________

            $sended_mail_list
EOF;

        #guarda envio na tabela de notas e recebe o id
        $note = new GestNotes();
        $note_id = $note->insert_note('newsletter', $relatorio, $nws_data['title']);

        $reg_contact_note = null;
        foreach ($sended_contacts_id as $value)
        {
            if (!empty($note_id) && !empty($value))
                $reg_contact_note .= "($note_id,$value),";
        }

        #grava a nota na tabela das notas de contactos
        $note->register_note($contacts_notes_table, trim($reg_contact_note, ","));

        #grava a nota na tabela das notas da newsletter
        $note->register_note($this->config['notes'], "($note_id,$newsletter_id)");

        #grava a nota na tabela das notas do grupo
        $note->register_note($group_notes_table, "($note_id,$group_id)");

        $modules = $this->json_file();

        //procura os resultados que condizem com o padrão e guarda numa array
        preg_match_all("/data-ob=['\"]___dataob:([A-Za-z0-9_]+)__([0-9]+)['\"]/", $corpo, $fox);

        $counter = count($fox[2]);

        foreach ($modules as $m_value)
        {
            if (!isset($m_value['name']))
                continue;

            $ce = 0;
            $item_id = NULL;

            for ($ce; $ce < $counter; $ce++)
            {
                if (!isset($fox[1][$ce]))
                    continue;

                if ($fox[1][$ce] != $m_value['name'])
                    continue;

                if (empty($fox[2][$ce]) || ($item_id == $fox[2][$ce]))
                    continue;

                $item_id = $fox[2][$ce];

                if ($m_value['notes'] && $note_id)
                {
                    $note->register_note($m_value['notes'], "($note_id, $item_id)");
                }
            }
        }

        unset($note);

        #lista de id de todos os contactos
        $sended_members = implode(",", $sended_contacts_id);

        try
        {
            parent::make_call("spSendedNewsletter", array($newsletter_id, $group_id, $sended_members));
        } catch (Exception $ex)
        {
            
        }

        return '{"enviados":[' . $sended_members . ']}';
    }

    /**
     *
     * cria 1 newsletter
     *
     * @param string $content conteudo da newsletter
     * @param string $mid identificador unico da cada envio
     * @param int $rid id do contacto que vai receber a newsletter
     * @param string $mail mail de quem vai receber a newsletter
     *
     * @return boolean | string HTML
     *
     */
    private function make_newsletter($content, $mid = NULL, $mail = NULL)
    {
        if (!$content)
            return parent::mess_alert("NWL" . __LINE__);

        $enc_text = '{"id":"' . $mail[0] . '","email":"' . md5($mail[1]) . '"}';

        $cat = $mail[2];

        $act = ($mail[2] === "empresa") ? $mail[3] : $mail[4];

        $query_string = '{"mid":"' . $mid . '","cat":"' . $cat . '","act":"' . $act . '"}';

        $dmail = $this->mgpencrypt($enc_text);
        $send_id = $this->mgpencrypt($query_string);

        $url = _RURL;
        $name_site = _NAMESITE;
        $date = date("Y");

        $cont = str_replace("****#substitui_por_outra_coisa#****", $mid, $content);

        $return = <<<EOF
                <!doctype html>
                <html>
                    <head>
                        <meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=1.0,user-scalable=no">
                        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
                    </head>
                    <body style="background-color:#fff">
                        <style type='text/css'>
                            body{background-color:#fff}
                            img{border:none}
                            a{font-size:14px;fonte-weigth:bold;color:#666;text-decoration:none}
                            #ncontainer{font-family:arial;width:100%;max-width:1000px;margin:auto;background-color:#fff;text-align:center}
                            #nrodape{height:40px;padding-top:10px; padding-bottom:10px;margin-bottom:10px;margin-top:20px;font-size:14px;clear: both;color:#666;text-align:center}
                            .nwslblok {width: 600px;margin: auto;padding-top: 10px;padding-bottom: 10px;text-align: justify;margin: 30px auto;}
                            .nwslblokint {width: 600px;margin: 5px 0; font-size:16px}
                            .nwslblokint img {width: 100%;margin: 3px 0;}
                            .newsanchor {display: block;border-radius: 5px;width: 75%;text-align: center;padding: 5px;font-size: 1em;font-weight: bold;color: #333333;background-color: #DDDDDD;margin: 15px auto;}
                            .newsbottom{width:100%;height:2px;clear:both;}
                        </style>
                        <table style="font-family:arial;width:600px;margin:auto;background-color:#fff;text-align:justify">
                            <tr>
                                <td>
                                    $cont
                                </td>
                            </tr>
                            <tr>
                                <td style="height:40px;padding-top:10px; padding-bottom:10px;margin-bottom:10px;margin-top:20px;font-size:14px;clear: both;color:#666;text-align:center">
                                    <p style="font-size:16px;color:#333;font-weight:bold">Visite o nosso site: <a href='{$url}' target='_blank'>$url</a><p>
                                    <p style="font-size:10px;color:#333;">Todos os direitos reservados&copy;{$date} {$name_site}</p>
                                    <p style="font-size:10px">Se não deseja manter a subscrição da nossa newsletter <a href='{$url}remove?_rnw={$dmail}' target='_blank' style="font-size:10px;color:#666;text-decoration:none"> clique aqui: REMOVER</a></p>
                                    <img src="{$url}styles/$mid/rnews.gif?__mid={$send_id}">
                                </td>
                            </tr>
                        </table>
                    </body>
                </html>
EOF;

        return $return;
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

        try
        {
            $result = parent::make_call("spNewsletterData", array($id));
            $newsletter = $result[0]['newsletter.conteudo'];
        } catch (Exception $ex)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        return $this->make_newsletter($newsletter);
    }

    /**
     * Ficha de criação de uma nova newsletter
     *
     * @return string HTML
     *
     */
    public function add()
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $h_result = new PDO(_PDOM, _US, _PS);

        if ($h_result)
        {
            $header_img_db = $h_result->query("SELECT header_news FROM newsletter_config WHERE id=1");
            $header_img = $header_img_db->fetch();

            try
            {
                $img = new GestImage();
                $content['newsletter.conteudo'] = '<div style="width: 100%;text-align:center;margin-bottom:10px" id="newsletter_header" contenteditable="false">' . $img->send_images_json($header_img[0], "img", "pt", FALSE, 'style="width: 600px;margin:10px auto 20px;"') . "</div>";
            } catch (Exception $exp)
            {
                $content['newsletter.conteudo'] = NULL;
            }
        }

        $addedit = new OperationsBar($this->config);
        return $addedit->add_item_sheet($content);
    }

    /**
     * Estatisticas de newsletter enviadas e abertas
     *
     * @param string $id - identificador único do item
     *
     * @return string json com os dados estatisticos ou mensagem de erro
     */
    public function ind_stats($id)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id_newsletter = parent::id($id))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $query[] = 'SELECT sum(u.n_enviadas) as enviadas, m.month FROM ( SELECT "01" AS MONTH UNION SELECT "02" AS MONTH  UNION SELECT "03" AS MONTH  UNION SELECT "04" AS MONTH  UNION SELECT "05" AS MONTH
  UNION SELECT "06" AS MONTH
  UNION SELECT "07" AS MONTH
  UNION SELECT "08" AS MONTH
  UNION SELECT "09" AS MONTH
  UNION SELECT "10" AS MONTH
  UNION SELECT "11" AS MONTH
  UNION SELECT "12" AS MONTH
 ) AS m
 LEFT JOIN relatorios_envio_newsletter as u
 ON m.month = date_format(u.inicio_envio,"%m")
 AND u.newsletter_id = ?
 GROUP BY m.month
 ORDER BY 2;';

        $query[] = '
             SELECT
             count(e.data_abertura) enviadas, m.month
             FROM
             (
                SELECT "01" AS MONTH
                UNION SELECT "02" AS MONTH
                UNION SELECT "03" AS MONTH
                UNION SELECT "04" AS MONTH
                UNION SELECT "05" AS MONTH
                UNION SELECT "06" AS MONTH
                UNION SELECT "07" AS MONTH
                UNION SELECT "08" AS MONTH
                UNION SELECT "09" AS MONTH
                UNION SELECT "10" AS MONTH
                UNION SELECT "11" AS MONTH
                UNION SELECT "12" AS MONTH
             ) AS m
             LEFT JOIN relatorios_envio_newsletter u
             ON m.month = date_format(u.inicio_envio,"%m")
             AND u.newsletter_id = ?
             LEFT JOIN relatorios_abertura_newsletter e
             ON u.mide=e.mide
             GROUP BY m.month
             ORDER BY 2';

        $charts = array("Envio por mês", "Leituras por mês");

        $count_charts = 0;

        try
        {
            $dbcon = new PDO(_PDOM, _US, _PS);
        } 
        catch (PDOException $ex)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }


        foreach ($query as $value)
        {
            $smt = $dbcon->prepare($value);

            $smt->bindParam(1, $id_newsletter, PDO::PARAM_INT);

            $smt->execute();

            $row = $smt->fetchAll();

            foreach ($row as $stats)
            {
                $month = strftime("%b", mktime(0, 0, 0, $stats['month'], 1));
                $datas[$month] = $stats['enviadas'];
            }

            $data['type'] = "bar";
            $data['data'] = $datas;

            $chart[$charts[$count_charts]] = $data;

            $count_charts++;
        }
        
        $rtr_stats = json_encode($chart);

        return $rtr_stats;
    }
}

?>
