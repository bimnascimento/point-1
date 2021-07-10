jQuery(document).ready(function($)
	{


		$(document).on('click', '.uv-expandable .header .expand-collapse', function()
			{
				if($(this).parent().parent().hasClass('active'))
					{
						$(this).parent().parent().removeClass('active');
					}
				else
					{
						$(this).parent().parent().addClass('active');	
					}
				
			
			})	


		$(document).on('click', '.uv-emails-templates .reset-email-templates', function()
			{

				if(confirm("Do you really want to reset ?")){
					
					$.ajax(
						{
					type: 'POST',
					context: this,
					url:qa_ajax.qa_ajaxurl,
					data: {"action": "user_verification_reset_email_templates", },
					success: function(data)
							{	
							
								$(this).val('Reset Done');
							
								location.reload();
							}
						});
					
					}

				})




	});	







