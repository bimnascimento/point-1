(function($){
$(document).ready(function(){

    //var extra = jQuery(".cart-pedido-lavanderia");
    //extra.hide();

    jQuery(document).on("click", ".single_add_to_cart_button", function(e) {
      if( jQuery('body').hasClass('logged-in')==false ){
        jQuery( ".btnLoginPrincipal" ).trigger( "click" );
        return;
      };
    });

    function pedidoPorPecaController(){

          var extra = jQuery(".cart-pedido-lavanderia");
          var id_extra = jQuery(".cart-pedido-lavanderia input[name='add-to-cart']").val();
          var edit = jQuery(".cart-pedido-lavanderia input[name='tc_cart_edit_key']");
          var buttons = jQuery(".widget_shopping_cart .buttons");
          var buttonAdd = jQuery(".cart-pedido-lavanderia .single_add_to_cart_button");
          var carrinho = jQuery(".product_cat-lavanderias .carrinho-pedido-lavanderia");
          //buttons.hide();

          //MyObject.refineUrl();
          //jQuery.alert( jQuery(window).width() );
          var editando = edit.length > 0;
          if( extra.length > 0 && !editando ){
              //extra.hide();
              //console.log(" 1 LIMPA COLETA "+post_id);
              buttonAdd.text("Adicionar ao Pedido!");
              carrinho.hide();
              //jQuery('.horario-coleta').val('');
              //jQuery('.data-coleta').val('');
              //buttons.fadeOut();
              /*const mq = window.matchMedia( "(min-width: 960px)" );
              if (mq.matches) {
                     jQuery.alert("window width >= 960px");
              } else {
                   alert("window width < 960px");
              }
              */
              //jQuery.alert( jQuery(window).width() );
              /*
              if( jQuery(window).width() < 768 )
                jQuery.alert('menor');
              else
                jQuery.alert('maior');
              */
          }else if( extra.length > 0 && ( editando && edit.val()==cart_item_key ) ){
              //console.log(" 2 EDITANDO "+post_id);
              /*
              var datapicket = plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-datepicker.js';
              if( jQuery('script[src*="' + datapicket + '"]').length == 0 ){
                jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-datepicker.js');
              }

              var timepicket = plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-timepicker.js';
              if( jQuery('script[src*="' + timepicket + '"]').length == 0 ){
                 jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-timepicker.js');
              }
              */

              MyObject.refineUrl();
              //console.log(" 2 DEFINI URL "+post_id);
              buttonAdd.text("Atualizar Pedido!");
              carrinho.show();
          }


          //OPCAO 2
          if( extra.length > 0 && ( window["cart-"+post_id] && !editando )  ){
              extra.hide();
              carrinho.show();
              //extra.remove();
              //buttons.fadeIn();
              //buttons.fadeOut();
              //console.log(" 3 ESCONDE COLETA "+post_id);
          } else if(  extra.length > 0  &&  (  ( !window["cart-"+post_id] )  ||  ( editando && edit.val()==cart_item_key )   )  ){
              extra.fadeIn();
              buttons.hide();
              carrinho.hide();
              //buttons.fadeIn();
              //console.log(" 4 MOSTRA COLETA "+post_id);
          }

          //console.log( parseInt(id_extra) != parseInt(post_id)  );
          if( extra.length > 0 && id_extra == post_id && !window["cart-"+post_id] ){
              buttons.show();
              carrinho.hide();
          }

          //console.log(extra.length);
          //if( extra.length == 0 && !window["cart-"+post_id] ){
              //console.log('entrou');
              //MyObject.radarSite();
              //location.reload();
          //}

          setTimeout(function () {
            if( jQuery('.campo-data').val() == '00/00/0000' ) jQuery('.campo-data').val('');
            if( jQuery('.campo-hora').val() == '00:00' ) jQuery('.campo-hora').val('');
          }, 200);

          jQuery('.produto-table .opcoes, .tm-cart-edit-options').on('click', function(e) {
              //jc_item.close();
              //return false;
              e.preventDefault();
              e.stopPropagation();
              //console.log('entrou');
              MyObject.editandoPopup(jQuery(this));

          });

    }

    //if( post_id !== false )
    pedidoPorPecaController();

          //console.log( window["cart-"+post_id] );

          jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
              //console.log("AJAX");
              //console.log("AJAX TYPE: "+event.type);
            if( event.type == 'ajaxComplete' ){
              //console.log("AJAX URL: "+settings.url);
            //setTimeout(function () {
              //window.settings = settings;
              //console.log( settings.url.indexOf('cart_item_key') );
              //console.log( settings.url.substr(-14) );
              //console.log( settings.url.charAt(settings.url.length - 14) );
              //console.log( settings.url.charAt(settings.url.length - 14) == 'admin-ajax.php' );

              if(  event.type == 'ajaxComplete' &&
                        ( settings.url.substr(-14) == 'admin-ajax.php' || settings.url.indexOf('get_refreshed_fragments') > -1 || settings.url.indexOf('update_order_review') > -1 )
                        //&& ( settings.url.indexOf('?') == -1 && settings.url.indexOf('?wc-ajax=get_refreshed_fragments') > -1 )
                 ){

                  //console.log("AJAX ATAUALIZA CARRINHO");
                  //if( post_id !== false )
                  pedidoPorPecaController();

                  var extra = jQuery(".cart-pedido-lavanderia");
                  if( extra.length == 0 && !window["cart-"+post_id] && settings.data && settings.data.indexOf('porto_cart_item_remove') > -1 && jQuery(".product_cat-lavanderias").length == 1 ){
                      //console.log('entrou');
                      MyObject.radarSite();
                      location.reload();
                  }

                  //console.log("AJAX TYPE: "+event.type);
                  //console.log("AJAX URL: "+settings.url);
                  /*
                  var extra = jQuery(".cart-pedido-lavanderia");
                  var edit = jQuery(".cart-pedido-lavanderia input[name='tc_cart_edit_key']");
                  var buttons = jQuery(".widget_shopping_cart .buttons");
                  var editando = edit.length > 0;

                  //extra.find('input[name="add-to-cart"]').val()==id_produto

                  if( extra.length > 0 && ( window["cart-"+post_id] && !editando )  ){
                      extra.fadeOut();
                      //extra.remove();
                      buttons.fadeIn();
                      jQuery('.horario-coleta').val('');
                      jQuery('.data-coleta').val('');
                      //buttons.fadeOut();
                      //console.log(" AJAX 3 ESCONDE COLETA "+post_id);
                  } else if(  extra.length > 0 && ( !window["cart-"+post_id] || ( editando && edit.val()==cart_item_key ) )  ){
                      extra.fadeIn();
                      buttons.hide();
                      //buttons.fadeIn();
                      //console.log(" AJAX 4 MOSTRA COLETA "+post_id);
                  }

                  //MyObject.editandoPopup();
                  console.log("AJAX");
                  jQuery('.tm-cart-edit-options').on('click', function(e) {
                      jc_item.close();
                      e.preventDefault();
                      e.stopPropagation();
                      MyObject.editandoPopup(jQuery(this));
                  });
                  */

              }
          	//}, 30);
            }
          });


          jQuery('.qty').each(function() {
              var qnt = jQuery( this ).max;
              if( qnt == null) qnt = jQuery( this ).attr('max');
              //console.log( qnt == null );
              if( qnt == null || qnt > 100 ){
                jQuery( this ).prop('max',100);
                jQuery( this ).attr('maxlength', 100);
                //jQuery( this ).maxlength({max: 100});
                //console.log( jQuery( this ) );
              }
          });

          jQuery('.quantity .qty').each(function() {
              var qnt = jQuery( this ).max;
              if( qnt == 1 ){
                //jQuery( this ).parent().css('display','none');
              }
          });

          /*
          jQuery( "form.cart2" ).submit(function( e ) {
            //alert( "Handler for .submit() called." );
            //e.preventDefault();
            //var valid = $(this).valid();
            //console.log("VALID: "+valid);
          });

          $("form.cart2").validate({
            submitHandler: function(form) {
              // do other things for a valid form
              //form.submit();
              console.log("TESTE");
            }
          });
          */


          /*
            jQuery('.single_add_to_cart_button2').on('click', function(e) {

                e.preventDefault();
                e.stopPropagation();

                window.button = jQuery(this);
               	var textOld = button.text();
                window.form = button.closest('form');

                window.id_produto = JSON.stringify( parseInt( form.find('input[name="product_id"]').val() ) );
                if( id_produto == "null" || !id_produto ) id_produto = JSON.stringify( parseInt( form.find('input[name="add-to-cart"]').val() ) );

                window.quantidade = JSON.stringify( parseInt( form.find('input[name="quantity"]').val() ) );

                console.log(" ID PRODUTO: "+id_produto );
                console.log(" QNT PRODUTO: "+quantidade );

                //button.trigger('click');
                button.submit();

                if( button.parent().parent().hasClass('cart-pedido-lavanderia') == true ){
            				//return;
            		}

                //continue;

            });
          */


          jQuery( document ).ajaxComplete(function( event, xhr, settings ) {

            setTimeout(function () {
              //window.settings = settings;
              //console.log(event.type);
              //console.log(settings.url);

              if( event.type == 'ajaxComplete' && ( settings.url.indexOf('orderby=') > -1 ) ){
                  //console.log('ENTROU');
                  jQuery.getScript(plugins_url+'/rating-form/assets/js/front.js');
                  jQuery('ul.products li.product .product-image .img-effect').find('.woocommerce-placeholder').parent().find('.hover-image').css('opacity', '1');
                  MyObject.CalculaDistanciaCep();
                  //jQuery(".shippingmethod_container").html('');
                  //if( enderecoLavanderias.length > 0 )
                  //MyObject.mostraLavanderiasCep();

              }

            }, 100);

          });// FECHA AJAX

          jQuery('ul.products li.product .product-image .img-effect').find('.woocommerce-placeholder').parent().find('.hover-image').css('opacity', '1');


          jQuery(document).on("click", ".single_add_to_cart_button", function(e) {
            if( jQuery('body').hasClass('logged-in')==false ){
              jQuery( ".btnLoginPrincipal" ).trigger( "click" );
              e.preventDefault();
              e.stopPropagation();
              return;
            };
          });

});
$(window).load(function(){

              /*
              var extra = jQuery(".cart-pedido-lavanderia");
              var id_extra = jQuery(".cart-pedido-lavanderia input[name='add-to-cart']").val();
              var edit = jQuery(".cart-pedido-lavanderia input[name='tc_cart_edit_key']");
              var buttons = jQuery(".widget_shopping_cart .buttons");
              var buttonAdd = jQuery(".cart-pedido-lavanderia .single_add_to_cart_button");
              //buttons.hide();

              //MyObject.refineUrl();
              //jQuery.alert( jQuery(window).width(); );
              //alert(jQuery(window).width());
              var tela = jQuery(window).width();
              var editando = edit.length > 0;
              if( extra.length > 0 && !editando && tela < 480){
                  //console.log("as");
                  jQuery('.carrinho-pedido-lavanderia').hide();
                  jQuery('.vc_column_container.vc_col-sm-8').hide();
              }
              */


              jQuery(document).on("click", ".single_add_to_cart_button", function(e) {
                if( jQuery('body').hasClass('logged-in')==false ){
                  jQuery( ".btnLoginPrincipal" ).trigger( "click" );
                  e.preventDefault();
                  e.stopPropagation();
                  return;
                };
              });

              jQuery('#verifica-cep-form2').on('keypress', function(e) {
                  if (e.which === 13) {
                      e.preventDefault();
                      return false;
                  }
                  return true;
              });

              /*
              jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
                  if( event.type == 'ajaxComplete' && settings.url.indexOf('get_lista_itens_ajax') > -1 ){
                          console.log('ajaxComplete');
                          jQuery.getScript(plugins_url+'/woocommerce-add-ajax/orak-ajax-add-to-cart.js');
                          MyObject.qtywrap();
                          jQuery('.produto-table .opcoes, .tm-cart-edit-options').on('click', function(e) {
                              e.preventDefault();
                              e.stopPropagation();
                              MyObject.editandoPopup(jQuery(this));
                          });
                  }
              });
              */
              /*
              jQuery('.click-get-itens').on('click', function(e) {

                  var item = jQuery(this).find('.itens-categoria');
                  console.log('entrou');
                  //console.log(item);
                  //MyObject.editandoPopup(jQuery(this));

              });
              */
              /*
              jQuery('.click-get-itens').on('click', function(e) {
                  console.log('entrou');
              });
              */

              jQuery('ul.products li.product .product-image .img-effect').find('.woocommerce-placeholder').parent().find('.hover-image').css('opacity', '1');
              MyObject.CalculaDistanciaCep();

              //if( enderecoLavanderias.length > 0 )
              //MyObject.mostraLavanderiasCep();

              jQuery('#yith-infs-button').on('click', function(e) {

                  //e.preventDefault();
                  //e.stopPropagation();
                  //console.log('ENTROU');

                  //<div class="lwa-loading"></div>
                  //archive-products
                  //setContentAppend('<br>Content loaded!');
                  //.appendChild(script1);
                  //jQuery('<script>').attr('src', src).appendTo('head');
                  //jQuery('archive-products').
                  jQuery('<div class="lavanderias-loading"></div>').prependTo('.shop-loop-after');


              });

              jQuery( document ).on( 'yith_infs_adding_elem', function(){
                  //if( block_loader )
                      //t.unblock();
                  //console.log('CARREGOU LAVANDERIAS AJAX FIM');
                  if( jQuery('.lavanderias-loading').length > 0 ) jQuery('.lavanderias-loading').remove();
                  jQuery.getScript(plugins_url+'/rating-form/assets/js/front.js');

                  jQuery('ul.products li.product .product-image .img-effect').find('.woocommerce-placeholder').parent().find('.hover-image').css('opacity', '1');
                  MyObject.CalculaDistanciaCep();
                  //jQuery(".shippingmethod_container").html('');
                  //if( enderecoLavanderias.length > 0 )
                  //MyObject.mostraLavanderiasCep();

                  //jQuery('[class^="rating_form_"]').each(function() {
                      //var h = jQuery(this);
                      //jQuery(this).remove();
                      //jQuery(this).find(".tooltip").remove();
                      //console.log(h);
                  //});

                  //jQuery('.details-lavanderia .rating_form .cyto-star').on("mouseenter",function(){
                      //console.log( jQuery(this).find(".tooltip").length );
                  //});

              });

              var extra = jQuery(".cart-pedido-lavanderia");
              if( extra.css('display') == 'block' && window["cart-"+post_id] ) extra.slideToggle( "slow");
              //extra.remove();
              //setTimeout(function () {
              //  extra.remove();
              //},500);

              if( extra.length > 0 ){

                    MyObject.getItensCategoria();
                    //console.log('PAGINA DE LAVANDERIA');

              }

              /*
              jQuery('.produto-table .opcoes, .tm-cart-edit-options').on('click', function(e) {

                  e.preventDefault();
                  e.stopPropagation();
                  MyObject.editandoPopup(jQuery(this));

              });
              */


              /*
              setTimeout(function () {
                var extra = jQuery(".cart-pedido-lavanderia");
                var edit = jQuery(".cart-pedido-lavanderia input[name='tc_cart_edit_key']");
                var buttons = jQuery(".widget_shopping_cart .buttons");
                //window.extra = extra;
                //window.edit = edit;
                edit = edit.length > 0;
                if( ( window["cart-"+post_id] && !edit ) && extra.length > 0 ){
                    //extra.fadeOut();
                    //buttons.fadeIn();
                    //buttons.fadeOut();
                    console.log(" 5 ESCONDE COLETA "+post_id);
                }else if(  ( !window["cart-"+post_id] || edit ) && extra.length > 0 ){
                    //extra.fadeIn();
                    //extra.slideToggle( "slow");
                    //buttons.fadeIn();
                    //buttons.fadeOut();
                    //MyObject.refineUrl();
                    //console.log(" 6 MOSTRA COLETA "+post_id);
                }
              },2000);
              */

});
}(jQuery));
