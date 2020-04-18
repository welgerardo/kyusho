<?php
/***************************************
 SCRIPT Products.php V3.00
 27-06-2014
 COPYRIGHT MANUEL GERARDO PEREIRA 2014
 TODOS OS DIREITOS RESERVADOS
 CONTACTO: GPEREIRA@MGPDINAMIC.COM
 WWW.MGPDINAMIC.COM
****************************************/

ini_set('display_errors', 0);
require 'Core.php';

class Products extends Core {

    private $product_config;

    public function __construct($JS) {

        parent::__construct();

        $this -> product_config = parent::json_file($JS);
    }

    public function get_product_config() {

        return $this -> product_config;
    }

    /*
     *edita um produto
     */
    public function edit() {
        if (parent::check()) {

            $id = parent::id($_POST['toke']);

            if (!$id) {

                return '{"alert":"Não foi possivel realizar esta operação. CODE:E002"}';
            }

            if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE'])) {

                return '{"alert":"Não foi possivel realizar esta operação. CODE:E001"}';
            }

            $q = logex::$tcnx -> query("SELECT * FROM " . $this -> product_config['table'] . " WHERE " . $this -> product_config['admin']['private']['toke']['db'] . "=" . $id . " LIMIT 1");

            return $this -> make_product($q -> fetch_array(), "UPDATE");

        } else {

            return '{"alert":"Não tem permissão para realizar esta operação. CODE:E003"}';
            exit ;
        }
    }

    /*
     * abre ficha para edição ou adição de um produto
     * $C = dados do produto guardados na base de dados
     * mode = ADD para adição de produto UPDATE para edição
     */
    public function make_product($C, $mode = "ADD") {
        if (parent::check()) {

            $pconf = $this -> product_config;
            
            extract($pconf['admin']['private'], EXTR_PREFIX_ALL, "adm");
            
            $id = FALSE;
           

            if (in_array($_POST['flag'], $GLOBALS['FLAGSMODE'])) {

                $id = parent::id($C[$adm_toke['db']]);

                if ($_POST['flag'] == "UPDATE" && !$id) {

                    $mode = FALSE;
                }
            } else {

                $mode = FALSE;
            }

            if ($mode) {
                    
                
                $manag_data['DB'] = $C;
                $manag_data['JS'] = $this -> product_config;
                $manag = $this -> make_add_block($manag_data, $mode);

                return $this -> make_edit_file($id, $folder, $this -> form_action(), $manag, $mode);

            } else {

                return '{"alert":"Não tem permissão para realizar esta operação. CODE:MP001"}';

            }
        }
    }

    /*
     * cria a ficha de um produto
     *
     */
    public function make_file() {

        if (parent::check()) {
            
            $sheet = new ItemSheet;

            $id = $sheet->validate_file_input();
            
            if (!$id) {

                return '{"alert":"Não foi possivel realizar esta operação. CODE:MF002"}';
            }
            
            #PROCURA DADOS NA TABELA CONTACTOS
            
            $idx = $this -> product_config['admin']['private']['toke']['db'];
            
            $RF = logex::$tcnx -> query("SELECT * FROM " . $this -> product_config['table'] . " WHERE $idx=$id LIMIT 1");
            $CT = $RF -> fetch_array();

            $RF -> free();
            
            $adm_block = $sheet->make_data_file_i($this -> product_config,$CT);
            
            return $adm_block;           

        } else {

            return '{"alert":"Não tem permissão para realizar esta operação. CODE:MF003"}';
            
        }
    }

    public function order() {

        extract($this -> product_config['adm_data']);

        return parent::make_order($toke['db'], $icon['db'], $identifier['db'], $this -> product_config['table'], $order['db']);
    }

    public function ordering() {

        extract($this -> product_config['adm_data']);

        return parent::save_new_order($this -> product_config['table'], $toke['db'], $order['db']);
    }

    /*
     * salva ou atualiza um produto
     *
     */
    public function save_product() {
        if (parent::check()) {

            $save_mode = ($_POST['filemode'] == "UPDATE" || $_POST['filemode'] == "ADD") ? $_POST['filemode'] : FALSE;

            if ($save_mode) {

                $columns = $this -> product_config;

                array_map(array("Core", "text_clean_json"), $_POST);

                $query = NULL;
                $titulo = NULL;
                $val = NULL;
                $product_fields = NULL;

                #GESTÃO
                extract($columns['adm_data'], EXTR_PREFIX_ALL, "adm");

                $identy = parent::id($_POST['toke']);

                $fld = parent::validate_name($_POST['folder']);

                $sts = (isset($_POST['status']) && $_POST['status'] === "online") ? "online" : "offline";

                $dt = ($save_mode == "UPDATE" && $identy) ? $adm_date_act['db'] : $adm_date['db'];

                $face = filter_input(INPUT_POST, "publish_face", FILTER_VALIDATE_INT);

                $product_fields = (isset($adm_folder['db'])) ? $adm_folder['db'] . "='" . logex::$tcnx -> real_escape_string($fld) . "'," : "";
                $product_fields .= (isset($adm_status['db'])) ? $adm_status['db'] . "='" . logex::$tcnx -> real_escape_string($sts) . "'," : "";
                $product_fields .= $dt . "='" . $GLOBALS['NOW'] . "',";

                
                #PRODUCT
                foreach ($columns['fields'] as $key => $value) {
                    foreach ($value as $kf => $field) {
                        if (is_array($field)) {
                            switch($field["data-type"]) {
                                case 'DATE' :
                                    $product_fields .= $field['db'] . "='" . parent::make_date($_POST[$kf]) . "',";
                                    break;
                                case 'IMAGE' :
                                    $imgs = parent::make_json_img_capt($field);
                                    $product_fields .= $field['db'] . "='" . logex::$tcnx -> real_escape_string($imgs) . "',";
                                    break;
                                case 'VIDEO' :
                                    $product_fields .= $field['db'] . "='" . logex::$tcnx -> real_escape_string(parent::get_video()) . "',";
                                    break;
                                default :
                                    if (array_key_exists("db", $field) && !is_array($field['db'])) {
                                        if (in_array($key, $GLOBALS['LANGPAGES'])) {
                                            $kf = $kf . "_" . $key;
                                        }
                                        $product_fields .= " $field[db] ='" . logex::$tcnx -> real_escape_string($_POST[$kf]) . "',";
                                    }
                                    break;
                            }

                        }
                    }
                }

               

                
                #ADICIONA
                if ($save_mode == "ADD" && !$identy) {

                    $ni = logex::$tcnx -> query("SELECT MAX(order_index) as IO FROM " . $columns['table']);
                    $n = $ni -> fetch_array();
                    $oi = $n['IO'] + 1;

                    $query = "INSERT INTO " . $columns['table'] . " SET " . rtrim($product_fields, ",") . ", order_index=" . $oi;
                }

                #ATUALIZA
                if ($save_mode == "UPDATE" && $identy) {

                    $query = "UPDATE " . $columns['table'] . " SET "  . rtrim($product_fields, ",") . " WHERE " . $adm_toke['db'] . "=$identy";
                }
                
                if (!$query) {

                    return "{\"alert\":\"Não tem permissão para realizar esta operação. CODE:003\"}";
                    exit ;
                }


                    $rslt = logex::$tcnx -> query($query);

                    $id = (logex::$tcnx -> insert_id) ? logex::$tcnx -> insert_id : $identy;

                    if ($rslt) {

                        #REDES SOCIAIS
                        if ($face && $sts == "online") {

                            foreach ($GLOBALS['LANGPAGES'] as $lang) {

                                if (is_array($this -> ajs['export'][$lang])) {

                                    $id_note = parent::send_social($id, $this -> ajs['export'][$lang], $lang);

                                    if ($id_note) {

                                        logex::$tcnx -> query("UPDATE " . $this -> ajs['table'] . " SET notas = CONCAT_WS(',',notas,'$id_note') WHERE " . $toke['db'] . "=" . $id);
                                    }

                                }
                            }
                        }

                        #ATUALIZA O SITEMAP
                        parent::update_sitemap();

                        return '{"result":["' . $_POST['folder'] . '","' . $id . '"]}';

                    } else {

                        #PRODUTO COM O MESMO NOME
                        if (logex::$tcnx -> errno == 1062) {

                            return '{"alert":"Já existe um item  com esse nome."}';
                        }
                        #ERRO A SALVAR
                        else {

                            return '{"alert":"Por favor, tente de novo.\\nSe não conseguir realizar a operação, entre em contato com\\n a assistencia técnica e informe este numero: ' . logex::$tcnx -> error . '"}';
                        }
                    }

                    $rslt -> free();
            } else {

                return '{"alert":"Não tem permissão para realizar esta operação. CODE:002"}';
                exit ;
            }
        } else {

            return '{"alert":"Não tem permissão para realizar esta operação. CODE:001"}';
            exit ;
        }
    }

    public function delete_product() {
        if (parent::check()) {

            $id = parent::id($_POST['toke']);

            if ($id) {
                extract($this -> product_config['adm_data']);
                return $this -> delete_iten($folder['db'], $this -> product_config['table'], $toke['db'], $id);
            }
        }
    }

    /*
     * cria layout para inserir produto numa newsletter
     */
    public function send_for_newsletter() {
        if (parent::check()) {
            
            if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE'])) {

                return FALSE;
            }
            
            $id = (isset($_POST['toke'])) ? $this -> id($_POST['toke']) : FALSE;
            if (!$id) {

                return '{"alert":"Não foi possivel realizar esta operação. CODE:002"}';
            }
            
            $p_config = $this -> product_config;
            
            $n_config = $p_config['export'];

            $N = NULL;
            $D = NULL;
            $query = $n_config['comum']['image'];

            foreach ($GLOBALS['LANGPAGES'] as $value) {

                $query .= "," . $n_config[$value]['title'] . "," . $n_config[$value]['text'];
            }

            $rslt = logex::$tcnx -> query("SELECT ". trim($query, ",") ." FROM " . $n_config['comum']['table'] . " WHERE " . $n_config['comum']['toke'] . "=$id LIMIT 1");
            $prd = $rslt -> fetch_array();

            foreach ($GLOBALS['LANGPAGES'] as $value) {

                $tx = NULL;

                $N .= ($N && $prd[$n_config[$value]['title']]) ? " / " . $prd[$n_config[$value]['title']] : $prd[$n_config[$value]['title']];

                $db = $n_config[$value]['text'];

                if (is_array($db)) {

                    foreach ($db as $topic => $text_topic) {

                        if ($prd[$topic])
                            $tx .= "<h2>$prd[$topic]</h2>";
                        if ($prd[$text_topic])
                            $tx .= $prd[$text_topic];

                    }
                } else {

                    $tx .= $prd[$db];
                }

                $D .= ($D) ? " <br><br> " . $tx : $tx;

            }

            $F = $this -> send_images_json($prd[$n_config['comum']['image']], "img", "pt", 0, NULL);

            return parent::for_newsletter($N, $F, $D, NULL, $id, $n_config['comum']['name']);
        }
    }

    /*
     * cria nota sobre o produto
     */
    public function show_notes() {
        if (parent::check()) {

            $id = parent::id($_POST['toke']);

            if ($id) {

                extract($this -> product_config['adm_data']);

                return parent::do_notes($notes['db'], $this -> product_config['table'], $toke['db'], $id);
            }
        }
    }

}
?>
