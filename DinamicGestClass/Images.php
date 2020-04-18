<?php
/***********************************************
    SCRIPT Images.php V1.00
    20-05-2013
    COPYRIGHT MANUEL GERARDO PEREIRA 2013
    TODOS OS DIREITOS RESERVADOS
    CONTACTO: GPEREIRA@MGPDINAMIC.COM
    WWW.MGPDINAMIC.COM

**************************************************/
header("Content-type: text/html; charset=utf-8");
ini_set("default_charset","UTF-8");
ini_set("memory_limit", "64M");
ini_set("upload_max_filesize", "16M");
ini_set('display_errors',0);
require 'Core.php';

class Images extends Core {     
    
/*
 * envia json com as imagens solicitadas e filtradas
 * $p=nome da pasta
 * $o=opção de pesquisa
 * $v=valor da opção de pesquisa
 */
public function image_list(){
        if (parent::check()) {
        
            $pasta=(isset($_POST['pasta']) && $this->validate_name($_POST['pasta'])) ? $_POST['pasta'] : "";      
        
            $rslt = logex::$tcnx -> query("SELECT id,nome,mini,pasta FROM foto_galeria WHERE pasta='$pasta' ORDER BY id ASC");

            $images="";

            while($image=$rslt->fetch_array())
            {
                $images .=',["i:'.$image['id'].'","'.  $this->cut_str($image['nome'],20).'","'._IMAGEURL.'/'.$image['mini'].'","'.$image['pasta'].'"]';
            }
            return '{"images":['.ltrim($images,",").']}';
        }
}

/*
 * faz o upload e guarda os dados da imagem na base de dados
 */
public function upload_image(){
    
    $photo = $_FILES['foto'];

    $name = str_replace(" ","_",$photo['name']);
    $image=$photo['tmp_name'];
    $typeimg = exif_imagetype($image);
    
    $folder = (isset($_POST['pasta']) && $this->validate_name($_POST['pasta'])) ? $_POST['pasta'] : "";


    if($typeimg <> 1 && $typeimg <> 2 && $typeimg <> 3){

        return '{"error":"ficheiro ineadequado"}';
    }

    
    $r_check_name =  logex::$tcnx->query("SELECT nome FROM foto_galeria WHERE nome='$name'");
    $check_name = $r_check_name->fetch_array();
    
    if($check_name[0]==$name){
        
        return '{"error":"ficheiro já existe"}';
    }



    $path=_IMAGEPATH.$name;
    
    $up_file=move_uploaded_file($image,$path);
    
    if(!$up_file){
        
       return '{"error":"erro de gravação"}';
       
    }

    if($photo['size']>10 ){
        
        $main_pic = $name;
        $mini_pic = $this->redimensdiona($path,"mini_".$name,100,100);
    }
    
    
    if(logex::$tcnx->query("INSERT INTO foto_galeria (nome,pasta,mini) VALUES ('$main_pic','$folder','$mini_pic')")){

        return $this->image_list();
    }
    else{
        
        return '{"error":"erro de gravação"}';
    }

}
/*
 * redimensiona uma imagem
 */
private function redimensdiona($imagem,$name,$w,$h){
        
        //criamos uma nova imagem ( que vai ser a redimensionada) a partir da imagem original
        $tipoimg=exif_imagetype($imagem);
        switch($tipoimg){
        case IMAGETYPE_JPEG:
                $imagem_orig = ImageCreateFromJPEG($imagem);
                break;
        case IMAGETYPE_PNG:
                $imagem_orig = ImageCreateFromPNG($imagem);
                break;
        case IMAGETYPE_GIF:
                $imagem_orig = ImageCreateFromGIF($imagem);
                break;
                }
        //pegamos a altura e a largura da imagem original
        $maxw=$w;
        $maxh=$h;
        $width  = imagesx($imagem_orig); 
        $height = imagesy($imagem_orig); 
        $scale  = min($maxw/$width, $maxh/$height); 
        if ($scale < 1) { 
                $largura = floor($scale*$width); 
                $altura = floor($scale*$height); }
        else{
            $largura = $width; 
            $altura = $height; 
                }
                $imagem_fin = imagecreatetruecolor($largura, $altura) or die();
                imagealphablending($imagem_fin, false);
                imagesavealpha($imagem_fin, true);


        //copiamos o conteudo da imagem original e passamos para o espaco reservado a redimencao
        imagecopyresized($imagem_fin, $imagem_orig, 0, 0, 0, 0, $largura, $altura, $width, $height);
        $path=_IMAGEPATH.$name;
        //Salva a imagem
        switch($tipoimg){
        case IMAGETYPE_JPEG:
                $a=imagejpeg($imagem_fin,$path);
                break;
        case IMAGETYPE_PNG:
                $a=imagepng($imagem_fin,$path);
                break;
        case IMAGETYPE_GIF:
                $a=imagegif($imagem_fin,$path);
                break;
                }
        //Libera a Memoria
        ImageDestroy ($imagem_orig);
        ImageDestroy ($imagem_fin);
        if(!$a || !$imagem_fin){
        return false;
        }
        else{
        return $name;}
}
}
?>
