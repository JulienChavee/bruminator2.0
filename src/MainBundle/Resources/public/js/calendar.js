$('body').on('click', '.changeMonth', function() {
    var month = $(this).data('month');
    var year = $(this).data('year');

    $.ajax({
        type: 'POST',
        url: Routing.generate('calendrier_ajax_get'),
        data: {
            month: month,
            year: year
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                $('.calendar').html(data.return);
                // TODO : Ajouter un effet pendant le chargement
                history.pushState('data.return', 'Calendrier', Routing.generate('calendrier_view', {year:year, month:month}));
            } else {
                $('.modal-body-more-info').html(data.message);
                $('.modal_alert_error').modal('show');

                console.log(data.debug);
            }
        }
    });
});
