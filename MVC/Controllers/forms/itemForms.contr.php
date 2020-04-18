<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itemForm
 *
 * @author Gerardo
 */
class itemForms {
    
    private $Reg;
    private $Model;



    public function __construct(MGPDINAMIC $registry) {
        
        $this->Reg = $registry;
        
        require 'Models/forms/MForms.mod.php';
        $this->Model = new MForms($registry);
    }
    
    public function sendMessPlus()
    {
        
        $this->Reg->storeObject("Anti", "anti");

        $Anti = $this->Reg->getObject("anti");
        $Core = $this->Reg->getObject("core");

        $nome    = $Anti-> verificaNome($_POST['nome']);
        $fone    = $Anti -> verificaTexto($_POST['tele']);
        $mail    = $Anti -> validateEmail($_POST['mail']);
        $assunto = $Anti -> verificaTexto($_POST['assunto']);
        $mens    = $Anti -> verificaTexto($_POST['mess']);

        $e1 = (!$assunto) ? 1 : 0;
        $e2 = (!$nome) ? 2 : 0;
        $e4 = (!$mail) ? 4 : 0;
        $e5 = (!$mens) ? 5 : 0;
        $e6 = 0;

        if ($_POST['type'] && $_POST['filter'])
        {

            $prod = $Anti -> verificaTexto($_POST['type']);
            $filtro = $Anti -> verificaTexto($_POST['filter']);

            $e7 = (is_numeric($prod)) ? 0 : 1;
            $e8 = ($filtro  !== "curso" || $filtro  !== "products") ? 0 : 1;

        }
        else
        {

            $prod = NULL;
            $filtro  = NULL;

            $e7 = 0;
            $e8 = 0;
        }

        if ($e1 || $e2 || $e6 || $e4 || $e5 || $e7 || $e8)
        {
            return "{\"error\":[$e1,$e2,0,$e4,$e5]}";

        }

        
        $mens = "Solicitação de mais informações para " . html_entity_decode($assunto, ENT_QUOTES, "UTF-8") . "<br>-----------------------------------------<br>nome:$nome<br>telefone:$fone<br>e-mail:$mail<br>-----------------------------------------<br>$mens<br><br>-----------------------------------------<br>mensagem enviada através de " . _NOMESITE;

               
        $param[] = $Core -> textCleanJson($mail);
        $param[] = $Core -> textCleanJson($nome);
        $param[] = $Core -> textCleanJson($fone);
        $param[] = $Core -> textCleanJson($assunto);
        $param[] = $Core -> textCleanJson($prod);
        $param[] = $Core -> textCleanJson($filtro );
        $param[] = $Core -> textCleanJson($mens);

        if ($this->Model->saveItemMessage($param))
        {
            return "{\"result\":\"" . _L_SUCESSMESS . "\"}";
        }
        else
        {
            return "{\"result\":\"" . _L_FAILMESS . "\"}";
        }
    }
}


