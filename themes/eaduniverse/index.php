
<div class="header-content-wrap">
    <div class="header-content">
        <h1>Revolucione sua carreira com cursos online!</h1>
        <h2 class="light"><small>São mais de 4.000 horas de conteúdo com 100% de foco no mercado de trabalho.</small></h2>
        <form class="search-form" action="" method="post" name="buscar">
            <input type="search" name="b" id="pesquisar" placeholder="Faça uma busca...">
            <button type="submit" class="btn-search"><img src="<?= INCLUDE_PATH; ?>/img/mkpwc_search.svg" alt="Pesquisar"></button>
        </form>
    </div>
</div>

<!-- Seção 1: Destaques da semana -->
<section class="home-section destaques gray">
    <div class="container">
        <div class="section-title">
            <h2>Cursos em destaque</h2>
            <a href="<?= BASE; ?>/explorar/cursos" class="btn btn-green">Ver todos os cursos</a>
        </div>
        <div class="row section-content">
            <?php
            $Read->ExeRead(DB_EAD_COURSES, "WHERE course_status = 1 AND course_created <= NOW() AND course_feature = 1 ORDER BY course_created DESC");
            if ($Read->getResult()):
                foreach ($Read->getResult() AS $Courses):
                    extract($Courses);

                    //GET COUNT MODULES AND TIME
                    $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
                    $ClassCount = $Read->getResult()[0]['ClassCount'];
                    $ClassTime = floor($Read->getResult()[0]['ClassTime'] / 60) . ":" . str_pad($Read->getResult()[0]['ClassTime'] % 60, 2, 0, 0);

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
                    require 'inc/courses.php';
                endforeach;
            endif;
            ?>
        </div>
        <div class="section-footer">
            <span>* Atualizado última vez hoje às <?= date("H:i"); ?></span>
        </div>
    </div>
</section>
<!-- // Seção 1: Destaques da semana -->

<?php
$Read->ExeRead(DB_USERS, "WHERE user_level <= 10 AND user_feature = 1 ORDER BY RAND() LIMIT 14");
if ($Read->getResult()):
    ?>
    <!-- Seção 2: Alunos Populares -->
    <section class="home-section populares">
        <div class="container">
            <div class="row">
                <div class="section-title">
                    <h2>Alunos em destaque</h2>
                </div>
                <div class="section-content">
                    <?php foreach ($Read->getResult() AS $Student): ?>
                        <div class="popular-wrap">
                            <div class="img-wrap">
                                <img src="<?= BASE; ?>/tim.php?src=uploads/<?= $Student['user_thumb']; ?>&w=200&h=200" alt="<?= $Student['user_name']; ?> <?= $Student['user_lastname']; ?>">
                            </div>
                            <div class="popular-name">
                                <h3><?= $Student['user_name']; ?> <?= $Student['user_lastname']; ?></h3>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- // Seção 2: Alunos Populares -->
    <?php
endif;
?>
<!-- Seção 3: Vantagens -->
<section class="home-section vantagens">
    <div class="container-fluid">
        <div class="row">
            <div class="section-content">
                <div class="vantagens-wrap">
                    <div class="vantagem">
                        <div class="img-wrap">
                            <img src="<?= INCLUDE_PATH; ?>/img/mkpwc_vantagem-1.png" alt="Todos os temas e Apps">
                        </div>
                        <div class="vantagem-content">
                            <h4 class="lightgreen">Suporte um a um</h4>
                            <h3>Suporte especializado em cada aula para os Cursos Pagos</h3>
                        </div>
                    </div>
                    <div class="vantagem">
                        <div class="img-wrap">
                            <img src="<?= INCLUDE_PATH; ?>/img/mkpwc_vantagem-2.svg" alt="">
                        </div>
                        <div class="vantagem-content">
                            <h4 class="lightgreen">Conteúdo atualizado</h4>
                            <h3>Cuidamos para que todos os nossos Cursos tenham conteúdos de qualidade</h3>
                        </div>
                    </div>
                    <div class="vantagem">
                        <div class="img-wrap">
                            <img src="<?= INCLUDE_PATH; ?>/img/mkpwc_vantagem-3.svg" alt="">
                        </div>
                        <div class="vantagem-content">
                            <h4 class="lightgreen">Plataforma moderna</h4>
                            <h3>Oferecemos a melhor experiência de aprendizado através do nosso campus</h3>
                        </div>
                    </div>
                    <a  href="<?= BASE; ?>/explorar/cursos" class="btn btn-green">Explorar marketplace</a>
                </div>
                <div class="vantagens-drawing">
                    <div class="img-wrap">
                        <img src="<?= INCLUDE_PATH; ?>/img/mkpwc_drawing.svg" alt="Explore o Marketplace">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- // Seção 3: Vantagens -->

<!-- Seção 4: Destaques gratuitos -->
<?php
$Read->ExeRead(DB_EAD_COURSES, "WHERE course_status = 1 AND course_created <= NOW() AND course_vendor_price = '0.00' ORDER BY course_created DESC");
if ($Read->getResult()):
    foreach ($Read->getResult() AS $Courses):
        extract($Courses);

        //GET COUNT MODULES AND TIME
        $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
        $ClassCount = $Read->getResult()[0]['ClassCount'];
        $ClassTime = floor($Read->getResult()[0]['ClassTime'] / 60) . ":" . str_pad($Read->getResult()[0]['ClassTime'] % 60, 2, 0, 0);

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
        require 'inc/freecourse.php';
    endforeach;
endif;
?>
<!-- Seção 4: Destaques gratuitos -->

<!-- Seção 5: Call to action -->
<section class="home-section call-to-action">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-10 col-md-offset-1">
                <div class="section-content">
                    <h2 class="lightblue">Você valoriza seu tempo e seus resultados? Nós também!</h2>
                    <h3 class="white">Entregamos ao aluno conhecimento prático e testado sem enrolação. <br />Você tem acesso a aulas com a melhor qualidade, recursos que aceleram seu aprendizado.</h3>
                    <a href="<?= BASE; ?>/explorar/cursos" class="btn btn-green">Navegar por cursos</a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Seção 4: Call to action -->

<?php
$Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_private = 0 AND post_date <= NOW() ORDER BY post_date DESC LIMIT 4");
if ($Read->getResult()):
    ?>
    <!-- Seção 5: Contribuições -->
    <section class="home-section contributions">
        <div class="container-fluid">
            <div class="section-title">
                <h3 class="lightgray">Conteúdos para você crescer</h3>
                <h2>Últimas do Nosso Blog</h2>
            </div>
            <div class="row section-content">
                <?php
                foreach ($Read->getResult() as $Post):
                    extract($Post);

                    //PEGA CATEGORIA
                    $Read->FullRead("SELECT category_title, category_name FROM " . DB_CATEGORIES . " WHERE category_id = :id", "id={$post_category}");
                    $PostCategory = $Read->getResult()[0];
                    
                    $BOX = 3;

                    require REQUIRE_PATH . '/inc/posts.php';
                endforeach;
                ?>
                <div class="section-footer">
                    <a href="<?= BASE; ?>/blog" class="btn btn-green">Ver todos os artigos</a>
                    <blockquote>"Se você der uma grande contribuição à vida de alguém, passará a fazer da história de vida desta pessoa para sempre."</blockquote>
                </div>
            </div>
        </div>
    </section>
    <!-- Seção 5: Contribuições -->
    <?php
endif;
require 'inc/newsletter.php';
?>