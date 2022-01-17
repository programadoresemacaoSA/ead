<section class="not-found">
    <div class="container text-center">
        <img src="<?= INCLUDE_PATH; ?>/img/404.svg" width="500"/>
        <h1>Opsss</h1>
        <p>Desculpe mas não encontramos o que você procura, tente novamente!</p>
    </div>
</section>
<div class="header-content-wrap" style="background: #007964;">
    <div class="header-content" style="padding-top: 0;">
        <div class="row">
            <form class="search-form" action="" method="post" name="buscar">
                <input type="search" name="b" id="pesquisar" placeholder="Procure por cursos e tecle enter...">
                <button type="submit" class="btn-search"><img src="<?= INCLUDE_PATH; ?>/img/mkpwc_search.svg" alt="Pesquisar"></button>
            </form>
        </div>
    </div>
</div>
<?php require 'inc/newsletter.php'; ?>