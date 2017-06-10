var synergie = 0;
var doubleClasse = false;

$('.selectClass').on('change', function() {
    synergie = 0;

    var classe;
    var classes = {};
    var nbPlayers = $('.nb_players_team').val();

    for (var i = 1; i <= nbPlayers; i++) {
        if(i==nbPlayers)
            getLastClass(classes,nbPlayers);
        classes[i] =  $('#class'+i).find(":selected").val();
        classe = $('#class' + i).find(":selected").val();
        if (classe != "null") {
            getClassPoints(classe, updateSynergie);

            var classeNext;
            for (var j = i + 1; j <= nbPlayers; j++) {
                classeNext = $('#class' + j).find(":selected").val();

                if (classeNext != "null") {
                    if (classe == classeNext)
                        doubleClasse = true;
                    else
                        getSynergie(classe, classeNext, updateSynergie);
                }
            }
        }
    }

    // TODO : Arrêter le calcul dès la détection d'une double classe
});

function updateSynergie(res) {
    synergie += parseInt(res);

    if(!doubleClasse) {
        if(synergie > TWIG.synergieMax)
            $('#pts_synergie').fadeOut(function() {
                $(this).removeClass("tag-success").addClass("tag-danger").text(synergie).fadeIn()
            });
        else
            $('#pts_synergie').fadeOut(function() {
                $(this).removeClass("tag-danger").addClass("tag-success").text(synergie).fadeIn();
            });
    } else {
        $('#pts_synergie').fadeOut(function() {
            $(this).removeClass("tag-success").addClass("tag-danger").text("Impossible").fadeIn()
        });
    }
}

function getSynergie(class1, class2, callback) {
    $.ajax({
        type : 'POST',
        url : Routing.generate('team_front_synergie_ajax_getsynergie'),
        data : {
            class1 : class1,
            class2 : class2
        },
        error    : function(request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: "+request.responseText);
        },
        success: function(data) {
            if(data.status == "ok")
                callback(data.return);
            else {
                console.log(data.debug); // TODO : Afficher un message d'erreur à l'utilisateur en cas de problème ?
            }
        }
    });
}

function getClassPoints(class1, callback) {
    $.ajax({
        type : 'POST',
        url : Routing.generate('team_front_synergie_ajax_getclasspoints'),
            data : {
            class : class1
        },
        error    : function(request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: "+request.responseText);
        },
        success: function(data) {
            if(data.status == "ok")
                callback(data.return);
            else {
                console.log(data.debug); // TODO : Afficher un message d'erreur à l'utilisateur en cas de problème ?
            }
        }
    });
}

function getLastClass(classes,numberPlayer) {
    var show = true;

    if(classes.length==0)
        show=false;

    for(var i = 1; i<=numberPlayer-1; i++)
    {
        if( classes[i] == "null" )
            show=false;
    }

    if(show) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('team_front_synergie_ajax_getlastclass'),
            data: {
                classes:classes
            },
            error: function (request, error) { // Info Debuggage si erreur
                console.log("Erreur : responseText: " + request.responseText);
            },
            success: function (data) {
                if (data.status == "ok") {
                    $('.list-group').empty();
                    if (data.return == false)
                        $('.list-group').append(
                            $('<li>').addClass("list-group-item").text("Aucune classe possible")
                        );
                    else {
                        $.each(data.return, function (i, val) {
                            $('.list-group').append(
                                $('<li>').addClass("list-group-item").text(val)
                            )
                        });
                    }
                    $('.list-group').removeClass("hidden-xs-up");
                } else {
                    console.log(data.debug); // TODO : Afficher un message d'erreur à l'utilisateur en cas de problème ?
                }
            }
        });
    }
    else
        $('.list-group').addClass("hidden-xs-up");
}
