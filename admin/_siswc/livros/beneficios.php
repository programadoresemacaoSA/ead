<?php
$AdminLevel = LEVEL_WC_SERVICOS;
if (!APP_SERVICOS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$PostId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$PostId):
    $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um benefício sem informar o serviço que ele pertence!", E_USER_NOTICE);
    header('Location: dashboard.php?wc=servicos/home');
    exit;
else:
    $Read->FullRead("SELECT servico_id, servico_title FROM " . DB_SERVICOS . " WHERE servico_id = :id", "id={$PostId}");
    if ($Read->getResult()):
        extract($Read->getResult()[0]);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um benefício sem informar o serviço que ele pertence!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=servicos/home');
        exit;
    endif;
endif;

?>
<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-new-tab">Benefícios</h1>
        <p class="dashboard_header_breadcrumbs">
            <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= $servico_title; ?>" href="dashboard.php?wc=servicos/create&id=<?= $PostId; ?>"><?= $servico_title; ?></a>
            <span class="crumb">/</span>
            &raquo; Benefícios
        </p>
    </div>

    <div class="dashboard_header_search">
        <a class="btn btn_green icon-plus icon-notext add"> Adicionar Benefício</a>
    </div>
</header>
<div class="dashboard_content">
    <!--CADASTRO-->
    <div class="beneficios box box100" id="cadastro">  
        <div class="panel">
            <span class="icon-cross icon-notext add close"></span>
            <div class="box box100">
                <form name="beneficio_add" class="j_beneficios" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="callback" value="Servicos"/>
                    <input type="hidden" name="callback_action" value="beneficio_manage"/>
                    <input type="hidden" name="servico_id" value="<?= $PostId; ?>"/>
                    <input type="hidden" name="beneficio_id" value=""/>

                    <div class="label_30">
                        <label class="label">
                            <span class="legend">Título:</span>
                            <input type="text" name="beneficio_title" value="" placeholder="Título" required/>
                        </label>
                        <label class="label">
                            <span class="legend">Descrição:</span>
                            <textarea name="beneficio_desc" value="" placeholder="Descrição" required></textarea>
                        </label>
                        <label class="label">
                            <span class="legend">Ícone: <small><a href="dashboard.php?wc=config/samples#icons" target="_blank">Consultar lista de ícones</a></small></span>
                            <input type="text" name="beneficio_icon" value="" placeholder="icon-icone" required/>
                        </label>
                    </div>

                    <div class="wc_actions" style="text-align: right;">
                        <button name="public" value="1" class="btn btn_green icon-share">Enviar</button>
                        <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    </div>
                </form>
            </div>
        </div>
        <div class="panel_footer_external"></div>
    </div>

    <!--LIST-->
    <div class="beneficios box box100" id="base">
        <?php
        $getPage = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT);
        $Page = ($getPage ? $getPage : 1);
        $Paginator = new Pager('dashboard.php?wc=servicos/beneficios&pg=', '<<', '>>', 5);
        $Paginator->ExePager($Page, 12);

        $Read->ExeRead(DB_SERVICOS_BENEFICIOS, "WHERE servico_id = :id ORDER BY beneficio_date DESC LIMIT :limit OFFSET :offset", "id={$PostId}&limit={$Paginator->getLimit()}&offset={$Paginator->getOffset()}");
        if (!$Read->getResult()):
            $Paginator->ReturnPage();
            echo Erro("<span class='al_center icon-notification'>Ainda não existem benefícios cadastrados {$Admin['user_name']}. Comece agora mesmo cadastrando seu primeiro!</span>", E_USER_NOTICE);
        else:
            foreach ($Read->getResult() as $Pdt):
                echo"<article class='box box25 beneficios_single' id='{$Pdt['beneficio_id']}'>
                        <header class='wc_normalize_height'>
                             <div class='info'>
                                <p><b>Título.</b> {$Pdt['beneficio_title']}</p>
                                <p><b>Desc.</b> " . Check::Words($Pdt['beneficio_desc'], 6) . "</p>
                                <p><b>Icone.</b> <i class='{$Pdt['beneficio_icon']}'></i></p>                           
                            </div>
                        </header>
                        <footer class='al_center'>
                            <span class='btn btn_blue icon-pencil icon-notext jbs_action' cc='Servicos' ca='GetEdit' rel='{$Pdt['beneficio_id']}'></span>
                            <span rel='beneficios_single' class='j_delete_action icon-cross icon-notext btn btn_red' id='{$Pdt['beneficio_id']}'></span>
                            <span rel='beneficios_single' callback='Servicos' callback_action='delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$Pdt['beneficio_id']}'>Deletar Benefício?</span>
                        </footer>              
                    </article>";

            endforeach;
            $Paginator->ExePaginator(DB_SERVICOS_BENEFICIOS);
            echo $Paginator->getPaginator();
        endif;
        ?>
    </div>
</div>