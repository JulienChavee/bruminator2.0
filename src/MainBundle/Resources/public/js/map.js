var idNow=0;
var idSelected=null;

$('.changemap').on('click', function() {
    idSelected=$(this).data('id');
    $('.carousel').carousel(idSelected);
});

$('.carousel').on('slide.bs.carousel', function () {
    $('.changemap[data-id="'+idNow+'"]').removeClass('active');
    idNow=idSelected != null ? idSelected : (idNow + 1);
    if(idNow > $('.changemap').last().data('id')) idNow=0;
    idSelected=null;
    $('.changemap[data-id="'+idNow+'"]').addClass('active');
});