/********************************************
 *COPYRIGHT MANUEL GERARDO PEREIRA 2013
 *TODOS OS DIREITOS RESERVADOS
 *CONTACTO: GPEREIRA@MGPDINAMIC.COM
 *WWW.MGPDINAMIC.COM 
 *******************************************/
var RURL = "http://localhost/kyusho/MVC/"
$$ = {
    make: function(e, j, o, t, ev) {

        var el, key, txt, n;

        switch (e) {

            case "tr":
                el = o.insertRow(-1);
                o = null;
                break;
            case "td":
                el = o.insertCell(-1);
                o = null;
                break;
            default:
                el = document.createElement(e);
                break;
        }

        if (j) {
            for (key in j) {
                if (key === "class") {
                    el.className = j[key];
                } else {
                    el.setAttribute(key, j[key]);
                }
            }
        }

        if (o) {
            o.appendChild(el);
        }

        if (t) {
            txt = document.createTextNode(t);
            el.appendChild(txt);
        }

        if (ev) {
            for (n in ev) {
                try {
                    el.attachEvent("on" + n, ev[n]);
                } catch (e) {
                    el.addEventListener(n, ev[n], false);
                }
            }
        }
        return el;
    },
    evento: function(o, v, f) {

        try {
            o.attachEvent("on" + v, f);

        } catch (e) {
            o.addEventListener(v, f, false);
        }
    },
    target: function(ev) {

        var e = null;

        if (ev) {
            e = (ev.srcElement) ? window.event.srcElement : ev.target;

        } else {
            e = null;
        }

        return e;
    }
};
SENDFORMS = {
    result: null,
    mode: null,
    start: function() {
        var a, b, c, d;

        a = document.getElementsByTagName("div");


        b = a.length;
        while (b--) {

            if (a[b].getAttribute("data-type") && a[b].getAttribute("data-type") === "mail") {

                $$.evento(a[b], "click", function() {
                    document.getElementById("zomm").style.display = "block";
                    SENDFORMS.init();
                })
            }

        }
        d = document.getElementById("closezomm");
        if (d){
            $$.evento(document.getElementById("closezomm"), "click", SENDFORMS.close_zomm);}
    },
    close_zomm: function() {

        var a, b, c, d;

        a = document.getElementById("zomm");
        b = a.getElementsByTagName("form");


        document.getElementById("zomm").style.display = "none";
        b[0].reset();

        $(".bloq").hide();
        $(".bloq").html("");
        $(".bloq").css({"opacity": "0.5"});
        $(".formTdInput").css({"border": "solid 1px #888"});
        $(".formTdText").css({"border": "solid 1px #888"});
    },
    init: function() {

        var f, l;
        f = document.forms;
        l = f.length;
      
        while (l--) {
            
            if (f[l].getAttribute("enctype") !== 'multipart/form-data') {

                $$.evento(f[l], "submit", SENDFORMS.send);

            }
        }

        return false;

    },
    send: function(event) {

        var a, t, d, sting, f, g;

        t = $$.target(event);

        try {
            event.preventDefault();
        } catch (y) {
            event.returnValue = false;
        }



        d = t.action.slice(t.action.lastIndexOf("/") + 1);
        g = t.getAttribute("data-type");
        f = t.getAttribute("data-filter");
        a = RURL + d;

        sting = "flag=" + d + "&type=" + g + "&filter=" + f + "&" + $(t).serialize();

        if (t.parentNode.className === "formStage" && $(".bloq")) {

            $(".bloq").show();
        }

        switch (d) {

            case "search":
                SENDFORMS.sendsearch(sting);
                break;
            case "sendmess":
            case "sendmessplus":
            case "sendfriend":
            case "sendnews":
            case "sendremove":
                $.ajax({
                    type: "POST",
                    url: a,
                    cache: false,
                    data: sting,
                    dataType: "json",
                    success: function(dat) {
                        switch (d) {
                            case "sendmess":
                            case "sendmessplus":
                            case "sendfriend":
                                SENDFORMS.sendmess(dat, t);
                                break;
                            case "sendnews":
                                SENDFORMS.sendnews(dat, t);
                                break;
                        }
                    }
                });
                break;
            default:
                t.submit();
                break;
        }

        return false;

    },
    sendmess: function(dat, t) {

        var td, x, y;
        //atenção não tem tag P dentro do da tag FORM
        td = t.children[0].getElementsByTagName("td");

        if (dat.error) {
            for (x = 0; x < dat.error.length; x++) {
                (dat.error[x]) ? td[x].style.border = "solid 1px red" : td[x].style.border = "solid 1px #888";
                $(".bloq").hide();
            }

        }

        if (dat.result) {
            $(".bloq").css({"opacity": "1"});
            $(".bloq").html("<div class='resp'>" + dat.result + "</div>");


        }
    },
    sendnews: function(dat, t) {
        t.children[1].innerHTML = dat.result;
    },
    read: function() {

        var td, x;

        td = document.getElementsByTagName("table")[0].getElementsByTagName("td");

        if (SENDFORMS.result.error) {
            for (x = 0; x < SENDFORMS.result.error.length; x++) {

                (SENDFORMS.result.error[x]) ? td[x].style.borderColor = "red" : td[x].style.borderColor = "#888";
            }
        }

        if (SENDFORMS.result.result) {

            $(".bloq").html("<div class='resp'>" + SENDFORMS.result.result + "</div>");
        }

    },
    sendsearch: function(d) {

        $.ajax({
            type: "POST",
            url: "imoveis",
            cache: false,
            data: d,
            success: function(ht) {

                $("#principal").html(ht);
            },
            complete: function() {
                SENDFORMS.init();
            }
        });
    }
};
NEWS = {
    xr: null,
    max: 0,
    initPager: function() {
        
        //carrega uma nova noticia
        if (!NEWS.xr) {

            var a = window.location.href;
            var p = $(".noticia").length;

            NEWS.xr = $.ajax({
                type: "POST",
                url: a,
                data: "page=" + p,
                cache: false,
                success: function(ht) {
                    if (ht) {
                        $("#cot").append(ht);
                        twttr.widgets.load();
                        try {
                            FB.XFBML.parse();
                        } catch (e) {

                        }
                    }
                },
                complete: function() {

                    $("#rodape img").css("display", "none");
                    NEWS.xr = null;
                }
            });
        }
        return false;
    },
    //verifica a posição da div rodape

    loader: function(e) {

        var p = document.getElementById("cot");

        // ie8 e anteriores não suportam window.pageYOffset.
        // mede a altura do scroll vertical
        var scrll = (window.pageYOffset) ? window.pageYOffset : (document.documentElement.scrollTop);

        //$(window).height() para ie8 e anteriores
        //mede o espaço vertical disponivel no browser.(altura vertical da tela que renderiza o site)

        var dh = (window.innerHeight) ? window.innerHeight : ($(window).height()) - 200;
        var sco = (scrll + dh);
        if ((sco > $("#rodape").offset().top) && (sco > NEWS.max)) {

            NEWS.max = ((scrll + dh) > NEWS.max) ? (scrll + dh) : NEWS.max;
            $("#rodape img").css("display", "inline");

            NEWS.initPager();

        }
    },
    //inicia o arquivo de noticias

    initArchive: function() {

        var main, ul, f, lif, x;

        main = document.getElementById("news_archive");

        ul = main.getElementsByTagName("ul");

        ul[0].children[0].children[0].style.display = "block";

        ul[0].children[0].children[0].children[0].children[0].style.display = "block";

        for (f = 0; f < ul.length; f++) {

            if (ul[f].className === "newsyear") {

                lif = ul[f];

                for (x = 0; x < lif.children.length; x++) {

                    $$.evento(lif.children[x], "click", this.openArchive);

                }
            }
        }
    },
    //abre as listas do arquivo de noticias

    openArchive: function(e) {



        if ($$.target(e).children[0].style.display == "none" || $$.target(e).children[0].style.display == "") {

            $$.target(e).children[0].style.display = "block";

        } else {

            $$.target(e).children[0].style.display = "none";

        }

    },
    //inicia o objecto

    init: function(sw) {
        //$(window).height() para ie8 e anteriores
        if (sw) {
            var mh = (window.innerHeight) ? window.innerHeight : ($(window).height()) - 200;
            
            $("#embru").css({
                "min-height": mh + "px"
            });

            window.onscroll = NEWS.loader;

        }
        
        this.initArchive();
    }

};
HOME = {
    init: function() {

        if (!!document.createElement("canvas").getContext) {

            var a, b, c, d, f, h, i, m, n, p, w, x = 0;

            a = document.getElementsByTagName("canvas");

            for (x; x < a.length; x++) {

                if (x % 2 === 0) {
                    m = -5;
                    n = 5;
                    p = 35;
                } else {
                    m = 5;
                    n = 18;
                    p = 5;
                }
                var img = a[x].children[0];

                if (img) {

                    i = a[x].width - 50;

                    b = a[x].getContext("2d");

                    imgx = new Image();
                    imgx.src = img.src;
                    w = (imgx.width / imgx.height);
                    c = i / w.toFixed(2);
                    //a[x].height=c+50;




                    b.translate(n, p);
                    b.rotate(m * Math.PI / 180);


                    b.drawImage(imgx, 10, 10, i.toFixed(2), c.toFixed(2));









                }


            }




        } else {
            alert("não suporta canvas");
        }
    }
};
IMGGALLERY = {
    init: function(cn) {

        var a, b, c, d,f,g;

        a = document.getElementsByClassName(cn);
        d = document.getElementById("curtain");
        f = $(".prod_lat2 img");

        c = a.length;
        for (b = 0; b < c; b++) {

            $$.evento(a[b], "click", IMGGALLERY.load_img);

        }
        
        for(g = 0; g < f.length; g++){
            
            $$.evento(f[g], "click", IMGGALLERY.load_text);
        }

        $$.evento(d, "click", IMGGALLERY.close_curtain);

    },
    load_img: function(event) {

        var a, b, c;

        a = document.getElementById("curtain");

        b = $$.make("div", {
            "class": "curtain_dis"
        }, a);

        c = $$.make("div", {
            "class": "curwimg"
        }, b);

        $$.make("div", {
            "id": "curtain_closer"
        }, c, "x", {"click": IMGGALLERY.close_curtain});

        $$.make(
                "img",
                {
                    "class": "",
                    "src": $$.target(event).src
                },
        c

                );

        a.style.display = "block";
    },
    load_text: function(event){
        
        var a, b, c, d;
        
        d = $$.target(event).getAttribute("data-type");
        
        $.ajax({
                    type: "POST",
                    url: RURL + d ,
                    cache: false,
                    data: "",
                    success: function(dat) {
                        
                        a = document.getElementById("curtain");

                        b = $$.make("div", {
                            "class": "curtain_dis"
                        }, a);

                        c = $$.make("div", {
                            "class": "curwimg"
                        }, b);

                        $$.make("div", {
                            "id": "curtain_closer",
                            "class":"blue"
                        }, c, "x", {"click": IMGGALLERY.close_curtain}).style.backgroundColor="#125";

                        $$.make(
                                "div",
                                {
                                    "class": "curtaintext",
                                },
                        c

                                ). innerHTML = dat;

                        a.style.display = "block";
                    }
                });
        
    },
    close_curtain: function(event) {
        var a, b;

        a = $$.target(event);

        b = document.getElementById("curtain");

        if (a.id === "curtain_closer") {
            b.innerHTML = "";
            b.style.display = "none";
        }
    }
};
MENU = {
    
    init:function(){
        
        var a,b;
        
        a = document.getElementsByClassName("nav1")[0].children[0];
        
        for(b=0;b<a.children.length;b++){
            
            if(b==1){
               $$.evento(a.children[b],"mouseover",MENU.display); 
            } else{
                $$.evento(a.children[b],"mouseover",MENU.hide); 
            }
        }
        
        if(document.getElementById("wrap_home"))
        $$.evento(document.getElementById("wrap_home"),"mouseout",MENU.hide);
    
         if(document.getElementById("wrap_product"))
        $$.evento(document.getElementById("wrap_product"),"mouseout",MENU.hide); 
    
     if(document.getElementById("wrap_news"))
        $$.evento(document.getElementById("wrap_news"),"mouseout",MENU.hide); 
             
    },
    display:function(){
        
        var a;
        
        a = document.getElementById("submenu");
        
        $("#submenu").slideDown();
        
        
        
        
        
    },
    hide:function(){
         $("#submenu").slideUp();
    }
}

function same_height(){
	 var a,b,c,d=0;
	 
	 a = document.getElementsByTagName("section");
	 console.log("a="+a.length);
	 for(b=0; a.length>b; b++){
	 	
	 	c= a[b].offsetHeight;
	 	console.log("c="+c);
	 	
	 	if(c>d){
	 		
	 		d=c;
	 	}
	 	console.log(c);
	 }

	 
	 console.log("d="+d);
	 
	 for(b=0; a.length>b; b++){
	 	a[b].style.height=d+"px";
	 }
}
