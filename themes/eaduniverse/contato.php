<div class="breadcrumb">
    <div class="container">
        <span>Você está em: <strong>Fale Conosco</strong>
        </span>
    </div>
</div>
<section class="contact">
    <div class="container">
        <div class="row">
            <div class="article contact">
                <div class="article-content">
                    <h1>Contato</h1>
                    <p>Fique a vontade para entrar em contato.</p>

                    <?php
                    $Contato = filter_input_array(INPUT_POST, FILTER_DEFAULT);
                    if ($Contato && $Contato['action'] == 'contact'):
                        unset($Contato['action']);

                        if (in_array('', $Contato)):
                            Erro("Para enviar seu contato, favor preencha todos os campos!", E_USER_WARNING);
                        elseif (!Check::Email($Contato['email']) || !filter_var($Contato['email'], FILTER_VALIDATE_EMAIL)):
                            Erro("Desculpe, mas o e-mail que você informou não tem um formato válido!", E_USER_ERROR);
                        else:
                            array_map('strip_tags', $Contato);

                            $MailContent = '
                        <table width="550" style="font-family: "Trebuchet MS", sans-serif;">
                         <tr><td>
                          <font face="Trebuchet MS" size="3">
                           <p>Novo contato de ' . $Contato['nome'] . '</p>
                           <p><b>MENSAGEM:</b> ' . $Contato['message'] . ' </p>
                          </font>
                          <p style="font-size: 0.875em;">
                          <img src="' . BASE . '/admin/_img/mail.jpg" alt="Atenciosamente ' . SITE_NAME . '" title="Atenciosamente ' . SITE_NAME . '" /><br><br>
                           ' . SITE_ADDR_NAME . '<br>Telefone: ' . SITE_ADDR_PHONE_A . '<br>E-mail: ' . SITE_ADDR_EMAIL . '<br><br>
                           <a title="' . SITE_NAME . '" href="' . BASE . '">' . SITE_ADDR_SITE . '</a><br>' . SITE_ADDR_ADDR . '<br>'
                                    . SITE_ADDR_CITY . '/' . SITE_ADDR_UF . ' - ' . SITE_ADDR_ZIP . '<br>' . SITE_ADDR_COUNTRY . '
                          </p>
                          </td></tr>
                        </table>
                        <style>body, img{max-width: 550px !important; height: auto !important;} p{margin-botton: 15px 0 !important;}</style>';

                            $Email = new Email;
                            $Email->EnviarMontando($Contato['subject'], $MailContent, $Contato['nome'], $Contato['email'], SITE_ADDR_NAME, SITE_ADDR_EMAIL);
                            if (!$Email->getError()):
                                $_SESSION['sucesso'] = "Sua mensagem foi enviada com sucesso!";
                                header('Location: ' . BASE . '/contato');
                                exit;
                            else:
                                Erro("Desculpe, não foi possível enviar sua mensagem. Entre em contato via " . SITE_ADDR_EMAIL . ". Obrigado!", E_USER_ERROR);
                            endif;
                        endif;
                    endif;

                    if (!empty($_SESSION['sucesso']) && empty($Contato)):
                        Erro($_SESSION['sucesso']);
                        unset($_SESSION['sucesso']);
                    endif;
                    ?>

                    <form action="" method="post" enctype="multipart/form-data" class="contact-form">
                        <input type="hidden" name="action" value="contact"/>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome*">
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" id="email" class="form-control" placeholder="E-mail*">
                            </div>
                        </div>

                        <input type="text" name="subject" id="subject" class="form-control" placeholder="Assunto">

                        <textarea name="message" id="message" placeholder="Escreva sua mensagem" class="form-control" rows="10"></textarea>

                        <button type="submit" class="btn btn-blue">Enviar mensagem</button>						
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>
<?php require 'inc/newsletter.php'; ?>