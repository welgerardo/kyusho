<?php
/**
 * script: News.php
 * client: EPKyusho
 *
 * @version V2.01.030615
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 *
 */

ini_set('display_errors', 0);
require_once 'Core.php';

class News extends Core
{

    //array de configuração do modulo
    private $config;

    private $exp_prefix = "NEWS";

    /**
     * Construtor
     *
     */
    public function __construct()
    {

        parent::__construct();

        $this -> config = parent::json_file("JNEWS");
    }

    /**
     * Devolve a array de configuração
     *
     * @return array associativa com os dados de configuração do objeto
     */
    public function get_config()
    {
        return $this -> config;
    }

    /**
     * Procura um contato na base de dados
     *
     * @uses Core::id(), Core::make_call()
     *
     * @param string $id_news - id do contato
     *
     * @return false|array - resultado da pesquisa na base de dados
     *
     */
    protected function query_news($id_news)
    {
        if (!$id = parent::id($id_news))
            throw new Exception($this -> exp_prefix . __LINE__, 1);

        try{
            $news = parent::make_call("spNewsData",array($id));
        }
        catch(Exception $exp)
        {
            throw new Exception($this -> exp_prefix . __LINE__, 1);
        }

        if(!isset($news[0]))
            throw new Exception($this -> exp_prefix . __LINE__, 1);

        return $news[0];
    }

    /**
     *
     */
    public function edit_news($id)
    {
        if (!parent::check())
            return parent::mess_alert($this -> exp_prefix . __LINE__);

        if (!$id = parent::id($_POST['toke']))
            return parent::mess_alert($this -> exp_prefix . __LINE__);

        $addedit = new OperationsBar($this -> config);
        $addedit -> set_mode("UPDATE");
        return $addedit -> edit_item_sheet_db("spNewsData",array($id));
    }

    /**
     * Cria a ficha da noticia
     * Este metodo manipula a array de configuração das noticias
     * para que a notiica seja apresentada de uma forma mais aproximada da
     * forma que é apresentado online
     *
     * @uses Core::mess_alert()
     * @uses Core::id()
     * @uses Logex::check()
     * @uses News::format_news()
     * @uses ItemSheet make_sheet()
     * 
     * @throws Exception
     *
     * @return string estrutura hmtl da ficha da noticia
     *
     */
    public function make_file()
    {

        if (!parent::check())
            return parent::mess_alert($this -> exp_prefix  . __LINE__);

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            return parent::mess_alert($this -> exp_prefix  . __LINE__);
        
        $id = $_POST['toke'];

        try
        {
            $text_db = $this -> config['content']['pt']['text']['db'];
            $destak_db = $this -> config['admin']['public']['destak']['db'];

            $news_config = array_merge($this -> config['content']['news_format'], $this -> config['content']['pt'], $this -> config['midia']['midia']);

            $news_content = $this -> query_news($id);

            $news_content[$text_db] = $this -> format_news($news_config, $news_content);

            $news_content[$destak_db] = (!empty($news_content[$destak_db])) ? "Sim" : "Não";

            $this -> config['admin']['public']['topic']['type'] = "L_INPUT";
            $this -> config['admin']['public']['topic']['name'] = "topico";
            $this -> config['admin']['public']['topic']['db'] = "news.categoria";

            unset($this -> config['content']['news_format']);
            unset($this -> config['midia']);

            unset($this -> config['content']['pt']['title']);
            unset($this -> config['content']['pt']['subtitle']);
            unset($this -> config['content']['pt']['topic']);
            unset($this -> config['content']['pt']['author']);

            $sheet = new ItemSheet();

            return $sheet -> make_sheet($this -> config, $news_content);

        }
        catch(Exception $exp)
        {
            return parent::mess_alert($exp -> getMessage());
        }

    }

    /**
     * Formata o corpo de uma noticia
     *
     * @uses GestImage::send_image_json, GestVideo::make_video
     *
     * @param array $config -  configuração da noticia
     * @param array $db_result - resultado da pesquisa na base de dados
     *
     * @return string - estrutura HTML com o corpo da noticia
     *
     */
    private function format_news(array $config, array $db_result)
    {

        #video
        $video = NULL;
        $text = NULL;
        $format = NULL;
        $author = NULL;
        $title = NULL;

        $images = new GestImage();
        $videox = new GestVideo();

        try
        {
            #fotografias da noticia
            if(isset($db_result[$config['images']['db']]))
                $photos = $images -> send_images_json($db_result[$config['images']['db']], "img", $GLOBALS['LANGPAGES'][0], 1);
        }
        catch(Exception $exp)
        {

            $photos = NULL;
        }

        #objecto json video (extraido da bd)
        $jv = NULL;

        #manipula o video conforme o modo de video e transforma numa string html
        if (isset($db_result[$config['video']['db']]))
        {

            $jv = json_decode($db_result[$config['video']['db']], TRUE);

            $video = $videox -> make_video($jv['video'], $jv['from']);
        }

        if(isset($db_result[$config['text']['db']]))
            $text = $db_result[$config['text']['db']];

        if(isset($db_result[$config['format']['db']]))
            $format = $db_result[$config['format']['db']];

        #cria o conteudo da noticia, junta texto, fotos e video numa string html.
        switch ($format) {

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
                $news_body = "<div class='news3_t'>$text</div><div class='news3'>$photos</div>";
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

        if(isset($db_result[$config['author']['db']]))
            $author =  $db_result[$config['author']['db']];

        #autor
        if (filter_var($author, FILTER_VALIDATE_EMAIL))
        {

            $vmail = "<a href='mailto:" . $db_result[$config['author']['db']] . "'>" . $db_result[$config['author']['db']] . "</a>";

        }
        else
        {

            $vmail = $author;
        }

        $autor = ($vmail) ? "<p class='spcab6'>autor:" . $vmail . "</p>" : "";

        if(isset($db_result[$config['title']['db']]))
            $title = $db_result[$config['title']['db']];

        #formata a noticia
        return "
            <div class='cabeca'>
                <p class='spcab3'>$title</p>
                $sub $autor
           </div>
            <div class='noticia' >
                $news
            </div>
            <div class='rodape'></div>";
    }

    /**
     * cria as notas referentes a uma newsletter
     * @return boolean || string HTML
     */
    public function show_notes()
    {
        if (!parent::check())
            return NULL;

        $id = $this -> id($_POST['toke']);

        $notes = new GestNotes();

        return $notes -> show_notes($this -> config['notes'], $id);

    }

}
?>
