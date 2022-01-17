<?php

session_start();
require '../../_app/Config.inc.php';

$Read = new Read;
$Update = new Update;
$Create = new Create;
$Delete = new Delete;
$Trigger = new Trigger;

usleep(50000);

//DEFINE O CALLBACK E RECUPERA O POST
$jSON = null;
$CallBack = 'Login';
$PostData = filter_input_array(INPUT_POST, FILTER_DEFAULT);

//VALIDA AÇÃO
if ($PostData && $PostData['callback_action'] && $PostData['callback'] == $CallBack):
    //PREPARA OS DADOS
    $Case = $PostData['callback_action'];
    unset($PostData['callback'], $PostData['callback_action']);

    //ELIMINA CÓDIGOS
    $PostData = array_map('strip_tags', $PostData);

    //SELECIONA AÇÃO
    switch ($Case):
        //LOGIN
        case 'login':
            $LoginTrue = false;
            $_SESSION['userLogin'] = null;

            if (in_array('', $PostData)):
                $jSON['trigger'] = $Trigger->notify("<b>OPPSSS:</b> Informe seu e-mail e senha para logar!", 'red', 'warning', 5000);
            else:
                if (!Check::Email($PostData['user_email']) || !filter_var($PostData['user_email'], FILTER_VALIDATE_EMAIL)):
                    $jSON['trigger'] = $Trigger->notify("<b>OPPSSS:</b> E-mail informado não é válido!", 'red', 'warning', 5000);
                elseif (strlen($PostData['user_password']) < 5):
                    $jSON['trigger'] = $Trigger->notify("<b>OPPSSS:</b> Senha informada não é compatível!", 'red', 'warning', 5000);
                else:
                    $Read = new Read;
                    $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_email = :email", "email={$PostData['user_email']}");
                    if (!$Read->getResult()):
                  $jSON['trigger'] = $Trigger->notify("<b>ERRO:</b> E-mail informado não é cadastrado!", 'red', 'warning', 5000);
                    else:
                        //CRIPTIGRAFA A SENHA
                        $PostData['user_password'] = hash('sha512', $PostData['user_password']);

                        $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_email = :email AND user_password = :pass", "email={$PostData['user_email']}&pass={$PostData['user_password']}");
                        if (!$Read->getResult()):
                            $jSON['trigger'] = $Trigger->notify("<b>ERRO:</b> E-mail e senha não conferem!", 'red', 'warning', 5000);
                        else:
                            $Read->ExeRead(DB_USERS, "WHERE user_email = :email AND user_password = :pass", "email={$PostData['user_email']}&pass={$PostData['user_password']}");
                            if (!$Read->getResult()):
                                $jSON['trigger'] = $Trigger->notify("<b>ERRO:</b> Você não tem permissão para acessar o painel!", 'red', 'warning', 5000);
                            else:
                                $Remember = (isset($PostData['user_remember']) ? 1 : null);
                                if ($Remember):
                                    setcookie('workcontrol', $PostData['user_email'], time() + 2592000, '/');
                                else:
                                    setcookie('workcontrol', '', 60, '/');
                                endif;

                                //ADICIONADA ESTA FUNÇÃO QUE ESTA INATIVA NA VERSÃO ORIGINAL
                                $UserLogin = $Read->getResult()[0];
                                if (!empty($UserLogin['user_blocking_reason'])):
                                    unset($UserLogin);
                                    $jSON['trigger'] = $Trigger->modal("Sua conta está bloqueada, {$Read->getResult()[0]['user_name']} :(", "Entre em contato através do e-mail <strong>" . SITE_ADDR_EMAIL . "</strong> ou por uns dos nossos canais de atendimento oficiais para maiores esclarecimentos.", "red", "sad");
                                    break;
                                endif;

                                if (EAD_STUDENT_MULTIPLE_LOGIN):
                                    $LoginTrue = true;
                                else:
                                    $LoginCookieFree = filter_input(INPUT_COOKIE, "wc_ead_login", FILTER_DEFAULT);
                                    $LoginTimeFree = (!empty($UserLogin['user_login']) ? $UserLogin['user_login'] + (EAD_STUDENT_MULTIPLE_LOGIN_BLOCK * 60) : 0);

                                    if (!$UserLogin['user_login_cookie'] || time() > $LoginTimeFree || ($LoginCookieFree && $LoginCookieFree == $UserLogin['user_login_cookie'])):
                                        $wc_ead_login_cookie = hash("sha512", time());
                                        $_SESSION['userLogin']['user_login_cookie'] = $wc_ead_login_cookie;
                                        setcookie('wc_ead_login', $wc_ead_login_cookie, time() + 2592000, '/');

                                        $UpdateUserLogin = ['user_lastaccess' => date('Y-m-d H:i:s'), 'user_login' => time(), 'user_login_cookie' => $wc_ead_login_cookie];
                                        $Update->ExeUpdate(DB_USERS, $UpdateUserLogin, "WHERE user_id = :user", "user={$UserLogin['user_id']}");

                                        $LoginTrue = true;
                                    else:
                                        $jSON['trigger'] = $Trigger->modal("Conta já conectada!", "Olá, <strong>{$UserLogin['user_name']}</strong>! Sua conta já esta conectada por outro dispositivo ou por outra pessoa. Isso não é permitido em nossa plataforma!<p>Caso tenha efetuado login de outro dispositivo, você pode conectar por ele agora!</p><p><strong class='icon-hour-glass'>Ou espere até as " . date("H\hi", $LoginTimeFree + 60) . " para conectar novamente!</strong></p><p class='icon-warning'>IMPORTANTE: Leia com atenção nossas regras para não ter sua conta bloqueada permanentemente!</p><p class='icon-info'>Caso alguém esteja conectado na sua conta o tempo de liberação deve aumentar!</p>", 'red', 'sad');
                                    endif;
                                endif;

                                //LOGIN EFFECT
                                if ($LoginTrue):
                                    $_SESSION['userLogin'] = $UserLogin;
                                    $jSON['trigger'] = $Trigger->notify("Olá <b>{$Read->getResult()[0]['user_name']},</b> Seja bem-vindo(a) de volta!", 'green', 'heart', 5000);
                                    $jSON['redirect'] = 'campus.php?wc=cursos/atividades';
                                endif;
                            endif;
                        endif;
                    endif;
                endif;
            endif;
            break;

        //STUDENT ACTIONS :: LOGIN FIX
        case 'wc_ead_login_fix':
            //MULTIPLE LOGIN
            if (!EAD_STUDENT_MULTIPLE_LOGIN):
                $wc_ead_login_cookie = filter_input(INPUT_COOKIE, "work_login", FILTER_DEFAULT);

                if (empty($_SESSION['userLogin']) || !$wc_ead_login_cookie):
                    unset($_SESSION['userLogin']);
                    $jSON['redirect'] = BASE . "/campus/login/multiple";
                else:
                    $Read->FullRead("SELECT user_login_cookie FROM " . DB_USERS . " WHERE user_id = :user", "user={$_SESSION['userLogin']['user_id']}");
                    if (!$Read->getResult() || $_SESSION['userLogin']['user_login_cookie'] != $Read->getResult()[0]['user_login_cookie']):
                        unset($_SESSION['userLogin']);
                        $jSON['redirect'] = BASE . "/campus/login/multiple";
                    else:
                        setcookie('work_login', $Read->getResult()[0]['user_login_cookie'], time() + 2592000, '/');
                    endif;
                endif;
            endif;

            //LOGIN FIX
            $jSON['login'] = true;
            break;

        //ALTERAÇÕES NO LOGIN
        case 'recover':
            if(empty($PostData['user_email'])):
                $jSON['trigger'] = $Trigger->notify("Erro ao recuperar. Por favor, informe seu e-mail de acesso para recuperar a senha!", 'red', 'warning', 3000);
            elseif (!Check::Email($PostData['user_email']) || !filter_var($PostData['user_email'], FILTER_VALIDATE_EMAIL)):
                $jSON['trigger'] = $Trigger->notify("E-mail Inválido. O e-mail informado parece não ter um formato válido :(", 'red', 'warning', 3000);
            else:

                $Read->ExeRead(DB_USERS, "WHERE user_email = :email", "email={$PostData['user_email']}");
                if (!$Read->getResult()):
                    $jSON['trigger'] = $Trigger->notify("Oops! E-mail não encontrado! Por favor, informe seu e-mail de acesso!", 'red', 'warning', 3000);
                else:

                    $rows = $Read->getResult()[0];

                    $ReadRecover = new Read;
                    $ReadRecover->ExeRead(DB_USERS_RECOVER, "WHERE status = :status AND user_id = :id", "status=1&id={$rows['user_id']}"); //VERIFICA SE EXISTE PARA ATUALIZAR (SE NÃO GRAVA)
                    if ($ReadRecover->getResult()): //ATUALIZA SE EXISTIR

                        $ArrayData = [
                            'code' => mb_strtoupper(Check::NewPass(8)), //CODE
                            'end' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                            'updated' => date('Y-m-d H:i:s')
                        ];
                        $Update->ExeUpdate(DB_USERS_RECOVER, $ArrayData, "WHERE status = :status AND id = :id", "status=1&id={$ReadRecover->getResult()[0]['id']}");
                    else: //GRAVA SE NÃO EXISTIR

                        $CreateNewRecover = [
                            'created' => date("Y-m-d H:i:s"), //DATA DE CADASTRO
                            'end' => date('Y-m-d H:i:s', strtotime('+1 day')), //DATA FINAL
                            'code' => mb_strtoupper(Check::NewPass(8)), //CODE
                            'user_id' => $rows['user_id'], //ID DO USUARIO
                            'status' => 1, //STATUS
                            'been' => 0 //ATUALIZADO
                        ];
                        $Create->ExeCreate(DB_USERS_RECOVER, $CreateNewRecover);
                    endif;

                    $ReadCode = new Read;
                    $ReadCode->FullRead("SELECT code FROM ". DB_USERS_RECOVER . " WHERE status = :status AND user_id = :id", "status=1&id={$rows['user_id']}");

                    require '../wc_ead.email.php';
                    $MailBody = "
                        <p style='font-size: 1.4em; text-align: center; margin: 30px auto 50px auto'>Sua senha já pode ser alterada!</p>
                        <p>Olá, {$Read->getResult()[0]['user_name']}, tudo certo?</p>
                        <p>Você solicitou a troca de senha em nossa Área de Membros. Para concluir sua solicitação é bem simples, basta clicar no link abaixo e informar seu código.</p>                                                
                        <p style='font-size: 1.2em;'><a href='" . BASE . "/campus/codigo.php' title='Alterar minha Senha Agora!'>ALTERAR MINHA SENHA AGORA!</a></p>
                        <p>Seu código é: <b style='color:#FF0000; font-family: Consolas; font-weight:bold;'> {$ReadCode->getResult()[0]['code']}</b></p>                      
                        <p>Depois, é só digitar e confirmar sua nova senha de acessso!</p>
                        <p>...</p>
                        <p>OBS.: Caso não tenha solicitado, favor ignore essa mensagem!</p>  
                        <p>IMPORTANTE: O link de recuperação é válido por 1 hora, e somente para o dispositivo que fez a solicitação!</p>
                        <p>...</p>
                        <p>Qualquer dúvida ou problema não deixe de entrar em contato pelo e-mail " . SITE_ADDR_EMAIL . ", ficamos a disposição!</p>
                        <p>Atenciosamente,</p>
                        <p>" . SITE_NAME . "!</p>
                    ";
                    $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                    $Email = new Email;
                    $Email->EnviarMontando("Instruções para redefinição da sua senha, {$Read->getResult()[0]['user_name']}!", $MailContent, MAIL_SENDER, MAIL_USER, "{$Read->getResult()[0]['user_name']} {$Read->getResult()[0]['user_lastname']}", $Read->getResult()[0]['user_email']);
                    $jSON['clear'] = true;
                    $jSON['trigger'] = $Trigger->modal("Instruções enviadas por e-mail.", "<b>Prezado, {$Read->getResult()[0]['user_name']}!</b> <p>As intruções junto com o código e link para recuperar sua senha foram enviadas para seu e-mail neste instante.</p><p class='icon-warning'><b>IMPORTANTE:</b> Para sua segurança o link de recuperação é válido por 1 hora e apenas para este dispositivo!</p><p>Qualquer problema não deixe de entrar em contato!</p>", "green", "envelop");
                endif;
            endif;
            break;

        case 'new_code':
            $UserCode = strip_tags(trim($PostData['code'])); //CODE
            unset($PostData['code']);

            if (in_array('', $PostData)):
                $jSON['trigger'] = $Trigger->notify("Oops! Preencha todos os campos para alterar sua senha!", 'red', 'warning', 3000);
            elseif (strlen($PostData['user_password']) < 5):
                $jSON['trigger'] = $Trigger->notify("Oops! A senha deve ter no mínimo 5 caracteres!", 'red', 'warning', 3000);
            elseif (isset($PostData['user_password']) && ($PostData['user_password'] != $PostData['user_password_re'])):
                $jSON['trigger'] = $Trigger->notify("Oops! Você informou 2 senhas diferentes. Por favor, informe a mesma senha!", 'red', 'warning', 3000);
            else:

                $ReadRecover = new Read;
                $ReadRecover->ExeRead(DB_USERS_RECOVER, "WHERE status = :status AND code = :id", "status=1&id={$UserCode}");
                if (!$ReadRecover->getResult()):
                    $jSON['trigger'] = $Trigger->notify("Oops! Seu código é inválido! Por favor, tente novamente ou gere um novo código para recuperar sua senha!", 'red', 'warning', 3000);
                    echo json_encode($jSON);
                    return;
                endif;

                unset($PostData['user_password_re']);

                $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$ReadRecover->getResult()[0]['user_id']}");
                if (!$Read->getResult()):
                    $jSON['trigger'] = $Trigger->notify("Oops! Não conseguimos trocar sua senha! Por favor, entre em contato com nossa central de atendimento!", 'red', 'warning', 3000);
                    echo json_encode($jSON);
                    return;
                endif;

                $rows = $Read->getResult()[0];
                $ReadRecover = $ReadRecover->getResult()[0];

                $newPassword = ['user_password' => hash('sha512', $PostData['user_password']), 'user_lastupdate' => date('Y-m-d H:i:s')];
                $Update->ExeUpdate(DB_USERS, $newPassword, "WHERE user_id = :id", "id={$rows['user_id']}"); //ATUALIZA A SENHA DO USUARIO

                $andRecover = ['been' => 1, 'status' => 2, 'updated' => date('Y-m-d H:i:s')]; //SIM
                $Update->ExeUpdate(DB_USERS_RECOVER, $andRecover, "WHERE status = :status AND id = :id", "status=1&id={$ReadRecover['id']}"); //ATUALIZA O RECOVER

                require '../wc_ead.email.php';
                $UserIp = $_SERVER['REMOTE_ADDR'];
                $BodyMail = "
                        <p>Tudo certo, {$rows['user_name']}!</p>                                                    
                        <p>Sua senha foi redefinida com sucesso!</p>                                                                                                                                                                                                               
                        <b>IP:</b> " . $UserIp . "<br/>
                        <b>Data:</b> " . date('d/m/Y', strtotime($rows['user_lastupdate'])) . "<br/><br/>                            
                        <b>NÃO FOI VOCÊ?</b><br/> É importante atualizar sua conta o quanto antes para manter seus dados seguros.<br/> Para isso <b>ACESSE SUA CONTA</b> e altere sua senha.<br/><br/>                          
                        <b>FOI VOCÊ?</b><br/> Então pode ignorar este e-mail, mas ele sempre será enviado como medida de segurança para que você tenha certeza que está tudo certo com sua conta. Como você sabe, nela existem seus dados pessoais.<br/><br/>                                                    
                        <b>DÚVIDAS, CRÍTICAS OU SUGESTÕES?</b> <br/>Estamos sempre à disposição para melhor atendê-los! Você sempre pode contar com nossa equipe de suporte!<br/><br/>
                        <p>Atenciosamente,</p>
                        <p>" . SITE_NAME . "!</p>        
                    ";

                $Mensagem = str_replace('#mail_body#', $BodyMail, $MailContent);

                $Email = new Email;
                $Email->EnviarMontando("Tudo certo, {$rows['user_name']}!", $Mensagem, MAIL_SENDER, MAIL_USER, $rows['user_name'], $rows['user_email']);

                $_SESSION['userLogin'] = $Read->getResult()[0];

                $jSON['trigger'] = $Trigger->notify("Sua senha foi alterada com sucesso!", 'green', 'happy', 3000);
                $jSON['redirect'] = BASE . "/campus/campus.php?wc=cursos/atividades";
            endif;
            break;
    endswitch;

    //RETORNA O CALLBACK
    if ($jSON):
        echo json_encode($jSON);
    else:
        $jSON['trigger'] = $Trigger->notify("<b class=\"icon-warning\">OPSS:</b> Desculpe. Mas uma ação do sistema não respondeu corretamente. Ao persistir, contate o desenvolvedor!", 'red', 'warning', 5000);
        echo json_encode($jSON);
    endif;
else:
    //ACESSO DIRETO
    die('<br><br><br><center><h1>Acesso Restrito!</h1></center>');
endif;
