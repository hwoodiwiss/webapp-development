CM = {};

CM.Alert = function(Heading, Content, Type)
{
	if ($('#MasterAlerts').length > 0)
	{
		var alert = $('<div class="alert alert-' + Type + ' alert-dismissable" role="alert">')
			.append('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>')
			.append($('<strong>').text(Heading))
			.append($('<p class="text-' + Type + ' small">').text(Content));
		$('#MasterAlerts').append(alert);
	}
}

$(function(){

	$('collapse-trigger').click(function(){
		var target = $(this).data('target');
		$(target).collapse('toggle');
	});

	$('form.async').data('prevent', true);

	$('form.async').submit(function(e){
		var DoPrevent = $('form.async').data('prevent');
		var CurrForm = $(this)
		var Method = CurrForm.attr('method');
		var Endpoint = CurrForm.attr('action');
		var OnSuccessStr = CurrForm.attr('onsuccess');
		var OnSuccess = new Function('data', OnSuccessStr);
		var FormData = CurrForm.serialize();
		var AjaxOptions = {
			url: Endpoint,
			data: FormData,
			method: Method
		};
		$.ajax(AjaxOptions)
		.done(function(data, textStatus, jqXHR){
			if(typeof OnSuccess === 'function')
			{
				var jsData = $.parseJSON(data);
				OnSuccess(jsData);
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown)
		{
			//Fall back to synchronous submission if submission fails
			CurrForm.data('prevent', false);
			CurrForm.submit();
		});

		if(DoPrevent) e.preventDefault();
	});

	$('tr[data-toggle="collapse"] > td > button').click(function(e){
		e.stopPropagation();
	});
	
	$('tr[data-toggle="collapse"] > td > a').click(function(e){
    	e.stopPropagation();
	});
});