(function (win, doc) {
	"use strict";

	/**
	 * HTTP Downloader class
	 */
	win.JSNHttpDownload = function (options) {
		this.opts = options;
		this.userAgent = win.navigator.userAgent;
		this.useIFrame = /MSIE/.test(this.userAgent);
		this.processId = this.opts.process || this.generateId();
		this.useIFrame == true
		 	? this.initIFrame()
		 	: this.initXHR()
	};

	win.JSNHttpDownload.prototype = {
		/**
		 * Just return a random string that use to determine
		 * process ID
		 * 
		 * @return string
		 */
		generateId: function () {
			var result = '',
				chars  = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
				length = 10;

			for (var i = length; i > 0; --i)
				result += chars[Math.round(Math.random() * (chars.length - 1))];

			return result;
		},

		/**
		 * Method use to create an iframe element, it have
		 * role as ajax transport
		 *
		 * @return void
		 */
		initIFrame: function () {
			var self = this;

			this.iframe = doc.createElement('iframe');
			this.iframe.style.display = 'none';

			// Add iframe to document
			doc.body.appendChild(this.iframe);

			// Generate name of callback function
			this.progressCallback = '__' + this.processId + '_progress';
			this.completeCallback = '__' + this.processId + '_complete';

			console.log(this.progressCallback);

			// Attach callback functions to window object
			win[this.progressCallback] = function (size, downloaded, speed) {
				self.progress(size, downloaded, speed);
			};

			win[this.completeCallback] = function (success) {
				self.complete(success);
			}
		},

		/**
		 * Initialize XMLHttpRequest object, this object use to
		 * send request to download server and update progress
		 * 
		 * @return void
		 */
		initXHR: function () {
			var self = this;
			this.xhr = null;
			
			try {
				this.xhr = new XMLHttpRequest();

				if (this.xhr.overrideMimeType)
					this.xhr.overrideMimeType('text/xml');
			} 
			catch(e) {
				// Switch back to IFrame transport when XHR
				// does not support
				this.useIFrame = true;
				this.initIFrame();

				return;
			}
		},

		/**
		 * This method use to tracking downloaded contents of file
		 * and update progress bar
		 * 
		 * @param   int    size        File size
		 * @param   int    downloaded  Downloaded size
		 * @param   float  speed       Download speed
		 * 
		 * @return  void
		 */
		progress: function (size, downloaded, speed) {
			var _size = this.convert(size, 2),
				_downloaded = this.convert(downloaded, 2),
				_speed = this.convert(speed, 2),
				_percent = this.round(downloaded/size * 100, 2);

			if (typeof(this.opts.progress) == 'function')
				this.opts.progress.call(this, _size, _downloaded, _percent, _speed);
		},

		/**
		 * This method is auto invoke when download process is completed
		 * 
		 * @param   boolean  success  This flag is true if download process is successfully
		 * @return  void
		 */
		complete: function (success) {
			if (typeof(this.opts.complete) == 'function')
				this.opts.complete.call(this, success == 'success');
		},

		/**
		 * Send a request to download server to begin download file
		 *
		 * @param   object  params  Parameters that will passed to download URL
		 * 
		 * @return  void
		 */
		start: function (params) {
			var self = this,
				urlParams = [],
				url = this.opts.url;

			if (this.useIFrame == true) params['transport'] = 'iframe';
			if (typeof(params) == 'object') {
				for (var key in params)
					urlParams.push(key + '=' + encodeURIComponent(params[key]));

				url.indexOf('?') != -1
					? url = url + '&' + urlParams.join('&')
					: url = url + '?' + urlParams.join('&');
			}

			if (this.useIFrame == true) {
				this.iframe.src = url;
				return;
			}

			this.xhr.open('GET', url, true);
			this.xhr.setRequestHeader('content-type', 'application/x-www-form-urlencoded');
			this.xhr.send(null);

			this.polling = setInterval(function () {
				var completePattern = /\[complete:([^\]]+)\]$/,
					progressPattern = /\[([0-9-\.]+)\s*,\s*([0-9-\.]+)\s*,\s*([0-9-\.]+)\]([^\[]*)$/,
					responseText    = self.xhr.responseText;

				// Update download progress
				if (progressPattern.test(responseText)) {
					var result = progressPattern.exec(responseText);

					self.fileSize = result[1];
					self.downloadSpeed = result[3];

					self.progress(self.fileSize, result[2], self.downloadSpeed);
				}

				if (completePattern.test(responseText)) {
					self.complete(completePattern.exec(responseText)[1]);

					// Stop polling  
					clearInterval(self.polling);
					return;
				}
			}, 300);
		},

		/**
		 * Send a request to download server to stop download
		 * process
		 * 
		 * @return  void
		 */
		stop: function () {
			this.stopXhr = null;

			try { this.stopXhr = new ActiveXObject("Msxml2.XMLHTTP");    } catch(e) {}
			try { this.stopXhr = new ActiveXObject("Microsoft.XMLHTTP"); } catch(e) {}
			try { this.stopXhr = new XMLHttpRequest();                   } catch(e) {}
			
			if (this.stopXhr == null)
				return;

			this.stopXhr.open('GET', 'stop.php?process=' + this.processId);
			this.stopXhr.send(null);
		},

		/**
		 * Returns the rounded value of number to specified
		 * precision (number of digits after the decimal point).
		 * precision can also be negative or zero (default).
		 * 
		 * @param   float   value      Number to be rounded
		 * @param   int     precision  Number of digits after the decimal point
		 * @param   string  mode       One of PHP_ROUND_HALF_UP,PHP_ROUND_HALF_DOWN, PHP_ROUND_HALF_EVEN, or PHP_ROUND_HALF_ODD
		 * 
		 * @return  float
		 */
		round: function (value, precision, mode) {
			var m, f, isHalf, sgn;
			
			precision |= 0;
			m = Math.pow(10, precision);
			value *= m;
			sgn = (value > 0) | -(value < 0);
			isHalf = value % 1 === 0.5 * sgn;
			f = Math.floor(value);

			if (isHalf) {
				switch (mode) {
					case 'PHP_ROUND_HALF_DOWN': value = f + (sgn < 0); break;
					case 'PHP_ROUND_HALF_EVEN':	value = f + (f % 2 * sgn); break;
					case 'PHP_ROUND_HALF_ODD' : value = f + !(f % 2); break;
					default: value = f + (sgn > 0);
				}
			}

			return (isHalf ? value : Math.round(value)) / m;
		},

		/**
		 * Convert number of bytes into human readable format
		 *
		 * @param   int  bytes      Number of bytes to convert
		 * @param   int  precision  Number of digits after the decimal separator
		 * 
		 * @return  string
		 */
		convert: function (size, precision) {
			var units = ['B', 'KB', 'MB', 'TB'],
				currentUnit = 0,
				calculatedSize = size;
			
			while (calculatedSize > 1024 && units[currentUnit] !== undefined) {
				calculatedSize = calculatedSize / 1024;
				currentUnit++;
			}

			return {
				size: this.round(calculatedSize, precision),
				unit: units[currentUnit]
			}
		}
	}
})(window, document)
