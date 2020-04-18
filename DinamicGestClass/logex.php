<?php
/*
 * SCRIPT logex.php V4.00 03-06-2011 COPYRIGHT MANUEL GERARDO PEREIRA 2011 TODOS OS DIREITOS RESERVADOS CONTACTO: GPEREIRA@MGPDINAMIC.COM WWW.MGPDINAMIC.COM
 */
require "Anti.php";

class logex extends Anti {
	public $identidade;
	public $autorizacoes;
	public $quem;
	
	// ligação a base de dados
	public static $tcnx = NULL;
	
	function __construct() {
		
		self::$tcnx = new mysqli ( 'localhost', _US, _PS, _DB );
		self::$tcnx->set_charset ( "utf8" );
	}
	
	public function check() {
		if (isset ( $_SESSION ['nick'] )) {
			$nick = $_SESSION ['nick'];
		} else {
			$nick = null;
		}
		if (isset ( $_SESSION ['senha'] )) {
			$senha = $_SESSION ['senha'];
		} else {
			$senha = null;
		}
		
		/*
		 * $nick= mysql_real_escape_string($nick); $senha= mysql_real_escape_string($senha); $ssid = mysql_real_escape_string(session_id());
		 */
		
		$deonde = "mgp";
		
		$q = self::$tcnx->query ( "SELECT id_contacto,nick,senha,tipo_user FROM colaboradores INNER JOIN access_col ON colaboradores.id_contacto = id_colb WHERE nick='$nick' AND senha='$senha'");
		$was = $q->num_rows;
		$resp = $q->fetch_array ();

		if ($nick && $senha && $was === 1) {
			
			$this->identidade = $resp [0];
			$this->autorizacoes = $resp [2];
			$this->quem = $resp [3];
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

?>