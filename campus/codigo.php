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
        <title>Recuperação de Senha - <?= SITE_NAME; ?>!</title>
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
                <form class="" name="work_login" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="callback" value="Login">
                    <input type="hidden" name="callback_action" value="new_code">

                    <label class="label">
                        <input type="text" name="code" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(_img/qr-code.png) left 15px center no-repeat;" autocomplete="off" placeholder="Digite seu código:" required/>
                    </label>

                    <label class="label">
                        <input type="password" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(_img/key.png) left 15px center no-repeat;" name="user_password" autocomplete="off" placeholder="Nova Senha:" required/>
                    </label>

                    <label class="label">
                        <input type="password" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(_img/key.png) left 15px center no-repeat;" name="user_password_re" autocomplete="off" placeholder="Confirme sua nova senha:" required/>
                    </label>

                    <p class="text-center">
                        <img class="form_load none" style="float: right; margin-top: 3px; margin-left: 10px;width: 40px" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.svg"/>
                    </p>

                    <button class="login_btn">Fazer login</button>
                </form>
            </div>
            <?php if (EAD_REGISTER): ?>
                <p class="register" style="margin-top:25px;">Não tem conta? <a href="<?= BASE; ?>/campus/registrar.php">Cadastre-se gratuitamente!</a></p>
            <?php endif; ?>
            <p class="powered">Feito com <i class="icon-heart icon-notext"></i> pela <a href="https://tresmd.com.br" title="Agência Tresmd">Tresmd.</a></p>
        </div>
    </div>


    <!--<div class="login_bg"></div>-->
    <script src="../_cdn/jquery.form.js"></script>
    <script src="_js/maskinput.js"></script>
    <script src="_js/campus.js"></script>

    <!-- Facebook Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '563284047478285');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
                   src="https://www.facebook.com/tr?id=563284047478285&ev=PageView&noscript=1"
        /></noscript>
    <!-- End Facebook Pixel Code -->

    <?php
    if (!empty(SEGMENT_FB_PIXEL_ID)):
        require '../_cdn/wc_track.php';
    endif;
    ?>

    </body>
    </html>
<?php
ob_end_flush();
