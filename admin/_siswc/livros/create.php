<?php
$AdminLevel = LEVEL_WC_LIVROS;
if (!APP_LIVROS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

// AUTO INSTANCE OBJECT CREATE
if (empty($Create)):
    $Create = new Create;
endif;

$PostId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($PostId):
    $Read->ExeRead(DB_LIVROS, "WHERE livro_id = :id", "id={$PostId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um Livro que não existe ou que foi removido recentemente!";
        header('Location: dashboard.php?wc=livros/home');
    endif;
else:
    $PostCreate = ['livro_date' => date('Y-m-d H:i:s'), 'livro_type' => 'post', 'livro_status' => 0, 'livro_author' => $Admin['user_id']];
    $Create->ExeCreate(DB_LIVROS, $PostCreate);
    header('Location: dashboard.php?wc=livros/create&id=' . $Create->getResult());
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-new-tab"><?= $livro_title ? $livro_title : "Novo Livro"; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=livros/home">Livros</a>
            <span class="crumb">/</span>
            Gerenciar Livro
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Novo Livro" href="dashboard.php?wc=livros/create" class="btn btn_green icon-plus">Novo Livro!</a>
    </div>
</header>

<div class="workcontrol_imageupload none" id="post_control">
    <div class="workcontrol_imageupload_content">
        <form name="workcontrol_post_upload" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Livros"/>
            <input type="hidden" name="callback_action" value="sendimage"/>
            <input type="hidden" name="livro_id" value="<?= $PostId; ?>"/>
            <div class="upload_progress none" style="padding: 5px; background: #00B594; color: #fff; width: 0%; text-align: center; max-width: 100%;">0%</div>
            <div style="overflow: auto; max-height: 300px;">
                <img class="image image_default" alt="Nova Imagem" title="Nova Imagem" src="../tim.php?src=admin/_img/no_image.jpg&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>" default="../tim.php?src=admin/_img/no_image.jpg&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>"/>
            </div>
            <div class="workcontrol_imageupload_actions">
                <input class="wc_loadimage" type="file" name="image" required/>
                <span class="workcontrol_imageupload_close icon-cancel-circle btn btn_red" id="post_control" style="margin-right: 8px;">Fechar</span>
                <button class="btn btn_green icon-image">Enviar e Inserir!</button>
                <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
            </div>
            <div class="clear"></div>
        </form>
    </div>
</div>

<div class="dashboard_content">
    <form class="auto_save" name="post_create" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="callback" value="Livros"/>
        <input type="hidden" name="callback_action" value="manager"/>
        <input type="hidden" name="livro_id" value="<?= $PostId; ?>"/>

        <div class="box box70">
            <div class="panel_header default">
                <h2 class="icon-blog">Dados sobre o Livro</h2>
            </div>
            <div class="panel">
                <label class="label">
                    <span class="legend">Título do Livro:</span>
                    <input style="font-size: 1.4em;" type="text" name="livro_title" value="<?= $livro_title; ?>" required/>
                </label>

                <label class="label">
                    <span class="legend">Descrição do Livro:</span>
                    <textarea class="work_mce" rows="5" name="livro_content"><?= $livro_content; ?></textarea>
                </label>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Categoria:</span>
                        <input type="text" name="livro_category" value="<?= $livro_category; ?>" required/>
                    </label>

                    <label class="label">
                        <span class="legend">Autor:</span>
                        <input type="text" name="livro_book_author" value="<?= $livro_book_author; ?>" required/>
                    </label>
                </div>

                <label class="label">
                    <span class="legend">Link de Compra:</span>
                    <input type="url" name="livro_link" value="<?= $livro_link; ?>" required/>
                </label>
            </div>
        </div>

        <div class="box box30">
            <div class="panel_header default">
                <h2 class="icon-image">Capa do Livro</h2>
            </div>
            <div class="panel">
                <div class="post_create_cover al_center m_botton">
                    <div class="upload_progress none">0%</div>
                    <?php
                    $PostCover = (!empty($livro_cover) && file_exists("../uploads/{$livro_cover}") && !is_dir("../uploads/{$livro_cover}") ? "uploads/{$livro_cover}" : 'admin/_img/no_image.jpg');
                    ?>
                    <img class="post_thumb post_cover" alt="Capa do Livro" title="Capa do Livro" src="../tim.php?src=<?= $PostCover; ?>&w=144&h=204" default="../tim.php?src=<?= $PostCover; ?>&w=144&h=204"/>
                </div>

                <label class="label">
                    <span class="legend">Capa: (JPG 144x204px)</span>
                    <input type="file" class="wc_loadimage" name="livro_cover"/>
                </label>

                <div class="box box100">
                    <div class="wc_actions" style="text-align: center">
                        <label class="label_check label_publish <?= ($livro_status == 1 ? 'active' : ''); ?>"><input style="margin-top: -1px;" type="checkbox" value="1" name="livro_status" <?= ($livro_status == 1 ? 'checked' : ''); ?>> Publicar Agora!</label>
                        <button name="public" value="1" class="btn btn_green icon-share">ATUALIZAR</button>
                        <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    </div>
                </div>
                
                <div class="clear"></div>
            </div>
        </div>
    </form>
</div>