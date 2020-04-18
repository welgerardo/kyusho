<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//CONFIGURAÇÕES
date_default_timezone_set('Europe/Lisbon');

//GLOBAIS
$NOW = date("YmdHis");
$WHITELIST = array("_CURSO", "_CURSOS", "_EVENTOS", "_EVENTO", "blog", "_POST","contatos","portfolio");
$FILTERLIST = array("services","products");//lista de filtros de mensagens
$LANGPAGES = array("pt", "en");

//DADOS
define('_HT','localhost');
//define('_DB','comercmgp211com17739_kyusho');
//define('_US', 'comer_mainuserk');
//define('_PS', 'q@lcF163');
define('_DB','kyushomgpdinamic');//dinamicshopdinamicgest
define('_US', 'root');//
define('_PS', '');//
define('_NOMESITE', 'kyusho.pt');
define('_CONTCIMG', '"imagens/ftCont.png"'); //imagem por defeito de um contato
define('_CONTCFOLDER', 'Contactos via site'); //pasta por defeito de um contato oriundo do site
//CAMINHOS
define('_ANX', '/var/www/vhosts/kyusho.pt/httpdocs/anexos/');
//pasta para gravar anexos enviados do site.
//TABELAS
define("_NEWSTB", "news"); //tabela do modulos noticias
define("_CONFIGTB", "config"); //tabela de configurações
define("_CONTCTB", "contactos"); //tabela que guarda os contatos
define("_EMPTB", "principal"); //tabela que guarda os dados da empresa
define("_MESSTB", "mensagens"); //tabela que guarda as mensagens
//URL
//define('_RURL', 'http://kyusho.pt/');
define('_RURL', 'http://localhost/kyusho/');
define('_SRURL', 'http://kyusho.pt/');
define("_PUREURL", "http://kyusho.pt");
define("_IMGURL", _RURL."galeria");;
define("_VIDEOURL", _RURL."videos/");
define("_ANEURL", _RURL."anexos");

//JSON
define('_MESSFIELDS', '{"nome":"nome","apelido":"apelido","morada":"rua","freguesia":"freguesia","cidade":"cidade","pais":"pais","codigo_postal":"c_postal","mail":"mail","fone":"telefone","movel1":"telemovel","sexo":"sexo","data_nascimento":"data_nascimento"}');
?>
