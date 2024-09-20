// JavaScript Document
$( function() {
	$( "#menu" ).menu();
} );

function AddFilter(product_id, filter_value_id){
	$.ajax({
		type: "POST",
		url: "products_filters.add.php",
		data: { pID: product_id, vID: filter_value_id }
	})
	.done(function( msg ) {
		if( msg != "" ){
			$("#filters").append('<span id="btn_' + filter_value_id + '" class="btn btn-primary">' + msg + ' <span onclick="RemoveFilter(' + product_id + ', ' + filter_value_id + ');" class="badge">X</span></span>');
		}
	});
}

function RemoveFilter(product_id, filter_value_id){
	$('#btn_' + filter_value_id ).remove();
	$.ajax({
		type: "POST",
		url: "products_filters.delete.php",
		data: { pID: product_id, vID: filter_value_id }
	});
}