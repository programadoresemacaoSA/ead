<?php

session_start();
require '../../_app/Config.inc.php';
$NivelAcess = LEVEL_WC_CLASS;

if (!APP_CLASS || empty($_SESSION['userLogin']) || empty($_SESSION['userLogin']['user_level']) || $_SESSION['userLogin']['user_level'] < $NivelAcess):
    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    echo json_encode($jSON);
    die;
endif;

usleep(50000);

//DEFINE O CALLBACK E RECUPERA O POST
$jSON = null;
$CallBack = 'Aulas';
$PostData = filter_input_array(INPUT_POST, FILTER_DEFAULT);

//VALIDA AÇÃO
if ($PostData && $PostData['callback_action'] && $PostData['callback'] == $CallBack):
    //PREPARA OS DADOS
    $Case = $PostData['callback_action'];
    unset($PostData['callback'], $PostData['callback_action']);

    // AUTO INSTANCE OBJECT READ
    if (empty($Read)):
        $Read = new Read;
    endif;

    // AUTO INSTANCE OBJECT CREATE
    if (empty($Create)):
        $Create = new Create;
    endif;

    // AUTO INSTANCE OBJECT UPDATE
    if (empty($Update)):
        $Update = new Update;
    endif;
    
    // AUTO INSTANCE OBJECT DELETE
    if (empty($Delete)):
        $Delete = new Delete;
    endif;

    //SELECIONA AÇÃO
    switch ($Case):
        //DELETE

        case 'delete':
            $PostData['class_id'] = $PostData['del_id'];

            $Read->FullRead("SELECT class_cover FROM " . DB_CLASS . " WHERE class_id = :ps", "ps={$PostData['class_id']}");
            if ($Read->getResult() && file_exists("../../uploads/{$Read->getResult()[0]['class_cover']}") && !is_dir("../../uploads/{$Read->getResult()[0]['class_cover']}")):
                unlink("../../uploads/{$Read->getResult()[0]['class_cover']}");
            endif;

            $Delete->ExeDelete(DB_CLASS, "WHERE class_id = :id", "id={$PostData['del_id']}");
            $Delete->ExeDelete(DB_COMMENTS, "WHERE class_id = :id", "id={$PostData['del_id']}");
            $jSON['success'] = true;
            break;

        //MANAGER
        case 'manage':
            $PostId = $PostData['class_id'];
            unset($PostData['class_id']);

            $Read->ExeRead(DB_CLASS, "WHERE class_id = :id", "id={$PostId}");
            $ThisPost = $Read->getResult()[0];

            $PostData['class_name'] = (!empty($PostData['class_name']) ? Check::Name($PostData['class_name']) : Check::Name($PostData['class_title']));
            $Read->ExeRead(DB_CLASS, "WHERE class_id != :id AND class_name = :name", "id={$PostId}&name={$PostData['class_name']}");
            if ($Read->getResult()):
                $PostData['class_name'] = "{$PostData['class_name']}-{$PostId}";
            endif;
            $jSON['name'] = $PostData['class_name'];

            if (!empty($_FILES['class_cover'])):
                $File = $_FILES['class_cover'];

                if ($ThisPost['class_cover'] && file_exists("../../uploads/{$ThisPost['class_cover']}") && !is_dir("../../uploads/{$ThisPost['class_cover']}")):
                    unlink("../../uploads/{$ThisPost['class_cover']}");
                endif;

                $Upload = new Upload('../../uploads/');
                $Upload->Image($File, $PostData['class_name'] . '-' . time(), IMAGE_W, "class");
                if ($Upload->getResult()):
                    $PostData['class_cover'] = $Upload->getResult();
                else:
                    $jSON['trigger'] = AjaxErro("<b class='icon-image'>ERRO AO ENVIAR CAPA:</b> Olá {$_SESSION['userLogin']['user_name']}, selecione uma imagem JPG ou PNG para enviar como capa!", E_USER_WARNING);
                    echo json_encode($jSON);
                    return;
                endif;
            else:
                unset($PostData['class_cover']);
            endif;

            $PostData['class_status'] = (!empty($PostData['class_status']) ? '1' : '0');
            $PostData['class_order'] = (!empty($PostData['class_order']) ? $PostData['class_order'] : null);
            $PostData['class_date'] = (!empty($PostData['class_date']) ? Check::Data($PostData['class_date']) : date('Y-m-d H:i:s'));

            if (isset($PostData['class_date_show'])):
                $PostData['class_date_show'] = (!empty($PostData['class_date_show']) && Check::Data($PostData['class_date_show']) ? Check::Data($PostData['class_date_show']) : null);
            endif;

            $Update->ExeUpdate(DB_CLASS, $PostData, "WHERE class_id = :id", "id={$PostId}");
            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>TUDO CERTO: </b> A Aula <b>{$PostData['class_title']}</b> foi atualizada com sucesso!");
            $jSON['view'] = BASE . "/campus/campus.php?wc=cursos/lives";
            break;

        case 'sendimage':
            $NewImage = $_FILES['image'];
            $Read->FullRead("SELECT class_title, class_name FROM " . DB_CLASS . " WHERE class_id = :id", "id={$PostData['class_id']}");
            if (!$Read->getResult()):
                $jSON['trigger'] = AjaxErro("<b class='icon-image'>ERRO AO ENVIAR IMAGEM:</b> Desculpe {$_SESSION['userLogin']['user_name']}, mas não foi possível identificar a aula vinculada!", E_USER_WARNING);
            else:
                $Upload = new Upload('../../uploads/');
                $Upload->Image($NewImage, $PostData['class_id'] . '-' . time(), IMAGE_W);
                if ($Upload->getResult()):
                    $PostData['image'] = $Upload->getResult();
                    $Create->ExeCreate(DB_CLASS_IMAGE, $PostData);
                    $jSON['tinyMCE'] = "<img title='{$Read->getResult()[0]['class_title']}' alt='{$Read->getResult()[0]['class_title']}' src='../uploads/{$PostData['image']}'/>";
                else:
                    $jSON['trigger'] = AjaxErro("<b class='icon-image'>ERRO AO ENVIAR IMAGEM:</b> Olá {$_SESSION['userLogin']['user_name']}, selecione uma imagem JPG ou PNG para inserir na aula!", E_USER_WARNING);
                endif;
            endif;
            break;

        case 'class_order':
            if (is_array($PostData['Data'])):
                foreach ($PostData['Data'] as $RE):
                    $UpdateClass = ['class_order' => $RE[1]];
                    $Update->ExeUpdate(DB_CLASS, $UpdateClass, "WHERE class_id = :class", "class={$RE[0]}");
                endforeach;

                $jSON['sucess'] = true;
            endif;
            break;
    endswitch;

    //RETORNA O CALLBACK
    if ($jSON):
        echo json_encode($jSON);
    else:
        $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS:</b> Desculpe. Mas uma ação do sistema não respondeu corretamente. Ao persistir, contate o desenvolvedor!', E_USER_ERROR);
        echo json_encode($jSON);
    endif;
else:
    //ACESSO DIRETO
    die('<br><br><br><center><h1>Acesso Restrito!</h1></center>');
endif;
