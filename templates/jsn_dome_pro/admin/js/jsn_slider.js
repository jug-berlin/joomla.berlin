var JSNSlider = new Class({
	initialize: function() {
		/* Exclude about section from being included in slider */
		var about_section = $(document.body).getElements('.pane-sliders').getElement('.panel');
		about_section.set('class', 'jsn-pane-slider-standalone');
		about_section.getElement('h3').set('class', 'jsn-h3-standalone').set('style', 'display: none;');
		about_section.getElement('div').set('class', 'jsn-inner-div-standalone');
		
		// Dispose the element to be injected later
		var removed_about = about_section.dispose();

		$$('.pane-sliders').set('class', 'jsn-pane-sliders').set('id', 'content-pane');
		var controller_holder 		= new Element('div', {'class': 'jsn-controller'});
		var controller_holder_a1 	= new Element('a', {id: 'expand-all', href: 'javascript:void(0);', html: templateLang.expand_all});
		var controller_holder_a2 	= new Element('a', {id: 'collapse-all', href: 'javascript:void(0);', html: templateLang.collapse_all});
		var controller_holder_a3 	= new Element('a', {id: 'separator', html: '|'});
		var fieldset_wrapper 	 	= new Element('fieldset', {'style': 'display: block', 'class': 'adminform', html: ''});
		var fieldset_wrapper_legend = new Element('legend', {html: 'Parameters'});
		$$('.width-40').set('class', 'width-50 fltrt').set('id', 'jsn');
		$$('.width-60').set('class', 'width-50 fltlft');
		
		var panel 		= $(document.body).getElements('div.panel');
		var h3 			= $(document.body).getElements('.pane-toggler');
		var div_inner 	= $(document.body).getElements('.pane-slider');

		Array.each(panel, function(item) {
			item.set('class', 'jsn-panel');
		});
		Array.each(h3, function(item) {
			item.set('class', 'title jsn-pane-toggler');
			var h3_a 		= item.getElement('a');
			var child_span 	= h3_a.getElement('span');
			var span1 		= new Element('span', {'class': 'toggle-arrow'});
			var span2 		= new Element('span', {'class': 'param-icon'});
			child_span.inject(span2.inject(span1.inject(item)));
			if(child_span.get('html').contains('(PRO)')) {
				child_span.set({
					'html':  child_span.get('html').replace(/\(PRO\)/, ""),
					'class': 'pro'
				});
			}
			h3_a.dispose();
		});
		Array.each(div_inner, function(item) {
			item.set('class', 'jsn-pane-slider content');
		});
		controller_holder_a1.inject(controller_holder);
		controller_holder_a3.inject(controller_holder);
		controller_holder_a2.inject(controller_holder);
		
		$('content-pane').inject($('jsn'));
		$('jsn').set('html', '<fieldset class="adminform"><legend>Parameters</legend><div class="jsn-pane-sliders" id="content-pane">'+ $('content-pane').get('html') +'</div></fieldset>');	
		controller_holder.inject($('content-pane'), 'top');
		removed_about.inject($('content-pane'), 'top');
	}
});
window.addEvent('domready', function() {var jsnSlider = new JSNSlider();});