<?php
$a = $_POST['flag'];
if ($a && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        
    require_once '/var/www/vhosts/kyusho.pt/SuperDinamicK/SComuns.php';
    require_once '/var/www/vhosts/kyusho.pt/SuperDinamicK/SConfig.php';
    
    $pp = new SComuns();
    
    switch ($a) {
        case "sendmess" :
            echo $pp -> sendMess();
            break;
        case "sendmessplus" :
            echo $pp -> send_mess_plus();
            break;
        case "sendnews" :
            echo $pp -> send_news();
            break;
        case "sendfriend" :
            echo $pp -> sendFriend();
            break;
        case "sendremove" :
            echo $pp -> unsubscrive_newsletter($_POST['em']);
            break;
    }
} else {    Header("Location:" . _PUREURL);
    exit ;
}
?>