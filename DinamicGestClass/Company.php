<?php
/*SCRIPT Company.php V1.10
 11-06-2013
 COPYRIGHT MANUEL GERARDO PEREIRA 2013
 TODOS OS DIREITOS RESERVADOS
 CONTACTO: GPEREIRA@MGPDINAMIC.COM
 WWW.MGPDINAMIC.COM

 inicio: 11-06-2014

 ultima alteração: 11-06-2014

 ***********************************/

require 'Core.php';
class Company extends Core {

    public $ajs;

    function __construct($JS) {

        parent::__construct();

        $this -> ajs = $this -> json_file($JS);

    }

    public function edit() {
        if (parent::check()) {

            $rcomp = logex::$tcnx -> query("SELECT * FROM " . $this -> ajs['table'] . " WHERE id=1");
            $C = $rcomp -> fetch_array();

            $about = NULL;

            $C[$this -> ajs['fields']['manag']['date_act']['db']] = $this -> make_date($C[$this -> ajs['fields']['manag']['date_act']['db']]);

            #MANAGAMENT
            $manag_data[0]["DB"] = $C;
            $manag_data[0]["JS"] = $this -> ajs['fields']['manag'];
            $mag = $this -> comun_fields_divEVO($manag_data, "UPDATE");

            #COMPANY DATA
            $comp_data[0]["DB"] = $C;
            $comp_data[0]["JS"] = $this -> ajs['fields']['comuns'];
            $comp = $this -> comun_fields_divEVO($comp_data, "UPDATE");

            #ABOUT COMPANY
            foreach ($GLOBALS['LANGPAGES'] as $value) {

                $about_data[0]["DB"] = $C;
                $about_data[0]["JS"] = $this -> ajs['fields'][$value];
                $about_data[0]["SUFIX"] = "_" . $value;
                $about_data[0]["ICON"] = $GLOBALS['LANGFLAGS'][$value];
                $about .= $this -> comun_fields_divEVO($about_data, "UPDATE");
            }

            #IMAGES
            $images = $this -> ajs['fields']['media'];

            if (isset($images['logo']))
                @$C[$images['logo']['db']] = $this -> make_photo_gallery_json($C[$images['logo']['db']], $images['logo']['image_name'], $images['logo']['captions']);

            if (isset($images['company_image']))
                @$C[$images['company_image']['db']] = $this -> make_photo_gallery_json($C[$images['company_image']['db']], $images['company_image']['image_name'], $images['company_image']['captions']);

            if (isset($images['contacts_image']))
                @$C[$images['contacts_image']['db']] = $this -> make_photo_gallery_json($C[$images['contacts_image']['db']], $images['contacts_image']['image_name'], $images['contacts_image']['captions']);

            $media_data[0]["DB"] = $C;
            $media_data[0]["JS"] = $this -> ajs['fields']['media'];
            $media = $this -> comun_fields_divEVO($media_data, "UPDATE");

            return $this -> make_edit_file(1, NULL, $this -> form_action(), $mag . $comp . $about . $media, "UPDATE");

        }
    }

    public function save() {
        if (parent::check()) {

            $columns = $this -> ajs;

            array_map(array("Core", "text_clean_json"), $_POST);

            extract($columns['fields']);

            #MANAGEMENT
            $comp_data = $manag['date_act']['db'] . "='" . $GLOBALS['NOW'] . "'";

            #IMAGES
            if (isset($media['logo'])) {

                $imgs_1 = $this -> make_json_img_capt($media['logo']);
                $comp_data .= "," . $media['logo']['db'] . "='" . logex::$tcnx -> real_escape_string($imgs_1) . "'";
            }

            if (isset($media['company_image'])) {

                $imgs_2 = $this -> make_json_img_capt($media['company_image']);
                $comp_data .= "," . $media['company_image']['db'] . "='" . logex::$tcnx -> real_escape_string($imgs_2) . "'";
            }

            if (isset($media['contacts_image'])) {

                $imgs_3 = $this -> make_json_img_capt($media['contacts_image']);
                $comp_data .= "," . $media['contacts_image']['db'] . "='" . logex::$tcnx -> real_escape_string($imgs_3) . "'";
            }

            #COMPANY DATA
            foreach ($columns['fields']['comuns'] as $key => $value) {

                if (isset($_POST[$key]))
                    $comp_data .= "," . $value['db'] . "='" . logex::$tcnx -> real_escape_string($_POST[$key]) . "'";
            }

            #TEXT
            foreach ($GLOBALS['LANGPAGES'] as $lang) {

                foreach ($columns['fields'][$lang] as $keyy => $valuey) {

                    $k = $keyy . "_" . $lang;

                    if (isset($_POST[$k]))
                        $comp_data .= "," . $valuey['db'] . "='" . logex::$tcnx -> real_escape_string($_POST[$k]) . "'";
                }
            }

            $query = "UPDATE " . $columns['table'] . " SET " . $comp_data . " WHERE id=1";
            $rslt = logex::$tcnx -> query($query);

            if ($rslt) {

                return '{"save":1}';

            } else {

                return '{"alert":"Por favor, tente de novo.\\nSe não conseguir realizar a operação, entre em contato com\\n a assistencia técnica e informe este numero: ' . logex::$tcnx -> error . ' "}';
            }

        }
    }

    public function make_file() {
        if (!parent::check())
            return '{"alert":"Não tem permissão para realizar esta operação. CODE:021"}';

        $rcomp = logex::$tcnx -> query("SELECT * FROM " . $this -> ajs['table'] . " WHERE id=1");
        $comp = $rcomp -> fetch_array();
        
         $sheet = new ItemSheet;

        return $sheet -> make_sheet($this -> ajs, $comp);

        if (parent::check()) {

            $comp_data = NULL;

            $dfile = $this -> make_data_file("1", NULL, "EMPRESA", NULL, NULL, NULL);

            /*foreach ($this->ajs['fields']['pt'] as $value){

             if(is_array($value)){
             $box[$value['name']] = $comp[$value['db']];}
             }*/

            foreach ($this->ajs['fields']['comuns'] as $valuex) {
                if (isset($valuex['db']) && isset($comp[$valuex['db']]))
                    $comp_data .= "<p class='p2'><span class='sp12FFM'>" . $valuex['name'] . ":</span><span>" . $comp[$valuex['db']] . "</span></p>";
            }

            return $dfile['header'] . $this -> make_image_box($this -> ajs['fields']['media'], $comp, TRUE) . "
            
            <div class='dv800px150x'>
                " . $comp_data . "
            </div>
            " . $this -> make_box_evo($this -> ajs['fields']['pt'], $comp) . "
            <div class='rodape'></div>
            " . $dfile['footer'];

        }
    }

}
?>
