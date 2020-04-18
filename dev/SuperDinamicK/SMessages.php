<?php
/*SCRIPT SMessages.php V1.0 para mgpdinamic
 08-02-2013
 COPYRIGHT MANUEL GERARDO PEREIRA 2013
 TODOS OS DIREITOS RESERVADOS
 CONTACTO: GPEREIRA@MGPDINAMIC.COM
 WWW.MGPDINAMIC.COM*/

/*
 * json com os seguintes indices:
 * mail = e-mail
 * nome = nome
 * apelido = apelido
 * fone = telefone
 * morada = morada
 * codigo_postal = codigo postal
 * freguesia = freguesia
 * movel1 = telemovel1
 * movel2 = telemovel2
 * movel3 = telemovel3
 * contribuinte = numero de contribuinte
 * pais = pais
 * cidade = cidade
 * data_nascimento = data de nascimento
 * sexo = sexo
 * id_produto = id do produto
 * tipo_produto = tipo de produto
 * assunto = assunto
 * mensagem = mensagem
 *
 */

ini_set('display_errors', 1);
require_once 'SConfig.php';
require_once 'Anti.php';

class SMessages {

	private $dat;

	private $cnx;

	private $fields;

	public function __construct() {

	}

	public function saveContact() {

		$tcnx = new mysqli(_HT, _US, _PS, _DB);
		$tcnx -> set_charset("utf8");

		$idx = NULL;

		$query = "SELECT id,mail FROM " . _CONTCTB . " WHERE mail='" . $this -> dat['mail'] . "' LIMIT 1;";
		$query .= "SELECT mail FROM " . _EMPTB . " WHERE id=1 LIMIT 1";

		if ($tcnx -> multi_query($query)) {

			$rslt = $tcnx -> store_result();
			$resultMail = $rslt -> fetch_array();
			$rslt -> free();
			$tcnx -> next_result();
			$rslt = $tcnx -> store_result();
			$tMail = $rslt -> fetch_array();
			$this -> dat['sendMail'] = $tMail[0];
			$rslt -> free();

		}

		$tfields = json_decode(_MESSFIELDS, TRUE);

		if (!$resultMail['id']) {

			$fld = "";
			$vld = "";

			foreach ($tfields as $key => $value) {

				$fld .= $value . ", ";
				$vld .= "'" . $this -> dat[$key] . "',";
			}

			$queryGuardaContacto = $tcnx -> query("INSERT INTO " . _CONTCTB . " ( $fld categoria,imagem,pasta,data) VALUES ( $vld 'pessoa','" . _CONTCIMG . "','" . _CONTCFOLDER . "'," . $GLOBALS['NOW'] . ")");

			if ($queryGuardaContacto) {

				$idx = ($tcnx -> insert_id) ? $tcnx -> insert_id : FALSE;

			}

		} else {

			$fld = "";

			foreach ($tfields as $key => $value) {

				if ($this -> dat[$key]) {

					$fld .= $value . "='" . $this -> dat[$key] . "',";
				}

			}

			$tcnx -> query("UPDATE " . _CONTCTB . " SET $fld data_act=" . $GLOBALS['NOW'] . "  WHERE id='$resultMail[id]'");

			$idx = $resultMail['id'];
		}

		$tcnx -> close();

		if ($this -> saveMessage($idx)) {

			return TRUE;

		} else {

			return FALSE;
		}

	}

	private function saveMessage($id) {

		$tcnx = new mysqli(_HT, _US, _PS, _DB);
		$tcnx -> set_charset("utf8");

		$qMensagem = "INSERT INTO " . _MESSTB . " (id_contacto,id_produto,tipo_produto,data,assunto,texto,anexo) VALUES ('$id','" . $this -> dat['id_produto'] . "','" . $this -> dat['tipo_produto'] . "','" . $GLOBALS['NOW'] . "','" . $this -> dat['assunto'] . "','" . $this -> dat['mensagem'] . "','" . $this -> dat['anexo'] . "')";
		$qMensagem2 = $tcnx -> query($qMensagem);

		echo "##" . $tcnx -> error;

		if ($qMensagem2) {

			return TRUE;

		} else {

			return FALSE;
		}

		$tcnx -> close();

	}

	public function sendMail($json) {

		$this -> dat = json_decode($json, TRUE);

		if ($this -> saveContact()) {

			$headers = "Return-Path:" . $this -> dat['sendMail'] . "\r\n";
			$headers .= "From:" . $this -> dat['sendMail'] . "\r\n";
			$headers .= "Content-type: text/html; charset=UTF-8\r\n";
			$headers .= "MIME-Version: 1.0\r\n";

			$ass = html_entity_decode($this -> dat['assunto'], ENT_QUOTES, "UTF-8");
			$mes = html_entity_decode($this -> dat['mensagem'], ENT_QUOTES, "UTF-8");

			$m =TRUE;#imap_mail($this -> dat['sendMail'], $ass, $mes, $headers);

			if ($m) {

				return TRUE;

			} else {

				return FALSE;

			}
		} else {

			return FALSE;

		}

	}

	public function saveComent($id) {

		$qMensagem = "INSERT INTO comentarios (id_contacto,post_id,data,comentario) VALUES ('$id','$this->assunto','$this->datex','$this->mensagem')";
		$qMensagem2 = mysql_query($qMensagem);
		if ($qMensagem2) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

}
?>