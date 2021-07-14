(function($) {
    $(function() {
        
        var lis;  //Global variable holding the data.
        var myCarousel01;  //Global variable holding the carousel.
        
        if( jQuery('.jcarousel').lenght > 0 ){

                var jcarousel = jQuery('.jcarousel')
                    .jcarousel({
                        // Core configuration goes here,
                        //center: false,
                        //wrap: 'both',
                        wrap: 'circular',
                        auto:.1,   //Amount of time you want slide to stop in seconds
                        //initCallback: myCarousel01_initCallback,
                        //itemFirstInCallback: myCarousel01_itemFirstInCallback,
                        /*animation: {
                            duration: 200,
                            easing:   'linear',
                            complete: function() {}
                        },*/
                    })
                    .jcarouselAutoscroll({
                        interval: 3000,
                        target: '+=1',
                        autostart: true
                    })
                ;
                
                
                $('.jcarousel-control-prev')
                    .on('jcarouselcontrol:active', function() {
                        $(this).removeClass('inactive');
                    })
                    .on('jcarouselcontrol:inactive', function() {
                        $(this).addClass('inactive');
                    })
                    .jcarouselControl({
                        target: '-=1'
                    });
        
                $('.jcarousel-control-next')
                    .on('jcarouselcontrol:active', function() {
                        $(this).removeClass('inactive');
                    })
                    .on('jcarouselcontrol:inactive', function() {
                        $(this).addClass('inactive');
                    })
                    .jcarouselControl({
                        target: '+=1'
                    });
        
                $.ajax({
                        type: 'GET',
                        url: my_ajax_object.ajax_url,
                        //async: false,
                        dataType: 'json',
                        data: { action : 'myAjaxFunc' },
                        complete: function(data) {
                            
                            //var dados = JSON.stringify(data);
                            //console.log(dados.usuarios);
                            window.data = data;
                            
                            //console.log(json.usuarios);
                            
                            
                            var html = '<ul>';
        
                            $.each(data.responseJSON.usuarios, function() {
                                html += '<li class="avatar"><img class="foto" src="' + this.img + '" alt="' + this.nome + '" title="' + this.nome + '"></li>';
                            });
                            
                            html += '<li class="avatar"><img title="aasas" class="foto" src="wp-content/themes/box/img/2.jpg"></li>';
                            html += '<li class="avatar"><img title="aasas" class="foto" src="wp-content/themes/box/img/3.jpg"></li>';
                            html += '<li class="avatar"><img title="aasas" class="foto" src="wp-content/themes/box/img/4.jpg"></li>';
                            html += '<li class="avatar"><img title="aasas" class="foto" src="wp-content/themes/box/img/5.jpg"></li>';
                            html += '<li class="avatar"><img title="aasas" class="foto" src="wp-content/themes/box/img/6.jpg"></li>';
                            html += '<li class="avatar"><img title="aasas" class="foto" src="wp-content/themes/box/img/7.jpg"></li>';
                            html += '<li class="avatar"><img class="foto" src="wp-content/themes/box/img/8.jpg"></li>';
                            html += '<li class="avatar"><img class="foto" src="wp-content/themes/box/img/9.jpg"></li>';
                            html += '<li class="avatar"><img class="foto" src="wp-content/themes/box/img/10.jpg"></li>';
                            html += '<li class="avatar"><img class="foto" src="wp-content/themes/box/img/11.jpg"></li>';
                            
                            
                            html += '</ul>';
                
                            // Append items
                            jcarousel
                                .html(html);
                
                            // Reload carousel
                            jcarousel
                                .jcarousel('reload').jcarouselAutoscroll({
                                    interval: 3000,
                                    target: '+=1',
                                    autostart: true
                                });
                            
                        }
                });     
                
                
        }   
        
    });
})(jQuery);
