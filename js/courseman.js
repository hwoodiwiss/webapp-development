//CourseMan app object that contains CourseMan functions
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

CM.ReloadAsyncPanel = function(Id, Target, Data)
{
	if($('#' + Id).length > 0)
	{

		$.get(Target, Data)
		.done(function(data)
		{
			$('#' + Id).empty();
			$('#' + Id).append(data);
			ApplyHandlers('#' + Id);
		});
	}
}

function ApplyHandlers(scope)
{

	$(scope).find('collapse-trigger').click(function(){
		var target = $(this).data('target');
		$(target).collapse('toggle');
	});

	$(scope).find('div.async-panel').each(function(index){
		var curr = $(this);
		CM.ReloadAsyncPanel(curr.attr('id'), curr.data('target'), curr.data('id'));
	});
	

	$(scope).find('form').not('.no-validate').validate();

	$(scope).find('form.async').data('prevent', true);

	$(scope).find('form.async').submit(function(e){
		var DoPrevent = $('form.async').data('prevent');
		var CurrForm = $(this)
		var Confirm = CurrForm.data('confirm');
		if(Confirm != undefined)
		{
			var isConfirmed = confirm(Confirm);
			if(!isConfirmed)
			{
				e.preventDefault();
				return;
			}
		}
		var Method = CurrForm.attr('method');
		var Endpoint = CurrForm.attr('action');
		var OnSuccessStr = CurrForm.attr('onsuccess');
		var OnFailStr = CurrForm.attr('onfail');
		var OnSuccess = new Function(['data', 'form'], OnSuccessStr);
		var OnFail = new Function(['data', 'form'], OnFailStr);
		var FormData = CurrForm.serialize();
		var AjaxOptions = {
			url: Endpoint,
			data: FormData,
			method: Method
		};
		$.ajax(AjaxOptions)
		.done(function(data, textStatus, jqXHR){
			var jsData = $.parseJSON(data);
			if(jsData.success == true)
			{
				if(typeof OnSuccess === 'function')
				{
					OnSuccess(jsData.data, CurrForm);
				}
			}
			else
			{
				if(typeof OnFail === 'function')
				{
					OnFail(jsData.data);
				}
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

	$(scope).find('tr[data-toggle="collapse"] > td > button').click(function(e){
		e.stopPropagation();
	});
	
	$(scope).find('tr[data-toggle="collapse"] > td > a').click(function(e){
    	e.stopPropagation();
	});

};

function ReloadContainingPanel(jqThis)
{
	var curr = jqThis.parents('div.async-panel');

	CM.ReloadAsyncPanel(curr.attr('id'), curr.data('target'), curr.data('id'));
}

$(document).ready(function(){
	ApplyHandlers('body');
});