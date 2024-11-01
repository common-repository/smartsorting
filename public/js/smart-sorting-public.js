var first, products, pluginPath;
first = 0;
products = new Map();

(function( $ ) {
	var current;
	$( document ).scroll( function () {
		current = getFirst();
		if ( null !== current ) {
			if( first !== current ) {
				first = current;
				products = getProducts();
			}
			products.forEach(( value, key ) => checkPosition( key, $ ));
		}
	} );
	$( window ).resize( function () {
		current = getFirst();
		if ( null !== current ) {
			if( first !== current ) {
				first = current;
				products = getProducts();
			}
			products.forEach(( value, key ) => checkPosition( key, $ ));
		}
	} );

	current = getFirst();
	first = current;
	if ( null !== current ) {
		products = getProducts();
		products.forEach(( value, key ) => checkPosition( key, $ ));
	}

})( jQuery );

function checkPosition( element, $ ) {
	var elementClass = '.post-' + element,
		divPosition = $( elementClass ).offset(),
		divTop = divPosition.top,
		divLeft = divPosition.left,
		divWidth = $( elementClass ).width(),
		divHeight = $( elementClass ).height(),
		topScroll = $( document ).scrollTop(),
		leftScroll = $( document ).scrollLeft(),
		screenWidth = $( window ).width(),
		screenHeight = $( window ).height(),
		seeX1 = leftScroll,
		seeX2 = screenWidth + leftScroll,
		seeY1 = topScroll,
		seeY2 = screenHeight + topScroll,
		divX1 = divLeft,
		divX2 = divLeft + divWidth,
		divY1 = divTop,
		divY2 = divTop + divHeight;

	if ( divX1 >= seeX1 && divX2 <= seeX2 && divY1 >= seeY1 && divY2 <= seeY2 ) {
		if ( ! products.get( element ) ) {
			products.set( element, true );
			$.post( ajax_obj.ajax_url, {
				_ajax_nonce: ajax_obj.nonce,
				action: "add_view",
				productId: element
				}
			);
		}
	}
}

function getFirst() {
	var startNum, endNum, postId,
		card = document.querySelector( '.product' );
	if ( null == card ) {
		return null;
	}
	startNum = card.className.indexOf( 'post-' ) + 5;
	endNum = card.className.indexOf( ' ', startNum );
	postId = card.className.substring( startNum, endNum );
	return parseInt( postId );
}

function getProducts() {
	var startNum, endNum, postId,
		elements = new Map(),
		cards = document.querySelectorAll( '.product' );
	cards.forEach(card => {
		startNum = card.className.indexOf( 'post-' ) + 5;
		endNum = card.className.indexOf( ' ', startNum );
		postId = card.className.substring( startNum, endNum );
		elements.set( parseInt( postId ), false );
	})
	return elements;
}
