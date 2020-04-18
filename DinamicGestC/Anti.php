<?php
/**
 * script: Anti.php
 * client: EPKyusho
 *
 * @version V4.10.080615
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
class Anti
{
    function validateEmail($email, $domainCheck = false)
    {
        # Check email syntax with regex
        if (preg_match('/^([a-zA-Z0-9\._\+%-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,15}|[0-9]{1,3})(\]?))$/ui', $email, $matches))
        {
            $user = $matches[1];
            $domain = $matches[2];
            # Check availability of DNS MX records
            if ($domainCheck && function_exists('checkdnsrr'))
            {
                # Construct array of available mailservers
                if (getmxrr($domain, $mxhosts, $mxweight))
                {
                    for ($i = 0; $i < count($mxhosts); $i++)
                    {
                        $mxs[$mxhosts[$i]] = $mxweight[$i];
                    }
                    asort($mxs);
                    $mailers = array_keys($mxs);
                }
                elseif (checkdnsrr($domain, 'MX'))
                {
                    $mailers[0] = gethostbyname($domain);
                }
                else
                {
                    $mailers = array();
                }
                $total = count($mailers);
                if ($total > 0)
                {
                    return $email;
                }
                else
                {
                    return false;
                }
            }

            return $email;
        }
        else
        {
            return false;
        }
    }

    //-----------------------anti injection----------------------------------------------------------------
    function antiInjection($str)
    {
        $str = preg_replace('/([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))/ui', "", $str);
        $str = preg_replace('/(www|www\.|http:|https:|http|https|ftp:|ftp:)((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))/ui', " ", $str);
        $str = preg_replace("/(javascript|javascript:|cmd=|\.fromCharCode|\.js|xss|img|src|FSCommand()|\(\)|link|href|mailto|\.gif|\.jpg|\.swf|\.txt|\.png|\.pdf|#|\*|\/\/|--|@|www|www\.|http:|https:|http|https|ftp:|ftp:)/", " ", $str);
        $str = htmlentities($str, ENT_QUOTES, "UTF-8", true);
        $str = strip_tags($str);
        $str = addslashes($str);
        return $str;
    }

    function antiInjectiond($str)
    {
        $str = preg_replace('/([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))/ui', "", $str);
        $str = preg_replace('/(www|www\.|http:|https:|http|https|ftp:|ftp:)((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))/ui', " ", $str);
        $str = preg_replace("/(<|>|javascript|javascript:|cmd=|\.fromCharCode|\.js|xss|img|src|FSCommand()|\(\)|link|href|mailto|\.gif|\.jpg|\.swf|\.txt|\.png|\.pdf|#|\*|\/\/|--|@|www|www\.|http:|https:|http|https|ftp:|ftp:)/", " ", $str);
        //$str =htmlentities($str,ENT_QUOTES,"UTF-8",true);
        $str = strip_tags($str);
        $str = addslashes($str);
        return $str;
    }

    function cleanInput($input)
    {

        $search = array(
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@' // Strip multi-line comments
        );

        $output = preg_replace($search, "", $input);
        return $output;
    }

    //---------------------------------------valida codigo postal--------------------------------------------

    function vtelefone($codigo1)
    {
        $a = preg_match("/([^\d\x20\(\)\-\+])/", $codigo1);
        if ($a)
        {
            return FALSE;
        }
        else
        {
            return $codigo1;
        }
    }

    //--------------------------------------------valida medidas--------------------------------------------------
    function verificaMedidas($string)
    {
        $b = antiInjection($string);
        $erro = "fals0";
        $a = preg_match("/([^0-9,.])/u", $b);
        if ($a == 1)
        {
            return $erro;
        }
        else
        {
            return $b;
        }
    }

    //---------------------------------------------anti xss----------------------------------------------------------
    function RemoveXSS($val)
    {

        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';

        for ($i = 0; $i < strlen($search); $i++)
        {
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val);
            // with a ;
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val);
            // with a ;
        }
        $ra1 = Array(
            'javascript',
            'vbscript',
            'expression',
            'applet',
            'meta',
            'xml',
            'blink',
            'link',
            'style',
            'script',
            'embed',
            'object',
            'iframe',
            'frame',
            'frameset',
            'ilayer',
            'layer',
            'bgsound',
            'title',
            'base'
        );
        $ra2 = Array(
            'onabort',
            'onactivate',
            'onafterprint',
            'onafterupdate',
            'onbeforeactivate',
            'onbeforecopy',
            'onbeforecut',
            'onbeforedeactivate',
            'onbeforeeditfocus',
            'onbeforepaste',
            'onbeforeprint',
            'onbeforeunload',
            'onbeforeupdate',
            'onblur',
            'onbounce',
            'oncellchange',
            'onchange',
            'onclick',
            'oncontextmenu',
            'oncontrolselect',
            'oncopy',
            'oncut',
            'ondataavailable',
            'ondatasetchanged',
            'ondatasetcomplete',
            'ondblclick',
            'ondeactivate',
            'ondrag',
            'ondragend',
            'ondragenter',
            'ondragleave',
            'ondragover',
            'ondragstart',
            'ondrop',
            'onerror',
            'onerrorupdate',
            'onfilterchange',
            'onfinish',
            'onfocus',
            'onfocusin',
            'onfocusout',
            'onhelp',
            'onkeydown',
            'onkeypress',
            'onkeyup',
            'onlayoutcomplete',
            'onload',
            'onlosecapture',
            'onmousedown',
            'onmouseenter',
            'onmouseleave',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'onmousewheel',
            'onmove',
            'onmoveend',
            'onmovestart',
            'onpaste',
            'onpropertychange',
            'onreadystatechange',
            'onreset',
            'onresize',
            'onresizeend',
            'onresizestart',
            'onrowenter',
            'onrowexit',
            'onrowsdelete',
            'onrowsinserted',
            'onscroll',
            'onselect',
            'onselectionchange',
            'onselectstart',
            'onstart',
            'onstop',
            'onsubmit',
            'onunload'
        );
        $ra = array_merge($ra1, $ra2);
        $found = true;
        // keep replacing as long as the previous round replaced something
        while ($found == true)
        {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++)
            {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++)
                {
                    if ($j > 0)
                    {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);
                // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val);
                // filter out the hex tags
                if ($val_before == $val)
                {
                    $found = false;
                }
            }
        }
        return $val;
    }

    //-----------------------------------------------------verifica numeros--------------------------------------------------
    function numeros($string)
    {
        $a = preg_match("/([^0-9])/u", $string);
        if ($a == 1)
        {
            return "NEUM";
        }
        else
        {
            settype($string, "integer");
            return $string;
        }
    }

    function vdata($string)
    {
        $a = preg_match("/\d{1,2}\/[0-9]{1,2}\/[0-9]{4}/", $string);
        if (!$a)
        {
            return FALSE;
        }
        else
        {
            return $string;
        }
    }

    function vhora($string)
    {
        $a = preg_match("/\d{1,2}:0-9]{1,2}/", $string);
        if (!$a)
        {
            return FALSE;
        }
        else
        {
            return $string;
        }
    }

    function verificaTexto($string)
    {
        $b = $this -> antiInjection($string);
        $c = $this -> cleanInput($b);
        $d = $this -> RemoveXSS($c);
        $e = preg_replace("/([\x23\x27\x2A\x3C\x3E\x40\x5C\'])/", " ", $d);
        $f = preg_replace("/(&lt;p&gt;|&lt;\/div&gt;)/ui", " ", $e);
        $g = preg_replace("/(&lt;br&gt;&lt;\/p&gt;|&lt;br&gt;|&lt;\/p&gt;|&lt;div&gt;)/ui", "<br>", $f);
        return $g;
    }

    public function validate_text($string)
    {
        $b = $this -> antiInjection($string);
        $c = $this -> cleanInput($b);
        $d = $this -> RemoveXSS($c);
        $e = preg_replace("/([\x23\x27\x2A\x3C\x3E\x40\x5C\'])/", " ", $d);
        $f = preg_replace("/(&lt;p&gt;|&lt;\/div&gt;)/ui", " ", $e);
        $g = preg_replace("/(&lt;br&gt;&lt;\/p&gt;|&lt;br&gt;|&lt;\/p&gt;|&lt;div&gt;)/ui", "<br>", $f);
        return $g;
    }

    function verificaNick($string)
    {
        $b = $this -> antiInjection($string);
        $c = $this -> cleanInput($b);
        $d = $this -> RemoveXSS($c);
        $d = $d;
        $a = preg_match('/([^0-9A-Za-zÀ-ÿ \_\-]+)/', $d);
        $r = strlen($d);
        if ($r > 2 && $r < 21)
        {
            if ($a == 1)
            {
                return false;
            }
            else
            {
                return $b;
            }
        }
        else
        {
            return FALSE;
        }
    }

    public function validate_date($datex)
    {

        if (preg_match("#^(\d{2})-(\d{2})-(\d{4})#", $datex, $matches))
        {

            if (checkdate($matches[2], $matches[1], $matches[3]))
            {

                return TRUE;
            }
            else
            {
                return FALSE;

            }

        }
        else
        {
            return FALSE;

        }

    }
    
    /**
     * Valida uma string de input
     * Remove ou substitui caracteres de xss e injections.
     * 
     * @param string $string
     * @param int $len - tamanho minimo da que deve ter a string (n-1). Por padrão o tamanho é de 2 caracteres. 
     * 
     * @return boolean
     */
    function verificaNome($string, $len=2)
    {
        $b = $this -> antiInjectiond($string);
        $c = $this -> cleanInput($b);
        $d = $this -> RemoveXSS($c);
        $a = preg_match('/([^\d\w\xc3\x80-\xc3\x96\x20\x2D\xc3\x99-\xc3\xb6,\xc3\xb9-\xc3\xbf])/i', $b);
        $r = strlen($d);
        if ($r > $len)
        {
            if ($a == 1)
            {
                return FALSE;
            }
            else
            {
                return $d;
            }
        }
        else
        {
            return FALSE;
        }
    }
    /**
     * Valida um input.
     * Remove ou substitui caracteres de xss e injections
     * 
     * @param type $string
     * 
     * @return string|boolean em caso de passar no teste devolve a string. Caso tenha caracteres proibidos devolve uma string vazia. Caso seja uma string vazia devolve FALSE
     * 
     */
    public function validate_name($string)
    {
        $b = $this -> antiInjectiond($string);
        $c = $this -> cleanInput($b);
        $d = $this -> RemoveXSS($c);
        $a = preg_match('/([^\d\w\xc3\x80-\xc3\x96\x20\x2D\xc3\x99-\xc3\xb6,\xc3\xb9-\xc3\xbf\.])/i', $b);
        $r = strlen($d);
        if ($r > 0)
        {
            if ($a == 1)
            {
                return "";
            }
            else
            {
                return $d;
            }
        }
        else
        {
            return FALSE;
        }
    }

    public function validate_url($urlx)
    {
        //$pat = "%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu";

        //$pat = "/\b((?:(?:https?|ftp):\/\/|www\.))([-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|])/i";

        if(!is_string($urlx))
            return FALSE;

        $pat = "/^(https:\/\/|http:\/\/)(www\.){0,1}([-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|])\.([a-z]){2,10}((\/|\?){1}(.)*)?$/i";

        if(preg_match($pat, $urlx, $matches))
            return $matches;
        return FALSE;
    }

}
?>
