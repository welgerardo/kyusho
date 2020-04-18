<?php

setlocale(LC_ALL, array('pt_PT.UTF-8', 'pt_PT@euro', 'pt_PT', 'portuguese'));

define('_L_JOBTEXT', 'Para candidatar-se, preencha o formulário com os seus dados. <br/>Não esqueça mencionar a função à qual se candidata. <br/>Caso não se enquandre em nenhuma função disponivel e deseje enviar o seu Curriculum Vitae, apenas digite CANDIDATURA no campo FUNÇÂO.');
define('_L_JOBL', 'função');
define('_L_NAME', 'nome');
define('_L_SURNAME', 'apelido');
define('_L_FONE', 'telefone');
define('_L_FAX', 'fax');
define('_L_MOBIL', 'telemóvel');
define('_L_CURRI', 'curriculum');
define('_L_MESSAG', 'mensagem');
define('_L_FORMSEND', 'enviar');
define('_L_WARNINGFORMJOB', '*Preenchimento obrigatório de todos os campos.<br>*Curriculos apenas em formato .pdf');
define('_L_WARNINGFORM', 'Preenchimento obrigatório.');
define('_L_SUBJECT', 'assunto');
define('_L_SUCESSMESS', 'A sua mensagem foi enviada com sucesso.<br><b>Por favor, aguarde o nosso contato.</b><br>Seremos breves na resposta.<br>Obrigado');
define('_L_FAILMESS', 'Não foi possível enviar a sua mensagem.<br> Por favor, tente mais tarde.');
define('_L_FAILJOBMESS', 'Não foi possível enviar a sua candidatura.<br> Por favor, tente mais tarde.');
define('_L_SUCESSJOBMESS', 'A sua candidatura foi enviada com sucesso.<br>Desde já agradecemos o seu contacto.<br>Seremos breves na resposta.');
define('_L_NWSLINVA', 'Por favor, insira um E-MAIL válido.');
define('_L_NWSLSUCESS', 'Obrigado por subscrever a nossa newsletter.');
define('_L_NWSLFAIL', 'Não foi possível subscrever a nossa newsletter.Por favor, tente mais tarde.');
define('_L_NWSLWARNNING', 'Este e-mail já subscreve a nossa newsletter.');
define('_L_CONTACTS','{"mail":"e-mail","fone":"fone","fax":"fax","movel":"telemóvel","gps":"gps"}');
define('_L_REMOVEMESS', "Subscrição da newsletter cancelada com sucesso.<br>Caso pretenda voltar a subscrever a nossa newsletter, por favor, utilize o formulário no final da página");


define('_F_NAV', '{"termos":"Termos de uso","recrutamento":"Recrutamento"}'); //menu de navegação
define('_F_TXTNEWSLETTER', 'Subscreva a nossa newsletter');
define('_F_TXTSOCIALNET', 'Siga-nos');
define('_F_TXTMAILTO', 'Fale connosco');
define('_F_TXTDEVELOP', 'desenvolvido por');
define('_F_TXTRIGHTS', 'Todos os direitos reservados');

define('_M_ANCHOR','Saiba mais sobre');

//botoões
define('_B_ORCA','Quero saber preços, sem compromisso.');
define('_B_CHAM','Quero contato comercial.');
//modulo SProdutos10
define("_PRODTEXTFORM", "Mais informações / Orçamento");
define("_OTHPRODUCTSNAME","Conheça também:");
//modulo noticias
define('_TOPICTITLE', 'Tópicos');
define('_ARCHTITLE', 'Arquivo');

//json
define('_NAV', '{"kyusho":"Kyusho","cursos":"Cursos","eventos":"Eventos","quem-somos":"Quem somos","noticias":"Notícias","contatos":"Contatos"}'); //menu de navegação

//define a tabela , os campos de pesquisa
define('produto', '{"table":"produtos010","fields":{"id":"id","name":"nome","caracteristics":"caracteristicas","description":{"topic1_pt":"text1_pt","topic2_pt":"text2_pt","topic3_pt":"text3_pt","topic4_pt":"text4_pt","topic5_pt":"text5_pt","topic6_pt":"text6_pt"},"image":"fotos_servico","intro":"introducao","video":"video"},"link_name":"produtos","link":"produtos","shortlink":"p","name":"products"}');

//define a tabela , os campos de pesquisa e o link da pagina produtos.
define('home', '{"table":"outras_paginas","fields":{"id":"id","name":"nome","title":"titulo","text":"texto","link":"link","image":"imagens","video":"video"},"link":""}');

define('_SEO', '{"table":"seo","fields":{"title":"titulo","descri":"descricao","keywords":"palavras"}}');

define('servico', '{"table":"produtos009","fields":{"id":"id","name":"nome","caracteristics":"caracteristicas","description":{"topic1_pt":"text1_pt","topic2_pt":"text2_pt","topic3_pt":"text3_pt","topic4_pt":"text4_pt","topic5_pt":"text5_pt","topic6_pt":"text6_pt"},"image":"fotos_servico","intro":"introducao","video":"video"},"link_name":"serviços","link":"servicos","shortlink":"s","name":"services"}');

define('produtos', '{"table":"produtos010","fields":{"id":"id","name":"nome","image":"fotos_servico","intro":"introducao"},"link":"produtos"}');

define('_CURSOS', '{
    "table":"workshops",
    "fields":{
        "id":"id_workshop",
        "name":"nome",
        "image":"fotos_workshop",
        "local":"local",
        "intro":"introducao"},
        "link":"pt/cursos"}');
define('_EVENTOS', '{
    "table":"portfolio",
    "fields":{
        "id":"id_portfolio",
        "name":"nome",
        "image":"fotos_portfolio",
        "intro":"apresentacao"},
        "link":"pt/eventos"}
        ');
define('_CURSO', '{
    "table": "workshops",
    "fields": {
        "id": "id_workshop",
        "name": "nome",
        "seo_description": "meta_descricao",
        "seo_title": "titulo_seo",
        "seo_keywords": "palavras_chave",
        "intro": "programa",
        "action": "call_to_action",
        "description":"descricao" ,
        "program":"programa",
        "image": "fotos_workshop",
        "video": "video",
        "o_data": {
            "local":"local",
            "formador": "formador",
            "data de inicío": "dia_inicio",
            "hora": "hora_inicio",
            "numero de horas": "numero_horas",
            "numero máximo de participantes": "max_participantes",
            "inscrições até": "data_inscricao"
        }
    },
    "link_name": "cursos",
    "link": "pt/cursos",
    "shortlink": "t",
    "name": "workshops"
}');
define('_EVENTO', '{
    "table": "portfolio",
    "fields": {
        "id": "id_portfolio",
        "name": "nome",
        "seo_description": "meta_descricao",
        "seo_title": "titulo_seo",
        "seo_keywords": "palavras_chave",
        "description":"apresentacao" ,
        "image": "fotos_portfolio",
        "video": "video",
        "o_data": ""
    },
    "link_name": "cursos",
    "link": "pt/cursos",
    "shortlink": "t",
    "name": "workshops"
}');
define('empresa', '{"table":"principal","fields":{"image":"foto","about":["quem_somos","Quem somos"],"mission":["missao","Missão"]}}');

define('_POST', '
{
    "table":"news",
    "fields":
        {
            "id":"id_noticia",
            "images":"fotos_noticia",
            "video":"video_noticias",
            "format":"formato_noticia",
            "text":"texto_noticia",
            "topic":"categoria",
            "subtitle":"subtitulo_noticia",
            "author":"autor_noticia",
            "title":"titulo_noticia",
            "date":"data_act",
            "description":"descricao_seo",
            "keywords":"palavras_seo"
        },
    "conditions":
        {
            "estado":"online"
        },
    "order":
        {
            "destaque":"DESC",
            "data_act":"DESC",
            "id_noticia":"DESC"
        },
    "link":"pt/noticias",
    "shortlink":"n"
}
');

define('_OTHERPAGES',
'
{
    "table":"outras_paginas",
    "fields":{
        "page":"pagina",
        "text":"texto",
        "title":"titulo",
        "image":"imagens"
    }
}
'
);

define('_NEWSARCH', '
{
    "table":"news",
    "fields":
        {
            "id":"id_noticia",
            "title":"titulo_noticia",
            "date":"data_act"
        },
    "conditions":
        {
            "estado":"online"
        },
    "order":
        {
            "data_act":"DESC"
        },
    "link":"pt/noticias"
}
');

define('_NEWSTOPIC', '
{
    "table":"news",
    "field":"categoria",
    "link":"pt/noticias",
    "conditions":
        {
            "estado":"online"
        },
    "order":
        {
            "categoria":"ASC"
        }
}
');
?>