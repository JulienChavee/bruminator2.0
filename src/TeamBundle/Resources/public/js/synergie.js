var synergie = 0;
doubleClasse = false;

$('.selectClass').on('change', function() {
    synergie = 0;

    var class1 = $('#class1').find(":selected").val();
    var class2 = $('#class2').find(":selected").val();
    var class3 = $('#class3').find(":selected").val();
    var class4 = $('#class4').find(":selected").val();

    doubleClasse = false;

    // TODO : Ne pas effectuer la recherche de synergie si l'on détecte 2 classes les mêmes

    if(class1 != "null") {
        getClassPoints(class1, updateSynergie);

        if(class2 != "null") {
            getSynergie(class1, class2, updateSynergie);

            console.log('test');

            if(class1 == class2)
                doubleClasse = true;
        }

        if(class3 != "null") {
            getSynergie(class1, class3, updateSynergie);

            if(class1 == class3)
                doubleClasse = true;
        }

        if(class4 != "null") {
            getSynergie(class1, class4, updateSynergie);

            if(class1 == class4)
                doubleClasse = true;
        }
    }

    if(class2 != "null") {
        getClassPoints(class2, updateSynergie);

        if(class3 != "null") {
            getSynergie(class2, class3, updateSynergie);

            if(class2 == class3)
                doubleClasse = true;
        }

        if(class4 != "null") {
            getSynergie(class2, class4, updateSynergie);

            if(class2 == class4)
                doubleClasse = true;
        }
    }

    if(class3 != "null") {
        getClassPoints(class3, updateSynergie);

        if(class4 != "null") {
            getSynergie(class3, class4, updateSynergie);

            if(class3 == class4)
                doubleClasse = true;
        }
    }

    if(class4 != "null")
        getClassPoints(class4, updateSynergie);

    if(class1 != "null" && class2 != "null" && class3 != "null") {
        getClass4(class1, class2, class3);
        $('.list-group').removeClass("hidden-xs");
    } else
        $('.list-group').addClass("hidden-xs");
});

function updateSynergie(res) {
    synergie += parseInt(res);

    if(!doubleClasse) {
        if(synergie > 117) // TODO : S'affrinchir de la limite manuelle de synergie pour tenir compte de la limite configuration
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

function getClass4(class1, class2, class3) {
    $.ajax({
        type : 'POST',
        url : Routing.generate('team_front_synergie_ajax_getclass4'),
        data : {
            class1 : class1,
            class2 : class2,
            class3 : class3
        },
        error    : function(request, error) { // Info Debuggage si erreur
            console.log("Erreur : responseText: "+request.responseText);
        },
        success: function(data) {
            if(data.status == "ok") {
                $('.list-group').empty();
                if(data.return == false)
                    $('.list-group').append(
                        $('<li>').addClass("list-group-item").text("Aucune classe possible")
                    );
                else {
                    $.each(data.return, function(i, val) {
                        $('.list-group').append(
                            $('<li>').addClass("list-group-item").text(val)
                        )
                    });
                }
            } else {
                console.log(data.debug); // TODO : Afficher un message d'erreur à l'utilisateur en cas de problème ?
            }
        }
    })
}
