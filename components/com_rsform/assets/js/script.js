var RSFormProCalendars = {};
var RSFormProPrices = {};

var RSFormProUtils = {
	hasClass: function (el, name) {
		return new RegExp('(\\s|^)' + name + '(\\s|$)').test(el.className);
	},
	addClass: function(el, name) {
		if (!RSFormProUtils.hasClass(el, name)) {
			el.className += (el.className ? ' ' : '') + name;
		}
	},
	removeClass: function(el, name) {
		if (RSFormProUtils.hasClass(el, name)) {
			el.className = el.className.replace(new RegExp('(\\s|^)' + name + '(\\s|$)'),' ').replace(/^\s+|\s+$/g, '');
		}
	}
};

function isset () {
  // http://kevin.vanzonneveld.net
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: FremyCompany
  // +   improved by: Onno Marsman
  // +   improved by: Rafał Kukawski
  // *     example 1: isset( undefined, true);
  // *     returns 1: false
  // *     example 2: isset( 'Kevin van Zonneveld' );
  // *     returns 2: true
  var a = arguments,
    l = a.length,
    i = 0,
    undef;

  if (l === 0) {
    throw new Error('Empty isset');
  }

  while (i !== l) {
    if (a[i] === undef || a[i] === null) {
      return false;
    }
    i++;
  }
  return true;
}

function refreshCaptcha(componentId, captchaPath)
{
	if(!captchaPath) captchaPath = 'index.php?option=com_rsform&task=captcha&componentId=' + componentId;
	document.getElementById('captcha' + componentId).src = captchaPath + '&' + Math.random();
	document.getElementById('captchaTxt' + componentId).value='';
	document.getElementById('captchaTxt' + componentId).focus();
}

function number_format(number, decimals, dec_point, thousands_sep)
{
    var n = number, prec = decimals;
    n = !isFinite(+n) ? 0 : +n;
    prec = !isFinite(+prec) ? 0 : Math.abs(prec);
    var sep = (typeof thousands_sep == "undefined") ? ',' : thousands_sep;
    var dec = (typeof dec_point == "undefined") ? '.' : dec_point;
 
    var s = (prec > 0) ? n.toFixed(prec) : Math.round(n).toFixed(prec); //fix for IE parseFloat(0.55).toFixed(0) = 0;
 
    var abs = Math.abs(n).toFixed(prec);
    var _, i;
 
    if (abs >= 1000) {
        _ = abs.split(/\D/);
        i = _[0].length % 3 || 3;
 
        _[0] = s.slice(0,i + (n < 0)) +
              _[0].slice(i).replace(/(\d{3})/g, sep+'$1');
 
        s = _.join(dec);
    } else {
        s = s.replace('.', dec);
    }
 
    return s;
}

function buildXmlHttp()
{
	var xmlHttp;
	try
	{
		xmlHttp=new XMLHttpRequest();
	}
	catch (e)
	{
		try
		{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			try
			{
				xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				alert("Your browser does not support AJAX!");
				return false;
			}
		}
	}
	return xmlHttp;
}

function ajaxValidation(form, page)
{
	try
	{
		var el = form.elements.length;
	}
	catch (err)
	{
		form = this;
	}
		
	var xmlHttp = buildXmlHttp();
	var url = '';
	if (typeof rsfp_ajax_root != 'undefined')
		url = rsfp_ajax_root + '/';
	url += 'index.php?option=com_rsform&task=ajaxValidate';
	
	if (page)
		url += '&page=' + page;
	
	var params = new Array();
	var submits = new Array();
	var success = false;
	var formId = 0;
	for (i=0; i<form.elements.length; i++)
	{
		// don't send an empty value
		if (!form.elements[i].name) continue;
		if (form.elements[i].name.length == 0) continue;
		// check if the checkbox is checked
		if (form.elements[i].type == 'checkbox' && form.elements[i].checked == false) continue;
		// check if the radio is selected
		if (form.elements[i].type == 'radio' && form.elements[i].checked == false) continue;
		
		if (form.elements[i].type == 'submit')
		{
			submits.push(form.elements[i]);
			form.elements[i].disabled = true;
		}
		
		// check if form is a dropdown with multiple selections
		if (form.elements[i].type == 'select-multiple')
		{
			for (var j=0; j<form.elements[i].options.length; j++)
				if (form.elements[i].options[j].selected)
					params.push(form.elements[i].name + '=' + encodeURIComponent(form.elements[i].options[j].value));
			
			continue;
		}
		
		if (form.elements[i].name == 'form[formId]')
			formId = form.elements[i].value;
		
		params.push(form.elements[i].name + '=' + encodeURIComponent(form.elements[i].value));
	}
	
	if (typeof ajaxExtraValidationScript[formId] == 'function') {
		ajaxExtraValidationScript[formId]('beforeSend', formId, {'url': url, 'params': params});
	}
	
	params = params.join('&');
	
	xmlHttp.open("POST", url, false);

	//Send the proper header information along with the request
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	//xmlHttp.setRequestHeader("Content-length", params.length);
	//xmlHttp.setRequestHeader("Connection", "close");
	xmlHttp.send(params);
	var success = true;
	
	if (xmlHttp.responseText.indexOf("\n") != -1)
	{
		var response = xmlHttp.responseText.split("\n");
		// All spans set to no error
		var ids = response[0].split(',');
		for (var i=0; i<ids.length; i++)
			if (!isNaN(parseInt(ids[i])) && document.getElementById('component'+ids[i]))
				document.getElementById('component'+ids[i]).className = 'formNoError';
			
		// Show errors
		var ids = response[1].split(',');
		for (var i=0; i<ids.length; i++)
			if (!isNaN(parseInt(ids[i])) && document.getElementById('component'+ids[i]))
			{
				document.getElementById('component'+ids[i]).className = 'formError';
				success = false;
			}
		
		if (response.length == 4)
		{
			page = parseInt(response[2]) - 1;
			totalPages = parseInt(response[3]);
			rsfp_changePage(formId, page, totalPages, false);
		}
		
		if (typeof ajaxExtraValidationScript[formId] == 'function') {
			ajaxExtraValidationScript[formId]('afterSend', formId, {'url': url, 'params': params, 'response': response});
		}
	}
	
	for (var i=0; i<submits.length; i++)
		submits[i].disabled = false;
	
	if (success == false && document.getElementById('rsform_error_' + formId))
	{
		try {
			document.getElementById('rsform_error_' + formId).style.display = 'block';
		}
		catch (err) { }
	}
	
	if (success == true) {
		try {
			document.getElementById('rsform_error_' + formId).style.display = 'none';
		}
		catch (err) { }
	}
	
	return success;
}

var ajaxExtraValidationScript = {};

function rsfp_addEvent(obj, evType, fn){ 
 if (obj.addEventListener){ 
   obj.addEventListener(evType, fn, false); 
   return true; 
 } else if (obj.attachEvent){ 
   var r = obj.attachEvent("on"+evType, fn); 
   return r; 
 } else { 
   return false; 
 } 
}

function rsfp_getForm(formId)
{
	var formIds = document.getElementsByName('form[formId]');
	for (var i=0; i<formIds.length; i++)
	{
		if (parseInt(formIds[i].value) != parseInt(formId))
			continue;
		
		var form = formIds[i].parentNode;		
		if (form.tagName == 'FORM' || form.nodeName == 'FORM')
			return form;
		
		while (form.parentNode)
		{
			form = form.parentNode;
			if (form.tagName == 'FORM' || form.nodeName == 'FORM')
				return form;
		}
	}
}

function rsfp_setCalculationsEvents(formId, fields) {
	var func 		= window["rsfp_Calculations" + formId];
	var thefields	= fields ? fields : RSFormProPrices;
	
	// Detect IE8.
	var isIE8 = navigator.userAgent.match(/MSIE 8\.0/);
	var event = 'click';
	
	for(field in thefields) {
		field = field.replace(formId+'_','');
		objects = rsfp_getFieldsByName(formId,field);
		for(i=0;i<objects.length;i++) {
			tagName = objects[i].tagName || objects[i].nodeName;
			
			if (tagName == 'INPUT' || tagName == 'SELECT') {
				// IE8 fix.
				if (tagName == 'INPUT' && isIE8 && objects[i].type && objects[i].type.toLowerCase() == 'checkbox') {
					event = 'click';
				} else {
					event = 'change';
				}
				
				rsfp_addEvent(objects[i], event, function() {
					if (typeof func == "function") {
						func();
					}
				});
			}
		}
	}
}

function rsfp_getValue(formId, name) {
	form		= rsfp_getForm(formId);
	values		= [];
	
	if (typeof form != 'undefined')
	{
		for (var i=0; i<form.elements.length; i++)
		{
			var element = form.elements[i];
			var tagName = element.tagName || element.nodeName;
			
			switch (tagName)
			{
				case 'INPUT':
					if (element.type)
						switch (element.type.toUpperCase())
						{
							case 'TEXT':
							case 'HIDDEN':
								if (!element.name || element.name != 'form[' + name + ']') continue;
								return element.value;
							break;
							
							case 'RADIO':
								if (!element.name || element.name != 'form[' + name + ']') continue;
								
								if (element.checked == true) {
									values.push(element.value);
								}
							break;
							
							case 'CHECKBOX':
								if (!element.name || element.name != 'form[' + name + '][]') continue;
								if (element.checked == true) {
									values.push(element.value);
								}
							break;
						}
				break;
				
				case 'SELECT':
					if (!element.name || element.name != 'form[' + name + '][]') continue;
					
					if (element.options)
						for (var o=0; o<element.options.length; o++)
							if (element.options[o].selected)
							{
								values.push(element.options[o].value);
							}
				break;
			}
		}
	}
	
	return values;
}

function rsfp_toNumber(number,replace1,replace2) {
	if (number.indexOf(',') == -1 && number.indexOf('.') != -1 ) {
		return number;
	} else if (number.indexOf(',') != -1 && number.indexOf('.') == -1 ) {
		return number.replace(',','.');
	}
	
	number = number.replace(replace1,'').replace(replace2,'');
    return parseFloat(number) / 100;
}

function rsfp_verifyChecked(formId, name, value) {
	
	isChecked = false;
	form = rsfp_getForm(formId);
	
	if (typeof form != 'undefined')
	{
		primary_loop:
		for (var i=0; i<form.elements.length; i++)
		{
			var element = form.elements[i];
			var tagName = element.tagName || element.nodeName;
			
			switch (tagName)
			{
				case 'INPUT':
					if (element.type)
						switch (element.type.toUpperCase())
						{
							case 'RADIO':
								if (!element.name || element.name != 'form[' + name + ']') continue;
								if (element.checked == true && element.value == value)
								{
									isChecked = true;
									break primary_loop;
								}
							break;
							
							case 'CHECKBOX':
								if (!element.name || element.name != 'form[' + name + '][]') continue;
								if (element.checked == true && element.value == value)
								{
									isChecked = true;
									break primary_loop;
								}
							break;
						}
				break;
				
				case 'SELECT':
					if (!element.name || element.name != 'form[' + name + '][]') continue;
					
					if (element.options)
						for (var o=0; o<element.options.length; o++)
							if (element.options[o].selected && element.options[o].value == value)
							{
								isChecked = true;
								break primary_loop;
							}
				break;
			}
		}
	}
	
	return isChecked;
}

function rsfp_addCondition(formId, name, fnCondition) {
	form = rsfp_getForm(formId);
	
	if (typeof form != 'undefined') {
		for (var i=0; i<form.elements.length; i++) {
			var element = form.elements[i];
			if (element.name && (element.name == 'form[' + name + ']' || element.name == 'form[' + name + '][]')) {
				rsfp_addEvent(element, 'click', function() {
					fnCondition();
				});
				rsfp_addEvent(element, 'change', function() {
					fnCondition();
				});
			}
		}
	}
}

function rsfp_getBlock(formId, block) {
	form = rsfp_getForm(formId);
	
	if (typeof form != 'undefined') {
		if (blocks = getElementsByClassName('rsform-block')) {
			for (i=0; i<blocks.length; i++) {
				var classes = blocks[i].className.split(' ');
				for (c=0; c<classes.length; c++) {
					if (classes[c] == 'rsform-block-' + block) {
						if (blocks[i].parentNode) {
							current_block = blocks[i];
							if (current_block == form)
								return [ blocks[i] ];
								
							while (current_block.parentNode) {
								current_block = current_block.parentNode;
								if (current_block == form)
									return [ blocks[i] ];
							}
						}
						
						return [ blocks[i] ];
					}
				}
			}
		}
	}
}

function rsfp_getFieldsByName(formId, name) {
	form = rsfp_getForm(formId);
	
	var results = new Array();
	
	if (typeof form != 'undefined') {
		for (var i=0; i<form.elements.length; i++) {
			var element = form.elements[i];
			pushed = false;
			if (element.name && (
				// single fields
				element.name == 'form[' + name + ']' ||
				// multiple fields
				element.name == 'form[' + name + '][]' ||
				// new field - Birthday field
				element.name == 'form[' + name + '][d]' ||
				element.name == 'form[' + name + '][m]' ||
				element.name == 'form[' + name + '][y]'
				)) {
				results.push(element);
				pushed = true;
			}
			
			if (pushed) {
				// for calendar
				if (element.id && element.id.indexOf('txtcal') > -1) {
					var suffix = element.id.replace('txtcal', '');
					results.push(document.getElementById('btn' + suffix));
				}
				// for labels (radio, checkbox)
				var labels = form.getElementsByTagName('label');
				for (var l=0; l<labels.length; l++) {
					if (labels[l].htmlFor && labels[l].htmlFor == element.id)
						results.push(labels[l]);
				}
			}
		}
	}
	
	return results;
}

function rsfp_setDisplay(items, value) {
	for (i=0; i<items.length; i++)
		items[i].style.display = value;
}

function rsfp_checkValidDate(fieldName) {
	var theDate = new Date();
	for (var day=1; day<=31; day++) {
		var year 	= parseInt(document.getElementById(fieldName + 'y').value);
		var month 	= parseInt(document.getElementById(fieldName + 'm').value) - 1;
		
		var index = day - 1;
		if (document.getElementById(fieldName + 'd').options[0].value == '') {
			index++;
		}
		document.getElementById(fieldName + 'd').options[index].disabled = false;
		if (!isNaN(year) && !isNaN(month)) {
			if (typeof theDate.__msh_oldSetFullYear == 'function') {
				theDate.__msh_oldSetFullYear(year, month, day);
			} else {
				theDate.setFullYear(year, month, day);
			}
			
			if (theDate.getDate() != day || theDate.getMonth() != month) {
				document.getElementById(fieldName + 'd').options[index].disabled = true;
			}
		}
	}
	if (document.getElementById(fieldName + 'd').options[document.getElementById(fieldName + 'd').selectedIndex].disabled == true) {
		for (var day=31; day>=28; day--) {
			var index = day - 1;
			if (document.getElementById(fieldName + 'd').options[0].value == '') {
				index++;
			}
			if (document.getElementById(fieldName + 'd').options[index].disabled == false) {
				document.getElementById(fieldName + 'd').value = day;
				break;
			}
		}
	}
}

function rsfp_geolocation(term, id, mapid,  map, marker, geocoder, type) {
	var content = document.getElementById('rsform_geolocation'+id);
	var address	= document.getElementById(mapid).clientWidth;
	
	document.getElementById('rsform_geolocation'+id).style.width = address+'px';
	document.getElementById('rsform_geolocation'+id).style.display = 'none';
	document.getElementById('rsform_geolocation'+id).innerHTML = '';
	
	if (term != '') {
		geocoder.geocode( {'address': term }, function(results, status) {
			if (status == 'OK') {
				for (var i=0; i<results.length; i++) {
					var item	= results[i];
					var theli	= document.createElement('li');
					var thea	= document.createElement('a');
					
					thea.setAttribute('href','javascript:void(0)');
					thea.innerHTML = item.formatted_address;
					
					rsfp_addEvent(thea,'click', (function() {
						var mapValue = type ? item.formatted_address : item.geometry.location.lat().toFixed(5) + ',' + item.geometry.location.lng().toFixed(5);
						var mapId	 = mapid;
						var location = new google.maps.LatLng(item.geometry.location.lat().toFixed(5), item.geometry.location.lng().toFixed(5));
						
						return function() {
							document.getElementById(mapId).value = mapValue;							
							marker.setPosition(location);
							map.setCenter(location);
							document.getElementById('rsform_geolocation'+id).style.display = 'none';
						}
					})());
					
					theli.appendChild(thea);
					content.appendChild(theli);
				}
			
				document.getElementById('rsform_geolocation'+id).style.display = '';
			}
		});
	}
}

function rsfp_runAllConditions(formId) {
	var func = window["rsfp_runAllConditions" + formId];
	if (typeof func == "function") {
		func();
	}
}

/*
	Developed by Robert Nyman, http://www.robertnyman.com
	Code/licensing: http://code.google.com/p/getelementsbyclassname/
*/	
var getElementsByClassName = function (className, tag, elm){
	if (document.getElementsByClassName) {
		getElementsByClassName = function (className, tag, elm) {
			elm = elm || document;
			var elements = elm.getElementsByClassName(className),
				nodeName = (tag)? new RegExp("\\b" + tag + "\\b", "i") : null,
				returnElements = [],
				current;
			for(var i=0, il=elements.length; i<il; i+=1){
				current = elements[i];
				if(!nodeName || nodeName.test(current.nodeName)) {
					returnElements.push(current);
				}
			}
			return returnElements;
		};
	}
	else if (document.evaluate) {
		getElementsByClassName = function (className, tag, elm) {
			tag = tag || "*";
			elm = elm || document;
			var classes = className.split(" "),
				classesToCheck = "",
				xhtmlNamespace = "http://www.w3.org/1999/xhtml",
				namespaceResolver = (document.documentElement.namespaceURI === xhtmlNamespace)? xhtmlNamespace : null,
				returnElements = [],
				elements,
				node;
			for(var j=0, jl=classes.length; j<jl; j+=1){
				classesToCheck += "[contains(concat(' ', @class, ' '), ' " + classes[j] + " ')]";
			}
			try	{
				elements = document.evaluate(".//" + tag + classesToCheck, elm, namespaceResolver, 0, null);
			}
			catch (e) {
				elements = document.evaluate(".//" + tag + classesToCheck, elm, null, 0, null);
			}
			while ((node = elements.iterateNext())) {
				returnElements.push(node);
			}
			return returnElements;
		};
	}
	else {
		getElementsByClassName = function (className, tag, elm) {
			tag = tag || "*";
			elm = elm || document;
			var classes = className.split(" "),
				classesToCheck = [],
				elements = (tag === "*" && elm.all)? elm.all : elm.getElementsByTagName(tag),
				current,
				returnElements = [],
				match;
			for(var k=0, kl=classes.length; k<kl; k+=1){
				classesToCheck.push(new RegExp("(^|\\s)" + classes[k] + "(\\s|$)"));
			}
			for(var l=0, ll=elements.length; l<ll; l+=1){
				current = elements[l];
				match = false;
				for(var m=0, ml=classesToCheck.length; m<ml; m+=1){
					match = classesToCheck[m].test(current.className);
					if (!match) {
						break;
					}
				}
				if (match) {
					returnElements.push(current);
				}
			}
			return returnElements;
		};
	}
	return getElementsByClassName(className, tag, elm);
};