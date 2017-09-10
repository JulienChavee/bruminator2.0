$('body').on('click', 'button[data-action="hide"]', function() {
    var id = $(this).data('id');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_map_ajax_hide'),
        data: {
            id: id
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                var lineMap = $('.table_maps_tbody tr[data-id="' + id + '"]');
                lineMap.replaceWith(data.return);
                lineMap = $('.table_maps_tbody tr[data-id="' + id + '"]');
                lineMap.effect("highlight", {color: '#c9c9c9'}, 5000);

                $('.modal_alert_success').modal('show');
                setTimeout(function () {
                    $(".modal_alert_success").modal('hide');
                }, 1700);
            } else {
                $('.modal-body-more-info').html(data.message);
                $('.modal_alert_error').modal('show');

                console.log(data.debug);
            }
        }
    });
});

$('.buttonAddMapDate').on('click', function() {
    var button = $(this);
    var modal = $('#addMapDate');

    button.attr('disabled', 'disabled');
    button.find('.fa').removeClass('fa-plus').addClass('fa-spinner fa-pulse');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_map_ajax_show_add_map_date'),
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                modal.find('.modal-body').html(data.return);

                $('#add_map_date').datetimepicker({
                    locale : 'fr',
                    format: 'YYYY-MM-DD',
                    useCurrent : false,
                    icons: {
                        time: 'fas fa-clock',
                        date: 'far fa-calendar',
                        up: 'far fa-chevron-up',
                        down: 'far fa-chevron-down',
                        previous: 'far fa-chevron-left',
                        next: 'far fa-chevron-right',
                        today: 'far fa-screenshot',
                        clear: 'far fa-trash',
                        close: 'far fa-remove'
                    }
                });

                modal.modal('show');
            } else {
                $('.modal-body-more-info').html(data.message);
                $('.modal_alert_error').modal('show');

                console.log(data.debug);
            }
        },
        complete: function(data) {
            button.find('.fa').removeClass('fa-pulse fa-spinner').addClass('fa-plus');
            button.removeAttr('disabled');
        }
    });
});

$('.addMapDate').on('click', function() {
    var button = $(this);
    var modal = $('#addMapDate');

    button.attr('disabled', 'disabled');
    button.find('.fa').removeClass('fa-plus').addClass('fa-spinner fa-pulse');

    var map = $('#add_map_map').val();
    var match = $('#add_map_match').val();
    var date = $('#add_map_date').val();

    $('.addMapError').addClass('hidden-xs-up');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_map_ajax_add_map_date'),
        data: {
            date: date,
            map: map,
            match: match
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                modal.modal('hide');

                $('.table_mapsDate_tbody').prepend(data.return);

                $('.modal_alert_success').modal('show');
                setTimeout(function () {
                    $(".modal_alert_success").modal('hide');
                }, 1700);
            } else {
                $('.addMapError').html(data.message).removeClass('hidden-xs-up');

                console.log(data.debug);
            }
        },
        complete: function(data) {
            button.find('.fa').removeClass('fa-pulse fa-spinner').addClass('fa-plus');
            button.removeAttr('disabled');
        }
    });
});