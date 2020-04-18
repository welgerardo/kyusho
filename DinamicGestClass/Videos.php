<?php

/* SCRIPT Videos V5.00
  29-06-2013
  COPYRIGHT MANUEL GERARDO PEREIRA 2013
  TODOS OS DIREITOS RESERVADOS
  CONTACTO: GPEREIRA@MGPDINAMIC.COM
  WWW.MGPDINAMIC.COM */
ini_set("memory_limit", "64M");
ini_set("upload_max_filesize", "16M");
require 'Core.php';

class Videos extends Core {

    public function add_video() {
        if (parent::check()) {

            $filename = $_FILES['foto']['name'];
            $ext = strtolower(substr($filename, strrpos($filename, '.') + 1));

            if ($ext === "mp4" || $ext === "flv") {

                $nome = preg_replace("/[^A-z0-9.]/", "_", $_FILES['foto']['name']);


                $Q2 = logex::$tcnx->query("SELECT nome_video FROM video_galeria WHERE nome_video='$nome'");
                $Q3 = $Q2->fetch_array();

                if ($Q3[0] == $nome) {
                    return '{"error":"Jà existe um video com esse nome."}';
                }

                $path = _VIDEOPATH . $nome;

                $foty = move_uploaded_file($_FILES['foto']['tmp_name'], $path);

                if (!$foty) {

                    return '{"error":"Erro na gravação do video.COD:V001"}';
                }


                $qImg2 = logex::$tcnx->query("INSERT INTO video_galeria (nome_video,pasta,data) VALUES ('$nome','$_POST[pasta]','" . $GLOBALS['NOW'] . "')");
                if (!$qImg2) {

                    return;
                    '{"error":"Erro na gravação do video.COD:V001"}';
                } else {

                    return $this->video_list();
                }
            } else {
                echo '{"error":"Formato de video incompatível."}';
                exit;
            }
        }
    }

    public function video_list() {
        if (parent::check()) {

            $pasta = (isset($_POST['pasta']) && $this->validate_name($_POST['pasta'])) ? $_POST['pasta'] : "";

            $rslt = logex::$tcnx->query("SELECT id,nome_video,pasta FROM video_galeria WHERE pasta='$pasta' ORDER BY id ASC");

            $images = "";

            while ($image = $rslt->fetch_array()) {
                $images .=',["i:' . $image['id'] . '","' . $image['nome_video'] . '","' . _VIDEOURL . '/' . $image['nome_video'] . '","' . $image['pasta'] . '"]';
            }
            return '{"videos":[' . ltrim($images, ",") . ']}';
        }
    }

}

?>