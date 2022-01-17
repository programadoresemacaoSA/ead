<?php
ob_start();
session_start();
require '../../_app/Config.inc.php';

$Read = new Read;
$Update = new Update;
$Create = new Create;
$Delete = new Delete;
$Trigger = new Trigger;

usleep(50000);

//DEFINE O CALLBACK E RECUPERA O PostData
$jSON = null;
$CallBack = 'Campus';
$PostData = filter_input_array(INPUT_POST, FILTER_DEFAULT);


//VALIDA AÇÃO
if ($PostData && $PostData['callback']):
    //PREPARA OS DADOS
    $Case = $PostData['callback'];
    unset($PostData['callback'], $PostData['callback']);

    //ELIMINA CÓDIGOS
    $PostData = array_map('trim', $PostData);
    $PostData = array_map('strip_tags', $PostData);

    //SELECIONA AÇÃO
    switch ($Case):

        //STUDENT ACTIONS :: ACCOUNT UPDATE
        case 'wc_ead_student_account_update':
            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/index.php";
            endif;

            $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$_SESSION['userLogin']['user_id']}");
            if (!$Read->getResult()):
                unset($_SESSION['userLogin']);
                $jSON['redirect'] = BASE . "/campus/index.php";
            else:
                $User = $Read->getResult()[0];
                $UserUpdate = $PostData;

                $UserUpdate['user_name'] = (!empty($UserUpdate['user_name']) ? $UserUpdate['user_name'] : $User['user_name']);
                $UserUpdate['user_lastname'] = (!empty($UserUpdate['user_lastname']) ? $UserUpdate['user_lastname'] : $User['user_lastname']);
                $UserUpdate['user_genre'] = (!empty($UserUpdate['user_genre']) ? $UserUpdate['user_genre'] : $User['user_genre']);

                $File = (!empty($_FILES['user_thumb']) ? $_FILES['user_thumb'] : null);
                if ($File):
                    if (file_exists("../uploads/{$User['user_thumb']}") && !is_dir("../uploads/{$User['user_thumb']}")):
                        unlink("../uploads/{$User['user_thumb']}");
                    endif;
                    $Upload = new Upload("../uploads/");
                    $Upload->Image($File, $User['user_id'] . "-" . Check::Name($UserUpdate['user_name'] . $UserUpdate['user_lastname']), AVATAR_W);
                    $UserUpdate['user_thumb'] = $Upload->getResult();
                else:
                    unset($UserUpdate['user_thumb']);
                endif;

                if (!empty($UserUpdate['user_document']) && !Check::CPF($UserUpdate['user_document'])):
                    $jSON['trigger'] = $Trigger->notify("Opppsss {$UserUpdate['user_name']}. O número de CPF informado não é válido.", 'red', 'warning', 3000);
                    break;
                endif;

                if (!empty($PostData['user_email'])):
                    unset($PostData['user_email']);
                endif;

                $Update->ExeUpdate(DB_USERS, $UserUpdate, "WHERE user_id = :id", "id={$User['user_id']}");
                $jSON['trigger'] = $Trigger->notify("Suas alterações foram salvas com sucesso ;-)", 'green', ' happy', 3000);
            endif;
            break;

        //STUDENT ACTIONS :: ADDRESS UPDATE
        case 'wc_ead_student_address_update':
            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/index.php";
            endif;

            $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$_SESSION['userLogin']['user_id']}");
            if (!$Read->getResult()):
                unset($_SESSION['userLogin']);
                $jSON['redirect'] = BASE . "/campus/index.php";
            else:
                $User = $Read->getResult()[0];

                $Update->ExeUpdate(DB_USERS_ADDR, $PostData, "WHERE user_id = :id", "id={$User['user_id']}");
                $jSON['trigger'] = $Trigger->notify("Suas alterações foram salvas com sucesso ;-)", 'green', ' happy', 3000);
            endif;
            break;

        //STUDENT ACTIONS :: ADDRESS UPDATE
        case 'wc_ead_student_password_update':
            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/index.php";
            endif;

            $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$_SESSION['userLogin']['user_id']}");
            if (!$Read->getResult()):
                unset($_SESSION['userLogin']);
                $jSON['redirect'] = BASE . "/campus/index.php";
            elseif (strlen($PostData['user_password']) < 5):
                $jSON['trigger'] = $Trigger->notify("Oppsss {$Read->getResult()[0]['user_name']}. Sua nova senha deve ter no mínimo 5 caracteres!", 'red', 'warning', 3000);
            elseif ($PostData['user_password'] != $PostData['user_password_re']):
                $jSON['trigger'] = $Trigger->notify("Oppsss {$Read->getResult()[0]['user_name']}. Você informou 2 novas senhas diferentes!", 'red', 'warning', 3000);
            else:
                $User = $Read->getResult()[0];
                $UpdatePassword = ['user_password' => hash("sha512", $PostData['user_password'])];

                $Update->ExeUpdate(DB_USERS, $UpdatePassword, "WHERE user_id = :id", "id={$User['user_id']}");
                $jSON['trigger'] = $Trigger->notify("Suas alterações foram salvas com sucesso ;-)", 'green', ' happy', 3000);
            endif;
            break;

        //STUDENT ACTIONS :: TASK MANAGER
        case 'wc_ead_student_task_manager':
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);
            $start_time = (!empty($_SESSION['wc_student_task']) ? $_SESSION['wc_student_task'] : null);
            $user_id = (!empty($_SESSION['userLogin']['user_id']) ? $_SESSION['userLogin']['user_id'] : null);
            $end_time = 0;

            if ($studend_class_id && $start_time && $user_id):
                $Read->FullRead("SELECT class_time FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND student_class_id = :class AND student_class_free IS NULL)", "user={$user_id}&class={$studend_class_id}");

                if ($Read->getResult()):

                    //SAVE CLASS TIME
                    $ClassTotalTime = $Read->getResult()[0]['class_time'];

                    if (EAD_STUDENT_CLASS_PERCENT):
                        $end_time = ceil(($start_time + ($Read->getResult()[0]['class_time'] * 60) * (EAD_STUDENT_CLASS_PERCENT / 100)));
                        $ClassTotalTime = floor($ClassTotalTime * (EAD_STUDENT_CLASS_PERCENT / 100));
                    endif;

                    if (EAD_STUDENT_CLASS_AUTO_CHECK):
                        if (time() >= $end_time):
                            $UpdateStudenClass = ['student_class_free' => 1, "student_class_check" => date("Y-m-d H:i:s")];
                            $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");
                            $jSON['check'] = "<span class='a active icon-checkmark jwc_ead_task_uncheck'>" . date("d/m/Y H\hi") . "</span>";
                            $jSON['stop'] = true;
                            break;
                        endif;
                    endif;

                    if (time() > $end_time && !EAD_STUDENT_CLASS_AUTO_CHECK):
                        $UpdateStudenClass = ['student_class_free' => 1];
                        $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");
                        $jSON['aprove'] = "<span class='a check icon-checkmark2 jwc_ead_task_check'>Concluir Tarefa</span>";
                        $jSON['stop'] = true;
                        break;
                    else:
                        $Read->FullRead("SELECT student_class_seconds FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :st_class", "st_class={$studend_class_id}");
                        if ($Read->getResult()):
                            $student_class_seconds = ['student_class_seconds' => $Read->getResult()[0]['student_class_seconds'] + 10];
                            $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $student_class_seconds, "WHERE student_class_id = :st_class", "st_class={$studend_class_id}");

                            //RECOVER USER LAST VIEW TIME
                            $ClassFreeTime = floor($Read->getResult()[0]['student_class_seconds'] / 60);
                            if ($ClassFreeTime >= $ClassTotalTime):
                                if (EAD_STUDENT_CLASS_AUTO_CHECK):
                                    $UpdateStudenClass = ['student_class_free' => 1, "student_class_check" => date("Y-m-d H:i:s")];
                                    $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");
                                    $jSON['check'] = "<span class='a active icon-checkmark jwc_ead_task_uncheck'>" . date("d/m/Y H\hi") . "</span>";
                                    $jSON['stop'] = true;
                                    break;
                                else:
                                    $UpdateStudenClass = ['student_class_free' => 1];
                                    $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");
                                    $jSON['aprove'] = "<span class='a check icon-checkmark2 jwc_ead_task_check'>Concluir Tarefa</span>";
                                    $jSON['stop'] = true;
                                    break;
                                endif;
                            else:
                                $jSON['waiting'] = true;
                            endif;
                        else:
                            $jSON['waiting'] = true;
                        endif;
                    endif;
                else:
                    $jSON['stop'] = true;
                    break;
                endif;
            else:
                $jSON['stop'] = true;
                break;
            endif;
            break;

        //PLAY ACTIONS :: AUTO CHECK
        case 'wc_student_task_manager_check':
            $studend_class_id = $PostData['classId'];
            $user_id = $PostData['userId'];

            $Read->FullRead("SELECT class_title FROM " . DB_CLASS . " WHERE class_id = :class", "class={$studend_class_id}");
            if ($Read->getResult()):
                $Read->ExeRead(DB_CLASS_CHECK, "WHERE user_id = :uid AND class_id = :cid", "uid={$user_id}&cid={$studend_class_id}");
                if ($Read->getResult()):
                    $UpdateStudenClass = ["class_check" => (!empty($Read->getResult()[0]['class_check']) ? null : date("Y-m-d H:i:s"))];
                    $Update->ExeUpdate(DB_CLASS_CHECK, $UpdateStudenClass, "WHERE user_id = :uid AND class_id = :cid","uid={$user_id}&cid={$studend_class_id}");
                    $jSON['trigger'] = $Trigger->notify("Aula concluída, {$_SESSION['userLogin']['user_name']}!", 'green', 'checkmark', 3000);
                else:
                    $CreateStudenClass = ["class_check" => date("Y-m-d H:i:s"), "class_id" => $studend_class_id, "user_id" => $user_id];
                    $Create->ExeCreate(DB_CLASS_CHECK, $CreateStudenClass);
                    $jSON['trigger'] = $Trigger->notify("Aula concluída, {$_SESSION['userLogin']['user_name']}!", 'green', 'checkmark', 3000);                endif;
            else:
                $jSON['trigger'] = $Trigger->notify("Aula não identificada {$_SESSION['userLogin']['user_name']}. Atualize a página para que a Aula seja identificada!", 'yellow', 'spinner3', 3000);
            endif;
            break;

        //STUDENT ACTIONS :: TASK CHECK
        case 'wc_ead_student_task_manager_check':
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);
            $user_id = (!empty($_SESSION['userLogin']['user_id']) ? $_SESSION['userLogin']['user_id'] : null);

            if (!$user_id || !$studend_class_id):
                $jSON['trigger'] = $Trigger->notify("<p>Erro ao concluir tarefa:</p> <p>Desculpe, não foi possível identificar seu login ou a tarefa acessada. As aulas devem ser feitas uma de cada vez.</p><p><b>EVITE ESSE ERRO:</b> Para evitar esse erro procure não abrir mais de uma aula em abas ao mesmo tempo.</p><p><a href='' class='btn btn_red icon-loop2' title=''>Atualizar Página Agora!</a></p><p>Por favor, atualize sua página e tente concluir novamente!", 'red', 'warning', 5000);
            else:
                $Read->FullRead("SELECT class_title FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                if ($Read->getResult()):
                    $UpdateStudenClass = ['student_class_free' => 1, "student_class_check" => date("Y-m-d H:i:s")];
                    $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");

                    $jSON['check'] = "<span class='a active icon-checkmark jwc_ead_task_uncheck'>" . date("d/m/Y H\hi") . "</span>";
                    $jSON['trigger'] = $Trigger->notify("Tarefa concluída {$_SESSION['userLogin']['user_name']}. Parabéns você concluiu a tarefa {$Read->getResult()[0]['class_title']}!", 'green', 'checkmark', 3000);
                else:
                    $jSON['trigger'] = $Trigger->notify("Tarefa não identificada {$_SESSION['userLogin']['user_name']}. Atualize a página para que a tarefa seja identificada!", 'yellow', 'spinner3', 3000);
                endif;
            endif;
            break;

        //STUDENT ACTIONS :: TASK UNCHECK
        case 'wc_ead_student_task_manager_uncheck':
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);
            $user_id = (!empty($_SESSION['userLogin']['user_id']) ? $_SESSION['userLogin']['user_id'] : null);

            if (!$user_id || !$studend_class_id):
                $jSON['trigger'] = $Trigger->modal("Erro ao desmarcar tarefa:", "<p>Desculpe, não foi possível identificar seu login ou a tarefa acessada. As aulas devem ser feitas uma de cada vez.</p><p><b>EVITE ESSE ERRO:</b> Para evitar esse erro procure não abrir mais de uma aula em abas ao mesmo tempo.</p><p><a href='' class='btn btn_red icon-loop2' title=''>Atualizar Página Agora!</a></p><p>Por favor, atualize sua página e tente desmarcar novamente!</p>", "red", "warning");
            else:
                $Read->FullRead("SELECT class_title FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                if ($Read->getResult()):
                    $UpdateStudenClass = ['student_class_free' => 1, "student_class_check" => null];
                    $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");

                    $jSON['check'] = "<span class='a check icon-checkmark2 jwc_ead_task_check'>Concluir Tarefa</span>";
                    $jSON['trigger'] = $Trigger->notify("Volte aqui depois {$_SESSION['userLogin']['user_name']}. Você desmarcou a tarefa {$Read->getResult()[0]['class_title']}!", 'blue', 'wink', 3000);
                else:
                    $jSON['trigger'] = $Trigger->notify("Tarefa não identificada {$_SESSION['userLogin']['user_name']}. Atualize a página para que a tarefa seja identificada!", 'yellow', 'shocked', 3000);
                endif;
            endif;
            break;

        //STUDENT ACTION :: SEND SUPPORT
        case 'wc_ead_student_task_ticket_add':
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);

            //ALL CHECK
            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/index.php";
                break;
            elseif (!$studend_class_id):
                $jSON['trigger'] = $Trigger->notify("Aula não identificada: Desculpe, {$_SESSION['userLogin']['user_name']}, para enviar sua dúvida antes atualize a página :/", 'red', 'warning', 3000);
                break;
            elseif (empty($PostData['ticket_content'])):
                $jSON['trigger'] = $Trigger->notify("Dúvida enviada com sucesso, {$_SESSION['userLogin']['user_name']}, agora basta aguardar uma repostas :)", 'green', 'bubbles4', 3000);
                break;
            endif;

            //VALIDATE TICKET
            $PregContent = preg_replace("/<p[^>]*>[\s|&nbsp;\ ]*<\/p>/", '', $PostData['ticket_content']);
            $TicketContent = str_replace("\r\n", "", $PregContent);

            if (empty($TicketContent)):
                $jSON['trigger'] = $Trigger->notify("Tarefa não identificada {$_SESSION['userLogin']['user_name']}. Atualize a página e tente enviar sua dúvida novamente!", 'yellow', 'warning', 3000);
            else:
                $Read->FullRead("SELECT course_id, class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class", "class={$studend_class_id}");
                if (!$Read->getResult()):
                    $jSON['trigger'] = $Trigger->notify("Aula não identificada: Desculpe, {$_SESSION['userLogin']['user_name']}, para enviar sua dúvida antes atualize a página :/", 'red', 'warning', 3000);
                else:
                    $SupportClass = $Read->getResult()[0];

                    //CREATE SUPPORT OR RESPONSE
                    $Read->FullRead("SELECT user_id, support_id FROM " . DB_EAD_SUPPORT . " WHERE user_id = :user AND class_id = :class", "user={$_SESSION['userLogin']['user_id']}&class={$SupportClass['class_id']}");
                    if (!$Read->getResult()):
                        $CreateTicket = ['user_id' => $_SESSION['userLogin']['user_id'], 'enrollment_id' => $_SESSION['wc_student_enrollment_id'], 'class_id' => $SupportClass['class_id'], 'support_content' => $TicketContent, 'support_status' => 1, 'support_open' => date("Y-m-d H:i:s")];
                        $Create->ExeCreate(DB_EAD_SUPPORT, $CreateTicket);

                        $UserThumb = "../../uploads/{$_SESSION['userLogin']['user_thumb']}";
                        $SuportUserThumb = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$_SESSION['userLogin']['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                        $jSON['trigger'] = $Trigger->notify("Dúvida enviada com sucesso: Pronto {$_SESSION['userLogin']['user_name']}, agora basta aguardar uma reposta :)", 'green', 'bubbles4', 3000);
                        $jSON['ead_support'] = true;

                        //ALERT TUTOR FOR NEW TICKET
                        if (EAD_TASK_SUPPORT_EMAIL && Check::Email(EAD_TASK_SUPPORT_EMAIL)):
                            $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                            $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');
                            $MailCourseId = ($Read->getResult() ? $Read->getResult()[0]['course_id'] : 'N/A');

                            $Read->FullRead("SELECT class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                            $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');
                            $MailClassName = ($Read->getResult() ? $Read->getResult()[0]['class_name'] : 'N/A');
                            $MailClassId = ($Read->getResult() ? $Read->getResult()[0]['class_id'] : 'N/A');

                            require '../wc_ead.email.php';
                            $MailBody = "
                                <p style='font-size: 1.4em; text-align: center; margin: 30px auto 50px auto'>Nova dúvida para responder!</p>
                                <p>{$_SESSION['userLogin']['user_name']} acabou de enviar uma dúvida na aula <b>{$MailClassTitle}</b> do curso <b>{$MailCourseTitle}.</b></p>
                                <p><b>IMPORTANTE:</b> Quanto antes responder as dúvidas, melhor é a avaliação de seus alunos para o suporte prestado!</p>
                                <p style='font-size: 1.2em;'><b><a href='" . BASE . "/campus/campus.php?wc=cursos/tarefa&id={$MailCourseId}&class={$MailClassId}#{$Create->getResult()}' title='Responder dúvida agora!'>RESPONDER AGORA!</a></b></p>
//                                <p style='font-size: 1.2em;'><b><a href='" . BASE . "/campus/tarefa/{$MailClassName}#{$Create->getResult()}' title='Responder dúvida agora!'>RESPONDER AGORA!</a></b></p>
                                <p>Você também pode responder todas as dúvidas de forma otimizada no menu suporte da plataforma!</p>
                                <p>...</p>
                                <p><b>Dúvida de {$_SESSION['userLogin']['user_name']}:</b> {$TicketContent}</p>
                                <p>...</p>
                                <p>Este e-mail automático não deve ser respondido!</p>
                            ";

                            $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                            $Email = new Email;
                            $Email->EnviarMontando("#" . str_pad($Create->getResult(), 4, 0, 0) . " - Nova dúvida em aberto!", $MailContent, SITE_NAME, MAIL_USER, MAIL_SENDER, EAD_TASK_SUPPORT_EMAIL);
                        endif;
                    else:
                        $SupportResponseId = $Read->getResult()[0]['support_id'];
                        $CreateResponse = ['user_id' => $_SESSION['userLogin']['user_id'], 'enrollment_id' => $_SESSION['wc_student_enrollment_id'], 'support_id' => $SupportResponseId, 'response_content' => $TicketContent, 'response_open' => date("Y-m-d H:i:s")];
                        $Create->ExeCreate(DB_EAD_SUPPORT_REPLY, $CreateResponse);
                        $UpdateTicket = ['support_status' => 1, 'support_reply' => date("Y-m-d H:i:s"), 'support_close' => null, 'support_review' => null, 'support_comment' => null];
                        $Update->ExeUpdate(DB_EAD_SUPPORT, $UpdateTicket, "WHERE support_id = :support", "support={$Read->getResult()[0]['support_id']}");

                        $UserThumb = "../../uploads/{$_SESSION['userLogin']['user_thumb']}";
                        $SuportUserThumb = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$_SESSION['userLogin']['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                        $jSON['trigger'] = $Trigger->notify("Resposta enviada com sucesso: {$_SESSION['userLogin']['user_name']}, agora basta aguardar outra resposta :)", 'green', ' happy', 3000);
                        $jSON['ead_support'] = true;
                        $jSON['ead_support_id'] = $Read->getResult()[0]['support_id'];

                        $jSON['ead_support_content'] = "<article class='dash_view_class_support_ticket_reply wc_ead_course_task_forum_ticket' id='{$Create->getResult()}'>
                            <img style='vertical-align: top;' class='thumb rounded' src='" . BASE . "/tim.php?src={$SuportUserThumb}&w=70&h=70' title='{$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}' alt='{$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}'>
                            <div class='htmlchars' style='display: inline-block; max-width: calc(95% - 60px)'>
                                    <h4>Resposta de {$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']} <span class='time'>dia " . date('d/m/Y H\hi') . "</span> " . ($_SESSION['userLogin']['user_level'] > 5 ? '<span class=\'dash_view_class_support_team radius icon-hipster2\' style=\'background: #f48720;opacity: 0.8\'>Suporte</span>' : '') . " </h4>
                                    {$TicketContent}                                    
                             </div>                            
                        </article>";

                        //ALERT TUTOR FOR RESPONSE
                        if (EAD_TASK_SUPPORT_EMAIL && Check::Email(EAD_TASK_SUPPORT_EMAIL)):
                            $Read->FullRead("SELECT course_id, course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                            $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');
                            $MailCourseId = ($Read->getResult() ? $Read->getResult()[0]['course_id'] : 'N/A');

                            $Read->FullRead("SELECT class_id, class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                            $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');
                            $MailClassName = ($Read->getResult() ? $Read->getResult()[0]['class_name'] : 'N/A');
                            $MailClassId = ($Read->getResult() ? $Read->getResult()[0]['class_id'] : 'N/A');

                            require '../wc_ead.email.php';
                            $MailBody = "
                                <p style='font-size: 1.4em; text-align: center; margin: 30px auto 50px auto'>Nova resposta em sua dúvida!</p>
                                <p>{$_SESSION['userLogin']['user_name']} acabou de enviar uma resposta em sua dúvida da aula <b>{$MailClassTitle}</b> no curso <b>{$MailCourseTitle}.</b></p>
                                <p><b>IMPORTANTE:</b> Quanto antes responder as dúvidas, melhor é a avaliação de seus alunos para o suporte prestado!</p>
                                <p style='font-size: 1.2em;'><b><a href='" . BASE . "/campus/campus.php?wc=cursos/tarefa&id={$MailCourseId}&class={$MailClassId}#{$SupportResponseId}' title='Responder dúvida agora!'>RESPONDER AGORA!</a></b></p>
                                <p>Você também pode responder todas as dúvidas de forma otimizada no menu suporte da plataforma!</p>
                                <p>...</p>
                                <p><b>Resposta de {$_SESSION['userLogin']['user_name']}:</b> {$TicketContent}</p>
                                <p>...</p>
                                <p>Este e-mail automático não deve ser respondido!</p>
                            ";

                            $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                            $Email = new Email;
                            $Email->EnviarMontando("#" . str_pad($SupportResponseId, 4, 0, 0) . " - Nova resposta em uma dúvida!", $MailContent, SITE_NAME, MAIL_USER, MAIL_SENDER, EAD_TASK_SUPPORT_EMAIL);
                        endif;
                    endif;
                    $jSON['clear'] = true;
                endif;
            endif;
            break;

        //STUDENT ACTION :: SEND REVIEW
        case 'wc_ead_student_task_ticket_review':
            $support_id = (!empty($PostData['support_id']) ? $PostData['support_id'] : null);
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);
            $courseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/campus.php?wc=cursos/tarefa&id={$courseId}&class={$studend_class_id}";
                break;
            elseif (!$support_id || !$studend_class_id):
                $jSON['trigger'] = $Trigger->notify("Aula não identificada: Desculpe, {$_SESSION['userLogin']['user_name']}, para avaliar o suporte, antes atualize a página :/", 'red', 'warning', 3000);
                break;
            endif;

            if (empty($PostData['support_review'])):
                $jSON['trigger'] = $Trigger->notify("Selecione a nota, {$_SESSION['userLogin']['user_name']}. Para avaliar o suporte é preciso selecionar uma nota :)", 'red', 'warning', 3000);
            else:
                $PostData['support_status'] = 3;
                $PostData['support_close'] = date("Y-m-d H:i:s");
                unset($PostData['support_id']);
                $Update->ExeUpdate(DB_EAD_SUPPORT, $PostData, "WHERE support_id = :support AND user_id = :user", "support={$support_id}&user={$_SESSION['userLogin']['user_id']}");

                if (!empty($PostData['support_comment'])) {
                    //ALERT TUTOR FOR RESPONSE
                    if (EAD_TASK_SUPPORT_EMAIL && Check::Email(EAD_TASK_SUPPORT_EMAIL)):
                        $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                        $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');

                        $Read->FullRead("SELECT class_title FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                        $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');

                        require '../wc_ead.email.php';
                        $MailBody = "
                                <p style='font-size: 1.4em; text-align: center; margin: 30px auto 50px auto'>Nova avaliação de suporte!</p>
                                <p>{$_SESSION['userLogin']['user_name']} enviou um feedback sobre o suporte recebido na aula <b>{$MailClassTitle}</b> do curso <b>{$MailCourseTitle}.</b></p>
                                <p>A nota para o suporte recebido foi {$PostData['support_review']} de 5!</p>
                                <p>...</p>
                                <p><b>Feedback de {$_SESSION['userLogin']['user_name']}:</b></p>
                                <p>" . nl2br($PostData['support_comment']) . "</p>
                                <p>...</p>
                                <p><b>Dados de contato:</b></p>
                                <p>
                                  Nome: {$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}<br>
                                  E-mail: {$_SESSION['userLogin']['user_email']}<br>
                                  Celular: {$_SESSION['userLogin']['user_cell']}<br>
                                  Telefone: {$_SESSION['userLogin']['user_telephone']} 
                                </p>
                                <p>...</p>
                                <p>Este e-mail automático não deve ser respondido!</p>
                            ";

                        $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                        $Email = new Email;
                        $Email->EnviarMontando("#" . str_pad($support_id, 4, 0, 0) . " - Feedback do suporte!", $MailContent, SITE_NAME, MAIL_USER, MAIL_SENDER, MAIL_USER);
                    endif;
                }

                $ReviewPositive = '<span class="icon-star-full icon-notext font_green"></span>';
                $ReviewNegative = '<span class="icon-star-empty icon-notext font_red"></span>';
                $ReviewTicket = str_repeat($ReviewPositive, $PostData['support_review']) . str_repeat($ReviewNegative, 5 - $PostData['support_review']);

                $jSON['trigger'] = $Trigger->notify("Obrigado por sua avaliação {$_SESSION['userLogin']['user_name']}! O Suporte foi concluído com sucesso!", 'green', 'heart', 5000);
                $jSON['close'] = ".jwc_ticket_review_content";
                $jSON['clear'] = true;
                $jSON['review'] = $ReviewTicket;
            endif;
            break;

        //STUDENT ACTION :: SEND REPLY
        case 'wc_ead_student_task_ticket_reply':
            $support_id = (!empty($PostData['support_id']) ? $PostData['support_id'] : null);
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);

            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/index.php";
                break;
            elseif (!$support_id || !$studend_class_id):
                $jSON['trigger'] = $Trigger->notify("Aula não identificada! Desculpe, {$_SESSION['userLogin']['user_name']}, para avaliar o suporte, antes atualize a página :/", 'red', 'warning', 3000);
                break;
            endif;

            if (empty($PostData['ticket_content'])):
                $jSON['trigger'] = $Trigger->notify("Oppsss, {$_SESSION['userLogin']['user_name']}. Você esqueceu de escrever sua resposta :/", 'yellow', 'wink', 3000);
            else:
                //VALIDATE TICKET CONTENT
                $PregContent = preg_replace("/<p[^>]*>[\s|&nbsp;\ ]*<\/p>/", '', $PostData['ticket_content']);
                $TicketContent = str_replace("\r\n", "", $PregContent);

                //VALID THUMB
                $UserThumb = "../../uploads/{$_SESSION['userLogin']['user_thumb']}";
                $SuportUserThumb = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$_SESSION['userLogin']['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                //READ USER
                $Read->FullRead("SELECT user_id FROM " . DB_EAD_SUPPORT . " WHERE support_id = :support", "support={$support_id}");
                $UserResponderId = $Read->getResult()[0]['user_id'];

                if (!$Read->getResult()):
                    $jSON['trigger'] = $Trigger->notify("Desculpe, {$_SESSION['userLogin']['user_name']}. Não foi possível identificar a dúvida. Recarregue a página!", 'red', 'heart-broken', 3000);
                    break;
                endif;

                //RESPONSE UPDATE STATUS
                $SetSupportStatus = 1;
                if ($UserResponderId != $_SESSION['userLogin']['user_id'] || $_SESSION['userLogin']['user_level'] > 5):
                    if ($_SESSION['userLogin']['user_level'] > 5):
                        //REPLY BY ADMIN
                        $SetSupportStatus = 2;
                    else:
                        //REPLY BY OTHER STUDENT
                        $SetSupportStatus = (EAD_TASK_SUPPORT_STUDENT_RESPONSE ? 2 : 1);
                    endif;
                endif;

                //RESPONSE UPDATE DATA
                $ReponseUpdate = ['support_status' => $SetSupportStatus, 'support_reply' => date("Y-m-d H:i:s"), 'support_close' => null, 'support_review' => null, 'support_comment' => null];
                $Update->ExeUpdate(DB_EAD_SUPPORT, $ReponseUpdate, "WHERE support_id = :support", "support={$support_id}");

                //REPLY CREATE
                $CreateReply = ['user_id' => $_SESSION['userLogin']['user_id'], 'enrollment_id' => $_SESSION['wc_student_enrollment_id'], 'support_id' => $support_id, 'response_content' => $TicketContent, 'response_open' => date("Y-m-d H:i:s")];
                $Create->ExeCreate(DB_EAD_SUPPORT_REPLY, $CreateReply);

                if ($UserResponderId == $_SESSION['userLogin']['user_id']):
                    //ALERT ADMIN :: Avisa admin sobre novo ticket!
                    if (EAD_TASK_SUPPORT_EMAIL && Check::Email(EAD_TASK_SUPPORT_EMAIL)):
                        $Read->FullRead("SELECT course_id, course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                        $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');
                        $MailCourseId = ($Read->getResult() ? $Read->getResult()[0]['course_id'] : 'N/A');

                        $Read->FullRead("SELECT class_id, class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                        $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');
                        $MailClassName = ($Read->getResult() ? $Read->getResult()[0]['class_name'] : 'N/A');
                        $MailClassId = ($Read->getResult() ? $Read->getResult()[0]['class_id'] : 'N/A');

                        require '../wc_ead.email.php';
                        $MailBody = "
                                <p style='font-size: 1.4em; text-align: center; margin: 30px auto 50px auto'>Nova resposta em sua dúvida!</p>
                                <p>{$_SESSION['userLogin']['user_name']} acabou de enviar uma resposta em sua dúvida da aula <b>{$MailClassTitle}</b> no curso <b>{$MailCourseTitle}.</b></p>
                                <p><b>IMPORTANTE:</b> Quanto antes responder as dúvidas, melhor é a avaliação de seus alunos para o suporte prestado!</p>
                                <p style='font-size: 1.2em;'><b><a href='" . BASE . "/campus/campus.php?wc=cursos/tarefa&id={$MailCourseId}&class={$MailClassId}#{$support_id}' title='Responder dúvida agora!'>RESPONDER AGORA!</a></b></p>
                                <p>Você também pode responder todas as dúvidas de forma otimizada no menu suporte da plataforma!</p>
                                <p>...</p>
                                <p><b>Resposta de {$_SESSION['userLogin']['user_name']}:</b> {$TicketContent}</p>
                                <p>...</p>
                                <p>Este e-mail automático não deve ser respondido!</p>
                            ";

                        $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                        $Email = new Email;
                        $Email->EnviarMontando("#" . str_pad($support_id, 4, 0, 0) . " - Nova resposta em uma dúvida!", $MailContent, SITE_NAME, MAIL_USER, MAIL_SENDER, EAD_TASK_SUPPORT_EMAIL);
                    endif;
                else:
                    //ALERT RESPONDER :: Avisa aluno sobre nova resposta!
                    $Read->FullRead("SELECT user_name, user_lastname, user_email, user_genre FROM " . DB_USERS . " WHERE user_id = :user", "user={$UserResponderId}");
                    $UserResponderData = $Read->getResult()[0];
                    $UserGenreString = ($Read->getResult()[0]['user_genre'] == 1 ? 'o' : 'a');

                    $Read->FullRead("SELECT course_id, course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                    $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');
                    $MailCourseId = ($Read->getResult() ? $Read->getResult()[0]['course_id'] : 'N/A');

                    $Read->FullRead("SELECT class_id, class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                    $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');
                    $MailClassName = ($Read->getResult() ? $Read->getResult()[0]['class_name'] : 'N/A');
                    $MailClassId = ($Read->getResult() ? $Read->getResult()[0]['class_id'] : 'N/A');

                    require '../wc_ead.email.php';
                    $MailBody = "
                        <p style='font-size: 1.4em; text-align: center; margin: 30px auto 50px auto'>Nova resposta em sua dúvida!</p>
                        <p>Olá, {$UserResponderData['user_name']}!</p>
                        <p>{$_SESSION['userLogin']['user_name']} acabou de enviar uma resposta em sua dúvida!</p>
                        <p>Para responder, efetue <a href='" . BASE . "/campus' title='Acessar minha conta na plataforma!'>login aqui</a> e acesse a aula <b>{$MailClassTitle}</b> do curso <b>{$MailCourseTitle}.</b></p>
                        <p>Já esta logad{$UserGenreString} na plataforma? Então acesse diretamente <a href='" . BASE . "/campus/campus.php?wc=cursos/tarefa&id={$MailCourseId}&class={$MailClassId}#{$support_id}' title='Acessar aula {$MailClassTitle}!'>clicando aqui!</a></p>
                        <p><b>IMPORTANTE:</b> Para concluir sua dúvida envie sua avaliação no ticket, ou adicione outra resposta para tirar mais dúvidas!</p>
                        <p>...</p>
                        <p>Se tiver qualquer problema, não deixe de responder este e-mail!</p>
                        <p>Atenciosamente,</p>
                        <p>" . SITE_NAME . "!</p>  
                    ";

                    $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                    $Email = new Email;
                    $Email->EnviarMontando("#" . str_pad($support_id, 4, 0, 0) . " - Sua dúvida foi respondida!", $MailContent, MAIL_SENDER, MAIL_USER, "{$UserResponderData['user_name']} {$UserResponderData['user_lastname']}", $UserResponderData['user_email']);
                endif;

                //RETURN REPLY
                $jSON['clear'] = true;
                $jSON['close'] = ".jwc_ticket_reply_content";
                $jSON['ead_support'] = true;
                $jSON['ead_support_id'] = $support_id;
                $jSON['ead_support_content'] = "<article class='dash_view_class_support_ticket_reply wc_ead_course_task_forum_ticket' id='{$Create->getResult()}'>
                    <img style='vertical-align: top;' class='thumb rounded' src='" . BASE . "/tim.php?src={$SuportUserThumb}&w=70&h=70' title='{$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}' alt='{$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}'>
                    <div class='htmlchars' style='display: inline-block; max-width: calc(95% - 60px)'>
                    <h4>Resposta de {$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']} <span class='time'>dia " . date('d/m/Y H\hi') . "</span> " . ($_SESSION['userLogin']['user_level'] > 5 ? '<span class=\'dash_view_class_support_team radius icon-hipster2\' style=\'background: #f48720;opacity: 0.8\'>Suporte</span>' : '') . " </h4>
                        {$TicketContent}
                    </div>";
            endif;
            break;

        //STUDENT :: CERTIFICATION
        case 'wc_ead_studend_certification':
            sleep(1);

            if (empty($_SESSION['userLogin']) || empty($_SESSION['userLogin']['user_id'])):
                $jSON['trigger'] = $Trigger->notify("Oppsss, perdemos algo: Sua conta não está mais conectada, recarregando!", 'red', 'spinner3', 3000);
                $jSON['reload'] = true;
                break;
            endif;

            $Read->FullRead("SELECT certificate_id FROM " . DB_EAD_STUDENT_CERTIFICATES . " WHERE enrollment_id = :enrol AND user_id = :user", "enrol={$PostData['enrollment_id']}&user={$_SESSION['userLogin']['user_id']}");
            if ($Read->getResult()):
                $jSON['trigger'] = $Trigger->notify("Oppsss, perdemos algo: Seu certificado para este curso já foi emitido {$_SESSION['userLogin']['user_name']}!", 'red', 'warning', 3000);
                break;
            endif;

            $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE enrollment_id = :enrol AND user_id = :user", "enrol={$PostData['enrollment_id']}&user={$_SESSION['userLogin']['user_id']}");
            if (!$Read->getResult()):
                $jSON['trigger'] = $Trigger->notify("Erro ao Emitir Certificado: Desculpe {$_SESSION['userLogin']['user_name']} mas não foi possível ler a matrícula deste curso. Favor atualize a página e tente novamente, e caso o erro persista entre em contato via " . SITE_ADDR_EMAIL . ".", 'red', 'warning', 5000);
                break;
            endif;

            extract($Read->getResult()[0]);

            $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
            $ClassCount = $Read->getResult()[0]['ClassCount'];

            $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL", "user={$user_id}&course={$course_id}");
            $ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

            $CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : 0);

            $Read->LinkResult(DB_EAD_COURSES, "course_id", $course_id, 'course_title, course_certification_request');
            extract($Read->getResult()[0]);

            if ($_SESSION['userLogin']['user_level'] < 6 && $CourseCompletedPercent < $course_certification_request):
                $jSON['trigger'] = $Trigger->notify("Oppsss {$_SESSION['userLogin']['user_name']}, Para solicitar seu certificado <b>antes complete {$course_certification_request}% do curso!", 'red', 'warning', 5000);
                break;
            endif;

            $CreateCertification = ['user_id' => $user_id, 'course_id' => $course_id, 'enrollment_id' => $enrollment_id, 'certificate_key' => "{$user_id}{$course_id}" . date('Ym'), 'certificate_issued' => date("Y-m-d"), 'certificate_status' => 1];
            $Create->ExeCreate(DB_EAD_STUDENT_CERTIFICATES, $CreateCertification);

            $jSON['certification'] = [
                "Image" => "<div class='wc_ead_win_image'><span class='wc_ead_win_image_icon icon-trophy icon-notext'></span></div>",
                "Icon" => "heart",
                "Title" => "Parabéns {$_SESSION['userLogin']['user_name']} :)",
                "Content" => "Mais uma conquista em sua carreira. Seu certificado para o curso <b>{$course_title}</b> foi emitido com sucesso!</p>",
                "Link" => BASE . "/imprimir-certificados/campus.php?wc=cursos/imprimir&id={$CreateCertification['certificate_key']}",
                "LinkIcon" => "printer",
                "LinkTitle" => "Imprimir Certificado!"
            ];
            break;

        //ALTERAÇÕES

    endswitch;
endif;

if ($jSON):
    echo json_encode($jSON);
else:
    $jSON['trigger'] = $Trigger->notify("Oppsss, Erro inesperado:', \"Um erro inesperado foi encontrado no sistema. Favor atualize a página e tente novamente!</p><p>Caso o erro persista, não deixe de nos avisar enviando um e-mail para ' . SITE_ADDR_EMAIL . '!</p><p>Obrigado. Atenciosamente ' . SITE_NAME . '!", 'red', 'warning', 5000);
    echo json_encode($jSON);
endif;

ob_end_flush();
