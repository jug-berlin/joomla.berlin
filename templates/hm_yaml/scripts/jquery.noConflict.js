/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * (en) Code example for 'no-conflict' with other JS-Libraries (e.g. with mootools)
 * (de) Code beispiel für 'no-conflict' mit anderen JS-Bibliotheken (z.B. mit mootools)
 *
 * @version         $Id: jquery.noConflict.js 467 2008-07-27 16:52:23Z hieblmedia $
 * @copyright       Copyright 2005-2008, Reinhard Hiebl
 * @license         CC-A 2.0/JYAML-C(all media,html,css,js,...) and GNU/GPL(php), 
                    - see http://www.jyaml.de/en/license-conditions.html
 * @link            http://www.jyaml.de
 * @package         yamljoomla
 * @revision        $Revision: 467 $
 * @lastmodified    $Date: 2008-07-27 18:52:23 +0200 (So, 27. Jul 2008) $
*/

jQuery.noConflict();
(function($) { 
  $(function() {
    $(document).ready(function(){
      /* code using $ as alias to jQuery | Code zur benutzung von $ als Alias zu jQuery */                               
    });
  });
})(jQuery);
/**
 * (en) other code using $ as an alias to the other library (for example mootools)
 * (de) Anderer Code der $ benutzt als Alias zu anderen Bibliotheken (zum Beispiel Mootools)
*/