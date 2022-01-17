<?php
ob_start();
session_start();

$Read = new Read;

$Cookie = filter_input(INPUT_COOKIE, 'workcontrol', FILTER_VALIDATE_EMAIL);
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="mit" content="2017-01-31T14:45:43-02:00+7467">
    <title>Ativar Cadastro - <?= SITE_NAME; ?>!</title>
    <meta name="description" content="<?= SITE_DESC; ?>"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0"/>
    <meta name="robots" content="index, follow"/>

    <link rel="base" href="<?= BASE; ?>/campus">
    <link rel="canonical" href="<?= BASE; ?>/campus"/>

    <meta itemprop="name" content="Ativar Cadastro - <?= SITE_NAME; ?>!"/>
    <meta itemprop="description" content="<?= SITE_DESC; ?>"/>
    <meta itemprop="image" content="<?= INCLUDE_PATH; ?>/images/default.jpg"/>
    <meta itemprop="url" content="<?= BASE; ?>/campus"/>

    <meta property="og:type" content="article" />
    <meta property="og:title" content="Ativar Cadastro - <?= SITE_NAME; ?>!" />
    <meta property="og:description" content="<?= SITE_DESC; ?>" />
    <meta property="og:image" content="<?= INCLUDE_PATH; ?>/images/default.jpg" />
    <meta property="og:image:secure_url" content="<?= INCLUDE_PATH; ?>/images/default.jpg" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="628" />
    <meta property="og:url" content="<?= BASE; ?>/campus" />
    <meta property="og:site_name" content="<?= SITE_NAME; ?>" />
    <meta property="og:locale" content="pt_BR" />
    <?php
    if (SITE_SOCIAL_FB_APP):
        echo '<meta property="fb:app_id" content="' . SITE_SOCIAL_FB_APP . '" />' . "\r\n";
    endif;
    ?>

    <meta property="twitter:card" content="summary_large_image" />
    <?php
    if (SITE_SOCIAL_TWITTER):
        echo '<meta property="twitter:site" content="@' . SITE_SOCIAL_TWITTER . '" />' . "\r\n";
    endif;
    ?>
    <meta property="twitter:domain" content="<?= BASE; ?>/campus" />
    <meta property="twitter:title" content="Ativar Cadastro - <?= SITE_NAME; ?>!" />
    <meta property="twitter:description" content="<?= SITE_DESC; ?>" />
    <meta property="twitter:image" content="<?= INCLUDE_PATH; ?>/images/default.jpg" />
    <meta property="twitter:url" content="<?= BASE; ?>/campus" />

    <link rel="shortcut icon" href="<?= INCLUDE_PATH; ?>/images/favicon.png"/>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Source+Code+Pro:300,500' rel='stylesheet' type='text/css'>
    <link rel="base" href="<?= BASE; ?>/campus/">

    <link rel="stylesheet" href="<?= BASE; ?>/campus/_css/reset.css">
    <link rel="stylesheet" href="<?= BASE; ?>/campus/_css/login_style.css">
    <link rel="stylesheet" href="../_cdn/bootcss/fonticon.css"/>
</head>
<body>
<div class="login">
    <div class="login_box">
        <img class="login_box_logo" src="campus/_img/logo.png" alt="<?= SITE_NAME; ?>" title="<?= SITE_NAME; ?>"/>
        <div class="login_box_content radius">
            <form class="" name="work_login" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="callback" value="Users">
                <input type="hidden" name="callback_action" value="wc_ead_register_create">

                <div class="trigger_ajax"></div>

                <label class="label">
                    <input type="text" style="cursor: no-drop; width: 100%; padding: 15px; text-indent: 40px; background: #eeeeee url(campus/_img/user.png) left 15px center no-repeat;" name="user_name" value="<?=$user_name?>" readonly placeholder="Informe seu Primeiro nome:" required=""/>
                </label>

                <label class="label">
                    <input type="text" style="cursor: no-drop; width: 100%; padding: 15px; text-indent: 40px; background: #eeeeee url(campus/_img/user.png) left 15px center no-repeat;" name="user_lastname" value="<?=$user_lastname?>" readonly placeholder="Informe Seu Sobrenome:" required=""/>
                </label>

                <label class="label">
                    <select name="user_genre" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(campus/_img/love.png) left 15px center no-repeat;" required>
                        <option value="">Selecione seu Gênero:</option>
                        <option value="1">Masculino</option>
                        <option value="2">Feminino</option>
                    </select>
                </label>

                <label class="label">
                    <input type="email" style="width: 100%; padding: 15px; text-indent: 40px; background: #eeeeee url(campus/_img/mail.png) left 15px center no-repeat;" name="user_email" value="<?=$user_email?>" readonly placeholder="Informe seu e-mail:" required/>
                </label>

                <label class="label">
                    <input type="password" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(campus/_img/key.png) left 15px center no-repeat;" name="user_password" placeholder="Digite sua senha:" required=""/>
                </label>

                <label class="label">
                    <input type="password" style="width: 100%; padding: 15px; text-indent: 40px; background: #fff url(campus/_img/key.png) left 15px center no-repeat;" name="user_pass" placeholder="Confirme sua senha:" required=""/>
                </label>

                <p class="text-center">
                    <img class="form_load none" style="float: right; margin-top: 3px; margin-left: 10px;width: 40px" alt="Enviando Requisição!" title="Enviando Requisição!" src="campus/_img/load.svg"/>
                </p>
                <button class="login_btn">Ativar Meu Cadastro</button>
            </form>
        </div>
    </div>
</div>

<script src="_cdn/jquery.js"></script>
<script src="_cdn/jquery.form.js"></script>
<script src="campus/_js/maskinput.js"></script>
<script>
    $(function () {
        $('html').on('submit', 'form:not(.ajax_off)', function () {

            var form = $(this);
            var callback = form.find('input[name="callback"]').val();
            var callback_action = form.find('input[name="callback_action"]').val();

            if (typeof tinyMCE !== 'undefined') {
                tinyMCE.triggerSave();
            }

            form.ajaxSubmit({
                url: 'campus/_ajax/' + callback + '.ajax.php',
                data: {callback_action: callback_action},
                dataType: 'json',
                beforeSubmit: function () {
                    form.find('.form_load').fadeIn('fast');
                    $('.trigger_ajax').fadeOut('fast');
                },
                uploadProgress: function (evento, posicao, total, completo) {
                    var porcento = completo + '%';
                    $('.workcontrol_upload_progrees').text(porcento);

                    if (completo <= '80') {
                        $('.workcontrol_upload').fadeIn().css('display', 'flex');
                    }
                    if (completo >= '99') {
                        $('.workcontrol_upload').fadeOut('slow', function () {
                            $('.workcontrol_upload_progrees').text('0%');
                        });
                    }
                    //PREVENT TO RESUBMIT IMAGES GALLERY
                    form.find('input[name="image[]"]').replaceWith($('input[name="image[]"]').clone());
                },
                success: function (data) {
                    //REMOVE LOAD
                    form.find('.form_load').fadeOut('slow', function () {
                        //EXIBE CALLBACKS
                        if (data.trigger) {
                            Trigger(data.trigger);
                        }

                        //NOVO MODELO TRIGGER
                        if (data.trigger) {
                            trigger(data.trigger);
                        }

                        //NOVO MODELO TRIGGER MODAL
                        if (data.message) {
                            triggerModal(data.message);
                        }
                        //REDIRECIONA
                        if (data.redirect) {
                            $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
                            $('.workcontrol_upload').fadeIn().css('display', 'flex');
                            window.setTimeout(function () {
                                window.location.href = data.redirect;
                                if (window.location.hash) {
                                    window.location.reload();
                                }
                            }, 1500);
                        }


                    });
                }
            });
            return false;
        });

        /**
         * trigger: Está função trata o objeto para executar qualquer tipo de notificação
         * @param {array} data
         */
        function trigger(data) {
            if (data[0]) {
                //Notificações multiplas
                var delay = 0;
                $.each(data, function (key, value) {
                    setTimeout(function () {
                        if (value.type === 'notify') {
                            triggerNotify(value);
                        } else if (value.type === "modal") {
                            alert("Olá, o sistema não suporta multiplas modais!");
                            return;
                        }

                    }, delay);
                    delay += 1000;
                });
            } else {
                if (data.type === 'notify') {
                    triggerNotify(data);
                } else if (data.type === "modal") {
                    triggerModal(data);
                }
            }
        }

        /**
         * triggerNotify: Aqui cria norificação que aparece no canto superior direito.
         * @param data color|icon|message|type|[,time]
         * @returns html notify
         */
        function triggerNotify(data) {
            var timeNotify = data.time || 5000;
            var triggerContent = "<div class='trigger_notify icon-" + data.icon + " trigger_" + data.color + "' style='left: 100%; opacity: 0;'>";
            triggerContent += data.message;
            triggerContent += "<span class='trigger_notify_time'></span>";
            triggerContent += "</div>";

            //GET OR ADD TRIGGER BOX
            if (!$(".trigger_notify_box").length) {
                $("body").prepend("<div class='trigger_notify_box'></div>");
            } else {
                $(".trigger_notify:gt(1)").animate({"left": "100%", "opacity": "0"}, 400, function () {
                    $(this).remove();
                });
            }

            $(".trigger_notify_box").prepend(triggerContent);
            $(".trigger_notify:first").stop().animate({"left": "0", "opacity": "1"}, 200, function () {
                $(this).find(".trigger_notify_time").animate({"width": "100%"}, timeNotify, "linear", function () {
                    $(this).parent(".trigger_notify").animate({"left": "100%", "opacity": "0"}, 200, function () {
                        $(this).remove();
                        if (data.location) {
                            if (data.location === true) {
                                window.location.reload();
                            } else {
                                window.location.href = data.location;
                                if (window.location.href === data.location) {
                                    window.location.reload();
                                }
                            }
                        }
                    });
                });
            });

            $("body").on('click', '.trigger_notify', function () {
                $(this).animate({"left": "100%", "opacity": "0"}, 200, function () {
                    $(this).remove();
                    if (data.location) {
                        if (data.location === true) {
                            window.location.reload();
                        } else {
                            window.location.href = data.location;
                            if (window.location.href === data.location) {
                                window.location.reload();
                            }
                        }
                    }
                });
            });
        }

        /**
         * triggerNotify: cria norifiÃ§Ã£o superior direita.
         * @param data color|icon|title|message|type|[,close url]
         * @returns html notify
         */
        function triggerModal(data) {
            var triggerContent = "<div class='trigger_modal_box'>";
            triggerContent += "<div class='trigger_modal trigger_" + data.color + "'>";
            triggerContent += "<span class='icon-cross trigger_modal_close icon-notext'></span>";
            triggerContent += "<div class='trigger_modal_icon icon-" + data.icon + " icon-notext'></div>";
            triggerContent += "<div class='trigger_modal_content'>";
            triggerContent += "<div class='trigger_modal_content_title'>" + data.title + "</div>";
            triggerContent += "<div class='trigger_modal_content_message'>" + data.message + "</div>";
            triggerContent += "</div></div></div>";

            if (!$(".trigger_modal_box").length) {
                $("body").prepend("<div class='trigger_notify_box'>" + triggerContent + "</div>");
            } else {
                $(".trigger_modal").fadeOut(200, function () {
                    $(this).remove();
                    $(".trigger_modal_box").html(triggerContent);
                });
            }

            $(".trigger_modal_box").fadeIn(200, function () {
                var modal_box = $(this);
                modal_box.find(".trigger_modal").animate({"top": "0", "opacity": "1"}, 200);

                modal_box.on("click", ".trigger_modal_close", function () {
                    modal_box.find(".trigger_modal").animate({"top": "100", "opacity": "0"}, 200, function () {
                        modal_box.fadeOut(200, function () {
                            $(this).remove();
                            if (data.location) {
                                if (data.location === true) {
                                    window.location.reload();
                                } else {
                                    window.location.href = data.location;
                                    if (window.location.href === data.location) {
                                        window.location.reload();
                                    }
                                }
                            }
                        });
                    });
                });
            }).css("display", "flex");
        }

    });

    //############## MODAL MESSAGE
    function Trigger(Message) {
        $('.trigger_ajax').fadeOut('fast', function () {
            $(this).remove();
        });
        // $('body').before("<div class='trigger_modal'>" + Message + "</div>");
        // $('.trigger_ajax').fadeIn();
    }

    function TriggerClose() {
        $('.trigger_ajax').fadeOut('fast', function () {
            $(this).remove();
        });
    }
</script>
</body>
</html>
<?php
ob_end_flush();
