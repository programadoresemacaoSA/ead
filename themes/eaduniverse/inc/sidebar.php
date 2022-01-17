<aside class="product-single-aside">
    <section class="aside-section price-details">
        <div class="price underlined-title" style="font-size: 26px;">
            Lista VIP
        </div>
        <p class="discount" style="margin: 0;">Cadastre-se para receber conteúdos exclusivos em seu e-mail</p>
        <form action="" method="post" name="newsletter">
            <input type="email" name="email" id="email" placeholder="Seu melhor e-mail">
            <button type="submit" class="btn btn-green">Quero receber!</button>
        </form>
    </section>
    <?php if ($URL[0] == 'artigo'): ?>
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
    <?php endif; ?>
    <section class="aside-section mead_side">
        <h3 class="underlined-title">Categorias</h3>
        <?php
        $Read->ExeRead(DB_CATEGORIES, "WHERE category_parent IS NULL AND category_id IN(SELECT post_category FROM " . DB_POSTS . " WHERE post_status = 1 AND post_private = 0 AND post_date <= NOW()) ORDER BY category_title ASC");
        if (!$Read->getResult()):
            echo Erro("Ainda não existem sessões cadastradas!", E_USER_NOTICE);
        else:
            echo "<ul>";
            foreach ($Read->getResult() as $Ses):
                echo "<li><a title='artigos/{$Ses['category_name']}' href='" . BASE . "/artigos/{$Ses['category_name']}'>&raquo; {$Ses['category_title']}</a></li>";
                $Read->ExeRead(DB_CATEGORIES, "WHERE category_parent = :pr AND category_id IN(SELECT post_category_parent FROM " . DB_POSTS . " WHERE post_status = 1 AND post_date <= NOW()) ORDER BY category_title ASC", "pr={$Ses['category_id']}");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $Cat):
                        echo "<li><a title='artigos/{$Cat['category_name']}' href='" . BASE . "/artigos/{$Cat['category_name']}'>&raquo;&raquo; {$Cat['category_title']}</a></li>";
                    endforeach;
                endif;
            endforeach;
            echo "</ul>";
        endif;
        ?>
    </section>
    <section class="aside-section mead_side">
        <h3 class="underlined-title">Mais vistos</h3>
        <ul class="articles-list">
            <?php
            $Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_private = 0 AND post_date <= NOW() ORDER BY post_views DESC, post_date DESC LIMIT 5");
            if (!$Read->getResult()):
                echo Erro("Ainda Não existe posts cadastrados. Favor volte mais tarde :)", E_USER_NOTICE);
            else:
                foreach ($Read->getResult() as $Post):
                    ?>
                    <li>
                        <figure class="img-wrap">
                            <a href="<?= BASE; ?>/artigo/<?= $Post['post_name']; ?>" title="<?= $Post['post_title']; ?>"><img src="<?= BASE; ?>/tim.php?src=uploads/<?= $Post['post_cover']; ?>&w=60&h=60" alt="<?= $Post['post_title']; ?>" /></a>
                        </figure>
                        <div class="article-content">
                            <h4><a href="<?= BASE; ?>/artigo/<?= $Post['post_name']; ?>" title="<?= $Post['post_title']; ?>"><?= $Post['post_title']; ?></a></h4>
                            <div class="date">
                                <i class="fa fa-calendar"></i>
                                <span><?= date("d/m/Y", strtotime($Post['post_date'])); ?></span>
                            </div>
                        </div>
                    </li>
                    <?php
                endforeach;
            endif;
            ?>
        </ul>
    </section>
</aside>