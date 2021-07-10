!function($){
	
	var timer,
		ignore = false,
		vc_loading_shortcode = false,
		working = false,
		loaded = false;
	
	var $button = $();
	
	String.prototype.contains = function(needle) {
		return this.indexOf(needle) >= 0;
	};
	
	// Helpers
	function is_vc_loading_shortcode(settings) {
		var data = settings.data;
		var data = decodeURIComponent(data);
		return (settings.type=='POST' && data.contains('action=vc_load_shortcode&'));
	}
	function is_vc_saving_post(settings) {
		var data = settings.data;
		var data = decodeURIComponent(data);
		return (settings.type=='POST' && data.contains('&action=editpost&'));
	}
	function is_vc_ready() {
		return (vc.app && (vc.app.status == "shown" || is_vc_frontend() ));
	}
	function is_vc_frontend() {
		return vc_mode == "admin_frontend_editor";
	}
	function get_vc_content() {
		return vc.shortcodes.stringify();
	}
	function get_tinymce() {
		return window.tinymce.editors.content;
	}
	function setWorking() {
		working = true;
		$button.addClass('working');
	}
	function unsetWorking() {
		working = false;
		$button.removeClass('working');
	}
	function vc_errorMessage(msg) {
		vc.showMessage(msg);
		$('body > .vc_message').removeClass('success').addClass('danger');
	}
	
	// Main functions
	function vc_unredo_setup(){
		
		// loading multiple times
		if( loaded ) return false;
		loaded = true;
		
		// Get post data
		var post_id = $('#post_ID').val();
		var content = vc.getContent();
		
		// Setup undoManager
		var umCallback = function(content, go){
			ignore = true;
			
			if( vc_mode == "admin_page" ) {
				vc.storage.setContent(content);
				vc.app.show();
				return true;
			} else {
				setWorking();
				
				$.ajax({
					type: 'post',
					url: ajaxurl,
					data: {
						action: 'get_editable_content',
						post_id: post_id,
						content: content
					},
					error: function(error){
						vc.errorMessage("Something gone wrong while restoring content");
						unsetWorking();
					},
					success: function(r) {
						if( r.post_content && r.post_shortcodes ) {
							vc.shortcodes.reset();
							
							vc.frame_window.jQuery( '#vc_template-post-content' ).html(r.post_content);
							
							vc.post_shortcodes = JSON.parse(decodeURIComponent( r.post_shortcodes ));
							vc.frame_window.vc_post_shortcodes = vc.post_shortcodes;
							
							vc.builder.buildFromContent();
							vc.showMessage("Content restored");
							go();
						} else {
							vc.errorMessage("Something gone wrong while restoring content");
						}
						unsetWorking();
					}
				});
				return false;
			}
		};
		var um = window.undoManager = new undoManager({
			key : post_id,
			fallback : content,
			callback : umCallback
		});
		
		// Add clear button
		$panel = vc.post_settings_view.$el;
		
		if( $panel.hasClass('vc_ui-panel-window') ) { // version 4.7 and later
			$clear = $('<span id="vc_urclear" class="vc_general vc_ui-button vc_ui-button-danger vc_ui-button-shape-rounded vc_pull-right" data-vc-ui-element="button-close">Clear History</span>');
			$clear.appendTo( $panel.find('.vc_ui-panel-footer > .vc_ui-button-group') );
		} else { // version 4.6 and bellow
			$clear = $('<button data-dismiss="panel" id="vc_urclear" class="vc_btn vc_btn-danger vc_pull-right" type="button">Clear History</button>');
			$clear.appendTo( $panel.children('.vc_panel-footer') );
		}
		
		// Create buttons objects
		var $undo = $("#vc_undo");
		var $redo = $("#vc_redo");
		
		// Setup buttons events
		$undo.on('click', function(e){
			if( working ) return;
			$button = $(this);
			um.undo();
		});
		$redo.on('click', function(e){
			if( working ) return;
			$button = $(this);
			um.redo();
		});
		$clear.on('click', function(e){
			$button = $(this);
			um.clear( vc.getContent() );
		});
		
		// Buttons events shortcuts
		$([window,vc.frame_window]).keydown(function(e){
			if(vc.activePanelName()) return true;
			
			if( e.ctrlKey && e.which === 89 ){
				$redo.trigger('click');
			}
			
			if( e.ctrlKey && e.which === 90 ){
				$undo.trigger('click');
			}
		});
		
		// Frontend Ajax events
		if( is_vc_frontend() ) {
			$(document).ajaxSend(function(e, xhr, settings){
				if( is_vc_loading_shortcode(settings) ) {
					vc_loading_shortcode = true;
				}
			});
			$(document).ajaxComplete(function(e, xhr, settings){
				if( is_vc_loading_shortcode(settings) ) {
					vc_loading_shortcode = false;
				}
				if( is_vc_saving_post(settings) ) {
					um.save();
				}
			});
		}
		
		// Backend save
		$('#post').on('submit',function(){
			um.save();
		});
		
		// Check if content changed
		setInterval(function(){
			if( is_vc_ready() ) {
				
				// Check if content has been modified
				if( !vc_loading_shortcode && content != vc.getContent() ) {
					content = vc.getContent();
					if( ignore ) {
						// Content changed from a restore point
						ignore = false;
					} else {
						// Add restore point
						um.add(content);
					}
				}
				
				// Check if we can undo changes
				if( um.hasUndo() ) {
					$undo.removeClass('disabled');
				} else {
					$undo.addClass('disabled');
				}
				
				// Check if we can redo changes
				if( um.hasRedo() ) {
					$redo.removeClass('disabled');
				} else {
					$redo.addClass('disabled');
				}
				
				// Check if we can clear history
				if( um.hasUndo() || um.hasRedo() ) {
					$clear.show();
				} else {
					$clear.hide();
				}
				
				// Change editors dirty check
				if( is_vc_frontend() ) {
					if( um.changed() ) {
						vc.setDataChanged();
					} else {
						vc.unsetDataChanged();
					}
				} else {
					if( get_tinymce() ) {
						get_tinymce().isNotDirty = !um.changed();
					}
				}
			}
		}, 100);
	};
	
	// Initialization
	$(window).ready(function(){
		timer = setInterval(function(){
			if( is_vc_ready() ) {
				vc.getContent = get_vc_content;
				vc.errorMessage = vc_errorMessage;
				
				setTimeout( vc_unredo_setup, 100 );
				clearInterval( timer );
			}
		}, 100);
	});
	
}( jQuery );