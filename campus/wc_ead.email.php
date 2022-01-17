<?php

$MailContent = '
<div class="rps_fee4">
    <style type="text/css">
        .rps_fee4 #x_outlook a { padding: 0; }
        .rps_fee4 body{ width: 100%!important; margin: 0; padding: 0; }
        .rps_fee4 .x_ExternalClass{ width: 100%; }
        .rps_fee4 .x_ExternalClass, .rps_fee4 .x_ExternalClass p, .rps_fee4 .x_ExternalClass span, .rps_fee4 .x_ExternalClass font, .rps_fee4 .x_ExternalClass td, .rps_fee4 .x_ExternalClass div
        { line-height: 100%; }
        .rps_fee4 #x_backgroundTable{ margin: 0; padding: 0; width: 100%!important; line-height: 100%!important; }
        .rps_fee4 img{ outline: none; text-decoration: none; }
        .rps_fee4 a img{ border: none; }
        .rps_fee4 .x_image_fix{ display: block; }
        .rps_fee4 p{ margin: 1em 0; }
        .rps_fee4 h1{ color: white!important; }
        .rps_fee4 h2, .rps_fee4 h3, .rps_fee4 h4, .rps_fee4 h5, .rps_fee4 h6{ color: #333333!important; }
        .rps_fee4 table td{ border-collapse: collapse; }
        .rps_fee4 table{ border-collapse: collapse; }
        .rps_fee4 a{ color: #4a8aca; }
    </style>

    <div style="background-color:#f1f1f1; direction:ltr">
        <table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#f1f1f1">
            <tr height="25">
                <td dir="ltr"></td>
                <td dir="ltr"></td>
                <td dir="ltr"></td>
            </tr>
            <tr>
                <td dir="ltr" valign="top"></td>
                <td dir="ltr" valign="middle" style="font-size:30px; font-weight:300; color:#262e33; line-height:48px; text-align: center; margin: auto">
                    <a title="' . MAIL_SENDER . '" href="' . BASE . '" target="_blank" style="text-decoration:none; color:#262e33; border:0">
                        <img src="' . BASE . '/tim.php?src=' . INCLUDE_PATH . '/images/logo-email.png&w=104&h=51" alt="' . MAIL_SENDER . '">
                    </a>
                </td>
                <td dir="ltr" valign="top"></td>
            </tr>
            <tr height="25">
                <td dir="ltr"></td>
                <td dir="ltr"></td>
                <td dir="ltr"></td>
            </tr>
            <tr>
                <td dir="ltr"></td>
                <td dir="ltr" width="800" id="x_main" bgcolor="#ffffff" style="border-top:3px solid #F6542B; line-height:1.5">
                    <table width="100%" cellpadding="20">
                        <tr>
                            <td dir="ltr" style=" font-size:15px; color:#333333; line-height:21px">                                                                    
                                <div face="Trebuchet MS" size="3">
                                   #mail_body#
                                   
                                   <p style="font-size: 0.875em;">
                                        <img src="' . INCLUDE_PATH . '/images/mail.jpg" alt="Atenciosamente ' . MAIL_SENDER . '" title="Atenciosamente ' . MAIL_SENDER . '" /><br/><br/>
                                        Contato: ' . SITE_ADDR_PHONE_A . '<br/> E-mail: ' . SITE_ADDR_EMAIL . '<br/><br/>'
    . SITE_ADDR_ADDR . '<br/> Bairro: ' . SITE_ADDR_DISTRICT . ' <br/> Cidade: ' . SITE_ADDR_CITY . ' / ' . SITE_ADDR_UF . '
                                   </p>
                                </div>                                                                
                            </td>
                        </tr>
                    </table>
                </td>
                <td dir="ltr"></td>
            </tr>               
        </table>
    </div>
</div>
';