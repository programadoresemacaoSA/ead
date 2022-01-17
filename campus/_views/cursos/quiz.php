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
        $Read->FullRead("SELECT course_title, course_name, course_color FROM " . DB_EAD_COURSES . " WHERE course_id = :course", "course={$CourseId}");
        if ($Read->getResult()):
            $course_name = $Read->getResult()[0]['course_name'];
            $course_title = $Read->getResult()[0]['course_title'];
            $course_color = $Read->getResult()[0]['course_color'];
            $course_id = $CourseId;
        else:
            $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$user_name}, erro ao acessar:", "Não foi possível identificar o Curso que você tentou acessar :("];
            header("Location: campus.php?wc=cursos/atividades");
            exit;
        endif;
    endif;

    $QuizId = filter_input(INPUT_GET, 'quiz', FILTER_VALIDATE_INT);

    //GET QUIZ ID BY SESSION
    if ($QuizId):
        $Read->FullRead("SELECT quiz_title, quiz_name, quiz_desc FROM " . DB_EAD_QUIZ . " WHERE quiz_id = :quiz", "quiz={$QuizId}");
        if ($Read->getResult()):
            $quiz_name = $Read->getResult()[0]['quiz_name'];
            $quiz_title = $Read->getResult()[0]['quiz_title'];
            $quiz_desc = $Read->getResult()[0]['quiz_desc'];
            $quiz_id = $QuizId;
        else:
            $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$user_name}, erro ao acessar:", "Não foi possível identificar o Curso que você tentou acessar :("];
            header("Location: campus.php?wc=cursos/atividades");
            exit;
        endif;
    endif;

    $QuizItemId = filter_input(INPUT_GET, 'quizitem', FILTER_VALIDATE_INT);

    //GET QUIZ ID BY SESSION
    if ($QuizItemId):
        $Read->FullRead("SELECT quiz_item_title FROM " . DB_EAD_QUIZ_ITEMS . " WHERE quiz_item_id = :quizitem", "quizitem={$QuizItemId}");
        if ($Read->getResult()):
            $quiz_item_title = $Read->getResult()[0]['quiz_item_title'];
            $quiz_item_id = $QuizItemId;
        else:
            $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$user_name}, erro ao acessar:", "Não foi possível identificar o Curso que você tentou acessar :("];
            header("Location: campus.php?wc=cursos/atividades");
            exit;
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
            <h1 class="icon-lab">Quiz</h1>
            <p class="dashboard_header_breadcrumbs">
                &raquo; <a style="font-weight:normal" href="campus.php?wc=cursos/cursos" title="<?= SITE_NAME; ?>"><?= SITE_NAME; ?></a>
                <span class="crumb">/</span>
                <a title="<?= SITE_NAME; ?>" href="campus.php?wc=cursos/cursos">Cursos</a>
                <span class="crumb">/</span>
                <a title="<?= SITE_NAME; ?>" href="campus.php?wc=cursos/estudar&id=<?= $course_id; ?>"><?= $course_title; ?></a>
                <span class="crumb">/</span>
                <a title="<?= SITE_NAME; ?>" href="campus.php?wc=cursos/estudar&id=<?= $course_id; ?>"><?= $quiz_title; ?></a>
                <span class="crumb">/</span>
                <?= $quiz_title; ?>
            </p>
        </div>
    </header>

    <section class="dash_view">
        <div class="quiz-content">
            <h1 class="quiz-title"><?= $quiz_title; ?></h1>
            <div class="quiz-description"><?= $quiz_desc; ?></div>

            <form id="quiz-form" class="quiz-form">
                <ul class="quiz-questions">
                    <?php
                    $Read->ExeRead(DB_EAD_QUIZ_ITEMS, "WHERE quiz_id = :quiz ORDER BY quiz_item_order ASC","quiz={$QuizId}");
                    $Quiz = $Read->getResult();

                    if (!$Quiz):
                        echo "<div class='trigger trigger_info trigger_none al_center icon-info m_top'>Ainda não existem perguntas cadastradas para este Quiz!</div>";
                    else:
                        foreach ($Quiz as $MOD):
                            extract($MOD);
                            ?><li class="question">
                            <div class="question-header">
                                <h3 class="question-title">
                                    <p><?= $quiz_item_title; ?></p>
                                </h3>
                            </div>
                            <div class="component-group c-inputs-stacked paper-element">
                                <ul class="question-options">
                                    <?php
                                    $Read->ExeRead(DB_EAD_QUIZ_QUESTION, "WHERE quiz_item_id = :quizitem ORDER BY question_order ASC","quizitem={$quiz_item_id}");
                                    if (!$Read->getResult()):
                                        echo "<div class='trigger trigger_info trigger_none al_center icon-info'>Ainda não existem respostas cadastradas!</div>";
                                    else:
                                        foreach ($Read->getResult() as $Question):
                                            extract($Question);?><li class="form-group paper-element">
                                            <label class="label-right c-input c-radio question-label">
                                                <input type="radio" name="question-0" value="2235306"><span class="c-indicator"></span>
                                                <p><?= $question_title; ?></p>
                                            </label>
                                            </li><?php
                                        endforeach;
                                    endif;
                                    ?>
                                </ul>
                            </div>
                            </li><?php
                        endforeach;
                    endif;
                    ?>
                </ul>
                <?php
                $Read->ExeRead(DB_EAD_QUIZ_ITEMS, "WHERE quiz_id = :quiz ORDER BY quiz_item_order ASC","quiz={$QuizId}");
                $Quiz = $Read->getResult();
                if ($Quiz):?>
                    <div class="btn_question">
                        <button class="btn_medium btn_outline transition">
                            <span>Terminei! Ver resultado</span>
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </section>
</div>

<style>
    .quiz-content{
        background:#fff;
        counter-reset:decimal;
        overflow:hidden;
        padding:30px;
        margin: 30px !important;
    }

    @media (min-width: 768px){
        .quiz-content{
            padding:48px;
            margin-top:80px
        }
    }

    .quiz-questions .question-label{
        padding:20px 35px !important;
        margin:0px
    }

    .quiz-questions .question-label .c-indicator{
        top:32px
    }

    .quiz-questions .question-label .c-indicator+p{
        display:inline
    }

    .quiz-form .question:first-child .question-title{
        margin-top:0
    }

    .quiz-title{
        margin-bottom:24px
    }

    .quiz-description{
        font-size:16px;
        line-height:1.63;
        margin-bottom:24px;
        border-bottom:1px solid #e5e5e5;
        padding-bottom:24px
    }

    .quiz-description p{
        margin-bottom:0px
    }

    .quiz-questions{
        list-style:none;
        padding:0
    }


    .question .question-title{
        margin:62px 0 10px;
        padding:0 0 10px;
        font-size:20px;
        line-height:28px
    }

    .question .question-title:before{
        content:counter(decimal) ".";
        counter-increment:decimal;
        color:<?= $course_color; ?>;
        /*color:#e84601;*/
        margin-right:20px
    }

    .question .question-title p:first-child{
        display:inline
    }

    .question .question-options{
        list-style:none;
        padding:0;
        margin:0
    }

    .component-group{;
        margin-top:10px;
        width:100%
    }

    .form-group.paper-element label .c-indicator {
        border: 2px solid #9b9b9b;
        background: #FFF;
        height: 22px;
        width: 22px;
        display: block;
        position: absolute;
        top: 50%;
        left: 0;
        margin-top: -11px;
        border-radius: 50%;
    }

    .form-group.paper-element label {
        font-size: 16px;
        padding-right: 30px;
        width: 100%;
        cursor: pointer;
    }

    .quiz_questions .question-label .c-indicator+p {
        display: inline;
    }

    .form-group.paper-element{
        position:relative;
        margin-bottom:0px;
        border-radius:0px;
        margin-top:40px;
    }

    .form-group.paper-element label input ~ .c-indicator:after{
        position:relative;
        top:3px;
        left:3px
    }

    .form-group.paper-element label input:checked ~ .c-indicator{
        border-color:<?= $course_color; ?> !important
    }

    .form-group.paper-element label input:checked ~ .c-indicator:after{
        top:0px;
        left:0px;
        height:12px;
        width:12px;
        margin:3px;
        background:<?= $course_color; ?>
    }

    .form-group.paper-element label input{
        position:absolute;
        z-index:-1;
        opacity:0
    }

    .form-group.paper-element label .c-indicator:after{
        -webkit-transition:all .3s ease-in-out;
        transition:all .3s ease-in-out;
        content:'';
        height:0px;
        width:0px;
        margin:6px;
        display:block;
        border-radius:50%
    }

    .btn_question{
        margin-top: 50px;
    }

    .btn_question .btn_outline{
        display: inline-block;
        font-weight: normal;
        line-height: 1.25;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        border: 1px solid #2ebd59;
        text-shadow: none;
        font-size: 1rem;
        border-radius: 0.25rem;
        padding: 18px 50px;
        color: #2ebd59;
        background-image: none;
        background-color: transparent;
    }

    .btn_question .btn_outline:hover{
        background: #2ebd59;
        color: #fff;
    }

</style>