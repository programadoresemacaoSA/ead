<?php
ob_start();
session_start();

require './_app/Config.inc.php';

//CHANCE THEME IN SESSION
$WC_THEME = filter_input(INPUT_GET, "wctheme", FILTER_DEFAULT);
if ($WC_THEME && $WC_THEME != 'null'):
    $_SESSION['WC_THEME'] = $WC_THEME;
    header("Location: " . BASE);
    exit;
elseif ($WC_THEME && $WC_THEME == 'null'):
    unset($_SESSION['WC_THEME']);
    header("Location: " . BASE);
    exit;
endif;

//READ CLASS AUTO INSTANCE
if (empty($Read)):
    $Read = new Read;
endif;

$Sesssion = new Session(SIS_CACHE_TIME);

//USER SESSION VALIDATION
if (!empty($_SESSION['userLogin']) && !empty($_SESSION['userLogin']['user_id'])):
    if (empty($Read)):
        $Read = new Read;
    endif;
    $Read->ExeRead(DB_USERS, "WHERE user_id = :user_id", "user_id={$_SESSION['userLogin']['user_id']}");
    if ($Read->getResult()):
        $_SESSION['userLogin'] = $Read->getResult()[0];
    else:
        unset($_SESSION['userLogin']);
    endif;
endif;

//GET PARAMETER URL
$getURL = strip_tags(trim(filter_input(INPUT_GET, 'url', FILTER_DEFAULT)));
$setURL = (empty($getURL) ? 'index' : $getURL);
$URL = explode('/', $setURL);
$SEO = new Seo($setURL);

//CHECK IF THIS POST ABLE TO AMP
if (APP_POSTS_AMP && (!empty($URL[0]) && $URL[0] == 'artigo') && file_exists(REQUIRE_PATH . '/amp.php')):
    $Read->ExeRead(DB_POSTS, "WHERE post_name = :name", "name={$URL[1]}");
    $PostAmp = ($Read->getResult()[0]['post_amp'] == 1 ? true : false);
endif;

if (APP_LIVES && $URL[0] == LIVE_PATH):
    require "_cdn/widgets/liveclass/liveclass.php";
else:

//INSTANCE AMP (valid single article only)
    if (APP_POSTS_AMP && (!empty($URL[0]) && $URL[0] == 'artigo') && file_exists(REQUIRE_PATH . '/amp.php') && (!empty($URL[2]) && $URL[2] == 'amp') && (!empty($PostAmp) && $PostAmp == true)):
        require REQUIRE_PATH . '/amp.php';
    else:
        ?><!DOCTYPE html>
        <html lang="pt-br" itemscope itemtype="https://schema.org/<?= $SEO->getSchema(); ?>">
            <head>
                <meta charset="UTF-8">
                <meta name="mit" content="2017-12-12T01:33:15-02:00+31419">
                <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0,user-scalable=0">

                <title><?= $SEO->getTitle(); ?></title>
                <meta name="description" content="<?= $SEO->getDescription(); ?>"/>
                <meta name="robots" content="index, follow"/>

                <link rel="base" href="<?= BASE; ?>"/>
                <link rel="canonical" href="<?= BASE; ?>/<?= $getURL; ?>"/>
                <?php
                if (APP_POSTS_AMP && (!empty($URL[0]) && $URL[0] == 'artigo') && file_exists(REQUIRE_PATH . '/amp.php') && (!empty($PostAmp) && $PostAmp == true)):
                    echo '<link rel="amphtml" href="' . BASE . '/' . $getURL . '/amp" />' . "\r\n";
                endif;
                ?>
                <link rel="alternate" type="application/rss+xml" href="<?= BASE; ?>/rss.php"/>
                <link rel="sitemap" type="application/xml" href="<?= BASE; ?>/sitemap.xml" />
                <?php
                if (SITE_SOCIAL_GOOGLE):
                    echo '<link rel="author" href="https://plus.google.com/' . SITE_SOCIAL_GOOGLE_AUTHOR . '/posts"/>' . "\r\n";
                    echo '            <link rel="publisher" href="https://plus.google.com/' . SITE_SOCIAL_GOOGLE_PAGE . '"/>' . "\r\n";
                endif;
                ?>

                <meta itemprop="name" content="<?= $SEO->getTitle(); ?>"/>
                <meta itemprop="description" content="<?= $SEO->getDescription(); ?>"/>
                <meta itemprop="image" content="<?= $SEO->getImage(); ?>"/>
                <meta itemprop="url" content="<?= BASE; ?>/<?= $getURL; ?>"/>

                <meta property="og:type" content="article" />
                <meta property="og:title" content="<?= $SEO->getTitle(); ?>" />
                <meta property="og:description" content="<?= $SEO->getDescription(); ?>" />
                <meta property="og:image" content="<?= $SEO->getImage(); ?>" />
                <meta property="og:url" content="<?= BASE; ?>/<?= $getURL; ?>" />
                <meta property="og:site_name" content="<?= SITE_NAME; ?>" />
                <meta property="og:locale" content="pt_BR" />
                <?php
                if (SITE_SOCIAL_FB):
                    echo '<meta property="article:author" content="https://www.facebook.com/' . SITE_SOCIAL_FB_AUTHOR . '" />' . "\r\n";
                    echo '            <meta property="article:publisher" content="https://www.facebook.com/' . SITE_SOCIAL_FB_PAGE . '" />' . "\r\n";

                    if (SITE_SOCIAL_FB_APP):
                        echo '<meta property="og:app_id" content="' . SITE_SOCIAL_FB_APP . '" />' . "\r\n";
                    endif;

                    if (SEGMENT_FB_PAGE_ID):
                        echo '            <meta property="fb:pages" content="' . SEGMENT_FB_PAGE_ID . '" />' . "\r\n";
                    endif;
                endif;
                ?>

                <meta property="twitter:card" content="summary_large_image" />
                <?php
                if (SITE_SOCIAL_TWITTER):
                    echo '<meta property="twitter:site" content="@' . SITE_SOCIAL_TWITTER . '" />' . "\r\n";
                endif;
                ?>
                <meta property="twitter:domain" content="<?= BASE; ?>" />
                <meta property="twitter:title" content="<?= $SEO->getTitle(); ?>" />
                <meta property="twitter:description" content="<?= $SEO->getDescription(); ?>" />
                <meta property="twitter:image" content="<?= $SEO->getImage(); ?>" />
                <meta property="twitter:url" content="<?= BASE; ?>/<?= $getURL; ?>" />           

                <!-- Favicon -->
                <link rel="icon" type="image/png" sizes="32x32" href="<?= INCLUDE_PATH; ?>/img/favicon.png">

                <!-- Bootstrap 3.3.7 -->
                <link rel="stylesheet" href="<?= INCLUDE_PATH; ?>/assets/bootstrap/css/bootstrap.min.css">

                <!-- Font Awesome 4.7.0 -->
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"/>

                <!-- Modal Video -->
                <link rel="stylesheet" href="<?= INCLUDE_PATH; ?>/assets/modalvideo/css/modal-video.min.css">

                <!-- Estilo da página -->
                <link rel="stylesheet" href="<?= INCLUDE_PATH; ?>/scss/style.css">


                <!--[if lt IE 9]>
                    <script src="<?= BASE; ?>/_cdn/html5shiv.js"></script>
                <![endif]-->

                <script src="<?= BASE; ?>/_cdn/jquery.js"></script>
                <script src="<?= BASE; ?>/_cdn/workcontrol.js"></script>


            </head>
            <body>     
                <?php
                require '_cdn/widgets/ecommerce/cart.inc.php';

                // MESSAGE MAINTENANCE FOR ADMIN        
                if (ADMIN_MAINTENANCE && !empty($_SESSION['userLogin']['user_level']) && $_SESSION['userLogin']['user_level'] >= 6):
                    echo "<div class='workcontrol_maintenance'>&#x267A; O MODO de manutenção está ativo. Somente administradores podem ver o site assim &#x267A;</div>";
                endif;

                // REDIRECT PUBLIC TO MAINTENANCE
                if (ADMIN_MAINTENANCE && (empty($_SESSION['userLogin']['user_level']) || $_SESSION['userLogin']['user_level'] < 6)):
                    require 'maintenance.php';
                else:

                    // PESQUISA
                    $Search = filter_input_array(INPUT_POST, FILTER_DEFAULT);
                    if ($Search && !empty($Search['s'])):
                        $Search = urlencode(strip_tags(trim($Search['s'])));
                        header('Location: ' . BASE . '/pesquisa/' . $Search);
                        exit;
                    endif;

                    $Buscar = filter_input_array(INPUT_POST, FILTER_DEFAULT);
                    if ($Buscar && !empty($Buscar['b'])):
                        $Buscar = urlencode(strip_tags(trim($Buscar['b'])));
                        header('Location: ' . BASE . '/buscar/' . $Buscar);
                        exit;
                    endif;

                    // HEADER
                    if (file_exists(REQUIRE_PATH . "/inc/header.php")):
                        require REQUIRE_PATH . "/inc/header.php";
                    else:
                        trigger_error('Crie um arquivo /inc/header.php na pasta do tema!');
                    endif;

                    // CONTENT
                    $URL[1] = (empty($URL[1]) ? null : $URL[1]);

                    if ($URL[0] == 'rss' || $URL[0] == 'feed' || $URL[0] == 'rss.xml'):
                        header("Location: " . BASE . "/rss.php");
                        exit;
                    endif;

                    $Pages = array();
                    $Read->FullRead("SELECT page_name FROM " . DB_PAGES);
                    if ($Read->getResult()):
                        foreach ($Read->getResult() as $SinglePage):
                            $Pages[] = $SinglePage['page_name'];
                        endforeach;
                    endif;

                    if (in_array($URL[0], $Pages) && file_exists(REQUIRE_PATH . '/pagina.php')):
                        if (file_exists(REQUIRE_PATH . "/page-{$URL[0]}.php")):
                            require REQUIRE_PATH . "/page-{$URL[0]}.php";
                        else:
                            require REQUIRE_PATH . '/pagina.php';
                        endif;
                    elseif (file_exists(REQUIRE_PATH . '/' . $URL[0] . '.php')):
                        if ($URL[0] == 'artigos' && file_exists(REQUIRE_PATH . "/cat-{$URL[1]}.php")):
                            require REQUIRE_PATH . "/cat-{$URL[1]}.php";
                        else:
                            require REQUIRE_PATH . '/' . $URL[0] . '.php';
                        endif;
                    elseif (file_exists(REQUIRE_PATH . '/' . $URL[0] . '/' . $URL[1] . '.php')):
                        require REQUIRE_PATH . '/' . $URL[0] . '/' . $URL[1] . '.php';
                    else:
                        if (file_exists(REQUIRE_PATH . "/404.php")):
                            require REQUIRE_PATH . '/404.php';
                        else:
                            trigger_error("Não foi possível incluir o arquivo themes/" . THEME . "/{$getURL}.php <b>(O arquivo 404 também não existe!)</b>");
                        endif;
                    endif;

                    // FOOTER
                    if (file_exists(REQUIRE_PATH . "/inc/footer.php")):
                        require REQUIRE_PATH . "/inc/footer.php";
                    else:
                        trigger_error('Crie um arquivo /inc/footer.php na pasta do tema!');
                    endif;
                endif;

                // WC CODES
                $Read->ExeRead(DB_WC_CODE);
                if ($Read->getResult()):

                    if (empty($Update)):
                        $Update = new Update;
                    endif;

                    $ActiveCodes = filter_input(INPUT_GET, 'url', FILTER_DEFAULT);
                    echo "\r\n\r\n\r\n<!--WorkControl Codes-->\r\n";
                    foreach ($Read->getResult() as $HomeCodes):

                        if (empty($HomeCodes['code_condition'])):
                            echo $HomeCodes['code_script'];
                            $UpdateCodes = ['code_views' => $HomeCodes['code_views'] + 1];
                            $Update->ExeUpdate(DB_WC_CODE, $UpdateCodes, "WHERE code_id = :id", "id={$HomeCodes['code_id']}");
                        elseif (preg_match("/" . str_replace("/", "\/", $HomeCodes['code_condition']) . "/", $ActiveCodes)):
                            echo $HomeCodes['code_script'];
                            $UpdateCodes = ['code_views' => $HomeCodes['code_views'] + 1];
                            $Update->ExeUpdate(DB_WC_CODE, $UpdateCodes, "WHERE code_id = :id", "id={$HomeCodes['code_id']}");
                        endif;
                    endforeach;
                    echo "\r\n<!--/WorkControl Codes-->\r\n\r\n\r\n";
                endif;

                if (!empty(SEGMENT_FB_PIXEL_ID)):
                    require '_cdn/wc_track.php';
                endif;

                // GOOGLE ANALYTICS WITH DEFINE IN CONFIG
                if (!empty(SEGMENT_GL_ANALYTICS_UA)):
                    echo "<script>(function (i, s, o, g, r, a, m) {i['GoogleAnalyticsObject'] = r;i[r] = i[r] || function () {(i[r].q = i[r].q || []).push(arguments)}, i[r].l = 1 * new Date();a = s.createElement(o),m = s.getElementsByTagName(o)[0];a.async = 1;a.src = g;m.parentNode.insertBefore(a, m)})(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');ga('create', '" . SEGMENT_GL_ANALYTICS_UA . "', 'auto');ga('send', 'pageview');</script>";
                endif;
                ?>

                <script src="<?= BASE; ?>/_cdn/jquery.form.js"></script>
                
                <!-- Bootstrap 3.3.7 -->
                <script src="<?= INCLUDE_PATH; ?>/assets/bootstrap/js/bootstrap.min.js"></script>

                <!-- Modal Video -->
                <script src="<?= INCLUDE_PATH; ?>/assets/modalvideo/js/jquery-modal-video.min.js"></script>
                <script src="<?= INCLUDE_PATH; ?>/assets/modalvideo/js/modal-video.min.js"></script>

                <!-- jQuery Validate -->
                <script src="<?= INCLUDE_PATH; ?>/assets/jquery-validate/jquery.validate.min.js"></script>

                <!-- JS da página -->
                <script src="<?= INCLUDE_PATH; ?>/js/main.js"></script>

                <div id="fb-root"></div>
                <script>(function (d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id))
                            return;
                        js = d.createElement(s);
                        js.id = id;
                        js.src = 'https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v3.2&appId=<?= SITE_SOCIAL_FB_APP; ?>&autoLogAppEvents=1';
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));</script>
            </body>

        </html>
    <?php
    endif;
endif;
ob_end_flush();

if (!file_exists('.htaccess')):
    $htaccesswrite = "RewriteEngine On\r\nOptions All -Indexes\r\n\r\n# WC WWW Redirect.\r\n#RewriteCond %{HTTP_HOST} !^www\. [NC]\r\n#RewriteRule ^ https://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\r\n\r\n# WC HTTPS Redirect\r\nRewriteCond %{HTTP:X-Forwarded-Proto} !https\r\nRewriteCond %{HTTPS} off\r\nRewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\r\n\r\n# WC URL Rewrite\r\nRewriteCond %{SCRIPT_FILENAME} !-f\r\nRewriteCond %{SCRIPT_FILENAME} !-d\r\nRewriteRule ^(.*)$ index.php?url=$1";
    $htaccess = fopen('.htaccess', "w");
    fwrite($htaccess, str_replace("'", '"', $htaccesswrite));
    fclose($htaccess);
endif;
