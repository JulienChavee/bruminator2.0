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
