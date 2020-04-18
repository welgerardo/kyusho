<?php
/**
 * script: Page.php
 * client:petit amour
 *
 * @version V1.00.241215
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 *
 */
class Page {

    private $reg;
    private $content;
    private $lang;
    
    public $page_body;
    private static $view_path = "Views/Page/";

    public function __construct(MGPDINAMIC $registry) {

        $this->reg = $registry;
        $this->lang = $registry->get_language();
    }

    /**
     * 
     * @param array $css
     * @param array $styles
     * @param array $javascript
     * @param array $scripts
     */
    public function do_page() {

        $folder = (isset($_GET['folder'])) ? $_GET['folder'] : "pages";
        $control = (isset($_GET['control'])) ? $_GET['control'] : "home";
        $method = (isset($_GET['method'])) ? $_GET['method'] : FALSE;

        $path = 'Controllers/' . $folder . '/' . $control . '.contr.php';

        if ($folder && is_dir('Controllers/' . $folder) && file_exists($path)) {

            require $path;

            if (class_exists($control)) {

                if ($method) {
                    $this->pageBit($control, $method);
                } else {
                    $this->pageAll($control);
                }
            }
        }
    }

    /**
     * 
     * @param type $controller
     * @param type $method
     */
    private function pageBit($controller, $method) {

        $class = new $controller($this->reg, FALSE);

        if (method_exists($class, $method)) {
            echo $class->$method();
            exit();
        }
    }

    private function pageAll($controller) {

        new $controller($this->reg);
        
        $this->page_body = $this->content;
    }
    
    /**
     * 
     * @param type $view_path
     * @return type
     */
    public function getView($view_path) {
        
        $view = "Views/" . $view_path;

        if (file_exists($view)) {

            $view = file_get_contents($view);
        } else {

            $view = NULL;
        }

        return $view;
    }

    /**
     * 
     * @param array $css
     * @param type $view
     * @param type $tag
     * @return type
     */
    private function add_lines(array $css, $view, $tag) {

        $lines = NULL;

        $content = file_get_contents($view);

        foreach ($css as $value) {

            $lines .= str_replace($tag, $value, $content);
        }

        return $lines;
    }


    public function set_content($cont) {

        $this->content = $cont;
    }

    /**
     * 
     * @param type $url
     * @return type
     */
    public function get_facebook_share_buttom($url) {

        return str_replace("{url}", $url, file_get_contents(self::$view_path . "social_buttons/facebookShareButtom.tpl.php"));
    }

    /**
     * 
     * @param type $url
     * @param type $image
     * @param type $description
     * @return type
     */
    public function get_pinterest_share_buttom($url, $image, $description) {

        $view = file_get_contents(self::$view_path . "social_buttons/pinterestShareButtom.tpl.php");

        $tags = array("{url}", "{image}", "{description}");

        return str_replace($tags, array($url, $image, $description), $view);
    }

    /**
     * 
     * @return type
     */
    public function get_googleplus_share_buttom() {

        return file_get_contents(self::$view_path . "social_buttons/googlePlusShareButtom.tpl.php");
    }
    
    /**
     * 
     * @param type $url
     * @param type $description
     * @return type
     */
    public function get_twitter_share_buttom($url, $description) {

        $view = file_get_contents(self::$view_path . "social_buttons/twitterShareButtom.tpl.php");

        $tags = array("{url}", "{text}");

        return str_replace($tags, array($url, $description), $view);
    }
    
        /**
     * 
     * @param type $url
     * @return type
     */
    public function get_linkedin_share_buttom($url) {

        return str_replace("{url}", $url, file_get_contents(self::$view_path . "social_buttons/linkedinShareButtom.tpl.php"));
    }

}
