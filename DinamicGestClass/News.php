<?php
/* SCRIPT News.php V1.50
 13-06-2014
 COPYRIGHT MANUEL GERARDO PEREIRA 2014
 TODOS OS DIREITOS RESERVADOS
 CONTACTO: GPEREIRA@MGPDINAMIC.COM
 WWW.MGPDINAMIC.COM
 *
 * inicio : 13-06-2014
 *
 * ultima modificação : 13-06-2014
 *
 * */

require_once 'Core.php';

class News extends Core {

    public $ajs;

    public function __construct($JS) {
        parent::__construct();

        $this -> ajs = parent::json_file($JS);
    }

    /**
     * Salva um noticia da base de dados
     *
     * @return string objeto json
     *
     */
    public function save_news() {

        if (!parent::check())
            return '{"alert":"Não tem permissão para realizar esta operação. CODE:001"}';

        if (isset($_POST['public_destak'][0])) {

            $_POST['public_destak'] = 1;

        } else {

            $_POST['public_destak'] = 0;
        }

        $addedit = new OperationsBar($this -> ajs);
        $saved_news = $addedit -> save_item();

        $j_saved_news = json_decode($saved_news, TRUE);

        if ($j_saved_news && isset($j_saved_news['result'])) {

            if ($_POST['public_destak']) {

                $destak = $this -> ajs['admin']['public']['destak']['db'];
                $toke = $this -> ajs['admin']['private']['toke']['db'];

                if (!logex::$tcnx -> query("UPDATE " . $this -> ajs['table'] . " SET $destak=0 WHERE $destak=1 AND $toke<>" . $j_saved_news['result'][1]))
                    return '{"alert":"Não foi possivel definir a noticia como DESTAQUE. Por favor tente de novo. Se o problema persistir contacte a assistência técnica."}';
            }

        }

        return $saved_news;
    }

    /*
     * elimina noticia
     */

    public function delete_news() {
        if (parent::check()) {

            $flag = (in_array($_POST['flag'], $GLOBALS['FLAGSMODE']) && $_POST['flag'] == "DELETE") ? $_POST['flag'] : FALSE;

            if ($flag) {
                $id = $this -> id($_POST['toke']);
                if ($id) {

                    extract($this -> ajs['fields']['manag']);
                    $del = $this -> delete_iten($folder['db'], $this -> ajs['table'], $toke['db'], $id);

                    if ($del) {

                        $this -> update_sitemap();

                        return $del;
                    }
                }
            }
        } else {

            return '{"alert":"Não tem permissão para realizar esta operação. CODE:061"}';
            exit ;
        }
    }

    /**
     * Cria a ficha da noticia
     * Este metodo manipula a array de configuração das noticias
     * para que a notiica seja apresentada de uma forma mais aproximada da
     * forma que é apresentado online
     *
     * @return string estrutura hmtl da ficha da noticia
     *
     */
    public function make_file() {

        if (!parent::check())
            return '{"alert":"Não tem permissão para realizar esta operação. CODE:021"}';

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            return FALSE;

        $id = (isset($_POST['toke'])) ? parent::id($_POST['toke']) : FALSE;

        if (!$id)
            return '{"alert":"Não foi possivel realizar esta operação. CODE:002"}';

        $toke = $this -> ajs['admin']['private']['toke'];
        $format = $this -> ajs['fields']['news_format']['format'];
        $midia = $this -> ajs['midia']['midia'];

        $RF = logex::$tcnx -> query("SELECT * FROM " . $this -> ajs['table'] . " WHERE " . $toke['db'] . "=$id");
        $CT = $RF -> fetch_array();

        $RF -> free();

        $news_config = array_merge($this -> ajs['fields']['news_format'], $this -> ajs['fields']['pt'], $this -> ajs['midia']['midia']);

        $text_db = $this -> ajs['fields']['pt']['text']['db'];

        $CT[$text_db] = $this -> format_news($news_config, $CT);

        $destak_db = $this -> ajs['admin']['public']['destak']['db'];

        $CT[$destak_db] = (!empty($CT[$destak_db])) ? "Sim" : "Não";

        $this -> ajs['admin']['public']['topic']['type'] = "L_INPUT";
        $this -> ajs['admin']['public']['topic']['name'] = "topico";
        $this -> ajs['admin']['public']['topic']['db'] = "categoria";

        unset($this -> ajs['fields']['news_format']);
        unset($this -> ajs['midia']);

        unset($this -> ajs['fields']['pt']['title']);
        unset($this -> ajs['fields']['pt']['subtitle']);
        unset($this -> ajs['fields']['pt']['topic']);
        unset($this -> ajs['fields']['pt']['author']);

        $sheet = new ItemSheet;

        return $sheet -> make_sheet($this -> ajs, $CT);

    }

    /**
     * Formata o corpo de uma noticia
     *
     * @param array $config -  configuração da noticia
     * @param array $db_result - resultado da pesquisa na base de dados
     *
     * @return string - estrutura HTML com o corpo da noticia
     *
     */
    private function format_news(array $config, array $db_result) {

        #video
        $video = NULL;

        #fotografias da noticia
        $photos = GestImage::send_images_json($db_result[$config['images']['db']], "img", $GLOBALS['LANGPAGES'][0], 1);

        #objecto json video (extraido da bd)
        $jv = NULL;

        #manipula o video conforme o modo de video e transforma numa string html
        if ($db_result[$config['video']['db']]) {

            $jv = json_decode($db_result[$config['video']['db']], TRUE);

            $video = GestVideo::make_video($jv['video'], $jv['from']);
        }

        $text = $db_result[$config['text']['db']];

        #cria o conteudo da noticia, junta texto, fotos e video numa string html.
        switch ($db_result[$config['format']['db']]) {

            case "news1" :
                $news_body = "<div class='news1'>$photos $text</div>";
                break;
            case "news2" :
                $news_body = "<div class='news2'>$photos $text</div>";
                break;
            case "news5" :
                $news_body = "<div class='news5'>$photos</div>$text";
                break;
            case "news3" :
                $news_body = "<div class='news3'>$photos</div><div class='news3_t'>$text</div>";
                break;
            case "news4" :
                $news_body = "<div class='news4'>$photos</div><div class='news4_1'>$text</div>";
                break;
            case "news6" :
                $news_body = "$text<div class='news5'>$photos</div>";
                break;
            default :
                $news_body = $text;
                break;
        }

        $news = ($jv['pos'] == "baixo") ? $news_body . $video : $video . $news_body;

        #subtitulo
        $sub = (!empty($db_result[$config['subtitle']['db']])) ? "<p class='spcab4'>" . $db_result[$config['subtitle']['db']] . "</p>" : "";

        #autor
        if (filter_var($db_result[$config['author']['db']], FILTER_VALIDATE_EMAIL)) {

            $vmail = "<a href='mailto:" . $db_result[$config['author']['db']] . "'>" . $db_result[$config['author']['db']] . "</a>";

        } else {

            $vmail = $db_result[$config['author']['db']];
        }

        $autor = ($vmail) ? "<p class='spcab6'>autor:" . $vmail . "</p>" : "";

        #formata a noticia
        return "          
            <div class='cabeca'>
                <p class='spcab3'>" . $db_result[$config['title']['db']] . "</p>
                $sub $autor
           </div>
            <div class='noticia' >
                $news                
            </div>";
    }

    /*
     * insere uma noticia numa newsletter
     */

    public function send_for_newsletter() {
        if (parent::check()) {

            if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE'])) {

                return FALSE;
            }

            extract($this -> ajs['fields']['manag']);
            extract($this -> ajs['fields']['comuns']);
            extract($this -> ajs['fields']['news_format']);

            $id = (isset($_POST['toke'])) ? $this -> id($_POST['toke']) : FALSE;
            if (!$id) {

                return '{"alert":"Não foi possivel realizar esta operação. CODE:002"}';
            }

            $N = NULL;
            $D = NULL;
            $query = NULL;

            foreach ($GLOBALS['LANGPAGES'] as $value) {

                $query .= "," . $this -> ajs['fields'][$value]['title']['db'] . "," . $this -> ajs['fields'][$value]['text']['db'];
            }

            $rslt = logex::$tcnx -> query("SELECT " . $format['db'] . "," . $images['db'] . $query . " FROM " . $this -> ajs['table'] . " WHERE " . $toke['db'] . "=$id LIMIT 1");
            $prd = $rslt -> fetch_array();

            foreach ($GLOBALS['LANGPAGES'] as $value) {

                $N .= ($N && $prd[$this -> ajs['fields'][$value]['title']['db']]) ? " / " . $prd[$this -> ajs['fields'][$value]['title']['db']] : $prd[$this -> ajs['fields'][$value]['title']['db']];

                $D .= ($D && $prd[$this -> ajs['fields'][$value]['text']['db']]) ? " <br><br> " . $prd[$this -> ajs['fields'][$value]['text']['db']] : $prd[$this -> ajs['fields'][$value]['text']['db']];
            }

            $F = $this -> send_images_json($prd[$images['db']], "img", "pt", 0, NULL);

            return $this -> for_newsletter($N, $F, $D, $prd[$format['db']], $id, $this -> ajs['name']);
        } else {

            return '{"alert":"Não tem permissão para realizar esta operação. CODE:041"}';
            exit ;
        }
    }

    public function show_notes() {
        if (parent::check()) {
            $id = $this -> id($_POST['toke']);

            if ($id) {

                extract($this -> ajs['fields']['manag']);

                return $this -> do_notes($notes['db'], $this -> ajs['table'], $toke['db'], $id);
            }
        }
    }

}
?>
