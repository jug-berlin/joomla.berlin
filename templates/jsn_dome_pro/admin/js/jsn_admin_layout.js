!function ($) {
	"use strict";

	$.JSNAdminLayout = function (params)
	{
		this.params 			= params;
		this.container			= $('#options');
		this.leftColumn 		= $('<div/>', { id: 'jsn-column-left' });
		this.mainColumn 		= $('<div/>', { id: 'jsn-column-main', 'class': 'accordion' });
		this.mainWrapper 		= $('<div/>', { id: 'jsn-column-main-wrapper' });
		this.aboutSection		= this.container.find('div.jsn-about');

		this.mainWrapper
			.append(this.mainColumn);

		this.leftColumn
			.append(this.aboutSection);

		this.buildAccordion();
		this.container
			.empty()
			.append(this.mainWrapper)
			.append(this.leftColumn);
	}

	$.JSNAdminLayout.prototype = {
		buildAccordion: function () {
			this.accordionGroups = this.container.find('.accordion-group:gt(0)');
			this.mainColumn.append(this.accordionGroups);

			this.groupGettingStarted = $(this.accordionGroups[0]);
			this.groupGettingStarted.addClass('in');
			this.groupGettingStarted.find('.accordion-body').addClass('in');

			var quickStarted = this.groupGettingStarted.find('#jsn-quickstarted'),
				quickStartedInner = this.groupGettingStarted.find('.accordion-inner');
			
			quickStarted.detach();
			quickStartedInner
				.empty()
				.append(quickStarted);

			this.accordionGroups.find('fieldset.radio').each(function () {
				var fieldSet = $(this),
					parentContainer = fieldSet.parent();

				fieldSet.find('input').each(function () {
					var input = $(this),
						label = input.next();

					label
						.prepend(input)
						.addClass('radio inline')
						.appendTo(parentContainer);
				});

				fieldSet.remove();
			});

			this.accordionGroups.on('show', function (e) {
				$(e.target).closest('div.accordion-group').addClass('in');
			});

			this.accordionGroups.on('hidden', function (e) {
				$(e.target).closest('div.accordion-group').removeClass('in');
			});
		}
	}
}(jQuery)