<?php require '../_cdn/widgets/contact/contact.wc.php'; ?>
<div class="wc_ead">
    <!--    EAD WIN-->
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
        <div class='wc_ead_alert_box {$_SESSION['wc_ead_alert'][0]}'>
            <div class='wc_ead_alert_icon icon-switch icon-notext'></div><div class='wc_ead_alert_text'>
                <p class='wc_ead_alert_title'>{$_SESSION['wc_ead_alert'][1]}</p>
                <p class='wc_ead_alert_content'>{$_SESSION['wc_ead_alert'][2]}</p>
            </div><div class='wc_ead_alert_close'><span class='icon-cross icon-notext'></span></div>
        </div>
    </div>";
        unset($_SESSION['wc_ead_alert']);
    endif;
    ?>

    <header class="dashboard_header">
        <div class="dashboard_header_title">
            <h1 class="icon-mug">Atividades</h1>
            <p class="dashboard_header_breadcrumbs">
                &raquo; <?= SITE_NAME; ?>
                <span class="crumb">/</span>
                <a title="Atividades" href="campus.php?wc=cursos/atividades">Atividades</a>
            </p>
        </div>
    </header>
    <div class='dash_view_desc dash_view_desc_activities'>
        <?php
        if (SITE_SOCIAL_GROUP):
            ?><a target='_blank' class='icon-bubbles4' href='<?= SITE_SOCIAL_GROUP; ?>'
                 title='Grupo Oficial do <?= SITE_NAME; ?>'>Grupo</a><?php
        endif;
        if (SITE_ADDR_EMAIL):
            ?><a target='_blank' class='jwc_contact icon-envelop icon-notext' href='<?= SITE_ADDR_EMAIL; ?>'
                 title='Central de Suporte'></a><?php
        endif;
        if (SITE_ADDR_PHONE_WHATSAPP):
            ?><a target='_blank' class='icon-whatsapp icon-notext'
                 href='https://api.whatsapp.com/send?phone=<?= SITE_ADDR_PHONE_WHATSAPP; ?>'
                 title='Comercial e Vendas'></a><?php
        endif;
        if (SITE_SOCIAL_INSTAGRAM):
            ?><a target='_blank' class='icon-instagram icon-notext'
                 href='https://www.instagram.com/<?= SITE_SOCIAL_INSTAGRAM; ?>'
                 title='<?= SITE_NAME; ?> no Instagram'></a><?php
        endif;
        if (SITE_SOCIAL_FB):
            ?><a target='_blank' class='icon-facebook2 icon-notext'
                 href='https://www.facebook.com/<?= SITE_SOCIAL_FB; ?>' title='<?= SITE_NAME; ?> no Facebook'></a><?php
        endif;
        if (SITE_SOCIAL_YOUTUBE):
            ?><a target='_blank' class='icon-youtube2 icon-notext'
                 href='https://www.youtube.com/c/<?= SITE_SOCIAL_YOUTUBE; ?>?sub_confirmation=1'
                 title='<?= SITE_NAME; ?> no YouTube'></a><?php
        endif;
        ?>
    </div>
    <div class="box_wrap">
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
                    <p>Olá, <strong><?= $user_name; ?></strong>, identificamos que seu pedido
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
            ?>

            <section class="dash_view_activities">
            <div class="dash_view_activities_favorites">
                <!--                <div class="showcase--minicourse__title">-->
                <!--                    <span class="section-title-name">Mastermind</span>-->
                <!--                </div>-->
                <div class="dash_view_activities_favorites_content">
                    <?php
                    $getPage = filter_input(INPUT_GET, 'campus', FILTER_VALIDATE_INT);
                    $Page = ($getPage ? $getPage : 1);
                    $Pager = new Pager(BASE .'/campus/campus.php?wc=cursos/atividades&campus=', '<', '>', 3);
                    $Pager->ExePager($Page, 12);
                    $Read->ExeRead(DB_EAD_COURSES, "WHERE course_status = 1 AND course_created <= NOW() ORDER BY course_created DESC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");

                    if (!$Read->getResult()):
                        $Pager->ReturnPage();
                        echo Erro("<div class='al_center'>Desculpe, mais ainda não existem Cursos cadastrados. Favor volte mais tarde :)</div>", E_USER_NOTICE);
                    else:
                        foreach ($Read->getResult() AS $Courses):
                            extract($Courses);

                            //GET COUNT MODULES AND TIME
                            $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
                            $ClassCount = $Read->getResult()[0]['ClassCount'];
                            $ClassTime = floor($Read->getResult()[0]['ClassTime'] / 60) . ":" . str_pad($Read->getResult()[0]['ClassTime'] % 60, 2, 0, 0);

                            $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL",
                                "user={$user_id}&course={$course_id}");
                            $ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

                            $CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : 0);

                            $CourseCompletedClass = ($ClassStudenCount == $ClassCount ? 'check-full':'check-outline');

                            $Read->FullRead("SELECT count(module_id) as total FROM " . DB_EAD_MODULES . " WHERE course_id = :course", "course={$course_id}");
                            $Modules = $Read->getResult()[0]['total'];

                            //GET COUNT STUDENTS
                            $Read->FullRead("SELECT count(enrollment_id) AS TotalEnrollment FROM " . DB_EAD_ENROLLMENTS . " WHERE course_id = :cs", "cs={$course_id}");
                            $StudentCount = str_pad($Read->getResult()[0]['TotalEnrollment'], 1, 0, 0);

                            //GET RATINGS
                            $CommentKey = $course_id;
                            $CommentType = 'course';

                            $CommentModerate = (COMMENT_MODERATE ? " AND (status = 1 OR status = 3)" : '');
                            $Read->FullRead("SELECT id FROM " . DB_COMMENTS . " WHERE course_id = :pid{$CommentModerate}", "pid={$course_id}");
                            $Aval = $Read->getRowCount();

                            $Read->FullRead("SELECT SUM(rank) as total FROM " . DB_COMMENTS . " WHERE course_id = :pid{$CommentModerate}", "pid={$course_id}");
                            $TotalAval = $Read->getResult()[0]['total'];
                            $TotalRank = $Aval * 5;
                            $getRank = ($TotalAval ? (($TotalAval / $TotalRank) * 50) / 10 : 0);
                            $Rank = str_repeat("<i class='fa fa-star'></i>", intval($getRank)) . str_repeat("<i class='fa fa-star-o'></i>", 5 - intval($getRank));

                            //CONSULTA SEGMENTO
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

                            $Read->ExeRead(DB_EAD_COURSES_SEGMENTS, "WHERE segment_id = :id", "id={$course_segment}");
                            if ($Read->getResult()):
                                $segment_color = $Read->getResult()[0]['segment_color'];
                            else:
                                $segment_color = "";
                            endif;

                            require 'inc/all_courses_home.php';
                        endforeach;
                    endif;
                    $Pager->ExePaginator(DB_EAD_COURSES, "WHERE course_status = 1 AND course_created <= NOW()");
                    ?>
                    <?php echo $Pager->getPaginator(); ?>
                </div>
                <footer class='dash_view_activities_newclasses'>
                    <h3 class='dash_view_activities_newclasses_title icon-power-cord'>Últimas Aulas Cadastradas:</h3>
                    <?php
                    $Read->FullRead("SELECT "
                        . "e.*,"
                        . "c.*"
                        . "FROM " . DB_EAD_CLASSES . " e "
                        . "INNER JOIN " . DB_EAD_COURSES . " c ON c.course_id = e.course_id "
                        . "ORDER BY e.class_id DESC LIMIT 10"
                    );

                    if ($Read->getResult()):
                        foreach ($Read->getResult() as $aulas):
                            extract($aulas);
                            ?><article>
                            <div>
                                <a href='campus.php?wc=cursos/estudar&id=<?= $course_id; ?>&class=<?= $class_id; ?>'
                                   title='Acessar aula <?= $class_title; ?>'>
                                    <img style="border-radius: 4px" src='<?= BASE; ?>/tim.php?src=uploads/<?= $course_cover; ?>&w=100&h=60'
                                         alt='Aula Um <?= $class_title; ?> do curso <?= $course_title; ?>'
                                         title='Aula <?= $class_title; ?> do curso <?= $course_title; ?>'>
                                </a>
                            </div>
                            <p><a href='campus.php?wc=cursos/estudar&id=<?= $course_id; ?>&class=<?= $class_id; ?>'
                                  title='Acessar aula <?= $class_title; ?>'>[<?= date("d/m/Y",
                                        strtotime($class_updated)); ?>] Aula <b><?= $class_title; ?></b> do curso
                                    <b><?= $course_title; ?></b></a></p>
                            </article><?php
                        endforeach;
                    endif;
                    ?>
                </footer>
            </div>
            <aside class='dash_view_activities_sidebar'>
            <article class='dash_view_activities_sidebar_widget'>
                <h3 class='dash_view_activities_sidebar_widget_title icon-stack'>Minha Evolução:</h3>
                <div class='dash_view_activities_sidebar_widget_content dash_view_activities_sidebar_blogs'>
                    <?php
                    $Read->FullRead("SELECT a.class_id, a.class_time, c.segment_title, c.segment_color, c.segment_icon
                                            FROM ws_ead_classes a, ws_ead_courses b, ws_ead_courses_segments c
                                            WHERE a.course_id IN (SELECT DISTINCT course_id FROM ws_ead_enrollments WHERE user_id = :user)
                                            AND a.course_id = b.course_id
                                            AND b.course_segment = c.segment_id",
                        "user={$User['user_id']}");
                    $result = [];
                    foreach ($Read->getResult() as $class):
                        $Read->FullRead("SELECT a.class_id, a.student_class_seconds, a.student_class_check
                                                FROM ws_ead_student_classes a
                                                WHERE a.class_id = :c 
                                                AND a.user_id = :user",
                            "c={$class['class_id']}&user={$User['user_id']}");
                        if ($Read->getResult()):
                            $student = $Read->getResult()[0];
                        else:
                            $student = [
                                'student_class_seconds' => null,
                                'student_class_check' => null
                            ];
                        endif;

                        if (!isset($result[$class['segment_title']])):
                            $result[$class['segment_title']] = [];
                            $result[$class['segment_title']]['classTime'] = 0;
                            $result[$class['segment_title']]['studentTime'] = 0;
                            $result[$class['segment_title']]['segment_color'] = 0;
                            $result[$class['segment_title']]['segment_icon'] = 0;
                        endif;

                        $result[$class['segment_title']]['classTime'] += (is_null($class['class_time']) || empty($class['class_time']) ? 0 : $class['class_time'] * 60);
                        $result[$class['segment_title']]['studentTime'] += (!is_null($student['student_class_check']) && !empty($student['student_class_check']) ? $class['class_time'] * 60 : (is_null($student['student_class_seconds']) ? 0 : (!empty($student['student_class_seconds']) ? $student['student_class_seconds'] : 0)));
                        $result[$class['segment_title']]['segment_color'] = (is_null($class['segment_color']) || empty($class['segment_color']) ? 0 : $class['segment_color']);
                        $result[$class['segment_title']]['segment_icon'] = (is_null($class['segment_icon']) || empty($class['segment_icon']) ? 0 : $class['segment_icon']);

                    endforeach;
                    foreach ($result as $indice => $valor):
                        ?>
                        <div class='dash_view_activities_sidebar_widget_skillset'
                             style='background:  <?= $valor['segment_color']; ?>;'>
                                <span class='<?= $valor['segment_icon']; ?>'
                                      style='width:<?= ($valor['classTime'] > 0 ? number_format((($valor['studentTime'] / $valor['classTime']) * 100)) : 0); ?>%'><?= $indice; ?>
                                    <?= ($valor['classTime'] > 0 ? number_format((($valor['studentTime'] / $valor['classTime']) * 100)) : 0); ?>%
                                </span>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
            </article>

            <article class='dash_view_activities_sidebar_widget'>
            <h3 class='dash_view_activities_sidebar_widget_title icon-newspaper'>Últimos Artigos:</h3>
            <div class='dash_view_activities_sidebar_widget_content dash_view_activities_sidebar_blogs'>

            <?php
            $Read->ExeRead(DB_POSTS,
                "WHERE post_status = 1 AND post_date <= NOW() ORDER BY post_date DESC LIMIT 3");
            if (!$Read->getResult()):
                echo Erro("<div class='al_center'>Desculpe, mais ainda não existem Artigos cadastrados.</div>", E_USER_NOTICE);
            else:
                foreach ($Read->getResult() as $Post):
                    extract($Post);
                    ?>
                    <article class='dash_view_activities_sidebar_blogs_article'>
                    <div>
                        <a href='<?= BASE; ?>/artigo/<?= $Post['post_name']; ?>'
                           title='<?= $Post['post_title']; ?>'>
                            <img style="border-radius: 4px" alt="<?= $Post['post_title']; ?>"
                                 title="<?= $Post['post_title']; ?>"
                                 src='<?= BASE; ?>/tim.php?src=uploads/<?= $Post['post_cover']; ?>&w=100&h=100'/>
                        </a>
                    </div>
                    <h4>
                        <a href='<?= BASE; ?>/artigo/<?= $Post['post_name']; ?>'
                           title='<?= $Post['post_title']; ?>'><?= $Post['post_title']; ?></a>
                        <span style="display: block; font-size: 0.9em; color: #b9b9b9; margin-top: 5px">
                            <i style="color: #00CAB6" class="icon-calendar icon-notext"></i>
                            <?= date("d/m/Y \à\s H\hi", strtotime($Post['post_date'])); ?>
                        </span>
                    </h4>
                    </article><?php
                endforeach;
                ?>
                </div>
                </article>
                </aside>
                </section>
            <?php
            endif;
        endif;

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
                    endif;
                endif;
            endforeach;
        endif;
        ?>
    </div>
</div>
