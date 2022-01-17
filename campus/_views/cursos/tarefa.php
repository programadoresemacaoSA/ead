<div class="wc_ead">
    <div class="trigger_box ds_none"></div>
    <?php
    //GET STUDENT
    if (!empty($_SESSION['userLogin'])):
        $Read->ExeRead(DB_USERS, "WHERE user_id = :user", "user={$User['user_id']}");
        if (!$Read->getResult()):
            header("Location: campus.php?wc=cursos/atividades");
            exit;
        else:
            $Student = $Read->getResult()[0];
            extract($Student);
            $Update = new Update;
            $UpdateStudentAcess = ['user_lastaccess' => date('Y-m-d H:i:s'), 'user_login' => time()];
            $Update->ExeUpdate(DB_USERS, $UpdateStudentAcess, "WHERE user_id = :user", "user={$user_id}");
        endif;
    else:
        header("Location: campus.php?wc=cursos/atividades");
        exit;
    endif;

    //VALIDATE CHARGEBACK
    $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user AND order_status = :status", "user={$user_id}&status=chargeback");
    if ($Read->getResult()):
        header("Location: " . BASE . "/campus/");
        exit;
    endif;

    $CourseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    //GET COURSE ID BY SESSION
    if ($CourseId):
        $Read->FullRead("SELECT course_name FROM " . DB_EAD_COURSES . " WHERE course_id = :course", "course={$CourseId}");
        if ($Read->getResult()):
            $course_name = $Read->getResult()[0]['course_name'];
            $course_id = $CourseId;
        else:
            $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$user_name}, erro ao acessar:", "Não foi possível identificar o Curso que você tentou acessar :("];
            header("Location: campus.php?wc=cursos/atividades");
            exit;
        endif;
    endif;

    $ClassId = filter_input(INPUT_GET, 'class', FILTER_VALIDATE_INT);
    //GET TASK
    if (!$ClassId):
        $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$user_name}, erro ao acessar:", "Não foi possível identificar a aula que você tentou acessar :("];
        header("Location: campus.php?wc=cursos/estudar&id={$course_id}");
        exit;
    else:
        $Read->ExeRead(DB_EAD_CLASSES, "WHERE class_id = :id AND course_id = :course", "id={$ClassId}&course={$course_id}");
        if (!$Read->getResult()):
            $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$user_name}, erro ao acessar:", "Não foi possível identificar a aula que você tentou acessar :("];
            header("Location: campus.php?wc=cursos/estudar&id={$course_id}");
            exit;
        else:
            extract($Read->getResult()[0]);

            $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id = :course", "course={$course_id}");
            extract($Read->getResult()[0]);

            //STUDENT CONTROL NAVIGATION RENEW
            $_SESSION['wc_student_course'] = $course_id;
        endif;
    endif;

    //VALID ENROLLMENT
    $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE course_id = :course AND user_id = :user", "course={$course_id}&user={$user_id}");
    if (!$Read->getResult()):
        $_SESSION['wc_ead_alert'] = ['red', "Ooops, você ainda não tem esse curso!", "Então, {$_SESSION['userLogin']['user_name']}, para estudar o <strong>Curso {$course_title}</strong>, antes você precisa adquiri-lo. Clique no botão <strong><a target='_blank' title='Saiba Mais' alt='Saiba Mais' href='". BASE . "/curso/{$course_name}'>SAIBA MAIS</a></strong> para conhecer uma oferta especial que preparamos para você!"];
        header("Location: campus.php?wc=cursos/estudar&id=" . $course_id);
        exit;
    else: extract($Read->getResult()[0]);
        $_SESSION['wc_student_enrollment_id'] = ($_SESSION['userLogin']['user_level'] < 6 ? $enrollment_id : null);

        //ENROLLMENT EXPIRED
        if (!empty($enrollment_bonus)):
            $Read->LinkResult(DB_EAD_ENROLLMENTS, "enrollment_id", $enrollment_bonus, 'enrollment_end');
            if (!empty($Read->getResult()) && !empty($Read->getResult()[0]['enrollment_end']) && time() >= strtotime($Read->getResult()[0]['enrollment_end'])):
                $_SESSION['wc_ead_alert'] = ['red', "Oppsss, Bônus bloqueado:", "Desculpe {$user_name}, mas para acessar um Bônus a assinatura ou Curso que liberou o mesmo não pode estar expirada(o)."];
                header("Location: campus.php?wc=cursos/atividades");
                exit;
            endif;
        elseif (!empty($enrollment_end) && time() >= strtotime($enrollment_end)):
            $_SESSION['wc_ead_alert'] = ['red', "Oppsss, acesso expirado:", "Desculpe {$user_name}, mas sua assinatura ao Curso <strong>{$course_title}</strong> expirou dia <strong>" . date("d/m/y \à\s H\hi", strtotime($enrollment_end)) . "</strong>."];
            header("Location: campus.php?wc=cursos/atividades");
            exit;
        endif;

        //UPDATE ENROLLMENT ACCESS
        $UpdateEnrollmentAcess = ['enrollment_access' => date("Y-m-d H:i:s")];
        $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollmentAcess, "WHERE enrollment_id = :enrol", "enrol={$enrollment_id}");
    endif;

    //VALID MODULE
    $Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :cs AND module_id = :mod", "cs={$course_id}&mod={$module_id}");
    if (!$Read->getResult()):
        $_SESSION['wc_ead_alert'] = ['yellow', "Oppsss, erro ao acessar:", "Desculpe,{$user_name}, mas não foi possível identificar o módulo da aula {$class_title}."];
        header("Location: campus.php?wc=cursos/estudar&id={$course_id}");
        exit;
    else: extract($Read->getResult()[0]);

        //REQUEST MODULE SEARCH
        $Read->FullRead(
            "SELECT "
            . "COUNT(c.class_id) as class_pending, "
            . "m.module_title "
            . "FROM " . DB_EAD_CLASSES . " c "
            . "INNER JOIN " . DB_EAD_MODULES . " m ON m.module_id = c.module_id "
            . "WHERE m.course_id = :cs "
            . "AND m.module_order < :mo "
            . "AND m.module_required = 1 "
            . "AND c.class_id NOT IN(SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " s WHERE s.course_id = :cs AND s.user_id = :us AND s.student_class_check IS NOT NULL) "
            . "GROUP BY m.module_title "
            . "ORDER BY m.module_order ASC, c.class_order ASC "
            . "LIMIT 1", "cs={$course_id}&mo={$module_order}&us={$user_id}"
        );

        //REQUEST MODULE VALIDATION
        $ClassesPendent = ($Read->getResult() ? $Read->getResult()[0]['class_pending'] : 0);
        if ($ClassesPendent > 0):
            if ($_SESSION['userLogin']['user_level'] <= 5):
                $_SESSION['wc_ead_alert'] = ['red', "Módulo: <strong>{$Read->getResult()[0]['module_title']}</strong> obrigatório!", "Existem <strong>{$Read->getResult()[0]['class_pending']} aulas pendente(s)</strong>, {$user_name}. Marque todas como concluídas para continuar o Curso!"];
                header("Location: campus.php?wc=cursos/estudar&id={$course_id}");
                exit;
            endif;
        endif;

        //VALID FOR MODULE TIME TO ACCESS
        if (!empty($module_release_date)):
            $ReleaseUnlock = strtotime($module_release_date);
            $ModuleUnlocked = (($ReleaseUnlock <= time() && $ClassesPendent == 0) ? true : false);
            $ModuleUnlockDate = date("d/m/Y \à\s H\hi", strtotime($module_release_date));
        else:
            $ReleaseUnlock = strtotime($enrollment_start . "+{$module_release}days");
            $ModuleUnlocked = (($ReleaseUnlock <= time() && $ClassesPendent == 0) ? true : false);
            $ModuleUnlockDate = date("d/m/Y \à\s H\hi", strtotime($enrollment_start . "+{$module_release}days"));
        endif;

        if (!$ModuleUnlocked && $_SESSION['userLogin']['user_level'] < 6):
            $_SESSION['wc_ead_alert'] = ['red', "Módulo: <strong>{$module_title}</strong> bloqueado:", "{$user_name}, acesse este módulo a partir do dia <strong>" . date("d/m/y \à\s H\hi", strtotime($module_release_date . "+{$module_release}days")) . "</strong>."];
            header("Location: campus.php?wc=cursos/estudar&id={$course_id}");
            exit;
        endif;
    endif;

    //VALID TASK AND COUNT CHECK
    $DateThis = date('Y-m-d H:i:s');
    $Read->ExeRead(DB_EAD_STUDENT_CLASSES, "WHERE user_id = :user AND course_id = :course AND class_id = :class", "user={$user_id}&course={$course_id}&class={$class_id}");
    if ($Read->getResult()):
        extract($Read->getResult()[0]);

        if (empty($_SESSION['wc_student_class']) || $_SESSION['wc_student_class'] != $student_class_id):
            $UpdateStudenClass = ['student_class_play' => $DateThis, 'student_class_views' => $student_class_views + 1];
            $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class_id", "class_id={$student_class_id}");
            $student_class_views = $student_class_views + 1;
        endif;
    else:
        $CreateStudentClass = ['user_id' => $user_id, 'enrollment_id' => $enrollment_id, 'course_id' => $course_id, 'class_id' => $class_id, 'student_class_play' => $DateThis, 'student_class_views' => 1];
        $Create = new Create;
        $Create->ExeCreate(DB_EAD_STUDENT_CLASSES, $CreateStudentClass);

        $Read->ExeRead(DB_EAD_STUDENT_CLASSES, "WHERE student_class_id = :class_id", "class_id={$Create->getResult()}");
        extract($Read->getResult()[0]);
    endif;

    //STUDENT CONTROL NAVIGATION
    $_SESSION['wc_student_class'] = $student_class_id;
    $_SESSION['wc_student_task'] = time();

    //TASK VARS :: CLASS PREV
    $ClassPreview = "<a class='classnav icon-arrow-left class_next' href='campus.php?wc=cursos/estudar&id={$course_id}' title='Voltar ao Índice do Curso'>Voltar ao Índice do Curso</a>";
    $Read->FullRead("SELECT class_title, class_name, class_id FROM " . DB_EAD_CLASSES . " WHERE course_id = :course AND module_id = :module AND class_order < :order ORDER BY class_order DESC LIMIT 1", "course={$course_id}&module={$module_id}&order={$class_order}");
    if ($Read->getResult()):
        $ClassPreview = "<a class='classnav align_left' title='Voltar para {$Read->getResult()[0]['class_title']}' href='campus.php?wc=cursos/tarefa&id={$course_id}&class={$Read->getResult()[0]['class_id']}'><span><b>Anterior</b><span>" . Check::Chars($Read->getResult()[0]['class_title'], 32) . "</span></span><span class='icon icon_left icon-circle-left'></span></a>";
    else:
        $Read->FullRead("SELECT class_title, class_name, class_id FROM " . DB_EAD_CLASSES . " WHERE course_id = :course AND module_id = (SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :course AND module_order < :order ORDER BY module_order DESC LIMIT 1) ORDER BY class_order DESC LIMIT 1", "course={$course_id}&order={$module_order}");
        if ($Read->getResult()):
            $ClassPreview = "<a class='classnav align_left' title='Voltar para {$Read->getResult()[0]['class_title']}' href='campus.php?wc=cursos/tarefa&id={$course_id}&class={$Read->getResult()[0]['class_id']}'><span><b>Anterior</b><span>" . Check::Chars($Read->getResult()[0]['class_title'], 32) . "</span></span><span class='icon icon_left icon-circle-left'></span></a>";
        endif;
    endif;

    //TASK VARS :: CLASS NEXT
    $ClassNext = "<a style='flex-direction: row-reverse;' class='classnav icon-arrow-right class_next' href='campus.php?wc=cursos/estudar&id={$course_id}' title='Voltar ao Índice do Curso'>Voltar ao Índice do Curso &nbsp;&nbsp;</a>";
    $Read->FullRead("SELECT class_title, class_name, class_id FROM " . DB_EAD_CLASSES . " WHERE course_id = :course AND module_id = :module AND class_order > :order ORDER BY class_order ASC LIMIT 1", "course={$course_id}&module={$module_id}&order={$class_order}");
    if ($Read->getResult()):
        $ClassNext = "<a class='classnav align_right' title='Ir para {$Read->getResult()[0]['class_title']}' href='campus.php?wc=cursos/tarefa&id={$course_id}&class={$Read->getResult()[0]['class_id']}'><span><b>Próxima</b><span>" . Check::Chars($Read->getResult()[0]['class_title'], 32) . "</span></span><span class='icon icon_right icon-circle-right'></span></a>";
    else:
        $Read->FullRead("SELECT class_title, class_name, class_id FROM " . DB_EAD_CLASSES . " WHERE course_id = :course AND module_id = (SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :course AND module_order > :order ORDER BY module_order ASC LIMIT 1) ORDER BY class_order ASC LIMIT 1", "course={$course_id}&order={$module_order}");
        if ($Read->getResult()):
            $ClassNext = "<a class='classnav align_right' title='Ir para {$Read->getResult()[0]['class_title']}' href='campus.php?wc=cursos/tarefa&id={$course_id}&class={$Read->getResult()[0]['class_id']}'><span><b>Próxima</b><span>" . Check::Chars($Read->getResult()[0]['class_title'], 32) . "</span></span><span class='icon icon_right icon-circle-right'></span></a>";
        endif;
    endif;

    //COURSE VARS
    $course_cover = (file_exists("uploads/{$course_cover}") && !is_dir("uploads/{$course_cover}") ? "uploads/{$course_cover}" : 'admin/_img/no_image.jpg');

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

    //SUPPORT
    $Read->FullRead("SELECT support_status FROM " . DB_EAD_SUPPORT . " WHERE user_id = :user AND class_id = :class", "user={$user_id}&class={$class_id}");
    $TaskSupport = (!$Read->getResult() ? 'Não Abriu' : ($Read->getResult()[0]['support_status'] == 1 ? 'Em Aberto' : ($Read->getResult()[0]['support_status'] == 2 ? '<b>Respondida</b>' : 'Concluída')));
    ?>

    <!--MODAL FROM REVIEW-->
    <div class="wc_ead_course_task_modal jwc_ticket_review_content">
        <div class="wc_ead_course_task_modal_content">
            <span class="btn btn_red icon-cross wc_ead_course_task_modal_content_close icon-notext j_wc_ticket_close"></span>
            <header class="review">
                <span class="wc_ead_course_task_modal_content_icon icon-heart icon-notext"></span>
                <h1>Enviar Avaliação:</h1>
            </header>
            <div class="wc_ead_course_task_modal_content_desc">
                <form name="wc_ead_student_task_ticket_review" method="post" action="">
                    <input type="hidden" name="support_id"/>
                    <label>
                        <span>Qual sua nota <?= $_SESSION['userLogin']['user_name']; ?>?</span>
                        <select name="support_review" required>
                            <option value="5">&starf;&starf;&starf;&starf;&starf; - Excelente</option>
                            <option value="4">&starf;&starf;&starf;&starf;&star; - Bom</option>
                            <option value="3">&starf;&starf;&starf;&star;&star; - Regular</option>
                            <option value="2">&starf;&starf;&star;&star;&star; - Ruim</option>
                            <option value="1">&starf;&star;&star;&star;&star; - Péssimo</option>
                        </select>
                    </label>
                    <label>
                        <span class="icon-sad2">
                            Teve algum problema com esse suporte?<br>
                            <small style="color: #888;">Informe o campo abaixo <b>para enviar sua reclamação!</b></small>
                        </span>
                        <textarea name="support_comment" rows="5"></textarea>
                    </label>

                    <div class="form_actions">
                        <button class="btn btn_blue icon-heart">Enviar Avaliação</button>
                        <img class="jwc_load" style="margin-left: 10px;" alt="Enviando Avaliação!" title="Enviando Avaliação!" src="<?= BASE; ?>/campus/_img/load.svg"/>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <header class="dashboard_header">
        <div class="dashboard_header_title">
            <h1 class="icon-lab">Meus Cursos</h1>
            <p class="dashboard_header_breadcrumbs">
                &raquo; <a style="font-weight:normal" href="campus.php?wc=cursos/cursos" title="<?= SITE_NAME; ?>"><?= SITE_NAME; ?></a>
                <span class="crumb">/</span>
                <a title="<?= SITE_NAME; ?>" href="campus.php?wc=cursos/cursos">Cursos</a>
                <span class="crumb">/</span>
                <a title="<?= SITE_NAME; ?>" href="campus.php?wc=cursos/estudar&id=<?= $course_id; ?>"><?= $course_title; ?></a>
                <span class="crumb">/</span>
                <?= $class_title; ?>
            </p>
        </div>
    </header>

    <section class="dash_view" style="display: block;">
        <article class="dash_view_class" id="play">
            <header class="dash_view_class_header">
                <h2><b>Aula:</b> <?= $class_title; ?></h2><div class="dash_view_class_header_tools"><span class="icon-hour-glass"><?= $class_time; ?> min.</span><span class="icon-stats-dots"><?= str_pad($student_class_views, 4, 0, 0); ?></span><span class="icon-bubbles4"><?= $TaskSupport; ?></span><span class="icon-folder btn_small btn_opacity  radius class_folder" style="background: #033A47"><b>PASTA DO CURSO</b></span></div>
            </header>
            <style>.dash_view_class_media_tools .classfinish{background: #033A47; opacity: 0.9}.dash_view_class_media_tools .classfinish:hover{background: #033A47; opacity: 1}</style>
            <div class="dash_view_class_media">
                <div class="dash_view_class_media_tools">
                    <?= $ClassPreview; ?>
                    <?= $ClassNext; ?>
                    <?php
                    echo "<span class='jwc_ead_task classnav classcheck '> ";
                    if (empty($student_class_check)):
                        if ($student_class_free || !EAD_STUDENT_CLASS_PERCENT):
                            echo "<span class='a check free icon-checkmark2 jwc_ead_task_check'>Concluir Tarefa</span>";
                        else:
                            echo "<span class='a icon-clock2'>Tarefa Pendente</span>";
                        endif;
                    else:
                        echo "<span class='a active check free  icon-checkbox-checked jwc_ead_task_uncheck'>Aula Concluída</span>";
                    endif;
                    echo "</span>";
                    ?>
                </div>
                <div class="dash_view_class_media_content">
                    <div class="dash_view_class_media_video">
                        <div class="embed-container radius dash_view_class_media_player" data-class="<?= $class_id; ?>" data-progress="<?= $class_id; ?>">
                            <?php if (is_numeric($class_video)):
                                $CourseColor = explode("#", $course_color)[1];
                                ?>
                                <iframe class="course_class_play radius dash_view_class_media_player_vimeo" src="https://player.vimeo.com/video/<?= $class_video; ?>?title=0&amp;byline=0&amp;portrait=0&color=<?= $CourseColor; ?>" width="100%" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                            <?php else: ?>
                                <iframe class="course_class_play radius dash_view_class_media_player_vimeo" width="100%" height="360" src="https://www.youtube.com/embed/<?= $class_video; ?>?showinfo=0&amp;rel=0" frameborder="0" allowfullscreen></iframe>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($class_desc): ?>
                <div class='htmlchars'><?= $class_desc; ?></div>
            <?php endif; ?>

            <?php
            if (!$class_support):
                echo "<div class='dash_view_class_support'><div class='dash_view_class_support_empty'><p>Fórum fechado para a aula {$class_title}!</p></div></div>";
            else:
                ?>

                <section class="dash_view_class_support" id="support">
                    <?php
                    $Read->ExeRead(DB_EAD_SUPPORT, "WHERE class_id = :class AND (support_published = 1 OR user_id = {$_SESSION['userLogin']['user_id']} OR {$_SESSION['userLogin']['user_level']} > 5) ORDER BY support_status ASC, support_reply DESC, support_open DESC", "class={$class_id}");
                    if (!$Read->getResult()):
                        ?>
                        <div class="jwc_content"></div>
                        <section class="dash_view_class_support wc_ead_course_task_forum_none">
                            <div class="dash_view_class_support_empty">
                                <p>Ainda não existem dúvidas para a aula <?= $class_title; ?></p>
                            </div>
                        </section>
                    <?php
                    else:
                        foreach ($Read->getResult() as $Ticket):
                            $Read->LinkResult(DB_USERS, "user_id", $Ticket['user_id']);
                            $StudentSupport = $Read->getResult()[0];

                            $StudentSupport['user_name'] = ($StudentSupport['user_name'] ? $StudentSupport['user_name'] : 'Novo');
                            $StudentSupport['user_lastname'] = ($StudentSupport['user_lastname'] ? $StudentSupport['user_lastname'] : 'Aluno');
                            $StudentUserThumb = ($StudentSupport['user_thumb'] ? "uploads/{$StudentSupport['user_thumb']}" : 'campus/_img/no_avatar.jpg');
                            $StudentSupport['user_thumb'] = (file_exists($StudentUserThumb) && !is_dir($StudentUserThumb) ? "uploads/{$StudentSupport['user_thumb']}" : 'campus/_img/no_avatar.jpg');

                            $TicketStatus = ($Ticket['support_status'] == 1 ? "<span class='status bar_red radius'>Em Aberto</span>" : ($Ticket['support_status'] == 2 ? "<span class='status bar_blue radius'>Respondido</span>" : ($Ticket['support_status'] == 3 ? "<span class='status bar_green radius'>Concluído</span>" : '')));
                            ?>
                            <article class="dash_view_class_support_ticket " id="<?= $Ticket['support_id']; ?>">
                                <img class="thumb rounded" src="<?= BASE; ?>/tim.php?src=<?= $StudentUserThumb; ?>&w=70&h=70" title="<?= "{$StudentSupport['user_name']} {$StudentSupport['user_lastname']}"; ?>" alt="<?= "{$StudentSupport['user_name']} {$StudentSupport['user_lastname']}"; ?>">
                                <div class="htmlchars">
                                    <h4><?= "{$StudentSupport['user_name']} {$StudentSupport['user_lastname']}"; ?> <?= ($StudentSupport['user_level'] > 5 ? '<span class="dash_view_class_support_team radius icon-hipster2" style="background: #f48720;opacity: 0.8"">Suporte</span>' : '<b class="icon-notext icon-neutral"></b>'); ?></h4>
                                    <?= $Ticket['support_content']; ?>
                                    <div class="wc_ead_course_task_forum_response">
                                        <?php
                                        $StudentReply = null;
                                        $Read->ExeRead(DB_EAD_SUPPORT_REPLY, "WHERE support_id = :support ORDER BY response_open ASC", "support={$Ticket['support_id']}");
                                        if ($Read->getResult()):
                                            foreach ($Read->getResult() as $Reply):
                                                $Read->LinkResult(DB_USERS, "user_id", $Reply['user_id']);
                                                $StudentReply = $Read->getResult()[0];

                                                $StudentReply['user_name'] = ($StudentReply['user_name'] ? $StudentReply['user_name'] : 'Novo');
                                                $StudentReply['user_lastname'] = ($StudentReply['user_lastname'] ? $StudentReply['user_lastname'] : 'Aluno');
                                                $StudentReplyThumb = ($StudentReply['user_thumb'] ? "uploads/{$StudentReply['user_thumb']}" : 'campus/_img/no_avatar.jpg');
                                                $StudentReply['user_thumb'] = (file_exists($StudentReplyThumb) && !is_dir($StudentReplyThumb) ? "uploads/{$StudentReply['user_thumb']}" : 'campus/_img/no_avatar.jpg');
                                                ?>
                                                <div class="dash_view_class_support_ticket dash_view_class_support_ticket_reply">
                                                    <img class="thumb rounded" src="<?= BASE; ?>/tim.php?src=<?= $StudentReplyThumb; ?>&w=70&h=70" title="" alt="">
                                                    <div class="htmlchars">
                                                        <h4><?= "{$StudentReply['user_name']} {$StudentReply['user_lastname']}"; ?> <?= ($StudentReply['user_level'] > 5 ? '<span class="dash_view_class_support_team radius icon-hipster2" style="background: #f48720;opacity: 0.8">Suporte</span>' : '<b class="icon-notext icon-neutral"></b>'); ?></h4>
                                                        <?= $Reply['response_content']; ?>
                                                    </div>
                                                </div>
                                            <?php
                                            endforeach;
                                        else:
                                            echo "<div class='wc_ead_course_task_forum_ticket_line'></div>";
                                        endif;
                                        ?>
                                    </div>
                                </div>
                                <div class="wc_ead_course_task_forum_ticket_actions">
                                    <?php
                                    //SUPPORT :: REVIEW PRESENTATION
                                    $ReviewPositive = '<span class="icon-star-full icon-notext font_green"></span>';
                                    $ReviewNegative = '<span class="icon-star-empty icon-notext font_red"></span>';
                                    $ReviewTicket = ($Ticket['support_review'] ? str_repeat($ReviewPositive, $Ticket['support_review']) . str_repeat($ReviewNegative, 5 - $Ticket['support_review']) : '');
                                    echo "<span style='margin-right: 10px;' class='review jwc_review_target'>{$ReviewTicket}</span>";

                                    //SUPPORT :: NEW OR UPDATE REVIEW
                                    if ($Ticket['user_id'] == $_SESSION['userLogin']['user_id'] && !empty($StudentReply)):
                                        echo "<span style='margin-right: 10px' class='btn btn_blue icon-heart jwc_ticket_review' id='{$Ticket['support_id']}'>Avaliar Suporte</span>";
                                    endif;
                                    ?>
                                </div>
                            </article>
                        <?php
                        endforeach;
                        echo '<div class="jwc_content"></div>';
                    endif;
                    ?>
                </section>

                <form class="dash_view_class_support_form" method="POST" action="" name="wc_ead_student_task_ticket_add">
                    <textarea name="ticket_content" class="editor"></textarea>
                    <div class="dash_view_class_support_form_act">
                        <button class="btn_form btn_opacity radius" style="background: #033A47">Enviar Minha Dúvida</button>
                        <img class="jwc_load" style="margin-left: 10px; margin-top: -2px;width: 35px;display: none" alt="Enviando Dúvida!" title="Enviando Dúvida!" src="<?= BASE; ?>/campus/_img/load.svg"/>
                    </div>
                </form>

                <script src="<?= BASE; ?>/campus/_js/tinymce/tinymce.min.js"></script>
            <?php endif; ?>

            <div class="dash_view_class_folder">
                <div class="dash_view_class_folder_close icon-cross">Fechar Pasta</div>
                <p class="dash_view_class_folder_title">Pasta do curso <?= $course_title; ?></p>
                <p class="dash_view_class_folder_subtitle">Os materiais são liberados de acordo com seu andamento no curso. Se algum download estiver bloqueado é porque você ainda não acessou o módulo deste material.</p>
                <div class="dash_view_class_folder_content">
                    <?php
                    $Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :course AND module_id IN(SELECT module_id FROM " . DB_EAD_CLASSES . " WHERE class_material IS NOT NULL AND class_created <= NOW()) ORDER BY module_order ASC","course={$CourseId}");
                    if ($Read->getResult()):
                        foreach ($Read->getResult() AS $Modules):
                            $Read->FullRead("SELECT COUNT(c.class_id) as classes
                                                    FROM ws_ead_classes c
                                                    WHERE c.course_id = :cid
                                                    AND c.module_id = :mid",
                                "cid={$CourseId}&mid={$Modules['module_id']}");
                            $totalClasses = ($Read->getResult() ? $Read->getResult()[0]['classes'] : 0);

                            $Read->FullRead("SELECT COUNT(c.class_id) as classes
                                                    FROM ws_ead_classes c
                                                    INNER JOIN ws_ead_student_classes s ON s.class_id = c.class_id
                                                    WHERE c.course_id = :cid
                                                    AND c.module_id = :mid
                                                    AND s.user_id = :uid
                                                    AND s.student_class_check IS NOT NULL",
                                "cid={$CourseId}&mid={$Modules['module_id']}&uid={$user_id}");
                            $totalStudentClasses = ($Read->getResult() ? $Read->getResult()[0]['classes'] : 0);

                            ?>
                            <p class="dash_view_class_folder_content_m icon-folder-open"><?= $Modules['module_title']; ?></p>
                            <div class="dash_view_class_folder_content_f">
                                <?php
                                $Read->ExeRead(DB_EAD_CLASSES, "WHERE class_material IS NOT NULL AND module_id = :module", "module={$Modules['module_id']}");
                                if ($Read->getResult()):
                                    foreach ($Read->getResult() AS $Material):
                                        if ($Modules['module_required'] == 1 && ($totalClasses - $totalStudentClasses) > 0):
                                            $ModuloObg = "<span class='dash_view_class_folder_content_fi look'><span class='icon-lock icon-notext'></span>{$Material['class_title']}</span>";
                                        elseif ($Modules['module_required'] == 0 && (is_null($totalStudentClasses || empty($totalStudentClasses)))):
                                            $ModuloObg = "<span class='dash_view_class_folder_content_fi look'><span class='icon-lock icon-notext'></span>{$Material['class_title']}</span>";
                                        else:
                                            $ModuloObg = "<a title='' href='" . BASE . "/_ead/download.php?f={$Material['class_id']}' class='dash_view_class_folder_content_fi not_look'><span class='icon-file-zip icon-notext'></span> {$Material['class_title']}</a>";
                                        endif;
                                        echo $ModuloObg;
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
        </article>
    </section>
</div>
