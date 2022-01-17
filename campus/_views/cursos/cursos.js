$(function () {

    //GET PROJETC BASE
    BASE = $("link[rel='base']").attr("href");

    //FORM SUBMIT
    $('.wc_ead').on("submit", "form", function (event) {
        var Form = $(this);

        Form.find('button').prop('disabled', true);

        Form.ajaxSubmit({
            url: BASE + '/campus/_ajax/Campus.ajax.php',
            data: {callback: Form.attr("name")},
            dataType: 'json',
            beforeSubmit: function () {
                Form.find('.jwc_load').fadeIn('fast');
            },
            uploadProgress: function (evento, posicao, total, completo) {
                var porcento = completo + '%';
                $('.jwc_ead_upload_progress').text(porcento);

                if (completo <= '80') {
                    $('.jwc_ead_upload').fadeIn().css('display', 'flex');
                }
                if (completo >= '99') {
                    $('.jwc_ead_upload').fadeOut();
                }
                //PREVENT TO RESUBMIT IMAGES GALLERY
                Form.find('input[name="image[]"]').replaceWith($('input[name="image[]"]').clone());
            },
            success: function (data) {
                //REMOVE LOAD
                Form.find('.jwc_load').fadeOut('slow', function () {

                    //MODAL
                    if (data.modal) {
                        wc_ead_modal(data.modal[0], data.modal[1], data.modal[2], data.modal[3]);
                    }

                    //ALERT
                    if (data.alert) {
                        wc_ead_alert(data.alert[0], data.alert[1], data.alert[2]);
                    }

                    //NOVO MODELO TRIGGER
                    if (data.trigger) {
                        trigger(data.trigger);
                    }

                    //NOVO MODELO TRIGGER MODAL
                    if (data.message) {
                        triggerModal(data.message);
                    }

                    //NOTYFY
                    if (data.notify) {
                        trigger(data.notify);
                    }

                    //TASK FORUM
                    if (data.ead_support) {
                        if (data.ead_support_id) {
                            if (!$('#' + data.ead_support_id + ' .wc_ead_course_task_forum_response .dash_view_class_support_ticket').length) {
                                $('.dash_view_class_support #' + data.ead_support_id + ' .wc_ead_course_task_forum_response').html(data.ead_support_content);
                            } else {
                                $(data.ead_support_content).insertAfter('.dash_view_class_support #' + data.ead_support_id + ' .wc_ead_course_task_forum_response .dash_view_class_support_ticket:last-child');
                            }
                        } else {
                            $('.wc_ead_course_task_forum_none').fadeOut(200);
                            $('.jwc_content').html(data.ead_support_content).fadeIn(300);
                        }
                    }

                    //FIX FOR HIGHLIGHT
                    setTimeout(function () {
                        if ($('*[class="brush: php;"]').length) {
                            $("head").append('<link rel="stylesheet" href="../../_cdn/highlight.min.css">');
                            $.getScript('../../_cdn/highlight.min.js', function () {
                                $('*[class="brush: php;"]').each(function (i, block) {
                                    hljs.highlightBlock(block);
                                });
                            });
                        }
                    }, 500);

                    //REVIEW
                    if (data.review) {
                        $('.jwc_review_target').html(data.review);
                    }

                    //RDIRECT
                    if (data.redirect) {
                        setTimeout(function () {
                            window.location.href = data.redirect;
                        }, 1000);
                    }

                    //CLOSE MODAL
                    if (data.close) {
                        $(data.close).fadeOut();
                    }

                    //FORUM CLEAR
                    if (data.clear) {
                        Form.trigger('reset');
                    }
                });
            },
            complete: function () {
                setTimeout(function () {
                    Form.find('button').prop('disabled', false);
                }, 3000);
            }
        });
        return false;
    });

    //TROCAR O BOTÃO LOCKER > CONHECER
    $('.j_locked').mouseenter(function(){
        $(this).find('.cadeado').fadeOut();
        $(this).find('.button-more').fadeIn().css('display', 'inline-block');
    });

    $('.j_locked').mouseleave(function(){
        $(this).find('.button-more').fadeOut();
        $(this).find('.cadeado').fadeIn().css('display', 'block');
    });

    //ALL SUPPORT
    $('.wc_ead').on('click', '.wc_ead_allsupport', function () {
        if (!$(this).hasClass('btn_green')) {
            $(this).removeClass('btn_blue').addClass('btn_green');
            $('.wc_ead_mysupport').removeClass('btn_green').addClass('btn_blue');
            $('.jwc_mysupport').fadeOut(function () {
                $('.jwc_allsupport').fadeIn(400);
            });
        }
    });

    //MY SUPPORT
    $('.wc_ead').on('click', '.wc_ead_mysupport', function () {
        if (!$(this).hasClass('btn_green')) {
            $(this).removeClass('btn_blue').addClass('btn_green');
            $('.wc_ead_allsupport').removeClass('btn_green').addClass('btn_blue');
            $('.jwc_allsupport').fadeOut(function () {
                $('.jwc_mysupport').fadeIn(400);
            });
        }
    });

    //INICIO DA NOVA TRIGGER
    //ALERT SETUP
    $('.wc_ead').on('click', '.trigger_modal_close', function () {
        var EadAlert = $(".trigger_");
        $('.wc_ead_alert').fadeOut(200, function () {
            setTimeout(function () {
                EadAlert.removeClass("blue green red yellow");
                EadAlert.find('.trigger_modal_content_title').html("{TITLE}");
                EadAlert.find('.trigger_modal_content_message').html("{CONTENT}");
            }, 210);
        });
    });

    //ALERT DISPLAY
    function wc_ead_alert(Color, Title, Content) {
        var EadAlert = $(".trigger_");

        //REMOVE LOAD
        $(".jwc_load").fadeOut(200);

        EadAlert.addClass(Color);
        EadAlert.find('.trigger_modal_content_title').html(Title);
        EadAlert.find('.trigger_modal_content_message').html(Content);
        $('.wc_ead_alert').fadeIn(200).css('display', 'flex');
    }

    //MODAL SETUP
    $('.wc_ead').on('click', '.wc_ead_modal_close', function () {
        var Modal = $(".trigger_");
        $('.wc_ead_modal').fadeOut(200, function () {
            setTimeout(function () {
                Modal.find('.trigger_modal_content_title').removeClass("blue green red yellow");
                Modal.find('.trigger_modal_content_title').removeClass().html("{TITLE}");
                Modal.find('.trigger_modal_content_message').html("{CONTENT}");
            }, 210);
        });
    });

    //MODAL DISPLAY
    function wc_ead_modal(Color, Icon, Title, Content) {
        var Modal = $(".trigger_");

        //REMOVE LOAD
        $(".jwc_load").fadeOut(200);

        Modal.find('.trigger_modal_content_title').addClass(Color);
        Modal.find('.trigger_modal_content_title').addClass("icon-" + Icon).html(Title);
        Modal.find('.trigger_modal_content_message').html(Content);
        $('.wc_ead_modal').fadeIn(200).css('display', 'flex');
    }

    //FINAL DA NOVA TRIGGER


    //ALERT SETUP
    $('.wc_ead').on('click', '.wc_ead_alert_close', function () {
        var EadAlert = $(".wc_ead_alert_box");
        $('.wc_ead_alert').fadeOut(200, function () {
            setTimeout(function () {
                EadAlert.removeClass("blue green red yellow");
                EadAlert.find('.wc_ead_alert_title').html("{TITLE}");
                EadAlert.find('.wc_ead_alert_content').html("{CONTENT}");
            }, 210);
        });
    });

    //ALERT DISPLAY
    function wc_ead_alert(Color, Title, Content) {
        var EadAlert = $(".wc_ead_alert_box");

        //REMOVE LOAD
        $(".jwc_load").fadeOut(200);

        EadAlert.addClass(Color);
        EadAlert.find('.wc_ead_alert_title').html(Title);
        EadAlert.find('.wc_ead_alert_content').html(Content);
        $('.wc_ead_alert').fadeIn(200).css('display', 'flex');
    }

    //MODAL SETUP
    $('.wc_ead').on('click', '.wc_ead_modal_close', function () {
        var Modal = $(".wc_ead_modal_box");
        $('.wc_ead_modal').fadeOut(200, function () {
            setTimeout(function () {
                Modal.find('.wc_ead_modal_title').removeClass("blue green red yellow");
                Modal.find('.wc_ead_modal_title span').removeClass().html("{TITLE}");
                Modal.find('.wc_ead_modal_content').html("{CONTENT}");
            }, 210);
        });
    });

    //MODAL DISPLAY
    function wc_ead_modal(Color, Icon, Title, Content) {
        var Modal = $(".wc_ead_modal_box");

        //REMOVE LOAD
        $(".jwc_load").fadeOut(200);

        Modal.find('.wc_ead_modal_title').addClass(Color);
        Modal.find('.wc_ead_modal_title span').addClass("icon-" + Icon).html(Title);
        Modal.find('.wc_ead_modal_content').html(Content);
        $('.wc_ead_modal').fadeIn(200).css('display', 'flex');
    }

    //CERTIFICATION GET
    // $('.jwc_ead_certification').click(function () {
    //     $('.jwc_ead_load').fadeIn().css('display', 'flex');
    //     $.post(BASE + "/campus/_ajax/Campus.ajax.php", {
    //         callback: 'wc_ead_studend_certification',
    //         enrollment_id: $(this).attr('id')
    //     }, function (data) {
    //         $('.jwc_ead_load').fadeOut(function () {
    //             if (data.alert) {
    //                 wc_ead_alert(data.alert[0], data.alert[1], data.alert[2]);
    //             }
    //
    //             if (data.reload) {
    //                 setTimeout(function () {
    //                     window.location.reload();
    //                 }, 1000);
    //             }
    //
    //             if (data.modal) {
    //                 wc_ead_modal(data.modal[0], data.modal[1], data.modal[2], data.modal[3]);
    //             }
    //
    //             //NOVO MODELO TRIGGER
    //             if (data.trigger) {
    //                 trigger(data.trigger);
    //             }
    //
    //             //NOVO MODELO TRIGGER MODAL
    //             if (data.message) {
    //                 triggerModal(data.message);
    //             }
    //
    //             if (data.certification) {
    //                 wcEadWin(data.certification);
    //             }
    //         });
    //     }, 'json');
    // });



    //CERTIFICATION GET
    $('.jwc_ead_certification').click(function () {
        $('.jwc_ead_load').fadeIn().css('display', 'flex');
        $.post(BASE + "/campus/_ajax/Campus.ajax.php", {callback: 'wc_ead_studend_certification', enrollment_id: $(this).attr('id')}, function (data) {
            $('.jwc_ead_load').fadeOut(function () {
                if (data.alert) {
                    wc_ead_alert(data.alert[0], data.alert[1], data.alert[2]);
                }

                if (data.reload) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                }

                if (data.modal) {
                    wc_ead_modal(data.modal[0], data.modal[1], data.modal[2], data.modal[3]);
                }

                if (data.certification) {
                    wcEadWin(data.certification);
                }
            });
        }, 'json');
    });



    //WC EAD DEFAULT WIN
    function wcEadWin(WinSetupObject) {
        var WC_WIN = $('.jwc_ead_win');
        var WC_WIN_CONTENT = WC_WIN.html();

        WC_WIN.html(
            WC_WIN_CONTENT.replace("{{IMAGE}}", WinSetupObject.Image)
                .replace("{{ICON}}", WinSetupObject.Icon)
                .replace("{{TITLE}}", WinSetupObject.Title)
                .replace("{{CONTENT}}", WinSetupObject.Content)
                .replace("{{LINK}}", WinSetupObject.Link)
                .replace("{{LINK_ICON}}", WinSetupObject.LinkIcon)
                .replace("{{LINK_TITLE}}", WinSetupObject.LinkTitle)
                .replace("{{LINK_NAME}}", WinSetupObject.LinkTitle)
        );
        WC_WIN.fadeIn().css('display', 'flex');
    }

    //IMAGE LOAD
    $('.wc_loadimage').change(function () {
        var input = $(this);
        var target = $('.' + input.attr('id'));
        var fileDefault = target.attr('default');

        if (!input.val()) {
            target.fadeOut('fast', function () {
                $(this).attr('src', fileDefault).fadeIn('slow');
            });
            return false;
        }

        if (this.files && (this.files[0].type.match("image/jpeg") || this.files[0].type.match("image/png"))) {
            var reader = new FileReader();
            reader.onload = function (e) {
                target.fadeOut('fast', function () {
                    $(this).attr('src', e.target.result).fadeIn('fast');
                });
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            wc_ead_alert("yellow", "Imagem Inválida:", "Envie uma imagem JPG ou PNG com 500x500px!");

            target.fadeOut('fast', function () {
                $(this).attr('src', fileDefault).fadeIn('slow');
            });
            input.val('');
            return false;
        }
    });

    //TASK MANAGER
    if ($('.jwc_ead_task').length) {
        var TaskTarget = $('.jwc_ead_task');
        var TaskRepeat = setInterval(function () {
            $.post(BASE + '/campus/_ajax/Campus.ajax.php', {callback: 'wc_ead_student_task_manager'}, function (data) {
                if (data.aprove) {
                    TaskTarget.fadeTo(400, 0.5, function () {
                        TaskTarget.html(data.aprove).fadeTo(400, 1);
                    });
                }

                if (data.check) {
                    TaskTarget.fadeTo(400, 0.5, function () {
                        TaskTarget.html(data.check).fadeTo(400, 1);
                    });
                }

                if (data.stop) {
                    clearTimeout(TaskRepeat);
                }
            }, 'json');
        }, 10000);
    }

    //TASK MANAGER :: MANUAL CHECK
    $('.wc_ead').on('click', '.jwc_ead_task_check', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var TaskTarget = $('.jwc_ead_task');
        $.post(BASE + '/campus/_ajax/Campus.ajax.php', {callback: 'wc_ead_student_task_manager_check'}, function (data) {
            if (data.check) {
                TaskTarget.fadeTo(400, 0.5, function () {
                    TaskTarget.html(data.check).fadeTo(400, 1);
                });
            }

            if (data.modal) {
                wc_ead_modal(data.modal[0], data.modal[1], data.modal[2], data.modal[3]);
            }

            if (data.alert) {
                wc_ead_alert(data.alert[0], data.alert[1], data.alert[2]);
            }

            //NOVO MODELO TRIGGER
            if (data.trigger) {
                trigger(data.trigger);
            }

            //NOVO MODELO TRIGGER MODAL
            if (data.message) {
                triggerModal(data.message);
            }
        }, 'json');
    });

    //TASK MANAGER :: MANUAL UNCHECK
    $('.wc_ead').on('click', '.jwc_ead_task_uncheck', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var TaskTarget = $('.jwc_ead_task');
        $.post(BASE + '/campus/_ajax/Campus.ajax.php', {callback: 'wc_ead_student_task_manager_uncheck'}, function (data) {
            if (data.check) {
                TaskTarget.fadeTo(400, 0.5, function () {
                    TaskTarget.html(data.check).fadeTo(400, 1);
                });
            }

            if (data.modal) {
                wc_ead_modal(data.modal[0], data.modal[1], data.modal[2], data.modal[3]);
            }

            if (data.alert) {
                wc_ead_alert(data.alert[0], data.alert[1], data.alert[2]);
            }

            //NOVO MODELO TRIGGER
            if (data.trigger) {
                trigger(data.trigger);
            }

            //NOVO MODELO TRIGGER MODAL
            if (data.message) {
                triggerModal(data.message);
            }
        }, 'json');
    });

    //TASK MANAGER :: CLOSE MODAL REPLY
    $('.wc_ead').on('click', '.j_wc_ticket_close', function () {
        $('.wc_ead_course_task_modal').fadeOut(200);
    });

    //TASK MANAGER :: OPEN MODAL REPLY
    $('.wc_ead').on('click', '.jwc_ticket_review', function () {
        $('.jwc_ticket_review_content').find("input[name='support_id']").val($(this).attr("id"));
        $('.jwc_ticket_review_content').fadeIn(200).css('display', 'flex');
    });

    //TASK MANAGER :: OPEN MODAL REVIEW
    $('.wc_ead').on('click', '.jwc_ticket_reply', function () {
        $('.jwc_ticket_reply_content').find("input[name='support_id']").val($(this).attr("id"));
        $('.jwc_ticket_reply_content').fadeIn(200).css('display', 'flex');
    });

    //STUDENT FIX LOGIN ON PLAY
    if ($('.jwc_ead_restrict').length) {
        setInterval(function () {
            $.post(BASE + '/campus/_ajax/Campus.ajax.php', {callback: 'wc_ead_login_fix'}, function (data) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            }, 'json');
        }, 60000);
    }

    //WC EAD BONUS CLOSE
    $('.wc_ead').on('click', '.jwc_ead_close_bonus', function () {
        $('.wc_ead_win').fadeOut(200, function () {
            window.location.reload();
        });
    });

    //WC EAD TAB AUTOCLICK
    if (window.location.hash) {
        $("a[href='" + window.location.hash + "']").click();
        if (window.location.hash == '#orders') {
            $('html, body').animate({scrollTop: 0}, 300);
        }
    }

    //NEW LINE ACTION
    $('textarea').keypress(function (event) {
        if (event.which === 13) {
            var s = $(this).val();
            $(this).val(s + "\n");
        }
    });

    /*
     * CLASS PLAY VIMEO
     */
    if ($(".dash_view_class_media_player").length) {
        var videoPlayer = $(".dash_view_class_media_player");
        var videoRepeat = 1000 * 10;

        $.getScript(BASE + "/_cdn/vimeoplayer.js", function () {
            var vimeo = (videoPlayer.find(".dash_view_class_media_player_vimeo").length ? new Vimeo.Player(document.querySelector(".dash_view_class_media_player_vimeo")) : null);
            var vimeoPlay = null;

            //vimeo player
            if (vimeo) {
                clearInterval(vimeoPlay);

                //start progress
                if (videoPlayer.attr('data-progress') >= 20) {
                    vimeo.setCurrentTime(videoPlayer.attr('data-progress') - 10).then(function () {
                        vimeo.pause();
                    });
                }

                //folder pause
                $(".class_folder").click(function () {
                    vimeo.pause();
                });

                //PLAYER ACTIONS :: play
                vimeo.on("play", function (play) {
                    var vimeoSeconds = 0;
                    vimeo.on('timeupdate', function (time) {
                        vimeoSeconds = time.seconds;
                    });

                    var vimeoPlay = setInterval(function () {
                        $.post(FILE, {
                            case: "course_class_play",
                            class_id: videoPlayer.attr("data-class"),
                            progress: vimeoSeconds,
                            seconds: videoRepeat
                        }, function (data) {
                            //TRIGGER CONTROL
                            if (data.trigger) {
                                trigger(data.trigger);
                            }

                            //STOP TIMER
                            if (data.stop) {
                                vimeo.pause();
                                clearInterval(vimeoPlay);
                            }

                            //FREE
                            if (data.classfree) {
                                studentClassFree(data);
                            }
                        }, 'json');
                    }, videoRepeat);

                    //PLAYER ACTIONS :: pause
                    vimeo.on('pause', function () {
                        clearInterval(vimeoPlay);
                    });

                    //PLAYER ACTIONS :: finish
                    vimeo.on('ended', function () {
                        clearInterval(vimeoPlay);
                        $.post(FILE, {
                            case: "course_class_play",
                            class_id: videoPlayer.attr("data-class"),
                            progress: "ended",
                            seconds: videoRepeat
                        });
                    });
                });
            } else {
                //OTHER PLAYER
                var otherPlayer = setInterval(function () {
                    $.post(FILE, {
                        case: "course_class_play",
                        class_id: videoPlayer.attr("data-class"),
                        progress: null,
                        seconds: videoRepeat
                    }, function (data) {
                        //TRIGGER CONTROL
                        if (data.trigger) {
                            trigger(data.trigger);
                        }

                        //FREE
                        if (data.classfree) {
                            studentClassFree(data);
                        }
                    }, 'json');
                }, videoRepeat);
            }
        });
    }

    //WC EAD TEXT EDITOR
    if ($('.jwc_ead_editor').length) {
        tinyMCE.init({
            selector: "jwc_ead_editor",
            language: 'pt_BR',
            menubar: false,
            theme: "modern",
            height: 200,
            verify_html: true,
            skin: 'light',
            entity_encoding: "raw",
            theme_advanced_resizing: true,
            plugins: [
                "paste autolink link"
            ],
            toolbar: "styleselect | removeformat |  bold | italic | link | unlink",
            content_css: BASE + "/admin/_css/tinyMCE.css",
            style_formats: [
                {title: 'Normal', block: 'p'},
                {title: 'Código', block: 'pre', classes: 'brush: php;'}
            ],
            link_title: false,
            target_list: false,
            media_dimensions: false,
            media_poster: false,
            media_alt_source: false,
            media_embed: false,
            extended_valid_elements: "a[href|target=_blank|rel|class]",
            image_dimensions: false,
            relative_urls: false,
            remove_script_host: false,
            resize: false,
            paste_as_text: true
        });
    }

    /*
     * FOLDER CONTROL
     */
    if ($(".dash_view_class_folder").length) {
        var folder = $(".dash_view_class_folder");
        $(".class_folder").click(function (e) {
            folder.fadeIn(1, function () {
                $("html").css("overflow", "hidden");
                folder.animate({"right": "0"}, 400, function () {
                    $(".dash_view_class_folder_close").fadeIn(1);
                });
            });
        });

        $(".dash_view_class_folder_close").click(function () {
            $(this).fadeOut(1, function () {
                $("html").css("overflow", "auto");
                folder.animate({"right": "-700px"}, 400, function () {
                    $(this).fadeOut(1);
                });
            });
        });
    }

    /**
     * SUPPORT EDITOR
     */
    if ($('.editor').length) {
        tinyMCE.init({
            selector: "textarea.editor",
            language: 'pt_BR',
            menubar: false,
            theme: "modern",
            statusbar: false,
            autoresize_min_height: 20,
            autoresize_bottom_margin: 0,
            autoresize_overflow_padding: 15,
            verify_html: true,
            skin: 'light',
            entity_encoding: "raw",
            theme_advanced_resizing: true,
            plugins: [
                "paste autolink link autoresize fullscreen"
            ],
            toolbar: "styleselect |  bold | italic | link | unlink",
            content_css: BASE + "/beta/_js/tinymce/tinyMCE.css",
            style_formats: [
                {title: 'Normal', block: 'p'},
                {title: 'Título', block: 'h3'},
                {title: 'Subtítulo', block: 'h4'},
                {title: 'Código', block: 'pre', classes: 'brush: php;'}
            ],
            link_title: false,
            target_list: false,
            media_dimensions: false,
            media_poster: false,
            media_alt_source: false,
            media_embed: false,
            extended_valid_elements: "a[href|target=_blank|rel|class]",
            image_dimensions: false,
            relative_urls: false,
            remove_script_host: false,
            resize: false,
            paste_as_text: true
        });
    }

    /*
     * CLASS MENU
     */
    if ($(".dash_view_class").length) {
        $(window).scroll(function () {
            if ($(window).scrollTop() > $(".dash_view_class_media").next().offset().top + 500 && !$(".dash_view_class_media_tools_suspense").length) {
                $(".dash_view_class").append("<div class='dash_view_class_media_tools_suspense'><div class='dash_view_class_media_tools'>" + $(".dash_view_class_media_tools").html() + "</div></div>");
                $(".dash_view_class_media_tools_suspense").animate({"opacity": "1"}, 200);
            } else if ($(window).scrollTop() < $(".dash_view_class_media").next().offset().top && $(".dash_view_class_media_tools_suspense").length) {
                $(".dash_view_class_media_tools_suspense").animate({"opacity": "0"}, 200, function () {
                    $(this).remove();
                });
            }
        });
    }

    /*
     * PLAY
     */

    //TASK MANAGER :: MANUAL CHECK
    $('.wc_ead').on('click', '.jwc_clas_task_check', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var classId = $('input[name="classId"]').val();
        var userId = $('input[name="userId"]').val();

        var TaskTarget = $('.jwc_clas_task_check');
        $.post(BASE + '/campus/_ajax/Campus.ajax.php', {
            callback: 'wc_student_task_manager_check',
            classId: classId,
            userId: userId
        }, function (data) {
            if (data.check) {
                TaskTarget.fadeTo(400, 0.5, function () {
                    TaskTarget.html(data.check).fadeTo(400, 1);
                });
            }

            if (data.modal) {
                wc_ead_modal(data.modal[0], data.modal[1], data.modal[2], data.modal[3]);
            }

            if (data.alert) {
                wc_ead_alert(data.alert[0], data.alert[1], data.alert[2]);
            }

            //NOVO MODELO TRIGGER
            if (data.trigger) {
                trigger(data.trigger);
            }

            //NOVO MODELO TRIGGER MODAL
            if (data.message) {
                triggerModal(data.message);
            }
        }, 'json');
    });

    $('.app_play_post_video_media_more_item').click(function () {

        var videoPlay = $(this);
        var videoEmbed = $(".app_play_post_video_media .embed-container");
        var videoId = videoPlay.data('video');
        var videoColor = videoPlay.data('color');

        videoEmbed.fadeTo(200, 0, function () {
            videoEmbed.find("iframe").attr('src', "https://player.vimeo.com/video/" + videoId + "?color=" + videoColor + "&title=0&byline=0&portrait=0");
            $('.app_play_post_video_media_more_item').removeClass('now icon-mug').addClass('icon-play2');
            videoPlay.addClass('now icon-mug').removeClass('icon-play2');
            videoEmbed.delay(600).fadeTo(200, 1);
        });
    });

    $("[data-played]").click(function (e) {
        var played = $(this);

        if (played.hasClass("played")) {
            played
                .addClass("icon-checkbox-unchecked")
                .removeClass("icon-checkbox-checked played")
                .text("Marcar como concluída");
        } else {
            played
                .addClass("icon-checkbox-checked played")
                .removeClass("icon-checkbox-unchecked")
                .text("Concluída");
        }

        $.post(FILE, {case: 'play_played', notsleep: true}, function (data) {
            if (data.reload) {
                window.location.reload();
            }
        }, "json");
    })

    $("[data-review]").click(function (e) {
        var review = $(this);
        var star = $(this).index() + 1;

        review.addClass("icon-star-full").removeClass("icon-star-empty");
        review.prevAll().addClass("icon-star-full").removeClass("icon-star-empty");
        review.nextAll().addClass("icon-star-empty").removeClass("icon-star-full");
        $.post(FILE, {case: 'play_review', review: star, notsleep: true});
    })

    $("[data-vote]").click(function () {
        var vote = $(this);
        var voteId = vote.data("vote");

        $.post(FILE, {case: 'play_vote', play_id: voteId, notsleep: true}, function (callback) {
            if (callback.trigger) {
                trigger(callback.trigger);
            }

            if (callback.vote) {
                $(".votes_" + voteId).html(callback.vote.votes);
                $(".lack_" + voteId).html(callback.vote.lack);
                vote.addClass("active");
            }
        }, "json");
    });

    /**
     * HIGHLIGHT
     */
    if ($('*[class="brush: php;"]').length) {
        $("head").append('<link rel="stylesheet" href="' + BASE + '/_cdn/highlight.min.css">');
        $.getScript(BASE + '/_cdn/highlight.min.js', function () {
            $('*[class="brush: php;"]').each(function (i, block) {
                hljs.highlightBlock(block);
            });
        });
    }

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

/*
 * studentClassFree: controla ajustes de botão na aula
 * @param {type} arrTrigger
 */
function studentClassFree(data) {
    if (data.classfree[0] === 'free') {
        var freeBtn = "<span class='icon-checkbox-unchecked'>CONCLUIR AULA</span>";
        $(".classcheck").html(freeBtn).removeClass("classpending classfinish").attr({
            "data-action": "course_class_check",
            "data-class": data.classfree[1]
        });

    } else if (data.classfree[0] === 'check') {
        var freeBtn = "<span class='icon-checkbox-checked'>AULA CONCLUÍDA</span>";
        $(".classcheck").html(freeBtn).removeClass("classpending").addClass("classfinish").attr({
            "data-action": "course_class_check",
            "data-class": data.classfree[1]
        });
    }
}