<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MWorkshops
 *
 * @author Gerardo
 */
class MWorkshops {
    
    private $DB;
    
    
    public function __construct(MGPDINAMIC $registry) {
        
        $this->DB = $registry->getObject("core");
    }
    
        /**
     *
     * @param string $page - nome identificador da página
     * @return boolean|array em caso de sucesso. A array tem os seguintes elementos: "title", "decri", "keywords", "g_analytics", "app_face", "adm_face"
     */
    public function headerData() {

        try {
            $result = $this->DB->make_call("spSeoFrontEnd", array("cursos"));
        } catch (Exception $ex) {
            $this->home_data['header'] = NULL;
        }

        if (!empty($result[0])){
        
            return $result[0];
            
        } 
    }
    
    public function intro(){
        
        $param[] = "cursos";
        $param[] = "";       
        
        try {
            
            $result = $this->DB->make_call(_OP, $param);
            
        } catch (Exception $ex) {
            
            
        }
        
        if(is_array($result)){
            
            foreach ($result as $key => $value) {           
                
                $data[$value['grupo']][] = array("title"=>$value['titulo'],"text1"=>$value['texto1'],"text2"=>$value['texto2'],"image"=>$value['fotos']);

            }
            
           return $data;
        }
        
        
    }
}
