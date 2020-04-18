<!DOCTYPE HTML>
<html lang="{lang}">
    <head>
        <meta http-equiv='X-UA-Compatible' content='IE=9'>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{title}</title>
        <meta name="Keywords" content="{keywords}">
        <meta name="description" content="{meta-description}">
        <meta name="author" content="mgpdinamic.com/manuel gerardo pereira">
        <link href='http://fonts.googleapis.com/css?family=Anton' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Black+Ops+One' rel='stylesheet' type='text/css'>
        <link rel="shortcut icon" href="{site-url}imagens/favicon.ico"  type="image/x-icon">
        <link rel="icon" href="{site-url}imagens/favicon.ico" type="image/x-icon">
        <link href="{site-url}css/k_styles.css" rel="stylesheet" type="text/css">
        <script  src="{site-url}js/jquery.js" type="text/javascript" ></script>
        <script type="text/javascript" src="{site-url}js/dinamicJS.js"></script>
        <script type="text/javascript">
            jQuery(window).ready(function () {
                same_height();
                SENDFORMS.init();
            })
        </script>
    </head>
    <body>
        <div id="fb-root"></div>
        <script>(function (d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id))
                            return;
                        js = d.createElement(s);
                        js.id = id;
                        js.src = "//connect.facebook.net/pt_PT/all.js#xfbml=1&appId=<? echo $seo['app_face'];  ?>";
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));</script>

        <div id="wtop_container">
            <div id="top_container">
                <div id="logo">
                    <a href="{site-url}" class="alogo">{logo}</a>
                </div>
                <div id="nav">
                    <ul class="sul">
                        <li class='flir'><a class="fla {selec}" href="cursos">Cursos</a></li>
                        <li class='flir'><a class="fla {selec}" href="eventos">Eventos</a></li>
                        <li class='flir'><a class="fla {selec}" href="noticias">Noticias</a></li>
                        <li class='flir'><a class="fla {selec}" href="contatos">Contatos</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div  id="container">   
            <div class="introc">
                {intro-imagem}
                <h1>{intro-titulo}</h1>
                <div class="introctext">
                    {intro-texto}
                </div>
            </div> 
            {cursos}
            <div class='pe'></div>

        </div>

        <div id="foot">
            <div id="foot_i">
                <div id="foot_w">
                    <div class='foot'>
                        <div id='fo1'>
                            <a href='http://www.mgpdinamic.com' class='dev'>
                                {desenvolvido-por}:
                                <br>
                                <img src="{site-url}imagens/desenvolvido_mgpdinamic.png" alt="{desenvolvido-por} mgpdinamic.com">
                            </a>
                            <br>
                            {direitos}&copy;{ano} {proprietario}
                        </div>

                        <div id='fo2'>
                            <div id='newsletter'>
                                <p>{newsletter-text}</p>
                                <form id='form1' name='form1' method='post' action='sendnews'>
                                    <div class='fnew'>
                                        <input name='news' type='text' placeholder='seu e-mail' class='inputtxt'>
                                        <input type='submit' class='inputsub' value="{newsletter-input-value}">
                                    </div>
                                    <div class='newsavs'>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id='fo3'>


                        </div>

                        <div id='fo5'>
                            <div id='social'>
                                <a href='https://www.facebook.com/mgpdinamic.face'>
                                    <img src="{site-url}imagens/lface.png" alt='facebook' class='logsocial'>
                                </a>
                                <a href="https://plus.google.com/116637226690895351080" rel="publisher">
                                    <img src="{site-url}imagens/icon_google+.png" alt='google+' class='logsocial'>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>