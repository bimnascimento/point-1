(function($){
jQuery(document).ready(function($) {
	"use strict";
	//console.log('CARREGOU AJAX FILTER');
	//jQuery('.add_to_cart_pedido_peca').text('Adicionar').prop('disabled', false);

	jQuery( "form.cart" ).submit(function( e ) {
	//jQuery(document).on("click", ".single_add_to_cart_button", function(e) {

		//console.log("ENTROU");
		e.preventDefault();

		if( jQuery(this).hasClass('product_type_variable') ) return true;

		var form = jQuery(this).closest('form');
		window.form = form;

		//var button = jQuery(this);
		var button = form.find('.single_add_to_cart_button')[0];
		//window.button = button;
		var textOld = jQuery(button).text();

		//jQuery(button).submit();
		//form.find('.tm-cell > :not(.tc-hidden) .tm-error').css('display')
		var erros = false;
		if( form.find('label.tm-error').length > 0  ) {

			form.find('label.tm-error').each(function() {
					var elementoErro = jQuery( this );
					if( jQuery( this ).css('display') == "table" ){
					//if( form.find( 'label.tm-error' ).css('display') == "inline-block" ){
						//console.log("ok1");
						if( form.find( '.verifica-obrigatorio' ).length == 0 ){
								form.prepend('<div class="verifica-obrigatorio">* Preencha os campos obrigatórios.</div>');
								///*
								setTimeout(function () {
										//console.log("ok2");
										form.find( 'label.tm-error' ).fadeOut().remove();
										//form.find( '.tm-error' ).css('display','table-cell')
										form.find( '.verifica-obrigatorio' ).fadeOut().remove();
								},5000);
								//*/
						}

						var offsetErro = jQuery( this ).offset();
						var is_in_viewport = MyObject.isElementInViewport(	jQuery( this )	);
						//console.log(is_in_viewport);
						if( !is_in_viewport || jQuery(document).find('.quickview-wrap').length > 0  ){
							jQuery('.jconfirm-scrollpane').scrollTop(offsetErro.top);
						}

						//console.log("ERRO CAMPO");

							erros = true;

					}else{
							//erros = false;
					}

			});


		}
		//console.log(erros);
		if(erros){
			//form.find( 'label.tm-error' ).fadeOut().remove();
			//form.find( '.tm-error' ).css('display','table-cell')
			//form.find( '.verifica-obrigatorio' ).fadeOut();
			return;
		}

		//console.log('PASSOU 1');
		//return;

		var id_produto = JSON.stringify( parseInt( form.find('input[name="product_id"]').val() ) );
		if( id_produto == "null" || !id_produto ) id_produto = JSON.stringify( parseInt( form.find('input[name="add-to-cart"]').val() ) );

		var quantidade = JSON.stringify( parseInt( form.find('input[name="quantity"]').val() ) );

		//console.log(" ID PRODUTO: "+id_produto );
		//console.log(" QNT PRODUTO: "+quantidade );

		//return;

		//if( jQuery(button).parent().parent().hasClass('cart-pedido-lavanderia') == true ){
				//return;
		//}

		if( parseInt( jQuery.data( document.body, "processing") ) == 1 ) return;

		jQuery.data(document.body, "processing", 1);
		jQuery.data(document.body, "processed_once", 0);

		var context = this;
		//var button_default_cursor = jQuery("button").css('cursor');

		//jQuery("html, body").css("cursor", "wait");
		//jQuery("button").css("cursor", "wait");

		//if ( button.length > 0 ){

			jQuery(button).attr('disabled', 'disabled');
			jQuery(button).addClass('disabled');
			jQuery(button).text('Aguarde..');
			if( jQuery(button).attr('data-id') != null ){
					jQuery('.'+jQuery(button).attr('data-id')).attr('disabled', 'disabled');
					jQuery('.'+jQuery(button).attr('data-id')).addClass('disabled');
					jQuery('.'+jQuery(button).attr('data-id')).text('Aguarde..');
			}

			//jQuery('.add2cartbtn_'+formdata['thisbuttonid']).addClass('working');
			//jQuery(".vtspinner_"+ formdata['thisbuttonid']).fadeIn(200);
			//jQuery(".vtspinner_"+ formdata['thisbuttonid']).css("display","inline-block");
		//}

		function isElementInViewport (el) {
			if (typeof jQuery === "function" && el instanceof jQuery) {
				el = el[0];
			}
			var rect = el.getBoundingClientRect();
			return (
				rect.top >= 0 &&
				rect.left >= 0 &&
				rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or jQuery(window).height() */
				rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or jQuery(window).width() */
			);
		}

		//window.dados = form.serialize();

		//form.validate({
			//submitHandler: function(form) {
				// do other things for a valid form
				//form.submit();
				//console.log("TESTE");
			//}
		//});

		//var addToCartForm = document.querySelector('form.variations_form');
		//formData = new FormData(addToCartForm);
		//e.preventDefault();
		//return;
		/*
		[tmcp_radio_0] => Somente lavar  (15% Desc)_1
    [cpf_product_price] => 15
    [tc_form_prefix] =>
    [tm-epo-counter] => 1
    [tcaddtocart] => 209
    [quantity] => 1
    [add-to-cart] => 209
		*/
		jQuery.ajax({
			type: "POST",
			url: form.attr( 'action' ),
			data: form.serialize(),
			//processData: false,  // tell jQuery not to process the data
   		//contentType: false,   // tell jQuery not to set contentType
			/*
			data: {
				"action" : "add_product_to_cart",
				"tmcp_radio_0" : JSON.stringify( form.find('input[name="tmcp_radio_0"]').val() ),
				"cpf_product_price" : JSON.stringify( form.find('input[name="cpf_product_price"]').val() ),
				"tm-epo-counter" : JSON.stringify( form.find('input[name="tm-epo-counter"]').val() ),
				"tcaddtocart" : JSON.stringify( form.find('input[name="tcaddtocart"]').val() ),
				"quantity" : JSON.stringify( form.find('input[name="quantity"]').val() ),
				"add-to-cart" : JSON.stringify( form.find('input[name="add-to-cart"]').val() )
			},
			*/
			success: function( response ){

				//jQuery("html, body").css("cursor", "default");
				//jQuery("button").css("cursor", button_default_cursor);

				//updateCartButtons(response);

				//console.log("RETORNO AJAX");

				e.preventDefault();
	      e.stopPropagation();

				//jQuery(document).find('.vc_tta-panel').removeClass('vc_active');

				jQuery('html,body').animate({scrollTop:0}, 0,'swing');
				jQuery(window).scrollTop(0);
				document.body.scrollTop = document.documentElement.scrollTop = 0;

				//window.response = response;

				//console.log("RESPONSE SUCESSO "+jQuery(response).find('.woocommerce-message').length );
				//console.log("RESPONSE ERRO "+jQuery(response).find('.woocommerce-error').length );

				// SE ERRO
				if( jQuery(response).find('.woocommerce-error').length > 0 ){

										var div_to_insert = getMessageParentDiv(response, 'woocommerce-error');

										if( jQuery(document).find('.woocommerce-message').length > 0 ){
														jQuery(document).find('.woocommerce-message').fadeOut(500, function() {
																	jQuery(document).find('.woocommerce-message').remove();
														});
										}

										if( jQuery(document).find('.woocommerce-error').length > 0 ){

														jQuery(document).find('.woocommerce-error').fadeOut(500, function() {
																	jQuery(document).find('.woocommerce-error').remove();
																	jQuery(div_to_insert).before( jQuery(response).find('.woocommerce-error').wrap('<div>').parent().html() ).fadeIn();
																	if( jQuery(document).find('.quickview-wrap').length > 0 ){
																			jQuery(document).find('.quickview-wrap').before( jQuery(response).find('.woocommerce-error').wrap('<div>').parent().html() ).fadeIn();
																	}
														});

										}else{
														jQuery(div_to_insert).before( jQuery(response).find('.woocommerce-error').wrap('<div>').parent().html()	).fadeIn();
														if( jQuery(document).find('.quickview-wrap').length > 0 ){
																jQuery(document).find('.quickview-wrap').before( jQuery(response).find('.woocommerce-error').wrap('<div>').parent().html() ).fadeIn();
														}
										}

										if( jQuery(document).find('.quickview-wrap').length > 0 ){
												//jQuery(document).find('.quickview-wrap').before( jQuery(response).find('.woocommerce-error').wrap('<div>').parent().html() ).fadeIn();
												//jQuery('html,body').animate({scrollTop:0}, 500,'swing');
												//jQuery(window).scrollTop(0);
												//document.body.scrollTop = document.documentElement.scrollTop = 0;
												jQuery('.jconfirm-scrollpane').animate({scrollTop:0}, 0,'swing');
												jQuery('.jconfirm-scrollpane').scrollTop(0);
										}

										var is_in_viewport = isElementInViewport(	jQuery(document).find('.woocommerce-error')	);
										if(!is_in_viewport){
												//jQuery('html,body').animate({
												//   		scrollTop: jQuery(".woocommerce-error").offset().top - 50
												//}, 500);
												//jQuery('html,body').animate({scrollTop:0}, 500,'swing');

												jQuery('html,body').animate({scrollTop:0}, 0,'swing');
												jQuery(window).scrollTop(0);
												document.body.scrollTop = document.documentElement.scrollTop = 0;
										}

										jQuery.data(document.body, "processing", 0);

				// SE SUCESSO
				}else if(	jQuery(response).find('.woocommerce-message').length > 0	){


										//console.log('SUCESSO MENSAGEM');

										updateCartButtons(response);



										///*
										var extra = jQuery(".cart-pedido-lavanderia");
										var edit = jQuery(".cart-pedido-lavanderia input[name='tc_cart_edit_key']");
										var buttons = jQuery(".widget_shopping_cart .buttons");
										var editando = edit.length > 0;
										if( extra.length > 0 && ( extra.find('input[name="add-to-cart"]').val()==id_produto ) ){

												if( extra.css('display') == 'block' ) extra.slideToggle( "slow");
												//extra.remove();
												setTimeout(function () {
													extra.remove();
												},500);
												//extra.fadeOut();
												buttons.fadeIn();
												//cart_item_key = '';
												//edit.val(0);
												//buttons.fadeOut();
												//console.log(" AJAX ESCONDE COLETA "+post_id);

										}


										var div_to_insert = getMessageParentDiv(response, 'woocommerce-message');

										if( jQuery(document).find('.woocommerce-error').length > 0 ){
														jQuery(document).find('.woocommerce-error').fadeOut(500, function() {
																	jQuery(document).find('.woocommerce-error').remove();
														});
										}

										if(	jQuery(document).find('.woocommerce-message').length > 0	){

													jQuery(document).find('.woocommerce-message').fadeOut(500, function() {
																	jQuery(document).find('.woocommerce-message').remove();
																	jQuery(div_to_insert).before(jQuery(response).find('.woocommerce-message').wrap('<div>').parent().html()).fadeIn();
													});

										}else{
													jQuery(div_to_insert).before(jQuery(response).find('.woocommerce-message').wrap('<div>').parent().html()).fadeIn();
										}

										if( jQuery(document).find('.quickview-wrap').length > 0 ){
												//jQuery(document).find('.quickview-wrap').before( jQuery(response).find('.woocommerce-error').wrap('<div>').parent().html() ).fadeIn();
												//jQuery('html,body').animate({scrollTop:0}, 500,'swing');
												//jQuery(window).scrollTop(0);
												//document.body.scrollTop = document.documentElement.scrollTop = 0;
												jc_item.close();
										}

										var url = window.location.href;
										if( url.indexOf('enviar-pedido') !== -1 ){
												//MyObject.loadingSite();
												jQuery('.checkout').addClass('validating');
												window.location.href = url;
										}else if( url.indexOf('minha-lavanderia') !== -1 ){
												MyObject.loadingSite();
												window.location.href = url;
										}



										var is_in_viewport = isElementInViewport( jQuery(document).find('.woocommerce-message') );
										if(!is_in_viewport){
												//jQuery('html,body').animate({
												   //scrollTop: jQuery(".woocommerce-message").offset().top - 50
												//}, 500);
												//jQuery('html,body').animate({scrollTop:0}, 500,'swing');

												setTimeout(function () {
													jQuery('html,body').animate({scrollTop:0}, 0,'swing');
													jQuery(window).scrollTop(0);
													document.body.scrollTop = document.documentElement.scrollTop = 0;
												},1000);

										}

										jQuery.data(document.body, "processing", 0);

				}

				//cart_item_key = '';

				jQuery(button).text(textOld);
				jQuery(button).removeAttr('disabled');
				jQuery(button).prop("disabled", false);
				jQuery(button).removeClass('disabled');
				if( jQuery(button).attr('data-id') != null ){
						jQuery('.'+jQuery(button).attr('data-id')).text(textOld);
						jQuery('.'+jQuery(button).attr('data-id')).removeAttr('disabled');
						jQuery('.'+jQuery(button).attr('data-id')).prop("disabled", false);
						jQuery('.'+jQuery(button).attr('data-id')).removeClass('disabled');
				}

				jQuery.data(document.body, "processed_once", 1);

				/*
				var removeComTempo = 0;
				//removeComTempo = 30000;
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


				/*
				setTimeout(function () {
						jQuery('html,body').animate({scrollTop:0}, 0,'swing');
						jQuery(window).scrollTop(0);
						document.body.scrollTop = document.documentElement.scrollTop = 0;
				},1000);
				*/

				jQuery('.woocommerce-message .close-button, .woocommerce-error .close-button,.woocommerce-info .close-button').on('click', function(){
			        jQuery(this).fadeOut().delay(10).queue(function(){
			          jQuery(this).parent().fadeOut();
			        });
			  });
				jQuery('.menu-topo-minha-conta > a, .archive-products:not(.lista-lavanderias) .product-image > a, .archive-products:not(.lista-lavanderias) .product-loop-title, .woocommerce-msg a, .checkout-button, .cart-actions .btn, .coupon .btn, .click-loading, .woocommerce-MyAccount-navigation-link > a, .logo > a').on('click', function(e) {
			      MyObject.loadingSite();
						if( jQuery(document).find('.quickview-wrap').length > 0 ){
								//jQuery(document).find('.quickview-wrap').before( jQuery(response).find('.woocommerce-error').wrap('<div>').parent().html() ).fadeIn();
								//jQuery('html,body').animate({scrollTop:0}, 500,'swing');
								//jQuery(window).scrollTop(0);
								//document.body.scrollTop = document.documentElement.scrollTop = 0;
								jc_item.close();
						}
			  });

				//console.log('FIM');


			},
			error:function(responseTxt, statusTxt, jqXHR){
						console.log("STATUS: "+statusTxt+" - Error: " + jqXHR.status + " " + jqXHR.statusText);
			}

		});
		return false;
	});

	function getCartUrl(){
		return oraksoft_js_data_watc.cart_url;
	}
	function getCartButtons(){
		return jQuery("a[href='"+getCartUrl()+"']:visible");
	}
	function getMessageParentDiv(response, woocommerce_msg){
		var default_dom = jQuery(".product.type-product:eq(0)");

		if(default_dom.length > 0){
			return default_dom;
		}else{
			var scheck_parent_div = jQuery(response).find("."+woocommerce_msg).parent();
			var id = jQuery(response).find("."+woocommerce_msg).parent().attr('id');
			if(id){
				return jQuery("#"+id).children().eq(jQuery("#"+id).children().length-1);
			}else{
				var classes = jQuery(response).find("."+woocommerce_msg).parent().attr('class');
				return jQuery(document).find("div[class='"+classes+"']").children().eq(jQuery(document).find("div[class='"+classes+"']").children().length-1);
			}
		}
	}

	function updateCartButtons(new_source){
		//jQuery(new_source).find('.woocommerce-error').remove();
		//jQuery(new_source).find('.woocommerce-message').remove();
		var cart_buttons_length = getCartButtons().length;
		if(cart_buttons_length > 0){
			getCartButtons().each(function(index) {
				if(jQuery(new_source).find("a[href='"+getCartUrl()+"']:visible").eq(index).length > 0){
					jQuery(this).replaceWith(jQuery(new_source).find("a[href='"+getCartUrl()+"']:visible").eq(index));
				}
			});
		}
		var $supports_html5_storage = ( 'sessionStorage' in window && window['sessionStorage'] !== null );
		var $fragment_refresh = {
			url: woocommerce_params.ajax_url,
			type: 'POST',
			data: { action: 'woocommerce_get_refreshed_fragments' },
			success: function( data ) {
				if ( data && data.fragments ) {
					$.each( data.fragments, function( key, value ) {
						jQuery(key).replaceWith(value);
					});
					if ( $supports_html5_storage ) {
						sessionStorage.setItem( "wc_fragments", JSON.stringify( data.fragments ) );
						sessionStorage.setItem( "wc_cart_hash", data.cart_hash );
					}
					jQuery('body').trigger( 'wc_fragments_refreshed' );
				}
			}
		};
		$.ajax($fragment_refresh);
	}


});
}(jQuery));
