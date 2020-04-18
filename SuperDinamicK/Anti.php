<?php

ini_set('display_errors', 1);
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Anti
 *
 * @author Gerardo
 *
 * v1.0
 * 24/06/2011
 */
class Anti {

    function validateEmail($email, $domainCheck = false) {
        # Check email syntax with regex
        if (preg_match('/^([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))$/ui', $email, $matches)) {
            $user = $matches[1];
            $domain = $matches[2];
            # Check availability of DNS MX records
            if ($domainCheck && function_exists('checkdnsrr')) {
                # Construct array of available mailservers
                if (getmxrr($domain, $mxhosts, $mxweight)) {
                    for ($i = 0; $i < count($mxhosts); $i++) {
                        $mxs[$mxhosts[$i]] = $mxweight[$i];
                    }
                    asort($mxs);
                    $mailers = array_keys($mxs);
                } elseif (checkdnsrr($domain, 'MX')) {
                    $mailers[0] = gethostbyname($domain);
                } else {
                    $mailers = array();
                }
                $total = count($mailers);
                if ($total > 0) {
                    return $email;
                } else {
                    return false;
                }
            }

            return $email;
        } else {
            return false;
        }
    }

//---------------valida nomes------------------------------------------------------------------------
    function verificaString2($string) {

        $a = preg_match('/([^A-Za-zÃ?-Ã?Ã‘-Ã–Ã™-Ã¶Ã¹-Ã¿ @-]+)/', $string);

        if (strlen($string) > 1 && strlen($string) < 50) {
            if ($a == 1) {
                return false;
            } else {
                return $string;
            }
        } else {
            return FALSE;
        }
    }

//-----------------------anti injection----------------------------------------------------------------
    function antiInjection($str) {
        $erro = "a";
        $str = preg_replace('/([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))/ui', " ", $str);
        $str = preg_replace('/(www|www\.|http:|https:|http|https|ftp:|ftp:)((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))/ui', " ", $str);
        $str = preg_replace("/(javascript|javascript:|cmd=|\.fromCharCode|\.js|xss|img|src|FSCommand()|\(\)|link|href|mailto|\.gif|\.jpg|\.swf|\.txt|\.png|\.pdf|#|\*|\/\/|--|@|www|www\.|http:|https:|http|https|ftp:|ftp:)/", " ", $str);
        $str = htmlentities($str, ENT_QUOTES, "UTF-8", true);
        $str = strip_tags($str);
        $str = addslashes($str);
        return $str;
    }

    function antiInjectiond($str) {
        $erro = " ";
        $str = preg_replace('/([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))/ui', " ", $str);
        $str = preg_replace('/(www|www\.|http:|https:|http|https|ftp:|ftp:)((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))/ui', " ", $str);
        $str = preg_replace("/(<|>|javascript|javascript:|cmd=|\.fromCharCode|\.js|xss|img|src|FSCommand()|\(\)|link|href|mailto|\.gif|\.jpg|\.swf|\.txt|\.png|\.pdf|#|\*|\/\/|--|@|www|www\.|http:|https:|http|https|ftp:|ftp:)/", " ", $str);
//$str =htmlentities($str,ENT_QUOTES,"UTF-8",true);
        $str = strip_tags($str);
        $str = addslashes($str);
        return $str;
    }

    function cleanInput($input) {

        $search = array(
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
        );

        $output = preg_replace($search, '', $input);
        return $output;
    }

//------------------------valida nick-----------------------------------------------------------------
    function verificaString($string) {
        $c = cleanInput($string);
        $b = antiInjection($c);
        $patern = "/($string)/";
        $a = preg_match('/([^A-Za-z Ã¡Ã Ã¢Ã£Ã¤Ã„Ã?Ã€ÃƒÃ‚Ã©Ã¨ÃªÃ«Ã‹Ã‰ÃˆÃŠÃ­Ã¬Ã?ÃŒÃ¶Ã³Ã²ÃµÃ´Ã–Ã�?Ã•Ã“Ã’Ã¼ÃºÃ¹Ã™ÃšÃœÃ§Ã‡Ã‘Ã±.0123456789\/\,:0_ÃªÃŠ-]+)/', $b);
        $r = strlen($b);
        if (strlen($b) > 2) {
            if ($a == 1) {
                return false;
            } else {
                return $b;
            }
        } else {
            return false;
        }
    }

//---------------------------------------valida codigo postal--------------------------------------------
    function verificaCp($codigo1) {
        $a = preg_match("/(\d{4} {0,}- {0,}\d{3}[A-Za-z \-�-��-��-��-�]{0,})/", $codigo1);
        if ($a) {
            return $codigo1;
        } else {
            return FALSE;
        }
    }

    function vtelefone($codigo1) {
        $a = preg_match("/([^\d\x20\(\)\-\+])/", $codigo1);
        if ($a) {
            return FALSE;
        } else {
            return $codigo1;
        }
    }

//--------------------------------------------valida medidas--------------------------------------------------
    function verificaMedidas($string) {
        $b = antiInjection($string);
        $erro = "fals0";
        $a = preg_match("/([^0-9,.])/u", $b);
        if ($a == 1) {
            return $erro;
        } else {
            return $b;
        }
    }

//---------------------------------------------anti xss----------------------------------------------------------
    function RemoveXSS($val) {

        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';

        for ($i = 0; $i < strlen($search); $i++) {
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
        }
        $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);
        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    $found = false;
                }
            }
        }
        return $val;
    }

//-----------------------------------------------------verifica numeros--------------------------------------------------
    function numeros($string) {
        $a = preg_match("/([^0-9])/u", $string);
        if ($a == 1) {
            return "NEUM";
        } else {
            settype($string, "integer");
            return $string;
        }
    }

    function vdata($string) {
        $a = preg_match("/\d{1,2}\/[0-9]{1,2}\/[0-9]{4}/", $string);
        if (!$a) {
            return FALSE;
        } else {
            return $string;
        }
    }

    function vhora($string) {
        $a = preg_match("/\d{1,2}:0-9]{1,2}/", $string);
        if (!$a) {
            return FALSE;
        } else {
            return $string;
        }
    }

    function verificaTexto($string) {
        $b = $this->antiInjection($string);
        $c = $this->cleanInput($b);
        $d = $this->RemoveXSS($c);
        $e = preg_replace("/([\x23\x27\x2A\x3C\x3E\x40\x5C\'])/", " ", $d);
        $f = preg_replace("/(&lt;p&gt;|&lt;\/div&gt;)/ui", " ", $e);
        $g = preg_replace("/(&lt;br&gt;&lt;\/p&gt;|&lt;br&gt;|&lt;\/p&gt;|&lt;div&gt;)/ui", "<br>", $f);
        return $g;
    }

    function verificaNick($string) {
        $b = $this->antiInjection($string);
        $c = $this->cleanInput($b);
        $d = $this->RemoveXSS($c);
        $d = $d;
        $a = preg_match('/([^0-9A-Za-zÀ-ÿ \_\-]+)/', $d);
        $r = strlen($d);
        if ($r > 2 && $r < 21) {
            if ($a == 1) {
                return false;
            } else {
                return $b;
            }
        } else {
            return FALSE;
        }
    }

    function verificaNome($string) {
        $b = $this->antiInjectiond($string);
        $c = $this->cleanInput($b);
        $d = $this->RemoveXSS($c);
        $a = preg_match('/([^\d\w\xc3\x80-\xc3\x96\x20\x2D\xc3\x99-\xc3\xb6,\xc3\xb9-\xc3\xbf])/i', $b);
        $r = strlen($d);
        if ($r > 2) {
            if ($a == 1) {
                return FALSE;
            } else {
                return $d;
            }
        } else {
            return FALSE;
        }
    }

    function dati($dias, $mesR, $sAno) {
        $ano = array();
        $year = strftime("%Y");
        for ($i = 0; $i < 108; $i++) {
            $ano[$i] = $year - $i;
        }
        $meses = array('Jan' => "01", 'Fev' => "02", 'Mar' => "03", 'Abr' => "04", 'Mai' => "05", 'Jun' => "06", 'Jul' => "07", 'Ago' => "08", 'Set' => "09", 'Out' => "10", 'Nov' => "10", 'Dez' => "12");
        $dia = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31");

        echo "<select name='dia' id='dia' size='1'><option value=''> -- </option>";
        if (strlen($dias) == 0) {
            foreach ($dia as $inDia => $numbDia) {
                echo "<option value=$numbDia>$numbDia</option>";
            }
        }
        if (strlen($dias) != 0) {
            foreach ($dia as $numbDia) {
                if ($dias == $numbDia) {
                    echo "<option value=$numbDia selected>$numbDia</option>";
                } else {
                    echo "<option value=$numbDia>$numbDia</option>";
                }
            }
        }
        echo"</select> / ";

        echo "<select name='mes' id='mes' size='1'><option value=''> -- </option>";
        if ($mesR == 0) {
            foreach ($meses as $nomeMes => $valor) {
                echo "<option value='$valor'>$nomeMes</option>";
            }
        } else {
            foreach ($meses as $nomeMes => $valor) {
                if ($mesR == $valor) {
                    echo "<option value='$valor' selected>$nomeMes</option>";
                } else {
                    echo "<option value='$valor'>$nomeMes</option>";
                }
            }
        }
        echo "</select> / ";

        echo"<select name='ano' id='ano' size='1'><option value=''> ---- </option>";
        if ($sAno == 0) {
            for ($f = 0; $f < 109; $f++) {
                echo "<option value=$ano[$f]>$ano[$f]</option>";
            }
        }
        if ($sAno != 0) {
            foreach ($ano as $indAno => $valor) {
                if ($sAno == $valor) {
                    echo "<option value=$valor selected>$valor</option>";
                } else {
                    echo "<option value=$valor >$valor</option>";
                }
            }
        }
        echo"</select>";
    }

    /*
     * limpa um texto dos carcteres que não podem ser utilizados em JSON
     * $T = texto
     */

    public function text_clean_json($T) {

        $ct = preg_replace_callback('/<[^>]*>/', create_function('$matches', 'return str_replace("\"","\'",$matches[0]);'), $T);
        $ct = str_replace('"', "&#34", $ct);
        $ct = preg_replace_callback('/>[^><]*</', create_function('$matches', 'return str_replace("\\\", "",$matches[0]);'), $ct);

        return $ct;
    }

}

?>
