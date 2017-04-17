var previousRemplacantPseudo = "";

$('body').on('click', '.playerCard i[data-action="edit"]', function() {
    var id = $(this).data('id');
    var modal = $('#editPlayer');

    $.ajax({
        type: 'POST',
        url: Routing.generate('team_player_ajax_get'),
        data: {
            id: id
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if(data.status == 'ok') {
                modal.find('#editPlayer_Pseudo').val(data.player.pseudo);
                modal.find('#editPlayer_Level').val(data.player.level);
                modal.find('#editPlayer_Class').val(data.player.class.id);
                if(data.player.remplacant) {
                    modal.find('#editPlayer_Remplacant_Pseudo').val(data.player.remplacant.pseudo);
                    modal.find('#editPlayer_Remplacant_Level').val(data.player.remplacant.level);
                } else
                    modal.find('.inversePlayer').addClass('hidden-xs-up');

                modal.find('.editPlayer').data('id', id);
                modal.find('[data-type="remove"]').data('id', id);
                modal.modal('show');
            } else {
                $('.modal-body-more-info').html(data.message);
                $('.modal_alert_error').modal('show');
                console.log(data.debug);
            }
        }
    });
});

$('.editPlayer').on('click', function() {
    var modal = $('#editPlayer');

    var button = $(this);

    var id = button.data('id');
    var team = button.data('team');
    var pseudo = modal.find('#editPlayer_Pseudo').val();
    var level = modal.find('#editPlayer_Level').val();
    var classe = modal.find('#editPlayer_Class').val();
    var remplacantPseudo = modal.find('#editPlayer_Remplacant_Pseudo').val();
    var remplacantLevel = modal.find('#editPlayer_Remplacant_Level').val();
    var newPlayer = $('input[name=editPlayer_SameOrNew]:checked').val();
    var newRemplacant = $('input[name=editPlayer_Remplacant_SameOrNew]:checked').val();
    var inverse = modal.find('#editPlayer_Inverse').is(":checked");

    button.attr('disabled', 'disabled');
    button.find('.fa').removeClass('fa-pencil').addClass('fa-spinner fa-pulse');

    $.ajax({
        type: 'POST',
        url: Routing.generate('team_player_ajax_edit'),
        data: {
            id: id,
            team: team,
            pseudo: pseudo,
            level: level,
            class: classe,
            remplacantPseudo: remplacantPseudo,
            remplacantLevel: remplacantLevel,
            newPlayer: newPlayer,
            newRemplacant: newRemplacant,
            inverse: inverse
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                modal.modal('hide');

                var line = $('div[data-id="' + id + '"]');
                line.replaceWith(data.return);
                line = $('div[data-id="' + id + '"]');
                line.effect("highlight", {color: '#c9c9c9'}, 5000);

                console.log(data.errors);

                if($('#div-team-errors').length > 0) {
                    $('#div-team-errors').replaceWith(data.errors);
                } else {
                    $('#editPlayer').after(data.errors);
                }

                $('.modal_alert_success').modal('show');
                setTimeout(function () {
                    $(".modal_alert_success").modal('hide');
                }, 1700);
            } else {
                $('.editPlayerError').removeClass('hidden-xs-up').html('<h4>' + data.message + '</h4>' + data.errors);
                button.find('.fa').removeClass('fa-pulse fa-spinner').addClass('fa-pencil');
                button.removeAttr('disabled');
                console.log(data.debug);
            }
        }
    });
});

$('#editPlayer_Inverse').on('change', function() {
    var temp;
    temp = $('#editPlayer_Pseudo').val();
    $('#editPlayer_Pseudo').val($('#editPlayer_Remplacant_Pseudo').val());
    $('#editPlayer_Remplacant_Pseudo').val(temp);
    temp = $('#editPlayer_Level').val();
    $('#editPlayer_Level').val($('#editPlayer_Remplacant_Level').val());
    $('#editPlayer_Remplacant_Level').val(temp);
});

$('#editPlayer_Class').on('change', function(){
    var modal = $('#editPlayer');

    if(modal.find('#editPlayer_Remplacant_Pseudo').val() != "") {
        $('.editPlayerClassChanged').removeClass('hidden-xs-up');
    }
});

$('#editPlayer_Pseudo').on('keyup change paste', function(){
    $('.editPlayerPseudoChanged').removeClass('hidden-xs-up');
});

$('#editPlayer_Remplacant_Pseudo').on('focus', function () {
    if(previousRemplacantPseudo == "")
        previousRemplacantPseudo = $(this).val();
}).on('keyup change paste', function(){
    if(previousRemplacantPseudo != "" && $(this).val() != "" )
        $('.editPlayerRemplacantPseudoChanged').removeClass('hidden-xs-up');
    else
        $('.editPlayerRemplacantPseudoChanged').addClass('hidden-xs-up');
});

$('#editPlayer').on('hide.bs.modal', function() {
    var button = $(this).find('.editPlayer');

    $(this).find('#editPlayer_Pseudo').val('');
    $(this).find('#editPlayer_Level').val('');
    $(this).find('#editPlayer_Class').val('');
    $(this).find('#editPlayer_Remplacant_Pseudo').val('');
    $(this).find('#editPlayer_Remplacant_Level').val('');

    $(this).find('#editPlayer_SamePlayer').prop('checked', 'checked');
    $(this).find('#editPlayer_RemplacantSamePlayer').prop('checked', 'checked');
    $(this).find('.editPlayerError').addClass('hidden-xs-up').html("");
    $(this).find('.editPlayerPseudoChanged').addClass('hidden-xs-up');
    $(this).find('.editPlayerClassChanged').addClass('hidden-xs-up');
    $(this).find('.inversePlayer').removeClass('hidden-xs-up');

    button.find('.fa').removeClass('fa-pulse fa-spinner').addClass('fa-pencil');
    button.removeAttr('disabled');
});

$('#editPlayer_Pseudo').autocomplete({
    source: Routing.generate('team_player_ajax_search'),
    minLength: 2,
    select: function(event, ui) {
        $('input[name=editPlayer_SameOrNew]').prop( 'checked', true )
    }
});

$('#editPlayer_Remplacant_Pseudo').autocomplete({
    source: Routing.generate('team_player_ajax_search'),
    minLength: 2,
    select: function(event, ui) {
        $('input[name=editPlayer_Remplacant_SameOrNew]').prop( 'checked', true )
    }
});

$('#addPlayer_Pseudo').autocomplete({
    source: Routing.generate('team_player_ajax_search'),
    minLength: 2
});

$('#addPlayer_Remplacant_Pseudo').autocomplete({
    source: Routing.generate('team_player_ajax_search'),
    minLength: 2
});

$('.addPlayerCard').on('mouseenter', function() {
    $(this).removeClass('card-outline-info').addClass('card-inverse card-info')
});

$('.addPlayerCard').on('mouseleave', function() {
    $(this).removeClass('card-inverse card-info').addClass('card-inverse card-outline-info')
});

$('.addPlayerCard').on('click', function() {
    $('#addPlayer').modal('show');
});

$('.addPlayer').on('click', function() {
    var modal = $('#addPlayer');

    var button = $(this);

    var team = button.data('team');
    var pseudo = modal.find('#addPlayer_Pseudo').val();
    var level = modal.find('#addPlayer_Level').val();
    var classe = modal.find('#addPlayer_Class').val();
    var remplacantPseudo = modal.find('#addPlayer_Remplacant_Pseudo').val();
    var remplacantLevel = modal.find('#addPlayer_Remplacant_Level').val();

    button.attr('disabled', 'disabled');
    button.find('.fa').removeClass('fa-plus').addClass('fa-spinner fa-pulse');

    $.ajax({
        type: 'POST',
        url: Routing.generate('team_player_ajax_add'),
        data: {
            team: team,
            pseudo: pseudo,
            level: level,
            class: classe,
            remplacantPseudo: remplacantPseudo,
            remplacantLevel: remplacantLevel
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                modal.modal('hide');

                $('#addPlayerCard').before(
                    data.return
                );

                if($('#div-team-errors').length > 0) {
                    $('#div-team-errors').replaceWith(data.errors);
                } else {
                    $('#editPlayer').after(data.errors);
                }

                $('.modal_alert_success').modal('show');
                setTimeout(function () {
                    $(".modal_alert_success").modal('hide');
                }, 1700);
            } else {
                $('.addPlayerError').removeClass('hidden-xs-up').html('<h4>' + data.message + '</h4>' + data.errors);
                button.find('.fa').removeClass('fa-pulse fa-spinner').addClass('fa-plus');
                button.removeAttr('disabled');
                console.log(data.debug);
            }
        }
    });
});

$('#editPlayer').on('click', '[data-type="remove"]', function() {
    var modal = $('#editPlayer');
    var id = $(this).data('id');

    var modalConfirmation = $('.modal_alert_confirmation')

    modal.modal('hide');

    modalConfirmation.find('.modal-confirmation-yes').data('id', id);
    modalConfirmation.modal('show');
});

$('.modal-confirmation-yes').on('click', function() {
    var id = $(this).data('id');

    $.ajax({
        type: 'POST',
        url: Routing.generate('team_player_ajax_remove'),
        data: {
            id: id
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if(data.status == 'ok') {
                $('div[data-id="' + id + '"]').fadeOut(1500, "easeOutExpo", function() {
                    $(this).remove();
                });

                if($('#div-team-errors').length > 0) {
                    $('#div-team-errors').replaceWith(data.errors);
                } else {
                    $('#editPlayer').after(data.errors);
                }

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