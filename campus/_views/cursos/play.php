<div class="wc_ead">
    <?php
    if (!$Read):
        $Read = new Read;
    endif;

    $Email = new Email;

    //GET CLASS ID BY SESSION
    $ClassId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($ClassId):
        $Read->ExeRead(DB_CLASS, " WHERE class_id = :class", "class={$ClassId}");
        if ($Read->getResult()):
            extract($Read->getResult()[0]);

            $UpdateView = ['class_views' => $class_views + 1, 'class_lastview' => date('Y-m-d H:i:s')];
            $Update->ExeUpdate(DB_CLASS, $UpdateView, "WHERE class_id = :id", "id={$class_id}");
        else:
            $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$_SESSION['userLogin']['user_name']}, erro ao acessar:", "Não foi possível identificar a Aula  que você tentou acessar :("];
            header("Location: campus.php?wc=cursos/lives");
            exit;
        endif;
    endif;

    $Read->FullRead("SELECT segment_title, segment_color FROM " . DB_EAD_COURSES_SEGMENTS . " WHERE segment_id = :id", "id={$class_segment}");
    $ClassSegment = $Read->getResult()[0];

    $Read->LinkResult(DB_USERS, "user_id", $class_author, "user_name, user_lastname, user_thumb");
    $ClassTutor = $Read->getResult()[0];

    $Read->ExeRead(DB_USERS, "WHERE user_id = :user", "user={$User['user_id']}");
    if ($Read->getResult()):
        $Student = $Read->getResult()[0];
        extract($Student);

        $UpdateStudentAcess = ['user_lastaccess' => date('Y-m-d H:i:s'), 'user_login' => time()];
        $Update = new Update;
        $Update->ExeUpdate(DB_USERS, $UpdateStudentAcess, "WHERE user_id = :user", "user={$user_id}");
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
        //EXPIRED ALERT
        $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user AND order_status = :status",
            "user={$user_id}&status=expired");
        if ($Read->getResult()):
            $OrderExpired = $Read->getResult()[0];

            $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :course",
                "course={$OrderExpired['course_id']}");
            $OrderExpiredCourse = ($Read->getResult() ? "Curso {$Read->getResult()[0]['course_title']}" : "produto {$OrderExpired['order_product_id']}");
            ?>
            <div class="chargeback_container">
                <div class="chargeback_item_img">
                    <img src="<?= BASE ?>/campus/_img/triste-icon.png" alt="Pedido Expirado"/>
                </div>
                <div class="chargeback_item">
                    <span>SEU PEDIDO ESTA EXPIRADO :(</span>
                    <p>Olá, <strong><?= $user_name; ?></strong>! Identificamos que seu pedido
                        <strong>#<?= str_pad($OrderExpired['order_id'], 5, 0, 0); ?></strong> para o
                        <strong><?= $OrderExpiredCourse; ?></strong> esta expirado.</p>
                    <span>POR ISSO SEU ACESSO FOI BLOQUEADO!</span>
                    <p>Isso ocorre quando a compensação do seu boleto não é realizada dentro do prazo do vencimento.</p>
                    <p>Quando este prazo expira ou seu pedido é cancelado o acesso a Central de Aulas fica bloqueada temporariamente até que o
                        pagamento seja processado e autorizado novamente.</p>
                    <span>COMO RESOLVER?</span>
                    <p>Entre em contato conosco pelo e-mail <strong><?= SITE_ADDR_EMAIL; ?></strong> e solicite
                        liberação do seu pagamento para a transação
                        <strong><?= $OrderExpired['order_transaction']; ?></strong>.</p>
                    <span><strong>IMPORTANTE</strong></span>
                    <p><strong><?= $user_name; ?></strong>, não resolver essa pendência em menos de 7 dias poderá gerar
                        bloqueio permanente da sua conta.</p>
                </div>
            </div>
        <?php
        else:
        //CANCELED ALERT
        $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user AND order_status = :status",
            "user={$user_id}&status=canceled");
        if ($Read->getResult()):
            $OrderCanceled = $Read->getResult()[0];

            $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :course",
                "course={$OrderCanceled['course_id']}");
            $OrderCanceledCourse = ($Read->getResult() ? "Curso {$Read->getResult()[0]['course_title']}" : "produto {$OrderCanceled['order_product_id']}");
            ?>
            <div class="chargeback_container">
                <div class="chargeback_item_img">
                    <img src="<?= BASE ?>/campus/_img/triste-icon.png" alt="Pedido Cancelado"/>
                </div>
                <div class="chargeback_item">
                    <span>SEU PEDIDO ESTA CANCELADO :(</span>
                    <p>Olá, <strong><?= $user_name; ?></strong>! Identificamos que seu pedido
                        <strong>#<?= str_pad($OrderCanceled['order_id'], 5, 0, 0); ?></strong> para o
                        <strong><?= $OrderCanceledCourse; ?></strong> esta cancelado.</p>
                    <span>POR ISSO SEU ACESSO FOI BLOQUEADO!</span>
                    <p>Isso ocorre quando a compensação do seu boleto não é realizada dentro do prazo do vencimento.</p>
                    <p>Quando este prazo expira ou seu pedido é cancelado o acesso a Central de Aulas fica bloqueada temporariamente até que o
                        pagamento seja processado e autorizado novamente.</p>
                    <span>COMO RESOLVER?</span>
                    <p>Entre em contato conosco pelo e-mail <strong><?= SITE_ADDR_EMAIL; ?></strong> e solicite
                        liberação do seu pagamento para a transação
                        <strong><?= $OrderCanceled['order_transaction']; ?></strong>.</p>
                    <span><strong>IMPORTANTE</strong></span>
                    <p><strong><?= $user_name; ?></strong>, não resolver essa pendência em menos de 7 dias poderá gerar
                        bloqueio permanente da sua conta.</p>
                </div>
            </div>
        <?php
        else:
        ?><div class="app_play_post_video">
            <div class="app_play_post_video_media">
                <div class="embed-container" style="border-radius: 5px">
                    <?php if (is_numeric($class_video)): ?>
                        <iframe itemprop="video" width='100%' frameborder="0" allow="autoplay" src="https://player.vimeo.com/video/<?= $class_video;?>?title=0&byline=0&portrait=0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                    <?php else: ?>
                        <iframe src="https://www.youtube.com/embed/<?= $class_video; ?>?showinfo=0&amp;rel=0" frameborder="0" allowfullscreen></iframe>
                    <?php endif; ?>
                </div>
            </div>
            <div class="app_play_post_video_actions">
                <?php
                $Read->ExeRead(DB_CLASS_CHECK, "WHERE user_id = :uid AND class_id = :cid","uid={$user_id}&cid={$ClassId}");
                if ($Read->getResult()):
                    $classCheck = $Read->getResult()[0];
                else:
                    $classCheck['class_check'] = null;
                endif;
                ?>

                <div data-played="true" class="app_play_post_video_actions_check wc_student_task_manager_check jwc_clas_task_check transition <?= (!empty($classCheck['class_check']) ? "icon-checkbox-checked played" : "icon-checkbox-unchecked"); ?>">
                    <?= (!empty($classCheck['class_check']) ? "Concluída" : "Marcar como concluída"); ?>
                </div>
                <input type="hidden" name="classId" value="<?= $ClassId; ?>"/>
                <input type="hidden" name="userId" value="<?= $user_id; ?>"/>

                <div class="app_play_post_video_actions_review">
                    <p>Qual sua nota para esta Live?</p>
                    <div class="app_play_post_video_actions_review_stars">
                        <span data-review="true" class="icon-star-empty icon-notext"></span>
                        <span data-review="true" class="icon-star-empty icon-notext"></span>
                        <span data-review="true" class="icon-star-empty icon-notext"></span>
                        <span data-review="true" class="icon-star-empty icon-notext"></span>
                        <span data-review="true" class="icon-star-empty icon-notext"></span>
                    </div>
                </div>

                <div class="app_play_post_video_actions_category">
                    <p class="legend">Título da Live:</p>
                    <p><?= $class_title;?></p>
                </div>

                <div class="app_play_post_video_actions_category m_top">
                    <p class="legend">Categoria:</p>
                    <p><?= $ClassSegment['segment_title']; ?></p>
                </div>

                <div class="app_play_post_video_actions_played">
                    <div class="app_play_post_video_actions_played_user">
                        <img class="rounded" src="<?= BASE; ?>/tim.php?src=uploads/<?= $ClassTutor['user_thumb']; ?>&w=50&h=50"
                             alt="<?= "{$ClassTutor['user_name']} {$ClassTutor['user_lastname']}"; ?>" title="<?= "{$ClassTutor['user_name']} {$ClassTutor['user_lastname']}"; ?>"/>
                        <p>Realizada por <?= "{$ClassTutor['user_name']} {$ClassTutor['user_lastname']}"; ?> no dia <?= $class_date_show ? date('d/m/Y', strtotime($class_date_show)) : date('d/m/Y'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="app_play_post_content htmlcontent">
            <?= $class_content;?>
        </div>

        <?php if (APP_COMMENTS && COMMENT_ON_CLASS): ?>
            <div style="padding: 50px; background: white">
                <?php
                $CommentKey = $class_id;
                $CommentType = 'class';
                require  '../_cdn/widgets/comments/comments.php';
                ?>
                <div class="clear"></div>
            </div>
        <?php endif; ?>
    </section><?php
endif;
endif;
endif;
?>
</div>