<?php
/**
 * Manipula imagems. Cria objeto json para guardar na tabela de dados, decodifica para inserir em página html, faz upload de imagem para o servidor e redimensiona.
 * Cria um objeto este objeto json para guardar as imagens em uma coluna de uma tabela na base de dados:
 * photos : { photo : [ { "photo" : " " , "idioma1" : " " , ... , "idiomaN" : " " }, ... ] }
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 1.30
 * @since 24/12/2015
 * @license Todos os direitos reservados
 *
 */


class GestImages {

    /**
     * @var string $ce_code - código da classe nas mensagens de erro
     *
     */
    private static $ce_code = "IMG";
    
    private $reg;

    public function __construct(MGPDINAMIC $registry) {
        
        $this->reg = $registry;
        ini_set("memory_limit", "64M");
        ini_set("upload_max_filesize", "16M");
    }

    /**
     * Manipula o objeto json de armazenamento de fotos na base de dados.
     * Recebe o objeto json guardado na tabela da base de dados e transforma numa coleção de endereços de imagem ou elementos html img
     *
     * @param array $pictures_object = objeto json com as imagens e as legendas armazenado na base de dados
     * @param string $mode = pode ter os valores
     *                  "src" para devolver apenas o endereço da imagem
     *                  "img" para devolver a tag completa
     *                  "arrimg" para devolver uma string com varias imgens envoltas em tag img
     *                  "arr" para devolver uma array de array javascript "["endereço1", "legendas1"],...,["endereçoN", "legendasN"]"
     * @param srting $captions_lang = idioma
     * @param boolean $collection = se FALSE só retorna a primeira foto
     * @param string $html_attributes = atributos html
     *
     * @throws Exception code 1
     *
     * @return string - coleção de endereços de imagens ou elementos html img
     *
     */
    public static function send_images_json($pictures_object, $mode, $captions_lang, $collection = 1, $html_attributes = NULL) {

        if (!$pictures_object)
            throw new Exception(GestImages::$ce_code . __LINE__, 1);

        $pictures = (is_array($pictures_object)) ? $pictures_object : json_decode($pictures_object, TRUE);
        
        

        if ($pictures && is_array($pictures) && is_array($pictures['photos'])) {
            $result = NULL;

            foreach ($pictures['photos'] as $value) {
                
                $captions = (isset($captions_lang)) ? trim($value[strtolower($captions_lang)], '"') : "";
                
                switch ($mode) {
                    case "img":
                        if (!empty($value['photo'])) {
                            
                            $result .= "<img src='" . $value['photo'] . "' alt='" . $captions . "' title='" . $captions . "' $html_attributes>";
                        }
                        break;
                    case "src":
                        if (!empty($value['photo'])) {
                            $result .= $value['photo'];
                        }
                        break;
                    case "imgarr" :
                        $result[] = "<img src='" . $value['photo'] . "' alt='" . $captions . "' title='" . $captions . "' $html_attributes>";
                        break;
                    case "arr":
                        $comma = ($result) ? "," : "";
                        $result .= $comma . '["' . $value['photo'] . '" ,"' . $captions . '"]';
                        break;
                    default:
                        break;
                }


                if (!$collection)
                    return trim($result, ",");
            }

            return $result;
        }

        if ($mode === "img" && $pictures_object)
            return "<img src='" . $pictures_object . "'  $html_attributes>";

        if ($mode === "src" && $pictures_object) {

            try {
                $param[] = $pictures_object;
                $result = parent::make_call("ifImageExist", $param);
            } catch (Exception $ex) {
                return NULL;
            }

            if (!is_array($result))
                return NULL;

            if (!empty($result[0]['resp'])) {
                return _IMAGEURL . "/" . $pictures_object;
            } else {
                return NULL;
            }
        }

        return NULL;
    }

    /**
     * Cria um objeto json para guardar os imagens na base de dados.
     * Retorna este objeto:
     * {"photos" : [{"photos":"" , "mobile":"" , "mini":"", "lang1":"", ... ,"langN":""},..]}
     *
     * @uses Core::text_clean_json();
     *
     * @param array $config - array com 2 elementos ['image_name'] = nome de $_POST com o nome da imagem e ['captions'] = nome das legendas.
     * Normalmente o elemento options do objeto.
     *
     * @param array $client_images - nome das imagens e legendas. As chaves devem ser os valores da array config.
     *  $client_images[$config['image_name']] e $client_images[$config['captions]] ($_POST)
     *
     * @param boolean $as_array . se TRUE devolve uma array, se falso devolve um objeto json
     *
     * @return null|string|array - objeto json
     *
     */
    public function make_json_img_capt(array $config, $client_images, $as_array = FALSE) {

        if (!is_array($client_images) || empty($client_images))
            return NULL;

        //objecto json de uma imagem
        $img_object = NULL;

        //array com o nome das imagens
        $images = NULL;

        //nome dos $_POST de legendas
        $captions_name = NULL;

        if (!isset($client_images) || empty($config['image_name']))
            return NULL;

        $img_name = $config['image_name'];
        $captions_name = $config['captions'];

        if (!isset($client_images[$img_name]))
            return NULL;

        $images = $client_images[$img_name];

        if (!is_array($images))
            return NULL;

        foreach ($images as $img) {

            $object_element = NULL;

            if (empty($img))
                continue;

            $img_url = parse_url($img);

            //Se for uma url completa
            if (parent::validate_url($img) && isset($img_url['host'])) {
                $s_name = $img;
                $object_element['photo'] = $s_name;
                $object_element['mobile'] = "";
                $object_element['mini'] = "";
            } else {
                //grava vários tamanhos das imagens
                $name = $img;
                $object_element['photo'] = _IMAGEURL . "/" . $img;
                $object_elemen['mobile'] = _IMAGEURL . "/mob_" . $name;
                $object_element['mini'] = _IMAGEURL . "/min_" . $name;
            }

            foreach ($GLOBALS['LANGPAGES'] as $lang) {
                $captions = NULL;

                if (isset($client_images[$lang . "_" . $captions_name][$img]))
                    $captions = $client_images[$lang . "_" . $captions_name][$img];

                @$object_element[$lang] .= $captions;
            }

            $img_object['photos'][] = $object_element;
        }

        if ($as_array) {
            return $img_object;
        } else {
        return json_encode($img_object, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
    }

    /**
     * Cria uma imagem por omissão para um item sem imagem
     *
     * @uses MasterConfig::_RAWIMAGE, MasterConfig::_FONT, MasterConfig::_IMAGEPATH, MasterConfig::_IMAGEURL
     *
     * @param string $nome - nome do item
     * @param string $ref - referencia do item
     *
     * @return false|string - nome da imagem
     */
    public function createImage($nome, $ref = "0000") {

        $w = explode(" ", $nome);

        $im = ImageCreateFromPNG(_RAWIMAGE);

        $font = _FONT;

        $text_color = imagecolorallocate($im, 10, 10, 10);

        imagettftext($im, 10, 0, 5, 20, $text_color, $font, $ref);

        if (isset($w[0])) {
            imagettftext($im, 16, 0, 5, 45, $text_color, $font, $w[0]);
        }
        if (isset($w[1])) {
            imagettftext($im, 16, 0, 5, 65, $text_color, $font, $w[1]);
        }
        if (isset($w[2])) {
            imagettftext($im, 16, 0, 5, 85, $text_color, $font, $w[2]);
        }
        if (isset($w[3])) {
            imagettftext($im, 16, 0, 5, 105, $text_color, $font, $w[3]);
        }
        if (isset($w[4])) {
            imagettftext($im, 16, 0, 5, 125, $text_color, $font, $w[4]);
        }
        if (isset($w[5])) {
            imagettftext($im, 16, 0, 5, 145, $text_color, $font, $w[5]);
        }

        $path = _IMAGEPATH . $GLOBALS['NOW'] . ".png";

        $a = imagepng($im, $path);

        if (!$a) {
            return false;
        } else {
            return _IMAGEURL . $GLOBALS['NOW'] . ".png";
        }
    }

    /**
     * Cria imagens embrulhadas em uma DIV que podem ser usadas como fotogaleria. As imagens podem ser acompanhadas por legendas.
     * Pode incluir campos e botão de apagar para usar em fichas de edição.
     * Este metodo repete as mesmas configurações do função javascript imgallery_with_captions do arquivo mgp_nucleogest.
     * Esta função javascript tambem pode manipular as DIV criadas por este metodo.
     *
     * @param array $images_object     - objeto json com as imagens e as legendas armazenado na base de dados
     * @param string $input_image_name - nome do campo input das fotos
     * @param string $captions_name    - nome do campo input das legendas
     * @param boolean $with_captions   - se verdadeiro cria as fotos com legendas
     * @param boolean $allow_edit      - se verdadeiro formata campos input e apresenta botão para apagar
     *
     * @throws Exception code 1
     *
     * @return string HTML
     */
    public function make_photo_gallery_json($images_object, $input_image_name = "foto", $captions_name = "legenda", $with_captions = TRUE, $allow_edit = TRUE) {

        if (empty($images_object))
            return NULL;

        if (!is_array($images_object)) {
        if (!$images_object = json_decode($images_object, TRUE))
            throw new Exception(GestImage::$ce_code . __LINE__, 1);
        }


        if (!isset($images_object['photos']) && !is_array($images_object['photos']))
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        $name = $input_image_name . "[]";
        $index = 0;
        $html_image_blocks = NULL;

        foreach ($images_object['photos'] as $image) {

            if (!is_array($image))
                continue;

            $capt = NULL;
            $del = NULL;
            $input_image = NULL;
            $only_read = "readonly";
            $img = $image['photo'];

            $img_url = parse_url($image['photo']);

            $image_name = ($img_url['host'] == _RURL) ? array_pop(explode("/", $image['photo'])) : $image['photo'];

            if ($allow_edit) {

                $del = '<img src="imagens/minidel.png" class="ig15A">';
                $input_image = '<input type="hidden" id="' . $image_name . '" name="' . $name . '" value="' . $image_name . '"/>';
                $only_read = NULL;
            }

            if ($with_captions) {

                if (!$allow_edit) {

                    $capt .= "
                       <p draggable='false' class='dv98pC'>
                       " . $image[$GLOBALS['LANGPAGES'][0]] . "
                       </p>";
                } else {
                    foreach ($GLOBALS['LANGPAGES'] as $value) {
                        $capt .= "
                       <p draggable='false' class='dv98pC'>
                       <img src='" . $GLOBALS['LANGFLAGS'][$value] . "' class='ig20M' draggable='false'>
                       <input type='text' name='" . $value . "_" . $captions_name . "[" . $image_name . "]' value='" . $image[$value] . "' class='tx90px35pxFF' draggable='false' $only_read >
                       </p>";
                    }
                }
            }

            $html_image_blocks .= "
                        <div data-index='$index' class='dvB' draggable='false'>
                            $del
                            <img src='" . $img . "' class='ig150xp150x'>
                            $input_image
                            $capt
                        </div>
                ";

            $index++;
        }

        return $html_image_blocks;
    }

    /**
     * Envi um elemento HTML img. Verifica se já é uma tag html ou apenas o endereço da imagem.
     *
     * @param string $s_img - nome da imagem ou tag img completa
     *
     * @return false|string - tag html img
     *
     */
    protected function make_html_img($s_img, $classe = NULL, $alt = NULL) {

        if (isset($s_img) && !empty($s_img)) {

            return (preg_match("#<[ ]*img(.*)[ ]*>#i", $s_img)) ? $s_img : ' <img src="' . $s_img . '" class="' . $classe . '" alt="' . $alt . '"> ';
        } else {

            return FALSE;
        }
    }

    /**
     * Envia json com as imagens solicitadas e filtradas
     *
     * @uses Logex::$tcnx, Anti::validate_name()
     *
     * @return string - json com a lista de imagens
     */
    public function image_list() {
        if (!parent::check())
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        $pasta = (isset($_POST['pasta']) && $this->validate_name($_POST['pasta'])) ? $_POST['pasta'] : "";

        try {
            $dbcon = new PDO(_PDOM, _US, _PS);

            $sttm = $dbcon->prepare("SELECT id,nome,mini,pasta FROM foto_galeria WHERE pasta=? ORDER BY id ASC");

            $sttm->bindValue(1, $pasta, PDO::PARAM_STR);

            $sttm->execute();

            $result = $sttm->fetchAll();
        } catch (PDOException $ex) {
            throw new Exception(GestImage::$ce_code . __LINE__, 1);
        }

        $images = "";

        foreach ($result as $image) {
            $images .= ',["i:' . $image['id'] . '","' . $this->cut_str($image['nome'], 20) . '","' . _IMAGEURL . '/' . $image['mini'] . '","' . $image['pasta'] . '"]';
        }

        return '{"images":[' . ltrim($images, ",") . ']}';
    }

    /**
     * Faz o upload e guarda os dados da imagem na base de dados
     *
     * @uses Logex::$tcnx, Anti::validate_name(), GestImage::image_list(), GestImage::redimensiona
     *
     * @throws Exception code 1
     * @throws Exception code 2
     * @throws Exception code 3
     *
     * @return string - json com a lista das imagens
     */
    public function upload_image() {

        if (!$_FILES['foto'])
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        $photo = $_FILES['foto'];

        if ($photo['size'] < 10)
            throw new Exception(GestImage::$ce_code . __LINE__ . $photo['size'], 1);

        $name = str_replace(" ", "_", $photo['name']);

        $image = $photo['tmp_name'];

        //se exif não estiver instalodo usa o gd
        if (function_exists('exif_imagetype')) {
            if (!$typeimg = exif_imagetype($image))
                throw new Exception(GestImage::$ce_code . __LINE__, 3);

            if ($typeimg <> 1 && $typeimg <> 2 && $typeimg <> 3)
                throw new Exception(GestImage::$ce_code . __LINE__, 3);
        }
        else {
            if (!$typeimg = getimagesize($image))
                throw new Exception(GestImage::$ce_code . __LINE__, 3);

            if ($typeimg[2] <> IMAGETYPE_GIF && $typeimg[2] <> IMAGETYPE_JPEG && $typeimg[2] <> IMAGETYPE_JPEG2000 && $typeimg[2] <> IMAGETYPE_PNG)
                throw new Exception(GestImage::$ce_code . __LINE__, 3);
        }

        //TODO - otimizar
        $folder = (isset($_POST['pasta']) && parent::validate_name($_POST['pasta'])) ? $_POST['pasta'] : "";
        try {
            $r_check_name = parent::make_call("spCheckImageName", array($name));

            if (isset($r_check_name[0]['nome'])) {
                $check_name = $r_check_name[0]['nome'];
            } else {
                $check_name = FALSE;
            }
        } catch (Exception $ex) {
            throw new Exception(GestImage::$ce_code . __LINE__, 3);
        }

        if ($check_name)
            throw new Exception(GestImage::$ce_code . __LINE__, 2);

        $path = _IMAGEPATH . $name;

        if (!move_uploaded_file($image, $path))
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        try {
            $mini_pic = $this->redimensdiona($path, "mini_" . $name, 100, 100);
            $this->redimensdiona($path, "min_" . $name, 400, 400);
            $this->redimensdiona($path, "mob_" . $name, 1000, 1000);
        } catch (Exception $exp) {
            $mini_pic = "";
        }

        try {


            $params[] = $name;
            $params[] = $folder;
            $params[] = $mini_pic;

            $result = parent::make_call("spInsertImage", $params);
            $s = "FDSF";
            if (!$result || empty($result[0])) {
                unlink($path);
                throw new Exception(GestImage::$ce_code . __LINE__, 1);
            }


            if (!$mess = json_decode($result[0]['ret'], TRUE)) {
                unlink($path);
                throw new Exception(GestImage::$ce_code . __LINE__, 1);
            }

            if (isset($mess['mgp_error'])) {
                unlink($path);
                throw new Exception(GestImage::$ce_code . __LINE__, 1);
            }
        } catch (Exception $ex) {
            unlink($path);
            throw new Exception(GestImage::$ce_code . __LINE__, 1);
        }

        try {
            return $this->image_list();
        } catch (Exception $exp) {
            throw new Exception(GestImage::$ce_code . __LINE__, 1);
        }
    }

    public function upload_imge_site($file_name) {

        if (!$_FILES[$file_name])
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        $photo = $_FILES[$file_name];

        if ($photo['size'] < 10)
            throw new Exception(GestImage::$ce_code . __LINE__ . $photo['size'], 1);

        $name = $GLOBALS['NOW'] . "_" . str_replace(" ", "_", $photo['name']);

        $image = $photo['tmp_name'];

        //se exif não estiver instalodo usa o gd
        if (function_exists('exif_imagetype')) {
            if (!$typeimg = exif_imagetype($image))
                throw new Exception(GestImage::$ce_code . __LINE__, 3);

            if ($typeimg <> 1 && $typeimg <> 2 && $typeimg <> 3)
                throw new Exception(GestImage::$ce_code . __LINE__, 3);
        }
        else {
            if (!$typeimg = getimagesize($image))
                throw new Exception(GestImage::$ce_code . __LINE__, 3);

            if ($typeimg[2] <> IMAGETYPE_GIF && $typeimg[2] <> IMAGETYPE_JPEG && $typeimg[2] <> IMAGETYPE_JPEG2000 && $typeimg[2] <> IMAGETYPE_PNG)
                throw new Exception(GestImage::$ce_code . __LINE__, 3);
        }

        $path = _IMAGESITEPATH . $name;

        if (!move_uploaded_file($image, $path))
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        try {
            $this->redimensdiona($path, "mini_" . $name, 100, 100);
            $this->redimensdiona($path, "min_" . $name, 400, 400);
            $this->redimensdiona($path, "mob_" . $name, 800, 800);
        } catch (Exception $exp) {
            $mini_pic = "";
        }

        return $name;
    }

    /**
     * Redimensiona uma imagem
     *
     * @param string $imagem - caminho para uma imagem
     * @param string $name - nome para salvar a imagem redimensionada.
     * @param int $w - largura da nova imagem
     * @param int $h - altura da nova imagem
     *
     * @return string - nome da imagem
     *
     */
    private function redimensdiona($imagem, $name, $w, $h) {

        //criamos uma nova imagem ( que vai ser a redimensionada) a partir da imagem original
        if (function_exists('exif_imagetype')) {
            if (!$tipoimg = exif_imagetype($imagem))
                throw new Exception(GestImage::$ce_code . __LINE__, 3);
        } else {
            if (!$typeimg = getimagesize($imagem))
                throw new Exception(GestImage::$ce_code . __LINE__, 3);

            $tipoimg = $typeimg[2];
        }

        switch ($tipoimg) {
            case IMAGETYPE_JPEG :
                $imagem_orig = ImageCreateFromJPEG($imagem);
                break;
            case IMAGETYPE_PNG :
                $imagem_orig = ImageCreateFromPNG($imagem);
                break;
            case IMAGETYPE_GIF :
                $imagem_orig = ImageCreateFromGIF($imagem);
                break;
        }
        //pegamos a altura e a largura da imagem original
        $maxw = $w;
        $maxh = $h;

        $width = imagesx($imagem_orig);
        $height = imagesy($imagem_orig);
        $scale = min($maxw / $width, $maxh / $height);

        if ($scale < 1) {
            $largura = floor($scale * $width);
            $altura = floor($scale * $height);
        } else {
            $largura = $width;
            $altura = $height;
        }

        if (!$imagem_fin = imagecreatetruecolor($largura, $altura))
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        imagealphablending($imagem_fin, false);
        imagesavealpha($imagem_fin, true);

        //copiamos o conteudo da imagem original e passamos para o espaco reservado a redimencao
        if (!imagecopyresized($imagem_fin, $imagem_orig, 0, 0, 0, 0, $largura, $altura, $width, $height))
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        $path = _IMAGEPATH . $name;

        //Salva a imagem
        switch ($tipoimg) {
            case IMAGETYPE_JPEG :
                $a = imagejpeg($imagem_fin, $path);
                break;
            case IMAGETYPE_PNG :
                $a = imagepng($imagem_fin, $path);
                break;
            case IMAGETYPE_GIF :
                $a = imagegif($imagem_fin, $path);
                break;
        }

        //Libera a Memoria
        ImageDestroy($imagem_orig);
        ImageDestroy($imagem_fin);

        if (!$a || !$imagem_fin)
            throw new Exception(GestImage::$ce_code . __LINE__, 1);

        return $name;
    }

    /**
     * Apaga uma imagem na base de dados
     *
     * @param string $db_table - nome da tabela que guarda as imagens
     * @param string $id_column - nome da coluna primary key
     * @param string $folder_column - nome da coluna de pasta
     * @param string $image_id - valor da imagem na columa primary key
     *
     * @return boolean | json
     *
     */
    public function delete_image($db_table, $id_column, $folder_column, $image_id) {

        if (!parent::check())
            return parent::mess_alert("GIM" . __LINE__);

        $id;

        if (!$id = $this->id($image_id))
            return parent::mess_alert("GIM" . __LINE__);

        $rfold = Logex::$tcnx->query("SELECT $folder_column FROM $db_table WHERE $id_column = $id");
        $folders = $rfold->fetch_array();

        $del = Logex::$tcnx->query("DELETE FROM $db_table WHERE $id_column = $id ");

        if (!$del) {

            return FALSE;
        } else {

            return '{"result":["' . $folders[0] . '","' . $id . '"]}';
        }
    }

}

