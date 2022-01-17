<div class="wc_ead">
    <!--    EAD WIN -->
    <div class='jwc_ead_win wc_ead_win wc_ead_win_default'>
        <div class='wc_ead_win_box'>
            {{IMAGE}}
            <div class='wc_ead_win_box_content al_center'>
                <p class='title icon-{{ICON}}'>{{TITLE}}</p>
                <p>{{CONTENT}}</p>
                <a target="_blank" title='{{LINK_TITLE}}' href='{{LINK}}' class='btn btn_blue icon-{{LINK_ICON}}'>{{LINK_NAME}}</a>
                <span title='Fechar Aviso!' class='m_left btn btn_red icon-cross jwc_ead_close_bonus'>Fechar</span>
            </div>
        </div>
    </div>

    <!--MODAL CERTIFICATION-->
    <div class='jwc_ead_win wc_ead_win wc_ead_win_default wc_ead'>
        <div class='wc_ead_win_box'>
            {{IMAGE}}
            <div class='wc_ead_win_box_content al_center'>
                <p class='title icon-{{ICON}}'>{{TITLE}}</p>
                <p>{{CONTENT}}</p>
                <a target="_blank" style="color: #FFFFFF" title='{{LINK_TITLE}}' href='{{LINK}}'
                   class='btn btn_blue icon-{{LINK_ICON}}'>{{LINK_NAME}}</a>
                <a style="color: #FFFFFF" title='Fechar Aviso!' href='<?= BASE ?>/campus'
                   class='btn btn_red icon-cross'>Fechar</a>
                <!--<span title='Fechar Aviso!' class='m_left btn btn_red icon-cross jwc_ead_close_bonus'>Fechar</span>-->
            </div>
        </div>
    </div>

    <!--BASE ALERT-->
    <div class="wc_ead_alert">
        <div class="wc_ead_alert_box">
            <div class="wc_ead_alert_icon icon-switch icon-notext"></div>
            <div class="wc_ead_alert_text">
                <p class="wc_ead_alert_title">{TITLE}</p>
                <p class="wc_ead_alert_content">{CONTENT}</p>
            </div>
            <div class="wc_ead_alert_close"><span class="icon-cross icon-notext"></span></div>
        </div>
    </div>

    <!--BASE MODAL-->
    <div class="wc_ead_modal">
        <div class="wc_ead_modal_box">
            <span class="wc_ead_modal_close icon-cross icon-notext"></span>
            <p class="wc_ead_modal_title"><span>{TITLE}</span></p>
            <div class="wc_ead_modal_content">{CONTENT}</div>
            <div class="wc_ead_modal_help">Precisa de ajuda? <a href="mailto:<?= SITE_ADDR_EMAIL; ?>"
                                                                title="Enviar E-mail!">Entre em contato!</a></div>
        </div>
    </div>

    <!--UPLOAD MODAL-->
    <div class="wc_ead_upload jwc_ead_upload">
        <div class="wc_ead_upload_progress jwc_ead_upload_progress">0%</div>
    </div>

    <!--LOAD MODAL-->
    <div class="wc_ead_load jwc_ead_load wc_ead">
        <div class="wc_ead_load_content">
            <img src="<?= BASE; ?>/campus/_img/load.svg" alt="Aguarde, enviando solicitação!" width="50"
                 title="Aguarde, enviando solicitação!"/>
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
            <h1 class="icon-stack">Meus Cursos</h1>
            <p class="dashboard_header_breadcrumbs">
                &raquo; <?= SITE_NAME; ?>
                <span class="crumb">/</span>
                <a title="Meus Cursos" href="campus.php?wc=cursos/cursos">Meus Cursos</a>
            </p>
        </div>
    </header>
    <div class="box_wrap">
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
                        <strong><?= $OrderChargebackCourse; ?></strong> sofreu um chargeback de compra na Hotmart.</p>
                    <span>SEU ACESSO FOI BLOQUEADO TEMPORARIAMENTE!</span>
                    <p>Um chargeback é uma negativa de compra no cartão de crédito. Isso ocorre quando o portador do
                        cartão informa junto a operadora que não autorizou ou reconhece a compra.</p>
                    <p>Quando um chargeback ocorre o acesso a todos os cursos fica bloqueado temporariamente até que o
                        pagamento seja processado e autorizado novamente.</p>
                    <span>COMO RESOLVER UM CHARGEBACK?</span>
                    <p>Entre em contato com a Hotmart pelo e-mail <strong>suporte@hotmart.com.br</strong> e solicite
                        liberação do seu pagamento para a transação
                        <strong><?= $OrderChargeback['order_transaction']; ?></strong>.</p>
                    <p>Precisa de nossa ajuda? Entre em contato pelo e-mail <?= SITE_ADDR_EMAIL; ?></p>
                    <span><strong>IMPORTANTE</strong></span>
                    <p><strong><?= $user_name; ?></strong>, não resolver o chargeback em menos de 7 dias poderá gerar
                        bloqueio permanente da sua conta.</p>
                </div>
            </div>
        <?php
        else:
            $Read->FullRead("SELECT "
                . "e.*,"
                . "c.*"
                . "FROM " . DB_EAD_ENROLLMENTS . " e "
                . "INNER JOIN " . DB_EAD_COURSES . " c ON c.course_id = e.course_id "
                . "WHERE e.user_id = :user "
                . "ORDER BY e.enrollment_access DESC, c.course_order ASC", "user={$user_id}"
            );

            if (!$Read->getResult()):
                echo "<div class='trigger trigger_info trigger-none icon-info al_center'>Você ainda não tem cursos {$user_name}!</div>";
            else:
                foreach ($Read->getResult() as $CS):
                    extract($CS);

                    $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE course_id = :cs",
                        "cs={$course_id}");
                    $ClassCount = $Read->getResult()[0]['ClassCount'];

                    $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL",
                        "user={$user_id}&course={$course_id}");
                    $ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

                    $CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : 0);
                    $CourseCompletedClass = ($ClassStudenCount == $ClassCount ? 'check-full':'check-outline');
                    $course_cover = ($course_cover ? "uploads/{$course_cover}" : 'campus/_img/no_image.jpg');

                    $CourseBonusOpen = null;
                    if ($enrollment_bonus):
                        $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_ENROLLMENTS . " WHERE enrollment_id = :enrollbonus)",
                            "enrollbonus={$enrollment_bonus}");
                        $CourseBonusOpen = "<p class='dash_view_course_bonus_fire icon-heart icon-notext radius wc_tooltip' style='z-index: 9999' title=''><span class='wc_tooltip_balloon'>Bônus do curso {$Read->getResult()[0]['course_title']}</span></p>";
                    endif;

                    $Read->FullRead("SELECT COUNT(support_id) as tickets FROM ws_ead_support a INNER JOIN ws_ead_classes b ON a.class_id = b.class_id INNER JOIN ws_ead_courses c ON b.course_id = c.course_id WHERE user_id = :user AND support_status = 3 AND c.course_id = :course GROUP BY c.course_id", "user={$user_id}&course={$course_id}");
                    if ($Read->getResult()):
                        $tickets = $Read->getResult()[0]['tickets'];
                    else:
                        $tickets = 0;
                    endif;

                    $Read->ExeRead(DB_EAD_COURSES_SEGMENTS, "WHERE segment_id = :id", "id={$course_segment}");
                    if ($Read->getResult()):
                        $segment = $Read->getResult()[0]['segment_title'];
                    else:
                        $segment = "";
                    endif;

                    $Read->ExeRead(DB_EAD_COURSES_SEGMENTS, "WHERE segment_id = :id", "id={$course_segment}");
                    if ($Read->getResult()):
                        $segment_icon = $Read->getResult()[0]['segment_icon'];
                    else:
                        $segment_icon = "";
                    endif;

                    $Read->FullRead("SELECT count(module_id) as total FROM " . DB_EAD_MODULES . " WHERE course_id = :course",
                        "course={$course_id}");
                    $Modules = $Read->getResult()[0]['total'];
                    ?><article class='box box5 dash_view_course'>
                    <div class='apl-card vertical course'>
                        <?php
                        if (!empty($enrollment_end)):
                            $EndDayNow = new DateTime();
                            $EndDayRow = new DateTime($enrollment_end);
                            $EndDayDif = $EndDayNow->diff($EndDayRow);

                            if ($EndDayDif->days < 90 || $EndDayDif->invert):
                                if ($course_vendor_renew && ($EndDayDif->days < 90 || $EndDayDif->invert)):
                                    $VendorRenew = "<a style='z-index: 99999; color: #D94452' target='_blank' href='{$course_vendor_renew}&sck={$course_name}_re' class='dash_view_course_calendar " . ($CourseBonusOpen ? 'wc_ead_home_courses_course_renew' : '') . " icon-fire icon-notext wc_tooltip'><span class='wc_tooltip_balloon icon-fire'>Renove seu acesso a esse curso por mais {$course_vendor_access} meses com 80% de desconto.</span></a>";
                                endif;
                            endif;
                        endif;

                        if ($CourseBonusOpen): ?>
                            <div class='dash_view_course_bonus'><?= $CourseBonusOpen; ?></div>
                        <?php else: ?>
                            <div style="display: none"><?= $CourseBonusOpen; ?></div>
                        <?php endif; ?>

                        <!--certificado-->
                        <?php
                        if ($course_certification_workload && EAD_STUDENT_CERTIFICATION):
                            if ($ClassCount == 0 || $CourseCompletedPercent < $course_certification_request):
                                echo "<div class='wc_ead_home_courses_certifications wc_tooltip dash_view_course_certificate icon-stopwatch icon-notext'><span class='wc_tooltip_balloon'>Certificado Pendente!</span></div>";
                            else:
                                $Read->FullRead("SELECT certificate_key FROM " . DB_EAD_STUDENT_CERTIFICATES . " WHERE enrollment_id = :enrol",
                                    "enrol={$enrollment_id}");
                                if (!$Read->getResult()):
                                    echo "<a class='icon-trophy wc_ead_home_courses_certifications wc_ead_home_courses_certifications_true jwc_ead_certification dash_view_course_certificate icon-trophy' id='{$enrollment_id}' href='javascript:void(0)'>Solicitar Certificado</a>";
                                else:
                                    echo "<a target='_blank' href='" . BASE . "/imprimir-certificados/campus.php?wc=cursos/imprimir&id={$Read->getResult()[0]['certificate_key']}' class='icon-printer icon-notext dash_view_course_certificate radius wc_tooltip' title='Imprimir Certificado'><span class='wc_tooltip_balloon'>Imprimir Certificado!</span></a>";
                                endif;
                            endif;
                        endif;
                        ?>

                        <?php
                        $Read->LinkResult(DB_EAD_ORDERS, "course_id", $course_id, 'order_signature_plan, order_signature_recurrency, order_signature_period');
                        if (!empty($Read->getResult()[0]['order_signature_plan'])):
                            $order_signature_recurrency = str_pad($Read->getResult()[0]['order_signature_recurrency'],
                                2, 0, 0);
                            echo "<p class='icon-rss2'>Assinatura {$Read->getResult()[0]['order_signature_plan']}</p>";
                        elseif ($enrollment_end && empty($enrollment_bonus)):
                            if (!$EndDayDif->invert):
                                echo "<div style='' class='dash_view_course_calendar icon-calendar icon-notext wc_tooltip'><span class='wc_tooltip_balloon'>Sua matrícula vence dia " . date("d/m/Y \à\s H\hi", strtotime($enrollment_end)) . "</span></div>";
                            else:
                                echo "{$VendorRenew}";
                            endif;
                        endif;
                        ?>

                        <a href='campus.php?wc=cursos/estudar&id=<?= $course_id; ?>' title='Ir para o Curso <?= $course_title; ?>' alt='Ir para o Curso <?= $course_title; ?>'>
                            <div class='coverlink'></div>
                        </a>
                        <div class='card-header' style='background: <?= $course_color; ?>'>
                            <div class='wrapper'>
                                <img class='brand' src='<?= BASE; ?>/tim.php?src=<?= $course_cover; ?>&w=300&h=100' alt='Abrir o Curso <?= $course_title; ?>' title='Abrir o Curso <?= $course_title; ?>'>
                                <h1 style='display: none'><?= $course_title; ?></h1>
                            </div>
                        </div>
                        <div class='card-footer '>
                            <div class='status <?= $CourseCompletedClass; ?>'></div>
                            <p><?= $ClassStudenCount; ?> / <?= $ClassCount; ?> aulas completas</p>
                            <div class='apl-progress tiny'>
                                <div class='progress-course' data-width='<?= $CourseCompletedPercent; ?>' style='background-color: #3BB75D; width:<?= $CourseCompletedPercent; ?>'></div>
                            </div>

                        </div>
                    </div>
                    </article><?php
                endforeach;
            endif;
        endif;
        ?>
    </div>

    <?php
    //READ BONUS
    $Read->FullRead(
        "SELECT "
        . "b.course_id,"
        . "b.bonus_course_id,"
        . "b.bonus_wait,"
        . "c.course_title,"
        . "c.course_name,"
        . "c.course_cover,"
        . "e.enrollment_id,"
        . "e.enrollment_start,"
        . "e.enrollment_end "
        . "FROM " . DB_EAD_COURSES_BONUS . " b "
        . "INNER JOIN " . DB_EAD_COURSES . " c ON b.bonus_course_id = c.course_id "
        . "LEFT JOIN " . DB_EAD_ENROLLMENTS . " e ON (b.course_id = e.course_id AND e.user_id = :user) "
        . "WHERE b.course_id IN(SELECT course_id FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id = :user) "
        . "AND (b.bonus_course_id NOT IN(SELECT course_id FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id = :user) "
        . "OR e.enrollment_end > (SELECT enrollment_end FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id = :user AND course_id = b.bonus_course_id) ) "
        . "AND CASE WHEN b.bonus_ever = 2 THEN e.enrollment_start >= b.bonus_ever_date ELSE 1 = 1 END "
        . "ORDER BY e.enrollment_end DESC, b.bonus_wait ASC", "user={$user_id}"
    );

    //SHOW BONUS
    $ShowBonus = null;
    if ($Read->getResult()):
        $EnrollmentUpdateKey = array();
        foreach ($Read->getResult() as $Bonus):
            $Read->FullRead(
                "SELECT "
                . "e.enrollment_id "
                . "FROM " . DB_EAD_ENROLLMENTS . " e "
                . "WHERE e.user_id = :user "
                . "AND e.course_id = :course", "user={$user_id}&course={$Bonus['bonus_course_id']}"
            );

            if ($Read->getResult()):
                if (!in_array($Bonus['course_title'], $EnrollmentUpdateKey)):
                    $EnrollmentUpdateKey[] = $Bonus['course_title'];
                    $UpdateBonus = [
                        'enrollment_bonus' => $Bonus['enrollment_id'],
                        'enrollment_end' => $Bonus['enrollment_end']
                    ];
                    $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateBonus,
                        "WHERE user_id = :user AND enrollment_id = :enroll",
                        "user={$user_id}&enroll={$Read->getResult()[0]['enrollment_id']}");
                endif;
            else:
                $Bonus['course_cover'] = (file_exists("../uploads/{$Bonus['course_cover']}") && !is_dir("../uploads/{$Bonus['course_cover']}") ? "uploads/{$Bonus['course_cover']}" : 'admin/_img/no_image.jpg');

                //FREE DAYS || TIME
                $DayThis = new DateTime(date("Y-m-d H:i:s"));
                $DayPlay = new DateTime($Bonus['enrollment_start'] . "+{$Bonus['bonus_wait']}days");
                $BonusDiff = $DayThis->diff($DayPlay);

                if (($BonusDiff->h <= 1 || $BonusDiff->invert) && empty($EnrollmentFree)):
                    $EnrollmentFree = true;

                    $CreateUserBonus = [
                        'user_id' => $user_id,
                        'course_id' => $Bonus['bonus_course_id'],
                        'enrollment_bonus' => $Bonus['enrollment_id'],
                        'enrollment_start' => date('Y-m-d H:i:s'),
                        'enrollment_end' => $Bonus['enrollment_end']
                    ];
                    $Create = new Create;
                    $Create->ExeCreate(DB_EAD_ENROLLMENTS, $CreateUserBonus);

                    echo "<div class='wc_ead_win'>"
                        . "<div class='wc_ead_win_box'>"
                        . "<img style='border-radius: 5px 5px 0 0;' src='" . BASE . "/tim.php?src={$Bonus['course_cover']}&w=" . round(IMAGE_W / 2) . "&h=" . round(IMAGE_H / 2) . "' alt='{$Bonus['course_title']}' title='{$Bonus['course_title']}'/>"
                        . "<div class='wc_ead_win_box_content al_center'>"
                        . "<p class='title icon-heart'>Uoolll {$user_name} :)</p>"
                        . "<p>O curso <b>{$Bonus['course_title']}</b> acaba de ser liberado como bônus em sua conta, e você já pode iniciar seus estudos!</p>"
                        . "<a title='Ver Curso Agora!' href='" . BASE . "/campus/campus.php?wc=cursos/estudar&id={$Bonus['bonus_course_id']}' class='btn btn_blue icon-lab'>Ver curso agora</a>"
                        . "<span title='Fechar Aviso!' class='m_left btn btn_red icon-cross jwc_ead_close_bonus'>Fechar</span>"
                        . "</div>"
                        . "</div>"
                        . "</div>";
                else:
                    $ShowBonus .= "<article class='box box4 dash_view_course'>"
                        . "<img alt='{$Bonus['course_title']}' title='{$Bonus['course_title']}' src='" . BASE . "/tim.php?src={$Bonus['course_cover']}&w=" . IMAGE_W / 3 . "&h=" . IMAGE_H / 3 . "'/>"
                        . "<div style='text-align: center;'>"
                        . " <h1 style='margin: 0; padding: 10px; font-weight: 500; font-size: 0.65em; background: #333; color: #fff;'><span class='icon-lock wc_tooltip'><span class='wc_tooltip_balloon'>Curso {$Bonus['course_title']}</span>" . ($BonusDiff->days > 1 ? "Libera em {$BonusDiff->days} Dias" : ($BonusDiff->days == 1 ? "Libera em 1 Dia" : "Libera em {$BonusDiff->h} Horas")) . "</span></h1>"
                        . "</div>"
                        . "</article>";
                endif;
            endif;
        endforeach;
        if ($ShowBonus):
            echo '<section class="box_wrap">'
                . '<header class="wc_ead_home_header col" style="margin-top: 30px; font-size: 0.8em;">'
                . '<h2 class="icon-rocket">Liberações Pendentes:</h2>'
                . '<p>Confira os bônus a serem liberados!</p>'
                . '</header>'
                . $ShowBonus .
                '</section>';
        endif;
    endif;
    ?>
</div>
