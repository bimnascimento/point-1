jQuery(document).ready(function($) {
	"use strict";

	$(document).on("click", ".cart .single_add_to_cart_button", function(e) {

		e.preventDefault();

		var button = $(this);
		//window.button = button;
		var textOld = button.text();

		if( $(this).parent().parent().hasClass('cart-pedido-lavanderia') == true ){
				return;
		}

		if( $(this).hasClass('product_type_variable') ) return true;

		if( parseInt(jQuery.data(document.body, "processing") ) == 1) return false;

		jQuery.data(document.body, "processing", 1);
		jQuery.data(document.body, "processed_once", 0);

		var context = this;

		var form = $(this).closest('form');
		var button_default_cursor = $("button").css('cursor');

		//$("html, body").css("cursor", "wait");
		//$("button").css("cursor", "wait");

		function isElementInViewport (el) {
			if (typeof jQuery === "function" && el instanceof jQuery) {
				el = el[0];
			}

			var rect = el.getBoundingClientRect();

			return (
				rect.top >= 0 &&
				rect.left >= 0 &&
				rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
				rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
			);
		}

		function get_form_data(formobj, i){
	    i = typeof i !== 'undefined' ? i : 0;
	    if (typeof formdata['variation_id'] === 'undefined') { formdata['variation_id'] = new Array; }
	    if (typeof formdata['product_id'] === 'undefined') { formdata['product_id'] = new Array; }
			if (typeof formdata['add-to-cart'] === 'undefined') { formdata['add-to-cart'] = new Array; }
	    if (typeof formdata['quantity'] === 'undefined') { formdata['quantity'] = new Array; }
	    if (typeof formdata['gift_wrap'] === 'undefined') { formdata['gift_wrap'] = new Array; }
	    if (typeof formdata['variations'] === 'undefined') { formdata['variations'] = new Array; }
	    if (typeof formdata['wcplprotable_ajax'] === 'undefined') { formdata['wcplprotable_ajax'] = new Array; }
	    if (typeof formdata['wcplprotable_globalcart'] === 'undefined') { formdata['wcplprotable_globalcart'] = new Array; }
	    if (typeof formdata['cartredirect'] === 'undefined') { formdata['cartredirect'] = new Array; }
	    if (typeof formdata['thisbuttonid'] === 'undefined') { formdata['thisbuttonid'] = new Array; }
	    formdata['variation_id'].push(formobj.find('input[name="variation_id"]').val());
	    formdata['product_id'].push(formobj.find('input[name="product_id"]').val());
			formdata['add-to-cart'].push(formobj.find('input[name="add-to-cart"]').val());
	    formdata['quantity'].push(formobj.find('input[name="quantity"]').val());
	    formdata['gift_wrap'].push(formobj.find('input[name="gift_wrap"]').val());
	    formdata['variations'].push(formobj.find('input[name="form_wcplprotable_attribute_json"]').val());
	    formdata['wcplprotable_ajax']       = formobj.closest('table.wcplprotable').data('wcplprotable_ajax');
	    formdata['wcplprotable_globalcart'] = formobj.closest('table.wcplprotable').data('globalcart');
	    formdata['cartredirect']            = formobj.closest('table.wcplprotable').data('cartredirect');
	    //var thisbuttonid = formobj.find('button.add_to_cart').attr('id').split('_');
			//formdata['thisbuttonid'].push(thisbuttonid[1]);
			//var thisbuttonid = formobj.find('input[name="add-to-cart"]').val();
	    //formdata['thisbuttonid'].push(thisbuttonid);
	    // formdata['addvtdata'].push(formobj.serialize());
	    // formdata['thisid'].push(formobj.attr("data-variation_id"));
	    // formdata['thisbutton'] = formobj.find('button.add_to_cart');
	    return formdata;
	  }

		var ajaxurl             = wcplprovars.ajax_url;
	  var carturl             = wcplprovars.cart_url;
	  var currency_symbol     = wcplprovars.currency_symbol;
	  var thousand_separator  = wcplprovars.thousand_separator;
	  var decimal_separator   = wcplprovars.decimal_separator;
	  var decimal_decimals    = wcplprovars.decimal_decimals;
	  var currency_pos        = wcplprovars.currency_pos;
	  var price_display_suffix= wcplprovars.price_display_suffix;
	  var gclicked            = 0;
	  var glob_clicked        = 0;
	  var count               = 0;
	  var numofadded          = 0;
	  var wcplpro_ajax        = wcplprovars.wcplpro_ajax;
	  var $fragment_refresh   = '';
	  var get_global_cart_id  = '';
	  var formdata            = new Array;
		//window.formdata = formdata;
		//window.numofadded = numofadded;

		if ( form.find(".quantity .qty").val() > 0 ) { // && jQuery(this).closest('tr').find("input.globalcheck").is(":checked")
        // reset the formdata array
        formdata = [];
        formdata.length = 0;
        //var formulario = get_form_data(form);
				formdata = get_form_data(form);
				//console.log(formulario);
				//$formdata = get_form_data(form);
				if ( button.length > 0 ){

          button.attr('disabled', 'disabled');
					button.text('Aguarde..');
          //jQuery('.add2cartbtn_'+formdata['thisbuttonid']).addClass('working');
          //jQuery(".vtspinner_"+ formdata['thisbuttonid']).fadeIn(200);
          //jQuery(".vtspinner_"+ formdata['thisbuttonid']).css("display","inline-block");
        }
        numofadded = numofadded + parseInt( form.find(".quantity .qty").val() );
        wcplpro_request(formdata);
				//console.log(numofadded);
    }

		return;

		function wcplpro_request(formdata){

			console.log("entrou");

			//console.log("REQUEST 0" + ajaxurl);
	    //console.log(JSON.stringify( parseInt(formdata['quantity']) ));
	    //console.log(formdata);

	    //jQuery(".added2cart_"+ formdata['thisbuttonid']).hide();

	    //var cart_qnt_atual = jQuery(document).find('#cart-'+formdata['product_id']).attr('data-qnt');

			//return;

	    jQuery.ajaxQueue({
	          type:"POST",
	          url: ajaxurl,
	          data: {
	            "action" : "add_product_to_cart",
	            //"product_id" : JSON.stringify(formdata['product_id']),
							"product_id" : JSON.stringify( formdata['add-to-cart'] ),
	            "variation_id" : JSON.stringify( formdata['variation_id'] ),
	            "quantity" : JSON.stringify( formdata['quantity'] ),
	            "gift_wrap" : JSON.stringify( formdata['gift_wrap'] )
	          },
	          success:function(data){

								//$.ajax($fragment_refresh);
								//console.log("SUCESSO");
								console.log(data);

								$.ajax({
		                    url: ajaxurl,
		                    // url: woocommerce_params.ajax_url,
		                    type: 'POST',
		                    data: { action: 'woocommerce_get_refreshed_fragments' },
		                    success: function( response ) {

														//console.log("WCPLPRO - AJAX");
		                        if ( response && response.fragments ) {

																button.text(textOld);
																button.removeAttr('disabled');

		                            //window.info = response;
		                            //window.fragmento = response.fragments;
		                            //jQuery(fragmento).find('#cart-5374').attr('data-qnt');
		                            //jQuery("#cart-5374").attr('data-qnt');

		                            $.each( response.fragments, function( key, value ) {
		                                //console.log(value);
		                                //$(key).replaceWith(value);
		                            });
		                            if ( $supports_html5_storage ) {
		                                //sessionStorage.setItem( "wc_fragments", JSON.stringify( response.fragments ) );
		                                //sessionStorage.setItem( "wc_cart_hash", response.cart_hash );
		                            }

		                            //console.log('refresh');
		                            //$('body').trigger( 'wc_fragments_refreshed' );
		                            //MyObject.atualizaPedidoPeca(formdata['product_id'],formdata['quantity'],cart_qnt_atual);

																window.dados = response;

																updateCartButtons(response);

																if( $(response).find('.woocommerce-error').length > 0)
																{
																	var div_to_insert = getMessageParentDiv(response, 'woocommerce-error');

																	if($(document).find('.woocommerce-error').length > 0)
																	{
																		$(document).find('.woocommerce-error').fadeOut(500, function() {
																			$(document).find('.woocommerce-error').remove();
																			$(div_to_insert).before($(response).find('.woocommerce-error').wrap('<div>').parent().html()).fadeIn();
																		});
																	}
																	else
																	{
																		$(div_to_insert).before($(response).find('.woocommerce-error').wrap('<div>').parent().html());
																	}

																	var is_in_viewport = isElementInViewport($(document).find('.woocommerce-error'));

																	if(!is_in_viewport)
																	{
																		$('html,body').animate({
																		   scrollTop: $(".woocommerce-error").offset().top - 50
																		}, 500);
																	}

																	jQuery.data(document.body, "processing", 0);
																}
																else if($(response).find('.woocommerce-message').length > 0)
																{
																	var div_to_insert = getMessageParentDiv(response, 'woocommerce-message');

																	if($(document).find('.woocommerce-message').length > 0)
																	{
																		$(document).find('.woocommerce-message').fadeOut(500, function() {
																			$(document).find('.woocommerce-message').remove();
																			$(div_to_insert).before($(response).find('.woocommerce-message').wrap('<div>').parent().html()).fadeIn();
																		});
																	}
																	else
																	{
																		$(div_to_insert).before($(response).find('.woocommerce-message').wrap('<div>').parent().html());
																	}


																	var is_in_viewport = isElementInViewport($(document).find('.woocommerce-message'));

																	if(!is_in_viewport)
																	{
																		$('html,body').animate({
																		   scrollTop: $(".woocommerce-message").offset().top - 50
																		}, 500);
																	}

																	jQuery.data(document.body, "processing", 0);
																}


																jQuery.data(document.body, "processed_once", 1);



		                        }
		                    },
		                    error:function(responseTxt, statusTxt, jqXHR){
		                          //$('#maincont').html("ERRO: TENTE NOVAMENTE!");
		                          console.log("STATUS: "+statusTxt+" - Error: " + jqXHR.status + " " + jqXHR.statusText);
		                          //jQuery(".loading-site").hide();
		                          //$('.navbar-more-overlay').click();
		                          //if ($('body').hasClass('navbar-more-show'))	{
		                          //  $('body').toggleClass('navbar-more-show');
		                      		//	$('.navbar-more-overlay').closest('li').removeClass('active');
		                      	  //	}
		                    }
		                });

						},
						error: function(XMLHttpRequest, textStatus, errorThrown) {
								console.log("Status: " + textStatus);
								console.log("Error: " + errorThrown);
						}
			});

		};

		$.ajax( {
			type: "POST",
			url: form.attr( 'action' ),
			data: form.serialize(),
			success: function( response )
			{
				$("html, body").css("cursor", "default");
				$("button").css("cursor", button_default_cursor);

				updateCartButtons(response);


				if($(response).find('.woocommerce-error').length > 0)
				{
					var div_to_insert = getMessageParentDiv(response, 'woocommerce-error');

					if($(document).find('.woocommerce-error').length > 0)
					{
						$(document).find('.woocommerce-error').fadeOut(500, function() {
							$(document).find('.woocommerce-error').remove();
							$(div_to_insert).before($(response).find('.woocommerce-error').wrap('<div>').parent().html()).fadeIn();
						});
					}
					else
					{
						$(div_to_insert).before($(response).find('.woocommerce-error').wrap('<div>').parent().html());
					}

					var is_in_viewport = isElementInViewport($(document).find('.woocommerce-error'));

					if(!is_in_viewport)
					{
						$('html,body').animate({
						   scrollTop: $(".woocommerce-error").offset().top - 50
						}, 500);
					}

					jQuery.data(document.body, "processing", 0);
				}
				else if($(response).find('.woocommerce-message').length > 0)
				{
					var div_to_insert = getMessageParentDiv(response, 'woocommerce-message');

					if($(document).find('.woocommerce-message').length > 0)
					{
						$(document).find('.woocommerce-message').fadeOut(500, function() {
							$(document).find('.woocommerce-message').remove();
							$(div_to_insert).before($(response).find('.woocommerce-message').wrap('<div>').parent().html()).fadeIn();
						});
					}
					else
					{
						$(div_to_insert).before($(response).find('.woocommerce-message').wrap('<div>').parent().html());
					}


					var is_in_viewport = isElementInViewport($(document).find('.woocommerce-message'));

					if(!is_in_viewport)
					{
						$('html,body').animate({
						   scrollTop: $(".woocommerce-message").offset().top - 50
						}, 500);
					}

					jQuery.data(document.body, "processing", 0);
				}


				jQuery.data(document.body, "processed_once", 1);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
					console.log("Status: " + textStatus);
					console.log("Error: " + errorThrown);
			}
		});

		return false;
	});

	function getCartUrl()
	{
		return oraksoft_js_data_watc.cart_url;
	}

	function getCartButtons()
	{
		return $("a[href='"+getCartUrl()+"']:visible");
	}

	function getMessageParentDiv(response, woocommerce_msg)
	{
		var default_dom = $(".product.type-product:eq(0)");

		if(default_dom.length > 0)
		{
			return default_dom;
		}
		else
		{
			var scheck_parent_div = $(response).find("."+woocommerce_msg).parent();
			var id = $(response).find("."+woocommerce_msg).parent().attr('id');

			if(id)
			{
				return $("#"+id).children().eq($("#"+id).children().length-1);
			}
			else
			{
				var classes = $(response).find("."+woocommerce_msg).parent().attr('class');
				return $(document).find("div[class='"+classes+"']").children().eq($(document).find("div[class='"+classes+"']").children().length-1);
			}
		}
	}

	function updateCartButtons(new_source)
	{
		$(new_source).find('.woocommerce-error').remove();
		$(new_source).find('.woocommerce-message').remove();

		var cart_buttons_length = getCartButtons().length;

		if(cart_buttons_length > 0)
		{
			getCartButtons().each(function(index) {
				if($(new_source).find("a[href='"+getCartUrl()+"']:visible").eq(index).length > 0)
				{
					$(this).replaceWith($(new_source).find("a[href='"+getCartUrl()+"']:visible").eq(index));
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
						$(key).replaceWith(value);
					});

					if ( $supports_html5_storage ) {
						sessionStorage.setItem( "wc_fragments", JSON.stringify( data.fragments ) );
						sessionStorage.setItem( "wc_cart_hash", data.cart_hash );
					}

					$('body').trigger( 'wc_fragments_refreshed' );
				}
			}
		};

		$.ajax($fragment_refresh);
	}
});
