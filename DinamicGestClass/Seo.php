<?php

/* SCRIPT Pages.php V1.10
  11-06-2014
  COPYRIGHT MANUEL GERARDO PEREIRA 2014
  TODOS OS DIREITOS RESERVADOS
  CONTACTO: GPEREIRA@MGPDINAMIC.COM
  WWW.MGPDINAMIC.COM 
 * 
 * inicio : 11-06-2014
 * 
 * última modificação: 11-06-2014
 * */
ini_set('display_errors', 1);
require 'Core.php';

class Seo extends Core {

    public $ender;
    public $jshp;

    public function __construct($JS) {

        parent::__construct();

        $this->jshp = $this->json_file($JS);
    }

    public function edit() {
        if (parent::check()) {

            $mode = "UPDATE";
            $message = NULL;

            $id = explode(":", $_POST['toke']);
            $id = (isset($id[1])) ? $id[1] : $id[0];

            #MANAG
            $mag = $this->jshp['fields']['manag'];

            $rhp = logex::$tcnx->query("SELECT * FROM " . $this->jshp['table'] . " WHERE " . $mag['toke']['db'] . "='" . $id . "'");
            $hp = $rhp->fetch_array();

            #data
            $hp[$mag['date_act']['db']] = $this->make_date($hp[$mag['date_act']['db']]);

            $data_manag[0]['DB'] = $hp;
            $data_manag[0]['JS'] = $this->jshp['fields']['manag'];
            $manag = $this->comun_fields_divEVO($data_manag, $mode);

            #LANG
            foreach ($GLOBALS['LANGPAGES'] as $lang) {
                if (is_array($this->jshp['fields']['comuns'][$lang])) {

                    #imagens
                    if (isset($this->jshp['fields']['comuns']['image'])) {

                        $imf = $this->jshp['fields']['comuns'][$lang]['image'];
                        @$hp[$imf['db']] = $this->make_photo_gallery_json($hp['imagens'], $imf['image_name'], $imf['captions']);
                    }

                    $data_mess[0]['DB'] = $hp;
                    $data_mess[0]['JS'] = $this->jshp['fields']['comuns'][$lang];
                    $data_mess[0]["ICON"] = $GLOBALS['LANGFLAGS'][$lang];
                    $data_mess[0]["SUFIX"] = "_" . $lang;
                    $message .= $this->comun_fields_divEVO($data_mess, $mode);
                }
            }

            #LANG

            if (isset($this->jshp['social'][$id])) {

                #imagens
                if (isset($this->jshp['social'][$id]['image'])) {

                    $imf = $this->jshp['social'][$id]['image'];
                    $hp[$imf['db']] = $this->make_photo_gallery_json($hp[$this->jshp['social'][$id]['image']['db']], $imf['image_name'],  $imf['captions'],FALSE);
                    
                }

                $data_mess[0]['DB'] = $hp;
                $data_mess[0]['JS'] = $this->jshp['social'][$id];
                $message .= $this->comun_fields_divEVO($data_mess, $mode);
            }


            return $this->make_edit_file($id, NULL, $this->form_action(), $manag . $message, $mode);
        }
    }

    public function save() {
        if (parent::check()) {

            $id = explode(":", $_POST['toke']);
            $id = (isset($id[1])) ? $id[1] : $id[0];

            #MANAG
            $manag = $this->jshp['fields']['manag'];

            #DATA
            $val = $manag['folder']['db'] . "='" . $_POST['folder'] . "'," . $manag['date_act']['db'] . "='" . $this->make_date(NULL, 1, "DATEBD") . "',";

            #OUTROS CAMPOS
            foreach ($GLOBALS['LANGPAGES'] as $lang) {

                if (!isset($this->jshp['fields']['comuns'][$lang])) {
                    break;
                }
                
                unset($this->jshp['fields']['comuns'][$lang]['name']);
                
                #imagens
                if (isset($this->jshp['fields']['comuns'][$lang]['image'])) {

                    $imgs = $this->make_json_img_capt($this->jshp['fields']['comuns'][$lang]['image']);
                    $val .= $this->jshp['fields'][$id]['comuns']['image']['db'] . "='" . logex::$tcnx->real_escape_string($imgs) . "',";

                    unset($this->jshp['fields']['comuns'][$lang]['image']);
                }


                #outros campos diferentes de imagens
                foreach ($this->jshp['fields']['comuns'][$lang] as $keyy => $valuey) {

                    $k = $keyy . "_" . $lang;
                    
                        $val .= $valuey['db'] . "='" . logex::$tcnx->real_escape_string($_POST[$k]) . "',";
                }
            }


            if (isset($this->jshp['social'][$id])) {
                
                unset($this->jshp['social'][$id]['name']);

                if (isset($this->jshp['social'][$id]['image'])) {
                    $imgs = $this->make_json_img_capt($this->jshp['social'][$id]['image']);
                    $val .= $this->jshp['social'][$id]['image']['db'] . "='" . logex::$tcnx->real_escape_string($imgs) . "',";

                    unset($this->jshp['social'][$id]['image']);
                }


                #outros campos diferentes de imagens
                foreach ($this->jshp['social'][$id] as $keyy => $valuey) {
                    
                        $val .= $valuey['db'] . "='" . logex::$tcnx->real_escape_string(@$_POST[$keyy ]) . "',";
                }
            }



            $r = logex::$tcnx->query("UPDATE " . $this->jshp['table'] . " SET " . trim($val, ",") . " WHERE " . $manag['toke']['db'] . "='$id'");
           
            if ($r) {

                return '{"result":["' . $_POST['folder'] . '","i:' . $id . '"]}';
            }
        }
    }

    public function make_file() {
        if (parent::check()) {

            #ID
            $id = explode(":", $_POST['toke']);
            $id = (isset($id[1])) ? $id[1] : $id[0];

            $mid = NULL;

            #BD
            $rhp = logex::$tcnx->query("SELECT * FROM " . $this->jshp['table'] . " WHERE " . $this->jshp['fields']['manag']['toke']['db'] . "='" . $id . "'");
            $hp = $rhp->fetch_array();

            foreach ($GLOBALS['LANGPAGES'] as $lang) {

                $ar = NULL;

                if (is_array($this->jshp['fields']['comuns'][$lang])) {

                    $fl = $this->jshp['fields']['comuns'][$lang];
                } else {

                    break;
                }



                unset($fl['name']);
                unset($fl['image']);

                foreach ($fl as $key => $value) {

                    $ar[$fl[$key]['name']] = $hp[$fl[$key]['db']];
                }

                $mid .= "<div class='p15_80'>[ <img src='" . $GLOBALS['LANGFLAGS'][$lang] . "' class='ig10M'> ] $lang" . $this->make_box($ar, "wfichap", "dv800px150xs") . "<div class='rodape'></div></div>";
            }

            if (isset($this->jshp['social'][$id])) {

                $fl = $this->jshp['social'][$id];

                $img = (isset($fl['image']['db']) && isset($hp[$fl['image']['db']])) ? $this->send_images_json($hp[$fl['image']['db']], "img", NULL, 0, 'class="i180"') : NULL;
                unset($fl['name']);
                unset($fl['image']);

                foreach ($fl as $key => $value) {

                    $ar[$fl[$key]['name']] = $hp[$fl[$key]['db']];
                }

                $mid .= $this->make_box($ar, "wficha", "dv800px150x");
            }

            $dfile = $this->make_data_file($id, $hp[$this->jshp['fields']['manag']['folder']['db']], NULL, NULL, NULL, NULL);

            return $dfile['header'] . "<div class='dv200'>
		              $img
                        </div>
                        
		        <div class='dv570xx200'>
                                <p class='p5'><span class='sp12FFM'>" . $this->jshp['fields']['manag']['date_act']['name'] . ":</span><span>" . $this->make_date($hp[$this->jshp['fields']['manag']['date_act']['db']]) . "</span></p>
		        			        
		        </div>
                        
        		 " . $mid . "
                             
         		<div class='rodape'></div>" . $dfile['footer'];
        }
    }

}

?>
