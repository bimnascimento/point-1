jQuery( function( $ ) {

	// Shipping calculator
	$( document ).on( 'change', '.compare-product-link input', function(e) {
        e.preventDefault();

		product_id = jQuery( this ).parent().attr('product-id');

		$( this ).parent().block({ message: null, overlayCSS: { background: '#fff url(' + woocommerce_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6 } });

		var data_ap = { action: 'be_compare_add_product', product: product_id };
		$.post( woocommerce_params.ajax_url, data_ap, function( response ) {

			$( '#compare-link-' + product_id ).replaceWith( response );

		});

		var data_ub = { action: 'be_compare_update_basket' };
		$.post( woocommerce_params.ajax_url, data_ub, function( response ) {

			$( 'div#compare-products-basket' ).replaceWith( response );

		});

		return;

	})

	// Delete single item from basket
	$( document ).on( 'click', '.compare-product-remove', function(e) {
        e.preventDefault();

		product_id = jQuery( this ).parent().attr('product-id');

		$( this ).parent().block({ message: null, overlayCSS: { background: '#fff url(' + woocommerce_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6 } });

		var data_ap = { action: 'be_compare_add_product', product: product_id };
		$.post( woocommerce_params.ajax_url, data_ap, function( response ) {

			$( '#compare-link-' + product_id ).replaceWith( response );

		});

		var data_ub = { action: 'be_compare_update_basket' };
		$.post( woocommerce_params.ajax_url, data_ub, function( response ) {

			$( 'div#compare-products-basket' ).replaceWith( response );

		});

		return;

	});

    // Show / Hide category details
    jQuery('#compare-products-basket .compare-clear-items a').live('click', function(e){
        e.preventDefault();

		var data_ap = { action: 'be_compare_empty_basket' };
    	$.post( woocommerce_params.ajax_url, data_ap, function( response ) {

			$( 'div#compare-products-basket' ).replaceWith( response );

		});

    });

    // Show / Hide category details
    jQuery('.compare-products-button').live('click', function(){
    	window.location.href = be_compare_params.compare_button_url;
    	return false;
    });

});
