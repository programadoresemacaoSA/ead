<div class="col-xs-12 col-sm-6 col-md-<?= $BOX ? $BOX : '3'; ?>">
    <div class="contribution">
        <a href="<?= BASE; ?>/artigo/<?= $post_name; ?>">
            <div class="img-wrap">
                <img src="<?= BASE; ?>/tim.php?src=uploads/<?= $post_cover; ?>&w=400&h=250" alt="<?= $post_title; ?>"/>
            </div>
        </a>
        <div class="contribution-content">
            <a href="<?= BASE; ?>/artigo/<?= $post_name; ?>">
                <div class="cat-label"><?= $PostCategory['category_title']; ?></div>
                <h3><?= $post_title; ?></h3>
            </a>
            <div class="contribution-info">
                <span class="by"><span class="fb-comments-count" data-href="<?= BASE; ?>/artigo/<?= $post_name; ?>"></span> comentários</span>
                <span class="bullet">&bullet;</span>
                <span class="date"><?= date("d/m/Y", strtotime($post_date)); ?></span> 
            </div>
        </div>
    </div>
</div>