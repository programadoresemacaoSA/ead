<!-- Header -->
<header class="header home-header">
    <div class="header-wrap">
        <div class="topbar">
            <div class="logo logo-mobile">
                <a href="<?= BASE; ?>" title="<?= SITE_NAME; ?>">
                    <img src="<?= INCLUDE_PATH; ?>/img/logo.png" alt="<?= SITE_NAME; ?>">
                </a>
            </div>
            <div class="navbar-header">
                <div id="menu-button" class="navbar-toggle collapsed">
                    <span class="sr-only">Navegação Toogle</span>
                    <span class="icon-bar top-bar"></span>
                    <span class="icon-bar middle-bar"></span>
                    <span class="icon-bar bottom-bar"></span>
                </div>
            </div>

            <div class="main-menu-wrap" id="menu-wrap">
                <nav class="main-menu">
                    <div class="top-links">
                        <div class="logo logo-desktop">
                            <a href="<?= BASE; ?>" title="<?= SITE_NAME; ?>"><img src="<?= INCLUDE_PATH; ?>/img/logo.png" alt="<?= SITE_NAME; ?>"></a>
                        </div>
                        <ul class="links-left">
                            <li><a href="<?= BASE; ?>/explorar/cursos">Cursos Online</a></li>
                            <li><a href="<?= BASE; ?>/contato">Fale conosco</a></li>
                        </ul>
                        <ul class="links-right">
                            <li><a href="<?= BASE; ?>/pedido/home"><i class="fa fa-shopping-cart"></i> (<span class="cart_count"><?= (!empty($_SESSION['wc_order']) ? count($_SESSION['wc_order']) : '0'); ?></span>)</a></li>
                            <li><a href="<?= BASE; ?>/campus">Área do Aluno</a></li>
                        </ul>
                    </div>
                    <ul class="main-options">
                        <li><a href="<?= BASE; ?>/explorar/cursos">Explorar cursos</a></li>
                        <li><a href="<?= BASE; ?>/certificados">Certificados</a></li>
                        <li><a href="<?= BASE; ?>/blog">Blog</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</header>
<!-- // Header -->