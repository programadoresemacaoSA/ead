$(function () {
    var BASE = $('link[rel="base"]').attr('href') + "/";

    /**
     * Controle de Formulários via Ajax
     * Basta criar o formulário e colocar em action o case do ajax.
     * <b>Observação:</b> Caso não queira que o formulário passe por esse controle basta colocar a classe .ajax_off
     */
    $("body").on("submit", "form:not(.ajax_off)", function (e) {
        e.preventDefault();
        var form = $(this);
        var form_data = $(this).serialize() + "&callback_action=" + form.attr("action");
        var form_button = ($("button[form='" + form.attr("id") + "']").length ? $("button[form='" + form.attr("id") + "']") : $(form.find("button:last")));
        var form_button_text = form_button.html();

        $.ajax({
            url: BASE + "_ajax/CourseFree.ajax.php",
            data: form_data,
            type: 'POST',
            dataType: 'json',
            beforeSend: function () {
                form_button.attr("disabled", "disabled").width(form_button.width()).html("<img class='form_load none' style='float: right; margin-top: 3px; margin-left: 10px;width: 40px' alt='Enviando Requisição!' title='Enviando Requisição!' src='_img/load.svg'/>");
            },
            success: function (data) {
                //TRIGGER CONTROL
                if (data.trigger) {
                    trigger(data.trigger);
                }

                //RELOAD NOW
                if (data.reload) {
                    window.location.reload();
                }

                //REDIRECT NOW
                if (data.redirect) {
                    window.location.replace(data.redirect);
                }

                if (data.location) {
                    window.location.href = data.location;
                    if (window.location.href === data.location) {
                        window.location.reload();
                    }
                }

                //LOAD REMOVE
                if (!data.location && !data.checkout && !data.reload) {
                    form_button.removeAttr("disabled").width("").html(form_button_text);
                }

                //FADE REMOVE
                if (data.faderemove) {
                    $(data.faderemove).fadeOut(function () {
                        $(this).remove();
                    });
                }

                //CHAT
                if (data.sent) {
                    document.getElementsByClassName(".j_message").value = "";
                    loadMessages();
                }

                //CLEAR
                if (data.clear) {
                    form.trigger('reset');
                }
            }
        });
    });

    /*
    ==============================
    ========= FUNCTIONS ==========
    ==============================
     */

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