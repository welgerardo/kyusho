<?php
/*SCRIPT SNoticias.php V2.0
 23-06-2014
 COPYRIGHT MANUEL GERARDO PEREIRA 2014
 TODOS OS DIREITOS RESERVADOS
 CONTACTO: GPEREIRA@MGPDINAMIC.COM
 WWW.MGPDINAMIC.COM*/
ini_set('display_errors', 0);
require_once 'SConfig.php';
require_once "SComuns.php";

class SNoticias extends SComuns
{

    #numero de noticias a envia por cada chamada
    private $itensPerPage;

    #titulo da noticia
    private $newstitle;

    #descrição da noticia
    private $newsdescription;

    #primeira imagem da noticia
    private $newsimage;

    #meta palavras-chave da noticia
    private $newskeywords;

    #objeto json de configuração
    private $modex;

    public function __construct($ipp)
    {

        parent::__construct();

        $mode = (!empty($_GET['mode']) && in_array($_GET['mode'], $GLOBALS['WHITELIST'])) ? $_GET['mode'] : "_POST";

        $this -> modex = json_decode(constant($mode), TRUE);

        $this -> itensPerPage = $ipp;

    }

    public function get_newstitle()
    {

        return $this -> newstitle;
    }

    public function get_newsdescription()
    {

        return $this -> newsdescription;
    }

    public function get_newsimage()
    {

        return $this -> newsimage;

    }

    public function get_newskeywords()
    {

        return $this -> newskeywords;

    }

    /*
     * cria resposta com multiplas noticias e com um scroll sem fim
     */
    public function shownews_endless_scroll()
    {

        $news = NULL;
        $category = NULL;
        $sub = NULL;

        $jnews_conf = $this -> modex;

        $page = filter_input(INPUT_POST, "page", FILTER_VALIDATE_INT);
        $category = filter_input(INPUT_GET, "sub", FILTER_SANITIZE_STRING);

        $pagex = ($page) ? $page * 1 : 0;

        $tcnx = new mysqli('localhost', _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        if (!empty($category))
        {
            $rcat = $tcnx -> query("SELECT " . $jnews_conf['fields']['topic'] . " FROM " . $jnews_conf['table'] . " WHERE " . $jnews_conf['fields']['topic'] . "='$category'");
            $sub = ($category && $rcat -> num_rows > 0) ? "AND categoria='$category'" : "";
            $rcat -> free();
        }

        #condiçoes de pesquisa na base de dados
        $sql_conditions = parent::define_sql_conditions($jnews_conf['conditions']);

        #oredenação dos resultados de pesquisa na base de dados
        $sql_order = parent::define_sql_order($jnews_conf['order']);

        $query = "SELECT " . implode(",", $jnews_conf['fields']) . " FROM " . $jnews_conf['table'] . " $sql_conditions $sub $sql_order LIMIT $pagex , $this->itensPerPage";
        $rslt = $tcnx -> query($query);

        while ($new = $rslt -> fetch_array())
        {

            if ($new[$jnews_conf['fields']['title']])
            {

                $news .= $this -> newsbi($new);

            }
        }

        return $news;

        $rslt -> close();

    }

    /*
     * constroi a noticia para a página de noticia
     */
    public function make_new()
    {

        $mode = $this -> modex;

        $idx = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, array("options" => array("min_range" => 0)));

        $tcnx = new mysqli('localhost', _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        $query = "SELECT " . implode(",", $mode['fields']) . " FROM " . $mode['table'] . " WHERE estado='online' AND " . $mode['fields']['id'] . "=$idx";
        $rslt = $tcnx -> query($query);

        $new = $rslt -> fetch_array();

        if ($new[$mode['fields']['title']] && $rslt -> num_rows > 0)
        {

            return $this -> newsbi($new);

        }
        else
        {

            return FALSE;
        }

        $rslt -> free();
        $tcnx -> close();
    }

    /*
     * cria uma noticia
     */
    private function newsbi($new)
    {

        #video
        $vid = "";

        #fotografias da noticia
        $photos = "";

        #objeto json de configuração
        $mode = $this -> modex;

        extract($mode['fields'], EXTR_PREFIX_ALL, "nws");

        #todas as fotos da notica
        $photos = $this -> send_images_json($new[$nws_images], "img", "pt");

        #descrição da noticia para redes sociais e seo
        $this -> newsdescription = ($new[$nws_description]) ? $new[$nws_description] : $this -> cut_text($new[$nws_title] . " " . $new[$nws_text], 150, FALSE);

        $this -> newskeywords = $new[$nws_keywords];

        #a primeira imagem da noticia
        $this -> newsimage = $this -> send_images_json($new[$nws_images], "src", "pt", 0);

        #titulo da noticia
        $this -> newstitle = $new[$nws_title];

        #objecto json video (extraido da bd)
        $jv = NULL;

        #manipula o video conforme o modo de video e transforma numa string html
        if ($new[$nws_video])
        {

            $jv = json_decode($new[$nws_video], TRUE);

            $vid = $this -> make_video($jv['video'], $jv['from']);
        }

        #cria o conteudo da noticia, junta texto, fotos e video numa string html.
        switch ($new[$nws_format]) {

            case "news1" :
                $t1 = "<div class='news1'>$photos " . $new[$nws_text] . "</div>";
                $t = ($jv['pos'] == "baixo") ? "$t1 $vid" : "$vid $t1";
                break;
            case "news2" :
                $t1 = "<div class='news2'>$photos " . $new[$nws_text] . "</div>";
                $t = ($jv['pos'] == "baixo") ? "$t1 $vid" : "$vid $t1";
                break;
            case "news5" :
                $t1 = "<div class='news5'>$photos</div>" . $new[$nws_text];
                $t = ($jv['pos'] == "baixo") ? "$t1 $vid" : "$vid $t1";
                break;
            case "news3" :
                $t1 = "<div class='news3'>$photos</div><div class='news3_1'>" . $new[$nws_text] . "</div>";
                $t = ($jv['pos'] == "baixo") ? "$t1 $vid" : "$vid $t1";
                break;
            case "news4" :
                $t1 = "<div class='news4'>$photos</div><div class='news4_1'>" . $new[$nws_text] . "</div>";
                $t = ($jv['pos'] == "baixo") ? "$t1 $vid" : "$vid $t1";
                break;
            case "news6" :
                $t = ($jv['pos'] == "baixo") ? "" . $new[$nws_text] . "<div class='news5'>$photos</div>$vid" : "$vid " . $new[$nws_text] . "<div class='news5'>$photos</div>";
                break;
            default :
                $t = ($jv['pos'] == "baixo") ? "" . $new[$nws_text] . " $vid" : "$vid " . $new[$nws_text] . "";
                break;
        }

        #topico da noticia
        $cat = (!empty($new[$nws_topic])) ? "<span class='spcab1'>" . strtolower($new[$nws_topic]) . "</span>" : "";

        #subtitulo
        $sub = (!empty($new[$nws_subtitle])) ? "<p class='spcab4'>" . $new[$nws_subtitle] . "</p>" : "";

        $vmail = (filter_var($new[$nws_author], FILTER_VALIDATE_EMAIL)) ? "<a href='mailto:" . $new[$nws_author] . "'>" . $new[$nws_author] . "</a>" : $new[$nws_author];
        $autor = ($vmail) ? "<p class='spcab6'>autor:" . $vmail . "</p>" : "";

        #formata a noticia
        return "
         <div class='wnoticia'>
            <div class='cabeca'>
            	<p class='notinfo'>$cat <span class='spcab2'>data:" . $this -> make_date($new[$nws_date], 0, "DATE", "/") . "</span></p>
             	<p class='spcab3'>" . $new[$nws_title] . "</p>
            	$sub $autor
           </div>
            <div class='noticia' >
                $t
            </div>
            " . $this -> social_buttons(urldecode(_RURL . $mode['link'] . "/" . $new[$nws_id] . "/" . $this -> clean_space($new[$nws_title])), urldecode(_RURL . $mode['shortlink'] . "/" . $new[$nws_id]), $this -> newsdescription, $this -> newsimage, FALSE) . "
        </div>";
    }

    /*
     * envia um objeto JSON com as vária categorias de uma noticia
     */
    public function send_json()
    {

        $p = "";
        $qnm = "SELECT DISTINCT categoria FROM " . $this -> table . " WHERE estado='online'";
        $qnm2 = mysql_query($qnm);
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

    /*
     *
     * chamada para as noticias
     * apresenta as ultimas noticias na horizontal
     * $N=numero de noticias
     * $F=TRUE para apresentar as noticias com fotografias
     * $P="v" para apresentar a noticias na vertical "h" para apresentar as noticias na horizontal
     */
    public function front_news($N, $F, $P)
    {

        $news = "";

        $numb = filter_var($N, FILTER_VALIDATE_INT, array("options" => array(
                "min_range" => 0,
                "max_range" => 10
            )));

        $photos = filter_var($F, FILTER_VALIDATE_BOOLEAN);

        $numb = ($numb > 0) ? $numb : 0;

        $p = (strtolower($P) == "h") ? "H" : "V";

        $jnews_conf = $this -> modex;

        #condiçoes de pesquisa na base de dados
        $sql_conditions = parent::define_sql_conditions($jnews_conf['conditions']);

        #ordenação dos resultados de pesquisa na base de dados
        $sql_order = parent::define_sql_order($jnews_conf['order']);

        extract($jnews_conf['fields'], EXTR_PREFIX_ALL, "n");

        $tcnx = new mysqli('localhost', _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        $query = "SELECT " . implode(",", $jnews_conf['fields']) . " FROM " . $jnews_conf['table'] . "  $sql_conditions $sql_order LIMIT $numb";
        if (!$rslt = $tcnx -> query($query))
            return NULL;

        while ($new = $rslt -> fetch_array())
        {
            if ($photos)
            {
                try
                {
                    $photo = parent::send_images_json($new[$n_images], "img", parent::get_language(), 0, "class='fnew$p'");
                }
                catch(Exception $exp)
                {
                    $photo = "";
                }
            }

            $news .= "
			<div class='new$p'>
				<span class='datenews'>
					" . parent::make_date($new[$n_date], 0, "DATE", "/") . "
				</span>
				<br>
				<h7 class='titlenews'>
					" . $new[$n_title] . "
				</h7>
				$photo
				<span class='textnews'>
					" . parent::cut_text($new[$n_text], 200, FALSE) . "
				</span>
				<br>
				<a class='more$p' href='" . urldecode(_RURL . $jnews_conf['link'] . "/" . $new[$n_id] . "/" . parent::clean_space($new[$n_title])) . "' >
					saiba mais
				</a>
			</div>
				";
        }

        return "
            <div id='news$p'>
                $news
            </div>
        ";

    }

    /*
     * monta o arquivo de noticias
     */
    public function show_archives()
    {

        $arq = json_decode(_NEWSARCH, TRUE);

        extract($arq['fields'], EXTR_PREFIX_ALL, "a");

        $li_post = NULL;
        $li_month = NULL;
        $li_year = NULL;

        $sql_conditions = parent::define_sql_conditions($arq['conditions']);
        $sql_order = parent::define_sql_order($arq['order']);

        $tcnx = new mysqli('localhost', _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        $arch = array();

        $query = "SELECT " . implode(",", $arq['fields']) . " FROM " . $arq['table'] . " " . $sql_conditions . $sql_order;
        $rslt = $tcnx -> query($query);

        #noticias online
        while ($db_post = $rslt -> fetch_array())
        {

            $post_date = $this -> make_date($db_post[$a_date], 1, "TIMEST");

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

                    if (!empty($post_title[$a_title]))
                    {
                        $li_post .= "
                            <li>
                                <a href='" . urldecode(_RURL . $arq['link'] . "/" . $post_title[$a_id] . "/" . $this -> clean_space($post_title[$a_title])) . "'>
                                    " . $post_title[$a_title] . "
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

        $rslt -> close();
    }

    /*
     * monta os categorias de noticias
     */
    public function show_topics()
    {

        $line = "";

        $topic = json_decode(_NEWSTOPIC, TRUE);

        $tcnx = new mysqli('localhost', _US, _PS, _DB);
        $tcnx -> set_charset("utf8");

        $sql_conditions = parent::define_sql_conditions($topic['conditions']);

        $sql_order = parent::define_sql_order($topic['order']);

        $rslt = $tcnx -> query("SELECT DISTINCT " . $topic['field'] . " FROM " . $topic['table'] . $sql_conditions . " " . $sql_order);

        while ($top = $rslt -> fetch_array())
        {

            $line .= "<li><a href='" . urldecode(_RURL . $topic['link'] . "/" . $top[$topic['field']]) . "'>" . strtolower($top[$topic['field']]) . "</a></li>";
        }

        $rslt -> free();
        $tcnx -> close();

        echo "
			<div id='news_subject'>
				<p>
					" . _TOPICTITLE . "
				</p>
				<ul class='newsyear'>
					" . $line . "
				</ul>
			</div>

			";

    }

}
?>