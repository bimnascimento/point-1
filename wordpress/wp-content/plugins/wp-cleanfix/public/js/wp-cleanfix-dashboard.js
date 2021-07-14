(function( $ )
{

  "use strict";

  function boot()
  {
    // refresh
    $( document ).on( 'click', '.wp-cleanfix-refresh button',
      function( e )
      {
        e.preventDefault();

        const $this = $( this );

        const payload = {
                action : 'wp_cleanfix_refresh',
                module : $this.parents( 'tr' ).data( 'module' ),
                test   : $this.data( 'test' )
              },
              $tr     = $this.parents( 'tr' );

        $tr.next( '.wp-cleanfix-bottom-row' ).fadeTo( 200, 0.3 );
        $tr.find( 'td' ).hide();
        $tr.find( '.wp-cleanfix-hidle' ).show();

        // loggedin
        $.post(
          ajaxurl,
          payload,
          function( data )
          {
            const test = data.test,
                  html = data.html;

            $tr.next( '.wp-cleanfix-bottom-row' ).remove();
            $tr.replaceWith( html );

          } );
      }
    );

    // cleanFix
    $( document ).on( 'click', '.wp-cleanfix-actions button',
      function( e )
      {
        e.preventDefault();

        const $this = $( this );

        const confirmMessage = $this.data( 'confirm' );

        if( 'undefined' !== typeof( confirmMessage ) ) {
          if( !confirm( confirmMessage ) ) {
            return false;
          }
        }

        const payload = {
          action : 'wp_cleanfix',
          module : $this.parents( 'tr' ).data( 'module' ),
          test   : $this.data( 'test' )
        };

        const filterName = $this.data( 'filter' );

        if( 'undefined' !== typeof( filterName ) ) {
          payload.extra = wpbones_apply_filters( filterName, null );
        }

        const beforeSend = $this.data( 'before_send' );

        if( 'undefined' !== typeof( beforeSend ) ) {
          if( !wpbones_apply_filters( beforeSend, true ) ) {
            return;
          }
        }

        const $tr = $this.parents( 'tr' );

        $tr.next( '.wp-cleanfix-bottom-row' ).fadeTo( 200, 0.3 );
        $tr.find( 'td' ).hide();
        $tr.find( '.wp-cleanfix-hidle' ).show();

        // loggedin
        $.post(
          ajaxurl,
          payload,
          function( data )
          {
            const test = data.test,
                  html = data.html;

            $tr.next( '.wp-cleanfix-bottom-row' ).remove();
            $( '[data-test="' + test + '"]' ).parents( 'tr' ).replaceWith( html );

          } );
      }
    );

    // refresh all
    $( document ).on( 'click', '.wp-cleanfix-refresh-all button',
      function( e )
      {
        e.preventDefault();

        $( this )
          .parents( '.wp-cleanfix-toolbar' )
          .next( 'table' )
          .find( '.wp-cleanfix-refresh button' )
          .click();
      }
    );

    // cleanFix all
    $( document ).on( 'click', '.wp-cleanfix-fix-all button',
      function( e )
      {
        e.preventDefault();

        $( this )
          .parents( '.wp-cleanfix-toolbar' )
          .next( 'table' )
          .find( '.wp-cleanfix-actions button' )
          .click();
      }
    );
  }

  boot();

})( jQuery );