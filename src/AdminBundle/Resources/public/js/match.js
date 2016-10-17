$('button[data-action="generateMatch"]').on('click', function() {
    var modal = $('.modal_alert_confirmation');
    modal.find('.modal-body-more-info').text("Seules les équipes ayant été validées seront sélectionnées pour la génération des matchs");
    modal.find('.modal-confirmation-yes').attr('data-action', 'generateMatch')

    modal.modal('show');
});

$('.modal_alert_confirmation').on('click', '.modal-confirmation-yes[data-action="generateMatch"]', function(){
    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_match_ajax_generate'),
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                location.reload();
            } else {
                $('.modal-body-more-info').html(data.message);
                $('.modal_alert_error').modal('show');

                console.log(data.debug);
            }
        }
    });
});

$('body').on('click', '.editDate i[data-action="edit"]', function() {
    var id = $(this).data('id');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_match_ajax_get_date'),
        data: {
            id: id
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                $('div[data-id="' + id + '"] .editDate')
                    .empty()
                    .append(
                        $('<input>').addClass('form-control inputDate').attr('type', 'text').attr('placeholder', 'Date du match (laisser vide pour aucune date)').val(data.return != null ? moment(data.return.date).format("DD/MM/YYYY HH:mm") : '')
                    ).append(
                        $('<button>').addClass('btn btn-primary m-t-1 m-r-1').text('Valider').attr('data-id', id).attr('data-action', 'validate')
                    ).append(
                        $('<button>').addClass('btn btn-outline-secondary m-t-1').text('Annuler').attr('data-id', id).attr('data-action', 'cancel')
                    );
            } else {
                $('div[data-id="' + id + '"] .editDate')
                    .empty()
                    .append(
                        $('<input>').addClass('form-control inputDate').attr('type', 'text').attr('placeholder', 'Date du match (laisser vide pour aucune date)')
                    ).append(
                        $('<button>').addClass('btn btn-primary m-t-1 m-r-1').text('Valider').attr('data-id', id).attr('data-action', 'validate')
                    ).append(
                        $('<button>').addClass('btn btn-outline-secondary m-t-1').text('Annuler').attr('data-id', id).attr('data-action', 'cancel')
                    );
            }

            $('.form-control[type="text"]').datetimepicker({
                locale : 'fr',
                format: 'DD/MM/YYYY HH:mm',
                useCurrent : false,
                icons: {
                    time: 'fa fa-clock-o',
                    date: 'fa fa-calendar',
                    up: 'fa fa-chevron-up',
                    down: 'fa fa-chevron-down',
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-screenshot',
                    clear: 'fa fa-trash',
                    close: 'fa fa-remove'
                }
            });
        }
    });

});

$('body').on('click', '.editDate button[data-action="validate"]', function() {
    var id = $(this).data('id');
    var date = $('.inputDate').val();

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_match_ajax_edit_date'),
        data: {
            id: id,
            date: date
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                var line = $('div[data-id="' + id + '"]');
                line.replaceWith(data.return);

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

$('body').on('click', '.editDate button[data-action="cancel"]', function(){
    var id = $(this).data('id');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_match_ajax_get_date'),
        data: {
            id: id
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                if( data.return != null) {
                    $('div[data-id="' + id + '"] .editDate').empty().append(moment(data.return.date).locale('fr').format("DD MMMM YYYY - HH:mm"));
                } else {
                    $('div[data-id="' + id + '"] .editDate').empty().append(
                        $('<i>').text('Aucune date pour le moment ')
                    ).append(
                        $('<i>').addClass('fa fa-pencil editable_tool invisible').data('id', id).attr('data-action', 'edit')
                    );
                }
            } else {
                location.reload();
            }
        }
    });

});

function getDate() {

}
