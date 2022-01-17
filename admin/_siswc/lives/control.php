<?php
$AdminLevel = LEVEL_WC_LIVES;
if (!APP_LIVES || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

// AUTO INSTANCE OBJECT CREATE
if (empty($Create)):
    $Create = new Create;
endif;

$LiveId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($LiveId):
    $Read->ExeRead(DB_LIVES, "WHERE live_id = :id", "id={$LiveId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar uma página que não existe ou que foi removida recentemente!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=lives/home');
    endif;
else:

    $Read->ExeRead(DB_LIVES, "ORDER BY live_date DESC LIMIT 1");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, parece que não temos nada para mostrar aqui!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=lives/home');
    endif;

endif;

echo "<link rel='live_base' href='" . BASE . "/_cdn/widgets/wclive'>";
echo "<link rel='stylesheet' type='text/css' href='" . BASE . "/admin/_siswc/lives/datatables.css'>";
echo "<script type='text/javascript' charset='utf8' src='" . BASE . "/admin/_siswc/lives/datatables.js'></script>";
echo "<script src='_siswc/lives/wclive.admin.js'></script>";
echo "<div id='fb-root'></div><script>(function(d, s, id){var js, fjs=d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js=d.createElement(s); js.id=id; js.src='https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v2.11&appId=" . SITE_SOCIAL_FB_APP . "'; fjs.parentNode.insertBefore(js, fjs);}(document, 'script', 'facebook-jssdk'));</script>";
echo "<meta property=\"og:url\" content=\"" . BASE . "/" . LIVE_PATH . "/" . $live_name . "\" />";
echo "<meta property=\"og:title\" content=\"" . $live_title . "\" />";
echo "<meta property=\"og:description\" content=\"" . $live_description . "\" />";
echo "<meta property=\"fb:app_id\" content=\"" . SITE_SOCIAL_FB_APP . "\"/>";
?>
<form class="auto_save" name="live_add" action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="callback" value="Lives"/>
    <input type="hidden" name="callback_action" value="manage"/>
    <input type="hidden" name="origem" value="control"/>
    <input type="hidden" name="live_id" value="<?= $LiveId; ?>"/>

    <header class="dashboard_header">
        <div class="dashboard_header_title">
            <h1 class="icon-hangouts">Sala de Controle</h1>
            <p class="dashboard_header_breadcrumbs">
                &raquo; <?= ADMIN_NAME; ?>
                <span class="crumb">/</span>
                <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
                <span class="crumb">/</span>
                <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=lives/home">Lives</a>
                <span class="crumb">/</span>
                <?= $live_title ? $live_title : 'Edite o Título da sua Live'; ?>
            </p>
        </div>

        <div class="dashboard_header_search" >
            <a title="Editar live" href="dashboard.php?wc=lives/create&id=<?= $live_id; ?>" class="btn btn_yellow icon-pencil2">Editar Sala</a>
            <a target="_blank" title="Ver no site" href="<?= BASE . "/" . LIVE_PATH; ?>/<?= $live_name; ?>" class="wc_view btn btn_green icon-eye">Acessar Sala</a>
        </div>
    </header>

    <div class="dashboard_content" style="background: #faf9f6;">
        <div class="al_center" style="margin-top: 6px;">
            <label class="btn_xlarge label_check label_publish <?= ($live_allow_comments == 1 ? 'active' : ''); ?>" style="width: 32.5%; text-transform: capitalize; font-size: 1em;">
                <input type="checkbox" value="1" name="live_allow_comments" <?= ($live_allow_comments == 1 ? 'checked' : ''); ?>> <i class="icon-bubbles3 no_icon"></i> Habilitar Comentários!
            </label>

            <label class="btn_xlarge label_check label_publish <?= ($live_offer == 1 ? 'active' : ''); ?>" style="width: 32.5%; text-transform: capitalize; font-size: 1em;">
                <input style="" type="checkbox" value="1" name="live_offer" <?= ($live_offer == 1 ? 'checked' : ''); ?>> <i class="icon-radio-checked no_icon"></i> Habilitar Oferta!
            </label>

            <a class="btn_green btn_xlarge label_check label_publish wc_tab" href="#boas-vindas" style="width: 32.5%; text-transform: capitalize; font-size: 1em; color: #fff;">
                <i class="icon-smile no_icon"></i> Boas Vindas <b>(<span class="j_live_offer_sales">0</span>)</b>
            </a>
        </div>

        <div class="box box70 m_top">
            <article class="wc_tab_target wc_active" id="principal">
                <div class="box_content radius">
                    <!--COMMENTS-->
                    <div class="box box100">
                        <span class="btn btn_blue btn_large icon-loop j_reload" style="width: 100%">Recarregar Comentários</span>
                        <div class="panel" id="comments">
                            <div class="fb-comments" data-href="<?= BASE . "/" . LIVE_PATH . "/" . ($live_name ? $live_name : null); ?>" data-width="100%" data-numposts="20" data-order-by="reverse_time"></div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="wc_tab_target" id="leads" style="padding: 0; margin: 0; display: none;">
                <div class="box box100">
                    <a href="#principal" class="btn btn_blue btn_large icon-loop wc_tab" style="width: 100%">Voltar para os Comentários</a>
                </div>
                <div class="box_content">
                    <table class="jwc_datatable display">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($live_mode == 1):
                                $Active = new ActiveCampaign;
                                $Active->getbyTags("live-{$live_name}");
                                $ActiveLeads = $Active->getResult();
                                if (!empty($ActiveLeads)):
                                    foreach ($ActiveLeads as $Leads):
                                        $LeadName = $Leads->first_name . " " . $Leads->last_name;
                                        echo "<tr>
                                        <td>{$LeadName}</td>
                                        <td>{$Leads->email}</td>
                                     </tr>";
                                    endforeach;
                                endif;
                            else:
                                $Read->FullRead("SELECT lead_name, lead_mail FROM " . DB_LEADS . " WHERE lead_source_id = :lid", "lid={$LiveId}");
                                if ($Read->getResult()):
                                    foreach ($Read->getResult() as $Leads):
                                        echo "<tr>
                                        <td>{$Leads['lead_name']}</td>
                                        <td>{$Leads['lead_mail']}</td>
                                     </tr>";
                                    endforeach;
                                endif;
                            endif;
                            ?>
                        </tbody>
                    </table>
                    <?php
                    $i = 1;
                    if ($Read->getResult()):
                        $LookALike = filter_input(INPUT_GET, 'look', FILTER_VALIDATE_BOOLEAN);
                        $LookCount = 0;
                        if ($LookALike):
                            $LookGenerate = "";
                            if ($live_mode == 1):
                                if ($Active->getCallback()->result_code == 1):
                                    foreach ($ActiveLeads as $Look):
                                        $LookName = $Look->first_name . " " . $Look->last_name;
                                        $LookGenerate .= "{$LookName}, {$Look->email}\r\n";
                                    endforeach;
                                endif;
                            else:
                                foreach ($Read->getResult() as $Look):
                                    $LookGenerate .= "{$Look['lead_name']}, {$Look['lead_mail']}\r\n";
                                endforeach;
                            endif;


                            $ZipLook = new ZipArchive;
                            $ZipLook->open("../uploads/look_a_like.zip", ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
                            $ZipLook->addFromString('look_a_like_' . date('d_m_Y') . '.txt', $LookGenerate);
                            $ZipLook->close();

                            header("Location: " . BASE . "/uploads/look_a_like.zip");
                            exit;
                        endif;
                        echo "<div class='clear'></div><div class='student_content_look'><a href='dashboard.php?wc=lives/control&id={$LiveId}&look=true' class='icon-download'>GERAR LISTA DE E-MAILS ( " . str_pad(($live_mode == 1 ? $i : $Read->getRowCount()), 4, 0, 0) . " LEADS )</a></div>";
                    endif;

                    if (file_exists("../uploads/look_a_like.zip") && empty($LookALike)):
                        unlink("../uploads/look_a_like.zip");
                    endif;
                    ?>
                    <div class="clear"></div>
                </div>
            </article>

            <div class="box_content radius">
                <article class="wc_tab_target" id="boas-vindas" style="padding: 0; margin: 0; display: none;">
                    <div class="box box100">
                        <a href="#principal" class="btn btn_blue btn_large icon-loop wc_tab" style="width: 100%">Voltar para os Comentários</a>
                    </div>
                    <div class="panel_header default">
                        <h2 class="icon icon-users">Alunos matriculados na oferta:</h2>
                    </div>

                    <div class="panel">
                        <?php
                        $Read->FullRead("SELECT u.user_id, u.user_thumb, u.user_email, u.user_name, u.user_lastname, o.order_confirmation_purchase_date FROM " . DB_USERS . " AS u INNER JOIN " . DB_EAD_ORDERS . " AS o WHERE o.order_sck = :skc ORDER BY o.order_confirmation_purchase_date DESC", "skc={$live_offer_sck}");
                        if (!$Read->getResult()):
                            Erro("Ops! Ainda não temos alunos matriculados pelo link de oferta.", E_USER_NOTICE);
                        else:
                            foreach ($Read->getResult() as $Study):
                                extract($Study);

                                $UserThumb = "../uploads/{$user_thumb}";
                                $user_thumb = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$user_thumb}" : 'admin/_img/no_avatar.jpg');

                                echo "<article class='wc_ead_dashstudent'>";
                                echo "<img src='" . BASE . "/tim.php?src={$user_thumb}' alt='{$user_name} {$user_lastname}' title='{$user_name} {$user_lastname}'/>";
                                echo "<h1>{$user_name} {$user_lastname} <span>{$user_email}</span></h1>";
                                echo "<p>" . date('d/m/y H\hi', strtotime($order_confirmation_purchase_date)) . "</p>";
                                echo "<p><a class='btn btn_green' href='dashboard.php?wc=teach/students_gerent&id={$user_id}' title='{$user_name} {$user_lastname}'><b>VER ALUN" . ($user_genre == 2 ? 'A' : 'O') . "</b></a></p>";
                                echo "</article>";
                            endforeach;
                        endif;
                        ?>
                    </div>
                    <div class="clear"></div>
                </article>
            </div>
        </div>

        <div class="box box30 m_top">
            <div class="radius" style="width: 100%; background: #0E96E5; color: #fff;">
                <div class="box al_right" style="width: 40%;">
                    <i class="icon-users icon-notext" style="font-size: 4em;"></i>
                </div>
                <div class="box al_right" style="width: 60%; margin-top: 10px; padding-right: 25px;">
                    <?php
                    $OnlineURL = LIVE_PATH . "/" . $live_name;
                    $Read->FullRead("SELECT count(online_id) AS online_student from " . DB_VIEWS_ONLINE . " WHERE online_endview >= NOW() AND online_url = :url", "url={$OnlineURL}");
                    echo "<span class='wc_viewsviews' style='display: block; font-size: .7em; text-transform: uppercase;'><b class='j_online' style='font-size: 2em;' id='{$live_id}'>" . str_pad($Read->getRowCount(), 3, 0, 0) . "</b> Online Agora</span>";
                    echo "<span class='wc_viewsusers' style='display: block; font-size: .7em; text-transform: uppercase;'><i class='icon-stats-bars no-text'></i><b class='j_live_views'>" . str_pad($live_views, 3, 0, 0) . "</b> Visualizações</span>";
                    echo "<span class='wc_viewspages' style='display: block; font-size: .7em; text-transform: uppercase;'><i class='icon-meter no-text'></i><b class='j_online_pitch'>" . str_pad($live_online_pitch, 3, 0, 0) . "</b> Pico</span>";
                    ?>
                </div>
            </div>
            <div class="radius m_top" style="width: 100%; background: #00B494; color: #fff;">
                <div class="box al_right" style="width: 40%;">
                    <i class="icon-coin-dollar icon-notext" style="font-size: 4em;"></i>
                </div>
                <div class="box al_right" style="width: 60%; margin-top: 10px; padding-right: 25px;">
                    <?php
                    $Read->FullRead("SELECT SUM(order_cms_vendor) as OrderCms FROM " . DB_EAD_ORDERS . " WHERE order_status IN('approved', 'completed') AND (order_currency IS NULL OR order_currency = 'BRL') AND YEAR(order_purchase_date) = YEAR(NOW()) AND MONTH(order_purchase_date) = MONTH(NOW()) AND order_sck = :sck", "sck={$live_offer_sck}");
                    $OrderCms = number_format($Read->getResult()[0]['OrderCms'], 2, ',', '.');

                    $Read->FullRead("SELECT SUM(order_cms_vendor) as OrderCurrency FROM " . DB_EAD_ORDERS . " WHERE order_status IN('approved', 'completed') AND order_currency != 'BRL' AND YEAR(order_purchase_date) = YEAR(NOW()) AND MONTH(order_purchase_date) = MONTH(NOW()) AND order_sck = :sck", "sck={$live_offer_sck}");
                    $OrderCurrency = number_format($Read->getResult()[0]['OrderCurrency'], 2, ',', '.');
                    ?>
                    <span style="display: block; font-size: .7em; text-transform: uppercase;"><b style="font-size: 2em;">R$ <a style="color: #fff;" class="j_OrderCms"><?= $OrderCms; ?></a></b></span>
                    <?php
//                    $Read->FullRead("SELECT count(order_id) as live_offer_sales FROM " . DB_EAD_ORDERS . " WHERE order_off = :sck AND order_cms_vendor != '0,00'", "sck={$live_offer_sck}");
                    $Read->FullRead("SELECT count(order_id) as live_offer_sales FROM " . DB_EAD_ORDERS . " WHERE order_off = :sck AND DATE_FORMAT(order_purchase_date, '%Y/%m/%d') = DATE_FORMAT(CURDATE(), '%Y/%m/%d') AND order_cms_vendor != '0,00'", "sck={$live_offer_sck}");
                    $Sales = ($Read->getResult() ? $Read->getResult()[0]['live_offer_sales'] : null);
                    if ($live_offer_clicks == 0):
                        $OfferStats = "0.00";
                    else:
                        $OfferStats = ($Sales / $live_offer_clicks) * 100;
                    endif;

                    $Read->FullRead("SELECT count(order_id) as total FROM " . DB_EAD_ORDERS . " WHERE order_off = :sck AND DATE_FORMAT(order_purchase_date, '%Y/%m/%d') = DATE_FORMAT(CURDATE(), '%Y/%m/%d')", "sck={$live_offer_sck}");
                    $SalesTotal = ($Read->getResult() ? $Read->getResult()[0]['total'] : 0);
                    echo "<span class='wc_viewsviews' style='display: block; font-size: .7em; text-transform: uppercase;'><b class='j_OfferTotal'>{$SalesTotal}</b> Pedidos | <b class='j_live_offer_sales'>" . $Sales . "</b> Vendas</span>";
                    echo "<span class='wc_viewspages' style='display: block; font-size: .7em; text-transform: uppercase;'><b class='j_OfferStats'>" . $OfferStats . "</b>% de Conversão</span>";
                    ?>
                </div>
            </div>
            <?php if ($live_mode == 3): 
                
                else:?>
                <div class="radius m_top" style="width: 100%; background: #FAAD50; color: #fff;">
                    <div class="box al_right" style="width: 40%;">
                        <i class="icon-address-book icon-notext" style="font-size: 4em;"></i>
                    </div>
                    <div class="box al_right" style="width: 60%; margin-top: 15px; padding-right: 25px;">
                        <?php
                        if ($live_mode == 1):
                            $i = 0;
                            $Active = new ActiveCampaign;
                            $Active->getbyTags("live-{$live_name}");
                            if ($Active->getCallback()->result_code == 1):
                                $ActiveLeads = $Active->getResult();
                                foreach ($Active->getResult() as $Leads):
                                    $i++;
                                endforeach;
                            endif;
                            $live_leads = $i;
                        else:
                            $Read->FullRead("SELECT count(lead_id) as live_leads FROM " . DB_LEADS . " WHERE lead_source_id = :lid", "lid={$live_id}");
                            extract($Read->getResult()[0]);
                        endif;
                        echo "<span style='display: block; font-size: .7em; text-transform: uppercase;'><b class='wc_useronline j_live_leads' style='font-size: 2em;'>" . str_pad($live_leads, 4, 0, STR_PAD_LEFT) . "</b> Leads</span>";
                        ?>
                        <a class="btn btn_yellow_leads radius wc_tab" href="#leads" title="Ver Leads">Ver Leads <i class="icon-arrow-right2 icon-notext"></i></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
</form>
</div>


