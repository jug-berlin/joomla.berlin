<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: default.php 467 2008-07-27 16:52:23Z hieblmedia $
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
?>

<form name="form2" method="post" action="index.php" class="mod_poll">
  <fieldset>
    <legend><?php echo $poll->title; ?></legend>
    <?php for ($i = 0, $n = count($options); $i < $n; $i++) : ?>
    <input type="radio" name="voteid" id="voteid<?php echo $options[$i]->id; ?>" value="<?php echo $options[$i]->id; ?>" alt="<?php echo $options[$i]->id; ?>" />
    <label for="voteid<?php echo $options[$i]->id; ?>">
      <?php echo $options[$i]->text; ?>
    </label>
    <br />
    <?php endfor; ?>
    <br />
    <input type="submit" name="task_button" class="button float_left" value="<?php echo JText::_('Vote'); ?>" />
    <a class="float_right" href="<?php echo JRoute::_('index.php?option=com_poll&id='.$poll->slug.$itemid.'#content'); ?>"><?php echo JText::_('Results'); ?></a>
  </fieldset>

  <input type="hidden" name="option" value="com_poll" />
  <input type="hidden" name="id" value="<?php echo $poll->id; ?>" />
  <input type="hidden" name="task" value="vote" />
  <input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
