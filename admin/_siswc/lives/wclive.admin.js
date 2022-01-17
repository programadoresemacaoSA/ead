$(function () {
    var BASE = $('link[rel="base"]').attr('href');

    //RELOAD COMMENTS
    $('.j_reload').click(function () {
        FB.XFBML.parse($('#comments').get(0));
    });

    if ($('.j_online').length) {
        setInterval(function () {
            Live();
        }, 5 * 1000);
    }
    /*DATATABLE*/
    if ($('.jwc_datatable').length) {
        live_dataTables();
    }

    /*SELECT ACTIVE*/
    $('.live_mode').on('change', function (){
       var mode = $('.live_mode').val();
       if(mode === '1'){
           $('.js_active').fadeIn('100');
       }else{
           $('.js_active').fadeOut('100');
       }
       
       if(mode === '3'){
           $('.js_system').fadeIn('100');
       }else{
           $('.js_system').fadeOut('100');
       }
       
       if (mode === "4") {
            $('.js_mailchimp').fadeIn(100);
        } else {
            $('.js_mailchimp').fadeOut(100);
        }
    });

    function Live() {

        var event_id = $('input[name="live_id"]').val();

        $.post(BASE + "_ajax/Lives.ajax.php", {callback: 'Lives', callback_action: 'live', live_id: event_id}, function (data) {
            if (data.online_users && data.online_pitch && data.live_views) {
                $('.j_online').html(data.online_users);
                $('.j_online_pitch').html(data.online_pitch);
                $('.j_live_views').html(data.live_views);
            }

            if (data.live_offer_clicks && data.live_offer_sales && data.OfferStats && data.OfferTotal) {
                $('.j_live_offer_clicks').html(data.live_offer_clicks);
                $('.j_live_offer_sales').html(data.live_offer_sales);
                $('.j_OfferStats').html(data.OfferStats);
                $('.j_OfferTotal').html(data.OfferTotal);
            }

            if (data.live_leads) {
                $('.j_live_leads').html(data.live_leads);
            }

            if (data.OrderCms && data.OrderCurrency) {
                $('.j_OrderCms').html(data.OrderCms);
                $('.j_OrderCurrency').html(data.OrderCurrency);
            }



        }, 'json');
    }

});

function live_dataTables(){
    $('.jwc_datatable').DataTable({
        "language": {
            "sEmptyTable": "Nenhum registro encontrado",
            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "_MENU_ resultados por página",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sZeroRecords": "Nenhum registro encontrado",
            "sSearch": "Pesquisar",
            "oPaginate": {
                "sNext": "Próximo",
                "sPrevious": "Anterior",
                "sFirst": "Primeiro",
                "sLast": "Último"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            }
        },
        "info": false
    });

}


