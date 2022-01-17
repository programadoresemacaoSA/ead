$(document).ready(function () {

    THEME = "eaduniverse";

    var url = BASE + '/themes/' + THEME + '/_ajax/control.ajax.php';
    
    //SUBMIT CERTIFICATE
    $('.app_about_certificates_header_form').on('submit', function(){
        var Form = $(this);
        
        var certificate = Form.find('input[name="certificate"]').val();
        
        Form.ajaxSubmit({
            url: url,
            data: {action: 'verify_certificate', certificate: certificate},
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                setTimeout(function () {
                    Form.trigger('reset');
                }, '500');
                
                if(data.redirect){
                    window.location.href = data.redirect;
                }

                if (data.notify) {
                    trigger(data.notify);
                }
            }
        });
        return false;
    });

    //MODAL QUICKVIEW COURSE
    $('html').on('click', '.j_view_course', function () {
        $.post(url, {action: 'get_course', course_id: $(this).attr('data-course-id')}, function (data) {
            if (data.course) {

                $('html').addClass('modal-open');
                $('body').append(data.course).addClass('modal-open');

                $('.modal').fadeIn('slow', function () {
                    $(this).find('.modal-content').fadeIn('slow');
                });

                //ADD CART
                //ADD ITEM TO CART
                var action = BASE + "/_cdn/widgets/ecommerce/cart.ajax.php";
                $('.wc_cart_add').submit(function () {
                    var data = $(this).serialize() + "&action=wc_cart_add";
                    $.post(action, data, function (data) {
                        if (data.cart_amount) {
                            $('.cart_count').html(data.cart_amount);
                        }
                        if (data.cart_course) {
                            $('.wc_cart_manager_info b').html(data.cart_course);
                            $('.wc_cart_manager').fadeIn(200, function () {
                                $('.wc_cart_manager_content').fadeIn(200);
                            });

                            $('.wc_cart_close').click(function () {
                                $('.wc_cart_manager_content').fadeOut(200, function () {
                                    $('.wc_cart_manager').fadeOut();
                                });
                            });
                        }

                        if (data.trigger) {
                            wcCartTrigger(data.trigger);
                        }
                    }, 'json');
                    return false;
                });

                //MODAL VIDEO
                $(".js-modal-btn-youtube").modalVideo();
                $(".js-modal-btn").modalVideo({channel: 'vimeo'});
                //Fecha modal
                $('html').on('click', '.close', function () {
                    $('.modal').fadeOut('fast', function () {
                        $(this).find('.modal-content').fadeOut('slow');
                        $(this).remove();
                    });
                    $('html').removeClass('modal-open');
                    $('body').removeClass('modal-open');
                });
            }
        }, 'json');

        return false;
    });

    // NEWSLETTER
    $('html').on('submit', 'form[name="newsletter"]', function () {
        var form = $(this);

        var email = form.find('input[name="email"]').val();
        form.find('i').attr('class', 'fa fa-spinner fa-spin');

        $.post(url, {action: 'add_newsletter', email: email}, function (data) {
            setTimeout(function () {
                form.find('i').attr('class', 'fa fa-envelope');
                form.trigger('reset');
            }, '500');

            if (data.notify) {
                trigger(data.notify);
            }
        }, 'json');

        return false;
    });

    // Configurações padrão

    /* Menu mobile */

    var button = $('#menu-button'),
            menu = $('#menu-wrap');

    button.on('click', function () {

        $(this).toggleClass('collapsed');
        $('body').toggleClass('menu-active');

        if (!menu.hasClass('menu-open')) {
            menu.addClass('in menu-open');
            setTimeout(function () {
                menu.removeClass('in');
            }, 250);
        } else {
            menu.removeClass('in');
            menu.addClass('out');
            setTimeout(function () {
                menu.removeClass('menu-open out');
            }, 250);
        }
    });

    /* Validation plugin - Mudando/traduzindo texto das mensagens de erro */
    if (jQuery.validator) {
        jQuery.extend(jQuery.validator.messages, {
            required: "Este campo é obrigatório.",
            remote: "por favor, corrija este campo.",
            email: "Informe um endereço de email válido.",
            url: "Informe uma URL válida.",
            date: "Informe uma data válida.",
            dateISO: "Informe uma data válida (ISO).",
            number: "Informe um número válido.",
            digits: "Informe apenas dígitos.",
            creditcard: "Informe um número de cartão de crédito válido.",
            equalTo: "Informe o mesmo valor novamente.",
            accept: "Informe um valor com uma extensão válida.",
            maxlength: jQuery.validator.format("Por favor, não insira mais do que {0} caractere(s)."),
            minlength: jQuery.validator.format("Por favor, insira no mínimo {0} caractere(s)."),
            rangelength: jQuery.validator.format("Por favor, informe um valor com no mínimo {0} e no máximo {1} caracteres."),
            range: jQuery.validator.format("Por favor, informe um valor entre {0} e {1}."),
            max: jQuery.validator.format("Por favor, informe um valor menor ou igual a {0}."),
            min: jQuery.validator.format("Por favor, informe um valor maior ou igual a  {0}.")
        });
    }

    /* Deixando os selects cinza quando estão intocados */

    $('select').change(function () {
        if ($(this).hasClass('off')) {
            $(this).removeClass('off');
            $(this).addClass('on');
        }
    });

    // Checkbox style

    $('input[type="checkbox"]').on('change', function () {
        var attr = $(this).attr('id');
        var label = $('label[for="' + attr + '"]');
        if ($(this).prop('checked')) {
            label.addClass('checked');
        } else {
            label.removeClass('checked');
        }
    });

    // Fim - Configurações padrão

 
    //MODAL VIDEO
    $(".js-modal-btn-youtube").modalVideo();
    $(".js-modal-btn").modalVideo({channel: 'vimeo'});

    //TRIGGERS PERSONALIZADAS
    function trigger(data) {
        if (data[0]) {
            $.each(data, function (key, value) {
                triggerNotify(data[key]);
            });
        } else {
            triggerNotify(data);
        }
    }

    function triggerNotify(data) {

        var triggerContent = "<div class='trigger_notify trigger_notify_" + data.color + "' style='left: 100%; opacity: 0;'>";
        triggerContent += "<p><i class='" + data.icon + "'></i> " + data.title + "</p>";
        triggerContent += "<span class='trigger_notify_timer'></span>";
        triggerContent += "</div>";

        if (!$('.trigger_notify_box').length) {
            $('body').prepend("<div class='trigger_notify_box'></div>");
        }

        $('.trigger_notify_box').prepend(triggerContent);
        $('.trigger_notify').stop().animate({'left': '0', 'opacity': '1'}, 200, function () {
            $(this).find('.trigger_notify_timer').animate({'width': '100%'}, data.timer, 'linear', function () {
                $(this).parent('.trigger_notify').animate({'left': '100%', 'opacity': '0'}, function () {
                    $(this).remove();
                });
            });
        });

        $('body').on('click', '.trigger_notify', function () {
            $(this).animate({'left': '100%', 'opacity': '0'}, function () {
                $(this).remove();
            });
        });
    }

}); // document.ready