<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MHomePage {

    private $Reg;
    private $DB;
    private $home_data;

    function __construct(MGPDINAMIC $registry) {

        $this->Reg = $registry;

        $this->DB = $registry->getObject("core");
        
        $this->headerData();
        $this->homePageBlocks();
    }

    
    public function getHomeData($key){
        return $this->home_data[$key];
    }
    /**
     *
     * @param string $page - nome identificador da página
     * @return boolean|array em caso de sucesso. A array tem os seguintes elementos: "title", "decri", "keywords", "g_analytics", "app_face", "adm_face"
     */
    private function headerData() {

        try {
            $result = $this->DB->make_call("spSeoFrontEnd", array("home"));
        } catch (Exception $ex) {
            $this->home_data['header'] = NULL;
        }

        if (!empty($result[0])){
        
            $this->home_data['header'] = $result[0];
        } else {
            $this->home_data = NULL;
        }
    }
    
    /**
     * 
     * @param type $page
     * @param type $group
     * @return type
     */
    private function homePageBlocks(){
        
        $param[] = "home";
        $param[] = "";
        
        
        
        try {
            
            $result = $this->DB->make_call(_OP, $param);
            
        } catch (Exception $ex) {
            
            
        }
        
        if(is_array($result)){
            
            foreach ($result as $key => $value) {           
                
                $this->home_data[$value['grupo']][] = array("title"=>$value['titulo'],"text1"=>$value['texto1'],"text2"=>$value['texto2'],"image"=>$value['fotos']);

            }
        }
        
        
    }

}
