<?php
session_start();
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Dinamic Gestor de Conteúdos</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=1.0,user-scalable=no">
		<meta http-equiv='X-UA-Compatible' content='IE=10'>
		<link rel="shortcut icon" href="imagens/favicon.ico"  type="image/x-icon">
		<link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/mgp_nucleogest.js" ></script>
		<script type="text/javascript" src="js/mgpdinamic_modulos.js" ></script>
		<link href="css/log_estilos.css" rel="stylesheet" type="text/css">
		<link href="css/styles_v5.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<div id="mainContainer">
			<div id="FrtBs" class="divLogin">
			    <img src="imagens/dinamicgest_logo.png" alt="mgpdinamic dinamicgest logo" class="dglogo">
				<form method="post" action="loginadm" name="regitro" id="regitro">
				    <div class="tablog">

					<div class="log2 user" >
						<input type="text" id="nick" placeholder="administrador">
					</div>
					<div class="log2 pass" >
						<input type="password" id="senha" placeholder="senha">
					</div>
					<div class="log2">
						<img src="imgauth.php" class="digit"><input type="text" size="4" name="codigoimg" maxlength="4" id="code" placeholder="digite os numeros">
					</div>
					<div>
						<input type="button" id="Submit3" value="Enviar" style="cursor:pointer">
					</div>
					<div class="log2" id="tdaviso"></div>
					</div>
				</form>
			</div>
		</div>
	</body>
</html>
