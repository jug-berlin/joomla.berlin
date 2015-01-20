<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: modules.php 467 2008-07-27 16:52:23Z hieblmedia $
 * @copyright       Copyright 2005-2008, Reinhard Hiebl
 * @license         CC-A 2.0/JYAML-C(all media,html,css,js,...) and GNU/GPL(php), 
                    - see http://www.jyaml.de/en/license-conditions.html
 * @link            http://www.jyaml.de
 * @package         yamljoomla
 * @revision        $Revision: 467 $
 * @lastmodified    $Date: 2008-07-27 18:52:23 +0200 (So, 27. Jul 2008) $
*/

/* No direct access to this file | Kein direkter Zugriff zu dieser Datei */
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * This is a file to add template specific chrome to module rendering.  To use it you would
 * set the style attribute for the given module(s) include in your template to use the style
 * for each given modChrome function.
 *
 * eg.  To render a module mod_test in the sliders style, you would use the following include:
 * <jdoc:include type="module" name="test" style="slider" />
 *
 * This gives template designers ultimate control over how modules are rendered.
 *
 * NOTICE: All chrome wrapping methods should be named: modChrome_{STYLE} and take the same
 * three arguments.
 */


/**
 * Custom module chrome, echos the whole module in a <div> and the header in <h{x}>. The level of
 * the header can be configured through a 'headerLevel' attribute of the <jdoc:include /> tag.
 * Defaults to <h3> if none given
 */
function modChrome_jyaml($module, &$params, &$attribs)
{
  $headerLevel = isset($attribs['headerLevel']) ? $attribs['headerLevel'] : '3';
  $cssId = isset($attribs['cssId']) ? ' id="'.$attribs['cssId'].'"' : '';
    
  if (!empty ($module->content)) : ?>
    <div<?php echo $cssId; ?> class="floatbox module<?php echo $params->get('moduleclass_sfx'); ?>">
      <?php if ($module->showtitle) : ?>
        <?php echo '<h'.$headerLevel.'>'; ?><?php echo $module->title; ?><?php echo '</h'.$headerLevel.'>'; ?>
      <?php endif; ?>
      <?php echo $module->content; ?>
    </div>
  <?php endif;
}

?>