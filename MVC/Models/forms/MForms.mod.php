<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MForms
 *
 * @author Gerardo
 */
class MForms {

    private $DB;

    public function __construct(MGPDINAMIC $resgistry) {

        $this->DB = $resgistry->getObject("core");
    }

    public function saveItemMessage(array $param) {


        try {
            $result = $this->DB->make_call("spSaveMessageFE", $param);
        } catch (Exception $ex) {
            return;
        }


        if (isset($result[0]["ret"])) {

            $ret = json_decode($result[0]["ret"], TRUE);

            if (!empty($ret['mail'])) {
                
                return $this->sendNotification($ret['mail'], $param[3], $param[6]);                
            }
        }
    }

    private function sendNotification($to, $subject, $text) {

        $headers = "Return-Path:" . $to . "\r\n";
        $headers .= "From:" . $to . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        $ass = html_entity_decode($subject, ENT_QUOTES, "UTF-8");
        $mes = html_entity_decode($text, ENT_QUOTES, "UTF-8");

        return imap_mail($to, $ass, $mes, $headers);
    }

}
