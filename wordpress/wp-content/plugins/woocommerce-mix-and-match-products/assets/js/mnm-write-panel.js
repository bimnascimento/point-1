jQuery( function($){

	if ( typeof woocommerce_admin_meta_boxes === 'undefined' ) {
		woocommerce_admin_meta_boxes = woocommerce_writepanel_params;
	}

	// Hide the "Grouping" field.
	$( '#linked_product_data .grouping.show_if_simple, #linked_product_data .form-field.show_if_grouped' ).addClass( 'hide_if_mix-and-match' );

	// Simple type options are valid for mnm.
	$( '.show_if_simple:not(.hide_if_mix-and-match)' ).addClass( 'show_if_mix-and-match' );

	// Mix and Match type specific options
	$( 'body' ).on( 'woocommerce-product-type-change', function( event, select_val, select ) {

		if ( select_val === 'mix-and-match' ) {

			$( '.show_if_external' ).hide();
			$( '.show_if_mix-and-match' ).show();

			$( 'input#_manage_stock' ).change();

			if ( wc_mnm_admin_params.is_wc_version_gte_2_7 === 'no' ) {
				$( '#_regular_price' ).val( $( '#_wc_mnm_base_regular_price' ).val() ).change();
				$( '#_sale_price' ).val( $( '#_wc_mnm_base_sale_price' ).val() ).change();
			}
		}

	} );

	$( 'select#product-type' ).change();

} );
