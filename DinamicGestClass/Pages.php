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
 * última alteração : 11-06-2014
 *
 * */
ini_set('display_errors', 1);
require 'Core.php';

class Pages extends Core {

    public $ender;
    public $jshp;
    private $operation_bar;

    private $item;

    public function __construct($JS) {

        parent::__construct();

        $this -> jshp = $this -> json_file($JS);
    }

    public function page_itens($page) {
        
        if (!parent::check())
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';
        
        if(!is_string($page))
            return FALSE;

        $folder = new PageGestFolder($this -> jshp,$page);

        return $folder -> make_folders();

    }
    
    public function change_folder_page($page, $item_id, $new_folder){
      if (!parent::check())
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';
        
        if(!is_string($page))
            return FALSE;

        $folder = new PageGestFolder($this -> jshp,$page);

        return $folder -> change_folder($item_id, $new_folder); 
        
    }

    private function get_item($item_id) {

        if (!$id = parent::id($item_id))
            parent::mess_alert("PG" . __LINE__);

        $query = "SELECT * FROM " . $this -> jshp['table'] . " WHERE " . $this -> jshp['admin']['private']['toke']['db'] . "='" . $id . "'";

        if (!$rhp = logex::$tcnx -> query($query))
            parent::mess_alert("PG" . __LINE__);

        return $rhp -> fetch_array();

    }

    public function edit($item_id) {
        if (parent::check())
            parent::mess_alert("PG" . __LINE__);

        $hp = $this -> get_item($item_id);
        $identifier = $hp[$this -> jshp['admin']['private']['identifier']['db']];

         $config = $this -> configurations($identifier);
        
        $edit_sheet = new OperationsBar($config);

        return $edit_sheet -> edit_item_sheet($hp);

    }

    private function configurations($object_name) {

        $item['table'] = $this -> jshp['table'];
        $item['admin']['private'] = $this -> jshp['admin']['private'];

        $item['admin']['public'] = $this -> jshp['admin']['public'];
        $item['fields'] = $this -> jshp[$object_name];
        $item['midia'] = NULL;
        $item['link'] = NULL;

        if (isset($item['fields']['midia'])) {

            $item['midia']['midia'] = $item['fields']['midia'];
            unset($item['fields']['midia']);
        }

        if (isset($item['fields']['link'])) {

            $item['link'] = $item['fields']['link'];
            unset($item['fields']['link']);
        }
        
        if (isset($item['fields']['search'])) {

            $item['link'] = $item['fields']['search'];
            unset($item['fields']['search']);
        }
        
        if (isset($item['fields']['export'])) {

            $item['link'] = $item['fields']['export'];
            unset($item['fields']['export']);
        }

        return $item;

        
    }

    public function save() {
        if (parent::check())
            parent::mess_alert("PG" . __LINE__);

        $hp = $this -> get_item($_POST['toke']);
        $identifier = $hp[$this -> jshp['admin']['private']['identifier']['db']];

        $config = $this -> configurations($identifier);
        
        $edit_sheet = new OperationsBar($config);

        return $edit_sheet -> save_item();
    }

    public function make_sheet_i($item_id) {

        if (!parent::check())
            parent::mess_alert("PG" . __LINE__);

        if (!$id = parent::id($item_id))
            parent::mess_alert("PG" . __LINE__);

        //retira os valores do item da base de dados
        $hp = $this -> get_item($item_id);
        $identifier = $hp[$this -> jshp['admin']['private']['identifier']['db']];

        //configura o item
        $config = $this -> configurations($identifier);

        $mid = NULL;

        //coloca todos os itens online, uma vez que este modulo não permite guardar itens offline
        $config['admin']['private']['status']['db'] = "estado";
        $hp['estado'] = "online";

        $sheet = new ItemSheet();
        
        if(!empty($config["midia"]))
            $mid .= $sheet -> make_sheet_content($config["midia"], $hp);

       foreach ($config['fields'] as $key => $value) {

            $sheet_content = NULL;
            $flag = NULL;

            $shp["baia"][$key] = $value;
            $sheet_content = $sheet -> make_sheet_content($shp["baia"], $hp, TRUE);
            unset($shp["baia"][$key]);

            if (in_array($key, $GLOBALS['LANGPAGES']))
                $flag = " <span class='fileboxflag'><img src='" . $GLOBALS['LANGFLAGS'][$key] . "' class='ig10M'></span> ";

            $mid .= "
                    <div class='wlang'>                        
                        $flag                                                    
                        $sheet_content 
                    </div>";
        }

        return $sheet -> make_sheet($config, $hp, $mid);

    }

}
/**
 *
 */
class PageGestFolder extends GestFolders {

    private $page = NULL;

    function __construct($argument, $item_page) {

        parent::__construct($argument);
        
        $this->page = $item_page;        

        parent::set_folder_query("SELECT DISTINCT pasta FROM outras_paginas WHERE pagina='$this->page'");
    }

    /*
     * Subescreve a função da classe mãe
     *
     */
    protected function set_item_query($folder) {

        $item_query = "SELECT DISTINCT id,nome FROM outras_paginas WHERE pagina='$this->page' AND pasta='$folder'";

        $q_item = logex::$tcnx -> query($item_query);

        return $this -> get_folders_itens($q_item, $folder);
    }

}
?>
