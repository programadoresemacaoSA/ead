<?php
if (!$Read):
    $Read = new Read;
endif;

$Read->ExeRead(DB_PAGES, "WHERE page_name = :nm", "nm={$URL[0]}");
if (!$Read->getResult()):
    require REQUIRE_PATH . '/404.php';
    return;
else:
    extract($Read->getResult()[0]);
endif;
?>
<div class="header-content-wrap course-single">
    <div class="header-content">
        <div class="container">
            <div class="row">
                <small style="color: #65c7b4;margin-bottom: 20px;display: block">
                    <span>Você está em: <?= $page_title; ?></span>
                </small>
                <h1><?= $page_title; ?></h1>
            </div>
        </div>
    </div>
</div>
<!-- Seção 2: Conteúdo -->
<section class="page-section content">
    <div class="container">
        <div class="row section-content">
            <div class="col-xs-12 col-md-12">
                <div class="product-content">
                    <?php
                    if (!empty($page_cover)):
                        echo '<figure class="img-wrap sample-image">';
                        echo "<img class='cover' title='{$page_title}' alt='{$page_title}' src='" . BASE . "/uploads/{$page_cover}'/>";
                        echo '</figure>';
                        $WC_TITLE_LINK = $page_title;
                        $WC_TYPE = "Página";
                        $WC_SHARE_HASH = "BoraProgramar";
                        $WC_SHARE_LINK = BASE . "/{$page_name}";
                        require './_cdn/widgets/share/share.wc.php';
                    endif;
                    echo $page_content;
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- // Seção 1: Conteúdo -->
<?php require 'inc/newsletter.php'; ?>