<?php

ini_set('display_errors', 0);

require_once '/var/www/vhosts/kyusho.pt/SuperDinamicK/SConfig.php';
require_once '/var/www/vhosts/kyusho.pt/SuperDinamicK/SNoticias.php';

$not = new SNoticias(3);


$seo = $not -> seo("Blog");

if (isset($_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest" && $_SERVER['REQUEST_METHOD'] === 'POST') {
    echo $not -> shownews_endless_scroll();
    exit ;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $not->lg ?>">
<head>
<meta charset="UTF-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=9" >
<title><? echo $seo['title']; ?></title>
<link href='http://fonts.googleapis.com/css?family=Anton' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Black+Ops+One' rel='stylesheet' type='text/css'>
<meta name="description" content="<? echo $seo['descri']; ?>">
<meta name="keywords" content="<? echo $seo['keywords']; ?>">
<link rel="shortcut icon" href="<?php echo _RURL; ?>imagens/favicon.ico"  type="image/x-icon">
<link rel="icon" href="<?php echo _RURL; ?>imagens/favicon.ico" type="image/x-icon">
<link href="<?php echo _RURL; ?>css/k_styles.css" rel="stylesheet" type="text/css">
<script  src="<?php echo _RURL; ?>js/jquery.js" type="text/javascript" ></script>
<script type="text/javascript" src="<?php echo _RURL; ?>js/dinamicJS.js"></script>
<script type="text/javascript">
	jQuery(window).ready(function(){
	   NEWS.init(1);
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

<?php echo $not -> top(); ?>

<div  id="container">       

	<div id="embru">
		<div id="cot">
			<?php echo $not -> shownews_endless_scroll(); ?>
		</div>

		<div id="rodape">
			<img src="<?php echo _RURL; ?>imagens/arrows32.gif" />
		</div>
	</div>

	<div id='latnews'>
			<?php	$not -> show_topics();$not -> show_archives();?>
	</div>   

    <div class='pe'></div>

</div>

<div id="foot">
        <div id="foot_i">
            <div id="foot_w">
                <?php echo $not->foot(); ?>
            </div>
        </div>
    </div>

</body>
</html>

