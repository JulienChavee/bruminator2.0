$('.table_users_tbody').on('click', 'button[data-action="promote"]', function() {
    var id = $(this).data('id');
    var role = $(this).data('role');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_user_ajax_promote'),
        data: {
            id: id,
            role: role
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                var line = $('tr[data-id="' + id + '"]');
                line.replaceWith(data.return);
                line = $('tr[data-id="' + id + '"]');
                line.effect("highlight", {color: '#c9c9c9'}, 5000);

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