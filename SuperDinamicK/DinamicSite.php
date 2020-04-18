<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
  DINAMICSHOP V1.1
  25-06-2011
 */
require_once "SComuns.php";

class DinamicSite extends SComuns
{

    /**
     * idioma da página
     * @var string 
     */
    private $lg;

    function __construct()
    {
        parent::__construct();

        $this->lg = parent::get_language();
    }

    private function get_iten_data()
    {

        if (in_array($_GET['mode'], $GLOBALS['WHITELIST']))
        {

            if (!$mode = json_decode(constant($_GET['mode']), TRUE))
                return FALSE;

            try
            {
                $result = parent::make_call($mode['workshops'], NULL);
            }
            catch (Exception $ex)
            {
                return FALSE;
            }

            if (is_array($result[0]))
                return $result[0];

            return FALSE;
        }
        else
        {

            return FALSE;
        }
    }

    /**
     * Retira da base de dados os dados para a página de apresentação dos cursos
     * 
     * @return boolean|array - Em caso de sucesso deveold um array com as seguintes chaves: intro_imagem, intro_titulo, intro_texto e ot. O chave "ot" é uma string html com uma chamada para os workshops disponiveis.
     * 
     */
    public function workshops($type)
    {
        $mode = json_decode(constant($_GET['mode']), TRUE);

        if (!$mode)
            return FALSE;

        if ($mode["dados_intro"])
        {

            $intro_workshops = parent::op_single_row($mode["dados_intro"]);

            try
            {
                if (isset($intro_workshops['fotos']))
                    $workshops['intro']['imagem'] = $this->images->send_images_json($intro_workshops['fotos'], "img", $this->lg, 0, "class='introcimg'");
            }
            catch (Exception $ex)
            {
                $workshops['intro']['imagem'] = NULL;
            }

            if (isset($intro_workshops['titulo']))
            {
                $workshops['intro']['titulo'] = $intro_workshops['titulo'];
            }
            else
            {
                $workshops['intro']['titulo'] = NULL;
            }


            if (isset($intro_workshops['texto1']))
            {
                $workshops['intro']['texto'] = $intro_workshops['texto1'];
            }
            else
            {
                $workshops['intro']['texto'] = NULL;
            }
        }

        try
        {
            $procedure = $mode['dados_workshops'];
            $result    = parent::make_call($procedure, NULL);
        }
        catch (Exception $ex)
        {
            return FALSE;
        }

        if (is_array($result))
        {
            switch ($type)
            {
                case "box":
                    $workshops['data'] = $this->box($result, $mode['link']);
                    break;
                case "gal":
                    $workshops['data'] = $this->gal($result, $mode['link']);
                    break;
                default:
                    break;
            }
        }
        else
        {
            $workshops['data'] = NULL;
        }
        return $workshops;
    }
    /**
     * 
     * @param type $itens
     * @param type $link
     * @return boolean|string
     */
    private function box($itens, $link)
    {

        if (!is_array($itens))
            return FALSE;

        $data = NULL;

        foreach ($itens as $item)
        {
            $mr = NULL;

            try
            {
                $fotos = ($item["fotos"]) ? $this->images->send_images_json($item["fotos"], "img", "pt", 0, "class='introcimg'") : "";
            }
            catch (Exception $ex)
            {
                $ex->getMessage();
                $fotos = NULL;
            }

            $url         = _RURL . $link . "/" . $item["id"] . "/" . parent::clean_space($item["nome"]);
            $anchor_text = mb_strtolower($item["nome"], 'UTF-8');

            $data .= "<section class='box trsp $mr introbox'>$fotos<h2 class='btit bintro'><strong>" . $item["nome"] . "<span class='localintro'>" . $item["local"] . "</span></strong></h2><div class='textintro'>" . $item["introducao"] . "</div><div class='introa'>" . _M_ANCHOR . "<a  href='$url' class='mais'><b>$anchor_text</b></a></div></section>";

            $mr = ($mr) ? NULL : "mr";
        }

        return $data;
    }
    /**
     * 
     * @param type $itens
     * @param type $link
     * @return boolean|string
     */
    private function gal($itens, $link)
    {
        if (!is_array($itens))
            return FALSE;

        $data = NULL;

        foreach ($itens as $item)
        {
            try
            {
                $fotos = ($item["fotos"]) ? $this->images->send_images_json($item["fotos"], "img", "pt", 0, "class='introcimg'") : "";
            }
            catch (Exception $ex)
            {
                $fotos = NULL;
            }

            if (!isset($item["nome"]))
                continue;


            $url         = _RURL . $link . "/" . $item["id"] . "/" . parent::clean_space($item["nome"]);
            $anchor_text = mb_strtolower($item["nome"], 'UTF-8');

            $data .= "<section class='galbox trsp'>$fotos<h2 class='galboxtitle'><strong>" . $item["nome"] . "</strong></h2><div class='textintro'> " . $item["introducao"] . "</div><div class='introa'>" . _M_ANCHOR . "<a  href='$url' class='mais'><b>$anchor_text</b></a></div></section>    
            ";
        }

        return $data;
    }

    /**
     * Gera dados para utilizados numa página individual sobre o item.
     * Está função depende de um objeto de configuração que informe o nome dos campos devolvidos pela base de dados.
     * Esse objeto de ser do tipo:
     *  {     *      
      "dados": "nome da stored procedure",
      "fields": {
      "id": "id do item",
      "name": "nome do item",
      "seo_description": "descricao_seo",
      "seo_title": "titulo_seo",
      "seo_keywords": "palavras_seo",
      "intro": "programa",
      "action": "call_to_action",
      "description":"descricao" ,
      "image": "fotos",
      "video": "video",
      "o_data": { dodos que serão agrupados em linhas  }
      },
      "link_name": "cursos",
      "link": "pt/cursos",
      "shortlink": "t",
      "name": "workshops"
     *  }
     * 
     * @param type $i - id do item
     * 
     * @return array com os dados com os seguintes campos
     */
    public function pagina_produto($i)
    {
        $idx = (is_numeric($i)) ? $i : FALSE;

        if ($idx && in_array($_GET['mode'], $GLOBALS['WHITELIST']))
        {

            if (!$mode = json_decode(constant($_GET['mode']), TRUE))
                return "erro";

            extract($mode['fields'], EXTR_PREFIX_ALL, "f");

            try
            {
                $result = parent::make_call($mode["dados"], array($idx));
            }
            catch (Exception $ex)
            {
                return NULL;
            }

            if (!is_array($result[0]))
                return NULL;

            $prod = $result[0];

            $images = new GestImage();
            $videos = new GestVideo();
            $social = new GestSocial();
            
            $serv['image'] = NULL;
            $serv['text']  = NULL;
            $serv['id']    = $prod[$f_id];
            $serv['nome']  = $prod[$f_name];

            if (!empty($f_intro))
                $serv['intro'] = $prod[$f_intro];

            //SEO da página
            $serv['seo_title']       = $prod[$f_seo_title];
            $serv['seo_description'] = $prod[$f_seo_description];
            $serv['seo_keywords']    = $prod[$f_seo_keywords];

            if (isset($f_action))
                $serv['action'] = $prod[$f_action];


            //links de localização do site
            $full_url_title = strtolower($prod[$f_name]);
            $full_url       = _RURL . $mode['link'] . "/" . $prod[$f_id] . "/" . parent::clean_space($full_url_title);
            $home_image     = "<img src='" . _RURL . "imagens/link_home_page.png' alt='home page " . _NAMESITE . "'>";

            $serv['map'] = "<div class='site_nav'><a href='" . _RURL . "'>$home_image</a> > <a href='" . _RURL . $mode['link'] . "'>" . $mode['link_name'] . "</a> > <a href='$full_url'>$full_url_title</a></div>";

            //Dados agrupados
            $o_text = NULL;

            if (isset($f_o_data) && is_array($f_o_data))
            {
                foreach ($f_o_data as $okey => $ovalue)
                {
                    $o_text .= ($prod[$ovalue]) ? "<p class='o_p'><span class='o_title'>$okey : </span><span class='o_text'>" . $prod[$ovalue] . "</span></p>" : "";
                }
                $serv_data = ($o_text) ? $o_text : "";
            }
            else
            {
                $serv_data = "";
            }

            //Texto
            $serv_text = NULL;

            if (isset($f_description) && is_array($f_description))
            {
                foreach ($f_description as $topic => $topic_text)
                {
                    $descrix = null;
                    if ($prod[$topic])
                        $descrix .= "<h2>$prod[$topic]</h2>";

                    if ($prod[$topic_text])
                        $descrix .= "<h3>$prod[$topic_text]</h3>";

                    $serv_text .= ($descrix) ? "<div class='topicserv'>$descrix</div>" : "";
                }
            } else
            {
                $serv_text = ($prod[$f_description]) ? $prod[$f_description] : "";
            }

            $serv['text'] = $serv_text;

            $serv['data'] = $serv_data;

            try
            {
                $serv['image'] = ($prod[$f_image]) ? $images->send_images_json($prod[$f_image], "img", $this->lg, TRUE, "class='introcimg'") : "";
                $social_img    = $images->send_images_json($prod[$f_image], "src", NULL, 0, NULL);
            }
            catch (Exception $ex)
            {
                $serv['image'] = NULL;
                $social_img    = NULL;
            }

            //formulário da página
            $serv['form'] = $this->form_message_plus(FALSE, $prod[$f_name], $mode['name'], $prod[$f_id]);

            //botões das redes sociais
            $serv['social'] = $social->social_buttons($full_url, $serv_text, NULL, $social_img, FALSE);

            return $serv;
        }
        echo "errop";
    }

}

?>