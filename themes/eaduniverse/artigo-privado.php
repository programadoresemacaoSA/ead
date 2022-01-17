<?php
if (!$Read):
    $Read = new Read;
endif;

$Read->ExeRead(DB_POSTS, "WHERE post_name = :nm", "nm={$URL[1]}");
if (!$Read->getResult()):
    require REQUIRE_PATH . '/404.php';
    return;
else:
    extract($Read->getResult()[0]);
    $Update = new Update;
    $UpdateView = ['post_views' => $post_views + 1, 'post_lastview' => date('Y-m-d H:i:s')];
    $Update->ExeUpdate(DB_POSTS, $UpdateView, "WHERE post_id = :id", "id={$post_id}");

    $Read->FullRead("SELECT category_title, category_name FROM " . DB_CATEGORIES . " WHERE category_id = :id", "id={$post_category}");
    $PostCategory = $Read->getResult()[0];

    $Read->FullRead("SELECT user_name, user_lastname, user_thumb, user_occupation, user_desc FROM " . DB_USERS . " WHERE user_id = :user", "user={$post_author}");
    $AuthorName = "{$Read->getResult()[0]['user_name']} {$Read->getResult()[0]['user_lastname']}";
    $AuthorThumb = $Read->getResult()[0]['user_thumb'];
    $AuthorOccupation = $Read->getResult()[0]['user_occupation'];
    $AuthorDesc = $Read->getResult()[0]['user_desc'];
endif;
?>
<div class="header-content-wrap course-single">
    <div class="header-content">
        <div class="container">
            <div class="row">
                <small style="color: #65c7b4;margin-bottom: 20px;display: block">
                    <span style="margin-right: 20px"><i class="fa fa-tags"></i> Categoria: <?= $PostCategory['category_title']; ?></span>
                    <span style="margin-right: 20px"><i class="fa fa-calendar"></i> Publicado em: <?= date("d/m/Y", strtotime($post_date)); ?></span>
                    <span><i class="fa fa-comments"></i> <span class="fb-comments-count" data-href="<?= BASE; ?>/artigo/<?= $post_name; ?>"></span> comentários</span>
                    <span style="margin-left: 20px"><i class="fa fa-lock"></i> Artigo: Privado</span>
                </small>
                <h1><?= $post_title; ?></h1>
            </div>
        </div>
    </div>
</div>
<!-- Seção 2: Conteúdo -->
<section class="page-section content">
    <div class="container">
        <div class="row section-content">
            <div class="col-xs-12 col-md-8">
                <div class="product-content">
                    <?php
                    if ($post_video && !is_numeric($post_video)):
                        echo "<div class='sample-image'>";
                        echo "<div class='embed-responsive embed-responsive-16by9'>";
                        echo "<iframe class='embed-responsive-item' src='https://www.youtube.com/embed/{$post_video}?rel=0&amp;showinfo=0&autoplay=0&origin=" . BASE . "' frameborder='0' allowfullscreen></iframe>";
                        echo "</div>";
                        echo "</div>";
                    elseif ($post_video && is_numeric($post_video)):
                        echo "<div class='sample-image'>";
                        echo "<div class='embed-responsive embed-responsive-16by9 sample-image'>";
                        echo "<iframe class='embed-responsive-item' src='https://player.vimeo.com/video/{$post_video}' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";
                        echo "</div>";
                        echo "</div>";
                    else:
                        echo '<figure style="margin-bottom: 35px" class="img-wrap sample-image">';
                        echo "<img class='cover' title='{$post_title}' alt='{$post_title}' src='" . BASE . "/uploads/{$post_cover}'/>";
                        echo '</figure>';
                    endif;

                    echo $post_content;
                    ?>
                    <div class="comments">
                        <h3>Deixe um comentário</h3>
                        <div class="fb-comments" data-href="<?= BASE; ?>/artigo/<?= $post_name; ?>" data-numposts="5" data-width="100%"></div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <?php require 'inc/sidebar-privado.php'; ?>
            </div>
        </div>
    </div>
</section>
<!-- // Seção 1: Conteúdo -->
<?php require 'inc/newsletter.php'; ?>