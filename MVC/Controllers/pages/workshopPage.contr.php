<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of workshopPage
 *
 * @author Gerardo
 */
class workshopPage {
    
    private $Reg;
    private $Page;
    private $Model;
    
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

         $Core = $this->Reg->getObject("core");
        
        $id = $Core->id($_GET['toke']);
        
        if($id){           

        $data = $this->Model->item(array($id));
        
        if(is_array($data)){
            
            $view = $this->Page->getView("pages/workshopPage.tpl.php");
            $lang = $this->Reg->get_language();
            
            try {
            $logo = GestImages::send_images_json($data['logotipo'], "img", $lang, 0);
        } catch (Exception $ex) {
            $logo = NULL;
        }
        
        try
            {
                $image = GestImages::send_images_json($data['fotos'], "img", $this->lg, TRUE, "class='introcimg'") ;
                $social_img    = GestImages::send_images_json($data['fotos'], "src", NULL, 0, NULL);
            }
            catch (Exception $ex)
            {
                $image = NULL;
                $social_img    = NULL;
            }
        
            try {
               $social_text = $Core->cut_text($data['introducao'],150);  
            } catch (Exception $ex) {
                $social_text = NULL;
            }   
           
        
        $tag[] = "{lang}";
        $replacer[] = $lang;

        $tag[] = "{title}";
        $replacer[] = $data['titulo_seo'];


        $tag[] = "{keywords}";
        $replacer[] = $data['palavras_seo'];

        $tag[] = "{meta-description}";
        $replacer[] = $data['descricao_seo'];

        $tag[] = "{site-url}";
        $replacer[] = _RURL;

        $tag[] = "{app_face}";
        $replacer[] = "";        

        $tag[] = "{logo}";
        $replacer[] = $logo;

        $tag[] = "{home-url}";
        $replacer[] = _CUR_URL;
        
        $tag[] = "{page-url}";
        $replacer[] = $data['url'];

        $tag[] = "{page-name}";
        $replacer[] = strtolower($data['nome']);
        
        $tag[] = "{image}";
        $replacer[] = $image;
        
        $tag[] = "{name}";
        $replacer[] = $data['nome'];
        
        $tag[] = "{intro-text}";
        $replacer[] = $data['descricao'];
        
        $tag[] = "{local}";
        $replacer[] = $data['local'];
        
        $tag[] = "{formador}";
        $replacer[] = $data['formador'];
        
        $tag[] = "{inicio}";
        $replacer[] = $data['dia_inicio'];
        
        $tag[] = "{hora}";
        $replacer[] = $data['hora_inicio'];
        
        $tag[] = "{numero-horas}";
        $replacer[] = $data['numero_horas'];
        
        $tag[] = "{numero-participantes}";
        $replacer[] = $data['max_participantes'];
        
        $tag[] = "{data-inscricao}";
        $replacer[] = $data['data_inscricao'];
        
        $tag[] = "{preco}";
        $replacer[] = $data['preco'];
        
        $tag[] = "{programa}";
        $replacer[] = $data['programa'];
        
        $tag[] = "{call-to-action}";
        $replacer[] = $data['call_to_action'];
        
        $tag[] = "{data-type}";
        $replacer[] = $data['id'];
        
        $tag[] = "{valor-assunto}";
        $replacer[] = $data['nome'] ;
        
        $tag[] = "{ano}";
        $replacer[] = strftime("%Y") ;
        
        $tag[] = "{proprietario}";
        $replacer[] = $data['nome_empresa'] ;
        
        $tag[] = "{social-image}";
        $replacer[] = $social_img ;
        
        $tag[] = "{social-text}";
        $replacer[] = $social_text ;
        
        $content = str_replace($tag, $replacer, $view);

        $this->Reg->getObject("page")->set_content($content);
        
        }
        
        
    }
        }
}
