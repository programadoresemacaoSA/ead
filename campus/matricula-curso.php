<?php
ob_start();
session_start();
require '../_app/Config.inc.php';

$Read = new Read;

$setUrl = explode("/", strip_tags(filter_input(INPUT_GET, "url", FILTER_DEFAULT)));
$CourseId = filter_input(INPUT_GET, 'course', FILTER_VALIDATE_INT);

$Read->exeRead(DB_EAD_COURSES,"WHERE course_id = :id","id={$CourseId}");
if(!$Read->getResult()):
    //redirecionar para uma pagina;
else:
    if($Read->getResult()[0]['course_free'] == 1):
        //curso free
        $CourseId = $Read->getResult()[0]['course_id'];
        $CourseTitle = $Read->getResult()[0]['course_title'];
        $CourseDesc = $Read->getResult()[0]['course_headline'];
        $CourseCover = $Read->getResult()[0]['course_cover'];
    else:
        //redirecionar para pagina de pagamento ou 404 ou index pagina com uma mensagem
    endif;
endif;
?><!DOCTYPE html>
<html lang="pt-br">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <meta name="mit" content="2017-01-31T14:45:43-02:00+7467">
    <title><?= $CourseTitle; ?> - Realizar Matrícula Gratuita!</title>
    <meta name="description" content="<?= $CourseDesc; ?>"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0"/>
    <meta name="robots" content="index, follow"/>

    <link rel="base" href="<?= BASE; ?>/campus">
    <link rel="canonical" href="<?= BASE; ?>/campus/matricula-curso2.php?course=<?= $CourseId; ?>"/>

    <meta itemprop="name" content="<?= $CourseTitle; ?> - Realizar Matrícula Gratuita!"/>
    <meta itemprop="description" content="<?= $CourseDesc; ?>"/>
    <meta itemprop="image" content="<?= BASE; ?>/uploads/<?= $CourseCover; ?>"/>
    <meta itemprop="url" content="<?= BASE; ?>/campus/matricula-curso.php?course=<?= $CourseId; ?>"/>

    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?= $CourseTitle; ?> - Realizar Matrícula Gratuita!" />
    <meta property="og:description" content="<?= $CourseDesc; ?>" />
    <meta property="og:image" content="<?= BASE; ?>/uploads/<?= $CourseCover; ?>" />
    <meta property="og:image:secure_url" content="<?= BASE; ?>/uploads/<?= $CourseCover; ?>" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="628" />
    <meta property="og:url" content="<?= BASE; ?>/campus/matricula-curso.php?course=<?= $CourseId; ?>" />
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
    <meta property="twitter:title" content="<?= $CourseTitle; ?> - Realizar Matrícula Gratuita!" />
    <meta property="twitter:description" content="<?= $CourseDesc; ?>" />
    <meta property="twitter:image" content="<?= BASE; ?>/uploads/<?= $CourseCover; ?>" />
    <meta property="twitter:url" content="<?= BASE; ?>/campus/matricula-curso.php?course=<?= $CourseId; ?>" />

    <link rel="shortcut icon" href="<?= INCLUDE_PATH; ?>/images/favicon.png"/>
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
            <form action="lead_login" name="" method="post" enctype="multipart/form-data" novalidate>
<!--                <input type="hidden" name="setUrl" value="--><?//= $setUrl[0]; ?><!--"/>-->
                <input type="hidden" name="course_id" value="<?=$CourseId?>"/>
                <input type="hidden" name="course_title" value="<?=$CourseTitle?>"/>

                <div class="trigger_ajax"></div>

                <label class="label">
                    <input type="text" class="name" name="lead_name" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(_img/user.png) left 15px center no-repeat;" placeholder="Informe seu Nome:" required=""/>
                </label>

                <label class="label">
                    <input class="email" type="email" name="lead_email" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(_img/mail.png) left 15px center no-repeat;" placeholder="Informe seu e-mail:" required/>
                </label>

                <label class="label_check small">
                    <input type="checkbox" name="user_aware" checked value="1" style="top: 10px"> <small class="termos">Estou ciente dos <a href="<?= BASE; ?>/termos.php" target="_blank">Termos de Uso.</a></small>
                </label>

                <p class="text-center">
                    <img class="form_load none" style="float: right; margin-top: 3px; margin-left: 10px;width: 40px" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.svg"/>
                </p>
                <button class="login_btn">Realizar minha matrícula</button>
            </form>
        </div>
        <p class="register">Já possui conta? <a href="<?= BASE; ?>/campus">Conecte-se aqui!</a></p>
    </div>
</div>

<script src="../_cdn/jquery.form.js"></script>
<script src="_js/maskinput.js"></script>
<script src="_js/scripts.js"></script>

</body>
</html>
<?php
ob_end_flush();
