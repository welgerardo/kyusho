<?php

/**
 * Manipula imagems. Cria objeto json para guardar na tabela de dados, decodifica para inserir em página html, faz upload de imagem para o servidor e redimensiona.
 * Cria um objeto este objeto json para guardar as imagens em uma coluna de uma tabela na base de dados:
 * photos : { photo : [ { "photo" : " " , "idioma1" : " " , ... , "idiomaN" : " " }, ... ] }
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version 1.0
 * @since 08/10/2014
 * @license Todos os direitos reservados
 *
 */
class GestVideos {

    /**
     * @var string $ce_code - código da classe nas mensagens de erro
     *
     */
    private static $ce_code = "VID";

    /**
     * cria objeto json para guardar dados de video na base de dados
     * @return json object
     */
    public function get_video() {

        $from = NULL;
        $vid = NULL;
        $pos = NULL;

        if (!empty($_POST['embvideo']) || !empty($_POST['filevideo'])) {

            //verifica que tipo de video foi enviado, se embeded, se do arquivo
            if ($_POST['embvideo']) {

                $from = "embeded";
                $vid = $_POST['embvideo'];
            }
            if ($_POST['filevideo']) {

                $from = "fromfile";
                $vid = $_POST['filevideo'];
            }

            $pos = ($_POST['videopos'] == "baixo") ? "baixo" : "cima";

            return '{"video":"' . self::text_clean_json($vid) . '","from":"embeded","pos":"' . $pos . '"}';
        } else {
            return "";
        }
    }

    /**
     * Cria na ficha de edição um espaço para inserir videos
     *
     * @uses ElemetSelect::make_select()
     *
     * @param string $V - objeto de configuração dos campos de video
     *
     * @return string - HTML com os campos de inserção de dados.
     *
     */
    public function make_video_insert($V, $select_active = FALSE) {
        
        $video = json_decode($V, TRUE);

        $local_video_name = NULL;
        $emb_video = NULL;
        $select = NULL;

        $cima = ($video['pos'] === "cima") ? "checked='checked'" : $baixo = "checked='checked'";

        //verifica o tipo de video inserido
        if ($video["from"] == "fromfile")
            $local_video_name = $video["video"];

        if ($video["from"] == "embeded")
            $emb_video = $video["video"];

        if ($select_active) {
            $select_video = array();

            $select_video['dynamic']['table'] = "video_galeria";
            $select_video['dynamic']['values'] = array("nome_video" => "nome_video");
            $select_video['dynamic']['condition'] = NULL;
            $select_video['static'] = NULL;

            $tag_select = new ElemetSelect;

            $select = "<div class='video_division'>
                          <p class='video_title'>
                               Arquivo
                          </p>
                          " . $tag_select->make_select('filevideo', $local_video_name, $select_video) . "
                       </div>";
        }

        $pos = new ElementRadioButton();

        return "
            <div id='video_insert'>
                <div class='video_division'>
                    <p class='video_title'>Posição</p>
                    " . $pos->make_radiob(array("cima" => "cima", "baixo" => "baixo"), $video['pos'], "videopos") . "
                </div>
                <div class='video_division'>
                    <p class='video_title'>Incorporar</p>
                    <textarea name='embvideo' class='editbox'>$emb_video</textarea>
                </div>
                $select
            </div>
            <div class='rodape' ></div>
                ";
    }

    /**
     * cria a reprodução de um video para inserir numa pagina html
     * @param string $V = video
     * @param string $M = mode de video
     * @param string $C = class css
     * @param int $W = width
     * @param int $H = height
     */
    public function make_video($V, $M, $C = "newsvideo", $W = 600, $H = 409) {
        if ($V) {
            if ($M == "embeded") {

                return "<div class='$C'>$V</div>";
            }
            if ($M == "fromfile") {

                $pt = urlencode(_VIDEOURL);

                return '
                        <div class="' . $C . '">
                            <object width="' . $W . '" height="' . $H . '">
                                <param name="wmode" value="transparent"></param>
                                <param name="movie" value="http://fpdownload.adobe.com/strobe/FlashMediaPlayback.swf"></param>
                                <param name="flashvars" value="src=' . $pt . $V . '"></param>
                                <param name="allowFullScreen" value="true"></param>
                                <param name="allowscriptaccess" value="always"></param>
                                <embed src="http://fpdownload.adobe.com/strobe/FlashMediaPlayback.swf" type="application/x-shockwave-flash" wmode="transparent" allowscriptaccess="always" allowfullscreen="true" width="' . $W . '" height="' . $H . '" flashvars="src=' . $pt . $V . '"></embed>
                            </object>
                        </div>';
            }
        }
    }

    /*
     * cria a reprodução de um video para inserir numa pagina html
     * $V = resultado da pesquisa na base de dados
     * $C = class css
     * $W = width
     * $H = height
     */

    public function make_video_json($V, $C = "newsvideo", $W = 600, $H = 409) {

        $j_video = json_decode($V, TRUE);
        
        if (!empty($j_video)) {
            if ($j_video['from'] == "embeded") {

                return "<div class='$C'>" . $j_video['video'] . "</div>";
            }
            if ($j_video['from'] == "fromfile") {

                $pt = urlencode(_VIDEOURL);

                return '
                        <div class="' . $C . '">
                            <object width="' . $W . '" height="' . $H . '">
                                <param name="wmode" value="transparent"></param>
                                <param name="movie" value="http://fpdownload.adobe.com/strobe/FlashMediaPlayback.swf"></param>
                                <param name="flashvars" value="src=' . $pt . $j_video['video'] . '"></param>
                                <param name="allowFullScreen" value="true"></param>
                                <param name="allowscriptaccess" value="always"></param>
                                <embed src="http://fpdownload.adobe.com/strobe/FlashMediaPlayback.swf" type="application/x-shockwave-flash" wmode="transparent" allowscriptaccess="always" allowfullscreen="true" width="' . $W . '" height="' . $H . '" flashvars="src=' . $pt . $j_video['video'] . '"></embed>
                            </object>
                        </div>';
            }
        } else {
            return FALSE;
        }
    }

}