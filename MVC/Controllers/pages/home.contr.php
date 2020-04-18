<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class home {

    private $Reg;
    private $Page;
    private $data;
    private $lang;

    function __construct(MGPDINAMIC $registry, $make_page = TRUE) {

        $this->Reg = $registry;
        $this->Page = $registry->getObject("page");

        require_once 'Models/pages/MHomePage.mod.php';

        $this->data = new MHomePage($registry);

        $this->lang = $registry->get_language();


        if ($make_page) {
            $this->make_page();
        }
    }

    private function make_page() {

        $view = $this->Page->getView("pages/homePage.tpl.php");

        $header_data = $this->data->getHomeData("header");

        $lang = $this->Reg->get_language();
        
        $defesa = $this->data->getHomeData("defesa");
        $saude = $this->data->getHomeData("saude");
        

        try {
            $logo = GestImages::send_images_json($header_data['logotipo'], "img", $lang, 0);
        } catch (Exception $ex) {
            $logo = NULL;
        }

        try {
            $banner = GestImages::send_images_json($this->data->getHomeData("banner")[0]['image'], "img", $lang, 0, "class='img_banner'");
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

        $tag[] = "{banner}";
        $replacer[] = $banner;

        $tag[] = "{defesa-titulo}";
        $replacer[] = $defesa[0]['title'];

        $tag[] = "{defesa-texto}";
        $replacer[] = $defesa[0]['text1'];
        
        $tag[] = "{saude-titulo}";
        $replacer[] = $saude[0]['title'];

        $tag[] = "{saude-texto}";
        $replacer[] = $saude[0]['text1'];
        
        $tag[] = "{video}";
        $replacer[] = $this->getVideo();

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
     * @param type $difer
     * @return type
     */
    private function getVideo() {

        $video = json_decode($this->data->getHomeData("video"), TRUE);
        
        if($video && $video['from'] == "embeded"){
            
            return $video['video'];
        }
    }

}
