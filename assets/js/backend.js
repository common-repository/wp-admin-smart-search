var wpasTimeout = null;

jQuery( document ).ready( function() {
	jQuery( '.wpas_content' ).perfectScrollbar( {suppressScrollX: true, theme: 'wpas'} );

	jQuery( 'body' ).bind( 'click tap', function( e ) {
		if ( (
			     jQuery( e.target ).closest( '.wpas_sidebar' ).length === 0
		     ) && (
			     jQuery( e.target ).closest( '.wpas_menu' ).length === 0
		     ) ) {
			wpas_hide_box();
		}
	} );

	jQuery( '.wpas_close_btn' ).on( 'click tap', function() {
		wpas_hide_box();
	} );

	jQuery( '.wpas_settings_btn' ).on( 'click tap', function() {
		jQuery( '.wpas_content .wpas_settings' ).toggle();
	} );

	jQuery( '.wpas_menu' ).on( 'click tap', function() {
		wpas_toggle_box();
	} );

	jQuery( document ).on( 'keydown', function( e ) {
		if ( e.ctrlKey && (
				String.fromCharCode( e.which ).toLowerCase() === 'm'
			) ) {
			wpas_toggle_box();
		}
	} );

	jQuery( '#wpas_search_input' ).keyup( function() {
		jQuery( '.wpas_search_result' ).html( '' );
		var wpas_search = jQuery( '#wpas_search_input' ).val().trim();
		if ( wpas_search != '' ) {
			jQuery( '.wpas_search_result' ).html( '<div class="line"><span class="main">Searching...</span></div>' );
			if ( wpasTimeout != null ) {
				clearTimeout( wpasTimeout );
			}
			wpasTimeout = setTimeout( wpas_ajax_get_data, 300 );
			return false;
		} else {
			jQuery( '.wpas_search_result' ).html( '' );
		}
	} );

	jQuery( '.wpas_widget_header' ).on( 'click', function() {
		jQuery( this ).parent().find( '.wpas_widget_content' ).parent().toggleClass( 'wpas_widget_close' );
	} );

	jQuery( '.wpas_quick_setting' ).on( 'change', function() {
		var setting = jQuery( this ).data( 'for' );
		if ( jQuery( this ).is( ":checked" ) ) {
			wpas_quick_setting( setting, 'yes' );
		} else {
			wpas_quick_setting( setting, 'no' );
		}
	} );
} );

function wpas_quick_setting( setting, value ) {
	data = {
		action: 'wpas_quick_setting',
		setting: setting,
		value: value,
		wpas_nonce: wpas_vars.wpas_nonce
	};
	jQuery.post( wpas_vars.ajax_url, data, function( response ) {
		console.log( 'changed: ' + setting + '/' + value );
	} );
}

function wpas_toggle_box() {
	if ( jQuery( '.wpas_box' ).hasClass( 'open' ) ) {
		wpas_hide_box();
	} else {
		wpas_show_box();
	}
}

function wpas_show_box() {
	jQuery( '.wpas_box' ).addClass( 'open' );
	setTimeout( function() {
		jQuery( '#wpas_search_input' ).focus()
	}, 1000 );
}

function wpas_hide_box() {
	jQuery( '.wpas_box' ).removeClass( 'open' );
}

function wpas_ajax_get_data() {
	wpasTimeout = null;
	data = {
		action: 'wpas_ajax',
		key: jQuery( '#wpas_search_input' ).val(),
		wpas_nonce: wpas_vars.wpas_nonce
	};
	jQuery.post( wpas_vars.ajax_url, data, function( response ) {
		jQuery( '.wpas_search_result' ).html( response );
	} );
}