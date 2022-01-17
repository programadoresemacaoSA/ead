<?php
ob_start();
session_start();
require '../_app/Config.inc.php';

$Cookie = filter_input(INPUT_COOKIE, 'workcontrol', FILTER_VALIDATE_EMAIL);
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="mit" content="2017-01-31T14:45:43-02:00+7467">
    <title>Recuperar Senha - <?= SITE_NAME; ?>!</title>
    <meta name="description" content="<?= SITE_DESC; ?>"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0"/>
    <meta name="robots" content="noindex, nofollow"/>

    <link rel="shortcut icon" href="<?= INCLUDE_PATH; ?>/images/favicon.png"/>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Source+Code+Pro:300,500' rel='stylesheet' type='text/css'>

    <link rel="base" href="<?= BASE; ?>/campus/">

    <link rel="stylesheet" href="<?= BASE; ?>/campus/_css/reset.css">
    <link rel="stylesheet" href="<?= BASE; ?>/campus/_css/login_style.css">
    <link rel="stylesheet" href="../_cdn/bootcss/fonticon.css"/>

    <script src="../_cdn/jquery.js"></script>
</head>
<body>
<div class="login">
    <div class="login_box">
        <img class="login_box_logo" src="_img/logo.png" alt="<?= SITE_NAME; ?>" title="<?= SITE_NAME; ?>"/>
        <div class="login_box_content radius">
            <form class="" name="work_login" action="" method="post" enctype="multipart/form-data">
                <div class="trigger trigger_info m_botton" style="border-radius: 5px">Informeu seu e-mail abaixo. Você receberá uma link para recuperar sua senha!</div>
                <div class="callback_return m_botton"></div>
                <input type="hidden" name="callback" value="Login">
                <input type="hidden" name="callback_action" value="recover">

                <label class="label">
                    <input type="email" name="user_email" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(_img/mail.png) left 15px center no-repeat;" value="<?= $Cookie ? $Cookie : ''; ?>" placeholder="Digite seu E-mail:" required/>
                </label>

                <img class="form_load none" style="float: right; margin-top: 3px; margin-left: 10px;width: 40px" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.svg"/>
                <button class="login_btn">Obter Nova Senha!</button>
            </form>
        </div>
        <p class="register">Já possui conta? <a href="<?= BASE; ?>/campus">Conecte-se aqui!</a></p>
    </div>
</div>

<script src="../_cdn/jquery.form.js"></script>
<script src="_js/maskinput.js"></script>
<script src="_js/campus.js"></script>
</body>
</html>
<?php
ob_end_flush();
