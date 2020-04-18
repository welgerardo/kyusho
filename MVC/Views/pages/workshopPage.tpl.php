<!DOCTYPE html>
<!DOCTYPE HTML>
<html lang="pt">
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
                        js.src = "//connect.facebook.net/pt_PT/all.js#xfbml=1&appId={app_face}";
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));
        </script>

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
            <div class='site_nav'>
                <a href="{home-url}"><img src="{site-url}imagens/link_home_page.png" alt="home page"></a> > <a href="{home-url}/cursos">Cursos</a> > <a href={page-url}>{page-name}</a></div>
            <div class="introc">
                {image}
                <h1 class="btit bintro">{name}</h1>
                <div class="socialintro">
                                <div class='butface'>
                                    <div class='fb-like' data-href="{page-url}" data-send="true" data-width='100' data-show-faces='false'  data-layout="button_count">
                                    </div>
                                </div>
                                <div class='butgmais'>
                                    <div class='g-plusone' data-size='medium' data-annotation='none' ></div>
                                    <script type='text/javascript'>window.___gcfg = {lang: 'pt-PT'};(function () {
            var po = document.createElement('script');
            po.type = 'text/javascript';
            po.async = true;
            po.src = 'https://apis.google.com/js/plusone.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(po, s);
        })();</script>
                                </div>
                                <div class='butpin'>
                                    <a href='http://pinterest.com/pin/create/button/?url={page-url}&media={social-image}&description={social-text}' class='pin-it-button' count-layout='none'>
                                        <img border='0' src='//assets.pinterest.com/images/PinExt.png' title='Pin It' />
                                    </a>
                                </div>
                                <div class='butpin'>
                                    <a href="https://twitter.com/share?url={page-url}" class='twitter-share-button' data-lang='en' data-url='{page-url}' data-counturl='{page-url}' data-text='{social-text}' data-count='none'>Tweet</a>
                                    <script>
                                        !function (d, s, id) {
                                            var js, fjs = d.getElementsByTagName(s)[0];if (!d.getElementById(id)) {
                                                js = d.createElement(s);
                                                js.id = id;
                                                js.src = '//platform.twitter.com/widgets.js';
                                                fjs.parentNode.insertBefore(js, fjs);
                                            }
                                        }(document, 'script', 'twitter-wjs');
                                    </script>
                                </div>
                                <div class='butpin'>
                                    <script src='//platform.linkedin.com/in.js' type='text/javascript'></script>
                                    <script type='IN/Share' data-url='{page-url}'></script>
                                </div>
                            </div>
                

                <div class="introctext">
                    {intro-text}
                </div>
                <section class='box trsp introbox'>                                           
                    <h2 class='btit bintro'>
                        Dados    
                    </h2>
                    <div class='textintro'> 
                        <p class='o_p'><span class='o_title'>Local : </span><span class='o_text'>{local}</span></p>
                        <p class='o_p'><span class='o_title'>Formador : </span><span class='o_text'>{formador}</span></p>
                        <p class='o_p'><span class='o_title'>Data de inicio : </span><span class='o_text'>{inicio}</span></p>
                        <p class='o_p'><span class='o_title'>Horário : </span><span class='o_text'>{hora}</span></p>
                        <p class='o_p'><span class='o_title'>Numero de horas : </span><span class='o_text'>{numero-horas}</span></p>
                        <p class='o_p'><span class='o_title'>Numero máximo de participantes : </span><span class='o_text'>{numero-participantes}</span></p>
                        <p class='o_p'><span class='o_title'>Inscrições até: </span><span class='o_text'>{data-inscricao}</span></p>
                        <p class='o_p'><span class='o_title'>Preço: </span><span class='o_text'>{preco}</span></p>
                    </div>
                </section> 
                <section class='box trsp mr introbox'>                                           
                    <h2 class='btit bintro'>
                        Programa   
                    </h2>
                    <div class='textintro'> 
                        {programa}
                    </div>
                </section>  		
            </div>
            <div class="introc">
                <div class='btit bintro'>
                    {call-to-action}
                </div>
                <div class='formStage'>
            	<div class='bloq'></div>
                <form method='post' action='sendmessplus' lang='pt' data-type="{data-type}" data-filter="curso">
                        <table>
                            <tr>
                            <td class='formTdInput'>*Assunto: <input type='text' name='assunto'  value='{valor-assunto}' readonly></td>
                            </tr>
                            <tr>
                            <td class='formTdInput'>*Nome: <input type='text' name='nome' value=''></td>
                            </tr>
                            <tr>
                            <td class='formTdInput'>Telemóvel: <input type='text' name='tele' value=''></td>
                            </tr>
                            <tr>
                            <td class='formTdInput'>*E-mail: <input type='text' name='mail' value=''></td>
                            </tr>
                            <tr>
                            <td class='formTdText'>*Mensgem:<br><textarea name='mess'></textarea></td>
                            </tr>
                            <tr>
                            <td ><input type='submit' value="Enviar"></td>
                            </tr>
                            <tr>
                            <td class='formTdWarn'>*preenchimento obrigatório</td>
                            </tr>
                        </table>
                </form>

            </div>                
            </div>
            <div class='pe'></div>

        </div>

        <div id="foot">
            <div id="foot_i">
                <div id="foot_w">
                    <div class='foot'>
                        <div id='fo1'>
                            <a href='http://www.mgpdinamic.com' class='dev'>
                                desenvolvido por:
                                <br>
                                <img src="{site-url}imagens/desenvolvido_mgpdinamic.png" alt="{desenvolvido-por} mgpdinamic.com">
                            </a>
                            <br>
                            Todos os direitos reservados &copy;{ano} {proprietario}
                        </div>

                        <div id='fo2'>
                            <div id='newsletter'>
                                <p>Subescreva a nossa newsletter</p>
                                <form id='form1' name='form1' method='post' action='sendnews'>
                                    <div class='fnew'>
                                        <input name='news' type='text' placeholder='seu e-mail' class='inputtxt'>
                                        <input type='submit' class='inputsub' value="Enviar">
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