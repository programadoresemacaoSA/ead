<?php

session_start();
require '../../_app/Config.inc.php';
$NivelAcess = LEVEL_WC_LIVES;

if (!APP_LIVES || empty($_SESSION['userLogin']) || empty($_SESSION['userLogin']['user_level']) || $_SESSION['userLogin']['user_level'] < $NivelAcess):
    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    echo json_encode($jSON);
    die;
endif;

usleep(50000);

//DEFINE O CALLBACK E RECUPERA O POST
$jSON = null;
$CallBack = 'Lives';
$PostData = filter_input_array(INPUT_POST, FILTER_DEFAULT);

//VALIDA AÇÃO
if ($PostData && $PostData['callback_action'] && $PostData['callback'] == $CallBack):
    //PREPARA OS DADOS
    $Case = $PostData['callback_action'];
    unset($PostData['callback'], $PostData['callback_action'], $PostData['DataTables_Table_0_length']);

    // AUTO INSTANCE OBJECT READ
    if (empty($Read)):
        $Read = new Read;
    endif;

    // AUTO INSTANCE OBJECT CREATE
    if (empty($Create)):
        $Create = new Create;
    endif;

    // AUTO INSTANCE OBJECT UPDATE
    if (empty($Update)):
        $Update = new Update;
    endif;

    // AUTO INSTANCE OBJECT DELETE
    if (empty($Delete)):
        $Delete = new Delete;
    endif;

    //SELECIONA AÇÃO
    switch ($Case):
        //DELETE
        case 'live':

            $Read->FullRead("SELECT live_name, live_mode, live_online_pitch, live_views,live_offer_clicks, live_offer_sck FROM " . DB_LIVES . " WHERE live_id = :lid", "lid={$PostData['live_id']}");
            extract($Read->getResult()[0]);

            $Online = $Offer = $Leads = $Finance = array();
            $OnlineURL = LIVE_PATH . "/" . $live_name;
            $Read->FullRead("SELECT count(online_id) AS total FROM " . DB_VIEWS_ONLINE . " WHERE online_endview >= NOW() AND online_url = :url", "url={$OnlineURL}");
            $Online = [
                "live_views" => str_pad($live_views, 3, 0, 0),
                "online_users" => str_pad($Read->getResult()[0]['total'], 3, 0, 0),
                "online_pitch" => str_pad($live_online_pitch, 3, 0, 0)
            ];

            //Verifica e atualiza o pico de alunos online
            if ($Read->getResult()[0]['total'] > $live_online_pitch):
                $Update = new Update;
                $Update->ExeUpdate(DB_LIVES, ["live_online_pitch" => $Read->getResult()[0]['total']], "WHERE live_id = :lid", "lid={$PostData['live_id']}");
            endif;

            //Verifica e atualiza Ofertas -
//            $Read->FullRead("SELECT count(order_id) as live_offer_sales FROM " . DB_EAD_ORDERS . " WHERE order_off = :sck AND DATE_FORMAT(order_purchase_date, '%Y/%m/%d') = DATE_FORMAT(CURDATE(), '%Y/%m/%d')", "sck={$live_offer_sck}");
            $Read->FullRead("SELECT count(order_id) as live_offer_sales FROM " . DB_EAD_ORDERS . " WHERE order_off = :sck AND DATE_FORMAT(order_purchase_date, '%Y/%m/%d') = DATE_FORMAT(CURDATE(), '%Y/%m/%d') AND order_cms_vendor != '0,00'", "sck={$live_offer_sck}");
            extract($Read->getResult()[0]);
            if ($live_offer_clicks == 0):
                $OfferStats = "0.00";
            else:
                $OfferStats = ($live_offer_sales / $live_offer_clicks) * 100;
            endif;


            $Offer = [
                "live_offer_clicks" => $live_offer_clicks,
                "live_offer_sales" => $live_offer_sales,
                "OfferStats" => $OfferStats
            ];

            $Read->FullRead("SELECT count(order_id) as total FROM " . DB_EAD_ORDERS . " WHERE order_off = :sck AND DATE_FORMAT(order_purchase_date, '%Y/%m/%d') = DATE_FORMAT(CURDATE(), '%Y/%m/%d')", "sck={$live_offer_sck}");
            extract($Read->getResult()[0]);
            if ($total == 0):
                $OfferTotal = "0";
            else:
                $OfferTotal = $total;
            endif;
            $Offer['OfferTotal'] = $OfferTotal;
            //Leads
            if ($live_mode == 1):
                $i = 0;
                $Active = new ActiveCampaign;
                $Active->getbyTags("live-{$live_name}");
                if ($Active->getCallback()->result_code == 1):
                    foreach ($Active->getResult() as $Leads):
                        $i++;
                    endforeach;
                endif;
                $Leads = [
                    "live_leads" => str_pad($i, 4, 0, STR_PAD_LEFT)
                ];
            else:
                $Read->FullRead("SELECT count(lead_id) as live_leads FROM " . DB_LEADS . " WHERE lead_source_id = :lid", "lid={$PostData['live_id']}");
                extract($Read->getResult()[0]);
                $Leads = [
                    "live_leads" => str_pad($live_leads, 4, 0, STR_PAD_LEFT)
                ];
            endif;


            //FINANCEIRO - live_offer_sck = order_off
//            echo "SELECT SUM(order_cms_vendor) as OrderCms FROM " . DB_EAD_ORDERS . " WHERE order_status IN('approved', 'completed') AND (order_currency IS NULL OR order_currency = 'BRL') AND YEAR(order_purchase_date) = YEAR(NOW()) AND MONTH(order_purchase_date) = MONTH(NOW()) AND order_off = {$live_offer_sck}";
            $Read->FullRead("SELECT SUM(order_cms_vendor) as OrderCms FROM " . DB_EAD_ORDERS . " WHERE order_status IN('approved', 'completed') AND (order_currency IS NULL OR order_currency = 'BRL') AND YEAR(order_purchase_date) = YEAR(NOW()) AND MONTH(order_purchase_date) = MONTH(NOW()) AND order_off = :offer", "offer={$live_offer_sck}");
            $OrderCms = number_format($Read->getResult()[0]['OrderCms'], 2, ',', '.');

            $Read->FullRead("SELECT SUM(order_cms_vendor) as OrderCurrency FROM " . DB_EAD_ORDERS . " WHERE order_status IN('approved', 'completed') AND order_currency != 'BRL' AND YEAR(order_purchase_date) = YEAR(NOW()) AND MONTH(order_purchase_date) = MONTH(NOW()) AND order_off = :offer", "offer={$live_offer_sck}");
            $OrderCurrency = number_format($Read->getResult()[0]['OrderCurrency'], 2, ',', '.');

            $Finance = [
                "OrderCms" => $OrderCms,
                "OrderCurrency" => $OrderCurrency
            ];

            $jSON = array_merge($Online, $Offer, $Leads, $Finance);

            break;
        case 'delete':
            $Read->FullRead("SELECT live_cover FROM " . DB_LIVES . " WHERE live_id = :ps", "ps={$PostData['del_id']}");
            if ($Read->getResult() && file_exists("../../uploads/{$Read->getResult()[0]['live_cover']}") && !is_dir("../../uploads/{$Read->getResult()[0]['live_cover']}")):
                unlink("../../uploads/lives/{$Read->getResult()[0]['live_cover']}");
            endif;

            $Delete->ExeDelete(DB_LIVES, "WHERE live_id = :id", "id={$PostData['del_id']}");

            $jSON['success'] = true;
            break;

        //MANAGER
        case 'manage':
            $LiveId = $PostData['live_id'];
            $Origem = $PostData['origem'];
            unset($PostData['live_id'], $PostData['origem']);

            $Read->ExeRead(DB_LIVES, "WHERE live_id = :id", "id={$LiveId}");
            $ThisLive = $Read->getResult()[0];

            $PostData['live_offer'] = (!empty($PostData['live_offer']) ? '1' : '0');
            $PostData['live_allow_comments'] = (!empty($PostData['live_allow_comments']) ? '1' : '0');
            $PostData['live_courses'] = (!empty($PostData['live_courses']) ? implode(',', $PostData['live_courses']) : null);
            $PostData['live_title'] = (!empty($PostData['live_title']) ? $PostData['live_title'] : $ThisLive['live_title']);

            if ($Origem == 'create'):
                $PostData['live_date'] = (!empty($PostData['live_date']) ? Check::Data($PostData['live_date']) : date('Y-m-d H:i:s'));
                $PostData['live_enddate'] = (!empty($PostData['live_enddate']) ? Check::Data($PostData['live_enddate']) : "");
                $PostData['live_status'] = (!empty($PostData['live_status']) ? '1' : '0');
                if (LIVE_LINK_LIVES == 1 && empty($PostData['live_name'])):
                    $PostData['live_name'] = (!empty($PostData['live_name']) ? Check::Name($PostData['live_name']) : Check::Name($PostData['live_title']));

                    $Read->FullRead("SELECT live_name FROM " . DB_LIVES . " WHERE live_name = :nm AND live_id != :id", "nm={$PostData['live_name']}&id={$LiveId}");
                    if ($Read->getResult()):
                        $PostData['live_name'] = "{$PostData['live_name']}-{$PageId}";
                    endif;

                    $jSON['name'] = $PostData['live_name'];
                    $jSON['view'] = BASE . "/" . LIVE_PATH . "/{$PostData['live_name']}";
                endif;
            endif;

            if (!empty($_FILES['live_cover'])):
                $File = $_FILES['live_cover'];

                if ($ThisLive['live_cover'] && file_exists("../../uploads/{$ThisLive['live_cover']}") && !is_dir("../../uploads/{$ThisLive['live_cover']}")):
                    unlink("../../uploads/lives/{$ThisLive['live_cover']}");
                endif;

                $Upload = new Upload('../../uploads/lives/');
                $Upload->Image($File, (!empty($PostData['live_name']) ? $PostData['live_name'] : $ThisLive['live_name']) . '-' . time(), IMAGE_W);
                if ($Upload->getResult()):
                    $PostData['live_cover'] = $Upload->getResult();
                else:
                    $jSON['trigger'] = AjaxErro("<b class='icon-image'>ERRO AO ENVIAR CAPA:</b> Olá {$_SESSION['userLogin']['user_name']}, selecione uma imagem JPG ou PNG para enviar como capa!", E_USER_WARNING);
                    echo json_encode($jSON);
                    return;
                endif;
            else:
                unset($PostData['live_cover']);
            endif;

            $Update->ExeUpdate(DB_LIVES, $PostData, "WHERE live_id = :id", "id={$LiveId}");
            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>Live atualizada com sucesso!</b>");
            break;

    endswitch;

    //RETORNA O CALLBACK
    if ($jSON):
        echo json_encode($jSON);
    else:
        $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS:</b> Desculpe. Mas uma ação do sistema não respondeu corretamente. Ao persistir, contate o desenvolvedor!', E_USER_ERROR);
        echo json_encode($jSON);
    endif;
else:
    //ACESSO DIRETO
    die('<br><br><br><center><h1>Acesso Restrito!</h1></center>');
endif;
