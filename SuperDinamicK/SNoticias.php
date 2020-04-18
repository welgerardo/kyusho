<?php

/* SCRIPT SNoticias.php V3.0
  16-06-2015
  COPYRIGHT MANUEL GERARDO PEREIRA 2015
  TODOS OS DIREITOS RESERVADOS
  CONTACTO: GPEREIRA@MGPDINAMIC.COM
  WWW.MGPDINAMIC.COM */
ini_set('display_errors', 1);
require_once "SComuns.php";

class SNoticias extends SComuns
{

    /**
     * numero de noticias a envia por cada chamada
     * @var int
     */
    private $itensPerPage;

    /**
     * titulo da noticia
     * @var type
     */
    private $newstitle;

    /**
     * meta-descrição da noticia
     * @var string
     */
    private $newsdescription;

    /**
     * primeira imagem da noticia
     * @var type
     */
    private $newsimage;

    /**
     * meta palavras-chave da noticia
     * @var type
     */
    private $newskeywords;

    /**
     * objeto json de configuração
     * @var array
     */
    private $modex;
    
    /**
     * idioma da noticias
     * @var string 
     */
    private $lg;

    /**
     *
     * @param int $ipp - numero de noticias por pagina
     */
    public function __construct($ipp)
    {

        parent::__construct();

        $mode = (!empty($_GET['mode']) && in_array($_GET['mode'], $GLOBALS['WHITELIST'])) ? $_GET['mode'] : "_BLOG";

        $this->modex = json_decode(constant($mode), TRUE);
        
        $n_news = filter_var($ipp, FILTER_VALIDATE_INT, array("options" => array(
                "min_range" => 0,
                "max_range" => 10
        )));
        
        
        $this->itensPerPage = ($n_news) ? $n_news : 10;
        
        $this->lg = parent::get_language();
    }

    /**
     *
     * @return string - titulo da noticia
     */
    public function get_newstitle()
    {

        return $this->newstitle;
    }

    /**
     *
     * @return string - meta-descrição da noticia
     */
    public function get_newsdescription()
    {

        return $this->newsdescription;
    }

    /**
     *
     * @return string - endereço da primeira imagem da noticia
     */
    public function get_newsimage()
    {

        return $this->newsimage;
    }

    /**
     *
     * @return string - palavras-chave da noticia
     */
    public function get_newskeywords()
    {

        return $this->newskeywords;
    }

    /**
     * cria resposta com multiplas noticias e com um scroll sem fim
     *
     * @return string html com as noticias
     */
    public function shownews_endless_scroll()
    {

        $news = NULL;

        $jnews_conf = $this->modex;

        $page     = filter_input(INPUT_POST, "page", FILTER_VALIDATE_INT);
        $category = filter_input(INPUT_GET, "sub", FILTER_SANITIZE_STRING);

        $pagex = ($page) ? $page * 1 : 0;

        try
        {
            $result = parent::make_call($jnews_conf['news page'], array($category, $pagex, $this->itensPerPage));
        }
        catch (Exception $ex)
        {
            return NULL;
        }

        foreach ($result as $new)
        {

            if ($new['titulo'])
            {

                $news .= $this->newsbi($new);
            }
        }

        return $news;
    }

    /*
     * constroi a noticia para a página de noticia
     */

    public function make_new()
    {

        $mode = $this->modex;

        $idx = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, array("options" => array("min_range" => 0)));
        
        try
        {
            $result = parent::make_call($mode["news single"],array($idx));            
        }
        catch (Exception $ex)
        {
            return NULL;
        }
        
        if(!empty($result[0]))
            return $this->newsbi($result[0]);
            
        
        return FALSE;
      
    }

    /*
     * cria uma noticia
     */

    private function newsbi($new)
    {

        #video
        $videos = new GestVideo();

        #fotografias da noticia
        $images = new GestImage();

        //redes sociais
        $social = new GestSocial();

        #objeto json de configuração
        $mode = $this->modex;

        #todas as fotos da notica
        try
        {
            //todas as fotos das noticias
            $photos = $images->send_images_json($new['fotos'], "img", $this->lg);
            
            //a primeira imagem da noticia
            $this->newsimage = $images->send_images_json($new['fotos'], "src", $this->lg, 0);
        }
        catch (Exception $ex)
        {
            $photos = "";
            $this->newsimage = "";
        }


        //descrição da noticia para redes sociais e seo
        if (isset($new['descricao']))
        {
            $this->newsdescription = $new['descricao'];
        }
        else
        {
            $this->newsdescription = parent::cut_text($new['texto'], 150, FALSE);
        }

        if (isset($new['palavras']))
            $this->newskeywords = $new['palavras'];

        #titulo da noticia
        $this->newstitle = $new['titulo'];

        #objecto json video (extraido da bd)
        $jv = NULL;

        #manipula o video conforme o modo de video e transforma numa string html
        if (isset($new['video']))
        {
            $jv = json_decode($new['video'], TRUE);

            $vid = $videos->make_video($jv['video'], $jv['from']);
        }

        #cria o conteudo da noticia, junta texto, fotos e video numa string html.
        switch ($new['formato'])
        {

            case "news1" :
                $t1 = "<div class='news1'>$photos " . $new['texto'] . "</div>";
                $t  = ($jv['pos'] == "baixo") ? "$t1 $vid" : "$vid $t1";
                break;
            case "news2" :
                $t1 = "<div class='news2'>$photos " . $new['texto'] . "</div>";
                $t  = ($jv['pos'] == "baixo") ? "$t1 $vid" : "$vid $t1";
                break;
            case "news5" :
                $t1 = "<div class='news5'>$photos</div>" . $new['texto'];
                $t  = ($jv['pos'] == "baixo") ? "$t1 $vid" : "$vid $t1";
                break;
            case "news3" :
                $t1 = "<div class='news3'>$photos</div><div class='news3_1'>" . $new['texto'] . "</div>";
                $t  = ($jv['pos'] == "baixo") ? "$t1 $vid" : "$vid $t1";
                break;
            case "news4" :
                $t1 = "<div class='news4'>$photos</div><div class='news4_1'>" . $new['texto'] . "</div>";
                $t  = ($jv['pos'] == "baixo") ? "$t1 $vid" : "$vid $t1";
                break;
            case "news6" :
                $t  = ($jv['pos'] == "baixo") ? "" . $new['texto'] . "<div class='news5'>$photos</div>$vid" : "$vid " . $new['texto'] . "<div class='news5'>$photos</div>";
                break;
            default :
                $t  = ($jv['pos'] == "baixo") ? "" . $new['texto'] . " $vid" : "$vid " . $new['texto'] . "";
                break;
        }

        #topico da noticia
        $cat = (!empty($new['topico'])) ? "<span class='spcab1'>" . strtolower($new['topico']) . "</span>" : "";

        #subtitulo
        $sub = (!empty($new['subtitulo'])) ? "<p class='spcab4'>" . $new['subtitulo'] . "</p>" : "";

        $vmail = (filter_var($new['autor'], FILTER_VALIDATE_EMAIL)) ? "<a href='mailto:" . $new['autor'] . "'>" . $new['autor'] . "</a>" : $new['autor'];
        $autor = ($vmail) ? "<p class='spcab6'>autor:" . $vmail . "</p>" : "";


        $data = GDATE::make_date($new['data'], 0, "DATE", "/");

        $url       = urldecode(_RURL . $mode['link'] . "/" . $new['id'] . "/" . parent::clean_space($this->newstitle));
        $short_url = urldecode(_RURL . $mode['shortlink'] . "/" . $new['id']);

        $social_buttons = $social->social_buttons($url, $short_url, $this->newsdescription, $this->newsimage, FALSE);

        #formata a noticia
        return " <div class='wnoticia'><div class='cabeca'><p class='notinfo'>$cat <span class='spcab2'>data:$data</span></p><p class='spcab3'>$this->newstitle</p>$sub $autor</div><div class='noticia' >$t</div>$social_buttons</div>";
    }

    /*
     * envia um objeto JSON com as vária categorias de uma noticia
     */

    public function send_json()
    {

        $p      = "";
        $qnm    = "SELECT DISTINCT categoria FROM " . $this->table . " WHERE estado='online'";
        $qnm2   = mysql_query($qnm);
        while ($catnot = mysql_fetch_array($qnm2))
        {
            if ($catnot[0])
            {
                $p .= ",\"$catnot[0]\"";
            }
        }
        $p = ltrim($p, ",");
        return "[$p]";
    }

    /**
     * Apresenta uma chamdada para as notícas. Normalmente utilizada na home page 
     * 
     * @param integer $news_numb - numero de noticias
     * @param boolean $whith_img - TRUE para apresentar as noticias com fotografias 
     * @param string $display - "v" para apresentar a noticias na vertical "h" para apresentar as noticias na horizontal 
     * 
     * @return string html com as noticias
     */
    public function front_news($news_numb, $whith_img, $display)
    {

        $news = "";

        $images = new GestImage();
        $datas  = new GDate();

        $vnumb = filter_var($news_numb, FILTER_VALIDATE_INT, array("options" => array(
                "min_range" => 0,
                "max_range" => 10
        )));

        $photos = filter_var($whith_img, FILTER_VALIDATE_BOOLEAN);

        $numb = ($vnumb > 0) ? $vnumb : 0;

        $p = (strtolower($display) == "h") ? "H" : "V";

        $jnews_conf = $this->modex;

        try
        {
            $result = parent::make_call($jnews_conf['home page'], array($numb));
        }
        catch (Exception $ex)
        {

            return NULL;
        }

        if (!is_array($result))
            return NULL;

        foreach ($result as $new)
        {
            if ($photos)
            {
                try
                {
                    $photo = $images->send_images_json($new['fotos'], "img", $this->lg, 0, "class='fnew$p'");
                    $text  = parent::cut_text($new['texto'], 200, FALSE);
                }
                catch (Exception $exp)
                {
                    $photo = "";
                    $text  = "";
                }
            }

            $data = $datas->make_date($new['data'], 0, "DATE", "/");
            $url  = urldecode(_RURL . $jnews_conf['link'] . "/" . $new['id'] . "/" . parent::clean_space($new['titulo']));

            $news .= "<article class='new$p'><footer class='datenews'>$data</footer><header><h7 class='titlenews'>" . $new['titulo'] . "</h7></header><p>$photo<span class='textnews'>$text</span></p><a class='more$p' href='$url' >saiba mais</a></article>";
        }

        return "<div id='news$p'>$news</div>
        ";
    }

    /**
     * 
     * @return string html com links para  as noticias agrupadas por ano e mês
     */
    public function show_archives()
    {

        $arq = json_decode(_NEWSARCH, TRUE);

        extract($arq['fields'], EXTR_PREFIX_ALL, "a");

        $li_post  = NULL;
        $li_month = NULL;
        $li_year  = NULL;

        $arch = array();

        try
        {
            $result = parent::make_call("spNewsArq", NULL);
        }
        catch (Exception $ex)
        {
            return NULL;
        }

        #noticias online
        foreach ($result as $db_post)
        {

            $post_date = GDate::make_date($db_post['data'], 1, "TIMEST");

            #ano da noticia
            $post_year = date("Y", $post_date);

            #mês da noticia no idioma
            $post_month = strftime("%B", $post_date);

            if (!isset($arch[$post_year]))
            {

                $arch[$post_year] = array();
            }

            if (!isset($arch[$post_year][$post_month]))
            {

                $arch[$post_year][$post_month] = array();
            }

            $arch[$post_year][$post_month][] = $db_post;
        }

        foreach ($arch as $posts_year => $posts_month)
        {

            foreach ($posts_month as $month => $post)
            {

                foreach ($post as $post_title)
                {

                    if (!empty($post_title['titulo']))
                    {
                        $li_post .= "
                            <li>
                                <a href='" . urldecode(_RURL . $arq['link'] . "/" . $post_title['id'] . "/" . parent::clean_space($post_title['titulo'])) . "'>
                                    " . $post_title['titulo'] . "
                                </a>
                            </li>
                        ";
                    }
                }

                $li_month .= "
                    <li>
                        $month
                        <ul class='newsnew'>
                            $li_post
                        </ul>
                    </li>";
                $li_post = "";
            }

            $li_year .= "
                <li>
                    $posts_year
                    <ul class='newsmonth'>
                        $li_month
                    </ul>
                </li>";
            $li_month = "";
        }

        echo "
			<div id='news_archive'>
				<p>
					" . _ARCHTITLE . "
				</p>
				<ul class='newsyear'>
					" . $li_year . "
				</ul>
			</div>

			";
    }

    /**
     * 
     * @return string html com links para filtar as noticias por tópicos
     */
    public function show_topics()
    {

        $line = "";

        $topic = json_decode(_BLOG, TRUE);

        try
        {
            $result = parent::make_call("spNewsTopics", NULL);
        }
        catch (Exception $ex)
        {
            return NULL;
        }

        foreach ($result as $top)
        {
            $line .= "<li><a href='" . urldecode(_RURL . $topic['link'] . "/" . $top['topico']) . "'>" . strtolower($top['topico']) . "</a></li>";
        }

        echo "<div id='news_subject'><p>" . _TOPICTITLE . "</p><ul class='newsyear'>$line</ul></div>";
    }

}

?>