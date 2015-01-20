/**
 *
 */
var JSNUpdater = new Class({
	options : {},
	initialize: function(options) {
		this.options = Object.merge(this.options, options);
	},
	backupModifiedFile: function(template_name, extract_dir, url, style_id, auto, file_name) {
		var icon_element = $('jsn-create-modified-list');
		var title_element = $('jsn-create-modified-list-subtitle');
		icon_element.addClass('jsn-icon-small-loader');
		var self = this;
		var jsonRequest = new Request.JSON({
			url: url,
			onSuccess: function(jsonObj){
				if (jsonObj.backup)
				{
					icon_element.removeClass('jsn-icon-small-loader');
					icon_element.addClass('jsn-icon-small-successful');
					title_element.addClass('jsn-successful-subtitle');
					var backup_file = jsonObj.backup_file_name;
					var update_template_li_element 	= $('jsn-update-template-li');
					update_template_li_element.removeClass('jsn-updater-display-none');
					if (!auto)
					{
						self.manualUpdateTemplate(template_name, extract_dir, url, style_id, backup_file, file_name);
					}
					else
					{
						self.autoUpdateTemplate(template_name, extract_dir, url, style_id, backup_file);
					}
				}
				else
				{
					icon_element.removeClass('jsn-icon-small-loader');
					icon_element.addClass('jsn-icon-small-error');
					title_element.removeClass('jsn-successful-subtitle');
					self.toggleCancelButton(true);
				}
		}}).get({'template': template_name, 'tmpl': 'jsn_runajax', 'task': 'backupModifiedFile', 'template_style_id': style_id, 'rand': Math.random()});
	},
	manualUpdateTemplate: function(template_name, extract_dir, url, style_id, backup_file, file_name) {
		var update_succesfully_container 	= $('jsn-update-succesfully-container');
		var update_succesfully_wrapper 		= $('jsn-update-succesfully-wrapper');
		var attention_container 			= $('text-alert-attention');
		var title_element 					= $('jsn-update-template-subtitle');
		var icon_element 					= $('jsn-update-template');
		var modifiled_file_name_element		= $('modified_file_name');
		var from_version_to_version_element = $('jsn-from-version-to-version');
		var self = this;
		icon_element.addClass('jsn-icon-small-loader');
		var jsonRequest = new Request.JSON({
			url: url,
			onSuccess: function(jsonObj){
				from_version_to_version_element.set('html', '(' + jsonObj.from_version + ' -&gt; ' + jsonObj.to_version + ')');
				if (jsonObj.update)
				{
					update_succesfully_container.removeClass('jsn-updater-display-none');
					icon_element.removeClass('jsn-icon-small-loader');
					icon_element.addClass('jsn-icon-small-successful');
					title_element.addClass('jsn-successful-subtitle');
					update_succesfully_wrapper.removeClass('jsn-updater-display-none');
					if (jsonObj.backup_file_name !== '')
					{
						attention_container.removeClass('jsn-updater-display-none');
						modifiled_file_name_element.value = jsonObj.backup_file_name;
					}
				}
				else
				{
					if (jsonObj.redirect)
					{
						var link = url + 'index.php?template=' + template_name + '&tmpl=jsn_updaternotification&template_style_id=' + style_id + '&backup_file=' + backup_file + '&package_name=' + file_name + '&type=manual';
						window.location = link;
						return;
					}

					$('jsn-download-package-message').set('html', jsonObj.message);
					icon_element.removeClass('jsn-icon-small-loader');
					icon_element.addClass('jsn-icon-small-error');
					title_element.removeClass('jsn-successful-subtitle');
					self.toggleCancelButton(true);
				}

		}}).get({'template': template_name, 'tmpl': 'jsn_runajax', 'task': 'manualUpdateTemplate', 'template_style_id': style_id, 'extract_dir': extract_dir, 'backup_file': backup_file, 'package_name': file_name, 'rand': Math.random()});
	},

	dowloadTemplatePackage: function(template_name, url, style_id) {

		var download_package_icon_element 		= $('jsn-download-package');
		var download_package_subtitle_element 	= $('jsn-download-package-subtitle');
		var download_package_message_element 	= $('jsn-download-package-message');

		var create_modified_list_li_element 	= $('jsn-create-modified-list-li');
		var download_package_manual_update_element 	= $('jsn-download-package-manual-update');
		var progress = $('jsn-download-package-progress'),
			progressBar = progress.getElement('div.bar'),
			progressPercentage = progress.getElement('span.percentage');

		download_package_icon_element.addClass('jsn-icon-small-loader');
		var self = this;
		var initialRequest = new Request.JSON({
			url: url + '?template=' + template_name + '&tmpl=jsn_runajax&task=initialDownloadTemplatePackage&template_style_id=' + style_id + '&rand=' + Math.random(),
			onSuccess: function (response) {
				var downloader  = new JSNHttpDownload({
					url: joomlaTemplateUrl + '/jsn_downloader.php',
					process: response.key,
					progress: function (size, downloaded, percent, speed) {
						progressBar.setStyle('width', percent + '%');
						progressPercentage.innerHTML = percent + '%';
					},

					complete: function (success) {
						if (success)
						{
							progressBar.setStyle('width', '100%');
							progressPercentage.innerHTML = '100%';

							download_package_icon_element.removeClass('jsn-icon-small-loader');
							download_package_icon_element.addClass('jsn-icon-small-successful');
							download_package_subtitle_element.addClass('jsn-successful-subtitle');
							create_modified_list_li_element.removeClass('jsn-updater-display-none');
							self.backupModifiedFile(template_name, response.key + '.zip', url, style_id, true, '');
						}
						else
						{
							download_package_icon_element.removeClass('jsn-icon-small-loader');
							download_package_icon_element.addClass('jsn-icon-small-error');
							download_package_message_element.set('html', jsonObj.message);
							if (jsonObj.manual)
							{
								download_package_manual_update_element.removeClass('jsn-updater-display-none');
							}
							self.toggleCancelButton(true);
						}
					}
				});

				downloader.start({
					key    : response.key,
					action : 'download'
				});

				progress.fade('in');
			}
		});

		initialRequest.post(self.options);
	},

	autoUpdateTemplate: function(template_name, package_name, url, style_id, backup_file) {
		var update_template_icon		 			= $('jsn-update-template');
		var download_package_manual_update_element 	= $('jsn-download-package-manual-update');
		var update_template_message_element 		= $('jsn-update-template-message');
		var update_template_subtitle_element 		= $('jsn-update-template-subtitle');
		var update_succesfully_container 			= $('jsn-update-succesfully-container');
		var update_succesfully_wrapper 				= $('jsn-update-succesfully-wrapper');
		var modifiled_file_name_element				= $('modified_file_name');
		var attention_container 					= $('text-alert-attention');
		update_template_icon.addClass('jsn-icon-small-loader');
		var self = this;
		var jsonRequest = new Request.JSON({
			url: url,
			onSuccess: function(jsonObj)
			{
				if (jsonObj.update)
				{
					update_template_icon.removeClass('jsn-icon-small-loader');
					update_template_icon.addClass('jsn-icon-small-successful');
					update_template_subtitle_element.addClass('jsn-successful-subtitle');
					update_succesfully_container.removeClass('jsn-updater-display-none');
					update_succesfully_wrapper.removeClass('jsn-updater-display-none');
					if (jsonObj.backup_file_name !== '')
					{
						attention_container.removeClass('jsn-updater-display-none');
						modifiled_file_name_element.value = jsonObj.backup_file_name;
					}
				}
				else
				{
					if (jsonObj.redirect)
					{
						var link = url + '?template=' + template_name + '&tmpl=jsn_updaternotification&template_style_id=' + style_id + '&backup_file=' + backup_file + '&package_name=' + package_name + '&type=auto';
						window.location = link;
						return;
					}

					update_template_message_element.set('html', jsonObj.message);
					update_template_icon.removeClass('jsn-icon-small-loader');
					update_template_icon.addClass('jsn-icon-small-error');
					update_template_subtitle_element.removeClass('jsn-successful-subtitle');

					if (jsonObj.manual)
					{
						download_package_manual_update_element.removeClass('jsn-updater-display-none');
					}
					self.toggleCancelButton(true);
				}
			}
		}).get({'template': template_name, 'tmpl': 'jsn_runajax', 'task': 'autoUpdateTemplate', 'template_style_id': style_id, 'package_name': package_name, 'backup_file': backup_file, 'rand': Math.random()});
	},

	toggleCancelButton: function(toggle)
	{
		var element_cancel = $('jsn-updater-cancel');
		if (toggle)
		{
			element_cancel.removeClass('jsn-updater-display-none');
		}
		else
		{
			element_cancel.addClass('jsn-updater-display-none');
		}
	}
})

JSNUpdater.implement(new Options, new Events);
