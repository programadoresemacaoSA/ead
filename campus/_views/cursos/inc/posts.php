<article class='app_play_content_article dash_view_course' >
    <div class='app_play_content_article_thumb'>
        <a target="_blank" href='<?= BASE; ?>/artigo-privado/<?= $post_name; ?>' title='Ver Artigo <?= $post_title; ?>'>
            <img style='border-radius: 4px 4px 0 0' src='../tim.php?src=uploads/<?= $post_cover; ?>&w=500' alt='Ver Artigo <?= $post_title; ?>' title='Ver Artigo <?= $post_title; ?>'/>
        </a>
        <div class='app_play_content_article_played'>
            <p class='icon-lock icon-notext wc_tooltip'><span class="wc_tooltip_baloon">Post Privado</span></p>
        </div>                            
    </div>
    <div class='app_play_content_article_desc'>
        <p class='app_play_content_article_desc_cat'><?= $Category; ?></p>
        <p class='app_play_content_article_desc_review'><span class='icon-star-full'></span><span class='icon-star-full'></span><span class='icon-star-full'></span><span class='icon-star-full'></span><span class='icon-star-full'></span></p>
        <h2><a target="_blank" href='<?= BASE; ?>/artigo-privado/<?= $post_name; ?>' title='Ver Artigo <?= $post_title; ?>'><?= $post_title; ?></a></h2>
    </div>
</article>