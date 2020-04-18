<?php

ini_set('display_errors', 1);

//set_include_path("/var/www/vhosts/kyusho.pt/");
require_once 'SuperDinamicK/DinamicSite.php';

$eventos = new DinamicSite();

$eventos_data= $eventos->workshops("gal");
$seo = $eventos->seo(13);

?>
<!DOCTYPE html>
<html lang="<?php echo $eventos->get_language() ?>">
<head>
<meta charset="UTF-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=9" >
<title><?php echo $seo['title']; ?></title>
<meta name="description" content="<?php echo $seo['descri'] ?>">
<meta name="keywords" content="<?php echo $seo['keywords'] ?>">
<link href='http://fonts.googleapis.com/css?family=Anton' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Black+Ops+One' rel='stylesheet' type='text/css'>
<link rel="shortcut icon" href="<?php echo _RURL; ?>imagens/favicon.ico"  type="image/x-icon">
<link rel="icon" href="<?php echo _RURL; ?>imagens/favicon.ico" type="image/x-icon">
<link href="<?php echo _RURL; ?>css/k_styles.css" rel="stylesheet" type="text/css">
<script  src="<?php echo _RURL; ?>js/jquery.js" type="text/javascript" ></script>
<script type="text/javascript" src="<?php echo _RURL; ?>js/dinamicJS.js"></script>
<script type="text/javascript">
	jQuery(window).ready(function(){
	   same_height();
	   SENDFORMS.init();
	})
</script>
</head>
<body>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/pt_PT/all.js#xfbml=1&appId=<? echo $seo['app_face'];  ?>";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<?php echo $eventos -> top(); ?>

<div  id="container">
    <div id="wgal"> 
<?php echo $eventos_data['data']; ?>
    <div class='pe'></div>
</div> 
</div>

<div id="foot">
        <div id="foot_i">
            <div id="foot_w">
                <?php echo $eventos->foot(); ?>
            </div>
        </div>
    </div>

</body>
</html>

