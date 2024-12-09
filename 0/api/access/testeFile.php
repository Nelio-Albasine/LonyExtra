<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/SignUp_Erros.log');


$handleInvitationDIR = "../invitation/handleInvitation.php";
if (file_exists($handleInvitationDIR)) {
    require_once $handleInvitationDIR;

    echo("Arquivo  existe!");
} else {
    echo("Arquivo handleInvitation.php nao existe!");
}
