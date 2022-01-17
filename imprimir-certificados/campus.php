<?php
ob_start();
session_start();
require '../_app/Config.inc.php';
require '../_cdn/cronjob.php';

if (isset($_SESSION['userLogin'])):
    $Read = new Read;
    $Read->FullRead("SELECT user_level FROM " . DB_USERS . " WHERE user_id = :user",
        "user={$_SESSION['userLogin']['user_id']}");
    if (!$Read->getResult()):
        unset($_SESSION['userLogin']);
        header('Location: ./index.php');
        exit;
    else:
        $User = $_SESSION['userLogin'];
        $User['user_thumb'] = ($User['user_thumb'] ? BASE . "/tim.php?src=uploads/{$User['user_thumb']}&w=76&h=76" : BASE . '/tim.php?src=campus/_img/no_avatar.jpg&w=76&h=76');

        $Create = new Create;
        $Update = new Update;

        $DashboardLogin = true;
    endif;
else:
    unset($_SESSION['userLogin']);
    header('Location: ./index.php');
    exit;
endif;

$UserLogOff = filter_input(INPUT_GET, 'logoff', FILTER_VALIDATE_BOOLEAN);
if ($UserLogOff):
    $_SESSION['trigger_login'] = Erro("<b>LOGOFF:</b> Olá {$User['user_name']}, você desconectou com sucesso da " . SITE_NAME . ", volte logo!");
    unset($_SESSION['userLogin']);
    header('Location: ./index.php');
    exit;
endif;

$getViewInput = filter_input(INPUT_GET, 'wc', FILTER_DEFAULT);
$getView = ($getViewInput == 'home' ? 'dash' : $getViewInput);

$URL = explode('/', $getViewInput);
$SEO = new Seo($getViewInput);
?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title><?= $SEO->getTitle(); ?></title>
        <meta name="description" content="<?= SITE_DESC; ?>"/>
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
        <meta name="robots" content="noindex, nofollow"/>

        <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800' rel='stylesheet' type='text/css'>
        <link href='https://fonts.googleapis.com/css?family=Source+Code+Pro:300,500' rel='stylesheet' type='text/css'>
        <link rel="base" href="<?= BASE; ?>">
        <link rel="shortcut icon" href="_img/favicon.ico"/>

        <link rel="stylesheet" href="../_cdn/datepicker/datepicker.min.css"/>
        <link rel="stylesheet" href="_css/reset.css"/>
        <link rel="stylesheet" href="_css/campus.css"/>
        <link rel="stylesheet" href="_css/campus-860.css" media="screen and (max-width: 860px)"/>
        <link rel="stylesheet" href="_css/campus-480.css" media="screen and (max-width: 480px)"/>
        <link rel="stylesheet" href="../_cdn/bootcss/fonticon.css"/>

        <script src="../_cdn/jquery.js"></script>
        <script src="../_cdn/jquery.form.js"></script>
        <script src="_js/campus.js"></script>

        <script src="_js/maskinput.js"></script>

        <script src="../_cdn/highcharts.js"></script>
        <script src="../_cdn/datepicker/datepicker.min.js"></script>
        <script src="../_cdn/datepicker/datepicker.pt-BR.js"></script>


        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>
    <body class="dashboard_main">
    <div class="workcontrol_upload workcontrol_loadmodal">
        <div class="workcontrol_upload_bar">
            <img class="m_botton" width="50" src="_img/load_w.svg" alt="Processando requisição!"
                 title="Processando requisição!"/>
            <p><span class="workcontrol_upload_progrees">0%</span> - Processando requisição!</p>
        </div>
    </div>

    <div class="dashboard_fix">
        <?php
        if (isset($_SESSION['trigger_controll'])):
            echo "<div class='trigger_modal' style='display: block'>";
            Erro("<span class='icon-warning'>{$_SESSION['trigger_controll']}</span>", E_USER_ERROR);
            echo "</div>";
            unset($_SESSION['trigger_controll']);
        endif;
        ?>

        <nav class="dashboard_nav">
            <div class="dashboard_nav_admin">
                <img class="dashboard_nav_admin_thumb rounded" alt="" title="" src="<?= $User['user_thumb']; ?>"/>
                <p><a href="campus.php?wc=user/edit&id=<?= $User['user_id']; ?>"
                      title="Meu Perfil"><?= $User['user_name']; ?> <?= $User['user_lastname']; ?></a></p>
            </div>
            <ul class="dashboard_nav_menu">
                <li class="dashboard_nav_menu_li <?= $getViewInput == 'cursos/home' || $getViewInput == 'cursos/estudar' || $getViewInput == 'cursos/tarefa' ? 'dashboard_nav_menu_active' : ''; ?>">
                    <a class="icon-lab" title="Meus Cursos" href="campus.php?wc=cursos/home">Meus Cursos</a></li>
                <li class="dashboard_nav_menu_li <?= $getViewInput == 'user/edit' || $getViewInput == 'user/address' ? 'dashboard_nav_menu_active' : ''; ?>">
                    <a class="icon-user" title="Minha Conta" href="campus.php?wc=user/edit&id=<?= $User['user_id']; ?>">Meu
                        Perfil</a></li>
                <li class="dashboard_nav_menu_li <?= $getViewInput == 'orders/home' ? 'dashboard_nav_menu_active' : ''; ?>">
                    <a class="icon-cart" title="Minha Conta" href="campus.php?wc=orders/home">Meus Pedidos</a></li>
                <li class="dashboard_nav_menu_li"><a class="icon-undo" title="Ver Site"
                                                     href="<?= BASE; ?>">Voltar <?= SITE_NAME; ?></a></li>
            </ul>
            <div class="dashboard_nav_normalize"></div>
        </nav>

        <div class="dashboard">
            <?php
            if (empty($User['user_thumb'])):
                echo "<div>";
                echo Erro("<span class='al_center'><b class='icon-warning'>IMPORTANTE:</b> Atualize sua foto de perfil! <a class='btn btn_yellow' href='campus.php?wc=user/edit&id={$User['user_id']}' title=''>Atualizar</a></span>",
                    E_USER_ERROR);
                echo "</div>";
            endif;
            ?>
            <div class="dashboard_sidebar">
                <span class="mobile_menu btn btn_blue icon-menu icon-notext"></span>
                <div class="fl_right">
                    <span class="dashboard_sidebar_welcome m_right">Bem-vindo(a) a <?= SITE_NAME; ?>, Hoje é <?= date('d/m/y H\hi'); ?></span>
                    <a class="icon-exit btn btn_red" title="Desconectar da <?= SITE_NAME; ?>!"
                       href="campus.php?wc=home&logoff=true">Sair!</a>
                </div>
            </div>
            <?php
            //QUERY STRING
            if (!empty($getView)):
                $ShowModule = explode("/", $getView);
                $ValidaCssModule = __DIR__ . '/_views/' . $ShowModule[0] . '/' . $ShowModule[0] . '.css';
                $ValidaJsModule = __DIR__ . '/_views/' . $ShowModule[0] . '/' . $ShowModule[0] . '.js';
                $includepatch = __DIR__ . '/_views/' . strip_tags(trim($getView)) . '.php';
            else:
                $includepatch = __DIR__ . '/_views/' . 'campus.php';
            endif;
            if (file_exists(__DIR__ . "/_views/" . strip_tags(trim($getView)) . '.php')):
                if (file_exists($ValidaCssModule)):
                    echo "<link rel='stylesheet' href='" . '_views/' . $ShowModule[0] . '/' . $ShowModule[0] . '.css' . "'/>";
                endif;
                if (file_exists($ValidaJsModule)):
                    echo "<script src='" . '_views/' . $ShowModule[0] . '/' . $ShowModule[0] . '.js' . "'></script>";
                endif;
                require_once __DIR__ . "/_views/" . strip_tags(trim($getView)) . '.php';
            elseif (file_exists($includepatch)):
                require_once($includepatch);
            else:
                $_SESSION['trigger_controll'] = "<b>OPPSSS:</b> <span class='fontred'>_views/{$getView}.php</span> ainda está em contrução!";
                header('Location: campus.php?wc=home');
                exit;
            endif;
            ?>
        </div>
    </div>

    <?php
    $UserActiveCampaign = (!empty($_SESSION['userLogin']) && !empty($_SESSION['userLogin']['user_email']) ? $_SESSION['userLogin']['user_email'] : null);
    ?>
    <script type="text/javascript">
        var trackcmp_email = '<?= $UserActiveCampaign; ?>';
        var trackcmp = document.createElement("script");
        trackcmp.async = true;
        trackcmp.type = 'text/javascript';
        trackcmp.src = '//trackcmp.net/visit?actid=66026982&e=' + encodeURIComponent(trackcmp_email) + '&r=' + encodeURIComponent(document.referrer) + '&u=' + encodeURIComponent(window.location.href);
        var trackcmp_s = document.getElementsByTagName("script");
        if (trackcmp_s.length) {
            trackcmp_s[0].parentNode.appendChild(trackcmp);
        } else {
            var trackcmp_h = document.getElementsByTagName("head");
            trackcmp_h.length && trackcmp_h[0].appendChild(trackcmp);
        }
    </script>
    </body>
    </html>
<?php
ob_end_flush();