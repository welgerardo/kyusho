<?php

ini_set('display_errors', 1);
//set_include_path("/var/www/vhosts/kyusho.pt/");
require_once 'SuperDinamicK/DinamicSite.php';

$curso = new DinamicSite();

$c_intro = $curso -> pagina_produto($_GET['id']);
?>
<!DOCTYPE html>
<html lang="<?php echo $curso->get_language() ?>">
<head>
<meta charset="UTF-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=9" >
<title><?php echo $c_intro['seo_title']; ?></title>
<meta name="description" content="<?php /*echo $not->get_newsdescription();*/ ?>">
<meta name="keywords" content="<?php /*echo $not->get_newskeywords();*/ ?>">
<link href='http://fonts.googleapis.com/css?family=Anton' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Black+Ops+One' rel='stylesheet' type='text/css'>
<link rel="shortcut icon" href="<?php echo _RURL; ?>imagens/favicon.ico"  type="image/x-icon">
<link rel="icon" href="<?php echo _RURL; ?>imagens/favicon.ico" type="image/x-icon">
<link href="<?php echo _RURL; ?>css/k_styles.css" rel="stylesheet" type="text/css">
<script  src="<?php echo _RURL; ?>js/jquery.js" type="text/javascript" ></script>
<script type="text/javascript" src="<?php echo _RURL; ?>js/dinamicJS.js"></script>
<script type="text/javascript">
	jQuery(window).ready(function() {
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
  js.src = "//connect.facebook.net/pt_PT/all.js#xfbml=1&appId=<?php echo $seo['app_face']; ?>
	";
	fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
</script>

<?php echo $curso -> top(); ?>

<div  id="container">       
    <?php echo $c_intro['map']; ?>
	<div class="introc">
		<?php echo $c_intro['image']; ?>
		<h1 class="btit bintro"><?php echo $c_intro['nome']; ?></h1>
		<div class="socialintro">
		    <?php echo $c_intro['social']; ?>
		</div>
		
		<div class="introctext">
			<?php echo $c_intro['text']; ?>
		</div>
    <section class='box trsp introbox'>                                           
        <h2 class='btit bintro'>
            Dados    
        </h2>
        <div class='textintro'> 
            <?php echo $c_intro['data']; ?>
        </div>
    </section> 
    <section class='box trsp mr introbox'>                                           
        <h2 class='btit bintro'>
            Programa   
        </h2>
        <div class='textintro'> 
            <?php echo $c_intro['intro']; ?>
        </div>
    </section>  		
	</div>
<div class="introc">
    <div class='btit bintro'>
            <?php echo $c_intro['action']; ?>
    </div>
    <?php echo $c_intro['form']; ?>
</div>
    <div class='pe'></div>

</div>

<div id="foot">
        <div id="foot_i">
            <div id="foot_w">
                <?php echo $curso -> foot(); ?>
            </div>
        </div>
    </div>

</body>
</html>

