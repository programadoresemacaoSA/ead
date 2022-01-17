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
$CallBack = 'Users';
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
    $Upload = new Upload('../../uploads/');


    //SELECIONA AÇÃO
    switch ($Case):
        case 'manager':
            $UserId = $PostData['user_id'];
            unset($PostData['user_id'], $PostData['user_thumb']);

            $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_email = :email AND user_id != :id", "email={$PostData['user_email']}&id={$UserId}");
            if ($Read->getResult()):
                $jSON['trigger'] = $Trigger->notify("<b>Opss:</b> Olá, {$_SESSION['userLogin']['user_name']}. O e-mail <b>{$PostData['user_email']}</b> já está cadastrado na conta de outro usuário!", 'red', 'warning', 3000);
            else:
                $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_document = :dc AND user_id != :id", "dc={$PostData['user_document']}&id={$UserId}");
                if ($Read->getResult()):
                    $jSON['trigger'] = $Trigger->notify("<b>Opss:</b> Olá, {$_SESSION['userLogin']['user_name']}. O CPF <b>{$PostData['user_document']}</b> já está cadastrado na conta de outro usuário!", 'red', 'warning', 3000);
                else:
                    if (Check::CPF($PostData['user_document']) != true):
                        $jSON['trigger'] = $Trigger->notify("<b>Opss:</b> Olá, {$_SESSION['userLogin']['user_name']}. O CPF <b>{$PostData['user_document']}</b> informado não é válido!", 'red', 'warning', 3000);
                        echo json_encode($jSON);
                        return;
                    endif;

                    if (!empty($_FILES['user_thumb'])):
                        $UserThumb = $_FILES['user_thumb'];
                        $Read->FullRead("SELECT user_thumb FROM " . DB_USERS . " WHERE user_id = :id", "id={$UserId}");
                        if ($Read->getResult()):
                            if (file_exists("../../uploads/{$Read->getResult()[0]['user_thumb']}") && !is_dir("../../uploads/{$Read->getResult()[0]['user_thumb']}")):
                                unlink("../../uploads/{$Read->getResult()[0]['user_thumb']}");
                            endif;
                        endif;

                        $Upload->Image($UserThumb, $UserId . "-" . Check::Name($PostData['user_name'] . $PostData['user_lastname']) . '-' . time(), 600);
                        if ($Upload->getResult()):
                            $PostData['user_thumb'] = $Upload->getResult();
                        else:
                            $jSON['trigger'] = $Trigger->notify("<b>ERRO AO ENVIAR FOTO:</b> Olá, {$_SESSION['userLogin']['user_name']}, selecione uma imagem JPG ou PNG para enviar como foto!", 'red', 'warning', 3000);
                            echo json_encode($jSON);
                            return;
                        endif;
                    endif;

                    if (!empty($PostData['user_password'])):
                        if (strlen($PostData['user_password']) >= 5):
                            $PostData['user_password'] = hash('sha512', $PostData['user_password']);
                        else:
                            $jSON['trigger'] = $Trigger->notify("<b>ERRO DE SENHA:</b> Olá, {$_SESSION['userLogin']['user_name']}, a senha deve ter no mínimo 5 caracteres para ser redefinida!", 'red', 'warning', 3000);
                            echo json_encode($jSON);
                            return;
                        endif;
                    else:
                        unset($PostData['user_password']);
                    endif;

                    if ($UserId == $_SESSION['userLogin']['user_id']):
                        if ($PostData['user_level'] != $_SESSION['userLogin']['user_level']):
                            $jSON['trigger'] = $Trigger->notify("<b>PERFIL ATUALIZADO COM SUCESSO:</b> Olá, {$_SESSION['userLogin']['user_name']}, seus dados foram atualizados com sucesso!<p class='icon-warning'>Seu nível de usuário não foi alterado pois não é permitido atualizar o próprio nível de acesso!</p>", 'green', 'checkmark', 5000);
                        else:
                            $jSON['trigger'] = $Trigger->notify("<b>PERFIL ATUALIZADO COM SUCESSO:</b> Olá, {$_SESSION['userLogin']['user_name']}, seus dados foram atualizados com sucesso!", 'green', 'checkmark', 3000);
                        endif;
                        $SesseionRenew = true;
                        unset($PostData['user_level']);
                    elseif ($PostData['user_level'] > $_SESSION['userLogin']['user_level']):
                        $PostData['user_level'] = $_SESSION['userLogin']['user_level'];
                        $jSON['trigger'] = $Trigger->notify("<b>TUDO CERTO:</b> Olá, {$_SESSION['userLogin']['user_name']}. O usuário {$PostData['user_name']} {$PostData['user_lastname']} foi atualizado com sucesso!<p class='icon-warning'>Você não pode criar usuários com nível de acesso maior que o seu. Então o nível gravado foi " . getWcLevel($PostData['user_level']) . "!</p>", 'green', 'checkmark', 5000);
                    else:
                        $jSON['trigger'] = $Trigger->notify("<b>TUDO CERTO:</b> Olá, {$_SESSION['userLogin']['user_name']}. O usuário {$PostData['user_name']} {$PostData['user_lastname']} foi atualizado com sucesso!", 'green', 'checkmark', 3000);
                    endif;

                    $PostData['user_datebirth'] = (!empty($PostData['user_datebirth']) ? Check::Nascimento($PostData['user_datebirth']) : null);

                    //ATUALIZA USUÁRIO
                    $Update->ExeUpdate(DB_USERS, $PostData, "WHERE user_id = :id", "id={$UserId}");
                    if (!empty($SesseionRenew)):
                        $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$UserId}");
                        if ($Read->getResult()):
                            $_SESSION['userLogin'] = $Read->getResult()[0];
                        endif;
                    endif;
                endif;
            endif;
            break;

        case 'addr_add':
            $AddrId = $PostData['addr_id'];
            unset($PostData['addr_id']);

            $Update->ExeUpdate(DB_USERS_ADDR, $PostData, "WHERE addr_id = :addr", "addr={$AddrId}");
            $jSON['trigger'] = $Trigger->notify("<b>TUDO CERTO! Seu endereço foi atualizado com sucesso!</b>", 'green', 'checkmark', 3000);
            break;

        case 'addr_delete':
            $Read->ExeRead(DB_ORDERS, "WHERE order_addr = :addr", "addr={$PostData['del_id']}");
            if ($Read->getResult()):
                $jSON['trigger'] = $Trigger->notify("<b>ERRO AO DELETAR:</b> Olá, {$_SESSION['userLogin']['user_name']}, deletar um endereço vinculado a pedidos não é permitido pelo sistema!", 'red', 'warning', 3000);
            else:
                $Delete->ExeDelete(DB_USERS_ADDR, "WHERE addr_id = :addr", "addr={$PostData['del_id']}");
                $jSON['sucess'] = true;
            endif;
            break;

        //ALTERAÇÕES NO LOGIN
        case 'wc_ead_register':
            if (in_array("", $PostData)):
                $jSON['trigger'] = $Trigger->notify("Erro ao Cadastrar: Por favor, preencha todos os campos para criar sua conta! ", 'red', 'warning', 5000);
            elseif (!Check::Email($PostData['user_email']) || !filter_var($PostData['user_email'], FILTER_VALIDATE_EMAIL)):
                $jSON['trigger'] = $Trigger->notify("Digite um e-mail válido! Por favor, informe um e-mail válido para se cadastrar.", 'red', 'warning', 5000);
            else:
                //READ USER_EMAIL
                $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_email = :email", "email={$PostData['user_email']}");
                if ($Read->getResult()):
                    $jSON['trigger'] = $Trigger->notify("E-mail já cadastrado! O e-mail informado já esta cadastrado! <a href='" . BASE . "/campus/recuperar.php' title='Recuperar Senha'>Recupere sua senha aqui!</a>", 'red', 'warning', 5000);
                else:
                    $wcActiveCode = base64_encode("{$PostData['user_name']}&{$PostData['user_lastname']}&{$PostData['user_email']}");

                    require '../wc_ead.email.php';
                    $MailBody = "
                        <p style='font-size: 1.4em; text-align: center; margin: 30px auto 50px auto'>Chegou a hora de ativar sua conta!</p>
                        <p>Olá, {$PostData['user_name']}!</p>
                        <p>Você está recebendo este e-mail pelo fato de ter realizado seu cadastro em nossa Área de Membros.</p>
                        <p>Seja muito bem-vindo(a), {$PostData['user_name']}!</p>
                        <p><b>AGORA, SÓ FALTA MAIS UMA PASSO!</b></p>
                        <p>Para completar seu cadastro e ativar sua conta, basta clicar no link abaixo e então preencher os últimos dados de ativação!</p>
                        <p style='font-size: 1.2em;'><b><a href='" . BASE . "/ativar-cadastro.php?active={$wcActiveCode}' title='Ativar Minha Conta Agora'>ATIVAR MINHA CONTA AGORA!</a></b></p>
                        <p>...</p>    
                        <p>Assim que completar seu perfil sua conta estará ativa, e você já pode desfrutar de nossos treinamentos.</p>
                        <p>Qualquer dúvida ou problema não deixe de entrar em contato pelo e-mail " . SITE_ADDR_EMAIL . ", ficamos a disposição!</p>
                        <p>Atenciosamente,</p>
                        <p>" . SITE_NAME . "!</p>
                    ";
                    $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                    $Email = new Email;
                    $Email->EnviarMontando("Ative sua conta, {$PostData['user_name']}!", $MailContent, MAIL_SENDER, MAIL_USER, "{$PostData['user_name']} {$PostData['user_lastname']}", $PostData['user_email']);

                    $jSON['clear'] = true;
                    $jSON['trigger'] = $Trigger->modal("Cadastro realizado com sucesso!", "<p>Enviamos um e-mail de confirmação para <b>{$PostData['user_email']}</b>. Verifique a sua caixa de entrada e clique no botão de confirmação para acessar sua conta.</p><p>Se ainda não recebeu o e-mail, verifique também na <b>Caixa de SPAM</b>, <b>Lixo Eletrônico</b> ou na Aba <b>Promoções</b> se você utiliza o Gmail.</p>", "blue", "trophy");
                endif;
            endif;
            break;

        //USER REGISTRATION
        case 'wc_ead_register_create':
            if (in_array("", $PostData)):
                $jSON['trigger'] = $Trigger->notify("Erro ao cadastrar, {$PostData['user_name']}, você precisa informar todos os dados para criar e ativar sua conta!", 'red', 'warning', 3000);
            elseif (!Check::Email($PostData['user_email']) || !filter_var($PostData['user_email'], FILTER_VALIDATE_EMAIL)):
                $jSON['trigger'] = $Trigger->notify("E-mail Inválido. Por favor, informe seu e-mail para criar e ativar sua conta!", 'red', 'warning', 3000);
            elseif (strlen($PostData['user_password']) < 5):
                $jSON['trigger'] = $Trigger->notify("Senha Inválida. Sua senha deve ter no mínimo 5 caracteres!", 'red', 'warning', 3000);
            elseif ($PostData['user_password'] != $PostData['user_pass']):
                $jSON['trigger'] = $Trigger->notify("As senhas não conferem!", 'red', 'warning', 3000);
            else:
                $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_email = :email", "email={$PostData['user_email']}");
                if ($Read->getResult()):
                    $jSON['trigger'] = $Trigger->notify("E-mail já cadastrado! O e-mail informado já esta cadastrado! <a href='" . BASE . "/campus/recuperar.php' title='Recuperar Senha'>Recupere sua senha aqui!</a>", 'red', 'warning', 3000);
                else:
                    $PostData['user_registration'] = date("Y-m-d H:i:s");
                    $PostData['user_lastupdate'] = date("Y-m-d H:i:s");
                    $PostData['user_lastaccess'] = date("Y-m-d H:i:s");
                    $PostData['user_login'] = time();
                    $PostData['user_level'] = 1;
                    $PostData['user_password'] = hash("sha512", $PostData['user_password']);
                    $PostData['user_channel'] = "Ead Register";

                    $Create->ExeCreate(DB_USERS, $PostData);
                    $UserGenre = ($PostData['user_genre'] == 1 ? 'o' : 'a');

                    $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$Create->getResult()}");

                    require '../wc_ead.email.php';
                    $MailBody = "
                        <p style='font-size: 1.4em; text-align: center; margin: 30px auto 50px auto'>Obrigado por se inscrever!</p>
                        <p><b>Olá, {$PostData['user_name']}!</b></p>
                        <p>Está tudo pronto para você acessar sua Área de Membros Exclusiva. Uma área que entrega a melhor experiência em usabilidade e o melhor conteúdo do mercado para você.</p>                       
                        <p>Este é o link para acessar:<br>
                        <a href='" . BASE . "/campus/' title='Link de Acesso'>" . BASE . "/campus</a></p>
                        <p><hr></p>
                        <p>Qualquer dúvida ou problema não deixe de entrar em contato pelo e-mail " . SITE_ADDR_EMAIL . ", ficamos a disposição!</p>
                        <p>Atenciosamente,</p>
                        <p>" . SITE_NAME . "!</p>
                    ";
                    $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);

                    $Email = new Email;
                    $Email->EnviarMontando("Seja muito bem-vind{$UserGenre}, {$PostData['user_name']}!", $MailContent, MAIL_SENDER, MAIL_USER, "{$PostData['user_name']} {$PostData['user_lastname']}", $PostData['user_email']);

                    $_SESSION['userLogin'] = $Read->getResult()[0];

                    $jSON['trigger'] = $Trigger->notify("Seja bem-vind{$UserGenre}, {$PostData['user_name']}! Sua conta foi criada com sucesso! 😉", "green", "happy");
                    $jSON['redirect'] = BASE . "/campus/campus.php?wc=cursos/atividades";
                endif;
            endif;
            break;

        //USER RECOVER PASSWORD SEND
        case 'wc_ead_password':
            if (empty($PostData['user_email'])):
                $jSON['trigger'] = $Trigger->notify("Erro ao recuperar. Por favor, informe seu e-mail de acesso para recuperar a senha!", 'red', 'warning', 3000);
            elseif (!Check::Email($PostData['user_email']) || !filter_var($PostData['user_email'], FILTER_VALIDATE_EMAIL)):
                $jSON['trigger'] = $Trigger->notify("E-mail Inválido. O e-mail informado parece não ter um formato válido :(", 'red', 'warning', 3000);
            else:
                $Read->ExeRead(DB_USERS, "WHERE user_email = :email", "email={$PostData['user_email']}");
                if (!$Read->getResult()):
                    $jSON['trigger'] = $Trigger->notify("Nenhuma conta encontrada para <b>{$PostData['user_email']}</b>. Não há nenhuma conta do " . SITE_NAME . " com o e-mail fornecido :(", 'red', 'sad', 3000);
                else:
                    $UserMail = base64_encode($Read->getResult()[0]['user_email']);
                    $UserPass = $Read->getResult()[0]['user_password'];
                    $UserTime = base64_encode(time() + 3600);
                    $RecoverLink = "&m={$UserMail}&p={$UserPass}&t={$UserTime}";

                    require '../wc_ead.email.php';
                    $MailBody = "
                        <p>Olá, {$Read->getResult()[0]['user_name']}!</p>
                        <p>Foi solicitado uma recuperação de senha para seu e-mail em nossa plataforma. Caso queira recuperar sua senha, basta clicar no link abaixo:</p>
                        <p style='font-size: 1.2em;'><a href='" . BASE . "/nova-senha.php?t={$RecoverLink}' title='Ativar Minha Conta Agora'>RECUPERAR MINHA SENHA AGORA!</a></p>
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
                    $Email->EnviarMontando("Recupere sua senha, {$Read->getResult()[0]['user_name']}!", $MailContent, MAIL_SENDER, MAIL_USER, "{$Read->getResult()[0]['user_name']} {$Read->getResult()[0]['user_lastname']}", $Read->getResult()[0]['user_email']);
                    $jSON['clear'] = true;
                    $jSON['trigger'] = $Trigger->modal("Instruções enviadas por e-mail.", "<b>Prezado, {$Read->getResult()[0]['user_name']}!</b> <p>As intruções junto com o link para recuperar sua senha foram enviadas para seu e-mail neste instante.</p><p class='icon-warning'><b>IMPORTANTE:</b> Para sua segurança o link de recuperação é válido por 1 hora e apenas para este dispositivo!</p><p>Qualquer problema não deixe de entrar em contato!</p>", "green", "envelop");
                endif;
            endif;
            break;

        //CRIA O USUÁRIO PARA CURSOS FREE
        case 'wc_ead_register_course_free':
            if (in_array("", $PostData)):
                $jSON['trigger'] = $Trigger->notify("Erro ao Cadastrar: Por favor, preencha todos os campos para criar sua conta! ", 'red', 'warning', 5000);
            elseif (!Check::Email($PostData['user_email']) || !filter_var($PostData['user_email'], FILTER_VALIDATE_EMAIL)):
                $jSON['trigger'] = $Trigger->notify("Digite um e-mail válido! Por favor, informe um e-mail válido para se cadastrar.", 'red', 'warning', 5000);
            else:
                //READ USER_EMAIL
                $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_email = :email", "email={$PostData['user_email']}");
                $wcActiveCode = base64_encode("{$PostData['user_name']}&{$PostData['user_lastname']}&{$PostData['user_email']}&{$PostData['course_id']}");

                $Read->exeRead(DB_EAD_COURSES,"WHERE course_id = :cId","cId={$PostData['course_id']}");
                $Course = $Read->getResult()[0];

                require '../wc_ead.email.php';
                $MailBody = "
                        <p style='font-size: 1.4em; text-align: center; margin: 30px auto 50px auto'>Obrigado por ser Inscrever!</p>
                        <p>Olá, {$PostData['user_name']}! Tudo bem?</p>                
                        <p>Só falta mais um passo para você começar a utilizar o treinamento <b>{$Course['course_title']}</b> em nossa Área de Membros. Uma área exclusiva para nossos alunos poderem estudar de onde e quando quiserem!</p>
                        <p>Para isso, precisamos que confirme o seu cadastro clicando no link abaixo e então preencher os últimos dados da ativação!</p>
                        <p style='font-size: 1.2em;'><b><a href='" . BASE . "/ativar-matricula.php?active={$wcActiveCode}' title='Ativar Minha Matrícula'>ATIVAR MATRÍCULA</a></b></p>
                        <p>...</p>    
                        <p>Assim que completar seu cadastro, sua conta será estivada e você já poderá desfrutar dos nossos treinamentos.</p>
                        <p>Qualquer dúvida ou problema não deixe de entrar em contato pelo e-mail " . SITE_ADDR_EMAIL . ", ficamos a sua disposição!</p>
                        <p>Atenciosamente,</p>
                        <p>" . SITE_NAME . "!</p>                           
                    ";
                $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                $Email = new Email;
                $Email->EnviarMontando("Confirme sua Inscrição no {$Course['course_title']}, {$PostData['user_name']}!", $MailContent, MAIL_SENDER, MAIL_USER, "{$PostData['user_name']} {$PostData['user_lastname']}", $PostData['user_email']);

                $jSON['clear'] = true;
                $jSON['trigger'] = $Trigger->modal("Só mais alguns passos, {$PostData['user_name']}!", "<p>1. Verifique na sua caixa de entrada a mensagem com o título: <b>Confirme sua Inscrição no {$Course['course_title']}</b>.</p><p>2. Clique no botão <b>ATIVAR MATRÍCULA!</b> e logo depois, preencha os últimos dados para finalizar seu cadastro!</p><p>Se ainda não recebeu o e-mail, verifique também na <b>Caixa de SPAM</b>, <b>Lixo Eletrônico</b> ou na Aba <b>Promoções</b> se você utiliza o Gmail.</p>", "green", "trophy");
            endif;
            break;

        //MATRICULA O USUÁRIO AO CURSO
        case 'wc_ead_register_course_free_add':
            $CourseId = $PostData['course_id'];
            unset($PostData['course_id']);
            if (in_array('', $PostData)):
                $jSON['trigger'] = $Trigger->notify("Erro ao Cadastrar: Por favor, preencha todos os campos para criar sua conta! ", 'red', 'warning', 5000);
            elseif (!Check::Email($PostData['user_email']) || !filter_var($PostData['user_email'], FILTER_VALIDATE_EMAIL)):
                $jSON['trigger'] = $Trigger->notify("<b>OPPSSS:</b> O e-mail informado não é válido!", 'red', 'warning', 5000);
            elseif (strlen($PostData['user_password']) < 5):
                $jSON['trigger'] = $Trigger->notify("<b>OPPSSS:</b> Você precisa criar uma senha com 5 ou mais caracteres!", 'red', 'warning', 5000);
            elseif (strlen($PostData['user_password']) != (strlen($PostData['user_pass']))):
                $jSON['trigger'] = $Trigger->notify("Senhas diferentes: Você precisa confirmar a mesma senha do campo anterior!", 'red', 'warning', 5000);
            else:
                $Read->FullRead("SELECT user_email FROM " . DB_USERS . " WHERE user_email = :email", "email={$PostData['user_email']}");

                if ($Read->getResult()):
                    $PostData['user_id'] = $Read->getResult()[0]['user_id'];
                else:
                    $PostData['user_password'] = hash("sha512", $PostData['user_password']);
                    $PostData['user_pass'] = hash("sha512", $PostData['user_pass']);
                    $PostData['user_genre'] = ($PostData['user_genre']);
                    $PostData['user_registration'] = date("Y-m-d H:i:s");
                    $PostData['user_lastupdate'] = date("Y-m-d H:i:s");
                    $PostData['user_lastaccess'] = date("Y-m-d H:i:s");
                    $PostData['user_channel'] = "Curso Gratuíto";
                    $PostData['user_level'] = 1;
                    $Create->ExeCreate(DB_USERS, $PostData);
                    $PostData['user_id'] = $Create->getResult();
                endif;

                $UserGenreString = ($PostData['user_genre'] == 1 ? 'o' : 'a');

                $PostData['course_id'] = $CourseId;
                $Read->ExeRead(DB_EAD_COURSES,"WHERE course_id = :cId","cId={$PostData['course_id']}");
                $Course = $Read->getResult()[0];

                //realiza a matricula
                $CreateOrder = [
                    'user_id' => $PostData['user_id'],
                    'course_id' => $PostData['course_id'],
                    'order_transaction' => time(),
                    'order_callback_type' => "1",
                    'order_price' => "0.00",
                    'order_currency' => "BRL",
                    'order_payment_type' => "admin_free",
                    'order_purchase_date' => date('Y-m-d H:i:s'),
                    'order_warranty_date' => date('Y-m-d H:i:s'),
                    'order_confirmation_purchase_date' => date('Y-m-d H:i:s'),
                    'order_sck' => "admin_free",
                    'order_src' => 1,
                    'order_cms_aff' => "0.00",
                    'order_cms_marketplace' => "0.00",
                    'order_cms_vendor' => "0.00",
                    'order_status' => "admin_free",
                    'order_delivered' => 1
                ];

                $Create->ExeCreate(DB_EAD_ORDERS, $CreateOrder);

                $Enrollment['user_id'] = $PostData['user_id'];
                $Enrollment['course_id'] = $PostData['course_id'];
                $Enrollment['enrollment_order'] = $Create->getResult();
                $Enrollment['enrollment_end'] = date("Y-m-d H:i:s", strtotime("+{$Course['course_release_days']}days"));

                $Read->FullRead("SELECT enrollment_end, enrollment_id FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id=:us AND course_id=:cs", "us={$Enrollment['user_id']}&cs={$PostData['course_id']}");
                if ($Read->getResult()):
                    //UPDATE ENROLLMENTE
                    if (!empty($Read->getResult()[0]['course_end'])):
                        $UpdateEnrollmentData = ['enrollment_end' => date("Y-m-d H:i:s", strtotime(($Read->getResult()[0]['enrollment_end'] && $Read->getResult()[0]['enrollment_end'] != '0000-00-00 00:00:00' ? $Read->getResult()[0]['enrollment_end'] : date('Y-m-d H:i:s')) . "+{$PostData['course_end']}days"))];
                    else:
                        $UpdateEnrollmentData = ['enrollment_end' => null];
                    endif;
                    $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollmentData, "WHERE enrollment_id=:id", "id={$Read->getResult()[0]['enrollment_id']}");
                else:
                    //CREATE ENROLLMENTE
                    $Enrollment['enrollment_start'] = date('Y-m-d H:i:s');
                    $Create->ExeCreate(DB_EAD_ENROLLMENTS, $Enrollment);
                endif;

                require "../wc_ead.email.php";
                $MailBody = "
                    <p style='font-size: 1.4em; text-align: center; margin: 30px auto 50px auto'>Matrícula realizada com sucesso!</p>
                    <p>Olá, {$PostData['user_name']}!</p>
                    <p>Este e-mail é para informar que sua matrícula para o treinamento <b>{$Course['course_title']}</b> foi realizada com sucesso! Seja muito bem-vind{$UserGenreString}! 😉 </p>
                    <p>Você já pode acessar nossa Área de Membros <a href='" . BASE . "/campus' title='Acessar minha conta na plataforma!'>clicando aqui</a>!</b></p>
                    <p><b>DADOS DA MATRÍCULA:</b></p>
                    <p>
                    <b>Treinamento:</b> {$Course['course_title']}<br>
                    <b>Liberação:</b> " . date('d/m/Y H\hi') . "<br>
                    <b>Validade:</b> " . (!empty($Enrollment['enrollment_end']) ? date("d/m/Y H\hi", strtotime($Enrollment['enrollment_end'])) : 'Indefinida') . "
                    </p>
                    <p>...</p>
                    <p>Se tiver qualquer problema, não deixe de responder este e-mail!</p>
                    <p>Atenciosamente,</p>
                    <p>" . SITE_NAME . "!</p>   
                ";

                $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                $Email = new Email;
                $Email->EnviarMontando("Sua matrícula para {$Course['course_title']} foi realizada com sucesso!", $MailContent, MAIL_SENDER, MAIL_USER, "{$PostData['user_name']}", $PostData['user_email']);

                $Read->ExeRead(DB_USERS,"WHERE user_email = :email", "email={$PostData['user_email']}");

                $_SESSION['userLogin'] = $Read->getResult()[0];

                $jSON['trigger'] = $Trigger->notify("Seja bem-vind{$UserGenreString}, {$PostData['user_name']}! Sua conta foi criada e ativada com sucesso!", 'green', 'heart', 5000);
                $jSON['redirect'] = BASE . "/campus/campus.php?wc=cursos/atividades";
            endif;
            break;
    endswitch;

    //RETORNA O CALLBACK
    if ($jSON):
        echo json_encode($jSON);
    else:
        $jSON['trigger'] = $Trigger->notify("<b>OPSS:</b> Desculpe. Mas uma ação do sistema não respondeu corretamente. Ao persistir, contate o desenvolvedor!", 'red', 'warning', 5000);
        echo json_encode($jSON);
    endif;
else:
    //ACESSO DIRETO
    die('<br><br><br><center><h1>Acesso Restrito!</h1></center>');
endif;
