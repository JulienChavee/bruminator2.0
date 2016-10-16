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
