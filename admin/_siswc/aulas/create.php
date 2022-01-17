<?php
$AdminLevel = LEVEL_WC_CLASS;
if (!APP_CLASS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
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
    $Read->ExeRead(DB_CLASS, "WHERE class_id = :id", "id={$PostId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar uma Aula que não existe ou que foi removida recentemente!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=aulas/home');
    endif;
else:
    $ClassCreate = ['class_date' => date('Y-m-d H:i:s'), 'class_status' => 0];
    $Create->ExeCreate(DB_CLASS, $ClassCreate);
    header('Location: dashboard.php?wc=aulas/create&id=' . $Create->getResult());
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-play2"><?= $class_title ? $class_title : 'Nova Aula'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=aulas/home">Central de Aulas</a>
            <span class="crumb">/</span>
            Gerenciar Aulas
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Nova Aula" href="dashboard.php?wc=aulas/create" class="btn btn_green icon-plus">Nova Aula!</a>
    </div>
</header>

<div class="workcontrol_imageupload none" id="post_control">
    <div class="workcontrol_imageupload_content">
        <form name="workcontrol_post_upload" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Aulas"/>
            <input type="hidden" name="callback_action" value="sendimage"/>
            <input type="hidden" name="class_id" value="<?= $PostId; ?>"/>
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

<div class="workcontrol_filesupload none" id="post_control">
    <div class="workcontrol_imageupload_content">
        <form name="workcontrol_post_upload" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Aulas"/>
            <input type="hidden" name="callback_action" value="sendfile"/>
            <input type="hidden" name="class_id" value="<?= $PostId; ?>"/>
            <div class="upload_progress none" style="padding: 5px; background: #00B594; color: #fff; width: 0%; text-align: center; max-width: 100%;">0%</div>
            <div class="workcontrol_imageupload_actions">
                <input type="text" name="name" placeholder="Baixar aquivo 1 *" required style="margin-bottom: 10px;"/>
                <input class="wc_loadmedia" type="file" name="arquivo" style="margin-bottom: 10px;" required/>
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
        <input type="hidden" name="callback" value="Aulas"/>
        <input type="hidden" name="callback_action" value="manage"/>
        <input type="hidden" name="class_id" value="<?= $PostId; ?>"/>

        <div class="box box70">
            <div class="panel_header default">
                <h2 class="icon-page-break">Insira as informações da Aula</h2>
            </div>

            <div class="panel">
                <label class="label">
                    <span class="legend">Título do Aula:</span>
                    <input style="font-size: 1.2em;"  type="text" name="class_title" value="<?= $class_title; ?>" placeholder="Título da Aula" required/>
                </label>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Capa: (JPG <?= IMAGE_W; ?>x<?= IMAGE_H; ?>px)</span>
                        <input style="padding: 7px;" type="file" class="wc_loadimage" name="class_cover"/>
                    </label>

                    <label class="label">
                        <span class="legend">Segmento:</span>
                        <select style="padding: 9.4px;" name="class_segment" required>
                            <option value="">Aula sem Segmentação!</option>
                            <?php
                            $Read->FullRead("SELECT segment_id, segment_title FROM " . DB_EAD_COURSES_SEGMENTS . " ORDER BY segment_order ASC, segment_title ASC");
                            if ($Read->getResult()):
                                foreach ($Read->getResult() as $ClassSegment):
                                    echo "<option";
                                    if ($ClassSegment['segment_id'] == $class_segment):
                                        echo " selected='selected'";
                                    endif;
                                    echo " value='{$ClassSegment['segment_id']}'>{$ClassSegment['segment_title']}</option>";
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </label>
                </div>

                <div class="label_33">
                    <label class="label">
                        <span class="legend icon-play">ID do Vídeo:</span>
                        <input style="padding: 9px;" type="text" name="class_video" value="<?= $class_video; ?>" placeholder="ID do Vídeo da Aula" required>
                    </label>

                    <label class="label">
                        <span class="legend">Data da Realização</span>
                        <input style="padding: 9px;" type="text" class="jwc_datepicker" data-timepicker="true" readonly="readonly" name="class_date_show" value="<?= $class_date_show ? date('d/m/Y H:i', strtotime($class_date_show)) : date('d/m/Y H:i'); ?>" required/>
                    </label>

                    <label class="label">
                        <span class="legend">Autor:</span>
                        <select style="padding: 10.2px;" name="class_author" required>
                            <option value="<?= $Admin['user_id']; ?>"><?= $Admin['user_name']; ?> <?= $Admin['user_lastname']; ?></option>
                            <?php
                            $Read->FullRead("SELECT user_id, user_name, user_lastname FROM " . DB_USERS . " WHERE user_level >= :lv AND user_id != :uid", "lv=6&uid={$Admin['user_id']}");
                            if ($Read->getResult()):
                                foreach ($Read->getResult() as $PostAuthors):
                                    echo "<option";
                                    if ($PostAuthors['user_id'] == $class_author):
                                        echo " selected='selected'";
                                    endif;
                                    echo " value='{$PostAuthors['user_id']}'>{$PostAuthors['user_name']} {$PostAuthors['user_lastname']}</option>";
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </label>
                </div>

                <label class="label">
                    <span class="legend">Descrição da Aula:</span>
                    <textarea class="work_mce" rows="50" name="class_content"><?= $class_content; ?></textarea>
                </label>

                <div class="panel">
                    <div class="wc_actions">
                        <label class="label_check label_publish <?= ($class_status == 1 ? 'active' : ''); ?>"><input style="margin-top: -1px;" type="checkbox" value="1" name="class_status" <?= ($class_status == 1 ? 'checked' : ''); ?>> Publicar Agora!</label>
                        <button name="public" value="1" class="btn btn_green icon-share">ATUALIZAR</button>
                        <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <div class="box box30">
            <div class="panel_header default">
                <h2 class="icon-image">Capa da Aula</h2>
            </div>

            <div class="post_create_cover">
                <div class="upload_progress none">0%</div>
                <?php
                $Class_Cover = (!empty($class_cover) && file_exists("../uploads/{$class_cover}") && !is_dir("../uploads/{$class_cover}") ? "uploads/{$class_cover}" : 'admin/_img/no_image.jpg');
                ?>
                <img class="post_thumb class_cover" alt="Capa da Aula" title="Capa Aula" src="../tim.php?src=<?= $Class_Cover; ?>&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>" default="../tim.php?src=<?= $Class_Cover; ?>&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>"/>
            </div>

            <div class="panel_header default m_top">
                <h2 class="icon-play2">Vídeo da Aula</h2>
            </div>

            <div class="j_content">
                <?php if ($class_video): ?>
                    <div class="embed-container">
                        <?php if (is_numeric($class_video)): ?>
                            <iframe src="https://player.vimeo.com/video/<?= $class_video; ?>?color=<?= EAD_VIMEO_COLOR; ?>&title=0&byline=0&portrait=0" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                        <?php else: ?>
                            <iframe width="640" height="360" src="https://www.youtube.com/embed/<?= $class_video; ?>?showinfo=0&amp;rel=0" frameborder="0" allowfullscreen></iframe>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>