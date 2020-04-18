<?php

/* SCRIPT SNoticias.php V2.0
  24-07-2012
  COPYRIGHT MANUEL GERARDO PEREIRA 2012
  TODOS OS DIREITOS RESERVADOS
  CONTACTO: GPEREIRA@MGPDINAMIC.COM
  WWW.MGPDINAMIC.COM */
ini_set('display_errors', 1);
require_once 'SConfig.php';
require_once 'SComuns.php';

class SProdutos10 extends SComuns {

    private $modex;
    public $titulo;
    public $description;
    public $image;
    public $buttons;

    public function __construct() {
        
        parent::__construct();

        $lg = (in_array($_GET['lang'], $GLOBALS['LANGPAGES'])) ? $_GET['lang'] : $GLOBALS['LANGPAGES'][0];

        require_once "SLang" . strtoupper($lg) . ".php";

        $this->lang = $_GET['lang'];

        $this->modex = $_GET['mode'];
    }

    public function mostra_produtos() {

        if (in_array($this->modex, $GLOBALS['WHITELIST'])) {

            $mode = json_decode(constant($this->modex), TRUE);

            $tcnx = new mysqli(_HT, _US, _PS, _DB);
            $tcnx->set_charset("utf8");

            foreach ($mode['fields'] as $key => $value) {

                $fields .= $value . ",";
                $$key = $value;
            }

            $rslt = $tcnx->query("SELECT " . rtrim($fields, ",") . " FROM " . $mode['table'] . " WHERE estado='online' ORDER BY order_index DESC");

            while ($prd = $rslt->fetch_array()) {
                
                $ft = ($prd[$image]) ? $this->send_images_json_i($prd[$image], "img", "pt", TRUE, "class='imgserv'"):"";
               
                $prod .= "		
							<a href='" . _RURL . $mode['link'] . "/" . $prd[$id] . "/" . $this->clean_space($prd[$name]) . "' class='serv'>
								<div class='titprodu'>" . $prd[$name] . "</div>
								$ft				
								<div class='textserv'>" . $prd[$intro] . "<p ><span class='mais'>saiba mais sobre ".strtolower($prd[$name])."</span></p></div>
							</a>	
			";
            }

            $rslt->free();
            $tcnx->close();

            return $prod;
        } else {

            header("location:" . _PUREURL);
        }
    }

    public function pagina_produto($i) {

        $idx = (is_numeric($i)) ? $i : FALSE;

        if ($idx && in_array($this->modex, $GLOBALS['WHITELIST'])) {

            $mode = json_decode(constant($this->modex), TRUE);

            foreach ($mode['fields'] as $key => $value) {

                $$key = $value;
            }

            $tcnx = new mysqli(_HT, _US, _PS, _DB);
            $tcnx->set_charset("utf8");

            $rslt = $tcnx->query("SELECT * FROM " . $mode['table'] . " WHERE estado='online' AND id='$idx' LIMIT 1");

            $prod = $rslt->fetch_array();

            $rslt->free();
            $tcnx->close();

            $this->titulo = $prod[$name];

            $this->description = $this->cut_text($prod[$intro], 150, FALSE);

            $this->image = $prod[$image];

            $carct = ($prod[$caracteristics]) ? "<div class='servcaract'>" . $prod[$caracteristics] . "</div>" : "";
            
            
            if(is_array($description)){
                
                foreach ($description as $topic => $topic_text) {
                        
                    if($prod[$topic])
                        $descri .="<h2>$prod[$topic]</h2>";
                    
                    if($prod[$topic_text])
                        $descri .="<p>$prod[$topic_text]</p>";
                    
                }
                
                
            } else{
                
                $descri = ($prod[$description]) ? "<div class='servdescri'>" . $prod[$description] . "</div>" : "";
            }
             
            $ft = ($prod[$image]) ? $this->send_images_json_i($prod[$image], "img", "pt", TRUE, "class='logoserv'"):"";

            return "
				<div class='site_nav'>
				    <a href='"._RURL."'><img src='"._RURL."imagens/link_home_page.png' alt='home page mgpdinamic empresa cria sites e seo'></a> 
				    > 
				    <a href='"._RURL.$mode['link']."'>".$mode['link_name']."</a> 
				    > 
				    <a href='"._RURL.$mode['link']."/".$prod[$id]."/".str_replace(" ", "-", strtolower($prod[$name]))."'>".strtolower($prod[$name])."</a>
				</div>
				<div class='serv1'>			
					
					$ft
				
					<p class='servinfo'>" . _PRODTEXTFORM . "</p>
				
					" . $this->form_message_plus("", $prod[$name], $mode['name'], $prod[$id]) . "		
				</div>
			
				<div class='serv2'>
				
					<h1 class='nomeserv'>" . $prod[$name] . "</h1>
					
					$descri
					
					$carct
					
					<div class='isocial'>
					
						" . $this->social_buttons(urldecode(_RURL . $mode['link'] . "/" . $prod[$id] . "/" . $this->clean_space($prod[$name])), urldecode(_SRURL . $mode['shortlink'] . $prod[$id]), $this->description, $prod[$image], TRUE) . "
						
					</div>
			         ".$this->other_products($prod[$id])."
				</div>		
		";
        
        }
    }
     public function pagina_produto2($i) {

        $idx = (is_numeric($i)) ? $i : FALSE;

        if ($idx && in_array($this->modex, $GLOBALS['WHITELIST'])) {

            $mode = json_decode(constant($this->modex), TRUE);

            foreach ($mode['fields'] as $key => $value) {

                $$key = $value;
            }

            $tcnx = new mysqli(_HT, _US, _PS, _DB);
            $tcnx->set_charset("utf8");

            $rslt = $tcnx->query("SELECT * FROM " . $mode['table'] . " WHERE estado='online' AND id='$idx' LIMIT 1");

            $prod = $rslt->fetch_array();

            $rslt->free();
            $tcnx->close();

            $this->titulo = $prod[$name];

            $this->description = $this->cut_text($prod[$intro], 150, FALSE);

            $this->image = $prod[$image];

            $carct = ($prod[$caracteristics]) ? "<div class='servcaract'>" . $prod[$caracteristics] . "</div>" : "";
            
            
            if(is_array($description)){
                
                foreach ($description as $topic => $topic_text) {
                   $descrix = null;     
                    if($prod[$topic])
                        $descrix .="<h2>$prod[$topic]</h2>";
                    
                    if($prod[$topic_text])
                        $descrix .="<p>$prod[$topic_text]</p>";
                    
                    $descri .= "<div class='topicserv'><div class='topicicon'>$descrix</div></div>";
                }
                
                
            } else{
                
                $descri = ($prod[$description]) ? "<div class='servdescri'>" . $prod[$description] . "</div>" : "";
            }
             
            $ft = ($prod[$image]) ? $this->send_images_json_i($prod[$image], "img", "pt", TRUE, "class='logoserv_2'"):"";

# <h1 class='nomeserv_2'>" . $prod[$name] . "</h1><img src='"._RURL."n_imagens/ame2.png' class='topicservicon'>
            
            $this->buttons = "
                    <div class='navbuttons'>
                        <div class='buttons' data-type='mail'>
                            "._B_ORCA."
                        </div>
                        <div class='buttons' data-type='phone'>
                            "._B_CHAM."
                        </div>
                    </div>            
            ";

            return $this->form_message_plus(1, $prod[$name], $mode['name'], $prod[$id]) . " 
               
                    <div class='navlinks'>
                        <a href='"._RURL."'><img src='"._RURL."imagens/link_home_page.png' alt='home page mgpdinamic empresa cria sites e seo'></a> 
                        > 
                        <a href='"._RURL.$mode['link']."'>".$mode['link_name']."</a> 
                        > 
                        <a href='"._RURL.$mode['link']."/".$prod[$id]."/".str_replace(" ", "-", strtolower($prod[$name]))."'>".strtolower($prod[$name])."</a>
                    </div>
                    
                    
                </div>
                <div class='serv1_2'>       
                    <img src='"._RURL."n_imagens/topserv.jpg' >
                    <div class='pagetitle'>
                    <h1 class='nomeserv_2'>" . $prod[$name] . "</h1>
                    </div>
                     
                </div>
            
                <div class='serv2_2'>
                
                   
                    
                    $descri
                    
                    $carct
                    <div class='navbuttons_main'>
                        <div class='mainbutton' data-type='mail'>
                        "._B_ORCA."
                        </div>
                        <div class='mainbutton' data-type='phone'>
                            "._B_CHAM."
                        </div>
                    </div> 
                    <div class='isocial'>
                    
                        " . $this->social_buttons(urldecode(_RURL . $mode['link'] . "/" . $prod[$id] . "/" . $this->clean_space($prod[$name])), urldecode(_SRURL . $mode['shortlink'] . $prod[$id]), $this->description, $prod[$image], TRUE) . "
                        
                    </div>
                     ".$this->other_products($prod[$id])."
                </div>      
        ";
        
       
        }
    }
    public function other_products($I=FALSE){
        
        $idx = (is_numeric($I)) ? $I : FALSE;
     
        if($idx){
            
            $mode = json_decode(constant($this->modex), TRUE);
            
            extract($mode['fields']);

            $tcnx = new mysqli(_HT, _US, _PS, _DB);
            $tcnx->set_charset("utf8");

            $rslt = $tcnx->query("SELECT * FROM " . $mode['table'] . " WHERE estado='online' AND id<>'$idx' ORDER BY order_index DESC");
            

            while($prod = $rslt->fetch_array()){
                
                $img = ($prod[$image]) ? $this->send_images_json_i($prod[$image], "img", "pt", TRUE, "class='op_img'"):"";
                
               $otp .="
                    <li class='op_li'>
                        $img  <a href='"._RURL.$mode['link']."/".$prod[$id]."/".str_replace(" ", "-", strtolower($prod[$name]))."' class='op_a'>".strtolower($prod[$name])."</a>
                    </li>
               " ;
            }
            
                        
           return "<div class='op_wrap'>"._OTHPRODUCTSNAME."</div><ul class='op_ul'>$otp</ul>"; 
        }
    }

}

?>