$( document ).ready(function() {
    $('.addEdition_Date').each(function() {
        $(this).datetimepicker({
            locale : 'fr',
            format: 'DD/MM/YYYY HH:mm',
            useCurrent : false,
            icons: {
                time: 'fa fa-clock-o',
                date: 'fa fa-calendar',
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down',
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            }
        });
    });
});

$('.addEdition').on('click', function() {
    var modal = $('#addEdition');

    var button = $(this);
    button.attr('disabled', 'disabled');
    button.find('.fa').removeClass('fa-pencil fa-plus').addClass('fa-spinner fa-pulse');

    var name = modal.find('#addEdition_Name').val();
    var date = {
        'inscription' : {
            'start' : modal.find('#addEdition_InscriptionStart').val(),
            'end' : modal.find('#addEdition_InscriptionEnd').val()
        },
        'quart' : {
            'start' : modal.find('#addEdition_QuartStart').val(),
            'end' : modal.find('#addEdition_QuartEnd').val()
        },
        'demi' : {
            'start' : modal.find('#addEdition_DemiStart').val(),
            'end' : modal.find('#addEdition_DemiEnd').val()
        },
        'finale' : {
            'start' : modal.find('#addEdition_FinaleStart').val(),
            'end' : modal.find('#addEdition_FinaleEnd').val()
        }
    };

    if($('#addEdition_QualificationStart').length) {
        date['qualification'] = {
            'start': modal.find('#addEdition_QualificationStart').val(),
            'end': modal.find('#addEdition_QualificationEnd').val()
        };
    } else {
        $('.addEdition_RondeStart').each(function(index) {
            date['ronde'+(index+1)] = {
                'start': $(this).val(),
                'end': $('#addEdition_Ronde'+(index+1)+'End').val()
            };
        });
    }


    $.ajax({
        type: 'POST',
        url: Routing.generate('admin_edition_ajax_add'),
        data: {
            name: name,
            date: JSON.stringify(date)
        },
        error: function (request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: " + request.responseText);
        },
        success: function (data) {
            if (data.status == 'ok') {
                modal.modal('hide');

                if ($('.table_editions_tbody').find('tr td').length == 1)
                    $('.table_editions_tbody').find('tr td').remove();

                $('.table_editions_tbody').append(data.return);

                $('.modal_alert_success').modal('show');
                setTimeout(function () {
                    $(".modal_alert_success").modal('hide');
                }, 1700);
            } else {
                $('.addEditionError').removeClass('hidden-xs-up').html('<h4>' + data.message + '</h4>' + data.errors);
            }

            button.find('.fa').removeClass('fa-pulse fa-spinner').addClass('fa-plus');
            button.removeAttr('disabled');
        }
    });
});
