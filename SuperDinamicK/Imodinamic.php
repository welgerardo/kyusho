<?php

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
    DINAMICSHOP V1.1
    25-06-2011
 */
require "config.php";
//require_once "Verifica.php";
//require_once "logex.php";
//require_once "Anti.php";

//require 'SContacto.php';
class Imodinamic extends SCore {

    public $table;
    
    //class contactos
    private $form;
    
    //dados da tabela pricipal
	private $principal;
	
	//url para imovel individual
	public $imoURL;
	
	   
    //meta descrição
    public $description;
    //titulo da pagina
    public $titlepage;
	
	//meta keywords
	public $metakeys;

	
	
	

    public function  __construct() {
        $this->form = new SContacto();
        
        $q=mysql_query("SELECT * FROM principal WHERE id='1'");
		$this->principal=mysql_fetch_array($q);
    }

 


/*
 * cria a uma pagina que mostra os imoveis
 */
public function showImoveis(){
			
		
		if($_POST['negocio']){$ngc="negocio='$_POST[negocio]' AND";}
		if($_POST['tipo']){$tp="tipo='$_POST[tipo]' AND";}
		if($_POST['tipologia']){$tplg="tipologia='$_POST[tipologia]' AND";}
		if($_POST['cidade']){$cdd="cidade='$_POST[cidade]' AND";}
		if($_POST['freguesia']){$frgs="freguesia='$_POST[freguesia]' AND";}
		if($_POST['imo_estado']){$std="imo_estado='$_POST[imo_estado]' AND";}
		
		$pesquisa="$ngc $tp $tplg $cdd $frgs $std ";
			
		$pagex=($_POST['page'])?$_POST['page']*8:0;
        
        $imoveis = NULL;
        $iresult=mysql_query("SELECT * FROM $this->table WHERE $pesquisa estado='online' ORDER BY order_index DESC LIMIT $pagex , 9");

        while($imo=mysql_fetch_array($iresult)){$imoveis .= $this->imovel3($imo);}
		if($imoveis){return $imoveis;}
		else{
	$i="
	
		<input type='hidden' name='negocio' value='$_POST[negocio]'>
		<input type='hidden' name='tipo' value='$_POST[tipo]'>
		<input type='hidden' name='tipologia' value='$_POST[tipologia]'>
		<input type='hidden' name='cidade' value='".$this->nomeCidade($_POST['cidade'])."'>
		<input type='hidden' name='freguesia' value='$_POST[freguesia]'>
		<input type='hidden' name='imo_estado' value='$_POST[imo_estado]'>
	
	";		
			
	return "
	<div class='returnsearch'>
	<p>Não foram encontrados imóveis para a sua pesquisa.</p><p> Se pretende ser informado quando estiverem disponiveis imóveis com estas caracteristicas, por favor, envie o formulário.</p>
	".$this->form->formMessagePlus(NULL,"Pesquisa de imóveis.",$i)."
	</div>
	
	";}
        
    }
    
private function destak($d){
        if($d){return "<div class='destk'><img src=$d></div>";}
        else{return "";}
        
    }



/*
 * cria o visão do imovel para a pagina outros imoveis
 */
private function imovel($i){
	
	$img=($this->makePhotoGalleryWC($i['fotos_imovel'],"class='ftimovelimg' alt='foto do imóvel'",TRUE,"img"))?$this->makePhotoGalleryWC($i['fotos_imovel'],"class='ftimovelimg' alt='foto do imóvel'",TRUE,"img"):"<img src='"._RURL."imagens/semimg.png' class='ftimovelimg' />";	
      	
      $imov=  "
      	<div class='imovel'>
			<div  class='ftimovel'>
					".$this->destak($i['foto_destaque'])."
					<a href='".$this->imoURL."$i[id]/$i[titulo]'>
						$img
					</a>			 	
			</div>

			<div class='dsimovel'>
			<p class='ptitulo'>$i[titulo]</p>
			<p class='pdesc'>$i[tipo] $i[tipologia]</p>
			<p class='pdesc'>$i[freguesia],".$this->nomeCidade($i['cidade'])."</p>
			<p class='pdesc'>". number_format($i['preco'],2,',',' ')."€</p>
			<a href='".$this->imoURL."$i[id]/$i[titulo]' class='adsimovel'>saiba mais</a>
			
			</div>
		</div>
	";
	
	return $imov;
}
private function imovel2($i){
	
	$img=($this->makePhotoGalleryWC($i['fotos_imovel'],"class='imovel2foto' alt='foto do imóvel'",TRUE,"img"))?$this->makePhotoGalleryWC($i['fotos_imovel'],"class='imovel2foto' alt='foto do imóvel'",TRUE,"img"):"<img src='"._RURL."imagens/semimg.png' />";	
      	
      $imov=  "
      
      <div class='imovel2'>
	<div class='imovel2fotodiv'>
	".$this->destak($i['foto_destaque'])."
					<a href='".$this->imoURL."$i[id]/$i[titulo]'>
						$img
					</a>
	</div>
	
	<div class='imovel2txtdiv'>
	<table>
	
	<tr>
	<td class='imovel2txtdivt'>negocio:</td>
	<td><h2 class='imovel2txtdivtc'>$i[negocio]</h2></td>
	</tr>
	
	<tr>
	<td class='imovel2txtdivt'>ref:</td>
	<td class='imovel2txtdivtc' id='ref'>$i[ref]</td>
	</tr>
	
	
	
	<tr>
	<td class='imovel2txtdivt'>tipo:</td>
	<td><h2 class='imovel2txtdivtc'>$i[tipo]</h2></td>
	</tr>
	<tr>
	<td class='imovel2txtdivt'>tipologia:</td>
	<td><h2 class='imovel2txtdivtc'>$i[tipologia]</h2></td>
	</tr>
	
	$estadx
	
	<tr>
	
	<td class='imovel2txtdivt'>freguesia:</td>
	<td><h2 class='imovel2txtdivtc'>$i[freguesia]</h2></td>
	</tr>
	
	<tr >
	<td class='imovel2txtdivt'>cidade:</td>
	<td><h2 class='imovel2txtdivtc'>".$this->nomeCidade($i[cidade])."</h2></td>
	</tr>
	
	<tr>
	<td class='imovel2txtdivtc' colspan='2' style='text-align:center'><div class='imovel2txtpc'>". number_format($i[preco],2,',',' ')."€</div></td>
	</tr>
	
	
	

	</table>
	
	<a href='".$this->imoURL."$i[id]/$i[titulo]' class='imovel2a'>
	<img src='/imagens/maisinfo.png' class='imovel2info' alt='mais informações'>
	</a>
	<img src='/imagens/solvisita.png' class='imovel2visit' id='_$i[id]' alt='solicitar visita'><br/>
	<img src='/imagens/enviar.png' class='imovel2friend' id='_$i[id]' alt='enviar a um amigo'>
	</div>
	</div>
      
      
      	
	";
	
	return $imov;
}
private function imovel3($i){
	
	$img=($this->makePhotoGalleryWC($i['fotos_imovel'],"class='ftimovelimg3' alt='foto do imóvel'",TRUE,"img"))?$this->makePhotoGalleryWC($i['fotos_imovel'],"class='ftimovelimg3' alt='foto do imóvel'",TRUE,"img"):"<img src='"._RURL."imagens/semimg.png' class='ftimovelimg3' />";	
      	
      $imov=  "
      	<div class='imovel3'>
			<div  class='ftimovel3'>
					".$this->destak($i['foto_destaque'])."
					<a href='".$this->imoURL."$i[id]/$i[titulo]'>
						$img
					</a>			 	
			</div>
			<div class='wdsimovel3'>
			<div class='dsimovel3'>
			<p class='ptitulo3'>$i[titulo]</p>
			<p class='pdesc3'>$i[negocio]</p>
			<p class='pdesc3'>ref: $i[ref]</p>
			<p class='pdesc3'>$i[tipo] $i[tipologia]</p>
			<p class='pdesc3'>$i[freguesia],".$this->nomeCidade($i['cidade'])."</p>
			<p class='pdesc3'>". number_format($i['preco'],2,',',' ')."€</p>
			<a href='".$this->imoURL."$i[id]/$i[titulo]' class='adsimovel3'>saiba mais</a>
			</div>
			</div>
		</div>
	";
	
	return $imov;
}
/*
 * cria a página individual de um imovel
 */
public function paginaImovel($id){
			
		if(is_numeric($id)){
				
				
			
		$q=mysql_query("SELECT * FROM $this->table WHERE id=$id AND estado='online'");
		$imo=mysql_fetch_array($q);
		
		$pl=$this->cleanText($imo['planta'],80);
		$eq=$this->cleanText($imo['equip'],80);
		$ac=$this->cleanText($imo['acab'],80);
		$ed=$this->cleanText($imo['edificio'],80);
		$ar=$this->cleanText($imo['areaenv'],80);
		
		$this->description=$this->limitText($imo['descricao'],250);
		$this->titlepage=$this->cleanText($imo['titulo'],155);
		$this->metakeys=$this->cleanText($this->nomePais($imo['pais'])." ".$this->nomeCidade($imo['cidade'])." $imo[imo_estado]  $imo[negocio] $imo[freguesia] $imo[tipo] $imo[tipologia] $pl $eq $ap $ed $ar",300);
		
		$img=($this->makePhotoGalleryWC($imo['fotos_imovel'],"class='mainfoto' alt='foto do imóvel'",TRUE,"img"))?$this->makePhotoGalleryWC($imo['fotos_imovel'],"class='mainfoto' alt='foto do imóvel'",TRUE,"img"):"<img src='"._RURL."imagens/semimg.png' class='mainfoto'/>";
		
		if($imo['endereco'] && $imo['show_end']!='nao'){
			$enderco="<li id='local' class='inavli'>local</li>";
			$scp="<script type='text/javascript'>var _AD=\"$imo[endereco], ".$this->nomeCidade($imo['cidade']).", ".$this->nomePais($imo['pais'])."\"</script>";
		}
		
		return "
			$scp
			<div class='icab'>
				<h1 class='ihcab'>
					<span class='iref'>
						$imo[negocio] - $imo[ref]
					</span>
					<span class='ipreco'>
						$imo[titulo] 
					</span>
				</h1>
			</div>
			
			<div class='ifotos'>
				<div class='imainfoto'>
					$img
				</div>
				
				<div class='igaleria'>
					
					
							".$this->makePhotoGalleryWC($imo['fotos_imovel'],"class='galfoto'",FALSE,"img")."
						
					
				</div>
				
				<div class='isocial'>
				<div class='butgmais'>
				<div class='g-plusone' data-size='medium' data-annotation='none' ></div>
				<script type='text/javascript'>
				  window.___gcfg = {lang: 'pt-PT'};
				
				  (function() {
				    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
				    po.src = 'https://apis.google.com/js/plusone.js';
				    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
				  })();
				</script>
				</div>
				<div class='butface'>
					<div class='fb-like' data-href='".urldecode($this->imoURL.$imo['id']."/".$imo['titulo'])."' data-send='true' data-width='100' data-show-faces='false'  data-layout=\"button_count\"></div>
				</div>
				<div>
				<a href='http://pinterest.com/pin/create/button/?url=".urldecode($this->imoURL.$imo['id']."/".$imo['titulo'])."&media=".$this->makePhotoGalleryWC($imo['fotos_imovel'],NULL,TRUE,"src")."&description=$this->description' class='pin-it-button' count-layout='none'><img border='0' src='//assets.pinterest.com/images/PinExt.png' title='Pin It' /></a>
				
				</div>
				</div>
			
			</div>
			
			<div class='idet'>
			
				<div class='iender'>
					
					<div class='ipreco'>".number_format($imo[preco],2,',',' ')."€</div>
					<h2>$imo[freguesia], ".$this->nomeCidade($imo[cidade])."</h2>
				</div>
				
				<div class='inav'>
					<ul id='navimo'>						
						<li id='caracteristicas' class='inavli'>caracteristicas</li>
						<li id='descricao' class='inavli'>descrição</li>
						$enderco
						<li id='visitar' class='inavli'>visitar imovel</li>
						<li id='enviar' class='inavli'>enviar amigo</li>
					</ul>
				</div>
				
				<div class='ipalco' >
				<div class='bloq' ><img class='navloader' src='"._RURL."imagens/35.gif'/></div>
					<div id='imopalco'>".$this->imoCaract($id)."</div>
				</div>
				
				
			
			</div>
			
			<div class='imais'>
				
				<h4 class='imaistit'>
					Outras ofertas
				</h4>
				
				".$this->maisImoveis($id,$imo[negocio])."
			
			</div>	
		
		";
	}
 }
/*
 * apresenta outros imoveis na pagina dos imoveis
 * $I = id do imovel que deve ser excluido da seleção
 * $N = tipo de negócio
 */
 public function maisImoveis($I,$N){
 	
	$qi=mysql_query("SELECT * FROM $this->table WHERE estado='online' AND id<>'$I' AND negocio='$N' ORDER BY RAND() LIMIT 5");
	while($imos=mysql_fetch_array($qi)){
			
		$r .=$this->imovel($imos);
	}
	
	return $r;
 	
 }
 /* 
  * envia a descrição de um imovel
  */
 public function descricao($ID){
 		
 	if(is_numeric($ID)){
			
		$q=mysql_query("SELECT descricao FROM $this->table WHERE id=$ID AND estado='online'");
		$desc=mysql_fetch_array($q);
		
		return $desc[0];
	}
 }
  /*
  * envia formulário para visitar o imovel
  */
 public function visita($ID){
 		
 	if(is_numeric($ID)){
			
		$q=mysql_query("SELECT ref FROM $this->table WHERE id=$ID AND estado='online'");
		$desc=mysql_fetch_array($q);
		
		return $this->form->formMessage(NULL,"Pedido de visita ao imovel ref: ".$desc[0]);
	}
 } 
  /*
  * envia formulário para enviar para um amigo
  */
 public function enviar($ID){
 		
 	if(is_numeric($ID)){
			
				
		return $this->form->formFriend(NULL,"Sugestão de imóvel.",$this->imoURL.$ID,$ID);
	}
 } 
    
/*
 * caracteristicas do imovel
 */
  public function imoCaract($ID){
  		
  	if(is_numeric($ID)){
  		
  		$q=mysql_query("SELECT * FROM $this->table WHERE id=$ID AND estado='online'");
		$carct=mysql_fetch_array($q);	
		
		if($carct['endereco'] && $carct['show_end']!='nao'){
			
			$endereco="<tr>
					<td colspan='2'>
						<p class='icaractablep'>endereço:</p><span id='endereco'>".wordwrap($carct['endereco'],61,"<br>",TRUE)."</span>
					</td>
				</tr>";
		}
		if($carct['planta']){
			
			$planta="<tr>
					<td colspan='2'>
						<p class='icaractablep'>planta:</p><span>".wordwrap($carct['planta'],61,"<br>",TRUE)."</span>
					</td>
				</tr>";
		}
		if($carct['equip']){
			
			$equip="<tr>
					<td colspan='2'>
						<p class='icaractablep'>equipamentos:</p><span>".wordwrap($carct['equip'],61,"<br>",TRUE)."</span>
					</td>
				</tr>";
		}
		
		if($carct['acab']){
			
			$acab="<tr>
					<td colspan='2'>
						<p class='icaractablep'>acabamentos:</p><span>".wordwrap($carct['acab'],61,"<br>",TRUE)."</span>
					</td>
				</tr>";
		}
		
		if($carct['edificio']){
			
			$edif="<tr>
					<td colspan='2'>
						<p class='icaractablep'>edificio:</p><span>$carct[edificio]</span>
					</td>
				</tr>";
		}
		
		if($carct['areaenv']){
			
			$area="<tr>
					<td colspan='2'>
						<p class='icaractablep'>àrea envolvente:</p><span>$carct[areaenv]</span>
					</td>
				</tr>";
		}
		
		return "
		
			<table class='icaractable'> 
				$endereco
				<tr>
					<td class='icaractabletdt'>
						<div class='icaractablespant'>tipo:</div><div>$carct[tipo]</div>
					</td>
					<td class='icaractabletdt'>
						<div class='icaractablespant'>tipologia:</div><div>$carct[tipologia]</div>
					</td>
				</tr>
				<tr>
					<td class='icaractabletdt'>
						<div class='icaractablespant'>estado:</div><div>$carct[imo_estado]</div>
					</td>
					<td class='icaractabletdt'>
						<div class='icaractablespant'>área bruta:</div><div>$carct[area]</div>
					</td>
				</tr>
				<tr>
					<td class='icaractabletdt'>
						<div class='icaractablespant'>nº de quartos:</div><div>$carct[quartos]</div>
					</td>
					<td class='icaractabletdt'> 
						<div class='icaractablespant'>nº de quartos de banho:</div><div>$carct[quartos_de_banho]</div>
					</td>
				</tr>
				<tr>
					<td class='icaractabletdt'>
						<div class='icaractablespant'>orientação:</div><div>$carct[orientacao]</div>
					</td>
					<td class='icaractabletdt'>
						<div class='icaractablespant'>garagem:</div><div>$carct[garagem]</div>
					</td>
				</tr>
				$planta
				$equip
				$acab
				$edif
				$area
			</table>
		
		
		";
  		
  		}
  }
	

public function imomes(){
			
		
	$imo=mysql_query("SELECT * FROM $this->table WHERE imo_mes='sim' AND estado='online'");
	$imoMes=mysql_fetch_array($imo);
	
	$dtq=($imoMes['foto_destaque'])?"<img class='imesfotod' src='$imoMes[foto_destaque]'>":"";
			
		return "
		
			<h2 class='imesdestaque'>Imóvel em destaque</h2>
			<div class='imesfotogal'>
				$dtq
				".$this->makePhotoGalleryWC($imoMes['fotos_imovel'],"class='imesfoto'",TRUE,"img")."
			</div>
			<div class='lay'>         
				<table >
				<tr> 
			    <td class=''><span class='fontimdes'>$imoMes[tipo] $imoMes[tipologia]</span></td>
			    <td class='fontimdes'>".number_format($imoMes['preco'],2,',',' ')."€</td>
			    </tr>
			    <tr> 
			    <td class='fontimdes'>$imoMes[freguesia] $imoMes[cidade]</td>
			    <td class='fontimdes'>$imoMes[negocio]</td>
			    </tr>
			    <tr> 
			    <td>&nbsp;</td>
			    <td colspan='2' class='det'>&nbsp;</td>
			    </tr>
			    <tr> 
			    <td>
				<h5>".$this->limitText($imoMes['descricao'],400)."</h5>
			    </td>
			    </tr>
			    </table>
		    </div>
    
		
		";
	
}

public function imomes2(){
			
		
	$imo=mysql_query("SELECT * FROM $this->table WHERE imo_mes='sim' AND estado='online'");
	$imoMes=mysql_fetch_array($imo);
			
		return "
		<script type='text/javascript'>
		var _IMES=[$imoMes[fotos_imovel]]
		</script>
		
	<div class='imestxt'>         
	<table>
	<tr>
	<td colspan='2' class='imestxtt'>
	$imoMes[titulo]
	</td>
		
	</tr>
	<tr> 
	<td class='imestxtl'>$imoMes[tipo] $imoMes[tipologia]</td>
	<td class='imestxtr'>$imoMes[negocio]</td>
	</tr>
	<tr> 
	<td class='imestxtl'>$imoMes[freguesia], ".$this->nomeCidade($imoMes['cidade'])."</td>
	<td class='imestxtr'>".number_format($imoMes[preco],2,',',' ')."€</td>
	</tr>
	<tr> 
	<td colspan='2' class='imestxtd'>".$this->limitText($imoMes['descricao'],400)."</td>
	</tr>
	<tr> 
	<td ><a href='$this->imoURL$imoMes[id]/$imoMes[titulo]' class='imestxta'>+ informação »</a></td>
	<td ></td>
	</tr>
	</table>
	
	
	</div>
	
	
	<div id='fotomes'>".$this->makePhotoGalleryWC($imoMes['fotos_imovel'],"class='imodes'",TRUE,"img")."</div> 
		
	
    
		
		";
	
}
public function imoveisCapaV(){
		
	$qCidade="SELECT * FROM $this->table WHERE master_cat='novo-venda' AND estado='online' OR master_cat='usado-venda' AND estado='online' ORDER BY order_index DESC LIMIT 9";
	$qCidade2=mysql_query($qCidade);
	$loop=0;
	while($cidade=mysql_fetch_array($qCidade2)){
			
		$icv .=$this->imovel($cidade);
	}
	
	return $icv;
	
}
public function imoveisCapaA(){
		
	$qCidade="SELECT * FROM $this->table WHERE master_cat='novo-arrendamento' AND estado='online' OR master_cat='usado-arrendamento' AND estado='online' ORDER BY order_index DESC LIMIT 6";
	$qCidade2=mysql_query($qCidade);
	$loop=0;
	while($cidade=mysql_fetch_array($qCidade2)){
			
		$icv .=$this->imovel($cidade);
	}
	
	return $icv;
	
}
}
?>
