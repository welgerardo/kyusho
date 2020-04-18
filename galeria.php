<?php

ini_set('display_errors', 1);

//set_include_path("/var/www/vhosts/kyusho.pt/");
require_once 'SuperDinamicK/DinamicSite.php';

$curso = new DinamicSite();

$c_intro= $curso -> pagina_produto($_GET['id']);

?>
<!DOCTYPE HTML>
<html lang="<?php echo $curso->get_language() ?>">
<head>
<meta charset="UTF-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=9" >
<title><? /*echo $curso->get_newstitle();*/ ?></title>
<meta name="description" content="<? /*echo $not->get_newsdescription();*/ ?>">
<meta name="keywords" content="<? /*echo $not->get_newskeywords();*/ ?>">
<link href='http://fonts.googleapis.com/css?family=Anton' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Black+Ops+One' rel='stylesheet' type='text/css'>
<link rel="shortcut icon" href="<?php echo _RURL; ?>imagens/favicon.ico"  type="image/x-icon">
<link rel="icon" href="<?php echo _RURL; ?>imagens/favicon.ico" type="image/x-icon">
<link href="<?php echo _RURL; ?>css/k_styles.css" rel="stylesheet" type="text/css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script  src="<?php echo _RURL; ?>galleria/galleria-1.3.6.min.js" type="text/javascript" ></script>
<script type="text/javascript" src="<?php echo _RURL; ?>js/dinamicJS.js"></script>
<link rel="stylesheet" href="<?php echo _RURL; ?>galleria/themes/classic/galleria.classic.css">
<script src="<?php echo _RURL; ?>galleria/themes/classic/galleria.classic.min.js"></script>
<script type="text/javascript">
	jQuery(window).ready(function(){
	   SENDFORMS.init();
	})
</script>
</head>
<body>
<script>
            if (Galleria) { $("body").text('Galleria works') }
        </script>


<?php echo $curso -> top(); ?>

<div  id="container">
    <div id="wgal"> 
        <div class='galleria'>
            <?php echo $c_intro['image']; ?>
        </div>
        <script>
            Galleria.run('.galleria');
        </script>
    <div class='pe'></div>
</div> 
</div>

<div id="foot">
        <div id="foot_i">
            <div id="foot_w">
                <?php echo $curso->foot(); ?>
            </div>
        </div>
    </div>

</body>
</html>

