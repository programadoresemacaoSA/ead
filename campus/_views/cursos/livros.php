<div class="wc_ead">
    <!--MODAL CERTIFICATION-->
    <div class='jwc_ead_win wc_ead_win wc_ead_win_default wc_ead'>
        <div class='wc_ead_win_box'>
            {{IMAGE}}
            <div class='wc_ead_win_box_content al_center'>
                <p class='title icon-{{ICON}}'>{{TITLE}}</p>
                <p>{{CONTENT}}</p>
                <a target="_blank" style="color: #FFFFFF" title='{{LINK_TITLE}}' href='{{LINK}}' class='btn btn_blue icon-{{LINK_ICON}}'>{{LINK_NAME}}</a>
                <a style="color: #FFFFFF" title='Fechar Aviso!' href='<?= BASE ?>/campus' class='btn btn_red icon-cross'>Fechar</a>
                <!--<span title='Fechar Aviso!' class='m_left btn btn_red icon-cross jwc_ead_close_bonus'>Fechar</span>-->
            </div>
        </div>
    </div>

    <!--BASE ALERT-->
    <div class="wc_ead_alert">
        <div class="wc_ead_alert_box">
            <div class="wc_ead_alert_icon icon-switch icon-notext"></div><div class="wc_ead_alert_text">
                <p class="wc_ead_alert_title">{TITLE}</p>
                <p class="wc_ead_alert_content">{CONTENT}</p>
            </div><div class="wc_ead_alert_close"><span class="icon-cross icon-notext"></span></div>
        </div>
    </div>

    <!--BASE MODAL-->
    <div class="wc_ead_modal">
        <div class="wc_ead_modal_box">
            <span class="wc_ead_modal_close icon-cross icon-notext"></span>
            <p class="wc_ead_modal_title"><span>{TITLE}</span></p>
            <div class="wc_ead_modal_content">{CONTENT}</div>
            <div class="wc_ead_modal_help">Precisa de ajuda? <a href="mailto:<?= SITE_ADDR_EMAIL; ?>" title="Enviar E-mail!">Entre em contato!</a></div>
        </div>
    </div>

    <!--UPLOAD MODAL-->
    <div class="wc_ead_upload jwc_ead_upload">
        <div class="wc_ead_upload_progress jwc_ead_upload_progress">0%</div>
    </div>

    <!--LOAD MODAL-->
    <div class="wc_ead_load jwc_ead_load wc_ead">
        <div class="wc_ead_load_content">
            <img src="<?= BASE; ?>/campus/_img/load.svg" alt="Aguarde, enviando solicitação!" width="50" title="Aguarde, enviando solicitação!"/>
            <p class="wc_ead_load_content_msg">Aguarde, enviando solicitação!</p>
        </div>
    </div>

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
            <h1 class="icon-book">Livros</h1>
            <p class="dashboard_header_breadcrumbs">
                &raquo; <?= SITE_NAME; ?>
                <span class="crumb">/</span>
                <a title="Livros" href="campus.php?wc=cursos/livros">Livros</a>
            </p>
        </div>
    </header>
    <section class="dash_view">
        <?php
        /* Seta o Cookie atual do usuário e grava no banco*/
        $LoginCookieFree = filter_input(INPUT_COOKIE, "wc_ead_login", FILTER_DEFAULT);
        $UpdateUserLogin = ['user_login_cookie' => $LoginCookieFree];
        $Update->ExeUpdate(DB_USERS, $UpdateUserLogin, "WHERE user_id = :user", "user={$user_id}");
        $_SESSION['userLogin']['user_login_cookie'] = $LoginCookieFree;
        setlocale(LC_ALL, 'pt_BR');

        //CHARGEBACK ALERT
        $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user AND order_status = :status", "user={$user_id}&status=chargeback");
        if ($Read->getResult()):
            $OrderChargeback = $Read->getResult()[0];

            $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :course", "course={$OrderChargeback['course_id']}");
            $OrderChargebackCourse = ($Read->getResult() ? "Curso {$Read->getResult()[0]['course_title']}" : "produto {$OrderChargeback['order_product_id']}");
            ?>
            <div class="chargeback_container">
                <div class="chargeback_item_img">
                    <img src="<?= BASE ?>/campus/_img/lock-icon.png" alt="[Image]"/>
                </div>
                <div class="chargeback_item">
                    <span>SEU PEDIDO SOFREU UM CHARGEBACK :(</span>
                    <p>Olá <strong><?= $user_name; ?></strong>, identificamos que seu pedido <strong>#<?= str_pad($OrderChargeback['order_id'], 5, 0, 0); ?></strong> para o <strong><?= $OrderChargebackCourse; ?></strong> sofreu um chargeback de compra na Hotmart.</p>
                    <span>SEU ACESSO FOI BLOQUEADO TEMPORARIAMENTE!</span>
                    <p>Um chargeback é uma negativa de compra no cartão de crédito. Isso ocorre quando o portador do cartão informa junto a operadora que não autorizou ou reconhece a compra.</p>
                    <p>Quando um chargeback ocorre o acesso a todos os cursos fica bloqueado temporariamente até que o pagamento seja processado e autorizado novamente.</p>
                    <span>COMO RESOLVER UM CHARGEBACK?</span>
                    <p>Entre em contato com a Hotmart pelo e-mail <strong>suporte@hotmart.com.br</strong> e solicite liberação do seu pagamento para a transação <strong><?= $OrderChargeback['order_transaction']; ?></strong>.</p>
                    <p>Precisa de nossa ajuda? Entre em contato pelo e-mail <?= SITE_ADDR_EMAIL; ?></p>
                    <span><strong>IMPORTANTE</strong></span>
                    <p><strong><?= $user_name; ?></strong>, não resolver o chargeback em menos de 7 dias poderá gerar bloqueio permanente da sua conta.</p>
                </div>
            </div>
        <?php
        else:
        ?>
        <div class='dash_view_desc'>
            <div class='dash_view_desc_icon icon-book'></div>
            <div class='dash_view_desc_info'>
                <p>
                    <strong><?= $user_name; ?></strong>, aqui estão minhas indicações para você que quer complementar os estudos e gosta de uma boa leitura.
                </p>
            </div>
        </div>

        <div class="dash_view_books box_wrap">
            <?php
            $getPage = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT);
            $Page = ($getPage ? $getPage : 1);
            $Pager = new Pager(BASE .'/campus/campus.php?wc=cursos/livros&pg=', '<<', '>>', 3);
            $Pager->ExePager($Page, 6);
            $Read->ExeRead(DB_LIVROS, "WHERE livro_status = 1 AND livro_date <= NOW() ORDER BY livro_date DESC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");

            if (!empty($S)):
                $WhereString[0] = "AND (livro_title LIKE '%' :s '%' OR livro_content LIKE '%' :s '%')";
                $WhereString[1] = "&s={$S}";
            else:
                $WhereString[0] = "";
                $WhereString[1] = "";
            endif;

            $Read->FullRead("SELECT * FROM " . DB_LIVROS . " WHERE 1=1 "
                . "{$WhereString[0]} "
                . "ORDER BY livro_order ASC, livro_date DESC "
                . "LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}{$WhereString[1]}"
            );

            if (!$Read->getResult()):
                $Pager->ReturnPage();
                echo Erro("<span class='al_center icon-notification'>Ainda não existem Livros cadastrados aqui, {$user_name}!</span>", E_USER_NOTICE);
            else:
                foreach ($Read->getResult() as $POST):
                    extract($POST);
                    $PostCover = (file_exists("../uploads/{$livro_cover}") && !is_dir("../uploads/{$livro_cover}") ? "uploads/{$livro_cover}" : 'admin/_img/no_image.jpg');
                    $livro_title = (!empty($livro_title) ? $livro_title : 'Edite esse rascunho para poder exibir como Livro em seu site!');

                    echo "<article class='dash_view_books_item dash_view_course' id='{$livro_id}'>
                <div class='cover'>
                    <a class='al_center' title='{$livro_title}' target='_blank' href='{$livro_link}'>
                        <img alt='{$livro_title}' title='{$livro_title}' src='../tim.php?src={$PostCover}&w=144&h=204'>                        
                    </a>
                </div>
                <div class='info'>
                    <p class='category icon-bookmark'>{$livro_category}</p>
                    <h2>{$livro_title}</h2>
                    <p class='author'>por {$livro_book_author}</p>
                    <div class='info-text' style='margin-top: 15px'>
                        {$livro_content}
                    </div>
                    <a class='radius btn_small btn_green icon-info' title='{$livro_title}' target='_blank' href='{$livro_link}'>SAIBA MAIS</a>
                </div>
            </article>";
                endforeach;
                $Pager->ExePaginator(DB_LIVROS, "WHERE livro_status = 1 AND livro_date <= NOW()");
                echo $Pager->getPaginator();
            endif;
            ?>
        </div>
    </section><?php
endif;
?>