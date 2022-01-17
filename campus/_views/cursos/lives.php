<div class="wc_ead">
    <?php
    $Read->ExeRead(DB_USERS, "WHERE user_id = :user", "user={$User['user_id']}");
    if ($Read->getResult()):
        $Student = $Read->getResult()[0];
        extract($Student);

        $UpdateStudentAcess = ['user_lastaccess' => date('Y-m-d H:i:s'), 'user_login' => time()];
        $Update = new Update;
        $Update->ExeUpdate(DB_USERS, $UpdateStudentAcess, "WHERE user_id = :user", "user={$user_id}");

        //USER VARS
        $user_thumb = ($user_thumb ? "uploads/{$user_thumb}" : 'campus/_img/no_avatar.jpg');

        $Read->LinkResult(DB_USERS_ADDR, "user_id", $user_id);
        if ($Read->getResult()):
            extract($Read->getResult()[0]);
        else:
            $NewAddr = ['user_id' => $user_id, 'addr_key' => 1, 'addr_name' => "Meu Endereço"];
            $Create->ExeCreate(DB_USERS_ADDR, $NewAddr);

            $Read->LinkResult(DB_USERS_ADDR, "user_id", $user_id);
            extract($Read->getResult()[0]);
        endif;
    endif;

    //GET FEEBACK ALERTS
    if (!empty($_SESSION['wc_ead_alert'])):
        echo "<div class='wc_ead_alert' style='display: flex;'>
    <div class='trigger_notify_box'>
    <div class='trigger_modal_box' style='display: flex;'>
    <div class='trigger_modal trigger_{$_SESSION['wc_ead_alert'][0]}' style='opacity: 1; top: 0px;'>
    <span class='icon-cross trigger_modal_close icon-notext'></span>
    <div class='trigger_modal_icon icon-sad icon-notext'></div>
    <div class='trigger_modal_content'>    
    <div class='trigger_modal_content_title'>{$_SESSION['wc_ead_alert'][1]}</div>
    <div class='trigger_modal_content_message'>{$_SESSION['wc_ead_alert'][2]}</div>
    </div></div></div></div></div>";
        unset($_SESSION['wc_ead_alert']);
    endif;
    ?>

    <header class="dashboard_header">
        <div class="dashboard_header_title">
            <h1 class="icon-play2">Central de Aulas</h1>
            <p class="dashboard_header_breadcrumbs">
                &raquo; <?= SITE_NAME; ?>
                <span class="crumb">/</span>
                <a title="Central de Aulas" href="campus.php?wc=cursos/lives">Central de Aulas</a>
            </p>
        </div>
    </header>

    <div class="">
        <section class="dash_view">
            <?php
            /* Seta o Cookie atual do usuário e grava no banco*/
            $LoginCookieFree = filter_input(INPUT_COOKIE, "wc_ead_login", FILTER_DEFAULT);
            $UpdateUserLogin = ['user_login_cookie' => $LoginCookieFree];
            $Update->ExeUpdate(DB_USERS, $UpdateUserLogin, "WHERE user_id = :user", "user={$user_id}");
            $_SESSION['userLogin']['user_login_cookie'] = $LoginCookieFree;
            setlocale(LC_ALL, 'pt_BR');

            //CHARGEBACK ALERT
            $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user AND order_status = :status",
                "user={$user_id}&status=chargeback");
            if ($Read->getResult()):
                $OrderChargeback = $Read->getResult()[0];

                $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :course",
                    "course={$OrderChargeback['course_id']}");
                $OrderChargebackCourse = ($Read->getResult() ? "Curso {$Read->getResult()[0]['course_title']}" : "produto {$OrderChargeback['order_product_id']}");
                ?>
                <div class="chargeback_container">
                    <div class="chargeback_item_img">
                        <img src="<?= BASE ?>/campus/_img/lock-icon.png" alt="[Image]"/>
                    </div>
                    <div class="chargeback_item">
                        <span>SEU PEDIDO SOFREU UM CHARGEBACK :(</span>
                        <p>Olá <strong><?= $user_name; ?></strong>, identificamos que seu pedido
                            <strong>#<?= str_pad($OrderChargeback['order_id'], 5, 0, 0); ?></strong> para o
                            <strong><?= $OrderChargebackCourse; ?></strong> sofreu um chargeback de compra na Hotmart.
                        </p>
                        <span>SEU ACESSO FOI BLOQUEADO TEMPORARIAMENTE!</span>
                        <p>Um chargeback é uma negativa de compra no cartão de crédito. Isso ocorre quando o portador do
                            cartão informa junto a operadora que não autorizou ou reconhece a compra.</p>
                        <p>Quando um chargeback ocorre o acesso a todos os cursos fica bloqueado temporariamente até que
                            o
                            pagamento seja processado e autorizado novamente.</p>
                        <span>COMO RESOLVER UM CHARGEBACK?</span>
                        <p>Entre em contato com a Hotmart pelo e-mail <strong>suporte@hotmart.com.br</strong> e solicite
                            liberação do seu pagamento para a transação
                            <strong><?= $OrderChargeback['order_transaction']; ?></strong>.</p>
                        <p>Precisa de nossa ajuda? Entre em contato pelo e-mail <?= SITE_ADDR_EMAIL; ?></p>
                        <span><strong>IMPORTANTE</strong></span>
                        <p><strong><?= $user_name; ?></strong>, não resolver o chargeback em menos de 7 dias poderá
                            gerar
                            bloqueio permanente da sua conta.</p>
                    </div>
                </div>
            <?php
            else:
            ?>
            <div class="dash_view_desc">
                <div class="dash_view_desc_icon icon-tv"></div>
                <div class="dash_view_desc_info">
                    <p>Uma Central de Aulas onde todos os membros da Comunidade do <?= SITE_NAME; ?> podem rever quantas
                        vezes acharem necessário. E ainda pode tirar aquelas dúvidas
                        específicas para implementar na sua vida.</p>
                </div>
            </div>

            <section class="app_play box_wrap">
                <div class="app_play_content">
                    <?php
                    $S = filter_input(INPUT_GET, "s", FILTER_DEFAULT);
                    $C = filter_input(INPUT_GET, "cat", FILTER_DEFAULT);
                    $getPage = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT);
                    $Page = ($getPage ? $getPage : 1);
                    $Paginator = new Pager("" . BASE . "/campus/campus.php?wc=cursos/lives&s={$S}&pg=&cat={$C}&pg=", '<<', '>>', 3);
                    $Paginator->ExePager($Page, 8);

                    if (!empty($C)):
                        $WhereCat[0] = "AND ((class_segment = :cat OR FIND_IN_SET(:cat, class_segment_parent)) OR :cat = '')";
                        $WhereCat[1] = "&cat={$C}";
                    else:
                        $WhereCat[0] = "";
                        $WhereCat[1] = "";
                    endif;

                    if (!empty($S)):
                        $WhereString[0] = "AND (class_title LIKE '%' :s '%' OR class_content LIKE '%' :s '%')";
                        $WhereString[1] = "&s={$S}";
                    else:
                        $WhereString[0] = "";
                        $WhereString[1] = "";
                    endif;

                    $Read->FullRead("SELECT * FROM " . DB_CLASS . " WHERE 1=1 "
                        . "{$WhereCat[0]} "
                        . "{$WhereString[0]} "
                        . "ORDER BY class_order ASC, class_date DESC "
                        . "LIMIT :limit OFFSET :offset", "limit={$Paginator->getLimit()}&offset={$Paginator->getOffset()}{$WhereCat[1]}{$WhereString[1]}"
                    );

                    if (!$Read->getResult()):
                        $Paginator->ReturnPage();
                        echo Erro("<span class='al_center icon-notification'>Ainda não existem Aulas cadastradas aqui, {$user_name}!", E_USER_NOTICE);
                    else:
                        foreach ($Read->getResult() as $POST):
                            extract($POST);
                            $ClassCover = (file_exists("../uploads/{$class_cover}") && !is_dir("../uploads/{$class_cover}") ? "uploads/{$class_cover}" : 'admin/_img/no_image.jpg');

                            $Read->ExeRead(DB_CLASS_CHECK, "WHERE user_id = :uid AND class_id = :cid","uid={$user_id}&cid={$class_id}");
                            if ($Read->getResult()):
                                $icon = (!empty($Read->getResult()[0]['class_check']) ? "icon-checkmark" : "icon-mug");
                            else:
                                $icon = "icon-mug";
                            endif;

                            $Category = null;
                            if (!empty($class_segment)):
                                $Read->FullRead("SELECT segment_id, segment_color, segment_title FROM " . DB_EAD_COURSES_SEGMENTS . " WHERE segment_id = :ct", "ct={$class_segment}");
                                if ($Read->getResult()):
                                    $CategoryColor = "{$Read->getResult()[0]['segment_color']}";
                                    $Category = "<a style='color: {$CategoryColor}' title='Ver todas as Aulas em {$Read->getResult()[0]['segment_title']}' href='" . BASE . "/campus/campus.php?wc=cursos/lives&s={$S}&cat={$Read->getResult()[0]['segment_id']}'>{$Read->getResult()[0]['segment_title']}</a>";
                                endif;
                            endif;

                            if (!empty($class_segment_parent)):
                                $Read->FullRead("SELECT segment_id, segment_color, segment_title FROM " . DB_EAD_COURSES_SEGMENTS . " WHERE segment_id IN({$class_segment_parent})");
                                if ($Read->getResult()):
                                    foreach ($Read->getResult() as $SubCat):
                                        $CategoryColor = "{$Read->getResult()[0]['segment_color']}";
                                        $Category .= "<a style='color: {$CategoryColor}' title='Ver todas as Aulas em {$SubCat['segment_title']}' href='" . BASE . "/campus/campus.php?wc=cursos/lives&s={$S}&cat={$SubCat['segment_id']}'>{$SubCat['segment_title']}</a>";
                                    endforeach;
                                endif;
                            endif;

                            echo "<article class='app_play_content_article dash_view_course' id='{$class_id}'>
                            <div class='app_play_content_article_thumb'>
                                <a href='campus.php?wc=cursos/play&id={$class_id}' title='{$class_title}'>
                                    <img style='border-radius: 4px 4px 0 0' src='../tim.php?src={$ClassCover}&w=500' alt='{$class_title}' title='{$class_title}'/>
                                </a>
                                <div class='app_play_content_article_played'>
                                    <p class='" . $icon . " icon-notext'></p>
                                </div>                            
                            </div>
                            <div class='app_play_content_article_desc'>
                                <p class='app_play_content_article_desc_cat'>
                                <a class='btn_opacity' href=campus.php?wc=cursos/play&id={$class_id}' title=''>{$Category}</a></p>
                                <p class='app_play_content_article_desc_review'><span class='icon-star-full'></span><span class='icon-star-full'></span><span class='icon-star-full'></span><span class='icon-star-full'></span><span class='icon-star-full'></span></p>
                                <h2><a href='campus.php?wc=cursos/play&id={$class_id}' title='{$class_title}'>{$class_title}</a></h2>
                            </div>
                            </article>";
                        endforeach;

                        $Paginator->ExePaginator(DB_CLASS, "WHERE "
                            . "((class_segment = :cat OR FIND_IN_SET(:cat, class_segment_parent)) OR :cat = '') "
                            . "AND (class_title LIKE '%' :s '%' OR class_content LIKE '%' :s '%')", "cat={$C}&s={$S}"
                        );
                        echo $Paginator->getPaginator();
                    endif;
                    ?>
                </div>
            </section>
        </section><?php
        endif;
        ?>
    </div>
</div>
