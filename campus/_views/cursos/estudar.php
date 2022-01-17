<div class="wc_ead">
    <?php
    $Trigger = new Trigger;
    $visitor = false;

    //GET STUDENT
    if (!empty($_SESSION['userLogin'])):
        $Read->ExeRead(DB_USERS, "WHERE user_id = :user", "user={$User['user_id']}");
        if (!$Read->getResult()):
            header("Location: campus.php?wc=cursos/atividades");
            exit;
        else:
            $Student = $Read->getResult()[0];
            extract($Student);
            $UpdateStudentAcess = ['user_lastaccess' => date('Y-m-d H:i:s'), 'user_login' => time()];

            if (empty($Update)):
                $Update = new Update;
            endif;
            $Update->ExeUpdate(DB_USERS, $UpdateStudentAcess, "WHERE user_id = :user", "user={$user_id}");
        endif;
    else:
        header("Location: " . BASE . "/campus");
        exit;
    endif;

    //VALIDATE CHARGEBACK
    $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user AND order_status = :status",
        "user={$user_id}&status=chargeback");
    if ($Read->getResult()):
        header("Location: campus.php?wc=cursos/atividades");
        exit;
    endif;

    $CourseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    //GET COURSE
    if (!$CourseId):
        $_SESSION['wc_ead_alert'] = [
            "yellow",
            "Oppps {$user_name}, erro ao acessar:",
            "Não foi possível identificar o curso acessado :/"
        ];
        header("Location: campus.php?wc=cursos/atividades");
        exit;
    else:
        $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id = :id", "id={$CourseId}");
        if (!$Read->getResult()):
            $_SESSION['wc_ead_alert'] = [
                "yellow",
                "Oppps {$user_name}, erro ao acessar:",
                "Não foi possível identificar o curso acessado :/"
            ];
            header("Location: campus.php?wc=cursos/atividades");
            exit;
        else:
            extract($Read->getResult()[0]);

            //STUDENT CONTROL NAVIGATION
            $_SESSION['wc_student_course'] = $course_id;

            $TaskRedirect = (!empty($URL[3]) ? $URL[3] : null);
            if ($TaskRedirect):
                header("Location: " . BASE . "/campus/tarefa/{$TaskRedirect}#play");
            endif;

            $Read->LinkResult(DB_USERS, "user_id", $course_author, "user_name, user_lastname, user_thumb");
            $CourseTutor = $Read->getResult()[0];

            $Read->LinkResult(DB_EAD_COURSES_SEGMENTS, "segment_id", $course_segment);
            if ($Read->getResult()):
                $CourseSegment = $Read->getResult()[0];
            endif;

            $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)",
                "cs={$course_id}");
            $ClassCount = $Read->getResult()[0]['ClassCount'];
            $ClassTime = floor($Read->getResult()[0]['ClassTime'] / 60) . ":" . str_pad($Read->getResult()[0]['ClassTime'] % 60,
                    2, 0, 0);
        endif;
    endif;

    //VALID ENROLLMENT
    $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE course_id = :course AND user_id = :user",
        "course={$course_id}&user={$user_id}");
    if (!$Read->getResult()):
        $_SESSION['wc_ead_alert'] = ['red', "Ooops, você ainda não tem esse curso!", "Então, {$_SESSION['userLogin']['user_name']}, para estudar o <strong>Curso {$course_title}</strong>, antes você precisa adquiri-lo. Clique no botão <strong><a target='_blank' title='Saiba Mais' alt='Saiba Mais' href='". BASE . "/curso/{$course_name}'>SAIBA MAIS</a></strong> para conhecer uma oferta especial que preparamos para você!"];
        $visitor = true;
    else:
        extract($Read->getResult()[0]);

        //ENROLLMENT EXPIRED
        if (!empty($enrollment_bonus)):
            $Read->LinkResult(DB_EAD_ENROLLMENTS, "enrollment_id", $enrollment_bonus, 'enrollment_end');
            if (!empty($Read->getResult()) && !empty($Read->getResult()[0]['enrollment_end']) && time() >= strtotime($Read->getResult()[0]['enrollment_end'])):
                $_SESSION['wc_ead_alert'] = [
                    'red',
                    "Oppsss, Bônus bloqueado:",
                    "Desculpe, {$user_name}, mas para acessar um Bônus a assinatura ou Curso que liberou o mesmo não pode estar expirada(o)."
                ];
                header("Location: campus.php?wc=cursos/atividades");
                exit;
            endif;
        elseif (!empty($enrollment_end) && time() >= strtotime($enrollment_end)):
            $_SESSION['wc_ead_alert'] = [
                'red',
                "Oppsss, acesso expirado:",
                "Desculpe, {$user_name}, mas sua assinatura para o <strong>Curso {$course_title}</strong> expirou dia <strong>" . date("d/m/y \à\s H\hi", strtotime($enrollment_end)) . "</strong>."
            ];
            header("Location: campus.php?wc=cursos/atividades");
            exit;
        endif;

        //UPDATE ENROLLMENT ACCESS
        $UpdateEnrollmentAcess = ['enrollment_access' => date("Y-m-d H:i:s")];
        $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollmentAcess, "WHERE enrollment_id = :enrol",
            "enrol={$enrollment_id}");

        //STUDENT CLASS COUNT
//        $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL",
//            "user={$user_id}&course={$course_id}");
//        $ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

        //PROGRESS VIEW
        $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
        $ClassCount = $Read->getResult()[0]['ClassCount'];

        $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCountView FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course",
            "user={$user_id}&course={$course_id}");
        $ClassStudenCountView = $Read->getResult()[0]['ClassStudentCountView'];

        $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL",
            "user={$user_id}&course={$course_id}");
        $ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

        $CourseViewPercent = ($ClassStudenCountView && $ClassCount ? round(($ClassStudenCountView * 100) / $ClassCount) : "0");
        $CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : "0");
    endif;

    //COURSE VARS
    $course_cover = ($course_cover ? "uploads/{$course_cover}" : 'campus/_img/no_image.jpg');

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

    //MORE VARS
    if ($visitor == false):
        $CourseViewPercent = ($ClassStudenCountView && $ClassCount ? round(($ClassStudenCountView * 100) / $ClassCount) : "0");
        $CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : "0");
    else:
        $CourseViewPercent = 0;
        $CourseCompletedPercent = 0;
    endif;

    //GET PENDING CLASS
    $Table1 = DB_EAD_MODULES;
    $Table2 = DB_EAD_CLASSES;
    $Read->FullRead("SELECT {$Table2}.class_id, {$Table2}.class_name, {$Table2}.class_title FROM {$Table1}, {$Table2} WHERE {$Table1}.course_id = :course AND {$Table1}.module_id = {$Table2}.module_id AND {$Table2}.class_id NOT IN(SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL) ORDER BY {$Table1}.module_order ASC, {$Table2}.class_order ASC LIMIT 1",
        "user={$user_id}&course={$course_id}");
    $ClassPending = ($Read->getResult() ? $Read->getResult()[0] : null);
    ?>

    <header class="dashboard_header">
        <div class="dashboard_header_title">
            <h1 class="icon-lab">Meus Cursos</h1>
            <p class="dashboard_header_breadcrumbs">
                &raquo; <a style="font-weight:normal" href="campus.php?wc=cursos/cursos" title="<?= SITE_NAME; ?>"><?= SITE_NAME; ?></a>
                <span class="crumb">/</span>
                <a title="<?= SITE_NAME; ?>" href="campus.php?wc=cursos/cursos">Cursos</a>
                <span class="crumb">/</span>
                <?= $course_title; ?>
            </p>
        </div>
    </header>
    <section class="dash_view" style="display: block;">
        <div class="dash_view_course_header gradient_blue" style="background: <?= $course_color; ?> !important;">
            <div class="dash_view_course_header_thumb"
                 style="background-image: url(<?= BASE; ?>/tim.php?src=<?= $course_cover; ?>&w<?= IMAGE_W / 3; ?>&h=<?= IMAGE_H / 3; ?>)"></div>
            <div class="dash_view_course_header_info">
                <div class="dash_view_course_header_info_box">
                    <p class="icon-mic dash_view_course_header_info_box_title">Tutor do Curso</p>
                    <p class="subtitle">
                        <img class="dash_view_course_header_info_box_author_thumb rounded" src="<?= BASE; ?>/tim.php?src=uploads/<?= $CourseTutor['user_thumb']; ?>&w=70&h=70" alt="Por <?= "{$CourseTutor['user_name']} {$CourseTutor['user_lastname']}"; ?>" title="Por <?= "{$CourseTutor['user_name']} {$CourseTutor['user_lastname']}"; ?>">
                        <span class="name"><?= "{$CourseTutor['user_name']} {$CourseTutor['user_lastname']}"; ?></span>
                    </p>
                </div>
                <div class="dash_view_course_header_info_box">
                    <p class="icon-hour-glass dash_view_course_header_info_box_title">Duração do curso</p>
                    <p class="subtitle"><?= $ClassTime; ?> horas aula</p>
                </div>
                <div class="dash_view_course_header_info_box" <?= ($visitor == true ? 'style="opacity: 0.2"' : ''); ?>>
                    <p class="icon-stats-bars2 dash_view_course_header_info_box_title">Seu andamento</p>
                    <p class="subtitle">
                        <span class="progress radius overflow_hidden margin_bottom_min">
                            <span class="progress_bar" style="background-color: #3872D5; width: <?= $CourseViewPercent ?>%"><?= $CourseViewPercent; ?>%&nbsp;assistido</span>
                        </span>
                        <span class="progress radius overflow_hidden">
                            <span class="progress_bar" style="width: <?= $CourseCompletedPercent ?>%"><?= $CourseCompletedPercent ?>%&nbsp;concluído</span>
                        </span>
                    </p>
                </div>
            </div>
            <?php if ($visitor == false): ?>
                <div class="dash_view_course_header_cta">
                    <div class="dash_view_course_header_cta_next radius">
                        <span class="icon icon-mug icon-notext"></span>
                        <p class="info">Próxima aula pendente</p>
                        <p><?= !empty($ClassPending) ? "<a href='campus.php?wc=cursos/tarefa&id={$CourseId}&class={$ClassPending['class_id']}' title='{$ClassPending['class_title']}' class='class'>{$ClassPending['class_title']}</a>" : "Nenhuma tarefa pendente!</a>"; ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class='dash_view_course_header_cta'>
                    <?php
                    //GET COUNT STUDENTS
                    $Read->FullRead("SELECT count(enrollment_id) AS TotalEnrollment FROM " . DB_EAD_ENROLLMENTS . " WHERE course_id = :cs",
                        "cs={$course_id}");
                    $StudentCount = str_pad($Read->getResult()[0]['TotalEnrollment'], 1, 0, 0);
                    ?>
                    <div class='dash_view_course_header_cta_info'>
                        <span class='icon-heart'><?= $StudentCount; ?></span>
                        <p>Alunos estudando este curso</p>
                    </div>
                    <a href="<?= BASE; ?>/curso/<?= $course_name; ?>" target="_blank" alt="Saiba Mais" title="Saiba Mais" class='btn_cta btn_blue radius icon-stack'>SAIBA MAIS</a>
                </div>
            <?php endif; ?>
        </div>
</div>
<div class="box_wrap">
    <div class="box">
        <?php
        $Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :course ORDER BY module_order ASC",
            "course={$course_id}");
        $Modules = $Read->getResult();

        if (!$Modules):
            echo "<div class='trigger trigger_info trigger_none al_center icon-info m_top'>Ainda não existem módulos cadastrados para o curso {$course_title}!</div>";
        else:
            foreach ($Modules as $MOD):
                extract($MOD);

                $Read->FullRead("(SELECT "
                    . "class_id "
                    . "FROM ws_ead_classes class "
                    . "WHERE class.module_id IN (SELECT modu.module_id FROM ws_ead_modules modu WHERE modu.course_id = {$course_id} AND modu.module_order < {$module_order} AND modu.module_required = 1) "
                    . "AND class.class_id NOT IN (SELECT stclass.class_id FROM ws_ead_student_classes stclass WHERE stclass.student_class_check IS NOT NULL AND stclass.user_id = {$user_id} AND stclass.course_id = {$course_id} AND stclass.class_id IN (SELECT classmod.class_id FROM ws_ead_classes classmod WHERE classmod.module_id IN (SELECT modreq.module_id FROM ws_ead_modules modreq WHERE modreq.course_id = {$course_id} AND modreq.module_order < {$module_order} AND modreq.module_required = 1))))");

                $ClassesPendent = $Read->getRowCount();

                if (empty($module_release_date)):
                    if ($visitor == true):
                        $enrollment_start = date('Y-m-d');
                    endif;
                    $ReleaseUnlock = strtotime($enrollment_start . "+{$module_release}days");
                    $ModuleUnlocked = (($ReleaseUnlock <= time() && $ClassesPendent == 0) ? true : false);
                    $ModuleRelease = ($ReleaseUnlock <= time() ? '<span class="dash_view_course_module_header_tools unlocked bar_icon radius icon-unlocked">' . date('d/m/Y \à\s H\hi',
                            $ReleaseUnlock) . '</span>' : '<span class="dash_view_course_module_header_tools bar_icon radius icon-lock">' . date('d/m/Y \à\s H\hi',
                            $ReleaseUnlock) . '</span>');
                else:
                    $ReleaseUnlock = $module_release_date;
                    $ModuleUnlocked = ((strtotime($ReleaseUnlock) <= time() && $ClassesPendent == 0) ? true : false);
                    $ModuleRelease = (strtotime($ReleaseUnlock) <= time() ? '<span class="dash_view_course_module_header_tools unlocked bar_icon radius icon-unlocked">' . date('d/m/Y \à\s H\hi',
                            strtotime($ReleaseUnlock)) . '</span>' : '<span class="dash_view_course_module_header_tools bar_icon radius icon-calendar">' . date('d/m/Y',
                            strtotime($ReleaseUnlock)) . '</span>');
                endif;

                $barRequired = "";
                if ($module_required == 1):
                    $Read->ExeRead(DB_EAD_STUDENT_CLASSES,
                        "WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL AND class_id IN (SELECT class_id FROM " . DB_EAD_CLASSES . " WHERE module_id = :module)",
                        "user={$user_id}&course={$course_id}&module={$module_id}");
                    $ClassesChecked = $Read->getRowCount();

                    $Read->ExeRead(DB_EAD_CLASSES, "WHERE module_id = :module AND course_id = :course",
                        "module={$module_id}&course={$course_id}");
                    $ClassesModule = $Read->getRowCount();

                    if ($ClassesChecked < $ClassesModule):
                        $barRequired = "<span class='dash_view_course_module_header_tools required radius icon-pushpin'>Módulo obrigatório</span>";
                    endif;

                endif;
                ?>
                <section class="dash_view_course_module dash_item radius">
                    <input class="trigger-input" id="dash_item_check-<?= $module_id ?>" type="checkbox">
                    <div class="trigger-wrapper">
                        <label class="dash_view_course_module_header" for="dash_item_check-<?= $module_id ?>">
                            <h2>
                                <span class="title dash_item_check"><?= $module_title; ?></span><?= $ModuleRelease; ?> <?= $barRequired; ?>
                            </h2>
                        </label>
                        <div class="dash_content">
                            <div class="dash_view_course_module_class legend">
                                <p class="dash_view_course_module_class_row title"><span class="icon-checkbox-checked">Aula Concluída</span><span class="icon-checkbox-unchecked">Aula Pendente</span></p>
                                <p class="dash_view_course_module_class_row"><span class="icon-hour-glass">Tempo da aula</span></p>
                                <p class="dash_view_course_module_class_row"><span class="icon-eye">Último Acesso</span></p>
                                <p class="dash_view_course_module_class_row"><span class="icon-bubbles4">Suas dúvidas</span></p>
                            </div>
                            <?php
                            $Read->ExeRead(DB_EAD_CLASSES, "WHERE module_id = :module ORDER BY class_order ASC",
                                "module={$module_id}");
                            if (!$Read->getResult()):
                                echo "<div class='trigger trigger_info trigger_none al_center icon-info'>Ainda não existem aulas cadastradas para o módulo {$module_title}!</div>";
                            else:
                                foreach ($Read->getResult() as $CLASS):
                                    extract($CLASS);

                                    $Read->ExeRead(DB_EAD_STUDENT_CLASSES,
                                        "WHERE class_id = :class AND user_id = :user",
                                        "class={$class_id}&user={$user_id}");
                                    if ($Read->getResult()):
                                        extract($Read->getResult()[0]);
                                        $ClassViews = str_pad($student_class_views, 4, 0, 0);

                                        //MAKE ACESS DAYS
                                        $DayThis = new DateTime(date("Y-m-d H:i:s"));
                                        $DayPlay = new DateTime($student_class_play);
                                        $DaysDif = $DayThis->diff($DayPlay)->days;

                                        $ClassPlay = (!$student_class_play ? 'NUNCA' : ($DaysDif < 1 ? "Hoje" : ($DaysDif == 1 ? "Ontem" : str_pad($DaysDif,
                                                2, 0, 0) . " dias")));
                                        $ClassCheck = ($student_class_check ? date("d/m/y",
                                            strtotime($student_class_check)) : null);
                                    else:
                                        $ClassViews = "0000";
                                        $ClassPlay = "Nunca";
                                        $ClassCheck = null;
                                    endif;

                                    //SUPPORT
                                    $Read->FullRead("SELECT support_status FROM " . DB_EAD_SUPPORT . " WHERE user_id = :user AND class_id = :class",
                                        "user={$user_id}&class={$class_id}");
                                    $TaskSupport = (!$Read->getResult() ? 'Não Abriu' : ($Read->getResult()[0]['support_status'] == 1 ? 'Em Aberto' : ($Read->getResult()[0]['support_status'] == 2 ? '<b>Respondida</b>' : 'Concluída')));
                                    ?>
                                    <article
                                            class="dash_view_course_module_class <?= (!empty($ClassPending) && $ClassPending['class_id'] == $class_id ? 'pending' : ''); ?>">
                                        <a class="dash_view_course_module_class_link"
                                           title="Acessar Aula <?= $class_title; ?>"
                                           href="campus.php?wc=cursos/tarefa&id=<?= $CourseId; ?>&class=<?= $class_id; ?>">
                                            <h3 class="dash_view_course_module_class_row title <?= $ClassCheck ? "icon-checkbox-checked" : 'icon-checkbox-unchecked'; ?>"><?= $class_title; ?></h3>
                                            <p class="dash_view_course_module_class_row icon-hour-glass"><?= $class_time; ?>
                                                min.</p>
                                            <p class="dash_view_course_module_class_row icon-eye"><?= $ClassPlay; ?></p>
                                            <p class="dash_view_course_module_class_row"><span
                                                        class="icon-bubbles4"><?= $TaskSupport; ?></span></p>
                                        </a>
                                    </article>
                                <?php
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                </section>
            <?php
            endforeach;
        endif;
        ?>
    </div>
    </section>
</div>
<style>
    .dash_view_course_module_class.pending {
        background: <?= $course_color; ?>;
    }
</style>