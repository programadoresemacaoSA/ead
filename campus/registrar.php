<?php
ob_start();
session_start();
require '../_app/Config.inc.php';

if (isset($_SESSION['userLogin'])):
    header('Location: campus.php?wc=cursos/atividades');
endif;

$Read = new Read;

$Cookie = filter_input(INPUT_COOKIE, 'workcontrol', FILTER_VALIDATE_EMAIL);
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="mit" content="2017-01-31T14:45:43-02:00+7467">
    <title>Cadastre-se Gratuitamente - <?= SITE_NAME; ?>!</title>
    <meta name="description" content="<?= SITE_DESC; ?>"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0"/>
    <meta name="robots" content="index, follow"/>

    <link rel="base" href="<?= BASE; ?>/campus">
    <link rel="canonical" href="<?= BASE; ?>/campus"/>

    <meta itemprop="name" content="Cadastre-se Gratuitamente - <?= SITE_NAME; ?>!"/>
    <meta itemprop="description" content="<?= SITE_DESC; ?>"/>
    <meta itemprop="image" content="<?= INCLUDE_PATH; ?>/images/default.jpg"/>
    <meta itemprop="url" content="<?= BASE; ?>/campus"/>

    <meta property="og:type" content="article" />
    <meta property="og:title" content="Cadastre-se Gratuitamente - <?= SITE_NAME; ?>!" />
    <meta property="og:description" content="<?= SITE_DESC; ?>" />
    <meta property="og:image" content="<?= INCLUDE_PATH; ?>/images/default.jpg" />
    <meta property="og:image:secure_url" content="<?= INCLUDE_PATH; ?>/images/default.jpg" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="628" />
    <meta property="og:url" content="<?= BASE; ?>/campus" />
    <meta property="og:site_name" content="<?= SITE_NAME; ?>" />
    <meta property="og:locale" content="pt_BR" />
    <?php
    if (SITE_SOCIAL_FB_APP):
        echo '<meta property="fb:app_id" content="' . SITE_SOCIAL_FB_APP . '" />' . "\r\n";
    endif;
    ?>

    <meta property="twitter:card" content="summary_large_image" />
    <?php
    if (SITE_SOCIAL_TWITTER):
        echo '<meta property="twitter:site" content="@' . SITE_SOCIAL_TWITTER . '" />' . "\r\n";
    endif;
    ?>
    <meta property="twitter:domain" content="<?= BASE; ?>/campus" />
    <meta property="twitter:title" content="Cadastre-se Gratuitamente - <?= SITE_NAME; ?>!" />
    <meta property="twitter:description" content="<?= SITE_DESC; ?>" />
    <meta property="twitter:image" content="<?= INCLUDE_PATH; ?>/images/default.jpg" />
    <meta property="twitter:url" content="<?= BASE; ?>/campus" />

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
                <input type="hidden" name="callback" value="Users">
                <input type="hidden" name="callback_action" value="wc_ead_register">

                <div class="trigger_ajax"></div>

                <label class="label">
                    <input type="text" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(_img/user.png) left 15px center no-repeat;" name="user_name" placeholder="Informe seu Primeiro nome:" required=""/>
                </label>

                <label class="label">
                    <input type="text" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(_img/user.png) left 15px center no-repeat;" name="user_lastname" placeholder="Informe Seu Sobrenome:" required=""/>
                </label>

                <label class="label">
                    <input type="email" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(_img/mail.png) left 15px center no-repeat;" name="user_email" placeholder="Informe seu e-mail:" required/>
                </label>

                <label class="label_check small">
                    <input type="checkbox" name="user_aware" checked value="1" style="top: 10px"> <small class="termos">Estou ciente dos <a href="<?= BASE; ?>/termos.php" target="_blank">Termos de Uso.</a></small>
                </label>

                <p class="text-center"><img class="form_load none" style="float: right; margin-top: 3px; margin-left: 10px;width: 40px" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.svg"/></p>
                <button class="login_btn">Quero Me Cadastrar </button>
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
