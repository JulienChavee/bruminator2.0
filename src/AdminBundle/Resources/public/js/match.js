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
                stepping: 5,
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
                    $('div[data-id="' + id + '"] .editDate').empty()
                        .append(moment(data.return.date).locale('fr').format("DD MMMM YYYY - HH:mm"))
                        .append(
                            $('<i>').addClass('fa fa-pencil editable_tool invisible').data('id', id).attr('data-action', 'edit')
                        );
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

$('body').on('click', '.editArbitre i[data-action="edit"]', function() {
    var id = $(this).data('id');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_match_ajax_get_arbitre'),
        data: {
            id: id
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                $('div[data-id="' + id + '"] .editArbitre')
                    .empty()
                    .append(
                        $('<select>').addClass('custom-select selectArbitre')
                    ).append(
                        $('<div>').append(
                            $('<button>').addClass('btn btn-primary m-t-1 m-r-1').text('Valider').attr('data-id', id).attr('data-action', 'validate')
                        ).append(
                            $('<button>').addClass('btn btn-outline-secondary m-t-1').text('Annuler').attr('data-id', id).attr('data-action', 'cancel')
                        )
                    );

                $.each(data.listArbitres, function (i, item) {
                    $('.selectArbitre').append(
                        $('<option>', {
                            value: item.id,
                            text : item.username
                        }
                    ));
                });
            } else {
                // TODO : Afficher une erreur
                console.log(data.debug)
            }
        }
    });

});

$('body').on('click', '.editArbitre button[data-action="validate"]', function() {
    var id = $(this).data('id');
    var arbitre = $('.selectArbitre').val();

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_match_ajax_edit_arbitre'),
        data: {
            id: id,
            arbitre: arbitre
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

$('body').on('click', '.editArbitre button[data-action="cancel"]', function(){
    var id = $(this).data('id');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_match_ajax_get_arbitre'),
        data: {
            id: id
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                if( data.return != null) {
                    console.log(data.return);
                    $('div[data-id="' + id + '"] .editArbitre').empty()
                        .append(data.return.username)
                        .append(
                            $('<i>').addClass('fa fa-pencil editable_tool invisible').data('id', id).attr('data-action', 'edit')
                        );
                } else {
                    $('div[data-id="' + id + '"] .editArbitre').empty().append(
                        $('<i>').text('Aucun arbitre pour le moment ')
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

$('.updateFeuille').on('click', function() {
    var id = $(this).data('id');

    var attack_mort = $('#attack_nb_mort').val();
    var attack_ini = {1:$('#attack_ini1').val(),2:$('#attack_ini2').val(),3:$('#attack_ini3').val(),4:$('#attack_ini4').val()};
    var attack_retard = $('#attack_retard').val();
    var attack_forfait = $('#attack_forfait').is(":checked");
    var attack_penalite = $('#attack_penalite_raison').val() != '' ? {'suisse':$('#attack_penalite_suisse').val(), 'goulta':$('#attack_penalite_goulta').val(),'raison':$('#attack_penalite_raison').val()} : null;

    var defense_mort = $('#defense_nb_mort').val();
    var defense_ini = {1:$('#defense_ini1').val(),2:$('#defense_ini2').val(),3:$('#defense_ini3').val(),4:$('#defense_ini4').val()};
    var defense_retard = $('#defense_retard').val();
    var defense_forfait = $('#defense_forfait').is(":checked");
    var defense_penalite = $('#defense_penalite_raison').val() != '' ? {'suisse':$('#defense_penalite_suisse').val(), 'goulta':$('#defense_penalite_goulta').val(),'raison':$('#defense_penalite_raison').val()} : null;

    var nb_tours = $('#nombre_tour').val();
    var first_team = $('#first_team').val();

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_match_ajax_update_feuille'),
        data: {
            match: JSON.stringify({
                'id': id,
                'nbTour':nb_tours,
                'first_team':first_team
            }),
            attack: JSON.stringify({
                'morts':attack_mort,
                'ini':attack_ini,
                'retard':attack_retard,
                'forfait':attack_forfait,
                'penalite':attack_penalite
            }),
            defense: JSON.stringify({
                'morts':defense_mort,
                'ini':defense_ini,
                'retard':defense_retard,
                'forfait':defense_forfait,
                'penalite':defense_penalite
            })
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                location.href=Routing.generate('admin_match');
            } else {
                $('.modal-body-more-info').html(data.message);
                $('.modal_alert_error').modal('show');

                console.log(data.debug);
            }
        }
    });
});

$('.nextRonde').on('click', function(){
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
