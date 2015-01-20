/**
* @author    JoomlaShine.com http://www.joomlashine.com
* @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
* @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
*/
var JSNUtils = {

	/* ============================== BROWSER  ============================== */

	writeCookie: function (name,value,days){
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		} else expires = "";

		document.cookie = name+"="+value+expires+"; path=/";
	},

	readCookie: function (name){
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	},

	isIE7: function() {
		return (navigator.appVersion.indexOf("MSIE 7.")!=-1);
	},
	
	isDesktopViewOnMobile: function () {
		return document.body.hasClass('jsn-mobile');
	},
	
	initMenuForDesktopView: function () {
		var sitetools = $('jsn-sitetools-menu');
		if (sitetools != null)
			sitetools.addClass('sitetool-desktop-on-mobile');
			
		$$('ul.menu-mainmenu').addClass('jsn-desktop-on-mobile');
	},

	getBrowserInfo: function(){
		var name	= '';
		var version = '';
		var ua 		= navigator.userAgent.toLowerCase();
		var match	= ua.match(/(opera|ie|firefox|chrome|version)[\s\/:]([\w\d\.]+)?.*?(safari|version[\s\/:]([\w\d\.]+)|$)/) || [null, 'unknown', 0];
		if (match[1] == 'version')
		{
			name = match[3];
		}
		else
		{
			name = match[1];
		}
		version = parseFloat((match[1] == 'opera' && match[4]) ? match[4] : match[2]);

		return {'name': name, 'version': version};
	},

	/* ============================== DEVICE  ============================== */

	checkMobile: function(){
		var mobiles = [
			"midp","240x320","blackberry","netfront","nokia","panasonic",
			"portalmmm","sharp","sie-","sonyericsson","symbian",
			"windows ce","benq","mda","mot-","opera mini",
			"philips","pocket pc","sagem","samsung","sda",
			"sgh-","vodafone","xda","palm","iphone",
			"ipod","android", "ipad"
		];
		var uagent = navigator.userAgent.toLowerCase();
		var isMobile = false;

		for(var d=0;d<mobiles.length;d+=1){
			if(uagent.indexOf(mobiles[d])!=-1){
				isMobile = true;
			}
		}

		return isMobile;
	},

	getScreenWidth: function(){
		var screenWidth;

		if(JSNUtils.checkMobile())
		{
			screenWidth = window.screen.width;
		}
		else
		{
			if( typeof( window.innerWidth ) == 'number' )
			{
				//Non-IE
				screenWidth = window.innerWidth;
			}
			else if ( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) )
			{
				//IE 6+ in 'standards compliant mode'
				screenWidth = document.documentElement.clientWidth;
			}
		}

		return screenWidth;
	},

	checkSmartphone: function(){
		var screenWidth = JSNUtils.getScreenWidth();
		var isSmartphone = false;

		if (screenWidth >= 320 && screenWidth < 480)
		{
			isSmartphone = true;
		}

		return isSmartphone;
	},

	checkTablet: function(){
		var screenWidth = JSNUtils.getScreenWidth();
		var isTablet = false;

		if (screenWidth >= 481 && screenWidth < 1024)
		{
			isTablet = true;
		}

		return isTablet;
	},

	getScreenType: function(){
		var screenType;

		if (JSNUtils.checkSmartphone()) {
			screenType = 'smartphone';
		} else if (JSNUtils.checkTablet()) {
			screenType = 'tablet';
		} else {
			screenType = 'desktop';
		}

		return screenType;
	},


	/* ============================== DOM - GENERAL ============================== */

	addEvent: function(target, event, func){
		if (target.addEventListener){
			target.addEventListener(event, func, false);
			return true;
		} else if (target.attachEvent){
			var result = target.attachEvent("on"+event, func);
			return result;
		} else {
			return false;
		}
	},

	getElementsByClass: function(targetParent, targetTag, targetClass, targetLevel){
		var elements, tags, tag, tagClass;

		if(targetLevel == undefined){
			tags = targetParent.getElementsByTagName(targetTag);
		}else{
			tags = JSNUtils.getChildrenAtLevel(targetParent, targetTag, targetLevel);
		}

		elements = [];

		for(var i=0;i<tags.length;i++){
			tagClass = tags[i].className;
			if(tagClass != "" && JSNUtils.checkSubstring(tagClass, targetClass, " ", false)){
				elements[elements.length] = tags[i];
			}
		}

		return elements;
	},

	getFirstChild: function(targetEl, targetTagName){
		var nodes, node;
		nodes = targetEl.childNodes;
		for(var i=0;i<nodes.length;i++){
			node = nodes[i];
			if (node.tagName == targetTagName)
				return node;
		}
		return null;
	},

	getFirstChildAtLevel: function(targetEl, targetTagName, targetLevel){
		var child, nodes, node;
		nodes = targetEl.childNodes;
		for(var i=0;i<nodes.length;i++){
			node = nodes[i];
			if (targetLevel == 1) {
				if(node.tagName == targetTagName) return node;
			} else {
				child = JSNUtils.getFirstChildAtLevel(node, targetTagName, targetLevel-1);
				if(child != null) return child;
			}
		}
		return null;
	},

	getChildren: function(targetEl, targetTagName){
		var nodes, node;
		var children = [];
		nodes = targetEl.childNodes;
		for(var i=0;i<nodes.length;i++){
			node = nodes[i];
			if(node.tagName == targetTagName)
				children.push(node);
		}
		return children;
	},

	getChildrenAtLevel: function(targetEl, targetTagName, targetLevel){
		var children = [];
		var nodes, node;
		nodes = targetEl.childNodes;
		for(var i=0;i<nodes.length;i++){
			node = nodes[i];
			if (targetLevel == 1) {
				if(node.tagName == targetTagName) children.push(node);
			} else {
				children = children.concat(JSNUtils.getChildrenAtLevel(node, targetTagName, targetLevel-1));
			}
		}
		return children;
	},

	addClass: function(targetTag, targetClass){
		if(targetTag.className == ""){
			targetTag.className = targetClass;
		} else {
			if(!JSNUtils.checkSubstring(targetTag.className, targetClass, " ")){
				targetTag.className += " " + targetClass;
			}
		}
	},

	getViewportSize: function(){
		var myWidth = 0, myHeight = 0;

		if( typeof( window.innerWidth ) == 'number' ) {
			//Non-IE
			myWidth = window.innerWidth;
			myHeight = window.innerHeight;
		} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
			//IE 6+ in 'standards compliant mode'
			myWidth = document.documentElement.clientWidth;
			myHeight = document.documentElement.clientHeight;
		} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
			//IE 4 compatible
			myWidth = document.body.clientWidth;
			myHeight = document.body.clientHeight;
		}

		return {width:myWidth, height:myHeight };
	},

	addURLPrefix: function(targetId)
	{
		var navUrl 			= window.location.href;
		var targetEl 		= document.getElementById(targetId);
		if(targetEl != undefined && targetEl.tagName.toUpperCase() == 'A')
		{
			orgHref = targetEl.href;
			targetEl.href = navUrl + ((navUrl.indexOf(orgHref) != -1)?'':orgHref);
		}
	},

	/* ============================== DOM - GUI ============================== */
	/* ============================== DOM - GUI - MENU ============================== */

	setMobileMenu: function(menuClass)
	{
		var toggle = function() {
			this.toggleClass("active");
			this.getNext("ul").toggleClass("jsn-menu-mobile");

			$$("." + menuClass + " .jsn-menu-toggle").each(function (item) {
				var a = item.getPrevious(),
					size = a.getSize();

				item.setStyle('height', size.y);
			});
		};

		// Setup toggle for main trigger
		$$("." + menuClass).getPrevious(".jsn-menu-toggle").addEvent('click', toggle);

		// Setup toggle for children triggers
		$$("." + menuClass + " .jsn-menu-toggle").addEvent('click', toggle);
	},
	
	setMobileSticky: function()
	{
		var page 			= $('jsn-page'),
			menu   			= $('jsn-menu'),
			menuToggler 	= menu.getElement('.jsn-menu-toggle'),
			mainMenu 		= menu.getElement('ul.menu-mainmenu'),
			menuSize 		= menu.getCoordinates(),
			menuPlacehoder 	= new Element('div', { 'id': 'jsn-menu-placeholder' }),
			menuParent		= menu.getParent(),
			menuParentOffset = menuParent.getCoordinates(),
			menuLeft 		= menuSize.left,
			menuPaddingHorz = parseInt(menu.getStyle('padding-left')) + parseInt(menu.getStyle('padding-right')),
			menuBorderHorz  = parseInt(menu.getStyle('border-left')) + parseInt(menu.getStyle('border-right')),
			isSticked		= false,
			touchStartOffset = {},
			isFixedSupport  = JSNUtils.isFixedSupport();
			
		menuPlacehoder.setStyles({
			height: menuSize.height,
			margin: menu.getStyle('margin')
		});

		var getMaxMenuHeight = function () { return window.innerHeight - menuSize.height; };
		var getTouchDirection = function (touchEvent) { return touchEvent.touches[0].pageY > touchStartOffset.y ? 'up' : 'down'; };

		var resetMenuPosition = function () {
			if (window.getScroll().y < menuPlacehoder.getPosition().y) {
				menu
					.removeClass('jsn-menu-sticky')
					.removeAttribute('style');

				menuPlacehoder.dispose();
				mainMenu.setStyles({
					'max-height': 'auto',
					'overflow-y': 'hidden'
				});

				isSticked = false;
			}
		};
		
		var getMenuWidth = function (forceMenuWidth) {
			var menuWidth = forceMenuWidth || menuSize.width;
			
			if (!isNaN(menuPaddingHorz))
				menuWidth = menuWidth - menuPaddingHorz;
				
			if (!isNaN(menuBorderHorz))
				menuWidth = menuWidth - menuBorderHorz;
				
			return menuWidth;
		};

		var fx = new Fx.Morph(menu, { transition: Fx.Transitions.Expo.easeOut });
			fx.addEvent('complete', resetMenuPosition);

		var makeMenuStick = function () {
			var scrollTop = window.getScroll().y,
				menuOffsetTop = menu.getPosition().y;

			if (mainMenu.getStyle('display') == 'block')
				return;

			if (scrollTop > menuOffsetTop && menuParent.getElement('#jsn-menu-placeholder') == null && isSticked == false) {
				if (fx.isRunning())
					fx.cancel();

				menu.addClass('jsn-menu-sticky')
					.setStyles({
						'left'		: menuLeft,
						'width'		: getMenuWidth(),
						'position'	: isFixedSupport ? 'fixed' : 'absolute',
						'top'		: isFixedSupport ? 0 : scrollTop,
						'z-index'   : 9999999
					});
				
				menuPlacehoder.inject(menu, 'before');
				isSticked = true;
			}
		};

		var updatePosition = function () {
			makeMenuStick();

			var scrollTop = window.getScroll().y,
				placeHoderOffset = menuPlacehoder.getPosition().y;

			if (fx.isRunning()) fx.pause();

			// Reset menu position
			if (isSticked == true && placeHoderOffset > scrollTop && menu.getStyle('position') == 'fixed') {
				menu.setStyles({
					position 	: 'absolute',
					top 		: scrollTop,
					left 		: menuLeft,
					width		: getMenuWidth()
				});

				fx.start({ top: placeHoderOffset });
			}

			// Update menu position
			else if (isSticked == true && menu.getStyle('position') == 'absolute') {
				var menuTop = menu.getPosition().y;

				if (mainMenu.getStyle('display') == 'block') {
					return;
				}

				fx.start({ top: (placeHoderOffset > scrollTop) ? placeHoderOffset : scrollTop });
			}
		};

		var updatePositionTimeout = null,
			updateMenuSizeTimeout = null,
			isMovedToTop          = false,
			backupWindowScroll    = null,
			pageHeight = page.getSize().y;

		menu.getElements('.jsn-menu-toggle').addEvent('click', function () {
			var menuHeight = menu.getSize().y + mainMenu.getSize().y;
			
			if (menuToggler.hasClass('active')) {
				if (menuHeight > window.getSize().y && menu.hasClass('jsn-menu-sticky')) {
					backupWindowScroll = window.getScroll();

					page.setStyles({ 'height': menuHeight, 'overflow': 'hidden' });
					menu.setStyles({ 'position': 'absolute', 'top': 0 });

					if (isMovedToTop == false) {
						window.scrollTo(0, 0);
						isMovedToTop = true;
					}
				}
				else {
					page.setStyles({ 'height': pageHeight, 'overflow': 'visible' });
					isMovedToTop = false;
				}
			}
			else {
				page.setStyles({ 'height': pageHeight, 'overflow': 'visible' });
				isMovedToTop = false;

				updatePosition();
			}
		});

		window.addEvent('touchmove', makeMenuStick);
		window.addEvent('scroll', updatePosition);
		window.addEvent('resize', function () {
			clearTimeout(updateMenuSizeTimeout);
			updateMenuSizeTimeout = setTimeout(function () {
				if (isSticked == true) {
					menuSize = menuPlacehoder.getCoordinates();
					menu.setStyle('width', getMenuWidth());
				}
				else {
					menuSize = menu.getCoordinates();
				}
			}, 100);
		});

		window.addEvent('load', function () {
			clearTimeout(updatePositionTimeout);
			updatePositionTimeout = setTimeout(updatePosition, 100);
		});
	},

	setMobileSitetool: function()
	{
		var siteToolPanel = $("jsn-sitetoolspanel");
		if (siteToolPanel)
		{
			siteToolPanel.getElements("li.jsn-sitetool-control").addEvent("click", function() {
				this.toggleClass("active");
			});
		}
	},

	getSelectMenuitemIndex: function(elementID)
	{
		var childs = ($(elementID).childNodes);
		var count  = childs.length;
		var index  = 0;

		for (var i = 0; i < count; i++)
		{
			if(childs[i].className != undefined && childs[i].className.indexOf('parent') != -1)
			{
				if(childs[i].className.indexOf('active') != -1)
				{
					return index;
				}
				index++;
			}
		}
		return -1;
	},

	createImageMenu: function(menuId, imageClass){
		if (!document.getElementById) return;

		var list = document.getElementById(menuId);
		var listItems;

		var listItem;

		if(list != undefined) {
			listItems = list.getElementsByTagName("LI");
			for(i=0, j=0;i<listItems.length;i++){
				listItem = listItems[i];
				if (listItem.parentNode == list) {
					listItem.className += " " + imageClass + (j+1);
					j++;
				}
			}
		}
	},

	/* Set position of side menu sub panels */
	setSidemenuLayout: function(menuClass, rtlLayout)
	{
		var sidemenus, sidemenu, smChildren, smChild, smSubmenu;
		sidemenus = JSNUtils.getElementsByClass(document, "UL", menuClass);
		if (sidemenus != undefined) {
			for(var i=0;i<sidemenus.length;i++){
				sidemenu = sidemenus[i];
				smChildren = JSNUtils.getChildren(sidemenu, "LI");
				if (smChildren != undefined) {
					for(var j=0;j<smChildren.length;j++){
						smChild = smChildren[j];
						smSubmenu = JSNUtils.getFirstChild(smChild, "UL");
						if (smSubmenu != null) {
							if(rtlLayout == true) { smSubmenu.style.marginRight = smChild.offsetWidth+"px"; }
							else { smSubmenu.style.marginLeft = smChild.offsetWidth+"px"; }
						}
					}
				}
			}
		}
	},

	/* Set position of sitetools sub panel */
	setSitetoolsLayout: function(sitetoolsId, rtlLayout)
	{
		var sitetoolsContainer, parentItem, sitetoolsPanel, neighbour;
		sitetoolsContainer = document.getElementById(sitetoolsId);
		if (sitetoolsContainer != undefined) {
			parentItem = JSNUtils.getFirstChild(sitetoolsContainer, "LI");
			sitetoolsPanel = JSNUtils.getFirstChild(parentItem, "UL");
			if (rtlLayout == true) {
				sitetoolsPanel.style.marginRight = -1*(sitetoolsPanel.offsetWidth - parentItem.offsetWidth) + "px";
			} else {
				sitetoolsPanel.style.marginLeft = -1*(sitetoolsPanel.offsetWidth - parentItem.offsetWidth) + "px";
			}
		}
	},

	/* Change template setting stored in cookie */
	setTemplateAttribute: function(templatePrefix, attribute, value)
	{
		JSNUtils.writeCookie(templatePrefix+attribute,value);
		window.location.reload(true);
	},

	createExtList: function(listClass, extTag, className, includeNumber){
		if (!document.getElementById) return;

		var lists = JSNUtils.getElementsByClass(document, "UL", listClass);
		var list;
		var listItems;
		var listItem;

		if(lists != undefined) {
			for(j=0;j<lists.length;j++){
				list = lists[j];
				listItems = JSNUtils.getChildren(list, "LI");
				for(i=0,k=0;i<listItems.length;i++){
					listItem = listItems[i];
					if(className !=''){
						listItem.innerHTML = '<'+ extTag + ' class='+className+'>' + (includeNumber?(k+1):'') + '</'+  extTag +'>' + listItem.innerHTML;
					}else{
						listItem.innerHTML = '<'+ extTag + '>' + (includeNumber?(k+1):'') + '</'+  extTag +'>' + listItem.innerHTML;
					}
					k++;
				}
			}
		}
	},

	createGridLayout: function(containerTag, containerClass, columnClass, lastcolumnClass) {
		var gridLayouts, gridLayout, gridColumns, gridColumn, columnsNumber;
		gridLayouts = JSNUtils.getElementsByClass(document, containerTag, containerClass);
		for(var i=0;i<gridLayouts.length;i++){
			gridLayout = gridLayouts[i];
			gridColumns = JSNUtils.getChildren(gridLayout, containerTag);
			columnsNumber = gridColumns.length;
			JSNUtils.addClass(gridLayout, containerClass + columnsNumber);
			JSNUtils.addClass(gridLayout, 'clearafter');
			for(var j=0;j<columnsNumber;j++){
				gridColumn = gridColumns[j];
				JSNUtils.addClass(gridColumn, columnClass);
				if(j == gridColumns.length-1) {
					JSNUtils.addClass(gridColumn, lastcolumnClass);
				}
				gridColumn.innerHTML = '<div class="' + columnClass + '_inner">' + gridColumn.innerHTML + '</div>';
			}
		}
	},

	sfHover: function(menuId, menuDelay) {
		if(menuId == undefined) return;

		var delay = (menuDelay == undefined)?0:menuDelay;
		var pEl = document.getElementById(menuId);
		if (pEl != undefined) {
			var sfEls = pEl.getElementsByTagName("li");
			for (var i=0; i<sfEls.length; ++i) {
				sfEls[i].onmouseover=function() {
					clearTimeout(this.timer);
					if(this.className.indexOf("sfhover") == -1) {
						this.className += " sfhover";
					}
				}
				sfEls[i].onmouseout=function() {
					this.timer = setTimeout(JSNUtils.sfHoverOut.bind(this), delay);
				}
			}
		}
	},

	sfHoverOut: function() {
		clearTimeout(this.timer);
		this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
	},

	setFontSize: function (targetId, fontSize){
		var targetObj = (document.getElementById) ? document.getElementById(targetId) : document.all(targetId);
		targetObj.style.fontSize = fontSize + '%';
	},

	setVerticalPosition: function(pName, pAlignment) {
		var targetElement = document.getElementById(pName);

		if (targetElement != undefined) {
			var topDelta, vpHeight, pHeight;
			vpHeight = (JSNUtils.getViewportSize()).height;
			pHeight = targetElement.offsetHeight;
			switch(pAlignment){
				case "top":
					topDelta = 0;
				break;

				case "middle":
					topDelta = Math.floor((100 - Math.round((pHeight/vpHeight)*100))/2);
				break;

				case "bottom":
					topDelta = 100 - Math.round((pHeight/vpHeight)*100);
				break;
			}

			topDelta = (topDelta < 0)?0:topDelta;

			targetElement.style.top = topDelta + "%";

			targetElement.style.visibility = "visible";
		}
	},

	setInnerLayout:function(elements)
	{
		var pleftWidth = 0;
		var pinnerleftWidth = 0;
		var prightWidth = 0;
		var pinnerrightWidth = 0;
		var root = document.getElementById(elements[0]);
		var rootWidth = root.offsetWidth;
		if(document.getElementById(elements[1]) != null) {
			pleftWidth = document.getElementById(elements[1]).offsetWidth;
		}
		if(document.getElementById(elements[3]) != null) {
			pinnerleftWidth = document.getElementById(elements[3]).offsetWidth;
			var resultLeft = (pleftWidth + pinnerleftWidth)*100/rootWidth;
			root.firstChild.style.right = (100 - resultLeft) + "%";
			root.firstChild.firstChild.style.left = (100 - resultLeft) + "%";
		}
		if(document.getElementById(elements[2]) != null) {
			prightWidth = document.getElementById(elements[2]).offsetWidth;
		}
		if(document.getElementById(elements[4]) != null) {
			pinnerrightWidth = document.getElementById(elements[4]).offsetWidth;
			var resultRight = (prightWidth + pinnerrightWidth)*100/rootWidth;
			root.firstChild.firstChild.firstChild.style.left = (100 - resultRight) + "%";
			root.firstChild.firstChild.firstChild.firstChild.style.right = (100 - resultRight) + "%";
		}
	},

	setEqualHeight: function()
	{
		var containerClass 	= "jsn-horizontallayout";
		var columnClass 	= "jsn-modulecontainer_inner";
		var horizontallayoutObjs = $$('.'+containerClass);
		var maxHeight = 0;
		Array.each(horizontallayoutObjs, function(item) {
			var columns = item.getElements('.'+columnClass);
			maxHeight = 0;
			Array.each(columns, function(col) {
				var coordinates = col.getCoordinates();
				if (coordinates.height > maxHeight) maxHeight = coordinates.height;
			});
			Array.each(columns, function(col) {
				col.setStyle('height',maxHeight);
			});
		});
	},


	/* ============================== MOOTOOLS ANIMATION  ============================== */

	setToTopLinkCenter: function(rtl, jquery)
	{
		/* Min distance to be away from top for the link to be displayed */
		var min = 200;

		/* Determine RTL layout or not to set margin correctly */
		var marginFrom = "margin-left";
		if (rtl === true) {
			marginFrom = "margin-right";
		}

		if (jquery) {
			var element = $j('#jsn-gotoplink');
			element.hide();
			($j(window).scrollTop() >= min) ? element.fadeIn() : element.fadeOut();
		} else if (typeof(MooTools) != 'undefined') {
			var element = $('jsn-gotoplink'),
				elementHeight = element.getSize().y;

			element
				.setStyle('margin-left', -(element.getSize().x/2))
				.set('opacity','0')
				.fade((window.getScroll().y >= min) ? 'in' : 'out')
				.fade((window.getScroll().y >= min) ? 1 : 0);

			if (!JSNUtils.isFixedSupport()) {
			 	element.setStyle('position', 'absolute');
			 	window.addEvent('scroll', function () {
			 		var height = window.innerHeight;
			 		element.setStyle('bottom', 'auto');
			 		element.setStyle('top', window.getScroll().y + (height - elementHeight));
			 	});
			}
		}
	},

	isFixedSupport: function () {
		var userAgent = window.navigator.userAgent + '',
			isAppleDevice = /ipod|ipad|iphone/.test(userAgent.toLowerCase()),
			isWindowPhone = /Windows Phone/.test(userAgent),
			isAndroid     = /Android/.test(userAgent),
			isSupported   = true;

		if (isAppleDevice || isWindowPhone || isAndroid) {
			var pattern = /AppleWebKit\/([0-9]+\.[0-9]+)\s+/;

			if (isWindowPhone)
				pattern = /IEMobile\/([0-9]+\.[0-9]+);/;

			if (pattern.test(userAgent)) {
				var result  = pattern.exec(userAgent);
				var version = result[1];

				isSupported = ((isAppleDevice || isAndroid) && JSNUtils.versionCompare(version, '534.1', '>='));
			}
		}

		return isSupported;
	},

	versionCompare: function (v1, v2, operator) {
	    this.php_js = this.php_js || {};
	    this.php_js.ENV = this.php_js.ENV || {};

	    var i = 0,
	        x = 0,
	        compare = 0,
	        vm = {
	            'dev': -6,
	            'alpha': -5,
	            'a': -5,
	            'beta': -4,
	            'b': -4,
	            'RC': -3,
	            'rc': -3,
	            '#': -2,
	            'p': -1,
	            'pl': -1
	        },

	        prepVersion = function (v) {
	            v = ('' + v).replace(/[_\-+]/g, '.');
	            v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.');
	            return (!v.length ? [-8] : v.split('.'));
	        },

	        numVersion = function (v) {
	            return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10));
	        };
	    v1 = prepVersion(v1);
	    v2 = prepVersion(v2);
	    x = Math.max(v1.length, v2.length);
	    for (i = 0; i < x; i++) {
	        if (v1[i] == v2[i]) {
	            continue;
	        }
	        v1[i] = numVersion(v1[i]);
	        v2[i] = numVersion(v2[i]);
	        if (v1[i] < v2[i]) {
	            compare = -1;
	            break;
	        } else if (v1[i] > v2[i]) {
	            compare = 1;
	            break;
	        }
	    }
	    if (!operator) {
	        return compare;
	    }

	    switch (operator) {
	    case '>':
	    case 'gt':
	        return (compare > 0);
	    case '>=':
	    case 'ge':
	        return (compare >= 0);
	    case '<=':
	    case 'le':
	        return (compare <= 0);
	    case '==':
	    case '=':
	    case 'eq':
	        return (compare === 0);
	    case '<>':
	    case '!=':
	    case 'ne':
	        return (compare !== 0);
	    case '':
	    case '<':
	    case 'lt':
	        return (compare < 0);
	    default:
	        return null;
	    }
	},

	setSmoothScroll: function(jquery)
	{
		var objBrowser = JSNUtils.getBrowserInfo();

		// Setup smooth go to top link
		if (jquery) {
			$j('#jsn-gotoplink').click(function() {
				var gotoplinkOffset = $j('#top').offset().top;
				$j('html,body').animate({scrollTop: gotoplinkOffset}, 500);
				return false;
			});
		} else if (typeof(MooTools) != 'undefined') {
			new Fx.SmoothScroll({ duration:300 }, window);
		}
	},

	setFadeScroll: function(jquery)
	{
		var min     = 200;
		if (jquery) {
			var element = $j('#jsn-gotoplink');
			if(element == null) return false;

			$j(window).scroll(function () {
				($j(window).scrollTop() >= min) ? element.fadeIn() : element.fadeOut();
			});
		} else if (typeof(MooTools) != 'undefined') {
			var element = $('jsn-gotoplink');
			if (element == null) return false;
			if (parseFloat(MooTools.version) < 1.2)
			{
				var fx 		   = new Fx.Style(element, "opacity", {duration: 500});
				var inside 	   = false;
				window.addEvent('scroll',function(e) {
					var position   = window.getSize().scroll;
					var y          = position.y;
					if (y >= min)
					{
						if (!inside)
						{
							inside = true;
							fx.start(0, 1);
						}
					}
					else
					{
						if (inside)
						{
							inside = false;
							fx.start(1, 0);
						}
					}
				}.bind(this));
			}
			else
			{
				window.addEvent('scroll',function(e) {
					element.fade((window.getScroll().y >= min) ? 'in' : 'out');
					element.fade((window.getScroll().y >= min) ? 1 : 0);
				}.bind(this));
			}
		}
	},

	/* ============================== TEXT  ============================== */

	checkSubstring: function(targetString, targetSubstring, delimeter, wholeWord){
		if(wholeWord == undefined) wholeWord = false;
		var parts = targetString.split(delimeter);
		for (var i = 0; i < parts.length; i++){
			if (wholeWord && parts[i] == targetSubstring) return true;
			if (!wholeWord && parts[i].indexOf(targetSubstring) > -1) return true;
		}
		return false;
	},

	/* ============================== REMOVE DUPLICATE CSS3 TAG IN IE7 - CSS3 PIE  ============================== */

	removeCss3Duplicate: function(className)
	{
		var element = $$('.' + className);
		if (element != undefined)
		{
			element.each(function(e){
				var elementParent = e.getParent();
				var duplicateTag = elementParent.getChildren('css3-container');
				if (duplicateTag.length && duplicateTag.length > 1)
				{
					elementParent.removeChild(duplicateTag[0]);
				}
			});
		}
	}
};
