$('.addTeam').on('click', function() {
    var button = $(this);

    button.attr('disabled', 'disabled');
    button.prepend($('<i>').addClass('fa fa-spinner fa-pulse'));

    var teamName = $('#team_name').val();
    var dispo = $('#team_disponibilite').val();
    var players = {};

    for(var i = 1; i<=$('.nb_players_team').val(); i++) {
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
                if($.isArray(data.message)) {
                    $.each(data.message, function (i, item) {
                        $('.modal-body-more-info').append(
                            $('<span>').addClass("text-danger").append(
                                $('<i>').addClass('fa fa-circle')
                            ).append(" " + item.message)
                                .append($('<br>'))
                        )
                    });
                } else
                    $('.modal-body-more-info').html(data.message);

                $('.modal_alert_error').modal('show');
                console.log(data.debug);

                button.find('.fa').remove();
                button.removeAttr('disabled');
            }
        }
    });
});

$('button[data-action="register"]').on('click', function(){
    var id = $(this).data('id');
    var type = $(this).data('type');

    $.ajax({
        type: 'POST',
        url: Routing.generate('team_ajax_register_to_new_edition'),
        data: {
            id: id,
            action: type=='yes' ? 'register' : 'new'
        },
        error: function (request, error) {
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                if(type=='new')
                    location.href = Routing.generate('team_registration');
                else
                    location.href = Routing.generate('team_homepage');
            } else {
                $('.modal-body-more-info').html(data.message);

                $('.modal_alert_error').modal('show');
                console.log(data.debug);
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

$('#btn_search').on('click', function(){
    var button = $(this);

    button.attr('disabled', 'disabled');
    button.find('.fa').removeClass('fa-search').addClass('fa-spinner fa-pulse');

    var search_text = $('#input_search_text').val();
    var only_actual_edition = $('#input_search_edition_only').is(':checked') ? 1 : 0;
    var only_player = $('#select_search_search_in').val() == 'player' ? 1 : 0;
    var only_team = $('#select_search_search_in').val() == 'team' ? 1 : 0;

    search(search_text,only_actual_edition,only_player,only_team)
});

$('.input_player').autocomplete({
    source: Routing.generate('team_player_ajax_search'),
    minLength: 2
});

function search(search_text,only_actual_edition,only_player,only_team) {
    button = $('#btn_search');

    $.ajax({
        type: 'POST',
        url: Routing.generate('team_ajax_search'),
        data: {
            search_text: search_text,
            only_actual_edition: only_actual_edition,
            only_player: only_player,
            only_team: only_team
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if(data.status == 'ok') {
                $('.teams_grid').html(data.return);
            } else {
                $('.modal-body-more-info').html(data.message);
                $('.modal_alert_error').modal('show');

                console.log(data.debug);
            }

            button.find('.fa').removeClass('fa-pulse fa-spinner').addClass('fa-search');
            button.removeAttr('disabled');
        }
    });
}
