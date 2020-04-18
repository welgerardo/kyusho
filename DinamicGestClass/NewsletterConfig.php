<?php

/* SCRIPT NewsletterConfig.php V1.10
  11-06-2014
  COPYRIGHT MANUEL GERARDO PEREIRA 2014
  TODOS OS DIREITOS RESERVADOS
  CONTACTO: GPEREIRA@MGPDINAMIC.COM
  WWW.MGPDINAMIC.COM 
 * 
 * incio : 11-06-2014
 * 
 * ultima modificação : 11-06-2014
 * 
 * */

ini_set('display_errors', 1);
require 'Core.php';

class NewsletterConfig extends Core {
    
    #json de configuração
    private $ajs;

    function __construct($JS) {

        parent::__construct();

        $this->ajs = $this->json_file($JS);
    }

    /**
     * Formulário de edição das configurações
     * @return boolean | string HTML
     */
    public function edit() {
        if (parent::check()) {

            if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE'])) {

                return FALSE;
            }

            extract($this->ajs['fields']['manag']);

            $rslt = logex::$tcnx->query("SELECT " . $send_mail['db'] . "," . $return_mail['db'] . "," . $header['db'] . " FROM " . $this->ajs['table'] . " WHERE id=1");
            $C = $rslt->fetch_array();

            $C[$header['db']] = $this->make_photo_gallery_json($C[$header['db']], $header['image_name'], $header['captions']);

            $data_conf[0]['DB'] = $C;
            $data_conf[0]['JS'] = $this->ajs['fields']['manag'];

            $conf = $this->comun_fields_divEVO($data_conf, "UPDATE");

            return $this->make_edit_file(1, NULL, $this->form_action(), $conf, "UPDATE");
        }
    }

    /**
     * Ficha das configurações
     * @return boolean | string HTML
     */
    public function make_file_config() {
        if (parent::check()) {

            if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE'])) {

                return FALSE;
            }

            extract($this->ajs['fields']['manag']);

            $rslt = logex::$tcnx->query("SELECT " . $send_mail['db'] . "," . $return_mail['db'] . "," . $header['db'] . " FROM " . $this->ajs['table'] . " WHERE id=1");
            $file = $rslt->fetch_array();

            $dfile = $this->make_data_file("1", "Configurações da newsletter", "", NULL, NULL, NULL);

            $fl[$send_mail["name"]] = $file[$send_mail['db']];
            $fl[$return_mail["name"]] = $file[$return_mail['db']];
            $fl[$header["name"]] = $this->send_images_json($file[$header['db']], "img", NULL, 0, 'class="ig600"');

            return $dfile['header'] . "   
                        
        		 " . $this->make_box($fl) . "
         		<div class='rodape'></div>
         		
         		" . $dfile['footer'];
        }
    }

    /**
     * Salva as configurações na base de dados
     * @return boolean|string|int
     */
    public function save() {
        if (parent::check()) {

            if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE'])) {

                return FALSE;
            }

            extract($this->ajs['fields']['manag']);

            $smail = $this->validateEmail($_POST['send_mail']);
            $rmail = $this->validateEmail($_POST['return_mail']);
            $head = logex::$tcnx->real_escape_string($this->make_json_img_capt($header));

            $q = logex::$tcnx->query("UPDATE " . $this->ajs['table'] . " SET " . $send_mail['db'] . "='$smail'," . $return_mail['db'] . "='$rmail', " . $header['db'] . "='$head' WHERE id=1 ");

            if ($q) {
                return '{"result":["","1"]}';
            } else {
                return 0;
            }
        }
    }

}

?>
