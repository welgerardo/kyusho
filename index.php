<?php
ini_set('display_errors', 1);
//set_include_path("/var/www/vhosts/kyusho.pt/");
require "SuperDinamicK/SComuns.php";
require "SuperDinamicK/SNoticias.php";

$comuns = new SComuns();
$home = $comuns->home();
$noticias = new SNoticias(0);
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv='X-UA-Compatible' content='IE=9'>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title></title>
        <link href='http://fonts.googleapis.com/css?family=Anton' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Black+Ops+One' rel='stylesheet' type='text/css'>
        <link href="css/k_styles.css" rel="stylesheet" type="text/css"/>    
        <link href="css/fractionslider.css" type="text/css" rel="stylesheet" />
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery.fractionslider.min.js"></script>
        <script type="text/javascript">
            jQuery(window).load(function () {
                $('.slider').fractionSlider();
            });
        </script>
    </head>

    <body>
        <?php echo $comuns->top(); ?>
        <div class="bintro" id="container">
            <div class="wslider">
                <div class="slider">
                    <?php echo $home['banner']; ?>
                </div>
                <!--<div class="slide_cover"></div>-->
            </div>
            <div id="wcont" class="trsp">
                <section class='box'>
                    <h2 class='btit'>
                        <?php echo $home['defesa']['title'] ?>
                    </h2>
                    <div class="box_text">
                        <?php echo $home['defesa']['text'] ?>
                    </div>
                </section>
                <section class='box mr'>
                    <h2 class='btit'>
                        <?php echo $home['saude']['title'] ?>
                    </h2>
                    <div class="box_text">
                        <?php echo $home['saude']['text'] ?>
                    </div>
                </section>
            </div>
            <div id="wvideo" class="trsp">
                <h2 class='btit'>
                    Video em destaque
                </h2>
                <?php echo $home['video']; ?>
            </div>
            <div id="wnews" class="trsp">
                <h2 class='btit'>
                    Noticías
                </h2>
                <?php echo $noticias->front_news(3, TRUE, "h"); ?>
            </div>


        </div>
        <div id="foot">
            <div id="foot_i">
                <div id="foot_w">
                    <?php echo $comuns->foot(); ?>
                </div>
            </div>
        </div>
    </body>

</html>