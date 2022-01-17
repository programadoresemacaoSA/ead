<?php
$Search = urldecode($URL[1]);
$SearchPage = urlencode($Search);

if (empty($_SESSION['buscar']) || !in_array($Search, $_SESSION['buscar'])):
    $Read->FullRead("SELECT search_id, search_count FROM " . DB_SEARCH . " WHERE search_key = :key", "key={$Search}");
    if ($Read->getResult()):
        $Update = new Update;
        $DataSearch = ['search_count' => $Read->getResult()[0]['search_count'] + 1];
        $Update->ExeUpdate(DB_SEARCH, $DataSearch, "WHERE search_id = :id", "id={$Read->getResult()[0]['search_id']}");
    else:
        $Create = new Create;
        $DataSearch = ['search_key' => $Search, 'search_count' => 1, 'search_date' => date('Y-m-d H:i:s'), 'search_commit' => date('Y-m-d H:i:s')];
        $Create->ExeCreate(DB_SEARCH, $DataSearch);
    endif;
    $_SESSION['buscar'][] = $Search;
endif;
?>
<div class="header-content-wrap courses-page">
    <div class="header-content">
        <div class="container">
            <div class="row">
                <h2 class="light"><small>São mais de 4.000 horas de conteúdo com 100% de foco no mercado de trabalho.</small></h2>
                <form class="search-form" action="" method="post" name="buscar">
                    <input type="search" name="b" id="pesquisar" placeholder="Procure por cursos e tecle enter..." value="<?= $Search; ?>">
                    <button type="submit" class="btn-search"><img src="<?= INCLUDE_PATH; ?>/img/mkpwc_search.svg" alt="Pesquisar"></button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Seção 2: Conteúdo -->
<section class="page-section content gray" style="margin-bottom: 0">
    <div class="container">
        <div class="row section-content">
            <div class="products-section">
                <div class="destaques">
                    <?php
                    $Page = (!empty($URL[2]) ? $URL[2] : 1);
                    $Pager = new Pager(BASE . "/buscar/{$SearchPage}/", "<", ">", 5);
                    $Pager->ExePager($Page, 12);
                    $Read->ExeRead(DB_EAD_COURSES, "WHERE course_status = 1 AND course_created <= NOW() AND (course_title LIKE '%' :s '%' OR course_headline LIKE '%' :s '%') ORDER BY course_created DESC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}&s={$Search}");
                    if (!$Read->getResult()):
                        $Pager->ReturnPage();
                        echo Erro("Desculpe, mas sua pesquisa para <b>{$Search}</b> não retornou nenhum curso. Talvez você queira utilizar outros termos!", E_USER_NOTICE);
                    else:
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

                    $Pager->ExePaginator(DB_EAD_COURSES, "WHERE course_status = 1 AND course_created <= NOW() AND (course_title LIKE '%' :s '%' OR course_headline LIKE '%' :s '%')", "s={$Search}");
                    ?>
                </div>
            </div>
            <?php echo $Pager->getPaginator(); ?>
        </div>
    </div>
</section>
<!-- // Seção 1: Conteúdo -->