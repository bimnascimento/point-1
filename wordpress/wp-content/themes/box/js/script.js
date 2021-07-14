(function($){
$(document).ready(function(){

  jQuery.xhrPool = [];
  jQuery.ajaxSetup({
      beforeSend: function(jqXHR) {
          jQuery.xhrPool.push(jqXHR);
          //console.log('beforeSend');
      },
      complete: function(jqXHR) {
          var index = jQuery.xhrPool.indexOf(jqXHR);
          if (index > -1) {
              jQuery.xhrPool.splice(index, 1);
          }
          //console.log('ajaxComplete');
      }
  });
  //MyObject.stopAllAjax();


  //console.log('BROWSER WIDTH: '+$( window ).width());
  //console.log('DOCUMENT WIDTH: '+$( document ).width());
  ///*
  var url = window.location.href;
  //?page=configuracao-lavanderia
  if (
      url.indexOf('?s=') !== -1 ||
      url.indexOf('?ativacao_reenviar') !== -1 ||
      url.indexOf('?confirma_email') !== -1 ||
      url.indexOf('?orderby') !== -1 ||
      url.indexOf('?tm_cart_item_key') !== -1 ||
      url.indexOf('?page=') !== -1 ) {
        //var str = href.split('?')[1];
        MyObject.refineUrl();
  }
  //*/
  //MyObject.refineUrl();

  jQuery('.menu-topo-minha-conta > a, .archive-products:not(.lista-lavanderias) .product-image > a, .archive-products:not(.lista-lavanderias) .product-loop-title, .woocommerce-msg a, .checkout-button, .cart-actions .btn, .coupon .btn, .click-loading, .woocommerce-MyAccount-navigation-link > a, .logo > a').on('click', function(e) {
      MyObject.loadingSite();
  });

  jQuery('.escolhe-cidade a, .archive-products .product-image > a, .archive-products .product-loop-title, .menu-topo-pedidos2 > a, .menu-topo-escolher > a, .click-radar, .product-nav a').on('click', function(e) {
      MyObject.radarSite();
  });

  /*
  var url = window.location.href;
  var removeComTempo = 0;
  removeComTempo = 40000;
  if( removeComTempo > 0 ){ //&& url.indexOf('enviar-pedido') === -1
      setTimeout(function () {
              if( jQuery(document).find('.woocommerce-error').length > 0 ){
                      jQuery(document).find('.woocommerce-error').fadeOut(500, function() {
                            jQuery(document).find('.woocommerce-error').remove();
                      });
              }
              if( jQuery(document).find('.woocommerce-message').length > 0 ){
                      jQuery(document).find('.woocommerce-message').fadeOut(500, function() {
                            jQuery(document).find('.woocommerce-message').remove();
                      });
              }
      }, removeComTempo);
  }
  */

  jQuery('#nav-panel .accordion-menu li.menu-item > a').on('click touchmove', function(event) {
          //e.preventDefault();
          //e.stopPropagation();
          //$(".ui-tm-datepicker-trigger").trigger("click");
          //$(".loading-site").show();
          //console.log(event.type);

          if(event.type === 'touchmove')
              return;

          var $html = jQuery('html');
          if ($html.hasClass('sidebar-opened')) {
              setTimeout(function () { jQuery('.sidebar-toggle').click(); },100);
          }
          if ($html.hasClass('panel-opened')) {
              setTimeout(function () { jQuery('.panel-overlay').click(); },100);
          }
          MyObject.loadingSite();

  });



  jQuery( document ).ajaxComplete(function( event, xhr, settings ) {

    setTimeout(function () {
      //window.settings = settings;
      //console.log(event.type);
      //console.log(settings.url);

      if(event.type == 'ajaxComplete' && ( settings.url.indexOf('admin-ajax.php') > -1 || settings.url.indexOf('get_refreshed_fragments') > -1 || settings.url.indexOf('checkout') > -1 || settings.url.indexOf('update_order_review') > -1 ) ){

        jQuery(".loading-site").css("height",jQuery( document ).height());
        jQuery(".radar-site").css("height",jQuery( document ).height());

        jQuery('.woocommerce-message .close-button, .woocommerce-error .close-button,.woocommerce-info .close-button').on('click', function(){
              $(this).fadeOut().delay(10).queue(function(){
                $(this).parent().fadeOut();
              });
        });
        jQuery('.woocommerce-msg a').on('click', function(e) {
            MyObject.loadingSite();
            $(this).fadeOut().delay(10).queue(function(){
              $(this).parent().fadeOut();
            });
        });

        jQuery('.menu-topo-minha-conta > a, .archive-products:not(.lista-lavanderias) .product-image > a, .archive-products:not(.lista-lavanderias) .product-loop-title, .woocommerce-msg a, .checkout-button, .cart-actions .btn, .coupon .btn, .click-loading, .woocommerce-MyAccount-navigation-link > a, .logo > a').on('click', function(e) {
            MyObject.loadingSite();
        });

        jQuery('.archive-products .product-image > a, .archive-products .product-loop-title, .menu-topo-pedidos > a, .menu-topo-escolher > a, .click-radar, .product-nav a').on('click', function(e) {
            MyObject.radarSite();
        });

      }

      /*
      var removeComTempo = 0;
      removeComTempo = 30000;
      if( removeComTempo > 0 ){
          setTimeout(function () {
                  if( jQuery(document).find('.woocommerce-error').length > 0 ){
                          jQuery(document).find('.woocommerce-error').fadeOut(500, function() {
                                jQuery(document).find('.woocommerce-error').remove();
                          });
                  }
                  if( jQuery(document).find('.woocommerce-message').length > 0 ){
                          jQuery(document).find('.woocommerce-message').fadeOut(500, function() {
                                jQuery(document).find('.woocommerce-message').remove();
                          });
                  }
          }, removeComTempo);
      }
      */

  	}, 100);

  });// FECHA AJAX

  //console.log("READ FIM");
  //jQuery("html,body").css("height",jQuery( document ).height());

  setTimeout(function () {
    jQuery('#b-c-facebook').css('display','none');
    jQuery('#chat_f_b_smal').css('display','block');
  }, 100);

});
$(window).load(function(){

  jQuery(".loading-site").fadeOut(800);
  jQuery(".radar-site").fadeOut(800);
  //MyObject.radarSite();


  jQuery(".loading-site").css("height",jQuery( document ).height());
  jQuery(".radar-site").css("height",jQuery( document ).height());

  jQuery('.woocommerce-message .close-button, .woocommerce-error .close-button,.woocommerce-info .close-button').on('click', function(){
        jQuery(this).fadeOut().delay(10).queue(function(){
          jQuery(this).parent().fadeOut();
        });
  });



  setTimeout(function () {
    jQuery('#b-c-facebook').css('display','none');
    jQuery('#chat_f_b_smal').css('display','block');
  }, 1000);


  //console.log("LOAD FIM");

  //jQuery("html,body").css("height",jQuery( document ).height());

});
$(window).resize(function() {
  //jQuery("html,body").css("height",jQuery( document ).height());
});
}(jQuery));

/*
(function($) {
    "use strict";

    jQuery(window).on('tm_epo_loaded',function(){
        var d = jQuery('#tmcp_date_1');
        console.log('1111');
        function custom_show_hide_date(){
            var time = jQuery("div[data-uniqid='563b2b6038d165.17766856']");
            if ( jQuery('#tmcp_date_1').val() == '' ) {
                time.hide();
                console.log('33333');
            } else {
                time.show();
                console.log('4444');
            }
        }
        d.on('change.customlogic',function(){
            console.log('22222');
            custom_show_hide_date();
        });
        custom_show_hide_date();
    });

})(jQuery);
*/
