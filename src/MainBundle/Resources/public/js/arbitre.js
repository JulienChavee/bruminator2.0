$('.becomeArbitre').on('click', function() {
    var button = $(this);
    button.attr('disabled', 'disabled');
    button.prepend($('<i>').addClass('fas fa-spinner-third fa-spin'));

    var modal = $('#becomeArbitre');

    var disponibilite = $('#becomeArbitre_Disponibilite').val();

    $.ajax({
        type: 'POST',
        url: Routing.generate('arbitres_ajax_become_arbitre'),
        data: {
            disponibilite: disponibilite
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if(data.status == 'ok') {
                location.href = Routing.generate('arbitres');
            } else {
                button.find('.fa').remove();
                button.removeAttr('disabled');

                console.log(data.debug);
            }
        }
    });
});

$('#becomeArbitre').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    if(button.data('dispo')) {
        var dispo = button.data('dispo');

        var modal = $(this);
        modal.find('#becomeArbitre_Disponibilite').val(dispo);
    }
});