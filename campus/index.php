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
        <title>Bem-vindo(a) a Área de Membros do <?= SITE_NAME; ?> - Entrar!</title>
        <meta name="description" content="<?= SITE_DESC; ?>"/>
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0"/>
        <meta name="robots" content="index, follow"/>

        <link rel="base" href="<?= BASE; ?>/campus">
        <link rel="canonical" href="<?= BASE; ?>/campus"/>

        <meta itemprop="name" content="Bem-vindo(a) a Área de Membros do <?= SITE_NAME; ?> - Entrar!"/>
        <meta itemprop="description" content="<?= SITE_DESC; ?>"/>
        <meta itemprop="image" content="<?= INCLUDE_PATH; ?>/images/default.jpg"/>
        <meta itemprop="url" content="<?= BASE; ?>/campus"/>

        <meta property="og:type" content="article" />
        <meta property="og:title" content="Bem-vindo(a) a Área de Membros do <?= SITE_NAME; ?> - Entrar!" />
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
        <meta property="twitter:title" content="Bem-vindo(a) a Área de Membros do <?= SITE_NAME; ?> - Entrar!" />
        <meta property="twitter:description" content="<?= SITE_DESC; ?>" />
        <meta property="twitter:image" content="<?= INCLUDE_PATH; ?>/images/default.jpg" />
        <meta property="twitter:url" content="<?= BASE; ?>/campus" />

        <link rel="shortcut icon" href="<?= INCLUDE_PATH; ?>/img/favicon.png"/>
        <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800' rel='stylesheet' type='text/css'>
        <link href='https://fonts.googleapis.com/css?family=Source+Code+Pro:300,500' rel='stylesheet' type='text/css'>

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
                    <input type="hidden" name="callback" value="Login">
                    <input type="hidden" name="callback_action" value="login">

                    <label class="label">
                        <input type="email" name="user_email" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(_img/mail.png) left 15px center no-repeat;" value="<?= $Cookie ? $Cookie : ''; ?>" placeholder="Digite seu E-mail:" required/>
                    </label>

                    <label for="pass" class="label">
                        <input id="pass" type="password" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(_img/key.png) left 15px center no-repeat;" name="user_password" placeholder="Digite sua Senha:" required/>
                    </label>

                    <a style="margin-top: 5px" class="fl_right login_link" href="<?= BASE; ?>/campus/recuperar.php">Esqueceu da Senha?</a>

                    <label class="label_check small">
                        <input id="pass-status" aria-hidden="true" onClick="viewPassword()" type="checkbox" name="" style="top: 10px;"> Exibir Senha
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
