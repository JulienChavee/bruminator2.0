$('.table_teams_tbody').on('click', 'button[data-action="validate"]', function() {
    var id = $(this).data('id');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_team_ajax_validate'),
        data: {
            id: id
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if(data.status == 'ok') {
                var line = $('tr[data-id="' + id + '"]');
                line.replaceWith(data.return);
                line = $('tr[data-id="' + id + '"]');
                line.effect("highlight", {color: '#c9c9c9'}, 5000);

                $('.modal_alert_success').modal('show');
                setTimeout(function(){
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

$('.table_teams_tbody').on('click', 'button[data-action="pay"]', function() {
    var id = $(this).data('id');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_team_ajax_pay'),
        data: {
            id: id
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if(data.status == 'ok') {
                var line = $('tr[data-id="' + id + '"]');
                line.replaceWith(data.return);
                line = $('tr[data-id="' + id + '"]');
                line.effect("highlight", {color: '#c9c9c9'}, 5000);

                $('.modal_alert_success').modal('show');
                setTimeout(function(){
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

function search(search_text,only_actual_edition,only_player,only_team) {
    button = $('#btn_search');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_team_ajax_search'),
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
                $('.table_teams_tbody').html(data.return);
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