<?php
$AdminLevel = LEVEL_WC_LIVROS;
if (!APP_LIVROS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

//AUTO DELETE POST TRASH
if (DB_AUTO_TRASH):
    $Delete = new Delete;
    $Delete->ExeDelete(DB_LIVROS, "WHERE livro_title IS NULL AND livro_content IS NULL and livro_status = :st", "st=0");

    //AUTO TRASH IMAGES
    $Read->FullRead("SELECT image FROM " . DB_LIVROS_IMAGE . " WHERE livro_id NOT IN(SELECT livro_id FROM " . DB_LIVROS . ")");
    if ($Read->getResult()):
        $Delete->ExeDelete(DB_LIVROS_IMAGE, "WHERE id >= :id AND livro_id NOT IN(SELECT livro_id FROM " . DB_LIVROS . ")", "id=1");
        foreach ($Read->getResult() as $ImageRemove):
            if (file_exists("../uploads/{$ImageRemove['image']}") && !is_dir("../uploads/{$ImageRemove['image']}")):
                unlink("../uploads/{$ImageRemove['image']}");
            endif;
        endforeach;
    endif;
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$S = filter_input(INPUT_GET, "s", FILTER_DEFAULT);

$Search = filter_input_array(INPUT_POST);
if ($Search && (isset($Search['s']) || isset($Search['status']))):
    $S = (isset($Search['s']) ? urlencode($Search['s']) : $S);
    $SearchCat = (!empty($Search['searchcat']) ? $Search['searchcat'] : null);
    header("Location: dashboard.php?wc=livros/home&s={$S}");
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-book">Livros</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="Todos os Livros" href="dashboard.php?wc=livros/home">Livros</a>
            <?= ($S ? "<span class='crumb'>/</span> <span class='icon-search'>{$S}</span>" : ''); ?>
        </p>
    </div>
    <div class="dashboard_header_search">
        <a title="Novo Livro" href="dashboard.php?wc=livros/create" class="btn btn_green icon-plus">Adicionar Novo Livro!</a>
        <span class="btn btn_blue icon-spinner9 wc_drag_active" title="Organizar Livros">Ordenar</span>
    </div>
</header>

<div class="dashboard_content">
    <div class='dash_view_books box_wrap'>
        <?php
        $getPage = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT);
        $Page = ($getPage ? $getPage : 1);
        $Paginator = new Pager("dashboard.php?wc=livros/home&s={$S}&pg=", '<<', '>>', 3);
        $Paginator->ExePager($Page, 4);

        if (!empty($S)):
            $WhereString[0] = "AND (livro_title LIKE '%' :s '%' OR livro_content LIKE '%' :s '%')";
            $WhereString[1] = "&s={$S}";
        else:
            $WhereString[0] = "";
            $WhereString[1] = "";
        endif;

        $Read->FullRead("SELECT * FROM " . DB_LIVROS . " WHERE 1=1 "
            . "{$WhereString[0]} "
            . "ORDER BY livro_order ASC, livro_date DESC "
            . "LIMIT :limit OFFSET :offset", "limit={$Paginator->getLimit()}&offset={$Paginator->getOffset()}{$WhereString[1]}"
        );

        if (!$Read->getResult()):
            $Paginator->ReturnPage();
            echo Erro("<span class='al_center icon-notification'>Ainda não existem Livros cadastrados aqui, {$Admin['user_name']}. Comece agora mesmo cadastrando seu primeiro Livro!</span>", E_USER_NOTICE);
        else:
            foreach ($Read->getResult() as $POST):
                extract($POST);

                $PostCover = (file_exists("../uploads/{$livro_cover}") && !is_dir("../uploads/{$livro_cover}") ? "uploads/{$livro_cover}" : 'admin/_img/no_image.jpg');
                $PostStatus = ($livro_status == 1 && strtotime($livro_date) >= strtotime(date('Y-m-d H:i:s')) ? '<span class="btn btn_blue icon-clock icon-notext wc_tooltip"><span class="wc_tooltip_baloon">Agendado</span></span>' : ($livro_status == 1 ? '<span class="btn btn_green icon-checkmark icon-notext wc_tooltip"><span class="wc_tooltip_baloon">Publicado</span></span>' : '<span class="btn btn_yellow icon-warning icon-notext wc_tooltip"><span class="wc_tooltip_baloon">Pendente</span></span>'));
                $livro_title = (!empty($livro_title) ? $livro_title : 'Edite esse rascunho para poder exibir como Livro em seu site!');

                echo "<article class='dash_view_books_item dash_view_course post_single wc_draganddrop' callback='Livros' callback_action='livros_order' id='{$livro_id}'>
                <div class='cover'>
                    <a class='al_center' title='{$livro_title}' target='_blank' href='{$livro_link}'>
                        <img alt='{$livro_title}' title='{$livro_title}' src='../tim.php?src={$PostCover}&w=144&h=204'>                        
                    </a>
                </div>
                <div class='info'>
                    <p class='category icon-bookmark'>{$livro_category}</p>
                    <h2>{$livro_title}</h2>
                    <p class='author'>por {$livro_book_author}</p>
                    <div class='info-text' style='margin-top: 15px'>
                        {$livro_content}
                    </div>
                    <div class='post_single_actions'>
                    <a title='Editar Livro' href='dashboard.php?wc=livros/create&id={$livro_id}' class='post_single_center icon-pencil btn btn_blue'>Editar</a>
                        <span rel='post_single' class='j_delete_action icon-cancel-circle btn btn_red' id='{$livro_id}'>Deletar</span>
                        <span rel='post_single' callback='Livros' callback_action='delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$livro_id}'>Deletar Livros?</span>
                    </div>                    
                </div>
            </article>";
            endforeach;

            $Paginator->ExePaginator(DB_LIVROS, "WHERE livro_title");
            echo $Paginator->getPaginator();
        endif;
        ?>
    </div>
</div>