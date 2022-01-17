<?php
//CENTRAL DE AULAS
if (APP_CLASS && $_SESSION['userLogin']['user_level'] >= LEVEL_WC_CLASS):
    $wc_class_alerts = null;
    $Read->FullRead("SELECT count(class_id) as total FROM " . DB_CLASS . " WHERE class_status != 1");
    if ($Read->getResult() && $Read->getResult()[0]['total'] >= 1):
        $wc_class_alerts .= "<span class='wc_alert bar_yellow'>{$Read->getResult()[0]['total']}</span>";
    endif;
    ?>
    <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'aulas/') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-play2" title="Central de Aulas" href="dashboard.php?wc=aulas/home">Central de Aulas<?= $wc_class_alerts; ?></a>
        <ul class="dashboard_nav_menu_sub">
            <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'aulas/home' ? 'active_sub' : ''; ?>"><a title="Ver Aulas" href="dashboard.php?wc=aulas/home">&raquo; Ver Aulas <?= $wc_class_alerts; ?></a></li>
            <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'aulas/create' ? 'active_sub' : ''; ?>"><a title="Nova Aula" href="dashboard.php?wc=aulas/create">&raquo; Nova Aula</a></li>
        </ul>
    </li>
<?php
endif;

//WC LIVE
if (APP_LIVES && $_SESSION['userLogin']['user_level'] >= LEVEL_WC_LIVES):
    $wc_lives_alerts = null;
    $Read->FullRead("SELECT count(live_id) as total FROM " . DB_LIVES . " WHERE live_status != 1");
    if ($Read->getResult() && $Read->getResult()[0]['total'] >= 1):
        $wc_lives_alerts .= "<span class='wc_alert bar_yellow'>{$Read->getResult()[0]['total']}</span>";
    endif;
    ?>
    <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'lives/') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-podcast" title="Lives" href="dashboard.php?wc=lives/home">Lives<?= $wc_lives_alerts; ?></a>
        <ul class="dashboard_nav_menu_sub">
            <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'lives/home' ? 'active_sub' : ''; ?>"><a title="Ver Lives" href="dashboard.php?wc=lives/home">&raquo; Ver lives</a></li>
            <li class="dashboard_nav_menu_sub_li <?= strstr($getViewInput, 'lives/create') ? 'active_sub' : ''; ?>"><a title="Criar Sala" href="dashboard.php?wc=lives/create">&raquo; Criar sala</a></li>
        </ul>
    </li>
    <?php
endif;

//WC LIVROS
if (APP_LIVROS && $_SESSION['userLogin']['user_level'] >= LEVEL_WC_LIVROS):    
    $wc_livro_alerts = null;
    $Read->FullRead("SELECT count(livro_id) as total FROM " . DB_LIVROS . " WHERE livro_status != 1");
    if ($Read->getResult() && $Read->getResult()[0]['total'] >= 1):
        $wc_livro_alerts .= "<span class='wc_alert bar_yellow'>{$Read->getResult()[0]['total']}</span>";
    endif;
    ?>
    <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'livros/') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-book" title="Livros" href="dashboard.php?wc=livros/home">Livros <?= $wc_livro_alerts; ?></a>
        <ul class="dashboard_nav_menu_sub">
            <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'livros/home' ? 'active_sub' : ''; ?>"><a title="Ver Livros" href="dashboard.php?wc=livros/home">&raquo; Ver Livros <?= $wc_livro_alerts; ?></a></li>
            <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'livros/create' ? 'active_sub' : ''; ?>"><a title="Novo Livro" href="dashboard.php?wc=livros/create">&raquo; Novo Livro</a></li>
        </ul>
    </li>
<?php
endif;