<?php

session_start();

$getPost = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (empty($getPost) || empty($getPost['callback_action'])) {
    die('Acesso Negado!');
}

$strPost = array_map('strip_tags', $getPost);
$POST = array_map('trim', $strPost);

$jSON = null;
$Action = $POST['callback_action'];
unset($POST['callback'], $POST['callback_action']);

usleep(2000);

require '../../_app/Config.inc.php';
require '../functions.php';

// AUTO INSTANCE OBJECT READ
if (empty($Read)) {
    $Read = new Read;
}

// AUTO INSTANCE OBJECT CREATE
if (empty($Create)) {
    $Create = new Create;
}

// AUTO INSTANCE OBJECT UPDATE
if (empty($Update)) {
    $Update = new Update;
}

// AUTO INSTANCE OBJECT UPDATE
if (empty($Delete)) {
    $Delete = new Delete;
}

//SELECIONA AÇÃO
switch ($Action) {
    //RESPONSÁVEL PELO LOGIN DE LEADs A SALA DA LIVE
    case 'lead_login':
        $CourseId = $POST['course_id'];
        unset($POST['course_id']);

        $CourseTitle = $POST['course_title'];
        unset($POST['course_title']);

        if (in_array('', $POST)) {
            //VERIFICA SE O USUÁRIO PREENCHEU TODOS OS DADOS
            $jSON['trigger'] = notify("Opss... para realizar seus cadastro é necessário que você preencha todos os campos.",
                "warning", "red");
        } elseif (!Check::Email($POST['lead_email']) || !filter_var($POST['lead_email'], FILTER_VALIDATE_EMAIL)) {
            //VERIFICA SE O USUÁRIO COLOCOU UM E-MAIL VÁLIDO
            $jSON['trigger'] = notify("Opss... o email informado não parece válido, por favor informe um email válido!",
                "warning", "yellow");
        } else {
            //Atualização/Cadastro do lead e criação da sessão de login na live
            $_SESSION['liveLogin'] = save_lead($POST['lead_name'], $POST['lead_email'], $CourseId);

            //READ USER_EMAIL
            $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_email = :email", "email={$POST['lead_email']}");
            $wcActiveCode = base64_encode("{$POST['lead_name']}&{$POST['lead_email']}&{$CourseId}");

            $Read->exeRead(DB_EAD_COURSES,"WHERE course_id = :cId","cId={$CourseId}");
            $Course = $Read->getResult()[0];

            require '../wc_ead.email.php';
            $MailBody = "
                        <p style='font-size: 1.4em; text-align: center; margin: 30px auto 50px auto'>Obrigado por ser Inscrever!</p>
                        <p>Olá, {$POST['lead_name']}! Tudo bem?</p>                
                        <p>Só falta mais um passo para você começar a utilizar o treinamento <b>{$CourseTitle}</b> em nossa Área de Membros. Uma área exclusiva para nossos alunos poderem estudar de onde e quando quiserem!</p>
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
            $Email->EnviarMontando("Confirme sua Inscrição no {$CourseTitle}, {$POST['lead_name']}!", $MailContent, MAIL_SENDER, MAIL_USER, "{$POST['lead_name']}", $POST['lead_email']);

            $jSON['clear'] = true;

            $jSON['trigger'] = modal("Só mais alguns passos, {$POST['lead_name']}!", "<p>1. Verifique na sua caixa de entrada a mensagem com o título: <b>Confirme sua Inscrição no {$CourseTitle}</b>.</p><p>2. Clique no botão <b>ATIVAR MATRÍCULA!</b> e logo depois, preencha os últimos dados para finalizar seu cadastro!</p><p>Se ainda não recebeu o e-mail, verifique também na <b>Caixa de SPAM</b>, <b>Lixo Eletrônico</b> ou na Aba <b>Promoções</b> se você utiliza o Gmail.</p>", "trophy", "green");
        }
        break;

}

//RETORNA O CALLBACK
echo json_encode($jSON);
