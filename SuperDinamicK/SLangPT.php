<?php

//CONFIGURAÇÕES
date_default_timezone_set('Europe/Lisbon');



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

//define a tabela , os campos de pesquisa e o link da pagina produtos.
define('_OP', "spOPFrontEnd");

//define a tabela , os campos de pesquisa e o link da pagina produtos.
define('home', '{"table":"outras_paginas","dados":"spOPFrontEnd","link":""}');

define('_CURSOS', '{"dados_intro":"10", "seo_id":"", "dados_workshops":"spWorkshopsFrontEnd", "link":"pt/cursos"}');
define('_CURSO', '{
    "dados": "spWorkshopDataFrontEnd",
    "fields": {
        "id": "id_workshop",
        "name": "nome",
        "seo_description": "descricao_seo",
        "seo_title": "titulo_seo",
        "seo_keywords": "palavras_seo",
        "intro": "programa",
        "action": "call_to_action",
        "description":"descricao" ,
        "image": "fotos",
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

define('_EVENTOS', '{"dados_intro":"", "seo_id":"", "dados_workshops":"spPortfoliosFrontEnd", "link":"pt/eventos"}');

define('_EVENTO', '{
    "dados": "spPortfolioDataFrontEnd",
    "fields": {
        "id": "id",
        "name": "nome",
        "seo_description": "descricao_seo",
        "seo_title": "titulo_seo",
        "seo_keywords": "palavras_seo",
        "description":"descricao" ,
        "image": "fotos",
        "video": "video",
        "o_data": ""
    },
    "link_name": "eventos",
    "link": "pt/evento",
    "shortlink": "t",
    "name": "eventos"
}');

define('_BLOG', '
{
    "table":"news",
    "home page":"spFrontNews",
    "news page":"spNewsFrontEnd",
    "news single":"spSingleNewsFrontEnd",
    "topics":"spNewsTopics",
    "archives":"spNewsArq",
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
?>