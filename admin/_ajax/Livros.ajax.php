<?php

session_start();
require '../../_app/Config.inc.php';
$NivelAcess = LEVEL_WC_LIVROS;

if (!APP_LIVROS || empty($_SESSION['userLogin']) || empty($_SESSION['userLogin']['user_level']) || $_SESSION['userLogin']['user_level'] < $NivelAcess):
    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    echo json_encode($jSON);
    die;
endif;

usleep(50000);

//DEFINE O CALLBACK E RECUPERA O POST
$jSON = null;
$CallBack = 'Livros';
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
            $PostData['livro_id'] = $PostData['del_id'];

//            $Read->FullRead("SELECT livro_id FROM " . DB_LIVROS_BENEFICIOS . " WHERE livro_id=:id", "id={$PostData['livro_id']}");
//            if ($Read->getResult()):
//                $jSON['trigger'] = AjaxErro("<b class='icon-warning'>ERRO AO DELETAR:</b> Para deletar um Livro, antes é preciso deletar todos os benefícios do mesmo!", E_USER_WARNING);
//            else:
                $Read->FullRead("SELECT livro_cover FROM " . DB_LIVROS . " WHERE livro_id = :ps", "ps={$PostData['livro_id']}");
                if ($Read->getResult() && file_exists("../../uploads/{$Read->getResult()[0]['livro_cover']}") && !is_dir("../../uploads/{$Read->getResult()[0]['livro_cover']}")):
                    unlink("../../uploads/{$Read->getResult()[0]['livro_cover']}");
                endif;

                $Read->FullRead("SELECT image FROM " . DB_LIVROS_IMAGE . " WHERE livro_id = :ps", "ps={$PostData['livro_id']}");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $PostImage):
                        $ImageRemove = "../../uploads/{$PostImage['image']}";
                        if (file_exists($ImageRemove) && !is_dir($ImageRemove)):
                            unlink($ImageRemove);
                        endif;
                    endforeach;
                endif;

                $Delete->ExeDelete(DB_LIVROS, "WHERE livro_id = :id", "id={$PostData['del_id']}");
                $Delete->ExeDelete(DB_LIVROS_BENEFICIOS, "WHERE beneficio_id = :id", "id={$PostData['livro_id']}");
                $Delete->ExeDelete(DB_LIVROS_IMAGE, "WHERE livro_id = :id", "id={$PostData['livro_id']}");
                $jSON['success'] = true;
            break;

        case 'manager':
            $PostId = $PostData['livro_id'];
            unset($PostData['livro_id']);

            $Read->ExeRead(DB_LIVROS, "WHERE livro_id = :id", "id={$PostId}");
            $ThisPost = $Read->getResult()[0];

            $PostData['livro_name'] = (!empty($PostData['livro_name']) ? Check::Name($PostData['livro_name']) : Check::Name($PostData['livro_title']));
            $Read->ExeRead(DB_LIVROS, "WHERE livro_id != :id AND livro_name = :name", "id={$PostId}&name={$PostData['livro_name']}");
            if ($Read->getResult()):
                $PostData['livro_name'] = "{$PostData['livro_name']}-{$PostId}";
            endif;
            $jSON['name'] = $PostData['livro_name'];

            if (!empty($_FILES['livro_cover'])):
                $File = $_FILES['livro_cover'];

                if ($ThisPost['livro_cover'] && file_exists("../../uploads/{$ThisPost['livro_cover']}") && !is_dir("../../uploads/{$ThisPost['livro_cover']}")):
                    unlink("../../uploads/{$ThisPost['livro_cover']}");
                endif;

                $Upload = new Upload('../../uploads/');
                $Upload->Image($File, $PostData['livro_name'] . '-' . time(), IMAGE_W, "livros");
                if ($Upload->getResult()):
                    $PostData['livro_cover'] = $Upload->getResult();
                else:
                    $jSON['trigger'] = AjaxErro("<b class='icon-image'>ERRO AO ENVIAR CAPA:</b> Olá {$_SESSION['userLogin']['user_name']}, selecione uma imagem JPG ou PNG para enviar como capa!", E_USER_WARNING);
                    echo json_encode($jSON);
                    return;
                endif;
            else:
                unset($PostData['livro_cover']);
            endif;

            $PostData['livro_status'] = (!empty($PostData['livro_status']) ? '1' : '0');
            $PostData['livro_order'] = (!empty($PostData['livro_order']) ? $PostData['livro_order'] : null);
            $PostData['livro_date'] = (!empty($PostData['livro_date']) ? Check::Data($PostData['livro_date']) : date('Y-m-d H:i:s'));

            $Update->ExeUpdate(DB_LIVROS, $PostData, "WHERE livro_id = :id", "id={$PostId}");
            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>TUDO CERTO: </b> O Livro <b>{$PostData['livro_title']}</b> foi atualizado com sucesso!");
            $jSON['view'] = BASE . "/campus/campus.php?wc=cursos/livros";
            break;

        case 'sendimage':
            $NewImage = $_FILES['image'];
            $Read->FullRead("SELECT livro_title, livro_name FROM " . DB_LIVROS . " WHERE livro_id = :id", "id={$PostData['livro_id']}");
            if (!$Read->getResult()):
                $jSON['trigger'] = AjaxErro("<b class='icon-image'>ERRO AO ENVIAR IMAGEM:</b> Desculpe {$_SESSION['userLogin']['user_name']}, mas não foi possível identificar o post vinculado!", E_USER_WARNING);
            else:
                $Upload = new Upload('../../uploads/');
                $Upload->Image($NewImage, $PostData['livro_id'] . '-' . time(), IMAGE_W, "livros");
                if ($Upload->getResult()):
                    $PostData['image'] = $Upload->getResult();
                    $Create->ExeCreate(DB_LIVROS_IMAGE, $PostData);
                    $jSON['tinyMCE'] = "<img title='{$Read->getResult()[0]['livro_title']}' alt='{$Read->getResult()[0]['livro_title']}' src='../uploads/{$PostData['image']}'/>";
                else:
                    $jSON['trigger'] = AjaxErro("<b class='icon-image'>ERRO AO ENVIAR IMAGEM:</b> Olá {$_SESSION['userLogin']['user_name']}, selecione uma imagem JPG ou PNG para inserir no post!", E_USER_WARNING);
                endif;
            endif;
            break;

        case 'beneficio_manage':
            $BenId = (!empty($PostData['beneficio_id']) ? $PostData['beneficio_id'] : null);

            if (empty($PostData['beneficio_title']) || empty($PostData['beneficio_desc']) || empty($PostData['beneficio_icon'])):
                $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS, Não foi possivel cadastrar!</b> Desculpe. Mas aparentemente existe campos em branco! Por favor verifique e tente novamente.', E_USER_WARNING);
            else:
                if (empty($BenId)):
                    //Realiza Cadastro
                    $PostData['beneficio_date'] = date("Y-m-d H:i:s");

                    $Create->ExeCreate(DB_LIVROS_BENEFICIOS, $PostData);
                    $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>Cadastro realizado!</b><br>{$_SESSION['userLogin']['user_name']}, o cadastro foi realizado com sucesso.");
                else:
                    //Atualiza Cadastro
                    $Read->ExeRead(DB_LIVROS_BENEFICIOS, "WHERE beneficio_id = :id", "id={$BenId}");
                    if (!$Read->getResult()):
                        $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS, Não foi possivel atualizar!</b> Desculpe. Mas não encontramos o benefício que deseja atualizar.', E_USER_WARNING);
                        echo json_encode($jSON);
                        return;
                    endif;

                    $Update->ExeUpdate(DB_LIVROS_BENEFICIOS, $PostData, "WHERE beneficio_id = :id", "id={$BenId}");
                    $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>TUDO CERTO: </b> Os benefícios para o serviço foi atualizado com sucesso!");
                endif;

                //RealTime
                $jSON['divcontent']['#base'] = null;
                $getPage = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT);
                $Page = ($getPage ? $getPage : 1);
                $Paginator = new Pager('dashboard.php?wc=livros/beneficios&pg=', '<<', '>>', 5);
                $Paginator->ExePager($Page, 12);

                $Read->ExeRead(DB_LIVROS_BENEFICIOS, "WHERE livro_id = :id ORDER BY beneficio_date DESC LIMIT :limit OFFSET :offset", "id={$PostData['livro_id']}&limit={$Paginator->getLimit()}&offset={$Paginator->getOffset()}");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $Pdt):
                        $jSON['divcontent']['#base'] .="<article class='box box25 beneficios_single' id='{$Pdt['beneficio_id']}'>
                        <header>
                             <div class='info'>
                                <p><b>Título.</b> {$Pdt['beneficio_title']}</p>
                                <p><b>Desc.</b> " . Check::Words($Pdt['beneficio_desc'], 6) . "</p>
                                <p><b>Icone.</b> <i class='{$Pdt['beneficio_icon']}'></i></p>                           
                            </div>
                        </header>
                        <footer class='al_center'>
                            <span class='btn btn_blue icon-pencil icon-notext jbs_action' cc='Livros' ca='GetEdit' rel='{$Pdt['beneficio_id']}'></span>
                            <span rel='beneficios_single' class='j_delete_action icon-cross icon-notext btn btn_red' id='{$Pdt['beneficio_id']}'></span>
                            <span rel='beneficios_single' callback='Livros' callback_action='delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$Pdt['beneficio_id']}'>Deletar Benefício?</span>
                        </footer>              
                    </article>";

                    endforeach;
                    $Paginator->ExePaginator(DB_LIVROS_BENEFICIOS);
                    $jSON['divcontent']['#base'] .= $Paginator->getPaginator();
                endif;

                //Actions
                $jSON['divremove'] = "#cadastro";
            endif;
            break;

        //Obtem Cadastros
        case "GetEdit":
            $BenId = $PostData['action_id'];
            $Read->ExeRead(DB_LIVROS_BENEFICIOS, "WHERE beneficio_id = :id", "id={$BenId}");
            if ($Read->getResult()):
                $Data = $Read->getResult()[0];


                $jSON['divcontent']['.thumb_controll'] = "";
                $jSON['form'] = ".j_beneficios";
                $jSON['result'] = $Data;
                $jSON['fadein'] = "#cadastro";
            else:
                $jSON['trigger'] = AjaxErro("<b class='icon-warning'>OPSS Não encontramos:</b> Não encontramos o benefício '<b>{$PostData['beneficio_title']}</b>'!", E_USER_WARNING);
            endif;
            break;

        case 'livros_order':
            if (is_array($PostData['Data'])):
                foreach ($PostData['Data'] as $RE):
                    $UpdatesLivro = ['livro_order' => $RE[1]];
                    $Update->ExeUpdate(DB_LIVROS, $UpdatesLivro, "WHERE livro_id = :livro", "livro={$RE[0]}");
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
