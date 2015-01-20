/**
 * This type of declaration will allow calls like
 * JSNTemplateUpgraderUtil.function()
 */
var JSNTemplateUpgraderUtil = {
	init: function() {},

	disableNextButtonOnSubmit: function(button, replaceText) {
		button.disabled = true;
		if (typeof replaceText !== "undefined")
		{
			button.set("html", replaceText);
		}
	},

	setNextButtonState: function(form, button) {
		var task          = form.task.value;
		var buttonDisable = true;

		if (typeof button === "undefined")
		{
			button = form.next_step_button;
		}

		switch (task)
		{
			case "edition_select":
				/* take care of the selection list */
				var edition = form.jsn_upgrade_edition.value;
				if (edition != '')
				{
					buttonDisable = false;
				}
				break;

			case "manual_upgrade":
				/* take care of the file selector */
				if (form.package.value != "")
				{
					buttonDisable = false;
				}
				break;

			default:
				/* take care of username password input boxes */
				var username = form.username.value;
				var password = form.password.value;
				if (username != '' && password != '')
				{
					buttonDisable = false;
				}
				break;
		}

		button.disabled = buttonDisable;
	}
};

var JSNTemplateUpgrader =  new Class({
	template_name: "",
	template_style_id: "",
	url: "",

	initialize: function(template_name, template_style_id, url) {
		this.template_name = template_name;
		this.template_style_id = template_style_id;
		this.url = url;
	},

	installTemplate: function() {
		var upgrade_template_icon     = $("jsn-upgrade-template");
		var upgrade_template_message  = $("jsn-upgrade-template-message");
		var upgrade_template_subtitle = $("jsn-upgrade-template-subtitle");

		upgrade_template_icon.addClass("jsn-icon-small-loader");

		var self = this;
		self.toggleCancelButton(false);
		var jsonRequest = new Request.JSON({
			url: this.url + "?template=" + this.template_name + "&tmpl=jsn_upgrade&task=ajax_install_pro&template_style_id=" + this.template_style_id + "&rand=" + Math.random(),
			onSuccess: function(jsonObj)
			{
				if (jsonObj.install)
				{
					upgrade_template_icon.removeClass("jsn-icon-small-loader");
					upgrade_template_icon.addClass("jsn-icon-small-successful");
					upgrade_template_subtitle.addClass("jsn-successful-subtitle");

					$("jsn-upgrade-succesfully-container").removeClass('jsn-updater-display-none');
					$("jsn-upgrade-finish-button-wrapper").removeClass('jsn-updater-display-none');
				}
				else
				{
					upgrade_template_message.set("html", jsonObj.message);
					upgrade_template_icon.removeClass("jsn-icon-small-loader");
					upgrade_template_icon.addClass("jsn-icon-small-error");
					upgrade_template_subtitle.removeClass("jsn-successful-subtitle");

					$("jsn-upgrade-finish-button").set("html", "Close");
					$("jsn-upgrade-finish-button-wrapper").removeClass("jsn-updater-display-none");
				}
			}
		}).post();
	},

	toggleCancelButton: function(toggle) {
		var element_cancel = $("jsn-upgrade-cancel");
		if (toggle)
		{
			element_cancel.removeClass("jsn-updater-display-none");
		}
		else
		{
			element_cancel.addClass("jsn-updater-display-none");
		}
	}
});
