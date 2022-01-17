<?php
$AdminLevel = LEVEL_WC_LIVES;
if (!APP_LIVES || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

//AUTO DELETE POST TRASH
if (DB_AUTO_TRASH):
    $Delete = new Delete;
    $Delete->ExeDelete(DB_LIVES, "WHERE live_title IS NULL AND live_description IS NULL AND live_status = :st", "st=0");
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-podcast">Salas</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            Salas
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Nova Live" href="dashboard.php?wc=lives/create" class="btn btn_green icon-plus">Adicionar Nova Live!</a>
    </div>

</header>
<div class="dashboard_content">
    <?php
    $getPage = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT);
    $Page = ($getPage ? $getPage : 1);
    $Paginator = new Pager('dashboard.php?wc=lives/home&pg=', '<<', '>>', 5);
    $Paginator->ExePager($Page, 12);

    $Read->ExeRead(DB_LIVES, "ORDER BY live_date DESC, live_title ASC, live_date DESC LIMIT :limit OFFSET :offset", "limit={$Paginator->getLimit()}&offset={$Paginator->getOffset()}");
    if (!$Read->getResult()):
        $Paginator->ReturnPage();
        echo Erro("<span class='al_center icon-notification'>Ainda não existem salas cadastrados {$Admin['user_name']}. Comece agora mesmo criando sua primeira sala!</span>", E_USER_NOTICE);
    else:
        foreach ($Read->getResult() as $PAGE):
            extract($PAGE);
            $live_status = ($live_status == 1 ? '<span class="icon-checkmark font_green">Habilitada</span>' : '<span class="icon-warning font_red">Desabilitada</span>');
            $LiveCover = (!empty($live_cover) && file_exists("../uploads/lives/{$live_cover}") && !is_dir("../uploads/lives/{$live_cover}") ? "uploads/lives/{$live_cover}" : 'admin/_img/no_image.jpg');

            echo "<article class='box box25 page_single' id='{$live_id}'>
                    <img src='../tim.php?src={$LiveCover}&w=" . IMAGE_W . "&h=" . IMAGE_H . "' alt='{$live_title}'>
                <div class='panel'>
                    <h1 class='title wc_normalize_height'>" . Check::Chars($live_title, 60) . "</h1>
                    <p>{$live_status}</p>
                    <a title='Ver Live' target='_blank' href='" . BASE . "/" . LIVE_PATH . "/{$live_name}' class='icon-notext icon-eye btn btn_green'></a>
                    <a title='Editar Live' href='dashboard.php?wc=lives/create&id={$live_id}' class='post_single_center icon-notext icon-pencil2 btn btn_blue'></a>
                    <a title='Controlar Live' href='dashboard.php?wc=lives/control&id={$live_id}' class='post_single_center icon-notext icon-cog btn btn_yellow'></a>
                    <span title='Excluir Live' rel='page_single' class='j_delete_action icon-notext icon-cancel-circle btn btn_red' style='top:0;' id='{$live_id}'></span>
                    <span rel='page_single' callback='Lives' callback_action='delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none; top:0;' id='{$live_id}'>Deletar?</span>
                </div>
            </article>";
        endforeach;

        $Paginator->ExePaginator(DB_LIVES);
        echo $Paginator->getPaginator();
    endif;
    ?>
</div>