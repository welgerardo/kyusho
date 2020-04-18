// JavaScript Document
/**
 * SCRIPT
 * MGP_NUCLEOGEST V8.03.140615
 * COPYRIGHT MANUEL GERARDO PEREIRA 2015
 * TODOS OS DIREITOS RESERVADOS
 * CONTACTO: GPEREIRA@MGPDINAMIC.COM WWW.MGPDINAMIC.COM
 *
 **/
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
var loader = false;
var FLAGS = {
	"pt" : "imagens/port.png",
	"en" : "imagens/ing.png",
	"fr" : "imagens/fran.png",
	"es" : "imagens/spai.png"
};
var FLAGSMODE = {
	"add" : "ADD",
	"up" : "UPDATE",
	"clo" : "CLONE",
	"sav" : "SAVE"
};
var $$ = {
	// array de chamadas ao servidor
	calls : [],
	/**
	 * retorna o event.target e = evento
	 */
	slf : function(evt) {

		var e = evt || window.event;

		if (e) {
			var targ;

			if (e.target)
				targ = e.target;

			if (e.srcElement)
				targ = e.srcElement;

			if (targ && targ.nodeType == 3)
				targ = targ.parentNode;

			return targ;
		} else {
			return null;
		}
	},
	/**
	 * constroi um elemento html
	 *
	 * e = tipo de elemento a construir
	 * j = objecto json com os atributos
	 * o = elemento onde o elemento criado sera inserido
	 * t = texto a colocar dentro do elemento
	 * ev = objecto json com os eventos a associar ao elemento
	 *
	 */
	make : function(e, j, o, t, ev) {
		var el = null;
		var key;

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
				if (key == "class") {
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
			el.innerHTML= t;
		}
		if (ev) {
			for (n in ev) {
				el.addEventListener(n, ev[n], false);
				// bubbling fase
				//$(el).on(n,ev[n]);
			}
		}
		return el;
	},
	/**
	 * associa um evento a um objecto
	 *
	 * o=objecto v=evento f=funçao
	 *
	 */
	events : function(o, v, f) {
		try {
			o.attachEvent("on" + v, f);
		} catch (e) {
			o.addEventListener(v, f, false);
		}
	},
	/**
	 *
	 * remove a associação de um evento a um objecto
	 * o = objeto
	 * v = evento
	 * f = função que deixa de ser executada
	 *
	 */
	stopEvent : function(o, v, f) {
		try {
			o.detachEvent("on" + v, f);

		} catch (e) {

			o.removeEventListener(v, f, false);
		}
	},
	/**
	 * insere texto em um elemento html
	 *
	 */
	text : function(o, tx) {
		var txt = document.createTextNode(tx);
		o.appendChild(txt);
	},
	/**
	 * retorna o elemento com o id fornecido o = id do elemento
	 *
	 */
	el : function(o) {
		try {
			return document.getElementById(o);
		} catch (e) {
			return false;
		}

	},
	/**
	 * remove um elemento o = elemento onde vai retirar o nó m = indice do nó
	 *
	 */
	remove : function(o, m) {
		o.removeChild(o.children[m]);
	},
	/**
	 * metodo ajax v = variaveis para solicitação ao servidor h = funçao que manipula a resposta do servidor m = modulo que deve ser chamado no servidor(server) c = module da requisição(module)
	 */
	callServer : function(v, h, m, c) {
		var a = [v, h, m, c];
		$$.calls.push(a);

		if ($$.calls.length === 1) {
			$$.callServer_handler();
		}
		;
	},
	callServer_handler : function() {

		if ($$.calls.length > 0) {
			if ($$.el("blk")) {
				$$.el("blk").style.display = "block";
				$("#iloader").show();
			}

			var en = ($$.calls[0][2]) ? BigBang.mURL + $$.calls[0][2] + ".php" : BigBang.mURL + BigBang.ob.server + ".php";

			var md = ($$.calls[0][3]) ? $$.calls[0][3] : BigBang.ob.module;
			var variables = "module=" + md + "&" + $$.calls[0][0];

			$$.ajx = $.ajax({
				type : "POST",
				url : en,
				cache : false,
				data : variables,
				processData: false,
				dataType : "text",
				success : function(dat) {
					if ($$.calls.length > 0) {
						var f = $$.calls[0][1];
						// chama a função handler
						f(dat);
					}
					$("#iloader").hide();
					$$.calls.shift();
					if ($$.calls.length < 1) {
						$$.el("blk").style.display = "none";
					}
					$$.callServer_handler();
				},
				error : function(dat) {

					$$.erro("Não foi possivel completar a operação.JS240");

				}
			});
		}

	},
	erro : function(a) {

		if (a)
			alert(a);

		$$.calls = [];
		$$.ajx = null;
		$("#iloader").hide();
		$$.el("blk").style.display = "none";
		return false;
	},
	parent : function(o, p) {

		var t;

		switch (typeof o) {

		case "object":
			t = o;
			break;
		case "string":
			t = $$.el(o);
			break;
		default:
			t = null;
		}

		if (t && p) {
			while (t.nodeName != p) {
				t = t.parentNode;
			}
		}

		return t;
	},
	blkon : function() {
		$$.el("blk").style.display = "block";
	},
	blkoff : function() {
		$$.el("blk").style.display = "none";
	}
};
/**
 * @version V1.00.220115
 *
 */
AddItem = {
	add : function() {
		$$.callServer("flag=ADD", AddItem.addHandler);
	},
	addHandler : function(xdat) {

		OperationsBar.barra(0, 0, 1, 0, 0);

		try {
			var a = JSON.parse(xdat);
			if (a.alert)
				$$.erro(a.alert);
		} catch (e) {
			BigBang.palcoum(xdat);
		}

		if (BigBang.ob.starter) {
			if (BigBang.ob.folders_on)
				Folders.selected_line();

			BigBang.ob.starter();

		}

		ShowHide.init();
		SideMenu.init_editors();

		Buttons.init();
		TollTips.init();
		Validate.init();

	}
};
/**
 * @version V1.01.140615
 *
 */
Buttons = {
	// array com as opções de select
	op : [],
	numberChar : 200,
	init : function() {
		var d = document.getElementsByTagName("*"),
		    dl = d.length,
		    f = false,
		    s = null;

		Buttons.init_images();

		while (dl--) {

			s = d[dl].getAttribute("data-type") || d[dl].getAttribute("data-action");

			$$.events(d[dl], "dragenter", DragDrop.over_drag_id);
			$$.events(d[dl], "dragover", DragDrop.over_drag_id);

			switch (s) {

			case "listItem":
				if (!f) {
					f = true;
					$$.callServer("flag=OPTIONS", function(xdat) {
						Buttons.list_options_handler(xdat);
					}, false, false);
				}
				$$.events(d[dl], "focus", Buttons.select_item);
				break;

			case "radioSelection":
				d[dl].onclick = Buttons.rdOptions;
				break;
			case "keywords":
				$$.events(d[dl], "dragenter", DragDrop.over_drag_id);
				$$.events(d[dl], "dragover", DragDrop.over_drag_id);
				$$.events(d[dl], "drop", Buttons.dropKeyWords);
				break;
			case "countchar":
				Buttons.count_char(d[dl], false);
				$$.events(d[dl], "keyup", function(event) {
					Buttons.count_char(false, event);
				});
				break;
			case "countwords":
				Buttons.count_words(d[dl]);
				$$.events(d[dl], "keyup", function(event) {
					Buttons.count_words(false);
				});
				break;
			}
		}
	},
	init_images : function() {

		var i,
		    x;

		i = document.images;
		x = i.length;

		while (x--) {
			if (i[x].getAttribute("data-action") === "newInput") {

				$$.events(i[x], "click", Buttons.change_select);
			}
			if (i[x].getAttribute("data-action") === "addcol") {                                
                                Tables.init(i[x]);
				$$.events(i[x], "click", Tables.add_col);
			}
			if (i[x].getAttribute("data-action") === "addrow") {

				$$.events(i[x], "click", Tables.add_row);
			}
			if (i[x].getAttribute("data-action") === "delcol") {

				$$.events(i[x], "click", Tables.delete_col);
			}
			if (i[x].getAttribute("data-action") === "delrow") {

				$$.events(i[x], "click", Tables.delete_row);
			}
			if (i[x].getAttribute("data-action") === "deltable") {

				$$.events(i[x], "click", Tables.delete_all);
			}
			if (i[x].getAttribute("data-action") === "delthis") {

				$$.events(i[x], "click", Buttons.delete_this);
			}
			if (i[x].getAttribute("data-action") === "pass") {

				$$.events(i[x], "click", Buttons.pass);
			}
			if (i[x].getAttribute("data-action") === "prodvar") {

				$$.events(i[x], "click", Product_var.init);
			}
		}
	},
	/**
	 * preenche a array op
	 */
	list_options_handler : function(xdat) {
		var a;
		try {
			a = JSON.parse(xdat);
		} catch (e) {
			return false;
		}
		if (Buttons.op.length === 0) {

			Buttons.op = a.result;
		}
	},
	list_itens : function(ev) {
		var v = null,
		    p = false,
		    e = $$.slf(ev) || ev,
		    t = e.getAttribute("data-target"),
		    tt,
		    x;

		if (t) {
			p = Buttons.op[t.toLowerCase()][e.value];
			v = $$.el(t).parentNode;
		}
		if (v) {
			v.children[0].value = "";
			v.children[1].innerHTML = "";

			if (p && p != undefined) {
				for ( x = 0; x < p.length; x++) {
					if (p[x])
						$$.make("p", "", v.children[1], p[x]);
				}
			}

			while (v.children[0].getAttribute("data-target")) {
				tt = v.children[0].getAttribute("data-target");
				v = $$.el(tt).parentNode;
				v.children[1].innerHTML = "";
				v.children[0].value = "";
			}

		}
	},
	/**
	 *
	 */
	select_item : function(event) {
		var s = $$.slf(event),
		    b;

		if ( b = s.parentNode.children[1]) {
			var c = b.querySelectorAll("p"),
			    d = c.length;

			while (d--) {
				$$.events(c[d], "click", Buttons.mark_item);
			}

			$$.events(document, "mousedown", function(ev) {
				var a = $$.slf(ev);

				if (b != a && b != a.parentNode) {
					b.style.display = "none";

				} else {
					try {
						ev.stopPropagation();
					} catch(e) {
						ev.returnValue = false;
					}
					return false;
				}
			});

			b.style.width = (s.clientWidth - 5) + "px";
			b.style.display = "block";
		}

	},
	mark_item : function(event) {
		var et = $$.slf(event);
		et.parentNode.parentNode.children[0].value = et.firstChild.nodeValue;
		et.parentNode.style.display = "none";
		Buttons.list_itens(et.parentNode.parentNode.children[0]);

	},
	schOptions : function(ev) {
		var x;
		var e = $$.slf(ev) || ev;

		var et = e.value;
		var t = e.getAttribute("data-target");

		var v = $$.el(t);

		var p = _Buttoms.op[t][et];

		v.innerHTML = "";

		$$.make("option", {
			"value" : ""
		}, v, "----------");

		if (p && p != undefined) {
			for ( x = 0; x < p.length; x++) {

				$$.make("option", {
					"value" : p[x][0]
				}, v, p[x][1]);
			}
		}

		if (v.getAttribute("data-target")) {
			_Buttoms.schOptionsRec(v);
		}
	},
	/*
	 * busca sesects que podem estar encadeadas e mudas as options
	 */
	schOptionsRec : function(o) {

		var e = o;
		var t = e.getAttribute("data-target");
		var v = $$.el(t);

		var p = _Buttoms.op[t][e.value];

		v.innerHTML = "";

		$$.make("option", {
			"value" : ""
		}, v, "----------");
		if (p && p != undefined) {
			for (var x = 0; x < p.length; x++) {

				$$.make("option", {
					"value" : p[x][0]
				}, v, p[x][1]);
			}
		}

		if (v.getAttribute("data-target")) {
			_Buttoms.schOptionsRec(v);
		}
	},
	dropKeyWords : function(event) {

		var data = event.dataTransfer.getData("keywords").innerHTML;

		var h = $$.slf(event);

		if (!h.innerHTML) {
			h.innerHTML = data;
		} else {
			var a = h.innerHTML;
			h.innerHtml = "";
			h.innerHTML = a + "," + data;
		}
	},
	delete_this : function(event) {

		var e = $$.slf(event).parentNode;
		e.parentNode.removeChild(e);

	},
	rdOptions : function(event) {

		$$.callServer("flag=option&tab=" + event.target.value, function(event) {
			_Buttoms.rdOptionsHandler(event);
		}, false, false);
	},
	rdOptionsHandler : function(xdat) {

		try {
			var dat = JSON.parse(xdat);
			var c = document.getElementById("campos");

			for (var t = 1; t < dat.campos.length; t++) {

				var s = $$.make("p", null, c, null, null);
				$$.make("input", {
					"type" : "checkbox",
					"value" : dat.campos[t],
					"name" : dat.campos[t],
					"class" : "ck11"
				}, s, null, null);
				$$.make("span", null, s, dat.campos[t], null);
			}
		} catch(e) {
			$$.erro("Não foi possivel realizar a operação.JSBT316");
		}

	},
	count_char : function(el, ev) {

		var a = (el) ? $(el) : $($$.slf(ev)),
		    c = a.attr("data-length"),
		    b = a.val() || a.text(),
		    d = a.prev(),
		    f = c - b.length,
		    g = a.prop("nodeName");

		if (g == "TEXTAREA" || g == "INPUT") {
			a.attr("maxlength", c);
			a.val(b.substr(0, c));

		} else {
			a.text(b.substr(0, c));
		}

		f = c - b.length;

		d.text((f < 0) ? 0 : f);
	},
	count_words : function(el) {
		var a = (el) ? $(el) : $(document.activeElement);

		if (a.attr("data-type") === "countwords") {
			var b = a.prev(),
			    h = a.text() || a.val();

			if (b) {
				if (h) {
					var d = (a.attr("data-divider")) ? a.attr("data-divider") : " ",
					    c = h.split(d),
					    f = (a.attr("data-length")) ? a.attr("data-length") : false;

					b.text(c.length);

					if (f && c.length > f) {
						b.css("color", "red");

					} else {
						b.css("color", "#999");
					}

				} else {
					b.text(0);
					b.css("color", "#999");
				}
			}

		}

	},
	pass : function(event) {
		var b = $($$.slf(event)).attr("data-target");

		$$.callServer("flag=PASS&toke=" + $$.el("identidade").value, function(xdat) {
			try {
				var a = JSON.parse(xdat);

				if (a.senha)
					$$.el(b).value = a.senha;

				if (a.alert)
					$$.erro(a.alert);

			} catch (e) {
				return false;
			}

		}, false, false);

	}
};

/**
 * manipula a inserção de tabelas em ficha de adição e atualização
 *
 *
 */
Tables = {
    /**
     * 
     * @param {type} a
     * @returns {Boolean}
     */
     init:function(a){
            var b,
		c,
                d;
		try {
			b = $$.parent(a, "DIV").getElementsByClassName("editbox")[0];
			c = b.getElementsByTagName("table");
                        c[0].contentEditable=false;
			if (c.length) {
				d=c[0].querySelectorAll("td");
                                for(var f=0;f<d.length;f++){
                                    d[f].contentEditable=true;
                                }  
                            } 
		} catch (e) {
			return false;
		}
        },
	/**
	 * devolve onde a tabela irão ser criadas as linhas e as colunas
	 */
	tbl : function(a) {
		var b,
		    c;
		try {
			b = $$.parent(a, "DIV").getElementsByClassName("editbox")[0];
			c = b.getElementsByTagName("table");

			if (c.length) {
				return c[0];
			} else {
				return $$.make("table", {
					"style" : "table-layout:fixed;margin:auto",
                                        "contenteditable":"false"
				}, b);
			}
		} catch (e) {
			return false;
		}
	},
	/**
	 * cria uma nova linha
	 *
	 * @param event
	 */
	add_row : function(event) {
		var a,
		    c,
		    d,
		    f,
		    g,
		    h,
                    x;

		a = Tables.tbl(event.target);

		if (a) {
			d = $$.make("tr", null, a);

			c = a.getElementsByTagName("tr");

			if (c.length) {

				g = c[0].getElementsByTagName("td");

				h = (g.length) ? g.length : 1;

				for ( f = 0; f < h; f++) {
					x= $$.make("td", {"style": "white-space:pre;padding:5px;border:1px solid #ccc"}, d, " ", null);
                                        x.contentEditable = true;
                                        var l=0;
				}
			}

		}
	},
	/**
	 * adiciona uma nova coluna
	 *
	 * @param event
	 */
	add_col : function(event) {
		var a,
		    b,
		    f,
                    x;

		a = Tables.tbl(event.target);

		if (a) {
                    
                    b = a.getElementsByTagName("tr");

                    if (b.length) {
                        for ( f = 0; f < b.length; f++) {
                           x= $$.make("td", {"style": "white-space:pre;padding:5px;border:1px solid #ccc"}, b[f], null, null);
                           x.contentEditable = true;
                           var l = 0;
				}
			} else {
                            Tables.add_row(event);
			}
		}
	},
	/**
	 * elimina uma linha
	 */
	delete_row : function() {
		var a,
		    c;

		a = window.getSelection();

		c = a.anchorNode;

		while (c.nodeName !== "TR") {
			c = c.parentNode;
		}

		c.parentNode.removeChild(c);
	},
	/**
	 * elimina uma coluna
	 */
	delete_col : function() {

		var a,
		    b,
                    c,
		    d;

		a = window.getSelection();
		b = a.anchorNode;

		while (b.nodeName !== "TD") {
			b = b.parentNode;
		}
                
                c = b.cellIndex;
		d = b;

		while (d.nodeName !== "TABLE") {
			d = d.parentNode;
		}
                
		var tr = d.getElementsByTagName("tr");

		for (var f = 0; f < tr.length; f++) {
                    var y = tr[f].getElementsByTagName("td");
                    if(y[c]){
                        tr[f].removeChild(y[c]);
                    }    			
		}
	},
	/**
	 * apaga a tabela
	 *
	 * @param event
	 */
	delete_all : function(event) {
		var a,
		    b,
		    d;

		a = window.getSelection();
		d = a.anchorNode;

		while (d.nodeName !== "TABLE") {
			d = d.parentNode;

		}

		d.parentNode.removeChild(d);

	},
};

/**
 * @version V1.01.280115
 *
 */
Contacts = {
	init : function() {
		var z = $$.el("zomm");
		z.style.display = "block";

		var dm = $$.make("div", {
			"class" : "wzoom"
		}, z);

		$$.make("img", {
			"src" : "imagens/minidel.png",
			"class" : "zommout"
		}, dm, null, {
			"click" : Contacts.close
		});

		var sp = $$.make("div", {
			"class" : "zommsel"
		}, dm, null, {
			"click" : Contacts.add
		});

		$$.make("img", {
			"src" : "imagens/ftCont.png",
			"class" : "zselimg"
		}, sp);
		$$.make("span", {
			"class" : "zselp"
		}, sp, "Pessoa");

		var se = $$.make("div", {
			"class" : "zommsel"
		}, dm, null, {
			"click" : Contacts.add
		});
		$$.make("img", {
			"src" : "imagens/ftEmp.png",
			"class" : "zselimg"
		}, se);
		$$.make("span", {
			"class" : "zselp"
		}, se, "Empresa");
	},
	close : function() {
		var z = $$.el("zomm");
		z.style.display = "none";
		z.innerHTML = "";
	},
	add : function(event) {
		var t = "&type=" + event.currentTarget.children[1].textContent;
		Contacts.close();
		$$.callServer("flag=" + FLAGSMODE['add'] + t, AddItem.addHandler);
	},
	/**
	 * Informação sobre colaboradores de uma empresa na ficha da empresa
	 *
	 * @param event
	 */
	cont_data : function(event) {

		var tg = event.currentTarget || $$.slf(event);
		FilterResult.make_active();
		FilterResult.make_select();
		BigBang.ob.folders_on()
		BigBang.ob.file_on(tg.id)

		//$$.callServer("flag=INFO&toke=" + , Contacts.cont_data_handler);
	},
	cont_data_handler : function(xdat) {
		var b,
		    d = $$.el("infoCont");
		d.innerHTML = "";

		try {
			var dat = JSON.parse(xdat);
		} catch (e) {

			d.innerHTML = "<p> Não é possivel mostrar a informação deste contato.<p>";
			return false;
		}

		for ( b = 0; b < dat.result.length; b++) {

			if (dat.result[b].match(/[\w-\.]+@([\w-]+\.)+[\w-]{2,4}/g)) {
				var pu = $$.make("p", null, d, null, null);
				$$.make("a", {
					"href" : "mailto:" + dat.result[b]
				}, pu, dat.result[b], null);
				continue;
			}
			$$.make("p", null, d, dat.result[b], null);
		}

	}
};

/**
 * @version V1.00.220115
 *
 */
DragDrop = {
	mode : null,
	/*
	 * inicializa a operação com a o preenchimento de setData event = evento tp = tipo de dados dt = valor dos dados
	 */
	set_drag_data : function(event, tp, dt) {

		DragDrop.mode = null;
		event.dataTransfer.setData("Text", dt);
		event.dataTransfer.effectAllowed = "copy";
		DragDrop.mode = tp;
	},
	/*
	 * verifica se o elemento permite que larguem dados desse tipo
	 */
	over_drag_id : function(event) {
		var a,
		    c,
		    y,
		    x;
		c = 0;
		try {
			y = event.target.getAttribute("data-type")
		} catch(e) {
			y = false
		}
		if (y) {
			y = y + ",Text";
			a = y.split(",");
			//no firefox o dataTransfer.types é uma lista de string e tem que ser testada para ver se contem os tipos de eventos que são permitiodos nesse
			//elemento (ver DomStringList)

			for ( x = 0; x < a.length; x++) {

				try {
					if (DragDrop.mode == a[x]) {
						c = 1;
						break;
					} else {
						c = 0;
					}

				} catch(e) {
					try {
						if (event.dataTransfer.types.contains(a[x])) {
							c = 1;
							break;
						} else {
							c = 0;
						}
					} catch(e) {
						c = 0;
					}
				}

			}
		}
		if (c) {
			event.dataTransfer.effectAllowed = "copy";
			event.dataTransfer.dropEffect = "copy";
			event.preventDefault();
			return false;
		} else {

			try {

				event.preventDefault();
				event.dataTransfer.effectAllowed = "none";
				event.dataTransfer.dropEffect = "none";
				return false;

			} catch(e) {

				window.event.returnValue = false;
				window.event.dataTransfer.dropEffect = "none";
				return false;
			}

		}

	},
	/**
	 *
	 */
	validate_drop : function(event) {
		var a = event.target.getAttribute("data-type"),
		    b = a.split(","),
		    c,
		    d = false;

		for ( c = 0; c < b.length; c++) {

			if ( d = event.dataTransfer.getData(b[c])) {
				event.preventDefault();
				return {
					"target" : event,
					"data" : d
				};
			}
		}
	},
	/*
	 * limpa a propriedade dataTransfer
	 */
	clean : function(event) {
		try {
			event.dataTransfer.clearData();
		} catch(e) {

		}

		DragDrop.mode = null;

	}
};

DeleteIten = {
	/*
	 * apaga item atraves do botão apagar
	 */
	delet : function() {

		var a;

		a = $$.el("mainEdition").getAttribute("data-id");

		if (a) {

			var confirma = confirm("Deseja apagar este item?");

			if (confirma) {

				Folders.delete_iten(a);

			} else {

				return false;
			}
		}
	}
};

Edit = {
	/*
	 * abre para edição
	 */
	init : function(slf) {

		var fc = $$.el("itenfile");
		try {
			var id = fc.getAttribute("data-id");
		} catch (e) {
			id = slf;
		}
		if (!BigBang.saveon()) {

			$$.callServer("flag=" + FLAGSMODE['up'] + "&toke=" + id, Edit.init_handler);
		} else {

			if (BigBang.sairSemSalvar())
				Edit.init(id);
		}

	},
	/*
	 * manipula o resultado do servidor
	 */
	init_handler : function(dat) {

		try {

			var a = JSON.parse(dat);

			if (a.alert)
				$$.erro(a.alert);
		} catch (e) {
console.log(dat);
			BigBang.palcoum(dat);
			OperationsBar.barra(0, 0, 1, 1, 0, 0);

			if (BigBang.ob.starter) {
				BigBang.ob.starter();
				ShowHide.init();
				SideMenu.init_editors();
				TollTips.init();
				Validate.init();
				Buttons.init();
			}

		}

	}
};
/**
 *
 */
Clone={
	/*
	 * abre para edição
	 */
	init : function(slf) {

		var fc = $$.el("itenfile");
		try {
			var id = fc.getAttribute("data-id");
		} catch (e) {
			id = slf;
		}
		if (!BigBang.saveon()) {

			$$.callServer("flag=" + FLAGSMODE['clo'] + "&toke=" + id, Clone.init_handler);
		} else {

			if (BigBang.sairSemSalvar())
				Edit.init(id);
		}

	},
	/*
	 * manipula o resultado do servidor
	 */
	init_handler : function(dat) {

		try
		{
			var a = JSON.parse(dat);

			if (a.alert)
				$$.erro(a.alert);
		}
		catch (e)
		{
			BigBang.palcoum(dat);
			OperationsBar.barra(0, 0, 1, 1, 0, 0);

			if (BigBang.ob.starter)
			{
				BigBang.ob.starter();
				ShowHide.init();
				SideMenu.init_editors();
				TollTips.init();
				Validate.init();
				Buttons.init();
			}

		}

	}
}
/**
 * @version V1.01.230115
 *
 */
File = {
	/**
	 * mostra a ficha do produto esta função deve sempre ser chamada um ultimo, pois o chama $$.callServer com o ultimo parametro true, o que evita que anule outra chamado ao servidor que esteja activa
	 */
	fo : function() {
		return $$.el("itenfile");
	},
	fid : function() {
		if (File.fo()) {
			return File.fo().getAttribute("data-id");
		}
		return false;
	},
	header : function() {
		return File.fo().children[0];
	},
	stage : function() {
		return File.fo().children[1];
	},
	show : function(evt) {

		var id = null;

		try {
			id = $$.slf(evt).id;
			evt.stopPropagation();
		} catch (e) {
			id = evt;
		}

		if (BigBang.saveon()) {
			if (BigBang.sairSemSalvar())
				File.show(id);

		} else {
			OperationsBar.barra(0, 0, 0, 0, 0, 0);
			$$.callServer("flag=FILE&toke=" + id, File.show_handler, null, false);
		}
	},
	/*
	 * manipula a ficha do produto
	 */
	show_handler : function(xdat) {

		if (xdat) {
			try {
				var d = JSON.parse(xdat);

				if (d.alert)
					$$.erro(d.alert);

				if (d.error)
					$$.erro(d.error);

			} catch (e) {

				SideMenu.off_editors();
				BigBang.palcoum(xdat);

				File.init_submenu();
				File.init_file();

				if (BigBang.ob.folders_on)
					Folders.selected_line();
			}

		}

		OperationsBar.barra(1, 1, 0, 0, 0, 1);
	},
	init_file : function() {

		var a = File.stage().getElementsByTagName("*"),
		    b;

		for ( b = 0; b < a.length; b++) {
			switch (a[b].getAttribute("data-action"))
			{

			case "anchor":
				$$.events(a[b], "click", File.goto_url);
				break;
			case "contact":
			console.log(a[b]);
				$$.events(a[b], "click", Contacts.cont_data);
				break;
			case "note":
				$$.events(a[b], "click", File.display);
			default:
				break;
			}
		}
	},
	/*
	 * abre links noutra pagina ou no browser
	 */
	goto_url : function(event) {

		//var a = new air.URLRequest(this.getAttribute("data-target"));

	},
	init_submenu : function() {
		var a,
		    c;

		a = $$.el('fichanav');

		if (a) {

			a = a.children[0].children;

			for ( c = 0; c < a.length; c++) {

				$$.events(a[c], "click", File.submenu);
			}

		}
	},
	submenu : function(event) {

		var a = $$.slf(event),
		    b = a.parentNode.getElementsByTagName("li"),
		    c = b.length,
		    d = File.fid();

		while (c--) {
			b[c].className = "lisubmenu";
		}

		a.className = "lisubmenusel";

		switch (a.id) {
		case "mensagens":
			$$.callServer("flag=MESSAGES&toke=" + d, File.submessages_handler);
			break;
		case "ficha":
			$$.callServer("flag=FILEOP&toke=" + d, File.submenu_handler);
			break;
		case "notas":
			$$.callServer("flag=NOTES&toke=" + d, File.submenu_handler);
			break;
		case "estatisticas":
			File.stage().innerHTML = "";
			Stats.stage = File.stage();
			$$.callServer("flag=ITENSTAT&toke=" + d, Stats.init_handler);
			break;
		}

	},
	submessages_handler : function(xdat) {

		Messages.make_result(File.stage(), xdat);
	},
	submenu_handler : function(xdat) {

		try {
			var a = JSON.parse(xdat);

			if (a.result)
				File.stage().innerHTML = "<h1>" + a.result + "</h1>";
		} catch(e) {
			var a,
			    c;

			a = File.stage();
			a.innerHTML = "";

			a.innerHTML = xdat;
		}

		File.init_file();
	},
	// TODO
	display : function(event) {

		var a = $$.slf(event);

		if ($(a).next().css("display") == "block") {
			$(a).next().hide(400);

		} else {
			$(a).next().show(400);
		}
	}
};
/**
 * @version V1.00.220115
 *
 */
Folders = {

	fo : "url('imagens/online.png')", // imagem de item online

	fc : "url('imagens/offline.png')", // imagem de item offline

	fou : "url('imagens/folderon.png')", // imagem de pasta aberta

	fcu : "url('imagens/folderoff.png')", // imagem de pasta fechada

	sf : [], // lista de pastas abertas

	qr : false, // categoria se essa opção estiver disponivel no modulo

	clear : function() {
		var a = $$.el("wrapfolders");

		a.innerHTML = "";

		return a;
	},
	/**
	 * função ajax que retira do servidor a lista das pastas
	 *
	 */
	open_folders : function() {
		var a = "",
		    b;

		if (Folders.qr) {
			for (b in Folders.qr) {
				a += "&" + b + "=" + Folders.qr[b];
			}
		}

		$$.callServer("flag=FOLDER" + a, Folders.open_folders_handler);
	},
	/**
	 * manipula o objeto json de configuração de pastas vindo do servidor
	 *
	 * @param {eventobject}
	 *            ev
	 * @returns {undefined}
	 *
	 */
	open_folders_handler : function(dat) {
		
            var a = false;

            try {
                a = JSON.parse(dat);
            } catch (e) {                    
                Folders.clear();
                return;
            }

            if (a) {
                if (a.alert) {
                    var d = (a.alert) ? a.alert : "erro";
                    $$.erro(d);
                    return false;
                }

                if (a.sfolder) {
                    Folders.make_simple_folders(a.sfolder);
                } else {
                    Folders.make_folders(a);
                }
            }
	},
	/**
	 * Cria pastas vazias
	 *
	 * @param ob - objeto json de configuração das pastas
	 *
	 */
	make_simple_folders : function(ob) {
		var a,
		    b,
		    c;

		a = Folders.clear();

		b = $$.make("ul", {
			"class" : "unidade",
			"id" : "foldersTree"
		}, a);

		for ( c = 0; c < ob.length; c++) {
			if (ob[c]) {
				$$.make("li", {
					"class" : "linha",
					"id" : ob[c],
					"data-type" : "menu"
				}, b, ob[c], {
					"click" : Folders.select_simple_folder,
					"dragenter" : DragDrop.over_drag_id,
					"dragover" : DragDrop.over_drag_id,
					"drop" : function(event) {
						Folders.change_folder(event, $$.slf(event).id);
					}
				});
			}
		}

		$$.make("ul", {
			"class" : "pastaintsolo",
			"id" : "",
			"data-type" : "menu"
		}, b, null, {
			"dragenter" : DragDrop.over_drag_id,
			"dragover" : DragDrop.over_drag_id,
			"drop" : function(event) {
				Folders.change_folder(event, "");
			}
		});

		// chamada para manter a configuração anterior a um atualização das
		// pastas.
		Folders.selected_simple_line();
	},
	/**
	 * Cria as pastas com conteúdo
	 *
	 * @param f - objeto json de configuração das pastas
	 *
	 * @returns {Boolean}
	 */
	make_folders : function(f) {

		a = Folders.clear();

		var m,
		    x,
		    s,
		    u,
		    l,
		    z;

		m = $$.make("ul", {
			"class" : "unidade",
			"id" : "foldersTree"
		}, a);

		// pastas com conteudo
		for (x in f) {
			//evita subir na cadeia de prototype
			if (x && f.hasOwnProperty(x)) {
				l = $$.make("li", {
					"class" : "linha",
					"id" : x,
					"data-type" : "menu"
				}, m, x, {
					"click" : Folders.select_folder,
					"dragenter" : DragDrop.over_drag_id,
					"dragover" : DragDrop.over_drag_id,
					"drop" : function(event) {
						Folders.change_folder(event, $$.slf(event).id);
					}
				});

				u = $$.make("ul", {
					"class" : "pastaint",
					"id" : "f:" + x
				}, l);

				Folders.make_line_folder(u, f[x]);
			}
		}

		s = $$.make("ul", {
			"class" : "pastaintsolo",
			"data-type" : "menu"
		}, m, null, {
			"dragenter" : DragDrop.over_drag_id,
			"dragover" : DragDrop.over_drag_id,
			"drop" : function(event) {
				Folders.change_folder(event, "");
			}
		});

		for (z in f) {
			if (!z)
				Folders.make_line_folder(s, f[z]);
		}

		// funções chamadas para recuperar a configuração anterior após uma
		// atualização
		Folders.folder_on();
		// abre as pastas que estavam definidas se
		// sf(pastas que já estavam abertas)
		Folders.selected_line();
		// marca a linha do item que está no palco

		return false;
	},
	/**
	 * cria os elementos de cada pasta
	 *
	 * @param a objeto onde inserir a linha
	 * @param b array com dados da linha
	 */
	make_line_folder : function(a, b) {

		var b,
		    d;

		for (c in b) {

			d = $$.make("li", {
				"class" : "linhaint",
				"title" : b[c]['status'],
				"id" : "i:" + b[c]['id'],
				"data-type" : "menu"
			}, a, b[c]['name'], {
				"dragstart" : Folders.init_drag,
				"dragend" : DragDrop.clean,
				"click" : BigBang.ob.file_on
			});

			d.draggable = true;

			d.style.listStyleImage = (b[c]['status'] === "online") ? Folders.fo : Folders.fc;
		}

	},
	/**
	 * abre e fecha pastas com conteudo e altera o icon. Esta função é chamada com um click numa pasta com conteúdo.
	 */
	select_folder : function(event) {

		try {
			event.stopPropagation();
		} catch(e) {

		}

        //objeto onde aconteceu o evento
		var t = $$.slf(event);

		if (BigBang.saveon() && event.ctrlKey) {
			$$.el("public_folder").value = t.id;
		} else {

			var i = Folders.sf.indexOf(t.id);

			// se estiver o icon de aberto na pasta fecha a pasta
			if (t.style.listStyleImage.indexOf("folderon.png") > -1) {
				if (i > -1) {
					Folders.sf.splice(i, 1);
					if (t.children[0]) {
						t.style.listStyleImage = Folders.fcu;
						$(t.children[0]).slideUp();
						t.folder_open = false;
					}

				}

			}
			// se estiver o icon de fechado na pasta abre a pasta
			else {
				if (i === -1) {
					Folders.sf.push(t.id);
					if (t.children[0]) {
						t.style.listStyleImage = Folders.fou;
						$(t.children[0]).slideDown();
						t.folder_open = true;
					}
				}
			}
		}
		return false;
	},
	/**
	 * Seleciona pasta sem conteúdo e chama o manipular do modulo
	 *
	 * @param event
	 * @returns {Boolean}
	 */
	select_simple_folder : function(event) {

		var c,
		    d,
		    t = $$.slf(event);

		event.stopPropagation();

		// se estiver o icon de fechado na pasta abre a pasta
		if (t.style.listStyleImage.indexOf("folderon.png") > -1) {
			t.style.listStyleImage = Folders.fcu;
			Folders.sf = "";
		}
		// se estiver o icon de aberto na pasta fecha a pasta
		else {
			t.style.listStyleImage = Folders.fou;
			Folders.sf = t.id;
		}

		c = document.querySelectorAll(".linha");
		d = c.length;

		while (d--) {
			if (c[d] !== t)
				c[d].style.listStyleImage = Folders.fcu;
		}

		BigBang.ob.subfolders_off(Folders.sf);
		return false;

	},
	/**
	 * Seleciona a linha que está definida em sf. Esta função é chamada quando existe uma atualização da lista de pastas sem conteúdo.
	 *
	 * @returns {Boolean}
	 */
	selected_simple_line : function() {

		if ( typeof Folders.sf === "string" && $$.el(Folders.sf)) {

			$$.el(Folders.sf).style.listStyleImage = Folders.fou;

		} else {
			return false;
		}
	},
	/**
	 * abre as pastas que estão na lista de pastas abertas. Esta função é chamada quando existe uma atualização das pastas.
	 *
	 * @param o - pasta ou nome da pasta
	 */
	folder_on : function(o) {
		// se existirem pasta com itens dentro abre as pastas
		if ($('.pastaint').length > 0) {
			for (var f in Folders.sf) {
				var a = $$.el(Folders.sf[f]);

				if (a && a.children[0]) {
					a.style.listStyleImage = Folders.fou;
					$(a.children[0]).slideDown();
				}
			}
		}
	},
	/**
	 * Marca a linha do item em exibição no palco.
	 *
	 * @param ev
	 * @returns {Boolean}
	 */
	selected_line : function(event) {

		var l = document.querySelectorAll("li.linhaints");

		for (var s in l) {
			l[s].className = "linhaint";
		}

		if (event && event != undefined && event != null)
		{
			$$.slf(event).className = "linhaints";
		}
		else
		{
			var f = File.fid(),
			    m = $$.el("mainEdition"),
			    a = $$.el("foldersTree");

			if ((f || m) && a)
			{
				var i = (f) ? f : m.getAttribute("data-id");

				if (!i)
					return false;

				if ($(a).find(jq(i)).length > 0)
				{
					// TODO - se por acaso existirem 2 elementos com o mesmo id
					// , garantir que apenas a linha da pasta muda de class
					$$.el(i).className = "linhaints";

				}
				else
				{
					if ($$.el("wrapfolders").innerHTML && (i.search(/i:(.+)/) !== -1))
						BigBang.palcoum();
				}
			}

		}

	},
	/**
	 * inicia a operação de arrastar o item dentro da pasta
	 */
	init_drag : function(event) {
		DragDrop.set_drag_data(event, "menu", $$.slf(event).id);
	},
	/**
	 * apaga um item na tabela i=id do item a apagar
	 */
	delete_iten : function(i) {
		$$.callServer("flag=DELETE&toke=" + i, Folders.delete_iten_handler);
	},
	/**
	 * manipula a resposta do servidor
	 */
	delete_iten_handler : function(xdat) {

		var a,
		    b = $$.el("mainEdition"),
		    dat;
console.log(xdat);
		try {
			dat = JSON.parse(xdat);

			if (dat.alert) {
				$$.erro(dat.alert);
				return false;
			}

			if (dat.result) {
				//edição
				if (b && (b.getAttribute("data-id") == "i:" + dat.result[1])) {
					OperationsBar.barra(1, 0, 0, 0, 0);
					SideMenu.off_editors();
				}

				if (File.fo() && File.fid() == "i:" + dat.result[1])
					OperationsBar.barra(1, 0, 0, 0, 0);

				SideMenu.stage();
				Folders.open_folders();

				if (BigBang.ob.subfolders_off) {
					BigBang.ob.subfolders_off(dat.result[0]);
					Folders.sf = dat.result[0];
				}

			}

		} catch (e) {
			$$.erro("Não possivel realizar a operação.JS1905");
			return false;
		}
	},
	/**
	 * muda a pasta de um item
	 *
	 * @param event =
	 *            evento
	 * @param ob =
	 *            pasta para a qual o item deve ser mudado
	 *
	 */
	change_folder : function(event, ob) {

		if (DragDrop.mode === "menu") {
			try {
				event.stopPropagation();
				event.preventDefault();
			} catch(e) {
				event.returnValue = false;
			}

			var data = event.dataTransfer.getData("Text");

			var c = "";

			for (var q in Folders.qr) {
				c += "&" + q + "=" + Folders.qr[q];
			}

			$$.callServer("flag=CHANGE&module=" + BigBang.ob.module + "&gal=" + ob + "&toke=" + data + c, Folders.change_folder_handler);

			return false
		}
	},
	/**
	 * manipula a resposta do servidor
	 */
	change_folder_handler : function(xdat) {
		try {
			var dat = JSON.parse(xdat);

			if (dat.sfolder) {
				BigBang.ob.subfolders_off(Folders.sf);
				Folders.make_simple_folders(dat.folders.sfolder);
			}

			if (dat.result) {
				var i = $$.el("identidade");
				var p = $$.el("pasta");

				if (i && p && i.value === dat.result[1]) {
					p.value = dat.result[0];
				}

				Folders.sf.push(dat.result[0]);
				Folders.make_folders(dat.folders);

			}

			if (dat.alert)
				$$.erro(dat.alert);

		} catch (e) {
			$$.erro("Não foi possivel realizar a operação.JS2006");
		}

	},
	/**
	 * cria a a caixa input para criar uma nova pasta
	 */
	new_folder : function() {
		if (!$$.el("foldersTree")) {
			$$.make("ul", {
				"class" : "unidade",
				"id" : "foldersTree"
			}, $$.el("wrapfolders"));
		}

		$(".unidade").prepend("<li class='linha'><input id='novapasta' size='10' type='text'/></li>");
		$$.events($$.el('novapasta'), "keypress", Folders.make_folder);

	},
	/*
	 * termina de criar a nova pasta que será gravada no servido por um evento drag and drop ou por salvar um item
	 */
	make_folder : function(event) {

		// nome da nova pasta
		var np = $$.el("novapasta");

		// substitui o input text por uma linha de pasta
		if (event.keyCode === 13) {
			var s = np.value.search(/[^\d\w\xc3\x80-\xc3\x96\x20\x2D\xc3\x99-\xc3\xb6,\xc3\xb9-\xc3\xbf]/i);

			if (s === -1) {
				if (np.value !== "") {
					if (np.value.length > 2) {
						$(".unidade li:first").remove();
						$(".unidade").prepend("<li id='" + np.value + "' class='linha' data-type='menu'>" + np.value + "</li>");

						// inicia a nova pasta para operaçoes de drag and drop
						var p = $$.el(np.value);
						$$.events(p, "click", (BigBang.ob.subfolders_off) ? Folders.select_simple_folder : Folders.select_folder);
						$$.events(p, "dragenter", DragDrop.over_drag_id);
						$$.events(p, "dragover", DragDrop.over_drag_id);
						$$.events(p, "drop", function(event) {
							Folders.change_folder(event, $$.slf(event).id);
						});
					} else {
						$$.erro("Nome de pasta deve ter no minimo 3 caracteres.");
					}

				} else {
					$("#novapasta").parent().remove();
				}
			} else {
				$$.erro("Nome de pasta inválido");

			}
		}

	}
};
/**
 * @version V1.00.220115
 *
 */
Images = {
	folder : null,
	cells : null,
	filex : null,
	cell : 0,
	sender : null,
	init : function() {

		Folders.open_folders();
		Images.open_images();

	},
	open_images : function(p) {

		var a = p || "";

		Images.folder = a;

		$$.callServer("flag=GALLERY&pasta=" + a, Images.open_images_handler);

	},
	open_images_handler : function(xdat) {

		var a;

		try {
			a = JSON.parse(xdat);

			if (a.alert) {
				$$.erro(a.alert);
				return false;
			}

			Images.make_images(a);
		} catch (e) {
			$$.erro("Não foi possivel realizar a operação.JS1850");
			return false;
		}

	},
	del_image : function(event) {

		var a = $$.parent($$.slf(event), "TABLE");

		$$.callServer("flag=DELETE&toke=" + a.id, Folders.delete_iten_handler);
	},
	make_images : function(dat) {

		if (dat) {

			var fg = BigBang.palcoum();

			for (var f = 0; f < dat.images.length; f++) {

				var t = $$.make("table", {
					"class" : "photo3",
					"id" : dat.images[f][0],
					"data-folder" : dat.images[f][3],
					"draggable" : true
				}, fg, null, {
					"dragstart" : Images.init_drag
				});

				var tr = $$.make("tr", null, t);
				var td = $$.make("td", {
					"valign" : "bottom",
					"style" : "height:120px"
				}, tr);

				$$.make("img", {
					"src" : "imagens/minidel.png",
					"class" : "delimg",
					"draggable" : false
				}, td, null, {
					"click" : Images.del_image
				});

				var im = $$.make("img", {
					"alt" : dat.images[f][1],
					"src" : dat.images[f][2],
					"draggable" : false
				}, td);

				var tr1 = $$.make("tr", null, t);

				var td1 = $$.make("td", {
					"align" : "center"
				}, tr1, dat.images[f][1]);
			}
		} else {
			$$.erro("Impossivel realizar a operação: JS2154");
		}
	},
	init_drag : function(event) {

		DragDrop.set_drag_data(event, "menu", $$.slf(event).id);
	},
	prepare_upload : function() {

		var a,
		    b,
		    c,
		    d;

		if ( a = $$.el("botoes")) {
			b = $$.make("div", {
				"id" : "winp"
			}, a);

			c = $$.make("form", {
				"enctype" : "multipart/form-data",
				"method" : "post",
				"id" : "imgsender"
			}, b);

			d = $$.make("input", {
				"type" : "file",
				"alt" : "adicionar",
				"name" : "foto[]",
				"id" : "fotimgx",
				"multiple" : true
			}, c, null, {
				"change" : Images.start_load
			});
		}
	},
	start_load : function(event) {

		var t = $$.slf(event);

		if (t && t.files.length) {
			Images.filex = t.files;

			var a,
			    b,
			    c,
			    d,
			    g,
			    h,
			    x;

			a = $$.el("zomm");

			var tr = null;

			a.style.display = "block";

			b = $$.make("div", {
				"class" : "addimagen"
			}, a);
			$$.make("input", {
				"type" : "button",
				"value" : "cancelar",
				"class" : "bt75"
			}, b, null, {
				"click" : Images.cancel_up
			});
			c = $$.make("div", {
				"class" : "wdisplayupfiles"
			}, b);

			d = $$.make("table", {
				"id" : "displayupfiles"
			}, c);

			for ( x = 0; x < Images.filex.length; x++) {
				g = $$.make("tr", null, d);

				h = $$.make("td", {
					"class" : "td70pL"
				}, g, Images.filex[x].name, null);

				$$.make("td", {
					"class" : "td30pR"
				}, g);
			}

			Images.cells = document.querySelectorAll(".td30pR");
			Images.make_upload();
		}

	},
	make_upload : function(f) {

		var a,
		    b,
		    c;

		if (Images.filex && Images.cell < Images.filex.length) {
			var fd = null;
			c = Images.filex.item(Images.cell);

			if ( fd = new FormData()) {
				fd.append("flag", "ADD");
				fd.append("foto", c);
				fd.append("pasta", Images.folder);

				$$.make("img", {
					"src" : "imagens/upimages.GIF"
				}, Images.cells.item(Images.cell));

				Images.sender = $.ajax({
					url : BigBang.mURL + BigBang.ob.server + ".php",
					type : 'POST',
					data : fd,
					cache : false,
					dataType : 'text',
					processData : false, // Don't process the files
					contentType : false, // Set content type to false as jQuery will tell the server its a query string request
					success : function(data) {
						Images.up_complete(data)
					}
				});
			} else {
				$$.erro("Impossivel realizar a operação: JS2154");
			}
		} else {
			a = $$.el("zomm").children[0];
			b = $$.make("input", {
				"type" : "button",
				"value" : "ok",
				"class" : "bt75"
			}, a, null, {
				"click" : Images.removeZomm
			});

			a.replaceChild(b, a.children[0]);
		}
	},
	removeZomm : function() {
		var x = $$.el("zomm");
		Images.cells = null;
		Images.cell = null;
		Images.filex = null;
		x.removeChild(x.children[0]);
		x.style.display = "none";
	},
	cancel_up : function() {

		var a,
		    b,
		    c;

		Images.sender.abort();

		for (Images.cell + 1; Images.cell < Images.cells.length; Images.cell++) {
			Images.cells.item(Images.cell).innerHTML = "<span>cancelado</span>";
		}

		Images.filex = null;
		Images.make_upload();
	},
	upload_p : function(event) {

		Images.cell.innerHTML = "<span style='margin-right:10px'>" + Math.round(event.bytesLoaded / event.bytesTotal * 100) + " %</span>";

	},
	up_complete : function(datx) {

		var dat = null;

		try {
			dat = JSON.parse(datx);
		} catch (e) {
			dat = false;
		}

		if (dat.images) {
			Images.cells.item(Images.cell).children[0].src = "imagens/upok.png";
			Images.make_images(dat);
		}
		if (dat.error) {
			Images.cells.item(Images.cell).innerHTML = dat.error;
		}
		if (!dat) {
			Images.cells.item(Images.cell).innerHTML = "arquivo corrompido";
		}

		Images.cell++;
		Images.make_upload();

	}
};
/**
 * @version V1.00.220115
 *
 */
LatMove = {
	ob : null,
	nx : null,
	init : function(event) {

		var e = event || window.event;

		LatMove.ob = $$.el("palcoDois");
		LatMove.nx = $$.el("working_area");

		$$.events(document, "mousemove", LatMove.movec);
		$$.events(document, "mouseup", LatMove.stop);

	},
	movec : function(event) {

		var e = event || window.event;

		if (LatMove.ob.offsetWidth > 200) {

			var m = (e.clientX * 100) / $$.el("mainContainer").offsetWidth;

			LatMove.ob.style.width = (m) + "%";
			LatMove.nx.style.width = (99 - m) + "%";

		} else {

			var f = (LatMove.ob.offsetWidth > 200) ? LatMove.ob.offsetWidth : 201;
			LatMove.stop();
			var m = ((f + 10) * 100) / $$.el("mainContainer").offsetWidth;
			LatMove.ob.style.width = (m) + "%";
			LatMove.nx.style.width = (99 - m) + "%";
		}

	},
	stop : function(event) {

		$$.stopEvent(document, "mousemove", LatMove.movec);
		$$.stopEvent(document, "mouseup", LatMove.stop);

		return false;

	}
};
/**
 * @version V1.00.220115
 *
 */
Loader = {
	init_garbage_box : function() {

		var lx = $$.el("lx");

		$$.events(lx, "dragenter", DragDrop.over_drag_id);
		$$.events(lx, "dragover", DragDrop.over_drag_id);
		$$.events(lx, "drop", Loader.garbage_box);

	},
	destructor : function() {

		FilterResult.op.options = null;
		FilterResult.os = null;
		FilterResult.cs = null;
		Folders.qr = null;

	},
	load : function() {

		Loader.destructor();

		if (BigBang.ob.delete_on)
			Loader.init_garbage_box();

		var p = BigBang.palcoum("");

		if (p.nextSibling && p.nextSibling.id == "wFilter")
			p.parentNode.removeChild(p.nextSibling);

		Folders.sf = [];

		OperationsBar.barra(1, 0, 0, 0, 0, 0);

		SideMenu.make_icon();
		Buttons.init();

		if (BigBang.ob.folders_on) {
			BigBang.ob.folders_on();
		}

		if (BigBang.ob.submenu_on) {
			BigBang.ob.submenu_on();
		}

	},
	load_without_folders : function(event) {

		Folders.qr = null;

		if (BigBang.ob.delete_on)
			Loader.init_garbage_box();

		var a = (event) ? $$.slf(event).textContent : "";

		if (!BigBang.saveon()) {

			Folders.sf = [];
			SideMenu.make_icon();
			BigBang.ob.file_on(a);

		} else {

			if (BigBang.sairSemSalvar())
				Loader.load_without_folders(a);
		}
	},
	garbage_box : function(event) {

		try {
			event.preventDefault();
		} catch(e) {
			event.returnValue = false;
		}

		event.dataTransfer.effectAllowed = "copy";
		var data = event.dataTransfer.getData("text");

		if (DragDrop.mode === "menu") {
			Folders.delete_iten(data);
		}
		if (DragDrop.mode === "keywords") {
			Keywords.delete_word(data);
		}

		DragDrop.clean();

	}
};
/**
 * @version V1.00.220115
 *
 */
OperationsBar = {
	baddon : "imagens/adicionar22.png",
	baddoff : "imagens/adicionar21.png",
	bsaveon : "imagens/salvar22.png",
	bsaveoff : "imagens/salvar21.png",
	bediton : "imagens/editar22.png",
	beditoff : "imagens/editar21.png",
	bdeleteon : "imagens/apagar20.png",
	bdeleteoff : "imagens/adicionar26.png",
	bsearchon : "imagens/pesquisar.png",
	bsearchoff : "imagens/pesquisar2.png",
	borderon : "imagens/ordenar22.png",
	borderoff : "imagens/ordenar21.png",
	bcopon : "imagens/copiar2.png",
	bcopoff : "imagens/copiar.png",
	barra : function(btsum, btedita, btsave, btdel, end, btcopy) {

		var icon = $$.el('botoes').getElementsByTagName('img');

		if ($$.el("winp"))
					$("#winp").remove();

		if (btsum && BigBang.ob.add_on) {
			if (BigBang.ob.upfiles_on) {
				icon[0].alt = "addon";
				icon[0].src = this.baddon;
				BigBang.ob.upfiles_on();
			} else {
				if ($$.el("winp"))
					$("#winp").remove();
				icon[0].alt = "addon";
				icon[0].src = this.baddon;
				icon[0].onclick = BigBang.ob.add_on;
			}
		} else {
			icon[0].alt = "addoff";
			icon[0].src = this.baddoff;
			icon[0].onclick = BigBang.nule;
		}

		if (btedita && BigBang.ob.edit_on) {
			icon[1].alt = "editon";
			icon[1].src = this.bediton;
			icon[1].onclick = BigBang.ob.edit_on;

		} else {
			icon[1].alt = "editoff";
			icon[1].src = this.beditoff;
			icon[1].onclick = BigBang.nule;
		}

		if (btsave && BigBang.ob.save_on) {
			icon[2].alt = "saveon";
			icon[2].src = this.bsaveon;
			icon[2].onclick = BigBang.ob.save_on;

		} else {
			icon[2].alt = "saveoff";
			icon[2].src = this.bsaveoff;
			icon[2].onclick = BigBang.nule;
		}

		if (btdel && BigBang.ob.delete_on) {
			icon[3].alt = "deleteon";
			icon[3].src = this.bdeleteon;
			icon[3].onclick = BigBang.ob.delete_on;
		} else {
			icon[3].alt = "deleteoff";
			icon[3].src = this.bdeleteoff;
			icon[3].onclick = null;
		}

		if (btcopy && BigBang.ob.copy_on) {
			icon[4].alt = "copyon";
			icon[4].src = this.bcopon;
			icon[4].onclick = BigBang.ob.copy_on;
		} else {
			icon[4].alt = "copyoff";
			icon[4].src = this.bcopoff;
			icon[4].onclick = BigBang.nule;
		}

	}
};
/**
 * @version V1.00.220115
 *
 */
OrderDisplay = {
	// div com o scroller
	srb : null,
	itens : null,
	init : function() {

		if (BigBang.saveon()) {
			if (BigBang.sairSemSalvar())
				OrderDisplay.init();
		} else {
			OperationsBar.barra(1, 0, 0, 0, 0);

			var a,
			    b;

			// cria a div onde será apresentado o resultado da pesquisa
			a = $$.make("div", {
				"class" : "searchresult"
			}, BigBang.palcoum());

			// cria a div de topo
			b = $$.make("div", {
				"class" : "searchresulttop"
			}, a);

			$$.make("span", {
				"class" : "fileboxflag"
			}, b, "Ordenar");

			// cria div para apresentação da tabela com os resultados da
			// pesquisa
			$$.make("div", {
				"class" : "updowndiv"
			}, a, null, {
				"dragenter" : OrderDisplay.order_anima_up
			});

			OrderDisplay.srb = $$.make("div", {
				"class" : "searchResultbottom"
			}, a);

			$$.make("form", {
				"action" : BigBang.ob.server + ".php",
				"method" : "post"
			}, OrderDisplay.srb);

			$$.make("div", {
				"class" : "updowndiv"
			}, a, null, {
				"dragenter" : OrderDisplay.order_anima_down
			});

			SideMenu.off_editors();
			SideMenu.stage();

			OrderDisplay.result();

		}
	},
	/*
	 * solicita ao servidor os itens para ordenar
	 */
	result : function() {

		// metodo ajaxa para buscar os resultados da pesquisa no servidor
		$$.callServer("flag=ORDER", OrderDisplay.result_handler);

	},
	/*
	 * manipula o resultado do servidor
	 */
	result_handler : function(xdat) {

		var a;
		try {
			a = JSON.parse(xdat);

			if (a.result)
				OrderDisplay.itens = a.result;

			if (a.alert)
				$$.erro(a.alert);
		} catch (e) {
			$$.erro("Não foi possivel completar a operação.JS2839");
		}

		OrderDisplay.make_table();

	},
	/*
	 * cria a linha da tabela di = index do item necessário para o ordenação id = id do item sr = endereço da imagem tx = nome do item o = table onde sera criada a ordenação
	 */
	make_table : function() {

		var a,
		    b,
		    c,
		    f = OrderDisplay.itens,
		    g,
		    i;

		OrderDisplay.srb.children[0].innerHTML = "";

		// cria post flag
		$$.make("input", {
			"type" : "hidden",
			"name" : "flag",
			"value" : "SAVEORDER"
		}, OrderDisplay.srb.children[0]);

		// cria a tabela para apresentação da ordenação
		c = $$.make("table", {
			"class" : "searchresulttable"
		}, OrderDisplay.srb.children[0]);

		if (f) {
			for (g in f) {

				a = $$.make("tr", {
					"id" : f[g][0],
					"data-index" : g,
					"data-type" : "ordena",
					"class" : "searchtr"
				}, c, null, {
					"dragenter" : DragDrop.over_drag_id,
					"dragover" : DragDrop.over_drag_id,
					"drop" : OrderDisplay.final_drop
				});

				b = $$.make("td", {
					"class" : "searchresultimagetd"
				}, a, null);

				i = (f[g][1]) ? f[g][1] : "imagens/broken_image.png";

				$$.make("img", {
					"src" : i,
					"draggable" : "true",
					"class" : "ig80xp80x",
					"data-type" : "ordena"
				}, b, null, {
					"dragstart" : OrderDisplay.init_drag
				});

				// cria post
				$$.make("input", {
					"type" : "hidden",
					"name" : "iorder[]",
					"value" : f[g][0]
				}, b);

				$$.make("td", {
					"class" : "searchresulttitletd"
				}, a, f[g][2]);

			}
		}
	},
	/*
	 * inicia a operação de arrastar e guarda o index da linha arrastada na variavel index no objecto
	 */
	init_drag : function(event) {

		// define o event.target
		var t = $$.parent(this, "TR");

		var a = t.getAttribute("data-index");

		DragDrop.set_drag_data(event, "ordena", a);

	},
	/*
	 * operação de ordenaçao dos itens segundo a nova ordem depois de um item ser largado event = evento
	 */
	final_drop : function(event) {

		if (DragDrop.mode === "ordena") {
			var a,
			    b,
			    c,
			    d,
			    f;
			try {
				event.preventDefault();
			} catch(e) {
				event.returnValue = false;
			}

			event.dataTransfer.effectAllowed = "copy";

			a = parseInt(event.dataTransfer.getData("Text"));

			// linha da tabela onde houve a operação de largar
			b = $$.parent(this, "TR");

			// numero de index da linha
			c = new Number(b.getAttribute("data-index"));

			d = OrderDisplay.itens;

			// retira a linha a ser mudada da array
			// f = a elemento que foi removido
			f = d.splice(a, 1);

			// insere a linha arrastada na nova posição
			d.splice(c, 0, f[0]);

			OrderDisplay.make_table();

			OperationsBar.barra(0, 0, 1, 0, 0);

			DragDrop.clean();
		}
	},
	/*
	 * sobe a tabela
	 */
	order_anima_up : function(event) {

		var fl = -parseInt(OrderDisplay.srb.children[0].offsetHeight);

		if (-fl > parseInt(OrderDisplay.srb.offsetHeight)) {
			$(OrderDisplay.srb).animate({
				'scrollTop' : 0
			}, "slow");
		}
	},
	/*
	 * desce a tabela
	 */
	order_anima_down : function(event) {

		var fl = parseInt(OrderDisplay.srb.children[0].offsetHeight);

		$(OrderDisplay.srb).animate({
			'scrollTop' : fl - parseInt(OrderDisplay.srb.offsetHeight)
		}, "slow");

	}
};
/**
 * @version V1.00.220115
 *
 */
Messages = {
	folder : null,
	init : function() {
		BigBang.palcoum();
		Folders.sf = "";
		Folders.open_folders();
		Messages.mess_open();
	},
	mess_open : function(p) {
		Messages.folder = p || "";
		OperationsBar.barra(0, 0, 0, 0, 0, 0);

		$$.callServer("flag=FILE&pasta=" + Messages.folder, Messages.mess_open_handler);

	},
	mess_open_handler : function(xdat) {
		try {
			var a,
			    b,
			    c,
			    d = JSON.parse(xdat);

			if (d.alert)
				$$.erro(d.alert);

			if (d.result) {

				a = $$.make("div", {
					"class" : "searchresult"
				}, BigBang.palcoum());

				b = $$.make("div", {
					"class" : "searchresulttop"
				}, a);
				$$.make("span", {
					"class" : "fileboxflag"
				}, b, Messages.folder);

				c = $$.make("div", {
					"class" : "searchResultbottom"
				}, a);

				Messages.make_result(c, xdat);

			}
		} catch (e) {

			$$.erro("Não foi possivel realizar a operação.JS3032");
		}

	},
	make_result : function(m, r) {

		var a,
		    b,
		    c,
		    d,
		    f,
		    g;

		f = JSON.parse(r);
		f = f.result;

		m.innerHTML = "";

		if (f) {
			for ( a = 0; a < f.length; a++) {

				c = $$.make("div", {
					"class" : "wmessage"
				}, m, null, null);

				g = (f[a][3] == "n") ? "coloryellow" : "";

				d = $$.make("p", {
					"class" : "messagesopen " + g,
					"id" : "i:" + f[a][0],
					"draggable" : "true",
					"data-action" : "message"
				}, c, null, {
					"click" : Messages.mess_read,
					"dragstart" : Messages.init_drag
				});

				$$.make("span", null, d, f[a][1] + " | ", null);

				$$.make("span", null, d, f[a][2] + " de " + f[a][4] + " [" + f[a][5] + "]", null);

				$$.make("div", {
					"class" : "wtextmessage"
				}, c, null, null);

			}
		}
	},
	make_message : function(m, r) {

		var a,
		    b,
		    c,
		    d,
		    f,
		    g,
		    h;
		try {
			a = JSON.parse(r);
		} catch (e) {
			return false;
		}

		a = a.result;
		m.innerHTML = "";

		if (a) {
			c = $$.make("div", {
				"class" : "headermessage"
			}, m, null, null);

			$$.make("img", {
				"class" : "delbutton",
				"src" : "imagens/apagar20.png",
				"data-id" : a[0]
			}, c, null, {
				"click" : Messages.delete_message
			});

			d = $$.make("p", null, c, null, null);

			$$.make("span", null, d, "assunto: ", null);
			$$.make("span", null, d, a[3], null);

			f = $$.make("p", null, c, null, null);

			$$.make("span", null, f, "data: ", null);
			$$.make("span", null, f, a[5], null);

			f = $$.make("p", null, c, null, null);
			$$.make("span", null, f, "de: ", null);
			$$.make("span", null, f, a[1] + " [ ", null);
			$$.make("a", {
				"href" : "mailto:" + a[2]
			}, f, a[2] + " ] ", null);

			g = $$.make("div", {
				"class" : "textmessage"
			}, m, null, null);
			g.innerHTML = a[4];

			h = $$.make("p", null, c, null, null);
			$$.make("span", null, h, "anexos: ", null);

			if (a[6]) {
				$$.make("a", {
					"href" : a[7] + a[6]
				}, f, a[6] + " ] ", null);
			}

			return true;

		}

		return false;

	},
	init_drag : function(event) {

		DragDrop.set_drag_data(event, "menu", this.id);
	},
	mess_read : function(event) {
		var a = this,
		    b = this.parentNode.children[1];

		if (b.style.display !== "block") {

			if (b.innerHTML.length < 1) {

				$$.callServer("flag=READ&toke=" + this.id, function(xdat) {

					if (Messages.make_message(b, xdat)) {
						a.className = "messagesopen ";
					} else {
						$$.erro("Não foi possivel abrir mensagem.");
					}

				}, Mensagix.server);
			}
			$(b).show(400);

		} else {

			$(b).hide(400);
		}

	},
	delete_message : function(event) {

		var i = this.getAttribute("data-id");

		$$.callServer("flag=DELETE&toke=" + i, Messages.delete_handler, Mensagix.server);
	},
	delete_handler : function(xdat) {

		try {
			var a = JSON.parse(xdat);

			if (a.alert) {
				$$.erro(a.alert);
			}

			if (a.result) {
				var b = document.getElementById("i:" + a.result),
				    c = b.parentNode,
				    d = c.parentNode;
				d.removeChild(c);
			}
		} catch (e) {
			return false;
		}
	},
	ap_comment : function(o) {

		var a,
		    b = 0;
		if (o.children[0].children[1]) {
			a = o.children[0].children[1].getElementsByTagName("span");
			b = a.length;
			while (b--) {

				$$.events(a[b], "click", Messages.aproval);
			}
		}
	},
	aproval : function(event) {

		var a = event.target,
		    b;

		$$.callServer("flag=CHANGEK&value=" + a.getAttribute("data-value") + "&toke=" + a.getAttribute("data-id"), function(xdat) {
			var c,
			    d;
			b = a.parentNode;
			d = b;
			while (d.className !== "dvBf") {

				d = d.parentNode;
			}

			c = JSON.parse(xdat);
			if (c) {

				if (c.result === "online") {

					b.children[0].className = "comment_ap green";
					b.children[1].className = "comment_ap";
					d.children[0].children[2].src = "imagens/bt_check.png";

				} else {

					b.children[0].className = "comment_ap";
					b.children[1].className = "comment_ap red";
					d.children[0].children[2].src = "imagens/bt_uncheck.png";
				}
			}

		});
	}
};
/**
 * @version V1.01.270115
 *
 */
Modules = {
	// modulo da pasta abertta
	module : null,
	server : null,
	mode : null,
	// tipo de objecto para arrastar
	type : false,
	/*
	 * cria a caixa dos modulos
	 */
	init : function(t) {

		var a,
		    b,
		    c,
		    d,
		    f,
		    g,
		    h;
            
		a = $$.make("div", {
			"id" : "modulax"
		});

		b = $$.make("div", {
			"id" : "modulaxHead",
			"draggable" : false
		}, a);

		$$.make("img", {
			"class" : "igM7",
			"src" : "imagens/modulos_on.png",
			"draggable" : false
		}, b);

		c = $$.make("div", {
			"id" : "modulaxBottom"
		}, a);


		t.appendChild(a);

		Modules.make_folders();

	},
	on : function() {
		var a = SideMenu.editors_on();

		if (!$$.el("modulax"))
			Modules.init(a);

		try {
			$$.el("modulax").style.display = "block";

		} catch(e) {
			return false;
		}

	},
	/*
	 * cria pastas que corresponde a cada um dos modulos disponiveis
	 */
	make_folders : function() {

		try {

			var gf = $$.el("modulaxHead");

			var gu = $$.make("select", {
				"id" : "moduleFolders"
			}, gf, null, {
				"change" : Modules.load_folders
			});

			$$.make("option", {
				"value" : "",
				"draggable" : false
			}, gu, "", null);

			for (var b in BigBang.ob.modulos) {

				$$.make("option", {
					"value" : b,
					"draggable" : false
				}, gu, b, null);

			}

		} catch (e) {

			$$.erro("Impossivel realizar a operação: JS3199");

		}
	},
	/**
	 /*
	 * carrega o modulo selecionado
	 */
	load_folders : function(event) {
		// modulo escolhido
		var g,
		    b,
		    h,a;

		b = this.value;

		$$.el("modulaxBottom").innerHTML = "";

		if (b && BigBang.ob.modulos[b])
		{
			switch (typeof BigBang.ob.modulos[b].module)
			{
			case "object":
				Modules.server = BigBang.ob.modulos[b].module.server;
				Modules.module = BigBang.ob.modulos[b].module.module;
				break;
			case "string":
				Modules.server = BigBang.ob.modulos[b].module;
				Modules.module = null;
				break;
			default:
				return false;
				break;
			}

			Modules.type = BigBang.ob.modulos[b].type;
			Modules.mode = BigBang.ob.modulos[b].mode;

			a = (BigBang.ob.modulos[b].flag) ? BigBang.ob.modulos[b].flag : "MODULE";

			//inicia o elementos que vão receber os itens largados
			g = $$.el("mainEdition").getElementsByTagName("div");
			h = g.length;

			while (h--)
			{
				if (g[h].getAttribute("data-type") == Modules.type)
				{
					$$.events(g[h], "dragenter", DragDrop.over_drag_id);
					$$.events(g[h], "dragover", DragDrop.over_drag_id);
					$$.events(g[h], "drop", Modules.drop);
				}
			}

			$$.callServer("flag="+a, Modules.load_folders_handler, Modules.server, Modules.module);

		}
	},
	/*
	 * manipula o modulo carregado
	 */
	load_folders_handler : function(xdat) {
		try {
			var a,
			    b,
			    c,
			    d,
			    f,
			    g,
			    h,
			    i,
			    j,
			    m,
			    n,
			    o,
			    p,
			    q,
			    x,
			    z;

			a = JSON.parse(xdat);

			if(a.alert)
			{
				alert(a.alert);
				return false;
			}

			b = $$.el("modulaxBottom");

			c = $$.make("ul", {
				"class" : "unidade",
				"id" : "foldersTreeModule"
			}, b);

			for (d in a) {

				h = a[d];
				if (d) {
					f = $$.make("li", {
						"class" : "linha"
					}, c, d, {
						"click" : Modules.open_folders
					});

					g = $$.make("ul", {
						"class" : "pastaint",
						"id" : "f:" + d
					}, f);

					for (i in h) {
						j = $$.make("li", {
							"class" : "linhaint",
							"title" : h[i]['status'],
							"id" : "i:" + h[i]['id']
						}, g, null, {
							"dragstart" : Modules.init_drag,
							"dragend" : DragDrop.clean
						});
						x = (h[i]['image']) ? h[i]['image'] : "imagens/sem_photo.png";
						$$.make("img", {
							"class" : "ig40x",
							src : x,
							"draggable" : false
						}, j);

						$$.text(j, h[i]['name']);

						j.draggable = true;

						j.style.listStyleImage = (h[i]['status'] === "online") ? Folders.fo : Folders.fc;
					}
				}
			}

			p = $$.make("ul", {
				"class" : "pastaintsolo"
			}, b);
			for (m in a) {

				n = a[m];

				if (!m) {

					for (o in n) {
						q = $$.make("li", {
							"class" : "linhaint",
							"title" : n[o]['status'],
							"id" : "i:" + n[o]['id']
						}, p, null, {
							"dragstart" : Modules.init_drag,
							"dragend" : DragDrop.clean
						});
						z = (n[o]['image']) ? n[o]['image'] : "imagens/sem_photo.png";
						$$.make("img", {
							"class" : "ig40x",
							"src" : z,
							"draggable" : false
						}, q);

						$$.text(q, n[o]['name']);

						q.draggable = true;

						q.style.listStyleImage = (n[o]['status'] === "online") ? Folders.fo : Folders.fc;
					}
				}
			}
		} catch(e) {

			$$.erro("Não foi possivel realizar a operação.JS3353")
		}
	},
	open_folders : function(event) {

		a = $$.slf(event);

		if (a.style.listStyleImage.indexOf("folderon.png") > -1) {

			if (a.children[0]) {
				a.style.listStyleImage = Folders.fcu;
				$(a.children[0]).slideUp();
			}

		}
		// se estiver o icon de aberto na pasta fecha a pasta
		else {

			if (a.children[0]) {
				a.style.listStyleImage = Folders.fou;
				$(a.children[0]).slideDown();
			}

		}
	},
	/*
	 * inicia a operação do item selecionado
	 */
	init_drag : function(event) {

		var i,
		    t;

		i = this.id;

		t = (Modules.type) ? Modules.type : "newsl";

		DragDrop.set_drag_data(event, t, i);
	},
        /**
         * Insere um novo elemento numa div. A função que chama no servidor depende é definida pela string do atributo data-service do 
         * elemento onde foi largado. Por questões de retroatividade a função chamada no servidor tambem pode ser definida na propriedade "mode" do 
         * objecto que configura o modulo. É preferivel usar o atributo data-service porque permite que um mesmo modulo tenha duas ou mais respostas diferentes.
         * 
         * @param {type} event
         * 
         * @returns {Boolean}
         * 
         */
	drop : function(event) {
console.log(DragDrop.mode)
console.log(Modules.type)
console.log(event);
		if (DragDrop.mode == Modules.type)
		{
			var a,b,c,
			    t;

			try
			{
				event.preventDefault();
			}
			catch(e)
			{
				event.returnValue = false;
			}

			a = event.dataTransfer.getData("Text");
                       
			t = $$.slf(event);
                        
                        b = t.getAttribute("data-service");
                        
                        c = (b) ? b : Modules.mode;

			$$.callServer("flag=" + c + "&toke=" + a, function(xdat)
			{
				$(t).append(xdat);
				Buttons.init_images();

			}, Modules.server, Modules.module);

			DragDrop.clean();

			return false;
		}

	}
};
/**
 * @version V1.01.270115
 *
 */
NavMenu = {
	init : function() {
		var a = $$.el("wNavMenu"),
		    b = 0,
		    c = a.children[0].children,
		    d,
		    g = 0;
console.log(a.children[0].offsetWidth)
		for ( d = 0; d < c.length; d++) {
			g += 2 + c[d].offsetWidth;
		}
		a.children[0].style.width = g + "px";
		b = parseInt(a.offsetWidth - g);

		$$.el("we").onmouseover = function() {
			NavMenu.init();
			if (a.children[0].offsetWidth > a.offsetWidth) {
				$(a.children[0]).animate({
					"marginLeft" : b
				}, 2000);
			} else {
				a.children[0].style.margin = "auto";
			}
		}

		$$.el("wd").onmouseover = function() {
			NavMenu.init();
			if (a.children[0].offsetWidth > a.offsetWidth) {
				$(a.children[0]).animate({
					"marginLeft" : 5
				}, 2000);
			} else {
				a.children[0].style.margin = "auto";
			}
		}
		$$.el("we").onmouseout = function() {
			$(a.children[0]).stop();
		};
		$$.el("wd").onmouseout = function() {
			$(a.children[0]).stop();
		};
	},
	select : function(event) {

		var a,
		    b,
		    c;

		$$.el("subMenu").innerHTML = "";

		a = $$.parent($$.slf(event), "LI");

		b = a.parentNode.querySelectorAll("li");

		for ( c = 0; c < b.length; c++) {
			b[c].className = "mnavli";
		}

		a.className = "mnavlisel";

		BigBang.ob.start_on();

	},
	sub_menu : function() {

		var a,
		    b,
		    c,
		    d,
		    f;

		var d = $$.make("div", {
			"id" : "wSubMenu"
		}, $$.el("subMenu"));

		a = $$.make("ul", null, d);

		for (c in BigBang.ob.submenu_options) {

			var z = $$.make("li", {
				"class" : "lisubmenu"
			}, a, c, {
				"click" : NavMenu.sub_menu_select
			});

			z.opener = BigBang.ob.submenu_options[c];
		}

	},

	sub_menu_select : function(event) {

		var a = $$.slf(event),
		    z = true;

		if (BigBang.saveon())
			z = BigBang.sairSemSalvar();

		if (z) {
			f = $$.el("subMenu").querySelectorAll("li");

			for (var r = 0; r < f.length; r++) {

				f[r].className = "lisubmenu";
			}

			a.className = "lisubmenusel";
			a.opener(event);

		}
	}
};
/**
 * @version V1.00.220115
 *
 */
Newsletter = {
	//contact stage
	cs : null,
	//folders group
	fg : null,
	//folders news
	fn : null,
	//newsletters stage
	ns : null,
	//group to
	gt : null,
	//letter to
	lt : null,
	//select all
	sa : null,
	init : function() {

		OperationsBar.barra(0, 0, 0, 0, 0);

		var a,
		    b,
		    c,
		    d,
		    f,
		    g,
		    h,
		    i,
		    j;

		a = BigBang.palcoum();

		b = $$.make("div", {
			"id" : "wnewsletter"
		}, a);

		c = $$.make("form", {
			"action" : BigBang.ob.server,
			"method" : "post"
		}, b);

		d = $$.make("div", {
			"id" : "hnewsletter"
		}, c);

		f = $$.make("div", {
			"id" : "infonwl"
		}, d);

		$$.make("span", {
			"class" : "sp15b"
		}, $$.make("p", {
			"class" : "dv95pL00"
		}, f), "Enviar newsletter");

		g = $$.make("p", null, f);
		$$.make("span", null, g, "enviar newsletter:");
		Newsletter.lt = $$.make("span", {
			"id" : "letterto",
			"class" : "sp15b"
		}, g);

		h = $$.make("p", null, f);
		$$.make("span", null, h, "para o grupo:");
		Newsletter.gt = $$.make("span", {
			"id" : "groupto",
			"class" : "sp15b"
		}, h);

		$$.make("input", {
			"type" : "hidden",
			"name" : "lettertosend",
			"value" : "",
			"id" : "lettertosend"
		}, f);

		$$.make("div", {
			"id" : "sendbt"
		}, d, null, {
			"click" : Newsletter.submit_newsletter
		});

		Newsletter.fg = $$.make("div", {
			"id" : "pastaGrupos"
		}, c);

		i = $$.make("div", {
			"id" : "wcontatos"
		}, c);

		j = $$.make("div", {
			"id" : "selctall"
		}, i)

		Newsletter.sa = $$.make("input", {
			"type" : "checkbox",
			"value" : 1,
			"id" : "___selectall"
		}, $$.make("div", {
			"class" : "wselctall"
		}, j, "Selecionar todos"), null, {
			"click" : Newsletter.select_all
		});

		Newsletter.cs = $$.make("div", {
			"id" : "contactos"
		}, i);

		Newsletter.fn = $$.make("div", {
			"id" : "pastaNews"
		}, c);

		Newsletter.ns = $$.make("div", {
			"id" : "letters"
		}, c);

		if (Newsletter.cs && Newsletter.fg && Newsletter.fn && Newsletter.ns) {
			$$.callServer("flag=FOLDER", Newsletter.load_folders_groups, "m_grupo");
			$$.callServer("flag=FOLDER", Newsletter.load_folders_newsletter, "m_newsletter");
		} else {
			$$.erro("Não foi possivel realizar a opreação.JS3655");
		}

	},
	load_folders : function(xdat, f, x) {

		var a,
		    b,
		    c,
		    d,
		    g,
		    i,
		    j,
		    m,
		    o,
		    p,
		    l;

		try {
			a = JSON.parse(xdat);
			b = x;

			if (a.alert) {
				$$.erro(a.alert);
				return false;
			}
		} catch (e) {
			$$.erro("Não foi possivel realizar a operação.JS4075");
			return false;
		}

		c = $$.make("ul", {
			"class" : "unidade",
			"id" : "foldersTreeGroup"
		}, b);

		for (d in a) {
			if (d) {
				l = $$.make("li", {
					"class" : "linha",
					"draggable" : false

				}, c, d, {
					"click" : Modules.open_folders
				});

				g = $$.make("ul", {
					"class" : "pastaint",
					"id" : "f:" + d
				}, l);

				Newsletter.make_line_folder(g, a[d], f);
			}
		}

		p = $$.make("ul", {
			"class" : "pastaintsolo"
		}, b);

		for (m in a) {
			if (!m) {
				Newsletter.make_line_folder(p, a[m], f);
			}
		}
	},
	make_line_folder : function(a, b, f) {

		var b,
		    d;

		for (c in b) {

			d = $$.make("li", {
				"class" : "linhaint",
				"title" : b[c]['status'],
				"id" : "i:" + b[c]['id'],
				"data-type" : "menu"
			}, a, b[c]['name'], {
				"click" : f
			});

			d.draggable = false;

			d.style.listStyleImage = (b[c]['status'] === "online") ? Folders.fo : Folders.fc;
		}

	},
	load_folders_groups : function(xdat) {

		Newsletter.load_folders(xdat, Newsletter.select_group, Newsletter.fg);
	},
	select_group : function(event) {

		var a,
		    b,
		    c,
		    d;

		a = $$.slf(event);
		d = a.className;

		b = Newsletter.fg.querySelectorAll("li");

		for ( c = 0; c < b.length; c++) {
			if (b[c].className == "linhaints") {
				b[c].className = "linhaint";
			}
		}

		if (a && d != "linhaints") {
			a.className = "linhaints";

			Newsletter.gt.innerHTML = a.innerHTML;
			Newsletter.gt.setAttribute("data-group", a.id);

			$$.callServer("flag=GROUP&toke=" + a.id + "&letter=" + $$.el("lettertosend").value, Newsletter.select_group_handler);
		} else {
			Newsletter.clean_group();
		}

	},
	select_group_handler : function(xdat) {
		console.log(xdat);
		try {
			var a,
			    b,
			    c;
			Newsletter.cs.innerHTML = "";
			Newsletter.sa.checked = false;
			a = JSON.parse(xdat);

			if (a.contacts) {
				c = a.contacts;

				for ( b = 0; b < c.length; b++) {

					Newsletter.make_member(c[b]['id'], c[b]['name'], c[b]['check']);

				}

				Newsletter.ready_to_send();

			} else {
				if (a.error) {

					$$.erro(a.error);
				}
				if (a.alert) {

					$$.erro(a.alert);
				}

				Newsletter.clean_group();
				Newsletter.select_group();

			}
		} catch (e) {
			$$.erro("Impossivel realizar a operação. JS4174.");
			Newsletter.clean_group();
		}

	},
	select_all : function(event) {

		var c = document.getElementsByName("contact");

		for (var b = 0; b < c.length; b++) {
			if (c[b].getAttribute("type") == "checkbox")
				c[b].checked = $$.slf(event).checked;
		}

		Newsletter.ready_to_send();
	},
	load_folders_newsletter : function(xdat) {
		Newsletter.load_folders(xdat, Newsletter.select_letter, Newsletter.fn);
	},
	select_letter : function(event) {

		var a,
		    b,
		    c,
		    d;

		a = $$.slf(event);
		c = a.className;

		b = Newsletter.fn.querySelectorAll("li");

		for ( d = 0; d < b.length; d++) {
			if (b[d].className == "linhaints") {
				b[d].className = "linhaint";
			}
		}

		if (a && c != "linhaints") {
			a.className = "linhaints";
			$$.callServer("flag=CLONE&toke=" + a.id, function(event) {
				Newsletter.select_letter_handler(event, a);

			});
		} else {

			Newsletter.ns.innerHTML = "";
			$$.el("lettertosend").value = "";
			Newsletter.lt.innerHTML = "";
			Newsletter.ready_to_send();
		}

	},
	select_letter_handler : function(xdat, tg) {

		Newsletter.ns.innerHTML = xdat;
		$$.el("lettertosend").value = tg.id;
		Newsletter.lt.innerHTML = tg.innerHTML;

		Newsletter.confirm_group();
		Newsletter.ready_to_send();

	},
	submit_newsletter : function() {

		var a,
		    b,
		    c,
		    d,
		    f,
		    g,
		    h,
		    i,
		    j;

		a = $$.el("lettertosend");
		h = $$.el("groupto");
		j = $$.el("sendbt");
		b = document.getElementsByName("contact");
		d = null;

		for ( c = 0; c < b.length; c++) {
			if (b[c].checked) {
				if (!d) {
					d = b[c].value;
				} else {
					d += "," + b[c].value;
				}
			}

		}

		f = $$.el("pastaGrupos").getElementsByTagName("li");

		for ( g = 0; g < f.length; g++) {

			if (f[g].innerHTML === h.innerHTML) {

				i = f[g].id;
			}

		}

		if (Newsletter.ready_to_send()) {

			if (d) {
				if (a.value) {

					j.style.backgroundColor = "yellow";
					j.style.backgroundImage = "url(imagens/img_preloag.gif)";
					$$.callServer("flag=SEND&dados=" + d + "&linque=" + a.value + "&grupo=" + i, Newsletter.submit_handler);

				} else {

					$$.erro("Tem que escolher uma newsletter.");
				}
			} else {

				$$.erro("Tem que seleccionar pelo menos um contacto.");
			}
		}

	},
	submit_handler : function(xdat) {
		var a,
		    b,
		    c;
		a = JSON.parse(xdat);
		if (a.enviados) {

			c = document.getElementsByTagName("input");

			for ( b = 0; b < c.length; b++) {
				if (c[b].getAttribute("type") == "checkbox" && a.enviados.indexOf(parseInt(c[b].value)) !== -1)
					c[b].checked = false;
			}
			Newsletter.ready_to_send();
		}
		if (a.alert) {

			$$.erro(a.alert);
		}

	},
	clean_group : function() {
		$$.el("groupto").innerHTML = "";
		$$.el("groupto").setAttribute("data-group", "");
		$$.el("contactos").innerHTML = "";
		Newsletter.ready_to_send();
	},
	confirm_group : function() {

		var a,
		    b;

		a = $$.el("groupto").getAttribute("data-group");
		b = $$.el("lettertosend").value;

		if (a && b) {
			$$.callServer("flag=GROUP&toke=" + a + "&letter=" + $$.el("lettertosend").value, Newsletter.select_group_handler);
		}
	},
	make_member : function(id, tx, ch) {

		var a,
		    b,
		    c;

		a = (ch) ? "checked" : "";

		b = $$.make("p", {
			"id" : id,
			"class" : "plinhaint",
			"data-type" : "selCont,selPasta",
			"draggable" : "false"
		}, $$.el("contactos"));

		c = $$.make("input", {
			"type" : "checkbox",
			"name" : "contact",
			"value" : id
		}, b, null, {
			"click" : Newsletter.ready_to_send
		});

		$$.text(b, tx);
		if (ch)
			c.setAttribute("checked", true);

	},
	ready_to_send : function() {

		var a,
		    b,
		    c,
		    d,
		    f;

		a = $$.el("sendbt");
		b = $$.el("groupto");
		c = $$.el("letterto");
		d = document.getElementsByName("contact");

		a.style.backgroundImage = "url(imagens/env_news.png)";
		a.style.backgroundColor = "red";

		if (b.innerHTML && c.innerHTML) {
			for ( f = 0; f < d.length; f++) {
				if (d[f].checked) {
					a.style.backgroundColor = "green";
					return true;

				}
			}
		}

		return false;

	}
};
/**
 * @version V1.00.220115
 *
 */
DoNewsletter = {

	init : function() {

		$$.callServer("flag=ADD", DoNewsletter.init_handler);
	},
	init_handler : function(xdat) {

		BigBang.palcoum(xdat);
		OperationsBar.barra(0, 0, 1, 0, 0);

		$(".linhaints").removeClass("linhaints").addClass("linhaint");

		if (BigBang.ob.starter) {

			BigBang.ob.starter();
		}

		SideMenu.init_editors();

		DoNewsletter.init_selection();
	},
	init_selection : function() {

		var a,
		    b,
		    c;

		a = document.forms[0].getElementsByTagName("div");

		for ( b = 0; b < a.length; b++) {

			if (a[b].hasAttribute("data-type")) {
				if (a[b].getAttribute("data-type") === "newsl") {
					c = a[b];
					break;
				}
			}
		}

		$$.events(c, "dragenter", DragDrop.over_drag_id);
		$$.events(c, "dragover", DragDrop.over_drag_id);
		$$.events(c, "drop", Modules.drop);
		$$.events(c, "keypress", DoNewsletter.key_press);
	},
	key_press : function(event) {

		if (event.keyCode == 13) {

			var a,
			    b,
			    d;

			event.preventDefault();

			a = window.getSelection();
			d = a.getRangeAt(0);
			b = document.createElement("br");
			d.insertNode(b);
			d.setEndAfter(b);
			d.setStartAfter(b);
			a.removeAllRanges();
			a.addRange(d);
		}
	}
};
/**
 * @version V1.01.280115
 *
 */
NewsletterGroups = {
	init : function() {
		var z = $$.el("zomm");

		var dm = $$.make("div", {
			"class" : "wzoom"
		}, z);

		$$.make("img", {
			"src" : "imagens/minidel.png",
			"class" : "zommout"
		}, dm, null, {
			"click" : Contacts.close
		});

		var sp = $$.make("div", {
			"class" : "zommsel"
		}, dm, null, {
			"click" : NewsletterGroups.add_open_group
		});

		$$.make("img", {
			"src" : "imagens/g_aberto.png",
			"class" : "zselimg"
		}, sp);
		$$.make("span", {
			"class" : "zselp"
		}, sp, "Aberto");

		var se = $$.make("div", {
			"class" : "zommsel"
		}, dm, null, {
			"click" : NewsletterGroups.add_close_group
		});
		$$.make("img", {
			"src" : "imagens/g_fechado.png",
			"class" : "zselimg"
		}, se);
		$$.make("span", {
			"class" : "zselp"
		}, se, "Fechado");

		z.style.display = "block";
	},
	close : function() {
		var z = $$.el("zomm");
		z.style.display = "none";
		z.innerHTML = "";
	},
	add_open_group : function() {
		$$.callServer("flag=ADDOPEN", NewsletterGroups.add_group_handler);
	},
	add_close_group : function() {
		$$.callServer("flag=ADDCLOSE", NewsletterGroups.add_groupc_handler);
	},
	add_group_handler : function(xdat) {

		NewsletterGroups.close();

		OperationsBar.barra(0, 0, 1, 0, 0);

		BigBang.palcoum(xdat);

		NewsletterGroups.init_open_group();

	},
	add_groupc_handler : function(xdat) {
		NewsletterGroups.close();

		OperationsBar.barra(0, 0, 1, 0, 0);

		BigBang.palcoum(xdat);

		NewsletterGroups.init_file();

	},
	init_open_group : function() {

		$$.events($$.el("members_data_option1"), "change", NewsletterGroups.init_open_group_handler);
		$$.events($$.el("members_data_option2"), "change", NewsletterGroups.init_open_group_handler);
	},
	init_open_group_handler : function(event) {
		if (event.currentTarget.nodeName !== "SELECT")
			return false;

		var a = event.currentTarget.value;
		var b = $(event.currentTarget).attr("data-target");

		if (a) {
			$.ajax({
				type : "POST",
				url : BigBang.mURL + "m_grupo.php",
				cache : false,
				data : "flag=GROUP&index=" + a,
				dataType : "text",
				success : function(dat) {
					NewsletterGroups.make_op(dat, b);
				},
				error : function(dat) {

				}
			});
		} else {
			NewsletterGroups.make_op(false, b);
		}

	},
	fetch_group_members : function() {

		var a = $$.el("members_data_option1"),
		    b = $$.el("members_data_option2"),
		    c = $$.el(a.getAttribute("data-target")).value,
		    d = $$.el(b.getAttribute("data-target")).value,
		    f = a.value,
		    g = b.value,
		    h = "",
		    j = "";

		if (f)
			h = f + "=" + c + "&";

		if (g)
			j = g + "=" + d;

		if (h || j) {

			$.ajax({
				type : "POST",
				url : BigBang.mURL + "m_grupo.php",
				cache : false,
				data : "flag=OPENMEMBERS&" + h + j,
				dataType : "text",
				success : function(dat) {
					$$.el("members_data_all_members").innerHTML = dat;
				},
				error : function(dat) {

				}
			});
		} else {

                    $$.el("members_data_all_members").innerHTML = "";
                }


	},
	make_op : function(a, b) {
		var c;
		try {
			c = JSON.parse(a);

		} catch (e) {
			c = false;
		}

		var g = document.getElementById(b);
		var gc = g.className;
		var y = g.parentNode;

		var w = $$.make("select", {
			"id" : b,
			"name" : b,
			"class" : gc
		}, null, null, {
			"change" : NewsletterGroups.fetch_group_members
		});

		$$.make("option", {
			"value" : ""
		}, w, "");

		if (c) {
			for (var f = 0; f < c.result.length; f++) {
				$$.make("option", {
					"value" : c.result[f]
				}, w, c.result[f]);
			}
		}

		y.removeChild(g);
		y.appendChild(w);

		NewsletterGroups.fetch_group_members();

	},
	init_file : function() {

		$$.callServer("flag=CLOSEGROUP", NewsletterGroups.load_folders_handler);

		var a = $$.el("group_members");
		$$.events(a, "dragenter", DragDrop.over_drag_id);
		$$.events(a, "dragover", DragDrop.over_drag_id);
		$$.events(a, "drop", NewsletterGroups.drop_group);

	},
	load_folders_handler : function(xdat) {
		var a,
		    b,
		    c,
		    d,
		    f,
		    g,
		    h,
		    i,
		    j,
		    m,
		    n,
		    o,
		    p,
		    q;
		try {
			a = JSON.parse(xdat);
		} catch (e) {
			$$.erro("Não foi posivel realizar a operação.JS4806");
			return false;
		}

		b = $$.el("group_fold");

		c = $$.make("ul", {
			"class" : "unidade",
			"id" : "foldersTreeGroup"
		}, b);

		for (d in a) {
			h = a[d];
			if (d) {
				f = $$.make("li", {
					"class" : "linha",
					"draggable" : true

				}, c, d, {
					"click" : Modules.open_folders,
					"dragstart" : NewsletterGroups.init_drag_folder
				});

				g = $$.make("ul", {
					"class" : "pastaint",
					"id" : "f:" + d
				}, f);

				for (i in h) {
					j = $$.make("li", {
						"class" : "linhaint",
						"title" : h[i]['status'],
						"id" : "m:" + h[i]['id']
					}, g, h[i]['name'], {
						"click" : NewsletterGroups.add_element,
						"dragstart" : NewsletterGroups.init_drag,
						"dragend" : DragDrop.clean
					});

					j.draggable = true;

					(h[i]['status'] === "online") ? j.style.listStyleImage = Folders.fo : j.style.listStyleImage = Folders.fc;
				}
			}
		}

		p = $$.make("ul", {
			"class" : "pastaintsolo"
		}, b);

		for (m in a) {
			n = a[m];

			if (!m) {
				for (o in n) {
					q = $$.make("li", {
						"class" : "linhaint",
						"title" : n[o]['status'],
						"id" : "m:" + n[o]['id']
					}, p, n[o]['name'], {
						"click" : NewsletterGroups.add_element,
						"dragstart" : NewsletterGroups.init_drag,
						"dragend" : DragDrop.clean
					});

					q.draggable = true;

					(n[o]['status'] === "online") ? q.style.listStyleImage = Folders.fo : q.style.listStyleImage = Folders.fc;
				}
			}
		}

	},
	init_drag : function(event) {
		DragDrop.start_drag_id(event, "selCont", event.currentTarget);
	},
	init_drag_folder : function(event) {
		DragDrop.start_drag_id(event, "selPasta", event.target.children[0].id);
	},
	drop_group : function(event) {
		var a,
		    b,
		    c;
		if (event.dataTransfer.getData("selPasta")) {
			a = $$.el(event.dataTransfer.getData("selPasta"));

			for ( b = 0; b < a.children.length; b++) {
				NewsletterGroups.make_member(a.children[b].id, a.children[b].innerHTML);
			}

		}
		if (event.dataTransfer.getData("selCont")) {
			c = event.dataTransfer.getData("selCont");
			NewsletterGroups.make_member(c.id, c.innerHTML);
		}

	},
	make_member : function(id, tx) {
		var a,
		    b,
		    c = false;

		a = $$.el("group_members").getElementsByTagName("p");

		for ( b = 0; b < a.length; b++) {
			if (a[b].id == id) {
				c = true;
				break;
			}
		}

		if (!c) {
			var pr = $$.make("p", {
				"id" : "p" + id,
				"class" : "plinhaint",
				"data-type" : "selCont,selPasta",
				"draggable" : "true"
			}, $$.el("group_members"), null, {
				"drop" : NewsletterGroups.drop_group
			});
			$$.make("input", {
				"type" : "hidden",
				"name" : "members[]",
				"value" : id
			}, pr);
			$$.make("img", {
				"src" : "imagens/bt_del.png",
				"class" : "plinhaintimg"
			}, pr, null, {
				"click" : NewsletterGroups.delete_p,
				"mouseover" : NewsletterGroups.change_color,
				"mouseout" : NewsletterGroups.back_color
			});

			$$.text(pr, tx);
		}
	},
	add_element : function(ev) {
		NewsletterGroups.make_member(ev.target.id, ev.target.innerHTML);
	},
	change_color : function(event) {
		event.target.parentNode.style.color = "red";
	},
	back_color : function(event) {
		event.target.parentNode.style.color = "#ddd";
	},
	delete_p : function(event) {
		var a = event.target.parentNode;
		a.parentNode.removeChild(a);
	},
	init_edit : function() {
		var a = false,
		    b = false,
		    c,
		    d;

		if (document.getElementById("members_data_option1") && document.getElementById("members_data_option2") && document.getElementById("members_data_select1") && document.getElementById("members_data_select2"))
			a = true;

		if (document.getElementById("group_members"))
			b = true;

		if (!a && !b) {
			$$.erro("Não é possivel realizar operação - JVS5040");
			document.getElementById('save').alt = "";
			File.show($("#mainEdition").attr("data-id"));
			return false;
		}
		if (a) {
			NewsletterGroups.init_open_group();

			$$.events($$.el("members_data_select1"), "change", NewsletterGroups.fetch_group_members);
			$$.events($$.el("members_data_select2"), "change", NewsletterGroups.fetch_group_members);

			return false;
		}
		if (b) {
			c = $$.el("group_members").getElementsByTagName("p");

			for (var x = 0; x < c.length; x++) {
				d = c[x].getElementsByTagName("img");
				c[x].getElementsByTagName("input")[0].name = "members[]";

				$$.events(d[0], "click", NewsletterGroups.delete_p);
				$$.events(d[0], "mouseover", NewsletterGroups.change_color);
				$$.events(d[0], "mouseout", NewsletterGroups.back_color);
				$$.events(c[x], "drop", NewsletterGroups.drop_group);
			}

			NewsletterGroups.init_file();
		}

	}
};
/**
 * @version V1.00.220115
 *
 */
Movex = {
	ob : null,
	ax : 0,
	ay : 0,
	/*
	 *
	 */
	startmove : function(event) {

		var e = event || window.event;

		Movex.ob = event.currentTarget;

		$$.events($$.el("palco"), "mousemove", Movex.move);

		Movex.ax = e.clientX - Movex.ob.parentNode.offsetLeft;
		Movex.ay = e.clientY - Movex.ob.parentNode.offsetTop;

	},
	stop : function() {

		$$.stopEvent($$.el("palco"), "mousemove", Movex.move);
		$$.el("palco").onmousemove = null;

	},
	move : function(event) {
		var e = event || window.event;

		Movex.ob.parentNode.style.top = ((e.clientY - Movex.ay)) + "px";
		Movex.ob.parentNode.style.left = (e.clientX - Movex.ax) + "px";

	}
};
/**
 * @version V1.01.270115
 *
 */
Photo = {
	init : function(a) {

		if (a) {

			var F = $$.make("div", {
				"id" : "fotogaleriax"
			});
			var gh = $$.make("div", {
				"id" : "galleryHead",
				"draggable" : false
			}, F, null, null);

			$$.make("img", {
				"class" : "igM7",
				"src" : "imagens/fotos_on.png",
				"draggable" : false
			}, gh);

			var gb = $$.make("div", {
				"id" : "galleryBottom"
			}, F);

			var gs = $$.make("div", {
				"id" : "galleryStage"
			}, gb);

			a.appendChild(F);

			Photo.make_folders();

		}
	},
	on : function() {

		var a = SideMenu.editors_on();

		if (!$$.el("fotogaleriax"))
			Photo.init(a);

		try {

			$$.el("fotogaleriax").style.display = "block";
		} catch(e) {
			return false;
		}

	},
	off : function() {
		try {
			$$.el("palco").style.height = "100%";
			$$.el("fotogaleriax").style.display = "none";
		} catch (e) {
			return false;
		}
	},
	load_images : function(event) {

		var pasta = "";

		$$.el("galleryStage").innerHTML = "";

		if (event) {
			pasta = event.target.value;
		}

		$$.callServer("flag=GALLERY&pasta=" + pasta, Photo.load_folders_handler, "m_imagens", false);
	},
	load_folders_handler : function(im) {
		try {

			var dat = JSON.parse(im);

			if (dat.alert) {
				$$.erro(dat.alert);
				return false;
			}

			if (dat.images) {

				var fg = $$.el("galleryStage");

				for (var f = 0; f < dat.images.length; f++) {
					var a = dat.images[f][2].split("/").pop();
					var b = a.split(".").pop();

					if (b.toLowerCase() == "jpg" || b.toLowerCase() == "png" || b.toLowerCase() == "gif") {
						var t = $$.make("table", {
							"class" : "galleryImg"
						}, fg);
						var tr = $$.make("tr", null, t);
						var td = $$.make("td", {
							"valign" : "middle",
							"style" : "height:90%"
						}, tr);
						var im = $$.make("img", {
							"id" : a.replace("mini_", ""),
							"alt" : dat.images[f][1],
							"title" : dat.images[f][1],
							"src" : dat.images[f][2]
						}, td, null, {
							"dragstart" : Photo.start_drag_src
						});
						var tr1 = $$.make("tr", null, t);
						var td1 = $$.make("td", {
							"align" : "center"
						}, tr1, dat.images[f][1]);
					}
				}

			}

		} catch (e) {
			$$.erro("Impossivel realizar a operação: JS001PH");
		}

	},
	make_folders : function() {

		$$.callServer("flag=GALLERYF", Photo.make_folders_handler, "m_imagens", false);

	},
	make_folders_handler : function(dat) {

		try {
			var dat = JSON.parse(dat);

			if (dat.alert) {
				$$.erro(da.alert);
				return false;
			}

			if (dat) {

				var gf = $$.el("galleryHead");

				var gu = $$.make("select", {
					"id" : "imageFolders"
				}, gf, null, {
					"change" : Photo.load_images
				});

				for (var f in dat) {

					$$.make("option", {
						"value" : f,
						"draggable" : false
					}, gu, f, null);

				}
				Photo.load_images();

			}
		} catch (e) {

			$$.erro("Impossivel realizar a operação: JS002PH");

		}
	},
	open_folders : function(event) {

		var gf = $$.el("galleryFolders");
		var gu = null;
		var slf = $$.slf(event);

		if (slf.alt == "open") {
			gf.style.width = "150px";
			slf.alt = "close";
			slf.src = "imagens/fecha.png";
			gf.children[1].style.display = "block";
		} else {
			gf.style.width = "25px";
			slf.alt = "open";
			slf.src = "imagens/abre.png";
			gf.children[1].style.display = "none";
		}
	},
	start_drag_src : function(evt) {

		var s = $$.slf(evt);
		// retira o src do item que vai mudar de pasta
		var i = (s.src) ? s.src : null;
		if (i) {

			i = i.replace("mini_", "");
			evt.dataTransfer.effectAllowed = "copy";
			evt.dataTransfer.dropEffect = "copy";
			var t = "photo," + s.id + "," + i;
			DragDrop.set_drag_data(evt, "photo", t)
			//evt.dataTransfer.setData();
			//var dataItem1 = dataTransferItemsList[0].add(t, "text/plain")

		}
	}
};
/**
 *
 */
Regex = {
	mail : /^([a-zA-Z0-9\._\+%-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,15}|[0-9]{1,3})(\]?))$/gi,
	url : /^(https:\/\/|http:\/\/)(www\.){0,1}([-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|])\.([a-z]){2,10}((\/|\?){1}(.)*)?$/i
};
/**
 * @version V1.00.210315
 *
 */
Validate = {
	mail : /^([a-zA-Z0-9\._\+%-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,15}|[0-9]{1,3})(\]?))$/gi,
	url : /^(https:\/\/|http:\/\/)(www\.){0,1}([-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|])\.([a-z]){2,10}((\/|\?){1}(.)*)?$/i,
	init:function(f){

		if (f && f.nodeName == "FORM")
		{
			Validate.start(f)
		}
		else
		{
			var a = document.forms,b;

			for (b=0; b<a.length;b++)
			{
				Validate.start(a[b]);
			}
		}



	},
	start:function(a){
		var b,
		    c,
		    d = new Array(),
		    f,g,r=new Array();


		b = a.getElementsByTagName("input");

		for ( c = 0; c < b.length; c++)
		{
			$$.events(b[c],"blur",Validate.field);
			$$.events(b[c],"mouseup",Validate.field);
			$$.events(b[c],"keyup",Validate.field);
			d.push(Validate.field(b[c]));
		}

		f = a.querySelectorAll(".wrapb");

		for ( g = 0; g < f.length; g++)
		{
			if(f[g].getAttribute("data-required"))
				d.push(Validate.group(f[g]));
		}

		if(d.indexOf(1)>-1){
			r.push(1);
		}
		if(d.indexOf(2)>-1){
			r.push(2);
		}


		return r;
	},
	group:function(a){

		var b,c,d=1;

		b = a.getElementsByTagName("input");

		for(c=0; c<b.length;c++)
		{
			switch(b[c].type)
			{
				case "checkbox":
					$$.events(b[c],"click",function(){Validate.group(a)});
					if(b[c].checked)
						d=0;
					break;
			}
		}

		if (d>0)
		{
			a.style.backgroundColor = "#F08080";
			return d;
		}
		else
		{
			a.style.backgroundColor = "";
			return d;
		}

	},
	field : function(f) {

		var a,
		    b = true,
		    c,
		    m,
		    u;

		a = $$.slf(f) || f;

		if(a){
				switch(a.type)
				{
					case "url":
						if (a.value)
							b = Validate.url.test(a.value);
						break;
				}

				if(a.required && (a.value == null || a.value == ""))
					b = false;

				if(!a.validity.valid)
						b = false;
		}

		if (!b)
		{
			c = (a.required) ? "#F08080" : "#FFFF99";
			a.style.backgroundColor = c;
			var f = (c === "#F08080") ? 1 : 2 ;
			return f;
		}
		else
		{

			a.style.backgroundColor = "#FFFFFF";
			return 0;
		}

	}

}
/**
 * @version V1.00.220115
 *
 */
Save = {
	saver : function() {


		$$.el("blk").style.display = "block";
		$("#iloader").show();

		if(!document.forms[0].id)
			document.forms[0].id = "mgpmainform";

		var a;

		a = Validate.start(document.forms[0]);

		if (a.length>0) {

			$$.erro();

			Save.warning(a)
			return false;
		}

	Save.submit_form(Save.serialize_form(document.forms[0]));

	},
	//f=formulário
	serialize_form : function(f) {
console.log(f)
		var sting = $(f).serialize();

		var at = f.getElementsByTagName("div");

		var dados = "";
		var amp = "&";

		for (var i = 0; i < at.length; i++)
		{
			if (at[i].getAttribute("contenteditable") && !at[i].querySelector("input"))
			{

				var x = at[i];

				if (x) {
					var xi = x.innerHTML;

					var st1 = xi.replace(/<div/g, "<p");
					var st2 = st1.replace(/<\/div>/g, "</p>");

					var st = st2;

					st = encodeURIComponent(st);

					if (!dados) {
						dados = amp + at[i].id + "=" + st;
					} else {
						dados += amp + at[i].id + "=" + st;
					}
				}
			}
		}

		return (sting.indexOf("flag") === -1) ? "module=" + BigBang.ob.module + "&" + "flag=SAVE&" + sting + dados : "module=" + BigBang.ob.module + "&" + sting + dados;


	},
	submit_form : function(s) {
console.log(s);
		var f = $$.el("mgpmainform");
		Save.back_to_form();

		if(!f)
		{
			alert("Não foi possivel realizar a operação.JS4629");

			return false;
		}

		var formul = f.action.split("/").pop();

		console.log(BigBang.mURL + formul)

		xhr = $.ajax({
			type : "POST",
			url : BigBang.mURL + formul,
			cache : false,
			data : s,
			dataType : "text",
			success : function(dat) {
console.log(dat);
				try {
					var dat = JSON.parse(dat);

					if (dat.result) {
						var dr = dat.result;

						OperationsBar.barra(0, 0, 0, 0, 0, 0);

						if (Folders.sf.indexOf(dat.result[0]) < 0)
							Folders.sf.push(dat.result[0]);
						if (dat.result.length >= 2)
							FilterResult.cs = dat.result[2];
						if (dat.result[3])
							FilterResult.os = dat.result[3] + "s";

						BigBang.palcoum();
						SideMenu.stage();

						if (BigBang.ob.folders_on)
							BigBang.ob.folders_on();
						if(dat.result[1])
							BigBang.ob.file_on(dat.result[1]);
					}
					if (dat.alert) {
						$$.erro(dat.alert);
					}
					if (dat.order) {
						OperationsBar.barra(0, 0, 0, 0, 0);
						OrderDisplay.init();
					}
					if (dat.nomove) {
						OperationsBar.barra(0, 0, 0, 0, 0), BigBang.palcoum(), SideMenu.stage();
						Folders.open_folders();
					}
					if (dat.save) {
						OperationsBar.barra(0, 0, 0, 0, 0);
						File.show(dat.save);
					}
					if (dat.Menu) {
						_firstBang.init();
					}
					if (dat.error) {
						Save.saver();
						return false;
					}
					if (dat.errormess) {
						Save.server(dat.errormess);
					}
					if (dat.login) {
						$("#tdaviso").html(dat.login);
					}
					if (dat.redes) {
						console.log(dat.redes);
						Save.social_data(dat.redes);
					}

					$("#iloader").hide();
					$$.el("blk").style.display = "none";

				} catch (e) {
					$$.erro("Não foi possivel realizar a operação.JS4893");
				}
			},
			error : function(dat) {
				$$.erro("Não foi possivel completar a operação.JS240");
			}
		});
	},
	server : function(m){
		var a,
		    b,
		    d,g ;

		a = $$.el("zomm");

		a.style.display = "block";

		b = $$.make("div", {
			"id" : "warnmess"
		}, a);

		 g = $$.make("div", {
					"class" : "redmess"
				}, b );

		$$.make("p", null, g ,m);

		$$.make("input", {
			"type" : "button",
			"class" : "back",
			"value" : "voltar para a ficha"
		}, b, null, {
			"click" : Save.back_to_form
		});
	},
	warning : function(f) {

		var a,
		    b,
		    c,
		    d = false,
		    g = false;

		a = $$.el("zomm");

		a.style.display = "block";

		b = $$.make("div", {
			"id" : "warnmess"
		}, a);

		for(c=0;c<f.length;c++)
		{
			if(f[c]==1){

				 g = $$.make("div", {
					"class" : "redmess"
				}, b );

				$$.make("p", null, g ,"Existem erros ou campos obrigatórios não preenchidos que impedem a gravação da ficha.");
			}

			if(f[c]==2){

				d = $$.make("div", {
					"class" : "yelmess"
				}, b );
				$$.make("p", null, d ,"Existem erros que podem causar erros gravação da ficha.");

				if(!g)
				$$.make("p", null, d ,"Se pretende continuar clique no botão \"continuar\".");
			}
		}

		$$.make("input", {
			"type" : "button",
			"class" : "back",
			"value" : "voltar para a ficha"
		}, b, null, {
			"click" : Save.back_to_form
		});

		if(d && !g)
		{
			$$.make("input", {
			"type" : "button",
			"class" : "go",
			"value" : "continuar"
		}, b, null, {
			"click" : Save.sww
		});
		}

	},
	sww:function(){
		Save.submit_form(Save.serialize_form($$.el("mgpmainform")));
	},
	social_data : function(xdat) {

		var a,
		    b,
		    c,
		    d,
		    f,
		    g,
		    h,
		    z;

		a = $$.el("zomm");

		a.style.display = "block";

		b = $$.make("div", {
			"class" : "ssocialdata"
		}, a);

		//cabeçalho
		f = $$.make("div", {
			"class" : "dsocialdata"
		}, b);

		$$.make("span", null, f, "Estes são os dados que o contato partilha publicamente nas redes sociais que indicou.");
		$$.make("span", null, f, "Selecione os dados que deseja gravar.");

		//formulário com os dados recolhidos das redes sociaias
		h = $$.make("form", {
			"id" : "socialnet_form",
			"method" : "post"
		}, b);
		d = $$.make("div", {
			"class" : "wsocialdata"
		}, h);

		for (c in xdat) {
			for (z in xdat[c]) {
				Save.wrapp_social_data(z, xdat[c][z], d, c)
			}

		}

		//envio do formulario
		g = $$.make("div", {
			"class" : "bsocialdata"
		}, b);

		$$.make("span", null, g, "Deseja gravar estes dados ?");
		$$.make("span", null, g, "A gravação destes dados irá subescrever os dados já gravados nestes campos.");

		$$.make("input", {
			"type" : "button",
			"class" : "back",
			"value" : "voltar para a ficha"
		}, g, null, {
			"click" : Save.back_to_form
		});
		$$.make("input", {
			"type" : "button",
			"class" : "ysave",
			"value" : "gravar os dados"
		}, g, null, {
			"click" : Save.socialdata_form
		});
		$$.make("input", {
			"type" : "button",
			"class" : "nsave",
			"value" : "não utilizar os dados"
		}, g, null,{"click":Save.no_socialdata_form});

	},
	wrapp_social_data : function(n, m, b, p) {

		var a,
		    c,
		    d,v=null;

		a = $$.make("div", {
			"class" : "fieldsocialdata"
		}, b);

		$$.make("p", {
			"class" : "title"
		}, a, n);

		c = $$.make("div", {
			"class" : "text"
		}, a);

		switch(n) {
		case "foto":
			$$.make("img", {
				"src" : m,
				"class" : "image"
			}, c)
			break;
		default:
			$$.make("span", null, c, m)
			break;
		}

		d = $$.make("div", {
			"class" : "check"
		}, a);

		$$.make("input", {
			"name" : p + "_" + n.replace(/ /gi,"_"),
			"value" : m,
			"type" : "checkbox",
			"class" : "box"
		}, d);

	},
	back_to_form : function() {

		a = $$.el("zomm");

		a.innerHTML = "";

		a.style.display = "none";
	},
	no_socialdata_form:function(){

		Save.submit_form(Save.serialize_form($$.el("mgpmainform"))+"&jssocial=y");

	},
	socialdata_form : function() {

		var a;

		a = Save.serialize_form($$.el("mgpmainform"));

		b = $("#socialnet_form").serialize();

		if (!b) {
			alert("Não selecionou dados!");
			return false;
		}

		Save.submit_form(a + "&jssocial=y&" + b);
	}
};
/**
 * @version V1.00.220115
 *
 */
Search = {
	r : null,
	init : function() {
		FilterResult.cs = "";
		FilterResult.os = "todos";

		Search.advanced_search($$.make("div", {
			"id" : "searchfiedls"
		}, SideMenu.stage()));

	},
	advanced_search : function(b) {
		var c = BigBang.ob.search_fields,
		    d = null,
		    f = null;

		b.innerHTML = "";

		for (d in c) {

			f = $$.make("div", {
				"class" : "edit_label"
			}, b);

			$$.make("label", {
				"class" : ""
			}, f, c[d]);
			$$.make("input", {
				"class" : "searchinput",
				"type" : "text",
				"name" : d
			}, f, null);
		}

		$$.make("div", {
			"class" : "btsearch"
		}, b, "pesquisar", {
			"click" : Search.result
		});
	},
	result : function(event) {
		if (BigBang.saveon()) {
			if (BigBang.sairSemSalvar())
				Search.result();
		} else {
			var a = "",
			    b = new Array("select", "input"),
			    c = $$.el("searchfiedls"),
			    d = null,
			    f = (BigBang.ob.search_handler) ? BigBang.ob.search_handler : Search.result_handler,
			    x = 0,
			    z = 0;

			for (x; x < b.length; x++) {
				d = c.getElementsByTagName(b[x]);

				for (z; z < d.length; z++) {
					a += "&" + d[z].name + "=" + d[z].value;
				}

			}

			$$.callServer("flag=SEARCH" + a, f);
		}
	},
	result_handler : function(xdat) {
		try {
			var a = JSON.parse(xdat);

			if (a.alert) {
				$$.erro(a.alert);
				return false;
			}
			if (a.result) {
				Search.r = a.result;
				Search.make_result();
			}

		} catch (e) {
			$$.erro("Não foi possivel realizar a operação.JSS118");
			return false;
		}

	},
	make_result : function() {

		var a,
		    b,
		    c,
		    d;

		OperationsBar.barra(0, 0, 0, 0, 0, 0);

		a = $$.make("div", {
			"class" : "searchresult"
		}, BigBang.palcoum());

		b = $$.make("div", {
			"class" : "searchresulttop"
		}, a);
		$$.make("span", {
			"class" : "fileboxflag"
		}, b, "Resultado da pesquisa: ");

		c = $$.make("div", {
			"class" : "searchResultbottom"
		}, a);

		d = $$.make("table", {
			"class" : "futi"
		}, c);

		SideMenu.off_editors();
		Search.make_result_lines(d);

	},
	make_result_lines : function(t) {

		var a = Search.r,
		    b,
		    c,
		    d;

		for (b in a) {
			c = $$.make("tr", null, t, null);

			for (d in a[b].fields) {
				$$.make("td", {
					"class" : "tdUti",
					"data-id" : "i:" + a[b].id
				}, c, a[b].fields[d], {
					"click" : Search.select_line
				});
			}
		}

	},
	select_line : function(event) {

		var a = $$.slf(event).getAttribute("data-id");

		if (BigBang.ob.file_on && a) {
			$$.callServer("flag=FILE&toke=" + a, Search.select_linha_handler);
		} else {
			$$.erro("Não é possivel apresentar o item selecionado.JS5070");
		}

	},
	select_linha_handler : function(xdat) {

		if (xdat) {
			File.show_handler(xdat);
		} else {
			$$.erro("Não é possivel apresentar o item selecionado.JS5104");
		}

	}
};

/**
 * @version V1.00.220115
 *
 */
ShowHide = {
	init : function() {

		var i = $$.el("mainEdition").querySelectorAll(".igop"),
		    x = i.length;

		while (x--) {
			if (i[x].getAttribute("data-type") == "hideshow") {
				$$.events(i[x], "click", ShowHide.close);
			}

		}
	},
	close : function(event) {

		var a = $$.slf(event);
		c = $$.parent(a, "P").nextSibling;

		if (c.style.display == "none") {
			a.src = "imagens/folderon.png";
			$(c).slideDown();

		} else {
			a.src = "imagens/folderoff.png";
			$(c).slideUp();
		}

	}
};
/**
 * @version V1.00.220115
 *
 */
SideMenu = {
	init : function() {

		var a,
		    b,
		    c,
		    d,
		    f,
		    g;

		b = $$.el('palcoDois');

		d = $$.make("div", {
			"id" : "contenpastax"
		}, b);

		g = $$.make("div", {
			"id" : "smopbar"
		}, d);

		SideMenu.adf = $$.make("img", {
			"src" : "imagens/add_folder.png",
			"classs" : "smopbarimg"
		}, g);

		a = $$.make("div", {
			"class" : "icons"
		}, d);

		SideMenu.fol = $$.make("img", {
			"src" : "imagens/pastas_off.png",
			"id" : "edfolders",
			"draggable" : false
		}, a, null, null);

		SideMenu.sta = $$.make("img", {
			"src" : "imagens/stats_off.png",
			"id" : "edstats",
			"draggable" : false
		}, a, null, null);

		SideMenu.ord = $$.make("img", {
			"src" : "imagens/ordenar_off.png",
			"id" : "edorder",
			"draggable" : false
		}, a, null, null);

		SideMenu.ser = $$.make("img", {
			"src" : "imagens/pesquisar_off.png",
			"id" : "edsearch",
			"draggable" : false
		}, a, null, null);

		SideMenu.key = $$.make("img", {
			"src" : "imagens/chave_off.png",
			"id" : "edkey",
			"draggable" : false
		}, a, null, null);

		SideMenu.fot = $$.make("img", {
			"src" : "imagens/fotos_off.png",
			"id" : "edfotos",
			"draggable" : false
		}, a, null, null);

		SideMenu.txt = $$.make("img", {
			"src" : "imagens/texto_off.png",
			"id" : "edtexto",
			"draggable" : false
		}, a, null, null);

		SideMenu.mod = $$.make("img", {
			"src" : "imagens/modulos_off.png",
			"id" : "edmodules",
			"draggable" : false
		}, a, null, null);

		//contenpastas
		c = $$.make("div", {
			"id" : "contenpasta"
		}, b);

		f = $$.make("div", {
			"id" : "mainwrapfolders"
		}, c);

		$$.make("div", {
			"class" : "tbfolders"
		}, f, null, {
			"dragenter" : SideMenu.up,
			"dragleave" : SideMenu.stop
		});

		$$.make("div", {
			"id" : "wrapfolders"
		}, f);

		$$.make("div", {
			"class" : "bfolders"
		}, f, null, {
			"dragenter" : SideMenu.down,
			"dragleave" : SideMenu.stop
		});

		$$.make("div", {
			"id" : "barw"
		}, c, null, {
			"mousedown" : LatMove.init,
			"mouseup" : LatMove.stop
		});

	},
	make_icon : function() {

		SideMenu.stage();
		SideMenu.off();

	},
	/**
	 * desliga todos os eventos e esconde o icon de adicionar a pasta
	 */
	off : function() {
		//esonde o icon de adicionar nova pasta
		SideMenu.add_el(false);

		$$.stopEvent(SideMenu.fol, "click", SideMenu.act);
		$$.stopEvent(SideMenu.sta, "click", SideMenu.act);
		$$.stopEvent(SideMenu.ord, "click", SideMenu.act);
		$$.stopEvent(SideMenu.ser, "click", SideMenu.act);
		$$.stopEvent(SideMenu.key, "click", SideMenu.act);

		//desliga os modulos usados para edição
		SideMenu.off_editors();


		SideMenu.on();

	},
	on : function() {

		if (BigBang.ob.folders_on) {
			SideMenu.fol.src = "imagens/pastas_on.png";
			SideMenu.fol.opener = BigBang.ob.folders_on;
			SideMenu.fol.add = Folders.new_folder;
			$$.events(SideMenu.fol, "click", SideMenu.act);
			SideMenu.add_el(Folders.new_folder);
		} else {
			SideMenu.fol.src = "imagens/pastas_off.png";
			SideMenu.fol.opener = false;
			SideMenu.fol.add = false;
		}

		if (BigBang.ob.order_on) {
			SideMenu.ord.src = "imagens/ordenar_on.png";
			SideMenu.ord.opener = BigBang.ob.order_on;
			SideMenu.ord.add = false;
			$$.events(SideMenu.ord, "click", SideMenu.act);
		} else {
			SideMenu.ord.src = "imagens/ordenar_off.png";
			SideMenu.ord.opener = false;
			SideMenu.ord.add = false;
		}

		if (BigBang.ob.stats_on) {
			SideMenu.sta.src = "imagens/stats_on.png";
			SideMenu.sta.opener = BigBang.ob.stats_on;
			SideMenu.sta.add = false;
			$$.events(SideMenu.sta, "click", SideMenu.act);
		} else {
			SideMenu.sta.src = "imagens/stats_off.png";
			SideMenu.sta.opener = false;
			SideMenu.sta.add = false;
		}

		if (BigBang.ob.search_on) {
			SideMenu.ser.src = "imagens/pesquisar_on.png";
			SideMenu.ser.opener = BigBang.ob.search_on;
			SideMenu.ser.add = false;
			$$.events(SideMenu.ser, "click", SideMenu.act);
		} else {
			SideMenu.ser.src = "imagens/pesquisar_off.png";
			SideMenu.ser.opener = false;
			SideMenu.ser.add = false;
		}

		if (BigBang.ob.keywords_on) {
			SideMenu.key.src = "imagens/chave_on.png";
			SideMenu.key.opener = BigBang.ob.keywords_on;
			SideMenu.key.add = Keywords.new_folderk;
			$$.events(SideMenu.key, "click", SideMenu.act);
		} else {
			SideMenu.key.src = "imagens/chave_off.png";
			SideMenu.key.opener = false;
			SideMenu.key.add = false;
		}

	},
	init_editors : function() {

		if (BigBang.saveon()) {
			SideMenu.fot.src = "imagens/fotos_on.png";
			$$.events(SideMenu.fot, "click", Photo.on);

			SideMenu.txt.src = "imagens/texto_on.png";
			$$.events(SideMenu.txt, "click", RichTextEditor.on);

			if (BigBang.ob.modulos) {
				SideMenu.mod.src = "imagens/modulos_on.png";
				$$.events(SideMenu.mod, "click", Modules.on);
			}

		}

	},
	editors_on : function() {
		SideMenu.add_el(false);
		return SideMenu.stage();
	},

	off_editors : function() {

		SideMenu.fot.src = "imagens/fotos_off.png";
		$$.stopEvent(SideMenu.fot, "click", Photo.on);

		SideMenu.txt.src = "imagens/texto_off.png";
		$$.stopEvent(SideMenu.txt, "click", RichTextEditor.on);

		SideMenu.mod.src = "imagens/modulos_off.png";
		$$.stopEvent(SideMenu.mod, "click", Modules.on);

	},
	/**
	 * aciona o evento associado ao icon
	 */
	act : function(event) {

		var a = $$.slf(event);

		SideMenu.add_el(a.add);

		a.opener(event);

	},
	/**
	 * liga o botão para adicionar uma pasta
	 */
	add_el : function(a) {

		var b = SideMenu.adf;

		$$.stopEvent(b, "click", Folders.new_folder);
		$$.stopEvent(b, "click", Keywords.new_folderk);

		b.style.display = "none";

		if (a)
		{
			b.style.display = "block";
			$$.events(b, "click", a);
		}
	},
	stage : function(hum) {

		var p = $$.el('wrapfolders');

		if (p) {
			p.innerHTML = hum || "";
			return p;
		} else {
			return false;
		}
	},
	up : function() {

		try {

			var fl = -parseInt($$.el("wrapfolders").children[0].offsetHeight);

			if (-fl > parseInt($$.el("wrapfolders").offsetHeight)) {
				$("#wrapfolders").animate({
					'scrollTop' : 0
				}, 5000);
			}
		} catch(e) {

			return false;
		}
	},
	down : function() {

		var fl = parseInt($$.el("wrapfolders").children[0].offsetHeight);

		$("#wrapfolders").animate({
			'scrollTop' : fl - parseInt($$.el("wrapfolders").offsetHeight)
		}, 5000);
	},
	stop : function() {

		$("#wrapfolders").stop();
	}
};
/**
 * @version V1.01.140515
 *
 */
FilterResult = {

	cs : null,//valor do elemento select

	os : null,//opções para clicar

	op : [],//lista de opções do elemento select

	caller:null,

	stage : function() {
		try
		{
			return document.getElementById("filterOptions");
		}
		catch (e)
		{
			return false;
		}
	},
	init : function(event)
	{
		var a,b,c;
		a =  Boolean(!FilterResult.cs && FilterResult.op.options);
		b = Boolean(FilterResult.op.options && FilterResult.op.options.indexOf(FilterResult.cs) > -1);

		if(event)
		FilterResult.caller = event.currentTarget.id;

		if (a || b)
		{
			FilterResult.call_folders();
		}
		else
		{
			if (BigBang.ob.filter_result_options)
			{
				$$.callServer("flag=SUBMENU", FilterResult.init_handler);
			}
		}

	},
	init_handler : function(xdat) {

		try
		{
			var a = JSON.parse(xdat);

			if (a.options)
				FilterResult.op = a;

			if (a.alert)
			{
				$$.erro(a.alert);
				return false;
			}
		}
		catch (e)
		{
			$$.erro("Não foi possivel realizar a operação.JS6279");
			return false;
		}

		if (!FilterResult.op)
			return false;

		if (FilterResult.make_filter())
			FilterResult.call_folders();

	},
	make_select : function(a) {

		var sm = FilterResult.stage();
		var b = (a) ? a : FilterResult.cs;

		//cria elemento select
		var sel = $$.make("select",{"id" : "smSelector"}, null, null, {"change" : FilterResult.call_folders});

		//cria opção inicial do elemeto select
		$$.make("option",{"value" : ""}, sel, "-------", null);

		//cria opções de filtro
		for (var f in FilterResult.op.options) {

			var slc = (b == FilterResult.op.options[f]) ? {
				"value" : FilterResult.op.options[f],
				"selected" : "selected"
			} : {
				"value" : FilterResult.op.options[f]
			};

			$$.make("option", slc, sel, FilterResult.op.options[f], null);

			slc = "";

		}

		if (sm.children[0].id == "smSelector")
			sm.removeChild(sm.children[0]);

		if (sm)
			sm.insertBefore(sel, sm.children[0]);

		if (b === null || b === undefined)
			sm.children[0].children[0].selected = true;

		return true;
	},
	make_filter : function() {

		if ($("#wFilter"))
			$("#wFilter").remove();

		var a,
		    b,
		    c,
		    d,
		    f,
		    g,
		    m,
		    s = $$.el("subMenu");

		if (s) {

			m = $$.make("div", {
				"id" : "wFilter"
			}, s);

			var w = $$.make("div", {
				"id" : "wfilterOptions"
			}, m);

			d = $$.make("div", {
				"id" : "filterOptions"
			}, w);

			a = $$.make("ul", null, d);

			//cria as opções para clicar
			for (c in BigBang.ob.filter_result_options) {

				$$.make("li", {
					"class" : "lisubmenu",
                                        "data-value":BigBang.ob.filter_result_options[c]
				}, a, c, {
					"click" : FilterResult.active_selection
				});

			}

			if (FilterResult.make_select())
				return true;
		}
	},
	active_selection : function(event) {

		if(!FilterResult.open_save())
			return false;

		var c = $$.slf(event);

		if (c.className == "lisubmenusel")
		{
			c.className = "lisubmenu";
		} else
		{
			FilterResult.make_active(c.textContent);
		}

		FilterResult.call_folders();
		return $(c).attr("data-value");

	},
	make_active : function(a) {

		var f = FilterResult.stage().getElementsByTagName("li"),
		    r;

		for (r in f) {
			if (f[r].textContent == a) {

				f[r].className = "lisubmenusel";

			} else {

				f[r].className = "lisubmenu";
			}
		}
	},
	open_save:function(){

		if (BigBang.saveon() && FilterResult.caller != "edfolders")
		{
			if (!BigBang.sairSemSalvar())
			{
				FilterResult.make_select(Folders.qr.cat);
				return false;
			}
			else
			{
				BigBang.palcoum();
			}

		}

		FilterResult.caller = null;

		return true;
	},
	call_folders : function(event) {

		var c,
		    d,
		    g = "",
		    h = "",
		    m = $("#smSelector option"),
		    t = 0;

		FilterResult.open_save();

		//se for chamada pelo select anula as opções de clique
		if(event && event.target.id==="smSelector")
		{
			FilterResult.make_active("");
		}
		else
		{

			c = FilterResult.stage().getElementsByTagName("li");

			if (FilterResult.os !== null)
			{
				FilterResult.make_active(FilterResult.os);
				FilterResult.os = null;
			}

			for (d in c)
			{
				if (c[d].className == "lisubmenusel") {
					g = $(c[d]).attr("data-value");
				}
			}
		}

		if (FilterResult.cs !== null && FilterResult.cs !== undefined)
			h = FilterResult.cs;

		for (t; t < m.length; t++) {

			if (FilterResult.cs !== null && m[t].value == h) {

				m[t].selected = true;
			}
		}

		FilterResult.cs = null;

		Folders.qr = {
			"cat" : ($("#smSelector").val() || h),
			"opc" : g
		};

		Folders.open_folders();

	}
};
/**
 * @version V1.00.220115
 *
 */
Start = {
	init : function() {

		var a,
		    b,
		    c,
		    f,
		    h;

		a = document.forms[0].action;
		b = $$.el('nick').value;
		c = $$.el('senha').value;
		d = $$.el('code').value;

		$.ajax({
			type : "POST",
			url : BigBang.mURL,
			cache : false,
			crossDomain : true,
			data : "flag=salvar&" + "toke1=" + b + "&toke2=" + c + "&toke3=" + d,
			dataType : "text",
			success : function(dat) {
				Start.init_handler(dat);
			},
			error : function(dat) {
				$$.el("tdaviso").innerHTML = "Impossivel de ligar ao servidor.JS6267";
			}
		});

	},
	init_handler : function(d) {

		var a,
		    b;

		b = $$.el("tdaviso");

		try {
			a = JSON.parse(d);
		} catch (e) {
			a = false;
		}

		if (a) {

			if (a.login) {

				b.innerHTML = a.login;
			}

			if (a.level) {

				FirstBang.init();
			}

		} else {

			b.innerHTML = event.target.data;

		}
	}
};
/**
 * @version V1.00.220115
 *
 */
UpDown = {
	up : function() {

		try {
			var fl = -parseInt($$.el("palco").children[0].offsetHeight);

			if (-fl > parseInt($$.el("palco").offsetHeight)) {

				$("#palco").animate({
					'scrollTop' : 0
				}, "slow");
			}
		} catch (e) {
			return false;
		}
	},
	down : function() {

		var fl = parseInt($$.el("palco").children[0].offsetHeight);

		$("#palco").animate({
			'scrollTop' : fl - parseInt($$.el("palco").offsetHeight)
		}, "slow");
	},
	stop : function() {

		$("#palco").stop();
	}
};

Stats = {
	//div onde são criados os graficos
	stage : null,
	c : ["#6495ED", "#008B8B", "#2F4F4F", "#C0C0C0", "#6A5ACD", "#708090", "#4682B4", "#D2B48C", "#008080", "#BC8F8F", "#808000", "#FFC0CB"],
	init : function() {

		if (BigBang.saveon()) {
			if (BigBang.sairSemSalvar())
				Stats.init();
		} else {
			OperationsBar.barra(1, 0, 0, 0, 0);

			var a,
			    b;

			// cria a div onde será apresentado o resultado da pesquisa
			Stats.stage = BigBang.palcoum();

			// cria a div de topo
			/*b = $$.make("div", {
			 "class" : "searchresulttop"
			 }, a);*/

			SideMenu.off_editors();
			SideMenu.stage();

			$$.callServer("flag=STATS", Stats.init_handler);

			//Stats.make_pie_chart();

		}
	},
	init_handler : function(datx) {

		try {
			var a,
			    b;

			a = JSON.parse(datx);

			if (a.alert) {
				$$.erro(a.alert);
				return false;
			}

			for (b in a) {
				switch(a[b].type) {
				case "pie":
					Stats.make_pie_chart(a[b], b)
					break;
				case "bar":
					Stats.make_bar_chart(a[b], b)
					break;
				}
			}
		} catch(e) {

			$$.erro("Não foi possivel realizar a operação.JS5814")
		}
	},
	make_canvas : function(n, s) {

		var a,
		    b,
		    c,
		    cv;

		a = $$.make("div", {
			"class" : "wstats"
		}, Stats.stage);

		$$.make("div", {
			"class" : "titlechart"
		}, a, n + " (" + s + ")");

		c = $$.make("div", {
			"class" : "piesecond"
		}, a);

		cv = $$.make("canvas", {
			"width" : c.offsetWidth,
			"height" : c.offsetHeight
		}, c);

		b = $$.make("div", {
			"class" : "piefirst"
		}, a);

	},
	make_bar_chart : function(r, n) {
		var a,
		    b,
		    c,
		    d,
		    f = 0,
		    g,
		    h,
		    i,
		    j,
		    m,
		    n,
		    p,
		    s = 0,
		    t = 0,
		    to = 0,
		    w,
		    x,
		    cv;

		var rep = r.data;

		for (g in rep) {
			if (rep.hasOwnProperty(g)) {
				p = parseInt(rep[g]);
				p = (!isNaN(p)) ? p : 0;

				if (f < p)
					f = p;
				to = to + p;
				s++;
			}

		}

		a = $$.make("div", {
			"class" : "wstats long"
		}, Stats.stage);

		$$.make("div", {
			"class" : "titlechart"
		}, a, n + " (" + s + ")");

		c = $$.make("div", {
			"class" : "piesecond bt"
		}, a);

		ww = parseInt(c.offsetWidth);
		hh = parseInt(c.offsetHeight);

		cv = $$.make("canvas", {
			"width" : ww,
			"height" : hh
		}, c);

		b = $$.make("div", {
			"class" : "piefirst"
		}, a);

		d = cv.getContext('2d');

		w = parseInt(ww - 30);
		h = parseInt(hh - 30);

		d.beginPath();
		d.moveTo(30, 0);
		d.lineTo(30, h);
		d.stroke();

		d.beginPath();
		d.moveTo(30, h);
		d.lineTo(ww, h);
		d.stroke();

		d.fillStyle = "rgba(102, 153, 153, 0.1)";
		d.fillRect(30, 0, w, (h * 0.25));

		d.fillStyle = "rgba(102, 153, 153, 0.1)";
		d.fillRect(30, (h * 0.50), w, (h * 0.25));

		i = parseInt((w / s) - 4);
		k = 32;
		d.fillStyle = "rgb(95, 116, 163)";

		j = $$.make("div", {
			"class" : "chartresult"
		}, b);

		for (g in rep) {
			n = 0;
			n = parseInt(rep[g]);
			n = (!isNaN(n)) ? n : 0;

			z = ((n * 100) / to);

			m = $$.make("div", {
				"class" : "chartresultrow"
			}, j);

			$$.make("div", {
				"class" : "fcell"
			}, m, g);
			$$.make("div", {
				"class" : "scell"
			}, m, rep[g]);
			$$.make("div", {
				"class" : "tcell"
			}, m, z.toFixed(2) + " %");

			x = (parseInt(rep[g]) * h) / f;
			d.fillRect(k, (h - x), i, x);

			t = ((4 + i) - parseInt(d.measureText(g).width)) / 2;

			d.fillText(g, (k + t), (h + 10))

			k = k + 4 + i

		}
	},
	make_pie_chart : function(r, n) {

		var a,
		    b,
		    c,
		    d,
		    f = 0,
		    g,
		    h,
		    ch,
		    cw,
		    s = 0;

		var rep = r.data;

		for (g in rep) {
			if (rep.hasOwnProperty(g)) {
				f += parseInt(rep[g]);
				s++;
			}

		}

		a = $$.make("div", {
			"class" : "wstats"
		}, Stats.stage);

		$$.make("div", {
			"class" : "titlechart"
		}, a, n + " (" + s + ")");

		c = $$.make("div", {
			"class" : "piesecond"
		}, a);

		cv = $$.make("canvas", {
			"width" : c.offsetWidth,
			"height" : c.offsetHeight
		}, c);

		b = $$.make("div", {
			"class" : "piefirst"
		}, a);

		d = cv.getContext('2d');

		ch = cv.height / 2;
		cw = cv.width / 2;

		var h = 0,
		    m = 0,
		    x = null;

		for (x in rep) {
			if (rep.hasOwnProperty(x)) {
				$$.make("div", {
					"class" : "piecolor",
					"style" : "background-color:" + Stats.c[m] + ";"
				}, $$.make("div", {
					"class" : "pieslice"
				}, b, x + "(" + rep[x] + ")"));

				z = h + ((parseInt(rep[x]) * 2) / f);

				d.beginPath();
				d.moveTo(cw, ch);
				d.arc(cw, ch, ch, (h * Math.PI), (z * Math.PI), false);
				d.closePath();
				d.fillStyle = Stats.c[m];
				d.fill();

				h = z;
				m++
			}
		}

	}
}
/**
 *
 */
TollTips = {
	box : false,
	tip : false,
	init : function() {

		var a,
		    b,
		    c = 0;

		if ($$.el("mainEdition"))
			a = $$.el("mainEdition").querySelectorAll("img.helpicon");

		for (b in a) {
			if ( typeof a[b] == "object" && a[b] != null) {
				$$.events(a[b], "mouseover", TollTips.show_tip);

			}

		}

	},
	show_tip : function(event) {

		var a,
		    b,
		    c,
		    d;

		a = $$.slf(event);
		$$.events(a, "mouseout", TollTips.destroy_tip);

		TollTips.box = a.parentNode;
		d = a.getAttribute("data-tip");

		if (d)
			TollTips.tip = $$.make("div", {
				"class" : "mgptooltip",
				"style" : "max-width:" + (TollTips.box.parentNode.offsetWidth * 0.8) + "px;"
			}, TollTips.box, d, {
				"mouseout" : TollTips.destroy_tip
			})

	},
	destroy_tip : function() {
		console.log(TollTips.tip)
		if (TollTips.tip) {
			TollTips.box.removeChild(TollTips.tip);
			TollTips.box = false;
			TollTips.tip = false;
		}

	}
};
/**
 * @version V1.01.280115
 *
 */
Keywords = {
	selected_folders : [],
	selected_element : null,
	init : function() {
		try {

			var cp = $$.el("wrapfolders");
			var k = (cp.getElementsByTagName("ul")[0]) ? cp.getElementsByTagName("ul")[0].id : null;

			if (k != "wordsTree") {
				Keywords.selected_folders = [];
				$$.callServer("flag=SENDK", Keywords.init_handler);
			}
		} catch (e) {
			$$.erro("Não foi possivel realizar a operação.JS6624");
		}

	},
	init_handler : function(xdat) {

		try {

			var dat = JSON.parse(xdat),
			    m = $$.el("wrapfolders"),
			    z;

			m.innerHTML = "";

			var um = $$.make("ul", {
				"class" : "unidade",
				"id" : "wordsTree",
				"data-type" : "text/plain,keywords"
			}, m, null, {
				"dragover" : Keywords.over_drag,
				"dragenter" : Keywords.over_drag,
				"drop" : Keywords.drop_up
			});

			for (var a in dat) {
				if (a && dat.hasOwnProperty(a)) {
					var j = $$.make("li", {
						"class" : "linha",
						"id" : a,
						"data-type" : "text/plain,keywords"
					}, um, a, {
						"dragover" : Keywords.over_drag,
						"dragenter" : Keywords.over_drag,
						"click" : Keywords.open_folders
					});

					var g = $$.make("ul", {
						"class" : "pastaint",
						"data-folder" : a,
						"data-type" : "text/plain,keywords"
					}, j, null, {
						"dragstart" : Keywords.init_drag,
						"dragover" : Keywords.over_drag,
						"dragenter" : Keywords.over_drag
					});
					for (var t = 0; t < dat[a].length; t++) {
						$$.make("li", {
							"draggable" : true,
							"class" : "linhaint",
							"data-type" : "text/plain,keywords"
						}, g, dat[a][t], {
							"dragstart" : Keywords.init_drag,
							"dragover" : Keywords.over_drag,
							"dragenter" : Keywords.over_drag,
							"mousedown" : Keywords.insert_key
						});
					}
				}
			}

			for (z in dat) {
				if (!z) {
					for (var c = 0; c < dat[z].length; c++) {
						$$.make("li", {
							"draggable" : true,
							"class" : "linhaint",
							"data-type" : "text/plain,keywords"
						}, um, dat[z][c], {
							"dragstart" : Keywords.init_drag,
							"dragover" : Keywords.over_drag,
							"dragenter" : Keywords.over_drag,
							"mousedown" : Keywords.insert_key
						});
					}
				}
			}

			Keywords.folder_on();
		} catch (e) {
			return false;
		}
	},
	init_drag : function(event) {
		var d = $$.parent(event.target, "UL").getAttribute("data-folder"),
		    a = event.target.textContent || event.target.innerText;
		DragDrop.start_drag_id(event, "keywords", a + "," + d);
	},
	drop_up : function(event) {

		event.preventDefault();

		var data = event.dataTransfer.getData("text/plain"),
		    datex = event.dataTransfer.getData("keywords"),
		    p = "",
		    s = null;

		if (event.target.className == "pastaint") {
			p = event.target.getAttribute("data-folder");

		} else if (event.target.className == "linhaint") {
			p = event.target.parentNode.getAttribute("data-folder");
		} else {
			if (event.target.className == "linha")
				p = event.target.id;
		}

		if (Keywords.selected_folders.indexOf(p) === -1)
			Keywords.selected_folders.push(p);

		if (data) {
			$$.callServer("flag=ADDK&pasta=" + p + "&word=" + data, Keywords.drop_up_handler);

			data = false;
		} else if ( s = datex.split(",")) {

			if (s[0] && s[1]) {
				$$.callServer("flag=CHANGEK&de=" + s[1] + "&para=" + p + "&word=" + s[0], Keywords.drop_up_handler);
			} else {

				$$.erro("Não foi possivel realizar a operação.JS5712");
			}

		} else {
			$$.erro("Não foi possivel realizar a operação.JS5717");
		}
	},
	drop_up_handler : function(xdat) {

		Keywords.init_handler(xdat);

	},

	open_folders : function(event) {

		var t = event.target;

		var i = Keywords.selected_folders.indexOf(t.id);
		if (t.style.listStyleImage.indexOf("folderon.png") > -1) {
			if (i > -1) {
				Keywords.selected_folders.splice(i, 1);
				if (t.children[0]) {
					t.style.listStyleImage = Folders.fcu;
					$(t.children[0]).slideUp();

				}

			}

		} else {
			if (i === -1) {
				Keywords.selected_folders.push(t.id);
				if (t.children[0]) {
					t.style.listStyleImage = Folders.fou;
					$(t.children[0]).slideDown();
				}
			}
		}
	},
	folder_on : function(dv) {
		var f,
		    g = null;

		for (f in Keywords.selected_folders) {
			if (Keywords.selected_folders[f]) {
				var ob = (dv) ? dv : $$.el(Keywords.selected_folders[f]);
				ob.style.listStyleImage = Folders.fou;
				$(ob.children[0]).slideDown();
			}
		}

	},
	delete_word : function(a) {
		var s;
		if ( s = a.split(",")) {

			if (s[0] && s[1]) {

				$$.callServer("flag=DELETEK&pasta=" + s[1] + "&word=" + s[0], Keywords.drop_up_handler);
			} else {

				$$.erro("Não foi possivel realizar a operação.JS5773");
			}

		} else {

			$$.erro("Não foi possivel realizar a operação.JS5780");
		}

	},
	insert_key : function(event) {
		try {
			var a,
			    b,
			    h,
			    t;

			if (document.activeElement.nodeName !== "BODY")
				Keywords.selected_element = document.activeElement;

			t = Keywords.selected_element;

			h = event.target.innerHTML;

			if (t.nodeName === "DIV") {

				if (!t.innerHTML) {
					t.innerHTML = h;

				} else {
					t.innerHTML += ", " + h;

				}
			}
			if (t.nodeName === "TEXTAREA") {

				if (!t.value) {
					t.value = h;
				} else {
					t.value += "," + h;
				}

				if (t.getAttribute("data-type") === "countwords")
					Buttons.count_words(t);
			}
		} catch (e) {
			return false;

		}
	},
	new_folderk : function() {

		if (!$$.el("wordsTree")) {

			$$.erro("Não é possivel realizar a operação.JS5863");
		}

		$("#wordsTree").prepend("<li class='linha'><input id='novapasta' size='10' type='text'/></li>");
		$$.events($$.el('novapasta'), "keypress", Keywords.make_folderk);

	},
	make_folderk : function(event) {

		var np = $$.el("novapasta");

		if (event.keyCode === 13) {

			var s = np.value.search(/[^\d\w\xc3\x80-\xc3\x96\x20\x2D\xc3\x99-\xc3\xb6,\xc3\xb9-\xc3\xbf]/i);

			if (s === -1) {
				if (np.value !== "") {
					if (np.value.length > 2) {
						$(".unidade li:first").remove();
						$(".unidade").prepend("<li id='" + np.value + "' class='linha' data-type='menu'>" + np.value + "</li>");

						var p = $$.el(np.value);
						$$.events(p, "dragenter", Keywords.over_drag);
						$$.events(p, "dragover", Keywords.over_drag);

					} else {

						$$.erro("Nome de pasta deve ter no minimo 3 caracteres.");
					}

				} else {

					$("#novapasta").parent().remove();
				}
			} else {

				$$.erro("Nome de pasta inválido");

			}
		}

	}
};
/**
 * @version V1.00.220115
 *
 */
RichTextEditor = {
	L : "imagens/",
	I : ['italico.png', 'negrito.png', 'sublinhado.png', 'justify.png', 'esquerda.png', 'centrado.png', 'direita.png', 'listanordenada.png', 'listaordenada.png', 'link.png', 'apaga_format.png', 'apaga_todo_format.png', 'apaga_tudo.png'],
	T : ['L1.png', 'L2.png', 'L3.png', 'L4.png', 'L5.png', 'L6.png', 'L7.png'],
	on : function() {

		var a = SideMenu.editors_on();

		try {
			if (!$$.el("editorx"))
				RichTextEditor.init(a)

			$$.el("editorx").style.display = "block";

		} catch(e) {
			return false;
		}

	},
	init : function(a) {

		var x = this;

		var E = $$.make("div", {
			"id" : "editorx"
		}, a);

		var gh = $$.make("div", {
			"id" : "editorHead",
			"draggable" : false
		}, E);
		$$.make("img", {
			"class" : "igM7",
			"src" : "imagens/texto_on.png",
			"draggable" : false
		}, gh);

		var o = $$.make("div", {
			"id" : "weditoroptions",
			"draggable" : false
		}, E);
		o.appendChild(x.makeImg());
		o.appendChild(x.fontSize());
		E.appendChild(x.makeColor());
	},
	makeImg : function() {
		var x = this;

		var imm = $$.make("div", {
			"id" : "weditoptions"
		});

		try {
			document.execCommand('styleWithCSS', true, null);
		} catch(e) {
			//o IE não suporta este comando
		}

		for (var i = 0; i < (x.I.length); i++) {
			$$.make("img", {
				"src" : x.L + x.I[i],
				"class" : "i30x5"
			}, imm);
		}
		var imx = imm.getElementsByTagName('img');

		imx[0].onmousedown = function() {
			document.execCommand('italic', false, null);
		};
		imx[1].onmousedown = function() {
			document.execCommand('bold', false, null);
		};
		imx[2].onmousedown = function() {
			document.execCommand('underline', false, null);
		};
		imx[3].onmousedown = function() {
			document.execCommand('justifyFull', false, null);
		};
		imx[4].onmousedown = function() {
			document.execCommand("justifyLeft", false, null);
		};
		imx[5].onmousedown = function() {
			document.execCommand('justifyCenter', false, null);
		};
		imx[6].onmousedown = function() {
			document.execCommand('justifyRight', false, null);
		};
		imx[7].onmousedown = function() {
			document.execCommand('insertUnorderedList', false, null);
		};
		imx[8].onmousedown = function() {
			document.execCommand('insertOrderedList', false, null);
		};
		imx[9].onmousedown = function() {
			var selObj = window.getSelection();
			var szURL = prompt("Digite o endereço:", "http://");
			if ((szURL != null) && (szURL != "")) {
				document.execCommand('CreateLink', true, szURL);
			}
			var an = selObj.anchorNode.parentNode.parentNode.getElementsByTagName("a");
			for (var x = 0; x < an.length; x++) {
				if (!an[x].getAttribute("target"))
					an[x].setAttribute("target", "_blank");
			}
		};
		imx[10].onmousedown = function() {
			document.execCommand('removeFormat', false, null);
		};

		imx[11].onclick = function() {

			var sel = window.getSelection();

			if (sel.anchorNode) {
				var tex = sel.anchorNode.parentNode;

				while (!tex.getAttribute("contenteditable")) {
					tex = tex.parentNode;
				}

				if (tex)
					tex.innerHTML = tex.innerHTML.replace(/<[^>]*>/gi, " ");
			}

		};
		imx[12].onclick = function() {

			var sel = window.getSelection();

			if (sel.anchorNode) {
				var tex = sel.anchorNode.parentNode;

				while (!tex.getAttribute("contenteditable")) {
					tex = tex.parentNode;
				}
				tex.innerHTML = "";
			}

		};
		return imm;
	},
	makeColor : function() {

		var x = this;
		var C = $$.make("div", {
			"id" : "colorPaletex"
		});
		var cc = ["FF", "CC", "99", "66", "33", "00"];
		var color = [];

		for (var c = 0; c < cc.length; c++) {
			for (var f = 0; f < cc.length; f++) {
				for (var g = 0; g < cc.length; g++) {
					var cor = cc[c] + cc[f] + cc[g];
					color.push(cor);
				}
			}
		}

		for (var m = 0; m < color.length; m++) {

			$$.make("div", {
				"style" : "width:100%;background-color:#" + color[m],
				"data-cor" : "#" + color[m],
				"class" : "cores"
			}, C, null, {
				"mousedown" : x.corTexto
			});
		}

		return C;
	},
	corTexto : function() {

		var j = this.getAttribute("data-cor");

		document.execCommand("ForeColor", false, j);

	},
	fontSize : function() {
		var x = this,
		    a;
		var imm = $$.make("div", {
			"class" : "letras"
		});
		for (var i = 1; i < 8; i++) {
			a = 8 + (i + i);
			$$.make("span", {
				"data-size" : i,
				"class" : "fsize ",
				"style" : "font-size:" + a + "px"
			}, imm, "A", {
				"mousedown" : x.fonteTam
			});
		}
		return imm;
	},
	fonteTam : function() {
		var size = this.getAttribute("data-size");
		document.execCommand("FontSize", false, size);
	}
};
/**
 * @version V1.00.220115
 *
 */
function imgallery_width_captions() {

	this.nameSubtitles = null;
	this.nameImage = null;
	this.ni = null;
	this.divix = null;
	this.lang = ["pt"];

	this.init = function(o) {

		var e,
		    f,
		    s,
		    x,
		    z;

		switch (typeof o) {

		case "object":
			this.divix = o;
			break;
		case "string":
			this.divix = $$.el(o);
			break;
		default:
			this.divix = false;

		}

		if (!this.divix)
			return false;

		e = this.divix;
		x = this;
		s = (this.subtitle) ? "photo,destak" : "photo,gallery";

		this.ni = this.divix.getAttribute("data-size");

		e.setAttribute("data-type", s);
		e.ondragenter = DragDrop.over_drag_id;
		e.ondragover = DragDrop.over_drag_id;
		$$.events(e, "drop", function(event) {

			x.dropDive(event);
		});

		z = e.getElementsByTagName("div");

		if (z.length) {
			for ( f = 0; f < z.length; f++) {
				z[f].setAttribute("data-type", s);
				if (z[f].children[0]) {
					z[f].children[0].onclick = x.dilite;
				}
				if (z[f].children[1]) {

					z[f].children[1].setAttribute("data-type", s);
					z[f].children[1].ondragstart = function(event) {
						x.initdrag(event);
					};
					z[f].children[1].ondragend = DragDrop.clean;
					$$.events(z[f].children[1], "dragenter", DragDrop.over_drag_id);
					$$.events(z[f].children[1], "dragover", DragDrop.over_drag_id);
				}
				$$.events(z[f], "dragenter", DragDrop.over_drag_id);
				$$.events(z[f], "dragover", DragDrop.over_drag_id);
				z[f].ondrop = function(event) {
					event.preventDefault();
					x.dropDive(event);

				};

			}
		}

	};
	this.makeDive = function(i, tx) {

		var x = this;
		var o = this.divix;

		if (i) {
			var s = (this.subtitle) ? "photo,destak" : "photo,gallery";
			var ii = i.split(",");
			var ar = o.getElementsByTagName('div').length;

			if (ar < this.ni) {

				var np = (this.nameImage) ? this.nameImage + "[]" : "foto[]";

				var nl = (this.nameSubtitles) ? this.nameSubtitles : "legenda";

				var t = $$.make("div", {
					"draggable" : false,
					"data-index" : ar,
					"data-type" : s,
					"class" : "dvB"
				}, o, null, {
					"dragenter" : DragDrop.over_drag_id,
					"dragover" : DragDrop.over_drag_id
				});
				t.ondrop = function(event) {

					x.dropDive(event);
				};
				var de = $$.make("img", {
					"draggable" : false,
					"src" : "imagens/minidel.png",
					"data-action" : "delthis",
					"class" : "ig15A"
				}, t);
				de.onclick = x.dilite;

				var im = $$.make("img", {
					"src" : ii[1],
					"draggable" : true,
					"class" : "ig150xp150x",
					"data-type" : s
				}, t, null, {
					"dragenter" : DragDrop.over_drag_id,
					"dragover" : DragDrop.over_drag_id
				});
				im.ondragstart = function(event) {
					x.initdrag(event);
				};
				im.ondragend = DragDrop.clean;

				$$.make("input", {
					"type" : "hidden",
					"value" : ii[0],
					"name" : np
				}, t, null, null);

				if (this.lang) {
					for (var b = 0; b < this.lang.length; b++) {

						var txt = (tx) ? tx[b] : "";

						var tt = $$.make("p", {
							"draggable" : false,
							"class" : "dv98pC"
						}, t);

						$$.make("img", {
							"src" : FLAGS[this.lang[b]],
							"class" : "ig20M",
							"draggable" : false
						}, tt, tx);
						$$.make("input", {
							"type" : "text",
							"name" : this.lang[b] + "_" + nl + "[" + ii[0] + "]",
							"class" : "tx90px35pxFF",
							"draggable" : false,
							"value" : txt
						}, tt);
					}
				}

				return t;
			} else {
				return false;
			}
		}
	};
	this.dilite = function(event) {
		var t = event.target;
		while (t.nodeName != "DIV") {
			t = t.parentNode;
		}
		$(t).remove();
	};
	this.initdrag = function(event) {

		var s = (this.subtitle) ? "destak" : "gallery";

		var t = $$.slf(event);
		while (t.nodeName != "DIV") {
			t = t.parentNode;
		}

		var a = t.getAttribute("data-index");

		DragDrop.set_drag_data(event, s, a);
	};
	this.dropDive = function(event) {

		var a,
		    d,
		    t,
		    data = null,
		    z = $(this.divix);

		try {
			event.stopPropagation();
			event.preventDefault();
		} catch(e) {
			event.returnValue = false;
		}

		event.dataTransfer.effectAllowed = "copy";
		data = event.dataTransfer.getData("Text").split(",");

		t = $(event.target).parent(".dvB") || z;

		var n = new Number(t.attr("data-index"));

		if (DragDrop.mode == "photo") {
			if ( d = this.makeDive(data[1] + "," + data[2], null)) {
				if (t == z) {
					z.append(d);
				} else {
					$(d).insertBefore(t);
				}
			} else {
				event.returnValue = false;
				$$.erro("Já atingiu o numero máximo de imagens.");
			}

		}

		var nel = z.children();

		if (DragDrop.mode == "gallery") {
			for ( ne = 0; ne < nel.length; ne++) {
				if (data[0] == $(nel[ne]).attr("data-index")) {
					if (t.attr("data-index") < data[0]) {
						$(nel[ne]).insertBefore(t);
						break;
					} else {
						$(nel[ne]).insertAfter(t);
						break;
					}

				}
			}
		}

		nel = z.children();

		for ( nex = 0; nex < nel.length; nex++) {

			$(nel[nex]).attr("data-index", nex);
		}

		DragDrop.clean(event);
		return false;
	}, this.cancelDrag = function() {
		return false;
	};
}

function jq(myid) {

	if (myid)
		return "#" + myid.replace(/(:|\.|\[|\])/g, "\\$1");

}

function uploadProg(event) {

	var a = document.getElementById("footer").getElementsByTagName("td")[1];
	a.innerHTML = "0%";
	a.innerHTML = Math.round(event.bytesLoaded / event.bytesTotal * 100) + " %";
}
