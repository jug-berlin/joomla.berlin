/**
* @author    JoomlaShine.com http://www.joomlashine.com
* @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
* @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
*/

	var JSNTemplate = {
		_templateParams:		{},

		initOnDomReady: function()
		{
			// Setup HTML code for typography
			JSNUtils.createGridLayout("DIV", "grid-layout", "grid-col", "grid-lastcol");
			JSNUtils.createExtList("list-number-", "span", "jsn-listbullet", true);
			JSNUtils.createExtList("list-icon", "span", "jsn-listbullet", false);

			// Setup Go to top link settings
			if (_templateParams.enableGotopLink) {
				JSNUtils.setToTopLinkCenter(_templateParams.enableRTL, false);
				JSNUtils.setSmoothScroll(false);
				JSNUtils.setFadeScroll(false);
			}

			// Setup mobile menu
			JSNUtils.setMobileMenu("menu-mainmenu");

			if (JSNUtils.isDesktopViewOnMobile()) {
				// Setup mobile sticky
				if (_templateParams.enableMobileMenuSticky && JSNUtils.checkMobile()) {
					JSNUtils.setMobileSticky();
				}
			}
			else {
				JSNUtils.initMenuForDesktopView();
			}
			
			// Setup mobile sitetool
			JSNUtils.setMobileSitetool();
		},
		
		initOnLoad: function()
		{
			// Stick positions layout setup
			JSNUtils.setVerticalPosition("jsn-pos-stick-leftmiddle", 'middle');
			JSNUtils.setVerticalPosition("jsn-pos-stick-rightmiddle", 'middle');
		},

		initTemplate: function(templateParams)
		{
			// Store template parameters
			_templateParams = templateParams;
			
			// Init template on "domready" event
			window.addEvent('domready', JSNTemplate.initOnDomReady);
			window.addEvent('load', JSNTemplate.initOnLoad);
		}
	}; // must have ; to prevent syntax error when compress
