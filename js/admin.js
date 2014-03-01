jQuery(document).ready(function($) {
	// Click a template tag
	$('ul.tags li a').click(function(e) {
		e.preventDefault();
		var templateTag = $(this).text();
		var inputElement = $('input[type=text],textarea', $(this).parent().parent().parent());
		$(inputElement).val($(inputElement).val()+templateTag);
	});
	
	// Conditional Fields
	$('input[type=checkbox]').each(function() {
		var condition = $(this).data('condition');
		var checked = $(this).data('checked');
		
		if (typeof condition != 'undefined') {
			if ($(this).prop('checked')) {
				if (checked == 'hide') {
					$('div.'+condition).hide();
				} else {
					$('div.'+condition).show();
				}
			} else {
				if (checked == 'hide') {
					$('div.'+condition).show();
				} else {
					$('div.'+condition).hide();
				}	
			}
		}
	});
	$('input[type=checkbox]').change(function() {
		var condition = $(this).data('condition');
		var checked = $(this).data('checked');
		
		if (typeof condition != 'undefined') {
			if ($(this).prop('checked')) {
				if (checked == 'hide') {
					$('div.'+condition).hide();
				} else {
					$('div.'+condition).show();
				}
			} else {
				if (checked == 'hide') {
					$('div.'+condition).show();
				} else {
					$('div.'+condition).hide();
				}	
			}
		}		
	});
	
	// Save, Test + Generate
	$('form#page-generator-generate input[type=submit]').click(function(e) {
		var action = $(this).data('action');
		switch (action) {
			case 'test':
				var result = confirm('This will save your settings and generate a single Page in draft mode. Proceed?');
				if (!result) {
					e.preventDefault();
					return false;
				}
				break;
			case 'generate':
				var result = confirm('This will save your settings and generate Pages. Proceed?');
				if (!result) {
					e.preventDefault();
					return false;
				}
				break;
		}
	});
});