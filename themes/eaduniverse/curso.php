<?php
if (!$Read):
    $Read = new Read;
endif;

$Read->ExeRead(DB_EAD_COURSES, "WHERE course_name = :nm AND course_status = 1", "nm={$URL[1]}");
if (!$Read->getResult()):
    require REQUIRE_PATH . '/404.php';
    return;
else:
    extract($Read->getResult()[0]);
    $Update = new Update;
    $UpdateView = ['course_views' => $course_views + 1];
    $Update->ExeUpdate(DB_EAD_COURSES, $UpdateView, "WHERE course_id = :id", "id={$course_id}");

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

    $Read->FullRead("SELECT user_name, user_lastname, user_thumb, user_occupation, user_facebook, user_instagram, user_twitter, user_youtube, user_desc FROM " . DB_USERS . " WHERE user_id = :user", "user={$course_author}");
    $AuthorName = "{$Read->getResult()[0]['user_name']} {$Read->getResult()[0]['user_lastname']}";
    $AuthorThumb = "{$Read->getResult()[0]['user_thumb']}";
    $AuthorOccupation = "{$Read->getResult()[0]['user_occupation']}";
    $AuthorFacebook = "{$Read->getResult()[0]['user_facebook']}";
    $AuthorInstagram = "{$Read->getResult()[0]['user_instagram']}";
    $AuthorTwitter = "{$Read->getResult()[0]['user_twitter']}";
    $AuthorYouTube = "{$Read->getResult()[0]['user_youtube']}";
    $AuthorDesc = "{$Read->getResult()[0]['user_desc']}";

    //GET COUNT MODULES AND TIME
    $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
    $ClassCount = $Read->getResult()[0]['ClassCount'];
    $ClassTime = floor($Read->getResult()[0]['ClassTime'] / 60) . "h" . str_pad($Read->getResult()[0]['ClassTime'] % 60, 2, 0, 0);

    $Read->LinkResult(DB_EAD_COURSES_SEGMENTS, "segment_id", $course_segment, 'segment_title, segment_icon, segment_name');
    $CourseSegment = ($Read->getResult() ? "{$Read->getResult()[0]['segment_title']}" : "");
    $CourseSegmentIcon = ($Read->getResult() ? "{$Read->getResult()[0]['segment_icon']}" : "");
    $CourseSegmentName = ($Read->getResult() ? "{$Read->getResult()[0]['segment_name']}" : "");

    $Read->FullRead("SELECT count(module_id) AS ModCount FROM " . DB_EAD_MODULES . " WHERE course_id = :cs", "cs={$course_id}");
    $ModCount = str_pad($Read->getResult()[0]['ModCount'], 1, 0, 0);

    $Read->FullRead("SELECT count(enrollment_id) AS TotalEnrollment FROM " . DB_EAD_ENROLLMENTS . " WHERE course_id = :cs", "cs={$course_id}");
    $StudentCount = str_pad($Read->getResult()[0]['TotalEnrollment'], 1, 0, 0);

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
endif;
?>
<div class="header-content-wrap course-single">
    <div class="header-content">
        <div class="container">
            <div class="row">
                <h1><small style="color: #65c7b4">Curso <?= ($course_vendor_price == '0.00' ? 'Gratuito' : NULL); ?> Online</small> <br><?= $course_title; ?></h1>
                <div class="header-tabs">
                    <li class="tab <?= (!isset($URL[2]) ? "tab-active" : null); ?>"><a href="<?= BASE; ?>/curso/<?= $course_name; ?>">Detalhes do Curso</a></li>
                    <li class="tab <?= (isset($URL[2]) && $URL[2] == 'avaliacoes' ? "tab-active" : null); ?>"><a href="<?= BASE; ?>/curso/<?= $course_name; ?>/avaliacoes">Avaliações (<?= $Aval; ?>)</a></li>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seção 2: Conteúdo -->
<section class="page-section content">
    <div class="container">
        <div class="row section-content">
            <div class="col-xs-12 col-md-8">
                <?php
                if (!(isset($URL[2]) && $URL[2] == 'avaliacoes')):
                    ?>
                    <div class="product-content">
                        <?php
                        if ($course_video && !is_numeric($course_video)):
                            echo "<div class='sample-image'>";
                            echo "<div class='embed-responsive embed-responsive-16by9'>";
                            echo "<iframe class='embed-responsive-item' src='https://www.youtube.com/embed/{$course_video}?rel=0&amp;showinfo=0&autoplay=0&origin=" . BASE . "' frameborder='0' allowfullscreen></iframe>";
                            echo "</div>";
                            echo "</div>";
                        elseif ($course_video && is_numeric($course_video)):
                            echo "<div class='sample-image'>";
                            echo "<div class='embed-responsive embed-responsive-16by9 sample-image'>";
                            echo "<iframe class='embed-responsive-item' src='https://player.vimeo.com/video/{$course_video}' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";
                            echo "</div>";
                            echo "</div>";
                        else:
                            echo '<figure class="img-wrap sample-image">';
                            echo "<img class='cover' title='{$course_title}' alt='{$course_title}' src='" . BASE . "/uploads/{$course_cover}'/>";
                            echo '</figure>';
                        endif;

                        $WC_TITLE_LINK = $course_title;
                        $WC_TYPE = "Curso";
                        $WC_SHARE_HASH = "CursoOnline";
                        $WC_SHARE_LINK = BASE . "/curso/{$course_name}";
                        require './_cdn/widgets/share/share.wc.php';
                        ?>
                        <div class="content-section details">
                            <h3 class="underlined-title">Sobre o curso</h3>
                            <?= $course_desc; ?>
                        </div>
                        <div class="content-section details">
                            <h3 class="underlined-title">Módulos do curso</h3>
                            <div class="course-selects">
                                <?php
                                $Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :id ORDER BY module_order ASC", "id={$course_id}");
                                $CourseClasses = 0;
                                $CourseHours = 0;

                                if (!$Read->getResult()):
                                    echo "<div class='trigger al_center trigger_info trigger_none font_medium'>Ainda não existem módulo cadastrados.</div>";
                                else:
                                    foreach ($Read->getResult() as $Module):
                                        extract($Module);

                                        $Read->LinkResult(DB_EAD_CLASSES, "module_id", $module_id, "class_id");
                                        $ModClasses = $Read->getRowCount();
                                        $CourseClasses += $ModClasses;

                                        $Read->FullRead("SELECT SUM(class_time) as ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id = :mod", "mod={$module_id}");
                                        $ModMinutes = $Read->getResult()[0]['ClassTime'];
                                        $CourseHours += $ModMinutes;
                                        ?>
                                        <div class="select">
                                            <div class="title" data-toggle="collapse" data-target="#modules-collapse-<?= $module_id; ?>" aria-expanded="true" aria-controls="modules-collapse-<?= $module_id; ?>">
                                                <span><?= $module_title; ?></span>
                                            </div>
                                            <ul class="collapse open in" id="modules-collapse-<?= $module_id; ?>">
                                                <?php
                                                $Read->ExeRead(DB_EAD_CLASSES, "WHERE module_id = :id ORDER BY class_order ASC", "id={$module_id}");
                                                if (!$Read->getResult()):
                                                    echo '<div class="trigger trigger_info trigger_none al_center icon-info">Ainda não existem aulas cadastradas em ' . $module_title . '!</div>';
                                                    echo '<div class="clear"></div>';
                                                else:
                                                    foreach ($Read->getResult() as $CLASS):
                                                        extract($CLASS);

                                                        $Read->FullRead("SELECT SUM(student_class_views) AS ClassTotalViews FROM " . DB_EAD_STUDENT_CLASSES . " WHERE class_id = :id", "id={$class_id}");
                                                        $ClassTotalViews = $Read->getResult()[0]['ClassTotalViews'];
                                                        ?>
                                                        <?php if (is_numeric($class_video)): ?>
                                                            <li <?= ($class_free == 1 ? 'class="free-lesson"' : NULL); ?>><?= $class_title; ?> <?= ($class_free == 1 ? '<a href="#" class="js-modal-btn" data-video-id="' . $class_video . '"><i class="fa fa-eye"></i> Assistir aula</a>' : NULL); ?></li>
                                                        <?php else: ?>
                                                            <li <?= ($class_free == 1 ? 'class="free-lesson"' : NULL); ?>><?= $class_title; ?> <?= ($class_free == 1 ? '<a href="#" class="js-modal-btn-youtube" data-video-id="' . $class_video . '"><i class="fa fa-eye"></i> Assistir aula</a>' : NULL); ?></li>
                                                        <?php endif; ?>
                                                    <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </ul>
                                        </div>
                                        <?php
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>

                        <?php
                        $CommentModerate = (COMMENT_MODERATE ? " AND (status = 1 OR status = 3)" : '');
                        $Read->ExeRead(DB_COMMENTS, "WHERE course_id = :course{$CommentModerate} AND alias_id IS NULL ORDER BY created " . COMMENT_ORDER, "course={$CommentKey}");
                        $CommentTitle = $course_title;
                        $_SESSION['comm']['course_id'] = $CommentKey;
                        if ($Read->getResult()):
                            ?>
                            <div class="content-section details">
                                <h3 class="underlined-title">O que os alunos falam do curso</h3>
                                <!-- Avaliações -->
                                <div class="box">
                                    <div class="reviews">
                                        <?php
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

                                            echo "<article class='review-single' id='comment{$Comment['id']}'>";
                                            echo '<div class="review-wrap">';
                                            echo '<div class="review-header">';
                                            echo "<figure class='img-wrap'><img src='{$UserAvatar}' alt='{$UserComment['user_name']} {$UserComment['user_lastname']}'></figure>";
                                            echo "<div class='review'><span class='number'>{$Comment['rank']}</span><i class='fa fa-star'></i></div>";
                                            echo '</div>'; //end review-header
                                            echo "<div class='review-content'><div class='content'><h4>{$UserComment['user_name']} {$UserComment['user_lastname']}</h4><span class='verify-student'><i class='fa fa-check-circle'></i> Aluno verificado</span><p class='date'>Avaliou esse curso em " . date('d/m/Y H\hi', strtotime($Comment['created'])) . "</p><p class='review-text'>" . nl2br($Comment['comment']) . "</p></div></div>";
                                            echo "</div>"; //end review-wrap
                                            echo "</article>";
                                        endforeach;
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endif;
                        ?>
                    </div>
                <?php else: ?>
                    <div class="article-content">
                        <h2>Avaliações</h2>
                        <div class="reviews-page-content">
                            <div class="article-boxes-group">
                                <?php
                                //TOTAL COMMENTS
                                $Read->FullRead("SELECT count(id) AS total FROM " . DB_COMMENTS . " WHERE course_id = :course OR alias_id IN(SELECT id FROM " . DB_COMMENTS . " WHERE course_id = :course)", "course={$CommentKey}");
                                $CommentCount = $Read->getResult()[0]['total'];

                                //RANK
                                $Read->FullRead("SELECT SUM(rank) as total FROM " . DB_COMMENTS . " WHERE course_id = :pid{$CommentModerate}", "pid={$CommentKey}");
                                $TotalAval = $Read->getResult()[0]['total'];
                                $TotalRank = $Aval * 5;
                                $getRank = ($TotalAval ? (($TotalAval / $TotalRank) * 50) / 10 : 0);
                                $Rank = str_repeat("<i class='fa fa-star'></i>", intval($getRank)) . str_repeat("<i class='fa fa-star-o'></i>", 5 - intval($getRank));
                                ?>
                                <div class="article-box review-box">
                                    <div class="number">
                                        <?= number_format($getRank, 1, '.', ''); ?>
                                    </div>
                                    <div class="stars-rate">
                                        <?= $Rank; ?>
                                    </div>
                                    <div class="reviews-count">
                                        <?= $CommentCount; ?> avaliações
                                    </div>
                                </div>
<!--                                <div class="article-box review-listbox">
                                    <ul>
                                        <li>
                                            <div class="review">
                                                <span class="number">5</span>
                                                <i class="fa fa-star"></i>
                                            </div>
                                            <div class="review-bar"></div>
                                            <div class="review-percent">100%</div>
                                        </li>
                                        <li>
                                            <div class="review">
                                                <span class="number">4</span>
                                                <i class="fa fa-star"></i>
                                            </div>
                                            <div class="review-bar empty"></div>
                                            <div class="review-percent">0%</div>
                                        </li>
                                        <li>
                                            <div class="review">
                                                <span class="number">3</span>
                                                <i class="fa fa-star"></i>
                                            </div>
                                            <div class="review-bar empty"></div>
                                            <div class="review-percent">0%</div>
                                        </li>
                                        <li>
                                            <div class="review">
                                                <span class="number">2</span>
                                                <i class="fa fa-star"></i>
                                            </div>
                                            <div class="review-bar empty"></div>
                                            <div class="review-percent">0%</div>
                                        </li>
                                        <li>
                                            <div class="review">
                                                <span class="number">1</span>
                                                <i class="fa fa-star"></i>
                                            </div>
                                            <div class="review-bar empty"></div>
                                            <div class="review-percent">0%</div>
                                        </li>
                                    </ul>
                                </div>-->
                            </div>

                            <?php
                            if (isset($_SESSION['userLogin']['user_id'])):
                                $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE course_id = :course AND user_id = :user", "course={$course_id}&user={$_SESSION['userLogin']['user_id']}");
                                if ($Read->getResult()):
                                    $Read->ExeRead(DB_COMMENTS, "WHERE user_id = :user AND course_id = :course AND status = 1", "course={$course_id}&user={$_SESSION['userLogin']['user_id']}");
                                    if ($Read->getResult()):
                                        Erro("{$_SESSION['userLogin']['user_name']}, você já avaliou esse curso!", E_USER_NOTICE);
                                    else:
                                        require '_cdn/widgets/comments-course/comments.php';
                                    endif;
                                endif;
                            endif;

                            //COMMENTS
                            $CommentModerate = (COMMENT_MODERATE ? " AND (status = 1 OR status = 3)" : '');
                            $Read->ExeRead(DB_COMMENTS, "WHERE course_id = :course{$CommentModerate} AND alias_id IS NULL ORDER BY created " . COMMENT_ORDER, "course={$CommentKey}");
                            $CommentTitle = $course_title;
                            $_SESSION['comm']['course_id'] = $CommentKey;
                            if (!$Read->getResult()):
                                Erro("Ainda não temos avaliações para mostrar aqui. Volte em breve!", E_USER_NOTICE);
                            else:
                                ?>
                                <!-- Avaliações -->
                                <div class="box">
                                    <div class="reviews">
                                        <?php
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

                                            echo "<article class='review-single' id='comment{$Comment['id']}'>";
                                            echo '<div class="review-wrap">';
                                            echo '<div class="review-header">';
                                            echo "<figure class='img-wrap'><img src='{$UserAvatar}' alt='{$UserComment['user_name']} {$UserComment['user_lastname']}'></figure>";
                                            echo "<div class='review'><span class='number'>{$Comment['rank']}</span><i class='fa fa-star'></i></div>";
                                            echo '</div>'; //end review-header
                                            echo "<div class='review-content'><div class='content'><h4>{$UserComment['user_name']} {$UserComment['user_lastname']}</h4><span class='verify-student'><i class='fa fa-check-circle'></i> Aluno verificado</span><p class='date'>Avaliou esse curso em " . date('d/m/Y H\hi', strtotime($Comment['created'])) . "</p><p class='review-text'>" . nl2br($Comment['comment']) . "</p></div></div>";
                                            echo "</div>"; //end review-wrap
                                            echo "</article>";
                                        endforeach;
                                        ?>
                                    </div>
                                </div>
                            <?php
                            endif;
                            ?>
                        </div>
                    </div>
                <?php
                endif;
                ?>
            </div>
            <div class="col-xs-12 col-md-4">
                <aside class="product-single-aside">
                    <section class="aside-section price-details">
                        <?php if ($course_vendor_price > '0.00'): ?>
                            <div class="price underlined-title">
                                <small>R$</small> <?= number_format($course_vendor_price, 2, ',', '.'); ?>
                            </div>
                            <p class="discount" style="margin: 0;">ou <strong><?= $NumSplit; ?>x de R$ <?= $SplitPrice; ?> sem juros</strong> no cartão</p>
                        <?php else: ?>
                            <div class="price underlined-title">
                                Gratuito
                            </div>
                            <p class="discount" style="margin: 0;">Comece estudar hoje mesmo!</p>
                        <?php endif; ?>
                        <ul class="checklist">
                            <li>Curso 100% em vídeo</li>
                            <?php if ($course_vendor_price > '0.00'): ?>
                                <li>Suporte em todas as aulas</li>
                            <?php endif; ?>
                            <li>Certificado de conclusão</li>
                            <li>Aprenda no seu ritmo</li>
                            <li>Vídeos em FULL HD</li>
                            <li>Materiais de Apoio</li>
                        </ul>
                        <?php require '_cdn/widgets/ecommerce/cart.add.php'; ?>
                    </section>
                    <section class="aside-section author">
                        <div class="img-wrap">
                            <img src="<?= BASE; ?>/tim.php?src=uploads/<?= $AuthorThumb; ?>&w=150&h=150" alt="<?= $AuthorName; ?>">
                        </div>
                        <div class="author-description">
                            <h3><?= $AuthorName; ?></h3>
                            <small><?= $AuthorOccupation; ?></small>
                            <?= $AuthorDesc; ?>
                        </div>
                    </section>
                    <section class="aside-section sells">
                        <strong><?= $StudentCount; ?></strong>
                        <small>Alunos</small>
                    </section>
                    <section class="aside-section comments">
                        <strong><?= $Aval; ?></strong>
                        <small>Avaliações</small>
                    </section>
                    <section class="aside-section observations">
                        <h3 class="underlined-title">Observações importantes</h3>
                        <p class="obs">
                            <strong>Lançado em</strong>
                            <span><?= date("d/m/Y", strtotime($course_created)); ?></span>
                        </p>
                        <p class="obs">
                            <strong>Nível de aprendizado</strong>
                            <span><?= $course_level; ?></span>
                        </p>
                        <p class="obs">
                            <strong>Duração</strong>
                            <span><?= $ClassTime; ?> horas de vídeos</span>
                        </p>
                        <p class="obs">
                            <strong>Qtd. de aulas</strong>
                            <span><?= $ClassCount; ?> aulas em vídeo</span>
                        </p>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</section>
<!-- // Seção 1: Conteúdo -->
<?php require 'inc/newsletter.php'; ?>