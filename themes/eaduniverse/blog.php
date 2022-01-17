<div class="header-content-wrap course-single">
    <div class="header-content">
        <div class="container">
            <div class="row">
                <small style="color: #65c7b4;margin-bottom: 20px;display: block">
                    <span>Você está em: Blog</span>
                </small>
                <h1>Conteúdos para você crescer</h1>
            </div>
        </div>
    </div>
</div>
<!-- Seção 2: Conteúdo -->
<section class="page-section content contributions" style="text-align: left;">
    <div class="container">
        <div class="row section-content">
            <div class="col-xs-12 col-md-8">
                <div class="row">
                    <?php
                    $Page = (!empty($URL[1]) ? $URL[1] : 0);
                    $Pager = new Pager(BASE . "/blog/", "<", ">", 5);
                    $Pager->ExePager($Page, 8);
                    $Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_private = 0 AND post_date <= NOW() ORDER BY post_date DESC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
                    if (!$Read->getResult()):
                        $Pager->ReturnPage();
                        echo Erro("Ainda Não existe posts cadastrados. Favor volte mais tarde :)", E_USER_NOTICE);
                    else:
                        foreach ($Read->getResult() as $Post):
                            extract($Post);

                            //PEGA CATEGORIA
                            $Read->FullRead("SELECT category_title, category_name FROM " . DB_CATEGORIES . " WHERE category_id = :id", "id={$post_category}");
                            $PostCategory = $Read->getResult()[0];
                            
                            $BOX = 6;

                            require REQUIRE_PATH . '/inc/posts.php';
                        endforeach;
                    endif;

                    $Pager->ExePaginator(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW()");
                    echo $Pager->getPaginator();
                    ?>
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <?php require 'inc/sidebar.php'; ?>
            </div>
        </div>
    </div>
</section>
<!-- // Seção 1: Conteúdo -->
<?php require 'inc/newsletter.php'; ?>