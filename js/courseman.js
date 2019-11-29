$(function(){

	$('collapse-trigger').click(function(){
		var target = $(this).data('target');
		$(target).collapse('toggle');
	});
});