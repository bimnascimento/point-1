(function( $ )
{

  "use strict";

  function boot()
  {
    // database tools - compact
    $( document ).on( 'click', 'button.wp-cleanfix-button-compact',
      function( e )
      {
        e.preventDefault();

        const $this          = $( this ),
              table          = $this.data( 'table' ),
              $autoIncrement = $( '[data-table="auto-increment-' + table + '"]' );

        const payload = {
          action     : 'wp_cleanfix_tools_compact_table',
          table_name : table,
          backup     : $( 'input[name="wp-cleanfix-tools-database-make-backup"]' ).is( ":checked" )
        };

        // display loading
        const $loading = $( '<img class="loading" src="/wp-admin/images/loading.gif"/>' );

        $this.attr( 'disabled', 'disabled' );

        $( $loading )
          .prependTo( $autoIncrement );

        // loggedin
        $.post(
          ajaxurl,
          payload,
          function( response )
          {
            $loading.remove();
            $this.removeAttr( 'disabled' );
            $autoIncrement.html( response.data.info.Auto_increment );

          } );
      }
    );

    // posts tools - clear fields
    $( document ).on( 'click', 'button.wp-clearfix-tools-posts-clear-fields,button.wp-cleanfix-tools-posts-ok-button',
      function( e )
      {
        e.preventDefault();

        $( '#wp-clearfix-tools-posts-feedback' ).html( '' );
        $( '#wp-cleanfix-tools-posts-posttypes' ).removeAttr( 'disabled' ).find( 'option:first-child' ).attr( 'selected', 'selected' );
        $( '#wp-cleanfix-tools-posts-poststatus' ).removeAttr( 'disabled' ).find( 'option:first-child' ).attr( 'selected', 'selected' );
        $( '#wp-cleanfix-tools-posts-find' ).removeAttr( 'disabled' ).val( '' );
        $( '#wp-cleanfix-tools-posts-replace' ).removeAttr( 'disabled' ).val( '' );

        $( 'button.wp-clearfix-tools-posts-find-button' ).removeAttr( 'disabled' );

      }
    );

    // posts tools - find
    $( document ).on( 'click', 'button.wp-clearfix-tools-posts-find-button',
      function( e )
      {
        e.preventDefault();

        const $this       = $( this ),
              $feedback   = $( '#wp-clearfix-tools-posts-feedback' ),
              $postType   = $( '#wp-cleanfix-tools-posts-posttypes' ),
              $postStatus = $( '#wp-cleanfix-tools-posts-poststatus' ),
              $find       = $( '#wp-cleanfix-tools-posts-find' ),
              $replace    = $( '#wp-cleanfix-tools-posts-replace' );

        const payload = {
          action      : "wp_cleanfix_tools_post_find",
          post_type   : $postType.val(),
          post_status : $postStatus.val(),
          find        : $find.val(),
          replace     : $replace.val()
        };

        // display loading
        const $loading = $( '<img class="loading" src="/wp-admin/images/loading.gif"/>' );

        $this.attr( 'disabled', 'disabled' );
        $postType.attr( 'disabled', 'disabled' );
        $postStatus.attr( 'disabled', 'disabled' );
        $find.attr( 'disabled', 'disabled' );
        $replace.attr( 'disabled', 'disabled' );

        $feedback.html( $loading );

        // loggedin
        $.post(
          ajaxurl,
          payload,
          function( response )
          {
            if( response.data.count == 0 || response.data.count.count == 0 ) {
              $this.removeAttr( 'disabled' );
              $postType.removeAttr( 'disabled' );
              $postStatus.removeAttr( 'disabled' );
              $find.removeAttr( 'disabled' );
              $replace.removeAttr( 'disabled' );
            }

            $feedback.html( response.data.feedback );

          } );
      }
    );

    // posts tools - cancel replace
    $( document ).on( 'click', 'button.wp-cleanfix-tools-posts-replace-cancel-button',
      function( e )
      {
        e.preventDefault();

        const $findButton = $( 'button.wp-clearfix-tools-posts-find-button' ),
              $feedback   = $( '#wp-clearfix-tools-posts-feedback' ),
              $postType   = $( '#wp-cleanfix-tools-posts-posttypes' ),
              $postStatus = $( '#wp-cleanfix-tools-posts-poststatus' ),
              $find       = $( '#wp-cleanfix-tools-posts-find' ),
              $replace    = $( '#wp-cleanfix-tools-posts-replace' );

        $findButton.removeAttr( 'disabled' );
        $postType.removeAttr( 'disabled' );
        $postStatus.removeAttr( 'disabled' );
        $find.removeAttr( 'disabled' );
        $replace.removeAttr( 'disabled' );

        $feedback.html( '' );
      }
    );

    // posts tools - continue/replace
    $( document ).on( 'click', 'button.wp-cleanfix-tools-posts-replace-button',
      function( e )
      {
        e.preventDefault();

        const $this     = $( this ),
              $feedback = $( '#wp-clearfix-tools-posts-feedback' );

        if( !confirm( $this.data( 'confirm' ) ) ) {
          return false;
        }

        const payload = {
          action      : "wp_cleanfix_tools_post_replace",
          post_type   : $( '#wp-cleanfix-tools-posts-posttypes' ).val(),
          post_status : $( '#wp-cleanfix-tools-posts-poststatus' ).val(),
          find        : $( '#wp-cleanfix-tools-posts-find' ).val(),
          replace     : $( '#wp-cleanfix-tools-posts-replace' ).val()
        };

        // display loading
        const $loading = $( '<img class="loading" src="/wp-admin/images/loading.gif"/>' );

        $this.attr( 'disabled', 'disabled' );

        $feedback.html( $loading );

        // loggedin
        $.post(
          ajaxurl,
          payload,
          function( response )
          {
            $this.removeAttr( 'disabled' );

            $feedback.html( response.data.feedback );

          } );
      }
    );

    // ---

    // comments tools - clear fields
    $( document ).on( 'click', 'button.wp-clearfix-tools-comments-clear-fields,button.wp-cleanfix-tools-comments-ok-button',
      function( e )
      {
        e.preventDefault();

        $( '#wp-clearfix-tools-comments-feedback' ).html( '' );
        $( '#wp-cleanfix-tools-comments-approved' ).removeAttr( 'disabled' ).find( 'option:first-child' ).attr( 'selected', 'selected' );
        $( '#wp-cleanfix-tools-comments-type' ).removeAttr( 'disabled' ).find( 'option:first-child' ).attr( 'selected', 'selected' );
        $( '#wp-cleanfix-tools-comments-find' ).removeAttr( 'disabled' ).val( '' );
        $( '#wp-cleanfix-tools-comments-replace' ).removeAttr( 'disabled' ).val( '' );

        $( 'button.wp-clearfix-tools-comments-find-button' ).removeAttr( 'disabled' );

      }
    );

    // comments tools - find
    $( document ).on( 'click', 'button.wp-clearfix-tools-comments-find-button',
      function( e )
      {
        e.preventDefault();

        const $this            = $( this ),
              $feedback        = $( '#wp-clearfix-tools-comments-feedback' ),
              $commentApproved = $( '#wp-cleanfix-tools-comments-approved' ),
              $commentType     = $( '#wp-cleanfix-tools-comments-type' ),
              $find            = $( '#wp-cleanfix-tools-comments-find' ),
              $replace         = $( '#wp-cleanfix-tools-comments-replace' );

        const payload = {
          action           : "wp_cleanfix_tools_comment_find",
          comment_approved : $commentApproved.val(),
          comment_type     : $commentType.val(),
          find             : $find.val(),
          replace          : $replace.val()
        };

        // display loading
        const $loading = $( '<img class="loading" src="/wp-admin/images/loading.gif"/>' );

        $this.attr( 'disabled', 'disabled' );
        $commentApproved.attr( 'disabled', 'disabled' );
        $commentType.attr( 'disabled', 'disabled' );
        $find.attr( 'disabled', 'disabled' );
        $replace.attr( 'disabled', 'disabled' );

        $feedback.html( $loading );

        // loggedin
        $.post(
          ajaxurl,
          payload,
          function( response )
          {
            if( response.data.count == 0 || response.data.count.count == 0 ) {
              $this.removeAttr( 'disabled' );
              $commentApproved.removeAttr( 'disabled' );
              $commentType.removeAttr( 'disabled' );
              $find.removeAttr( 'disabled' );
              $replace.removeAttr( 'disabled' );
            }

            $feedback.html( response.data.feedback );

          } );
      }
    );

    // comments tools - cancel replace
    $( document ).on( 'click', 'button.wp-cleanfix-tools-comments-replace-cancel-button',
      function( e )
      {
        e.preventDefault();

        const $findButton      = $( 'button.wp-clearfix-tools-comments-find-button' ),
              $feedback        = $( '#wp-clearfix-tools-comments-feedback' ),
              $commentApproved = $( '#wp-cleanfix-tools-comments-approved' ),
              $commentType     = $( '#wp-cleanfix-tools-comments-type' ),
              $find            = $( '#wp-cleanfix-tools-comments-find' ),
              $replace         = $( '#wp-cleanfix-tools-comments-replace' );

        $findButton.removeAttr( 'disabled' );
        $commentApproved.removeAttr( 'disabled' );
        $commentType.removeAttr( 'disabled' );
        $find.removeAttr( 'disabled' );
        $replace.removeAttr( 'disabled' );

        $feedback.html( '' );
      }
    );

    // comments tools - continue/replace
    $( document ).on( 'click', 'button.wp-cleanfix-tools-comments-replace-button',
      function( e )
      {
        e.preventDefault();

        const $this     = $( this ),
              $feedback = $( '#wp-clearfix-tools-comments-feedback' );

        if( !confirm( $this.data( 'confirm' ) ) ) {
          return false;
        }

        const payload = {
          action           : "wp_cleanfix_tools_comment_replace",
          comment_approved : $( '#wp-cleanfix-tools-comments-approved' ).val(),
          comment_type     : $( '#wp-cleanfix-tools-comments-type' ).val(),
          find             : $( '#wp-cleanfix-tools-comments-find' ).val(),
          replace          : $( '#wp-cleanfix-tools-comments-replace' ).val()
        };

        // display loading
        const $loading = $( '<img class="loading" src="/wp-admin/images/loading.gif"/>' );

        $this.attr( 'disabled', 'disabled' );

        $feedback.html( $loading );

        // loggedin
        $.post(
          ajaxurl,
          payload,
          function( response )
          {
            $this.removeAttr( 'disabled' );

            $feedback.html( response.data.feedback );

          } );
      }
    );

    // ---

    // postmeta tools - clear fields
    $( document ).on( 'click', 'button.wp-clearfix-tools-postmeta-clear-fields,button.wp-cleanfix-tools-postmeta-ok-button',
      function( e )
      {
        e.preventDefault();

        $( '#wp-clearfix-tools-postmeta-feedback' ).html( '' );
        $( '#wp-cleanfix-tools-postmeta-column' ).removeAttr( 'disabled' ).find( 'option:first-child' ).attr( 'selected', 'selected' );
        $( '#wp-cleanfix-tools-postmeta-find' ).removeAttr( 'disabled' ).val( '' );
        $( '#wp-cleanfix-tools-postmeta-replace' ).removeAttr( 'disabled' ).val( '' );

        $( 'button.wp-clearfix-tools-postmeta-find-button' ).removeAttr( 'disabled' );

      }
    );

    // postmeta tools - find
    $( document ).on( 'click', 'button.wp-clearfix-tools-postmeta-find-button',
      function( e )
      {
        e.preventDefault();

        const $this     = $( this ),
              $feedback = $( '#wp-clearfix-tools-postmeta-feedback' ),
              $column   = $( '#wp-cleanfix-tools-postmeta-column' ),
              $find     = $( '#wp-cleanfix-tools-postmeta-find' ),
              $replace  = $( '#wp-cleanfix-tools-postmeta-replace' );

        const payload = {
          action  : "wp_cleanfix_tools_postmeta_find",
          column  : $column.val(),
          find    : $find.val(),
          replace : $replace.val()
        };

        // display loading
        const $loading = $( '<img class="loading" src="/wp-admin/images/loading.gif"/>' );

        $this.attr( 'disabled', 'disabled' );
        $column.attr( 'disabled', 'disabled' );
        $find.attr( 'disabled', 'disabled' );
        $replace.attr( 'disabled', 'disabled' );

        $feedback.html( $loading );

        // loggedin
        $.post(
          ajaxurl,
          payload,
          function( response )
          {
            if( response.data.count == 0 || parseInt( response.data.count.count ) == 0 || parseInt( response.data.count.affected_postmeta ) == 0 ) {
              $this.removeAttr( 'disabled' );
              $column.removeAttr( 'disabled' );
              $find.removeAttr( 'disabled' );
              $replace.removeAttr( 'disabled' );
            }

            $feedback.html( response.data.feedback );

          } );
      }
    );

    // postmeta tools - cancel replace
    $( document ).on( 'click', 'button.wp-cleanfix-tools-postmeta-replace-cancel-button',
      function( e )
      {
        e.preventDefault();

        const $findButton = $( 'button.wp-clearfix-tools-postmeta-find-button' ),
              $feedback   = $( '#wp-clearfix-tools-postmeta-feedback' ),
              $column     = $( '#wp-cleanfix-tools-postmeta-column' ),
              $find       = $( '#wp-cleanfix-tools-postmeta-find' ),
              $replace    = $( '#wp-cleanfix-tools-postmeta-replace' );

        $findButton.removeAttr( 'disabled' );
        $column.removeAttr( 'disabled' );
        $find.removeAttr( 'disabled' );
        $replace.removeAttr( 'disabled' );

        $feedback.html( '' );
      }
    );

    // postmeta tools - continue/replace
    $( document ).on( 'click', 'button.wp-cleanfix-tools-postmeta-replace-button',
      function( e )
      {
        e.preventDefault();

        const $this     = $( this ),
              $feedback = $( '#wp-clearfix-tools-postmeta-feedback' );

        if( !confirm( $this.data( 'confirm' ) ) ) {
          return false;
        }

        const payload = {
          action  : "wp_cleanfix_tools_postmeta_replace",
          column  : $( '#wp-cleanfix-tools-postmeta-column' ).val(),
          find    : $( '#wp-cleanfix-tools-postmeta-find' ).val(),
          replace : $( '#wp-cleanfix-tools-postmeta-replace' ).val()
        };

        // display loading
        const $loading = $( '<img class="loading" src="/wp-admin/images/loading.gif"/>' );

        $this.attr( 'disabled', 'disabled' );

        $feedback.html( $loading );

        // loggedin
        $.post(
          ajaxurl,
          payload,
          function( response )
          {
            $this.removeAttr( 'disabled' );

            $feedback.html( response.data.feedback );

          } );
      }
    );


  }

  boot();

})( jQuery );