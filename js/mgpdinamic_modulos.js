/* global $$, Start, OperationsBar, AddItem, Search, Folders, Edit, Clone, Keywords, File, Save, DeleteIten, OrderDisplay, Stats, Loader, Photo, Buttons, NavMenu, Contacts, Images, FilterResult, Messages, DoNewsletter, NewsletterGroups, Newsletter, UpDown, SideMenu */

// JavaScript Document
/**
 * SCRIPT
 * MGP_MODULOS V8.50.110815
 * COPYRIGHT MANUEL GERARDO PEREIRA 2015
 * TODOS OS DIREITOS RESERVADOS
 * CONTACTO: GPEREIRA@MGPDINAMIC.COM WWW.MGPDINAMIC.COM
 * 
 * cliente:
 *
 **/
BigBang = {
    ob: "",
    mURL: "http://localhost/dinamicgest/DinamicGestM/",
    saveon: function(t) {
        if (document.getElementById('save').alt === "saveon") {
            return true;
        }
        if (t) {
            return true;
        }
        else {
            return false;
        }
    },
    nule: function() {
        return false;
    },
    sairSemSalvar: function() {

        var salve = confirm('Deseja sair sem salvar?');
        if (salve) {
            OperationsBar.barra(0, 0, 0, 0, 0);
            document.getElementById('save').alt = "";
            return true;
        }
        else {
            return false;
        }
    },
    palcoum: function(hum) {

        var p = document.getElementById('palco');

        p.innerHTML = hum || "";


        return p;
    },
    palcodois: function(hdois) {
        var p = document.getElementById('palcoDois');

        p.innerHTML = hdois || "";
        $("#carrega").hide(0);

        return p;
    }

};
Produtix = {
    server: "m_produtos",
    module: "products",
    nome: "Produtos",
    icon: null,
    modulos: false,
    add_on: AddItem.add,
    search_on: Search.init,
    search_fields: {
        "toke": "Id",
        "date": "Data",
        "name": "Nome",
        "status": "Estado",
        "date_act": "Atualizado",
        "descri": "Notas"
    },
    folders_on: Folders.open_folders,
    subFolders_off: false,
    filterOptions: false,
    edit_on: Edit.init,
    copy_on: Clone.init,
    keywords_on: Keywords.init,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: DeleteIten.delet,
    order_on: OrderDisplay.init,
    arquivos_on: false,
    stats_on:Stats.init,
    start_on: Loader.load,
    starter: function() {

        Photo.init();
        Buttons.init();

        var porti = new imgallery_width_captions();
        porti.nameImage = "produto_image";
        porti.nameSubtitles = "produto_captions";
        porti.lang = ["pt","en","fr","de"];
        porti.init("midia_images");

    },
    bang: function(event) {

        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Produtix.bang(event);
        }
        else {
            BigBang.ob = Produtix;
            NavMenu.select(event);
        }
    }
};
Workix = {
    server: "m_workshops",
    module: "workshops",
    nome: "Workshops",
    icon: null,
    modulos: false,
    add_on: AddItem.add,
    search_on: Search.init,
    search_fields: {
        "name": "Nome",
        "loc":"Local",
        "form": "Formador",
        "dinicio":"Dia de ínicio",
        "data_insc":"Data de inscrição",
        "prog":"Programa",
        "descri": "Descrição",
        "status": "Estado",
        "toke": "Id",
        "date": "Data",
        "date_act": "Atualizado"
    },
    folders_on: Folders.open_folders,
    subFolders_off: false,
    filterOptions: false,
    edit_on: Edit.init,
    copy_on: false,
    keywords_on: false,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: DeleteIten.delet,
    order_on: OrderDisplay.init,
    arquivos_on: false,
    stats_on:false,
    start_on: Loader.load,
    starter: function() {

        Photo.init();
        Buttons.init();

        var porti = new imgallery_width_captions();
        porti.nameImage = "work_image";
        porti.nameSubtitles = "work_captions";
        porti.lang = ["pt"];
        porti.init("midia_images");

    },
    bang: function(event) {

        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Workix.bang(event);
        }
        else {
            BigBang.ob = Workix;
            NavMenu.select(event);
        }
    }
};
Newsix = {
    server: "m_news",
    module: "news",
    nome: "Noticias",
    icon: null,
    modulos: false,
    search_on: Search.init,
    search_fields: {
        "ide": "Id",
        "date": "Inserido",
        "date_act": "Atualizado",
        "status": "Estado",
        "category": "Categoria",
        "title": "Titulo",
        "author": "Autor",
        "subtitle": "Subtitulo"
    },
    folders_on: Folders.open_folders,
    subfolders_off: false,
    filter_result_options: false,
    add_on: AddItem.add,
    edit_on: Edit.init,
    copy_on: false,
    keywords_on: Keywords.init,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: DeleteIten.delet,
    order_on: false,
    upfiles_on: false,
    start_on: Loader.load,
    starter: function() {

        Buttons.init();

        var porti = new imgallery_width_captions();
        porti.nameImage = "news_image";
        porti.nameSubtitles = "news_captions";
        porti.lang = ["pt"];
        porti.init("midia_images");

    },
    bang: function(event) {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Newsix.bang(event);
        }
        else {
            BigBang.ob = Newsix;
            NavMenu.select(event);
        }
    }
};
Colaborix = {
    server: "m_colaboradores",
    module: "employees",
    nome: null,
    icon: null,
    modulos: {"contactos":{"module":this,"type":"contactsr","mode":"FORNEWS","flag":null}},
    search_on: Search.pesquisar,
    search_fields: {
        "id": "id",
        "nome_empresa": "nome",
        "telefone": "telefone",
        "telemovel": "telemovel",
        "mail": "e-mail",
        "web": "página internet",
        "cidade": "cidade",
        "pais": "pais",
        "ramo_actividade": "ramo de actividade",
        "sexo": "sexo",
        "pasta": "pasta"
    },
    folders_on: Folders.open_folders,
    subfolders_off: false,
    filter_result_options: false,
    add_on: Contacts.init,
    edit_on: Edit.init,
    copy_on: false,
    keywords_on: false,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: DeleteIten.delet,
    order_on: OrderDisplay.init,
    upfiles_on: false,
    start_on: Loader.load,
    starter: function() {

        var porti = new imgallery_width_captions();
        porti.nameImage = "foto_contato";
        porti.nameSubtitles = "contato_captions";
        porti.lang = ["pt"];
        porti.init("midia_image");

    },
    bang: function() {
        if (BigBang.saveon())
        {
            if (BigBang.sairSemSalvar())
                Colaborix.bang();
        }
        else
        {
            BigBang.ob = Colaborix;
            Colaborix.start_on();
        }
    }
};
Clientix = {
    server: "m_clientes",
    module: "client",
    nome: null,
    icon: null,
    modulos: {"contactos":{"module":this,"type":"contactsr","mode":"FORNEWS","flag":null}},
    search_on: Search.pesquisar,
    search_fields: {
        "id": "id",
        "nome_empresa": "nome",
        "telefone": "telefone",
        "telemovel": "telemovel",
        "mail": "e-mail",
        "web": "página internet",
        "cidade": "cidade",
        "pais": "pais",
        "ramo_actividade": "ramo de actividade",
        "sexo": "sexo",
        "pasta": "pasta"
    },
    folders_on: Folders.open_folders,
    subfolders_off: false,
    filter_result_options: false,
    add_on: Contacts.init,
    edit_on: Edit.init,
    copy_on: false,
    keywords_on: false,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: DeleteIten.delet,
    order_on: false,
    upfiles_on: false,
    start_on: Loader.load,
    starter: function() {

        var porti = new imgallery_width_captions();
        porti.nameImage = "foto_contato";
        porti.nameSubtitles = "contato_captions";
        porti.lang = ["pt"];
        porti.init("midia_image");

    },
    bang: function() {
        if (BigBang.saveon())
        {
            if (BigBang.sairSemSalvar())
                Clientix.bang();
        }
        else
        {
            BigBang.ob = Clientix;
            Clientix.start_on();
        }
    }
};
Firmix = {
    server: "m_empresa",
    module: "company",
    nome: null,
    icon: null,
    modulos: false,
    search_on: false,
    search_fields: false,
    folders_on: false,
    subfolders_off: false,
    filter_result_options: false,
    add_on: false,
    edit_on: Edit.init,
    copy_on: false,
    keywords_on: false,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: false,
    order_on: false,
    upfiles_on: false,
    start_on: Loader.load_without_folders,
    starter: function() {

        var logo = new imgallery_width_captions();
        logo.nameImage = "logo_image";
        logo.nameSubtitles = "logo_captions";
        logo.lang = ["pt"];
        logo.init("midia_logo");

        var comp = new imgallery_width_captions();
        comp.nameImage = "comp_image";
        comp.nameSubtitles = "comp_captions";
        comp.lang = ["pt"];
        comp.init("midia_company_images");

        var cont = new imgallery_width_captions();
        cont.nameImage = "cont_image";
        cont.nameSubtitles = "cont_captions";
        cont.lang = ["pt"];
        cont.init("midia_contact_images");


    },
    bang: function(event) {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Firmix.bang(event);
        }
        else {
            BigBang.ob = Firmix;
            Firmix.start_on();
        }
    }
};
Empresix = {
    server: null,
    module: null,
    nome: "Empresa",
    icon: null,
    submenu_on: NavMenu.sub_menu,
    submenu_options: {"Dados da empresa": Firmix.bang, "Colaboradores": Colaborix.bang,"Clientes":Clientix.bang},
    start_on: Loader.load,
    bang: function(event) {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Empresix.bang(event);
        }
        else {
            BigBang.ob = Empresix;
            NavMenu.select(event);
        }
    }
};
Imagix = {
    server: "m_imagens",
    module: "images",
    nome: "Imagens",
    icon: null,
    modulos: false,
    folders_on: Images.init,
    subfolders_off: Images.open_images,
    file_on: false,
    upfiles_on: Images.prepare_upload,
    add_on: AddItem.add,
    edit_on: false,
    copy_on: false,
    save_on: false,
    delete_on: false,
    search_on: false,
    search_fields: null,
    search_handler: false,
    filter_result_options: null,
    keywords_on: false,
    order_on: false,
    start_on: Loader.load,
    bang: function(event) {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Imagix.bang(event);
        }
        else {
            BigBang.ob = Imagix;
            NavMenu.select(event);
        }
    }
};
Contactix = {
    server: "m_contatos",
    module: "contacts",
    nome: "Contactos",
    icon: null,
    modulos:  {"contactos":{"module":this,"type":"contactsr","mode":"FORNEWS","flag":null}},
    search_on: Search.init,
    search_fields: {
        "name": "Nome",
        "mail": "E-mail",
        "phone": "Telefone",
        "cell1": "Telemóvel 1",
        "nif": "NIF",
        "country": "Pais",
        "state": "Distrito",
        "city": "Cidade",
        "village": "Freguesia",
        "postal": "Código postal",
        "webpage": "Página internet",
        "date": "Inserido",
        "date_act": "Atualizado"
    },
    search_handler: false,
    folders_on: FilterResult.init,
    subfolders_off: false,
    filter_result_options: {"pessoas":"pessoas", "empresas":"empresas"},
    add_on: Contacts.init,
    edit_on: Edit.init,
    copy_on: false,
    keywords_on: false,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: DeleteIten.delet,
    order_on: false,
    upfiles_on: false,
    stats_on:Stats.init,
    start_on: Loader.load,
    starter: function() {

        var porti = new imgallery_width_captions();
        porti.nameImage = "foto_contato";
        porti.nameSubtitles = null;
        porti.lang = null;
        porti.init("midia_image");

    },
    bang: function(event) {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Contactix.bang(event);
        }
        else {
            BigBang.ob = Contactix;
            NavMenu.select(event);
        }
    }
};
Mensagix = {
    server: "m_mensagens",
    module: "messages",
    nome: "Mensagens",
    icon: null,
    modulos: false,
    search_on: Search.init,
    search_fields: {
        "date": "Data",
        "subject": "Assunto",
        "mail": "E-mail",
        "name": "Nome"
    },
    search_handler: Messages.mess_open_handler,
    folders_on: Messages.init,
    subfolders_off: Messages.mess_open,
    filter_result_options: false,
    add_on: false,
    edit_on: false,
    copy_on: false,
    keywords_on: false,
    file_on: false,
    save_on: false,
    delete_on: false,
    order_on: false,
    upfiles_on: false,
    start_on: Loader.load,
    bang: function(event) {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Mensagix.bang(event);
        }
        else {
            BigBang.ob = Mensagix;
            NavMenu.select(event);
        }
    }
};
MakeNewsletrix = {
    server: "m_newsletter",
    module: "make_newsletter",
    nome: null,
    icon: null,
    modulos: {"noticias":{"module":Newsix,"type":"newsl","mode":"FORNEWS","flag":null},"produtos":{"module":Produtix,"mode":"FORNEWS","type":"newsl","flag":null}},
    search_on: false,
    search_fields: false,
    folders_on: Folders.open_folders,
    subfolders_off: false,
    filter_result_options: false,
    add_on: DoNewsletter.init,
    edit_on: false,
    copy_on: false,
    keywords_on: false,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: DeleteIten.delet,
    order_on: false,
    upfiles_on: false,
    start_on: Loader.load,
    starter: function() {

    },
    bang: function() {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                MakeNewsletrix.bang();
        }
        else {
            BigBang.ob = MakeNewsletrix;
            MakeNewsletrix.start_on();
        }
    }
};
GroupsNewsletrix = {
    server: "m_grupo",
    module: "groups",
    nome: null,
    icon: null,
    modulos: false,
    search_on: false,
    search_fields: false,
    folders_on: Folders.open_folders,
    subfolders_off: false,
    filter_result_options: false,
    add_on: NewsletterGroups.init,
    edit_on: Edit.init,
    copy_on: false,
    keywords_on: false,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: DeleteIten.delet,
    order_on: false,
    upfiles_on: false,
    start_on: Loader.load,
    starter: function() {

        NewsletterGroups.init_edit();
    },
    bang: function() {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                GroupsNewsletrix.bang();
        }
        else {
            BigBang.ob = GroupsNewsletrix;
            GroupsNewsletrix.start_on();
        }
    }
};
ConfigNewsletrix = {
    server: "m_newsletterConfig",
    module: "config_newsletter",
    nome: null,
    icon: null,
    modulos: false,
    search_on: false,
    search_fields: false,
    folders_on: false,
    subfolders_off: false,
    filter_result_options: false,
    add_on: false,
    edit_on: Edit.init,
    copy_on: false,
    keywords_on: false,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: false,
    order_on: false,
    upfiles_on: false,
    start_on: Loader.load_without_folders,
    starter: function() {

        var porti = new imgallery_width_captions();
        porti.nameImage = "newsletter_image";
        porti.nameSubtitles = "newsletter_captions";
        porti.lang = ["pt", "en"];
        porti.init("midia_images");

    },
    bang: function() {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                ConfigNewsletrix.bang();
        }
        else {
            BigBang.ob = ConfigNewsletrix;
            ConfigNewsletrix.start_on();
        }
    }
};
SendNewsletrix = {
    server: "m_newsletter",
    module: "send_newsletter",
    icon: null,
    nome: null,
    search_on: false,
    search_fields: false,
    folders_on: false,
    subfolders_off: false,
    filter_result_options: false,
    add_on: false,
    edit_on: false,
    copy_on: false,
    keywords_on: false,
    file_on: Newsletter.init,
    save_on: false,
    delete_on: false,
    order_on: false,
    upfiles_on: false,
    start_on: Loader.load_without_folders,
    bang: function() {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                SendNewsletrix.bang();
        }
        else {
            BigBang.ob = SendNewsletrix;
            SendNewsletrix.start_on();
        }
    }
};
Newsletrix = {
    server: null,
    module: null,
    nome: "Newsletter",
    icon: null,
    submenu_on: NavMenu.sub_menu,
    submenu_options: {"Newsletters": MakeNewsletrix.bang, "Grupos de envio": GroupsNewsletrix.bang, "Enviar": SendNewsletrix.bang, "Configurações": ConfigNewsletrix.bang},
    start_on: Loader.load,
    bang: function(event) {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Newsletrix.bang(event);
        }
        else {
            BigBang.ob = Newsletrix;
            NavMenu.select(event);
        }
    }
};
Recrutix = {
    server: "m_pages",
    module: "recrutamento",
    nome: null,
    icon: null,
    modulos: null,
    search_on: false,
    search_fields: false,
    folders_on: Folders.open_folders,
    subfolders_off: false,
    filter_result_options: false,
    add_on: false,
    edit_on: Edit.init,
    copy_on: false,
    keywords_on: false,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: false,
    order_on: false,
    upfiles_on: false,
    start_on: Loader.load,
    starter: function() {
    },
    bang: function(event) {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Recrutix.bang();
        }
        else {
            BigBang.ob = Recrutix;
            Recrutix.start_on(event);
        }
    }
};
Privatix = {
    server: "m_pages",
    module: "privacidade",
    nome: null,
    modulos: false,
    search_on: false,
    search_fields: false,
    folders_on: Folders.open_folders,
    subfolders_off: false,
    filter_result_options: false,
    add_on: false,
    edit_on: Edit.init,
    copy_on: false,
    keywords_on: false,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: false,
    order_on: false,
    upfiles_on: false,
    start_on: Loader.load,
    starter: function() {
    },
    bang: function(event) {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Privatix.bang();
        }
        else {
            BigBang.ob = Privatix;
            Privatix.start_on(event);
        }
    }
};
Homix = {
    server: "m_pages",
    module: "home",
    nome: null,
    modulos: false,
    search_on: false,
    search_fields: false,
    folders_on: Folders.open_folders,
    subfolders_off: false,
    filter_result_options: false,
    add_on: false,
    edit_on: Edit.init,
    copy_on: false,
    keywords_on: false,
    file_on: File.show,
    save_on: Save.saver,
    delete_on: false,
    order_on: false,
    upfiles_on: false,
    start_on: Loader.load,
    starter: function() {
        Buttons.init();

        if (document.getElementById("pt_image")) {

            var porti = new imgallery_width_captions();
            porti.nameImage = "image_pt";
            porti.nameSubtitles = "captions_pt";
            porti.lang = ["pt"];
            porti.init("pt_image");


            var portie = new imgallery_width_captions();
            portie.nameImage = "image_en";
            portie.nameSubtitles = "captions_en";
            portie.lang = ["pt"];
            portie.init("en_image");

        }

        if (document.getElementById("banner_pt")) {

            var porti = new imgallery_width_captions();
            porti.nameImage = "banner_pt";
            porti.nameSubtitles = "bcaptions_pt";
            porti.lang = ["pt"];
            porti.init("banner_pt");

        }

         if (document.getElementById("midia_image")) {

            var porti = new imgallery_width_captions();
            porti.nameImage = "image_pt";
            porti.nameSubtitles = "captions_pt";
            porti.lang = ["pt","en","fr","de"];
            porti.init("midia_image");

        }

    },
    bang: function() {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Homix.bang();
        }
        else {
            BigBang.ob = Homix;
            Homix.start_on();
        }
    }
};
OP = {
    server: null,
    module: null,
    nome: "Site",
    icon: null,
    submenu_on: NavMenu.sub_menu,
    submenu_options: {"Home page": Homix.bang, "Recrutamento": Recrutix.bang, "Privacidade": Privatix.bang},
    start_on: Loader.load,
    bang: function(event) {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                OP.bang(event);
        }
        else {
            BigBang.ob = OP;
            NavMenu.select(event);
        }
    }
};
Redix = {
    server: "m_redes",
    module: "network",
    nome: "Redes sociais",
    icon: null,
    modulos: false,
    search_on: false,
    search_fields: false,
    folders_on: Folders.open_folders,
    subfolders_off: false,
    filter_result_options: false,
    add_on: false,
    edit_on: Edit.init,
    copy_on:false,
    keywords_on:false,
    save_on: Save.saver,
    file_on: File.show,
    delete_on: false,
    order_on:false,
    upfiles_on:false,
    start_on: Loader.load,
    starter: function() {

if (document.getElementById("config_image")) {
        var porti = new imgallery_width_captions();
        porti.nameImage = "logo_config";
        porti.nameSubtitles = "";
        porti.lang = null;
        porti.init("config_image");
        }
    },
    bang: function(event) {
        if (BigBang.saveon()) {
            if (BigBang.sairSemSalvar())
                Redix.bang();
        }
        else {
            BigBang.ob = Redix;
            NavMenu.select(event);
        }
    }
};
FirstBang = {
    botoes: ["imagens/adicionar21.png", "imagens/editar21.png", "imagens/salvar21.png", "imagens/adicionar26.png", "imagens/copiar.png"],
    modulos: [OP, Workix, Produtix, Newsix, Empresix, Imagix, Contactix,  Mensagix, Newsletrix,  Redix],
    init: function() {
    	window.scrollTo(0,0);
        
        $("body").css("background-image", "none");
        
        var c = $$.el("mainContainer");

        c.innerHTML = "";

        $$.make("div", {"id": "zomm"}, c, null, null);
        $$.make("div", {"id": "blk"}, c, null, null);
        var m = $$.make("div", {"id": "opMenu"}, c, null, null);
        $$.make("div", {"id": "logoMgp"}, m, null, null);
        $$.make("img", {"src": "imagens/lixeira.png", "id": "lx", "data-type": "menu,keywords"}, m, null, null);

        var b = $$.make("div", {"id": "botoes"}, m, null, null);

        $$.make("img", {"src": this.botoes[0]}, b, null, null);
        $$.make("img", {"src": this.botoes[1]}, b, null, null);
        $$.make("img", {"src": this.botoes[2], "id": "save"}, b, null, null);
        $$.make("img", {"src": this.botoes[3]}, b, null, null);
        $$.make("img", {"src": this.botoes[4]}, b, null, null);

        var n = $$.make("div", {"id": "navMenu"}, c, null, null);
        $$.make("div", {"id": "we"}, n, null, null);
        var w = $$.make("div", {"id": "wNavMenu"}, n, null, null);


        var u = $$.make("ul", null, w, null, null);
        for (var x = 0; x < this.modulos.length; x++) {
            var li = $$.make("li", {"class": "mnavli"}, u, null, {"click": this.modulos[x].bang});
            $$.make("img", {"src": this.modulos[x].icon, "draggable": "false"}, li, null, null);
            $$.text(li, this.modulos[x].nome);
        }

        $$.make("div", {"id": "wd"}, n, null, null);

        var wsm = $$.make("div", {"id": "msubMenu","class":"submenu"}, c, null, {"dragenter": UpDown.up, "dragleave": UpDown.stop});
        $$.make("div", {"id": "subMenu"}, wsm, null, null);
        $$.make("img", {"src": "imagens/22.gif", "id": "iloader"}, wsm, null, null);

        $$.make("div", {"id": "palcoDois"}, c, null, null);

        var d = $$.make("div", {"id": "working_area"}, c, null, null);
        $$.make("div", {"id": "palco"}, d, null, null);

        var ft = $$.make("div", {"id": "footer"}, c, null, {"dragenter": UpDown.down, "dragleave": UpDown.stop});
        SideMenu.init();
        NavMenu.init();

    }


},
jQuery(document).ready(function() {
    $$.events($$.el("Submit3"), "click", Start.init);
});

