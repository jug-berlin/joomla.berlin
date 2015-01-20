/**
* @author    JoomlaShine.com http://www.joomlashine.com
* @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
* @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
*/

var JSNSimpleLoadingStatus = {
	start: function(text_span) {
		var textArray = ["...still in progress", "...please wait"];
		var count = -1;

		this.textEl = text_span;
		this.textEl.removeClass("jsn-installsample-non-display")
			.addClass("jsn-installsample-display-inline");

		this.timer = setInterval(
			function() {
				text_span.set("html", textArray[++count % textArray.length]);
			}, 5000);
		return this;
	},

	clear: function() {
		clearInterval(this.timer);
		this.textEl.set("html", "")
			.removeClass("jsn-installsample-display-inline")
			.addClass("jsn-installsample-non-display");
	}
};

var JSNSampleData = {

	phpTimeoutMessage: "Error executing PHP process. The script might reach maximum execution time limit!",

	init: function() {},

	start: function(templateName, url, styleId)
	{
		$('jsn-installing-sampledata').addClass('jsn-installsample-display-block');

		JSNSampleData.downloadPackage(templateName, url, styleId);
	},

	downloadPackage: function(templateName, url, styleId)
	{
		/* Show loading icon */
		var stepLi = $("jsn-download-sample-data-package-title"),
			spanTitle = stepLi.getChildren("span.jsn-step-subtitle"),
			spanState = stepLi.getChildren("span.jsn-step-state-indicator"),
			progress = $('jsn-download-sampledata-progress'),
			progressBar = progress.getElement('div.bar'),
			progressPercentage = progress.getElement('span.percentage');

		spanState
			.addClass("jsn-sampledata-icon-small-loader")
			.addClass("jsn-installsample-display-inline");

		new Request.JSON({
			url: url,
			onSuccess: function (response) {
				var downloader  = new JSNHttpDownload({
					url: joomlaTemplateUrl + '/jsn_downloader.php',
					process: response.key,
					progress: function (size, downloaded, percent, speed) {
						progressBar.setStyle('width', percent + '%');
						progressPercentage.innerHTML = percent + '%';
					},

					complete: function (success) {
						if (success == true) {
							progressBar.setStyle('width', '100%');
							progressPercentage.innerHTML = '100%';

							setTimeout(function () {
								progress.fade('out');
							}, 1000);

							new Request.JSON({
								url: 'index.php?',
								onSuccess: function (jsonObj) {
									if(jsonObj.download) {
										/* Show success icon, strikethrough the text */
										spanTitle.addClass("jsn-successful-subtitle");
										spanState.removeClass("jsn-sampledata-icon-small-loader").addClass("jsn-sampledata-icon-small-successful");

										/* Tell user to choose which extensions to be installed */
										if (jsonObj.hasInstallation === true) {
											/* Show the selection section */
											$("jsn-extension-selection").removeClass("jsn-installsample-non-display");

											var extSelectionHtml = JSNSampleData.populateExtSelection(jsonObj.exts);
											$("jsn-extension-list").set("html", extSelectionHtml);

											$("jsn-install-continue-button").addEvent("click", function(e) {
												e.preventDefault();
												JSNSampleData.selectExtensions(templateName, url, styleId);
											});
										}
										else {
											/* Go directly to install sample data */
											JSNSampleData.installSampleData(templateName, jsonObj.sampleDataFile, url);
										}
									}
									else {
										/* Show error icon, error message */
										spanState.removeClass("jsn-sampledata-icon-small-loader").addClass("jsn-sampledata-icon-small-error");
										stepLi.getChildren("span.jsn-step-message")
											.set("html", jsonObj.message)
											.addClass("jsn-error-message")
											.addClass("jsn-installsample-display-inline");

										$("jsn-install-cancel").addClass("jsn-installsample-display-block");

										if (!jsonObj.connection) {
											$("jsn-install-sample-data-manually-inline").addClass("jsn-installsample-display-block");
										}
									}
								}
							})
							.get({"template": templateName, "tmpl": "jsn_runajax", "task": "getInstallableExtensions", "key": response.key, "template_style_id": styleId});
						}
					}
				});

				downloader.start({
					key    : response.key,
					action : 'download'
				});

				progress.fade('in');
			}
		})
		.get({
			"template": templateName, 
			"tmpl": "jsn_runajax", 
			"task": "initialDownloadSampleData", 
			"template_style_id": styleId
		});
	},

	selectExtensions: function(templateName, url, styleId)
	{
		var exts = $$("input.jsn-ext-list:checked").get('value');
		if (exts.length === 0) {
			exts = [];
		}

		var jsonRequest = new Request.JSON(
		{
			url: url,
			onSuccess: function(jsonObj)
			{
				/* Hide the selection section */
				$("jsn-extension-selection").addClass("jsn-installsample-non-display");

				if (jsonObj.exts && Object.getLength(jsonObj.exts) > 0)
				{
					var listHtml = "<ul>";

					Object.each(jsonObj.exts, function(item, key)
					{
						listHtml += JSNSampleData.populateExtListItem(key, item.desc);

						if (item.deps)
						{
							listHtml += "<ul>";
							Object.each(item.deps, function(item, key)
							{
								listHtml += JSNSampleData.populateExtListItem(key, item.desc);
								listHtml += "</li>";
							});
							listHtml += "</ul>";
						}

						listHtml += "</li>";
					});
					listHtml += "</ul>";

					$("jsn-install-extension-sublist").set("html", listHtml);

					/* Change to extension installation */
					$("jsn-install-extensions-title").addClass("jsn-installsample-display-list");
					JSNSampleData.installExtension(templateName, url, styleId, jsonObj.firstExt, jsonObj.childOf, jsonObj.isLastExt, jsonObj.sampleDataFile);
				}
				else
				{
					/* Go directly to install sample data */
					JSNSampleData.installSampleData(templateName, jsonObj.sampleDataFile, url);
				}
			}
		}).get({"template": templateName, "tmpl": "jsn_runajax", "task": "selectExtensions", "template_style_id": styleId, 'exts': exts});
	},

	installExtension: function(templateName, url, styleId, extId, childOf, isLastExt, sampleDataFile)
	{
		var self = this;
		var stepLi = $("jsn-install-extensions-title");

		/* First, get loading icon appears for currently-installed ext row */
		var subStepLi = $("jsn-install-extension-" + extId);
		var spanTitle = subStepLi.getChildren("span.jsn-step-subtitle");
		var spanState = subStepLi.getChildren("span.jsn-step-state-indicator");
		var spanProgress = subStepLi.getChildren("span.jsn-progress-message");

		spanState
			.addClass("jsn-sampledata-icon-small-loader")
			.addClass("jsn-installsample-display-inline");

		/* Disable cancel link */
		JSNSampleData.toggleCancelButton(false);

		/* Start the timer to display loop of "in-progress" messages */
		var loadingStatus = JSNSimpleLoadingStatus.start(spanProgress);

		new Request.JSON({
			url: url,
			onSuccess: function (response) {
				var container = $('jsn-install-extension-' + extId);
				var containerIndicator = container.getElement('span.jsn-step-state-indicator');
				var progress = container.getElement('.download-progress');
				var progressBar = progress.getElement('.bar');
				var progressPercentage = progress.getElement('.percentage');

				var downloader  = new JSNHttpDownload({
					url: joomlaTemplateUrl + '/jsn_downloader.php',
					process: response.key,
					progress: function (size, downloaded, percent, speed) {
						progressBar.setStyle('width', percent + '%');
						progressPercentage.innerHTML = percent + '%';
					},

					complete: function (success) {
						if (success == true) {
							progressBar.setStyle('width', '100%');
							progressPercentage.innerHTML = '100%';

							setTimeout(function () {
								progress.fade('out');
							}, 1000);

							/* Next, send request to install the extension to server */
							var request = new Request({
								url: url,
								method: "get",
								data: {"template": templateName, "tmpl": "jsn_runajax", "task": "requestInstallExtension", "ext_name": extId, "key": response.key},
								onComplete: function(requestInstallResponse)
								{
									var jsonObj = null;

									try {
										var jsonObj = JSON.decode(requestInstallResponse);

										if (jsonObj.installExt)
										{
											/* Show success icon, strikethrough the text */
											spanTitle.addClass("jsn-successful-subtitle");
											spanState
												.removeClass("jsn-sampledata-icon-small-loader")
												.addClass("jsn-sampledata-icon-small-successful");
										}
										else
										{
											/* Show error icon */
											spanState.removeClass("jsn-sampledata-icon-small-loader")
												.addClass("jsn-sampledata-icon-small-error");

											subStepLi.getChildren("span.jsn-step-message")
												.set("html", jsonObj.message)
												.addClass("jsn-error-message")
												.addClass("jsn-installsample-display-inline");
										}

										/* Process the next extensions for moving to next step */
										if (jsonObj.nextExt != "")
										{
											JSNSampleData.installExtension(templateName, url, styleId, jsonObj.nextExt, jsonObj.childOf, jsonObj.isLastExt, sampleDataFile);
										}
										else
										{
											// Strikethrough the "parent" title 
											stepLi.getChildren("span.jsn-step-subtitle")
												.addClass("jsn-successful-subtitle");

											JSNSampleData.installSampleData(templateName, sampleDataFile, url);
										}
									} catch (exception) {
										/* Send back an AJAX post to tell server that the ext installation has failed */
										var jsonRequest = new Request.JSON(
										{
											url: url,
											onSuccess: function(jsonResponse)
											{
												/* Show error icon */
												spanState.removeClass("jsn-sampledata-icon-small-loader")
													.addClass("jsn-sampledata-icon-small-error");

												subStepLi.getChildren("span.jsn-step-message")
													.set("html", self.phpTimeoutMessage)
													.addClass("jsn-error-message")
													.addClass("jsn-installsample-display-inline");

												/* Display error icon for non-succeeded exts */
												$("jsn-install-extension-sublist").getElements("span.jsn-step-state-indicator:not(.jsn-sampledata-icon-small-successful)")
													.addClass("jsn-sampledata-icon-small-error")
													.addClass("jsn-installsample-display-inline");

												/* Change to sample data installation immediately */
												JSNSampleData.installSampleData(templateName, sampleDataFile, url);
											}
										}).get({"template": templateName, "tmpl": "jsn_runajax", "task": "reportFailedExtension", "ext_name": extId});
									}
								}
							}).get();
						}
					}
				});

				downloader.start({
					key    : response.key,
					action : 'download'
				});

				progress.fade('in');
			}
		})
		.get({
			"template": templateName, 
			"tmpl": "jsn_runajax", 
			"task": "initialDownloadPackage",
			"template_style_id": styleId,
			"ext_name": extId
		});
	},

	installSampleData: function(templateName, file_name, url)
	{
		var self = this;

		/* Show loading icon */
		var stepLi = $("jsn-install-sample-data-package-title");
		stepLi.addClass("jsn-installsample-display-list");

		var spanTitle = stepLi.getChildren("span.jsn-step-subtitle");
		var spanState = stepLi.getChildren("span.jsn-step-state-indicator");
		spanState.addClass("jsn-sampledata-icon-small-loader")
			.addClass("jsn-installsample-display-inline");

		var jsonRequest = new Request.JSON(
		{
			url: url,
			onSuccess: function(jsonObj)
			{
				/* Show cancel link back again */
				JSNSampleData.toggleCancelButton(true);

				if(jsonObj.install)
				{
					/* Show success icon, strikethrough the text */
					spanTitle.addClass("jsn-successful-subtitle");
					spanState
						.removeClass("jsn-sampledata-icon-small-loader")
						.addClass("jsn-sampledata-icon-small-successful");

					/* Display green success message block */
					$("jsn-installing-sampledata-successfully").addClass("jsn-installsample-display-block");

					if (jsonObj.warnings.length)
					{
						$("jsn-warnings").addClass("jsn-installsample-display-block");
						JSNSampleData.renderWarning(jsonObj.warnings);
					}
				}
				else
				{
					/* Show error icon, error message */
					spanState.removeClass("jsn-sampledata-icon-small-loader").addClass("jsn-sampledata-icon-small-error");
					stepLi.getChildren("span.jsn-step-message")
						.set("html", jsonObj.message)
						.addClass("jsn-error-message")
						.addClass("jsn-installsample-display-inline");

					$("jsn-install-cancel").addClass("jsn-installsample-display-block");

					if (jsonObj.manual != undefined)
					{
						if (jsonObj.manual)
						{
							$("jsn-install-sample-data-manually-inline").addClass("jsn-installsample-display-block");
						}
					}
				}
			},
			onError: function()
			{
				/* Show error icon, error message */
				spanState.removeClass("jsn-sampledata-icon-small-loader").addClass("jsn-sampledata-icon-small-error");
				stepLi.getChildren("span.jsn-step-message")
					.set("html", self.phpTimeoutMessage)
					.addClass("jsn-error-message")
					.addClass("jsn-installsample-display-inline");
			}
		}).get({"template": templateName, "tmpl": "jsn_runajax", "task": "installSampleData", "file_name": file_name});
	},

	renderWarning: function(data)
	{
		var warnings = data;
		var count	 = warnings.length;
		var ul 		 = $("jsn-ul-warnings");
		if (count)
		{
			for(var i=0; i < count; i++)
			{
				var li = new Element("li", {html: warnings[i]});
				li.inject(ul);
			}
		}
	},

	setInstallationButtonState: function (form)
	{
		var username = form.username.value;
		var password = form.password.value;
		var agree = form.agree.checked;
		if (username != '' && password != '' && agree)
		{
			form.installation_button.disabled = false;
		}
		else
		{
			form.installation_button.disabled = true;
		}
	},

	setInlineInstallationButtonState:function(form)
	{
		if (form.install_package.value == "")
		{
			form.jsn_inline_install_manual_button.disabled = true;
		}
		else
		{
			form.jsn_inline_install_manual_button.disabled = false;
		}
	},

	populateExtSelection: function(exts)
	{
		var extSelectionHtml = '<ul>';
		Object.each(exts, function(item, key)
		{
			/* Only show item if it or its dependencies need to be installed */
			if (item.install === true || item.depInstall === true)
			{
				if (item.install === true)
				{
					extSelectionHtml += '<li><input class="jsn-ext-list" id="'+key+'" type="checkbox" name="installExts" value="'+key+'" onclick="JSNSampleData.toggleDepsCheck(this);" checked="checked" />';
					extSelectionHtml += '<label for="'+key+'">'+item.desc+'</label>';
				}
				else
				{
					/* No checkbox, no label */
					extSelectionHtml += '<li>'+item.desc;
				}

				if (item.productDesc)
				{
					/* Info icon to show long description */
					extSelectionHtml += '&nbsp;<a href="javascript:void();" class="link-action" onclick="JSNSampleData.toggleProductDesc(this, \''+key+'\');">[+]</a>';
				}

				if (item.message)
				{
					extSelectionHtml += '&nbsp;<span class="jsn-green-message">- '+item.message+'</span>';
				}

				if (item.productDesc)
				{
					extSelectionHtml += '<div id="product-desc-'+key+'" class="jsn-installsample-non-display">'+item.productDesc;
					if (item.url)
					{
						extSelectionHtml += '&nbsp;'+item.url;
					}
					extSelectionHtml += '</div>';
				}

				/* Populate a list of dependencies for current extension if available */
				if (item.deps)
				{
					/* All dependencies listed here are "required" so don't need subitem.install check */
					var subList = '<ul>';
					Object.each(item.deps, function(subitem, subkey)
					{
						subList += '<li><input class="jsn-ext-list deps-'+key+'" id="'+subkey+'" type="checkbox" name="installExts" value="'+key+'_'+subkey+'" checked="checked" disabled="disabled" />';
						subList += '<label for="'+subkey+'">'+subitem.desc+'</label>';

						if (subitem.message)
						{
							subList += '&nbsp;<span class="jsn-green-message">- '+subitem.message+'</span>';
						}
						subList += '</li>';
					});
					subList += '</ul>';

					extSelectionHtml += subList;
				}

				extSelectionHtml += '</li>';
			}
		});
		extSelectionHtml += '</ul>';

		return extSelectionHtml;
	},

	/**
	 * Check/uncheck all dependencies of selected extension
	 */
	toggleDepsCheck: function(parentInput)
	{
		var parentObj = $(parentInput);
		var parentId = parentObj.get("value");

		$$("input.deps-"+parentId).setProperty("checked", parentObj.getProperty("checked"));
	},

	/**
	 * Toggle the product description div by clicking an icon
	 */
	toggleProductDesc: function(clickedItem, extId)
	{
		var item = $(clickedItem);
		var itemText = item.get("text");
		if (itemText === '[+]')
		{
			item.set("text", "[-]");
		}
		else
		{
			item.set("text", "[+]");
		}

		$("product-desc-"+extId).toggleClass("jsn-installsample-display-block")
			.toggleClass("jsn-installsample-non-display");
	},

	/**
	 * Please remember to add '</li>' to the result of this function. This will
	 * be addressed later for more convenient use of code.
	 */
	populateExtListItem: function(id, desc)
	{
		var liHtml = "";

		liHtml += "<li id=\"jsn-install-extension-" + id + "\">";
		liHtml += "<span class=\"jsn-step-subtitle\">" + desc + "</span>";
		liHtml += "<span class=\"jsn-step-state-indicator\"></span>";
		liHtml += "<div id=\"jsn-download-progress-" + id + "\" class=\"download-progress\">";
		liHtml += "	<div class=\"progress\">"
		liHtml += "		<div class=\"bar\" style=\"width: 0%;\"></div>";
		liHtml += "	</div>";
		liHtml += "	<span class=\"percentage\"></span>";
		liHtml += "</div>";

		return liHtml;
	},

	/**
	 * This function return "identifier name" for an extension or template by
	 * converting all characters to lowercase and replacing (1 or more)
	 * whitespace(s) by "-" character.
	 */
	populateIdentifierName: function(name)
	{
		return name.toLowerCase().replace(/\s+/g, "-");
	},

	checkFolderPermission: function(templateName, url)
	{
		var folderListElement = $("jsn-li-folder-perm-failed");

		var checkAgainButton = $("jsn-perm-try-again");
		/* Disable the button */
		checkAgainButton.disabled = true;

		var jsonRequest = new Request.JSON(
		{
			url: url,
			onSuccess: function(jsonObj) {
				if(!jsonObj.permission)
				{
					/* Display a list of un-writable folders */
					var ul = folderListElement.getChildren("ul");

					var count = jsonObj.folders.length;
					var liHtml ="";
					Array.each(jsonObj.folders, function(item)
					{
						liHtml += "<li>" + item + "</li>";
					});

					ul.set("html", liHtml);

					/* Disable form inputs */
					JSNSampleData.disableFormInputs($("frm-login"), true);

					/* Hide the form */
					$("jsn-auto-install-login-form").addClass("jsn-installsample-non-display");

					/* Enable the check button again */
					checkAgainButton.disabled = false;
				}
				else
				{
					/* Remove the list */
					folderListElement.getChildren("ul").set("html", "");
					folderListElement.removeClass("jsn-installsample-display-list")
						.addClass("jsn-installsample-non-display");

					/* Enable form inputs */
					JSNSampleData.disableFormInputs($("frm-login"), false);

					/* Show the form */
					$("jsn-auto-install-login-form").removeClass("jsn-installsample-non-display")
						.addClass("jsn-installsample-display-block");
				}
			}
		}).get({"template": templateName, "tmpl": "jsn_runajax", "task": "checkFolderPermission"});
	},

	disableFormInputs: function(form, state)
	{
		form.username.disabled = state;
		form.password.disabled = state;
		form.agree.disabled = state;
	},

	disableModalCloseButton: function()
	{
		var closeButton = window.parent.document.getElementById("sbox-btn-close");
		if (closeButton)
		{
			closeButton.setStyle("display", "none");
		}
	},

	closeModalWindow: function()
	{
		/* Re-enable the default close button of Joomla Modal window */
		var closeButton = window.parent.document.getElementById("sbox-btn-close");
		if (closeButton)
		{
			setTimeout(function() {
				closeButton.setStyle("display", "block");
			}, 100);
		}

		window.top.setTimeout('SqueezeBox.close();', 100);
	},

	toggleCancelButton: function(toggle) {
		var cancelLink = $("jsn-install-cancel");
		if (toggle)
		{
			cancelLink.removeClass("jsn-updater-display-none");
		}
		else
		{
			cancelLink.addClass("jsn-updater-display-none");
		}
	}
};
