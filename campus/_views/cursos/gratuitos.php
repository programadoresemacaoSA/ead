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
            <h1 class="icon-gift">Cursos Gratuitos</h1>
            <p class="dashboard_header_breadcrumbs">
                &raquo; <a style="font-weight:normal" href="campus.php?wc=cursos/cursos" title="<?= SITE_NAME; ?>"><?= SITE_NAME; ?></a>
                <span class="crumb">/</span>
                <a style="font-weight:normal" title="Meus Cursos" href="campus.php?wc=cursos/cursos">Meus Cursos</a>
                <span class="crumb">/</span>
                <a title="Cursos Gratuitos" href="campus.php?wc=cursos/destaques">Cursos Gratuitos</a>
            </p>
        </div>
    </header>
    <div class="box_wrap">
        <?php
        $getPage = filter_input(INPUT_GET, 'campus', FILTER_VALIDATE_INT);
        $Page = ($getPage ? $getPage : 1);
        $Pager = new Pager(BASE .'/campus/campus.php?wc=cursos/gratuitos&campus=', '<<', '>>', 3);
        $Pager->ExePager($Page, 8);
        $Read->ExeRead(DB_EAD_COURSES, "WHERE course_status = 1 AND course_created <= NOW() AND course_free = 1 ORDER BY course_created DESC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");

        if (!$Read->getResult()):
            $Pager->ReturnPage();
            echo Erro("<div class='al_center'>Desculpe, mais ainda não existem Cursos Gratuitos cadastrados. Favor volte mais tarde :)</div>", E_USER_NOTICE);
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
                $course_cover = ($course_cover ? "uploads/{$course_cover}" : 'campus/_img/no_image.jpg');

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

                require 'inc/free_courses.php';
            endforeach;
        endif;
        $Pager->ExePaginator(DB_EAD_COURSES, "WHERE course_status = 1 AND course_created <= NOW()");
        ?>
        <?php echo $Pager->getPaginator(); ?>
    </div>
</div>