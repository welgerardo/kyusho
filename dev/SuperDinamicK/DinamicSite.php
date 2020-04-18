<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 DINAMICSHOP V1.1
 25-06-2011
 */
require_once "SComuns.php";

class DinamicSite extends SComuns {

    function __construct() {
        parent::__construct();
    }

    private function get_iten_data() {

        if (in_array($_GET['mode'], $GLOBALS['WHITELIST'])) {

            if (!$mode = json_decode(constant($_GET['mode']), TRUE))
                return FALSE;

            $iten_data['JS'] = $mode;

            $tcnx = new mysqli(_HT, _US, _PS, _DB);
            $tcnx -> set_charset("utf8");

            $rslt = $tcnx -> query("SELECT " . implode($mode['fields'], ",") . " FROM " . $mode['table'] . " WHERE estado='online' ORDER BY order_index DESC");

            while ($line = $rslt -> fetch_array()) {

                $iten_data['BD'][] = $line;
            }

            $rslt -> free();
            $tcnx -> close();

            return $iten_data;

        } else {

            return FALSE;
        }
    }

    public function curso() {

        $p_curso = array();

        $intro = $this -> foot_pages("cursos");
        $md = $this -> get_iten_data();

        if(empty($md['JS']) || empty($md['JS']) )
            return FALSE;

        $mode_js = $md['JS'];
        $mode_db = $md['BD'];

        $intro['image'] = $this -> send_images_json_i($intro['image'], "img", "pt", 0, "class='introcimg'");

        extract($mode_js['fields'], EXTR_PREFIX_ALL, "cr");

        $mr = NULL;

        $intro['ot'] = NULL;

        foreach ($mode_db as $prd) {

            $ft = ($prd[$cr_image]) ? $this -> send_images_json_i($prd[$cr_image], "img", "pt", 0, "class='introcimg'") : "";

            $intro['ot'] .= "
                            <section class='box trsp $mr introbox'>
                                $ft
                                   <h2 class='btit bintro'>
                                        <strong>
                                            " . $prd[$cr_name] . "
                                            <span class='localintro'>
                                            " . $prd[$cr_local] . "
                                            </span>
                                        </strong>
                                    </h2>
                                    <div class='textintro'>
                                        " . $prd[$cr_intro] . "
                                    </div>
                                    <div class='introa'>
                                        " . _M_ANCHOR . "
                                        <a  href='" . _RURL . $mode_js['link'] . "/" . $prd[$cr_id] . "/" . $this -> clean_space($prd[$cr_name]) . "' class='mais'>
                                            <b>
                                            " . mb_strtolower($prd[$cr_name], 'UTF-8') . "
                                            </b>
                                        </a>
                                    </div>

                            </section>
            ";

            $mr = ($mr) ? NULL : "mr";

        }

        return $intro;

    }

    public function portfolio() {

        $md = $this -> get_iten_data();

        if(empty($md['JS']) || empty($md['JS']) )
            return FALSE;

        $mode_js = $md['JS'];
        $mode_db = $md['BD'];

        extract($mode_js['fields'], EXTR_PREFIX_ALL, "cr");

        $mr = NULL;

        $intro['ot'] = NULL;

        foreach ($mode_db as $prd) {

            $ft = ($prd[$cr_image]) ? $this -> send_images_json_i($prd[$cr_image], "img", "pt", 0, "class='introcimg'") : "";

            $intro['ot'] .= "
                            <section class='galbox trsp'>
                                $ft
                                   <h2 class='galboxtitle'>
                                        <strong>
                                            " . $prd[$cr_name] . "
                                        </strong>
                                    </h2>
                                    <div class='textintro'>
                                        " . $prd[$cr_intro] . "
                                    </div>
                                    <div class='introa'>
                                        " . _M_ANCHOR . "
                                        <a  href='" . _RURL . $mode_js['link'] . "/" . $prd[$cr_id] . "/" . $this -> clean_space($prd[$cr_name]) . "' class='mais'>
                                            <b>
                                            " . mb_strtolower($prd[$cr_name], 'UTF-8') . "
                                            </b>
                                        </a>
                                    </div>

                            </section>
            ";



        }

        return $intro;

    }

    public function pagina_produto($i) {

        $idx = (is_numeric($i)) ? $i : FALSE;

        if ($idx && in_array($_GET['mode'], $GLOBALS['WHITELIST'])) {

            if (!$mode = json_decode(constant($_GET['mode']), TRUE))
                return "erro";

            extract($mode['fields'], EXTR_PREFIX_ALL, "f");

            $tcnx = new mysqli(_HT, _US, _PS, _DB);
            $tcnx -> set_charset("utf8");

            $rslt = $tcnx -> query("SELECT * FROM " . $mode['table'] . " WHERE estado='online' AND $f_id='$idx' LIMIT 1");

            $prod = $rslt -> fetch_array();

            $rslt -> free();
            $tcnx -> close();

            $this -> titulo = $prod[$f_seo_title];

            $this -> description = $prod[$f_seo_description];

            $this -> image = $prod[$f_image];

            $serv = array();
            $serv['image'] = NULL;
            $serv['text'] = NULL;
            $serv['id'] = $prod[$f_id];
            $serv['nome'] = $prod[$f_name];

            if(!empty($f_intro))
            $serv['intro'] = $prod[$f_intro];


            $serv['seo_title'] = $prod[$f_seo_title];
            $serv['seo_description'] = $prod[$f_seo_description];
            $serv['seo_keywords'] = $prod[$f_seo_keywords];
            if(isset($f_action))
            $serv['action'] = $prod[$f_action];

            $serv['map'] = "
                <div class='site_nav'>
                    <a href='" . _RURL . "'><img src='" . _RURL . "imagens/link_home_page.png' alt='home page futurekids braga centro de estudo e explicações'></a>
                    >
                    <a href='" . _RURL . $mode['link'] . "'>" . $mode['link_name'] . "</a>
                    >
                    <a href='" . _RURL . $mode['link'] . "/" . $prod[$f_id] . "/" . str_replace(" ", "-", strtolower($prod[$f_name])) . "'>" . strtolower($prod[$f_name]) . "</a>
                </div>";



            #o_data

            $o_text = NULL;

            if (isset($f_o_data) && is_array($f_o_data)) {

                foreach ($f_o_data as $okey => $ovalue) {

                    $o_text .= ($prod[$ovalue]) ? "<p class='o_p'><span class='o_title'>$okey : </span><span class='o_text'>" . $prod[$ovalue] . "</span></p>" : "";
                }

                $serv_data = ($o_text) ? $o_text : "";

            } else {

                $serv_data = "";
            }

            #texto

            $serv_text = NULL;

            if (isset($f_description) &&  is_array($f_description)) {

                foreach ($f_description as $topic => $topic_text) {

                    $descrix = null;

                    if ($prod[$topic])
                        $descrix .= "<h2>$prod[$topic]</h2>";

                    if ($prod[$topic_text])
                        $descrix .= "<h3>$prod[$topic_text]</h3>";

                    $serv_text .= ($descrix) ? "<div class='topicserv'>$descrix</div>" : "";
                }

            } else {

                $serv_text = ($prod[$f_description]) ? $prod[$f_description] : "";
            }

            $serv['text'] = $serv_text;

            $serv['data'] = $serv_data;

            $serv['program'] = $prod[$f_program];

            $serv['image'] = ($prod[$f_image]) ? $this -> send_images_json_i($prod[$f_image], "img", parent::get_language(), TRUE, "class='introcimg'") : "";

            $serv['form'] = $this -> form_message_plus(FALSE, $prod[$f_name], $mode['name'], $prod[$f_id]);

            $serv['social'] = parent::social_buttons(_RURL . $mode['link'] . "/" . $prod[$f_id] . "/" . str_replace(" ", "-", strtolower($prod[$f_name])), parent::cut_text($serv_text, 250, FALSE), NULL, $this -> send_images_json_i($prod[$f_image], "src", NULL, 0, NULL), FALSE);

            return $serv;

        }
        echo "errop";
    }

}
?>