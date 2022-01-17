<?php
$AdminLevel = LEVEL_WC_CLASS;
if (!APP_CLASS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$S = filter_input(INPUT_GET, "s", FILTER_DEFAULT);
$C = filter_input(INPUT_GET, "cat", FILTER_DEFAULT);

//AUTO DELETE POST TRASH
if (DB_AUTO_TRASH):
    $Delete = new Delete;
    $Delete->ExeDelete(DB_CLASS, "WHERE class_title IS NULL AND class_content IS NULL AND class_status = :st", "st=0");
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-play2">Central de Aulas</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            Central de Aulas
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Nova Aula" href="dashboard.php?wc=aulas/create" class="btn btn_green icon-plus">Adicionar Nova Aula!</a>
        <span class="btn btn_blue icon-spinner9 wc_drag_active" title="Organizar Aulas">Ordenar</span>
    </div>

</header>
<div class="dashboard_content">
    <?php
    $getPage = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT);
    $Page = ($getPage ? $getPage : 1);
    $Paginator = new Pager("dashboard.php?wc=aulas/home&s={$S}&pg=&cat={$C}&pg=", '<<', '>>', 3);
    $Paginator->ExePager($Page, 8);

    if (!empty($C)):
        $WhereCat[0] = "AND ((class_segment = :cat OR FIND_IN_SET(:cat, class_segment_parent)) OR :cat = '')";
        $WhereCat[1] = "&cat={$C}";
    else:
        $WhereCat[0] = "";
        $WhereCat[1] = "";
    endif;

    if (!empty($S)):
        $WhereString[0] = "AND (class_title LIKE '%' :s '%' OR class_content LIKE '%' :s '%')";
        $WhereString[1] = "&s={$S}";
    else:
        $WhereString[0] = "";
        $WhereString[1] = "";
    endif;

    $Read->FullRead("SELECT * FROM " . DB_CLASS . " WHERE 1=1 "
        . "{$WhereCat[0]} "
        . "{$WhereString[0]} "
        . "ORDER BY class_order ASC, class_date DESC "
        . "LIMIT :limit OFFSET :offset", "limit={$Paginator->getLimit()}&offset={$Paginator->getOffset()}{$WhereCat[1]}{$WhereString[1]}"
    );

    if (!$Read->getResult()):
        $Paginator->ReturnPage();
        echo Erro("<span class='al_center icon-notification'>Ainda não existem Aulas cadastradas aqui, {$Admin['user_name']}. Comece agora mesmo cadastrando sua primeira Aula!</span>", E_USER_NOTICE);
    else:
        foreach ($Read->getResult() as $POST):
            extract($POST);

            $ClassCover = (file_exists("../uploads/{$class_cover}") && !is_dir("../uploads/{$class_cover}") ? "uploads/{$class_cover}" : 'admin/_img/no_image.jpg');
            $ClassStatus = ($class_status == 1 && strtotime($class_date) >= strtotime(date('Y-m-d H:i:s')) ? '<span class="btn btn_blue icon-clock icon-notext wc_tooltip"><span class="wc_tooltip_baloon">Agendado</span></span>' : ($class_status == 1 ? '<span class="btn btn_green icon-checkmark icon-notext wc_tooltip"><span class="wc_tooltip_baloon">Publicado</span></span>' : '<span class="btn btn_yellow icon-warning icon-notext wc_tooltip"><span class="wc_tooltip_baloon">Pendente</span></span>'));
            $class_title = (!empty($class_title) ? $class_title : 'Edite esse rascunho para poder exibir como Aula na Área de Membros!');

            $Category = null;
            if (!empty($class_segment)):
                $Read->FullRead("SELECT segment_id, segment_color, segment_title FROM " . DB_EAD_COURSES_SEGMENTS . " WHERE segment_id = :ct", "ct={$class_segment}");
                if ($Read->getResult()):
                    $Category = "<span class='icon-bookmark'><a title='Aulas em {$Read->getResult()[0]['segment_title']}' href='dashboard.php?wc=aulas/home&s={$S}&cat={$Read->getResult()[0]['segment_id']}'>{$Read->getResult()[0]['segment_title']}</a></span> ";
                endif;
            endif;

            if (!empty($class_segment_parent)):
                $Read->FullRead("SELECT segment_id, segment_color, segment_title FROM " . DB_EAD_COURSES_SEGMENTS . " WHERE segment_id IN({$class_segment_parent})");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $SubCat):
                        $Category .= "<span class='icon-bookmarks'><a title='Aulas em {$SubCat['segment_title']}' href='dashboard.php?wc=aulas/home&s={$S}&cat={$SubCat['segment_id']}'>{$SubCat['segment_title']}</a></span> ";
                    endforeach;
                endif;
            endif;

            echo "<article class='box box25 post_single wc_draganddrop' callback='Aulas' callback_action='class_order' id='{$class_id}'>           
                <div class='post_single_cover'>
                    <a title='Ver Aula no Campus' target='_blank' href='" . BASE . "/campus/campus.php?wc=cursos/play&id={$class_id}'>
                        <img alt='{$class_title}' title='{$class_title}' src='../tim.php?src={$ClassCover}&w=" . IMAGE_W / 2 . "&h=" . IMAGE_H / 2 . "'/>
                    </a>
                    <div class='post_single_status'>
                        <span class='btn wc_tooltip'>" . str_pad($class_views, 4, 0, STR_PAD_LEFT) . " <span class='wc_tooltip_baloon'>Visualizações</span></span>
                        {$ClassStatus} 
                    </div>                    
                </div>
                <div class='post_single_content wc_normalize_height'>
                    <h1 class='title'><a title='Ver Aula no Campus' target='_blank' href='" . BASE . "/campus/campus.php?wc=cursos/play&id={$class_id}'>{$class_title}</a></h1>
                    <p class='post_single_cat'>{$Category}</p>
                </div>
                <div class='post_single_actions'>
                    <a title='Editar Aula' href='dashboard.php?wc=aulas/create&id={$class_id}' class='post_single_center icon-pencil btn btn_blue'>Editar</a>
                    <span rel='post_single' class='j_delete_action icon-cancel-circle btn btn_red' id='{$class_id}'>Deletar</span>
                    <span rel='post_single' callback='Aulas' callback_action='delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$class_id}'>Deletar Aula?</span>
                </div>
            </article>";
        endforeach;

        $Paginator->ExePaginator(DB_CLASS, "WHERE "
            . "((class_segment = :cat OR FIND_IN_SET(:cat, class_segment_parent)) OR :cat = '') "
            . "AND (class_title LIKE '%' :s '%' OR class_content LIKE '%' :s '%')", "cat={$C}&s={$S}"
        );
        echo $Paginator->getPaginator();
    endif;
    ?>
</div>