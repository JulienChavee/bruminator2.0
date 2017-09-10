window.setInterval(function(){
    $.ajax({
        url:"https://api.twitch.tv/kraken/streams/bruminator_officiel",
        dataType:"json",
        type:"get",
        headers: {
            'Client-ID': '74dease2xl8ecabhx0ocfbonno2toq'
        },
        success:function(data) {
            if(data.stream != null) {
                $('.streamInfos').empty();
                $('.streamInfos').append(
                    $('<h3>').text("Actuellement : ").append(
                        $('<small>').attr('id', 'streamTitle').addClass('text-muted')
                    )
                ).append(
                    $('<h4>').append(
                        $('<span>').addClass('fas fa-user')
                    ).append(
                        $('<span>').attr('id', 'viewers')
                    )
                );
            } else if(data.stream == null) {
                $('#viewers').parent().remove();
            }

            if(data.stream != null) {
                $('#streamTitle').text(data.stream.channel.status)
                jQuery({ Counter: $('#viewers').text() }).animate({ Counter: data.stream.viewers }, {
                    duration: 800,
                    easing: 'swing',
                    step: function (i) {
                        $('#viewers').text(Math.ceil(i));
                    }
                });
            }
        },
        error:function(request, error) {
            console.log(error)
        }
    })
}, 30000);
