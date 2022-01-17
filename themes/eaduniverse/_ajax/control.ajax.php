<?php

session_start();
$getPost = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (empty($getPost) || empty($getPost['action'])):
    die('Acesso Negado!');
endif;

$strPost = array_map('strip_tags', $getPost);
$POST = array_map('trim', $strPost);

$Action = $POST['action'];
unset($POST['action']);

$jSON = null;

usleep(2000);

require '../../../_app/Config.inc.php';
$Read = new Read;
$Create = new Create;
$Update = new Update;
$Delete = new Delete;
$Trigger = new Trigger;

switch ($Action):
    case 'loadmore':
        $CatId = $POST['cat_id'];
        $Limit = $POST['limit'];
        $Offset = $POST['offset'];

        $jSON['result'] = null;
        if (empty($Offset) && $Offset <= 0):
            $jSON['nomore'] = true;
        else:
            if (!empty($POST['count'])):
                $NextCount = $POST['count'];

                for ($i = 0; $i < $Limit; $i++):
                    $jSON['charge'][$i] = $NextCount;
                    $NextCount++;
                endfor;

                if ($NextCount > $POST['total']):
                    $jSON['postends'] = true;
                endif;

            endif;
        endif;
        break;

    case 'get_course':
        $CourseId = $POST['course_id'];
        $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id = :id", "id={$CourseId}");
        $jSON['course'] = null;
        if ($Read->getResult()):
            extract($Read->getResult()[0]);
            $UpdateView = ['course_views' => $course_views + 1];
            $Update->ExeUpdate(DB_EAD_COURSES, $UpdateView, "WHERE course_id = :id", "id={$course_id}");

            //GET TUTOR
            $Read->LinkResult(DB_USERS, "user_id", $course_author, "user_name, user_lastname, user_thumb, user_occupation, user_desc");
            $CourseTutor = $Read->getResult()[0]['user_name'] . " " . $Read->getResult()[0]['user_lastname'];
            $AuthorThumb = $Read->getResult()[0]['user_thumb'];
            $AuthorOccupation = $Read->getResult()[0]['user_occupation'];
            $AuthorDesc = $Read->getResult()[0]['user_desc'];

            //GET SEGMENTS
            $Read->LinkResult(DB_EAD_COURSES_SEGMENTS, "segment_id", $course_segment);
            if ($Read->getResult()):
                $CourseSegment = $Read->getResult()[0];
            endif;

            //GET COUNT MODULES AND TIME
            $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
            $ClassCount = $Read->getResult()[0]['ClassCount'];
            $ClassTime = floor($Read->getResult()[0]['ClassTime'] / 60) . "h" . str_pad($Read->getResult()[0]['ClassTime'] % 60, 2, 0, 0);

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
            $Rank = str_repeat("<i class='ns-icon'>star</i>", intval($getRank)) . str_repeat("<i class='ns-icon'>star_border</i>", 5 - intval($getRank));


            if (ECOMMERCE_PAY_SPLIT):
                $MakeSplit = intval($course_vendor_price / ECOMMERCE_PAY_SPLIT_MIN);
                $NumSplit = (!$MakeSplit ? 1 : ($MakeSplit && $MakeSplit <= ECOMMERCE_PAY_SPLIT_ACN ? $MakeSplit : ECOMMERCE_PAY_SPLIT_ACN));
                if ($NumSplit <= ECOMMERCE_PAY_SPLIT_ACN):
                    $SplitPrice = number_format(($course_vendor_price / $NumSplit), '2', ',', '.');
                elseif ($NumSplit - ECOMMERCE_PAY_SPLIT_ACN == 1):
                    $SplitPrice = number_format(($course_vendor_price * (pow(1 + (ECOMMERCE_PAY_SPLIT_ACM / 100), $NumSplit - ECOMMERCE_PAY_SPLIT_ACN)) / $NumSplit), '2', ',', '.');
                else:
                    $ParcSj = round($course_vendor_price / $NumSplit, 2); // Valor das parcelas sem juros
                    $ParcRest = (ECOMMERCE_PAY_SPLIT_ACN > 1 ? $NumSplit - ECOMMERCE_PAY_SPLIT_ACN : $NumSplit);
                    $DiffParc = round(($course_vendor_price * getFactor($ParcRest) * $ParcRest) - $course_vendor_price, 2);
                    $SplitPrice = number_format($ParcSj + ($DiffParc / $NumSplit), '2', ',', '.');
                endif;
            endif;

            $jSON['course'] .= "<div class='modal fade in modal-course show' style='display: block' id='modal-curso-{$course_name}' tabindex='-1' role='dialog' aria-labelledby='modal-curso-label' aria-hidden='true'>";
            $jSON['course'] .= '<div class="modal-dialog" role="document">';
            $jSON['course'] .= '<div class="modal-content">';
            $jSON['course'] .= '<div class="modal-header">';
            if ($course_video && !is_numeric($course_video)):
                $jSON['course'] .= "<div class='embed-responsive embed-responsive-16by9'>";
                $jSON['course'] .= "<iframe class='embed-responsive-item' src='https://www.youtube.com/embed/{$course_video}?rel=0&amp;showinfo=0&autoplay=0&origin=" . BASE . "' frameborder='0' allowfullscreen></iframe>";
                $jSON['course'] .= "</div>";
            elseif ($course_video && is_numeric($course_video)):
                $jSON['course'] .= "<div class='embed-responsive embed-responsive-16by9'>";
                $jSON['course'] .= "<iframe class='embed-responsive-item' src='https://player.vimeo.com/video/{$course_video}' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";
                $jSON['course'] .= "</div>";
            else:
                $jSON['course'] .= '<figure class="img-wrap">';
                $jSON['course'] .= "<img class='cover' title='{$course_title}' alt='{$course_title}' src='" . BASE . "/uploads/{$course_cover}'/>";
                $jSON['course'] .= '</figure>';
            endif;
            $jSON['course'] .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
            $jSON['course'] .= '<span aria-hidden="true">&times;</span>';
            $jSON['course'] .= '</button>';
            $jSON['course'] .= '</div>'; //END MODAL-HEADER
            $jSON['course'] .= '<div class="modal-body">';
            $jSON['course'] .= '<div class="content">';
            $jSON['course'] .= "<h2>{$course_title}</h2>";
            $jSON['course'] .= "<p class='desc'>{$course_headline}</p>";
            $jSON['course'] .= '<div class="product-content">';
            $jSON['course'] .= '<div class="content-section details">';
            $jSON['course'] .= '<h3 class="underlined-title">Sobre o curso</h3>';
            $jSON['course'] .= "{$course_desc}";
            $jSON['course'] .= '</div>';
            $jSON['course'] .= '<div class="content-section details">';
            $jSON['course'] .= '<h3 class="underlined-title">Módulos do curso</h3>';
            $jSON['course'] .= '<div class="course-selects">';
            $Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :id ORDER BY module_order ASC", "id={$course_id}");
            $CourseClasses = 0;
            $CourseHours = 0;

            if (!$Read->getResult()):
                $jSON['course'] .= "<div class='trigger al_center trigger_info trigger_none font_medium'>Ainda não existem módulo cadastrados.</div>";
            else:
                foreach ($Read->getResult() as $Module):
                    extract($Module);

                    $Read->LinkResult(DB_EAD_CLASSES, "module_id", $module_id, "class_id");
                    $ModClasses = $Read->getRowCount();
                    $CourseClasses += $ModClasses;

                    $Read->FullRead("SELECT SUM(class_time) as ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id = :mod", "mod={$module_id}");
                    $ModMinutes = $Read->getResult()[0]['ClassTime'];
                    $CourseHours += $ModMinutes;
                    $jSON['course'] .= '<div class="select">';
                    $jSON['course'] .= "<div class='title' data-toggle='collapse' data-target='#modules-collapse-{$module_id}' aria-expanded='true' aria-controls='modules-collapse-{$module_id}'>";
                    $jSON['course'] .= "<span>{$module_title}</span>";
                    $jSON['course'] .= '</div>';
                    $jSON['course'] .= "<ul class='collapse open in' id='modules-collapse-{$module_id}'>";
                    $Read->ExeRead(DB_EAD_CLASSES, "WHERE module_id = :id ORDER BY class_order ASC", "id={$module_id}");
                    if (!$Read->getResult()):
                        $jSON['course'] .= '<div class="trigger trigger_info trigger_none al_center icon-info">Ainda não existem aulas cadastradas em ' . $module_title . '!</div>';
                        $jSON['course'] .= '<div class="clear"></div>';
                    else:
                        foreach ($Read->getResult() as $CLASS):
                            extract($CLASS);

                            $Read->FullRead("SELECT SUM(student_class_views) AS ClassTotalViews FROM " . DB_EAD_STUDENT_CLASSES . " WHERE class_id = :id", "id={$class_id}");
                            $ClassTotalViews = $Read->getResult()[0]['ClassTotalViews'];
                            $jSON['course'] .= "<li " . ($class_free == 1 ? 'class="free-lesson"' : NULL) . ">{$class_title} " . ($class_free == 1 ? '<a href="#" class="js-modal-btn" data-video-id="' . $class_video . '"><i class="fa fa-eye"></i> Assistir aula</a>' : NULL) . "</li>";
                        endforeach;
                    endif;
                    $jSON['course'] .= '</ul>';
                    $jSON['course'] .= '</div>';
                endforeach;
            endif;
            $jSON['course'] .= '</div>';
            $jSON['course'] .= '</div>';

            $Read->ExeRead(DB_COMMENTS, "WHERE course_id = :course{$CommentModerate} AND alias_id IS NULL ORDER BY created " . COMMENT_ORDER, "course={$CommentKey}");
            $CommentTitle = $course_title;
            $_SESSION['comm']['course_id'] = $CommentKey;
            if ($Read->getResult()):
                $jSON['course'] .= '<div class="content-section details">';
                $jSON['course'] .= '<h3 class="underlined-title">O que os alunos falam do curso</h3>';
                $jSON['course'] .= '<div class="box">';
                $jSON['course'] .= '<div class="reviews">';
                foreach ($Read->getResult() as $Comment):
                    $Read->FullRead("SELECT user_id, user_thumb, user_name, user_lastname FROM " . DB_USERS . " WHERE user_id = :id", "id={$Comment['user_id']}");
                    if (!$Read->getResult()):
                        $Delete = new Delete;
                        $Delete->ExeDelete(DB_COMMENTS, "WHERE id = :id OR alias_id = :id", "id={$Comment['id']}");
                        header("Location: " . BASE . "/{$getURL}");
                        exit;
                    else:
                        $UserComment = $Read->getResult()[0];
                        $UserAvatar = ($UserComment['user_thumb'] ? BASE . "/tim.php?src=uploads/{$UserComment['user_thumb']}&w=" . AVATAR_W . "&h=" . AVATAR_H : BASE . "/tim.php?src=admin/_img/no_avatar.jpg&w=" . AVATAR_W . "&h=" . AVATAR_H);
                    endif;

                    $CommentStars = str_repeat("&starf;", $Comment['rank']) . str_repeat("&star;", 5 - $Comment['rank']);

                    $jSON['course'] .= "<article class='review-single' id='comment{$Comment['id']}'>";
                    $jSON['course'] .= '<div class="review-wrap">';
                    $jSON['course'] .= '<div class="review-header">';
                    $jSON['course'] .= "<figure class='img-wrap'><img src='{$UserAvatar}' alt='{$UserComment['user_name']} {$UserComment['user_lastname']}'></figure>";
                    $jSON['course'] .= "<div class='review'><span class='number'>{$Comment['rank']}</span><i class='fa fa-star'></i></div>";
                    $jSON['course'] .= '</div>'; //end review-header
                    $jSON['course'] .= "<div class='review-content'><div class='content'><h4>{$UserComment['user_name']} {$UserComment['user_lastname']}</h4><span class='verify-student'><i class='fa fa-check-circle'></i> Aluno verificado</span><p class='date'>Avaliou esse curso em " . date('d/m/Y H\hi', strtotime($Comment['created'])) . "</p><p class='review-text'>" . nl2br($Comment['comment']) . "</p></div></div>";
                    $jSON['course'] .= "</div>"; //end review-wrap
                    $jSON['course'] .= "</article>";
                endforeach;
                $jSON['course'] .= "</div>";
                $jSON['course'] .= "</div>";
                $jSON['course'] .= "</div>";
            endif;

            $jSON['course'] .= '</div>';

            $jSON['course'] .= '</div>';
            $jSON['course'] .= '<div class="course-details">';
            $jSON['course'] .= '<aside class="product-single-aside" style="margin-top: 0">';
            $jSON['course'] .= '<section class="aside-section price-details">';
            if ($course_vendor_price > '0.00'):
                $jSON['course'] .= '<div class="price underlined-title">';
                $jSON['course'] .= "<small>R$</small> " . number_format($course_vendor_price, 2, ',', '.');
                $jSON['course'] .= "</div>";
                $jSON['course'] .= "<p class='discount' style='margin: 0;'>ou {$NumSplit}x de <strong>R$ {$SplitPrice} sem juros</strong> no cartão</p>";
            else:
                $jSON['course'] .= '<div class="price underlined-title">Gratuito</div>';
                $jSON['course'] .= '<p class="discount" style="margin: 0;">Comece estudar hoje mesmo!</p>';
            endif;
            $jSON['course'] .= '<ul class="checklist">';
            $jSON['course'] .= '<li>Curso 100% em vídeo</li>';
            if ($course_vendor_price > '0.00'):
                $jSON['course'] .= "<li>Suporte em todas as aulas</li>";
            endif;
            $jSON['course'] .= "<li>Certificado de conclusão</li>";
            $jSON['course'] .= "<li>Aprenda no seu ritmo</li>";
            $jSON['course'] .= "<li>Vídeos em FULL HD</li>";
            $jSON['course'] .= "<li>Materiais de Apoio</li>";
            $jSON['course'] .= "</ul>";
            $jSON['course'] .= '<form id="' . $course_id . '" class="wc_cart_add" name="cart_add" method="post" enctype="multipart/form-data">
    <input name="course_id" type="hidden" value="' . $course_id . '"/>
    <input name="item_amount" type="hidden" value="1"/>
    <button class="btn btn-green cart">Matricule-se agora</button>
</form>';
            $jSON['course'] .= '</section>';
            $jSON['course'] .= '<section class="aside-section author">';
            $jSON['course'] .= '<div class="img-wrap">';
            $jSON['course'] .= "<img src='" . BASE . "/tim.php?src=uploads/{$AuthorThumb}&w=150&h=150' alt='{$CourseTutor}'>";
            $jSON['course'] .= "</div>";
            $jSON['course'] .= '<div class="author-description">';
            $jSON['course'] .= "<h3>{$CourseTutor}</h3>";
            $jSON['course'] .= "<small>{$AuthorOccupation}</small>";
            $jSON['course'] .= $AuthorDesc;
            $jSON['course'] .= '</div>';
            $jSON['course'] .= '</section>';
            $jSON['course'] .= '<section class="aside-section sells">';
            $jSON['course'] .= "<strong>{$StudentCount}</strong>";
            $jSON['course'] .= "<small>Alunos</small>";
            $jSON['course'] .= '</section>';
            $jSON['course'] .= '<section class="aside-section comments">';
            $jSON['course'] .= "<strong>{$Aval}</strong>";
            $jSON['course'] .= "<small>Avaliações</small>";
            $jSON['course'] .= '</section>';
            $jSON['course'] .= '<section class="aside-section observations">';
            $jSON['course'] .= '<h3 class="underlined-title">Observações importantes</h3>';
            $jSON['course'] .= '<p class="obs">';
            $jSON['course'] .= '<strong>Lançado em</strong>';
            $jSON['course'] .= "<span>" . date("d/m/Y", strtotime($course_created)) . "</span>";
            $jSON['course'] .= '</p>';
            $jSON['course'] .= '<p class="obs">';
            $jSON['course'] .= '<strong>Nível de aprendizado</strong>';
            $jSON['course'] .= "<span>{$course_level}</span>";
            $jSON['course'] .= '</p>';
            $jSON['course'] .= '<p class="obs">';
            $jSON['course'] .= '<strong>Duração</strong>';
            $jSON['course'] .= "<span>{$ClassTime} horas de vídeos</span>";
            $jSON['course'] .= '</p>';
            $jSON['course'] .= '<p class="obs">';
            $jSON['course'] .= '<strong>Qtd. de aulas</strong>';
            $jSON['course'] .= "<span>{$ClassCount} aulas em vídeo</span>";
            $jSON['course'] .= '</p>';
            $jSON['course'] .= '</section>';
            $jSON['course'] .= '</aside>';

            $jSON['course'] .= '</div>';
            $jSON['course'] .= '</div>'; //END MODAL-BODY
            $jSON['course'] .= "</div>"; //END MODAL-CONTENT
            $jSON['course'] .= "</div>"; //END MODAL-DIALOG
            $jSON['course'] .= "</div>"; //END MODAL-COURSE
        endif;
        break;


    case 'add_newsletter':
        $email = $POST['email'];

        if (!Check::Email($email)):
            $jSON['notify'][] = $Trigger->notify("<b>OPSS:</b> Seu e-mail é inválido!", 'yellow', 'fa fa-warning', 5000);
        else:
            $apiKey = MAILCHIMP_API_KEY;
            $listId = MAILCHIMP_LIST_ID;

            $memberId = md5(strtolower($email));
            $dataCenter = substr($apiKey, strpos($apiKey, '-') + 1);
            $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;

            $json = json_encode([
                'email_address' => $email,
                'status' => "subscribed",
            ]);

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($result):
                $jSON['notify'][] = $Trigger->notify("<b>SUCESSO:</b> Cadastro efetuado com sucesso!", 'green', 'fa fa-checked', 5000);
            else:
                $jSON['notify'][] = $Trigger->notify("<b>OPSS:</b> Erro ao cadastrar na Newsletter! CADASTRE SUAS CREDÊNCIAIS MAILCHIMP em <strong>_app/Config/Config.inc.php</strong>", 'red', 'fa fa-checked', 5000);
            endif;
        endif;
        break;


    case 'verify_certificate':
        $Certificate = $POST['certificate'];

        if (strlen($Certificate) >= 8):
            $Read->ExeRead(DB_EAD_STUDENT_CERTIFICATES, "WHERE certificate_key = :s", "s={$Certificate}");
            if ($Read->getResult()):
                $jSON['redirect'] = BASE . "/certificado/" . $Certificate;
            else:
                $jSON['notify'][] = $Trigger->notify('Não encontramos nenhum certificado neste número de autenticação.', 'red', 'icon-warning', 5000);
            endif;
        else:
            $jSON['notify'][] = $Trigger->notify('O número de autenticação precisa ter no mínimo 8 caracteres!', 'red', 'icon-warning', 5000);
        endif;
        break;

endswitch;

echo json_encode($jSON);
