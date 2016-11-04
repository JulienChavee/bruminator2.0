$('.addTeam').on('click', function() {
    var button = $(this);

    button.attr('disabled', 'disabled');
    button.prepend($('<i>').addClass('fa fa-spinner fa-pulse'));

    var teamName = $('#team_name').val();
    var dispo = $('#team_disponibilite').val();
    var players = {};

    for(var i = 1; i<=4; i++) {
        players[i] = {
            "name" : $('#player_' + i).val(),
            "level" : $('#player_level_' + i).val(),
            "class" : $('#player_class_' + i).val(),
            "remplacant" : {
                "name" : $('#player_remplacant_' + i).val(),
                "level" : $('#player_remplacant_level_' + i).val()
            }
        }
    }

    $.ajax({
        type: 'POST',
        url: Routing.generate('team_ajax_registration'),
        data: {
            name: teamName,
            players: JSON.stringify(players),
            dispo : dispo
        },
        error: function (request, error) {
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                location.href = Routing.generate('team_homepage');
            } else {
                $.each(data.message, function(i, item) {
                    $('.modal-body-more-info').append(
                        $('<span>').addClass("text-danger").append(
                            $('<i>').addClass('fa fa-circle')
                        ).append(" " + item)
                            .append($('<br>'))
                    )
                });
                $('.modal_alert_error').modal('show');
                console.log(data.debug);

                button.find('.fa').remove();
                button.removeAttr('disabled');
            }
        }
    });
});

$('.disponibilites').on('click', 'i[data-action="edit"]', function() {
    var id = $(this).data('id');

    $.ajax({
        type: 'POST',
        url: Routing.generate('team_ajax_get_dispo'),
        data: {
            id: id
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if(data.status == 'ok') {
                $('.disponibilites.editable_content').find('div').empty().append(
                    $('<textarea>').addClass('form-control input_disponibilite').text(data.return)
                ).append(
                    $('<button>').addClass('btn btn-primary mt-1 pull-xs-right update_dispo').html('<i class="fa fa-pencil"></i> Enregistrer').data('id', id)
                );
            } else {
                $('.modal-body-more-info').html(data.message);
                $('.modal_alert_error').modal('show');
                console.log(data.debug);
            }
        }
    });
});

$('body').on('click', '.update_dispo', function() {
    var id = $(this).data('id');
    var dispo = $('.input_disponibilite').val();
    var button = $(this);

    button.attr('disabled', 'disabled');
    button.find('.fa').removeClass('fa-pencil').addClass('fa-spinner fa-pulse');

    $.ajax({
        type: 'POST',
        url: Routing.generate('team_ajax_update_dispo'),
        data: {
            id: id,
            dispo: dispo
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if(data.status == 'ok') {
                var line = $('.disponibilites.editable_content').find('div');
                line.replaceWith(data.return);
                line = $('.disponibilites.editable_content').find('div');
                line.effect("highlight", {color: '#c9c9c9'}, 5000);

                $('.modal_alert_success').modal('show');
                setTimeout(function () {
                    $(".modal_alert_success").modal('hide');
                }, 1700);
            } else {
                $('.modal-body-more-info').html(data.message);
                $('.modal_alert_error').modal('show');
                console.log(data.debug);

                button.find('.fa').removeClass('fa-pulse fa-spinner').addClass('fa-pencil');
                button.removeAttr('disabled');
            }
        }
    });
});
