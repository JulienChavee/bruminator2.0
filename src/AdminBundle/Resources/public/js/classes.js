$('body').on('click', '.table_classes_tbody i[data-action="edit"]', function() {
    var class1 = $(this).data('class1');
    var class2 = $(this).data('class2');
    var rand = $(this).data('rand');

    var modal = $('#editSynergie');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_classe_ajax_getsynergie'),
        data: {
            class1: class1,
            class2: class2
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if(data.status == 'ok') {
                modal.find('#editClasse_Synergie').val(data.return);

                modal.find('.editSynergie').data('class1', class1);
                modal.find('.editSynergie').data('class2', class2);
                modal.find('.editSynergie').data('rand', rand);
                modal.modal('show');
            } else {
                $('.modal-body-more-info').html(data.message);
                $('.modal_alert_error').modal('show');
                console.log(data.debug);
            }
        }
    });
});

$('.editSynergie').on('click', function() {
    var modal = $('#editSynergie');

    var button = $(this);

    var class1 = button.data('class1');
    var class2 = button.data('class2');
    var rand = button.data('rand');
    var synergie = modal.find('#editClasse_Synergie').val();

    button.attr('disabled', 'disabled');
    button.find('.fa').removeClass('fa-pencil').addClass('fa-spinner fa-pulse');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_classe_ajax_editsynergie'),
        data: {
            class1: class1,
            class2: class2,
            synergie: synergie
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                modal.modal('hide');

                var line = $('td[data-rand="' + rand + '"]');
                line.text(data.return);
                line = $('td[data-rand="' + rand + '"]');
                line.effect("highlight", {color: '#c9c9c9'}, 5000);

                $('.modal_alert_success').modal('show');
                setTimeout(function () {
                    $(".modal_alert_success").modal('hide');
                }, 1700);
            } else {
                $('.editSynergieError').removeClass('hidden-xs-up').html('<h4>' + data.message + '</h4>' + data.errors);
                console.log(data.debug);
            }

            button.find('.fa').removeClass('fa-pulse fa-spinner').addClass('fa-pencil');
            button.removeAttr('disabled');
        }
    });
});

$('.editClasse').on('click', function() {
    var modal = $('#editClasse');

    var button = $(this);

    var nom = modal.find('#editClasse_Nom').val();
    var nomCourt = modal.find('#editClasse_NomCourt').val();
    var points = modal.find('#editClasse_Points').val();

    button.attr('disabled', 'disabled');
    button.find('.fa').removeClass('fa-pencil fa-plus').addClass('fa-spinner fa-pulse');

    if(button.data('edit')) {
        var id = button.data('id');

        $.ajax({
            type: 'POST',
            url: Routing.generate('admin_classe_ajax_editclasse'),
            data: {
                id: id,
                name: nom,
                shortname: nomCourt,
                points: points
            },
            error: function (request, error) { // Info Debuggage si erreur
                console.log("Erreur : responseText: " + request.responseText);
            },
            success: function (data) {
                if (data.status == 'ok') {
                    modal.modal('hide');

                    var line = $('tr[data-id="' + id + '"]');
                    line.replaceWith(data.return);
                    line = $('tr[data-id="' + id + '"]');
                    line.effect("highlight", {color: '#c9c9c9'}, 5000);

                    $('.modal_alert_success').modal('show');
                    setTimeout(function () {
                        $(".modal_alert_success").modal('hide');
                    }, 1700);
                } else {
                    $('.editClasseError').removeClass('hidden-xs-up').html('<h4>' + data.message + '</h4>' + data.errors);

                    console.log(data.debug);
                }

                button.find('.fa').removeClass('fa-pulse fa-spinner').addClass('fa-pencil');
                button.removeAttr('disabled');
            }
        });
    } else {
        // TODO : Add l'ajout de classe
    }
});

$('.table_classes_tbody').on('click', 'button[data-action="edit"]', function() {
    var id = $(this).data('id');
    var modal = $('#editClasse');

    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_classe_ajax_getclasse'),
        data: {
            id: id
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if(data.status == 'ok') {
                modal.find('#editClasse_Nom').val(data.classe.name);
                modal.find('#editClasse_NomCourt').val(data.classe.shortName);
                modal.find('#editClasse_Points').val(data.classe.points);

                modal.find('.editClasse').data('id', id).data('edit', '1');
                modal.modal('show');
            } else {
                $('.modal-body-more-info').html(data.message);
                $('.modal_alert_error').modal('show');

                console.log(data.debug);
            }
        }
    });
});
