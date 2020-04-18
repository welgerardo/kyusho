<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of workshopsPage
 *
 * @author Gerardo
 */
class workshopsPage {

    private $Reg;
    private $Page;
    private $Model;
    private $Workshops_model;

    public function __construct(MGPDINAMIC $registry, $page = TRUE) {

        $this->Reg = $registry;

        $this->Page = $registry->getObject("page");

        require "Models/MUniversal.mod.php";
        $this->Model = new MUniversal($registry, _WORKSHOPS);
        
        require "Models/workshops/MWorkshops.mod.php";
        $this->Workshops_model = new MWorkshops($registry);

        if ($page) {
            
            $this->makePage();
        }
    }

    public function makePage() {

         $view = $this->Page->getView("pages/workshopsPage.tpl.php");

        $header_data = $this->Workshops_model->headerData();

        $lang = $this->Reg->get_language();
        
        $intro = $this->Workshops_model->intro();
        

        try {
            $logo = GestImages::send_images_json($header_data['logotipo'], "img", $lang, 0);
        } catch (Exception $ex) {
            $logo = NULL;
        }

        try {
            $intro_image = GestImages::send_images_json($intro["intro"][0]['image'], "img", $lang, 0, "class='introcimg'");
        } catch (Exception $ex) {
            $banner = NULL;
        }

        $tag[] = "{lang}";
        $replacer[] = $lang;

        $tag[] = "{title}";
        $replacer[] = $header_data['titulo'];


        $tag[] = "{keywords}";
        $replacer[] = $header_data['palavras'];

        $tag[] = "{meta-description}";
        $replacer[] = $header_data['descricao'];

        $tag[] = "{site-url}";
        $replacer[] = _RURL;

        $tag[] = "{maps-key}";
        $replacer[] = "";

        $tag[] = "{maps-key}";
        $replacer[] = "";

        $tag[] = "{maps-key}";
        $replacer[] = "";

        $tag[] = "{gps - coord}";
        $replacer[] = "";

        $tag[] = "{logo}";
        $replacer[] = $logo;

        $tag[] = "{intro-imagem}";
        $replacer[] = $intro_image;

        $tag[] = "{intro-titulo}";
        $replacer[] = $intro["intro"][0]['title'];

        $tag[] = "{intro-texto}";
        $replacer[] = $intro["intro"][0]['text1'];
        
        $tag[] = "{cursos}";
        $replacer[] = $this->workshops();

        $tag[] = "{desenvolvido-por}";
        $replacer[] = _F_TXTDEVELOP ;
        
        $tag[] = "{direitos}";
        $replacer[] = _F_TXTRIGHTS ;
        
        $tag[] = "{ano}";
        $replacer[] = strftime("%Y") ;
        
        $tag[] = "{proprietario}";
        $replacer[] = "" ;
        
        $tag[] = "{newsletter-text}";
        $replacer[] = _F_TXTNEWSLETTER ;
        
        $tag[] = "{newsletter-input-value}";
        $replacer[] = _L_FORMSEND ;


        $content = str_replace($tag, $replacer, $view);

        $this->Reg->getObject("page")->set_content($content);
    }

    /**
     * 
     * @param type $itens
     * @param type $link
     * @return boolean|string
     */
    private function workshops() {

        $itens = $this->Model->itens();

        if ($itens) {

            $view = $this->Page->getView("workshops/workshopSummary.tpl.php");

            $tags[] = "{mr}";
            $tags[] = "{image}";
            $tags[] = "{name}";
            $tags[] = "{local}";
            $tags[] = "{intro}";
            $tags[] = "{workshop-url}";
            $tags[] = "{anchor-call}";
            $tags[] = "{anchor-text}";

            foreach ($itens as $item) {
                
                $mr = NULL;

                try {
                    $fotos = GestImages::send_images_json($item["fotos"], "img", "pt", 0, "class='introcimg'") ;
                } catch (Exception $ex) {
                    $fotos = NULL;
                }



                $replacer[] = $mr;
                $replacer[] = $fotos;
                $replacer[] = $item["nome"];
                $replacer[] = $item["local"];
                $replacer[] = $item["introducao"];
                $replacer[] = $item['url'];
                $replacer[] = _M_ANCHOR;
                $replacer[] = mb_strtolower($item["nome"], 'UTF-8');

                $data .= str_replace($tags, $replacer, $view);

                $mr = ($mr) ? NULL : "mr";
            }

            return $data;
        }
    }

}
