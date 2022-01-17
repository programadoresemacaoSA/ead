<?php
$AdminLevel = LEVEL_WC_LIVES;
if (!APP_LIVES || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
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

$LiveId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($LiveId):
    $Read->ExeRead(DB_LIVES, "WHERE live_id = :id", "id={$LiveId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);

        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar uma sala que não existe ou que foi removida recentemente!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=lives/home');
    endif;
else:

    $LiveCreate = [
        'live_name' => (LIVE_LINK_LIVES == 0 ? Check::NewPass(5, true, true, false) : ""),
        'live_date' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        'live_enddate' => date('Y-m-d H:i:s', strtotime('+7 days')),
        'live_create' => date('Y-m-d H:i:s'),
        'live_status' => 0,
    ];
    $Create->ExeCreate(DB_LIVES, $LiveCreate);
    header('Location: dashboard.php?wc=lives/create&id=' . $Create->getResult());
endif;

echo "<script src='_siswc/lives/wclive.admin.js'></script>";
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-podcast"><?= $live_title ? $live_title : 'Edite o Título da sua Live'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=lives/home">Lives</a>
            <span class="crumb">/</span>
            Gerenciar Live
        </p>
    </div>

    <div class="dashboard_header_search">
        <a href="dashboard.php?wc=lives/control&id=<?= $LiveId; ?>" class="btn btn_yellow icon-cog">Sala de controle</a>
        <a target="_blank" title="Ver no site" href="<?= BASE . "/" . LIVE_PATH; ?>/<?= $live_name; ?>"
           class="wc_view btn btn_green icon-eye">Ir para sala!</a>
    </div>
</header>

<div class="dashboard_content" style="background: #faf9f6;">

    <form class="auto_save" name="live_add" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="callback" value="Lives"/>
        <input type="hidden" name="callback_action" value="manage"/>
        <input type="hidden" name="live_id" value="<?= $LiveId; ?>"/>
        <input type="hidden" name="origem" value="create"/>
        <div class="box box70">
            <div class="box_content radius">
                <label class="label">
                    <span class="legend">Título da Aula:</span>
                    <input style="font-size: 1.2em;" type="text" name="live_title" value="<?= $live_title; ?>" placeholder="Título da Sala:" required/>
                </label>

                <label class="label">
                    <span class="legend">Subtítulo da Aula:</span>
                    <textarea style="font-size: 1em;" name="live_description" rows="3" placeholder="Sobre a Sala:" required><?= $live_description; ?></textarea>
                </label>

                <label class="label_50">
                    <?php if (LIVE_LINK_LIVES): ?>
                        <label class="label">
                            <span class="legend">Link da Sala:</span>
                            <input type="text" name="live_name" value="<?= $live_name; ?>" placeholder="Link da Sala:"/>
                        </label>
                    <?php endif; ?>

                    <label class="label">
                        <span class="legend">Id da Live (Youtube): <a class="icon-youtube" href="https://www.youtube.com/webcam" target="_blank" title="Criar Evento">Criar Evento</a></span>
                        <input type="text" name="live_video" value="<?= ($live_video ? $live_video : null); ?>" placeholder="Id da Live:"/>
                    </label>
                </label>

                <label class="label_50">
                    <label class="label">
                        <span class="legend">Capa da Live (1200x628px): <a class="icon-image" title="Ver Capa da Live" href="dashboard.php?wc=lives/home">VER CAPA DA LIVE</a></span>
                        <input type="file" style="padding: 6.75px;" class="wc_loadimage" name="live_cover"/>
                    </label>

                    <label class="label">
                        <span class="legend icon-fire">Código da Oferta: <small>(obrigatório para boas vindas)</small></span>
                        <input type="text" name="live_offer_sck" value="<?= ($live_offer_sck ? $live_offer_sck : null); ?>" placeholder="Invente um Código sem espaços" required/>
                    </label>
                </label>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">CTA | Texto do Botão:</span>
                        <input type="text" name="live_cta_text" value="<?= ($live_cta_text ? $live_cta_text : null); ?>" placeholder="Chamada para Ação:"/>
                    </label>

                    <label class="label">
                        <span class="legend">CTA | Link do Checkout:</span>
                        <input type="text" name="live_cta_link" value="<?= ($live_cta_link ? $live_cta_link : null); ?>" placeholder="Link da Oferta:"/>
                    </label>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Ícone da Oferta: <a class="icon-IcoMoon" target="_blank" title="Consultar Ícones" href="dashboard.php?wc=config/samples#icons">VER ÍCONES DISPONÍVEIS!</a></span>
                        <input type="text" name="live_cta_icon" value="<?= ($live_cta_icon ? $live_cta_icon : null); ?>" placeholder="Ícone da Oferta:"/>
                    </label>

                    <label class="label">
                        <span class="legend">Cor do Botão:</span>
                        <select style="padding: 9px;" name="live_cta_color">
                            <option selected disabled value="">Cor do Botão:</option>
                            <option <?= ($live_cta_color == 'blue' ? 'selected="selected"' : ''); ?> value="blue">Azul</option>
                            <option value="yellow" <?= ($live_cta_color == 'yellow' ? 'selected="selected"' : ''); ?>>Amarelo</option>
                            <option value="green" <?= ($live_cta_color == 'green' ? 'selected="selected"' : ''); ?>>Verde</option>
                            <option value="red" <?= ($live_cta_color == 'red' ? 'selected="selected"' : ''); ?>>Vermelho</option>
                        </select>
                    </label>
                </div>

                <div class="label_33">
                    <label class="label">
                        <span class="legend">Data da Aula:</span>
                        <input type="text" data-timepicker="true" readonly="readonly" class="jwc_datepicker" name="live_date" value="<?= (!empty($live_date) ? date('d/m/Y H:i:s', strtotime($live_date)) : date('d/m/Y H:i:s')); ?>" required/>
                    </label>

                    <label class="label">
                        <span class="legend">Habilitar Compartilhamento?</span>
                        <select name="live_share" style="padding: 9px;">
                            <option value="1" <?= ($live_share == '1' ? 'selected="selected"' : ''); ?>>Sim</option>
                            <option value="0" <?= ($live_share == '0' ? 'selected="selected"' : ''); ?>>Não</option>
                        </select>
                    </label>

                    <label class="label">
                        <span class="legend">Data Término da Aula: <span class="wc_tooltip icon-info"><span class="wc_tooltip_balloon">Data limite na qual o evento estará disponível. Deixe em brando para Sempre disponível</span></span></span>
                        <input type="text" class="jwc_datepicker" data-timepicker="true" readonly="readonly" name="live_enddate" value="<?= (!empty($live_enddate) ? date('d/m/Y H:i:s', strtotime($live_enddate)) : ""); ?>"/>
                    </label>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="box box30">
            <div class="box_content radius">
                <div class="post_create_cover">
                    <div class="upload_progress none">0%</div>
                    <?php
                    $LiveCover = (!empty($live_cover) && file_exists("../uploads/lives/{$live_cover}") && !is_dir("../uploads/lives/{$live_cover}") ? "uploads/lives/{$live_cover}" : 'admin/_img/no_image.jpg');
                    ?>
                    <img class="post_thumb live_cover" alt="Capa da Live" title="Capa da Live" src="../tim.php?src=<?= $LiveCover; ?>&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>" default="../tim.php?src=<?= $LiveCover; ?>&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>"/>
                </div>

                <label class="label m_top">
                    <span class="legend">Autenticação da Live:</span>
                    <select name="live_mode" class="live_mode" required="required">
                        <option selected disabled value="">Selecione o Tipo de Autenticação:</option>
                        <option value="1" <?= ($live_mode == 1 ? 'selected="selected"' : ''); ?>>Active Campaign</option>
                        <option value="2" <?= ($live_mode == 2 ? 'selected="selected"' : ''); ?>>Banco de Dados</option>
                        <option value="3" <?= ($live_mode == 3 ? 'selected="selected"' : ''); ?>>Login do Sistema</option>
                        <option value="4" <?= ($live_mode == 4 ? 'selected="selected"' : ''); ?>>MailChimp</option>
                    </select>
                    <div class="post_create_categories js_system m_top <?= $live_mode == 3 ? '' : 'none'; ?>">
                        <span class="legend">Habilitar live apenas para os cursos: <span class="wc_tooltip icon-info"><span class="wc_tooltip_balloon">Marque as caixas abaixo para limitar o acesso à sala apenas para membros matriculados em cursos específicos.</span></span></span>
                        <?php
                        $Read->FullRead("SELECT course_id, course_title FROM " . DB_EAD_COURSES);
                        if ($Read->getResult()):
                            foreach ($Read->getResult() as $Cursos):
                                echo "<p class='post_create_cat'><label class='label_check'><input type='checkbox' name='live_courses[]' value='{$Cursos['course_id']}'";
                                if (in_array($Cursos['course_id'], explode(',', $live_courses))):
                                    echo " checked";
                                endif;
                                echo "> {$Cursos['course_title']}</label></p>";
                            endforeach;
                            else:
                                echo "<small>Opss você ainda não cadastrou nenhum curso.</small>";
                        endif;
                        ?>
                    </div>
                </label>

                <?php
                if (!empty(ACTIVECAMPAIGN_HOST) && !empty(ACTIVECAMPAIGN_APIKEY)):
                    ?>
                    <div class="js_active <?= $live_mode == 1 ? '' : 'none'; ?>">
                        <h4>Configuração do Active Campaing</h4>
                        <label class="label m_top">
                            <span class="legend">ID Lista: <span class="icon-info icon-notext wc_tooltip"><span class="wc_tooltip_balloon">Informe o id (ou ids separado por vírgula) das listas do Active Campaing as quais você deseja cadastrar!</span></span></span>
                            <input type="text" name="live_lista_ac" value="<?= $live_lista_ac; ?>" placeholder="Ex: 1,2,3" />
                        </label>

                        <label class="label">
                            <span class="legend">TAG</span>
                            <input type="text" disabled value="<?= ($live_name ? "live-{$live_name}" : null); ?>"/>
                        </label>
                    </div>
                <?php
                endif;
                
                if (!empty(MAILCHIMP_API_KEY)):
                    ?>
                    <div class="js_mailchimp <?= $live_mode == 4 ? '' : 'none'; ?>">
                        <h4>Configuração do MailChimp</h4>
                        <label class="label m_top">
                            <span class="legend">Id da Lista Mailchimp: <span class="icon-info icon-notext wc_tooltip"><span class="wc_tooltip_balloon">Informe o id da lista do MailChimp a qual você deseja cadastrar os inscritos que se cadastrarem na Live</span></span></span>
                            <input type="text" name="live_lista_mailchimp" value="<?= $live_lista_mailchimp; ?>" placeholder="Ex: ca6f0b36db" />
                        </label>
                    </div>
                    <?php
                endif;
                ?>
                <div class="al_center">
                    <label class="label_check label_publish <?= ($live_status == 1 ? 'active' : ''); ?>">
                        <input style="margin-top: -1px;" type="checkbox" value="1" name="live_status" <?= ($live_status == 1 ? 'checked' : ''); ?>> Habilitar Agora!
                    </label>
                    <button name="public" value="1" class="btn btn_green icon-share">ATUALIZAR</button>
                    <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </form>
</div>

<!--<script src="_siswc/lives/wclive.admin.js"></script>-->