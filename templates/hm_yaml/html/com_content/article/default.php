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

<?php if (($this->user->authorize('com_content', 'edit', 'content', 'all') || $this->user->authorize('com_content', 'edit', 'content', 'own')) && !($this->print)) : ?>
<div class="contentpaneopen_edit<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
  <?php echo JHTML::_('icon.edit', $this->article, $this->params, $this->access); ?>
</div>
<?php endif; ?>

<div class="floatbox">
  <?php if ($this->print || $this->params->get('show_pdf_icon') || $this->params->get('show_print_icon') || $this->params->get('show_email_icon')) : ?>
    <p class="buttonheading">
      <?php if ($this->print) :
        echo JHTML::_('icon.print_screen', $this->article, $this->params, $this->access);
      elseif ($this->params->get('show_pdf_icon') || $this->params->get('show_print_icon') || $this->params->get('show_email_icon')) : ?>
      <?php if ($this->params->get('show_pdf_icon')) :
        echo JHTML::_('icon.pdf', $this->article, $this->params, $this->access);
      endif;
      if ($this->params->get('show_print_icon')) :
        echo JHTML::_('icon.print_popup', $this->article, $this->params, $this->access);
      endif;
      if ($this->params->get('show_email_icon')) :
        echo JHTML::_('icon.email', $this->article, $this->params, $this->access);
      endif;
      endif; ?>
    </p>
  <?php endif; ?>
    
  <?php if ($this->params->get('show_title')) : ?>
  <h1 class="contentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
    <?php if ($this->params->get('link_titles') && $this->article->readmore_link != '') : ?>
    <a href="<?php echo $this->article->readmore_link; ?>" class="contentpagetitle<?php echo $this->params->get('pageclass_sfx'); ?>">
      <?php echo $this->article->title; ?>
    </a>
    <?php else :
      echo $this->escape($this->article->title);
    endif; ?>
  </h1>
  <?php endif; ?>
</div>

<?php if (($this->params->get('show_section') && $this->article->sectionid) || ($this->params->get('show_category') && $this->article->catid)) : ?>
<p class="pageinfo">
  <?php if ($this->params->get('show_section') && $this->article->sectionid && isset($this->article->section)) : ?>
  <span>
    <?php if ($this->params->get('link_section')) : ?>
        <?php echo '<a href="'.JRoute::_(ContentHelperRoute::getSectionRoute($this->article->sectionid)).'">'; ?>
    <?php endif; ?>
    <?php echo $this->article->section; ?>
    <?php if ($this->params->get('link_section')) : ?>
        <?php echo '</a>'; ?>
    <?php endif; ?>
    <?php if ($this->params->get('show_category')) :
      echo ' - ';
    endif; ?>
  </span>
  <?php endif; ?>

  <?php if ($this->params->get('show_category') && $this->article->catid && isset($this->article->category)) : ?>
  <span>
    <?php if ($this->params->get('link_category')) : ?>
        <?php echo '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->article->catslug, $this->article->sectionid)).'">'; ?>
    <?php endif; ?>
    <?php echo $this->article->category; ?>
    <?php if ($this->params->get('link_category')) : ?>
        <?php echo '</a>'; ?>
    <?php endif; ?>
  </span>
  <?php endif; ?>
</p>
<?php endif; ?>



<?php if (!$this->params->get('show_intro')) :
  echo $this->article->event->afterDisplayTitle;
endif; ?>

<?php if ((!empty ($this->article->modified) && $this->params->get('show_modify_date')) || ($this->params->get('show_author') && ($this->article->author != "")) || ($this->params->get('show_create_date'))) : ?>
<p class="iteminfo">
  <?php if (!empty ($this->article->modified) && $this->params->get('show_modify_date')) : ?>
  <span class="modifydate">
    <?php echo JText::_('Last Updated').' ('.JHTML::_('date', $this->article->modified, JText::_('DATE_FORMAT_LC2')).')'; ?>
  </span>
  <?php endif;
  if (($this->params->get('show_author')) && ($this->article->author != "")) : ?>
  <span class="createdby">
    <?php echo JText::sprintf('Written by', ($this->article->created_by_alias ? $this->article->created_by_alias : $this->article->author)); ?>
  </span>
  <?php endif;
  if ($this->params->get('show_create_date')) : ?>
  <span class="createdate">
    <?php echo JHTML::_('date', $this->article->created, JText::_('DATE_FORMAT_LC2')); ?>
  </span>
  <?php endif; ?>
</p>
<?php endif; ?>

<?php echo $this->article->event->beforeDisplayContent; ?>

<?php if ($this->params->get('show_url') && $this->article->urls) : ?>
<span class="small">
  <a href="<?php echo $this->article->urls; ?>" target="_blank">
    <?php echo $this->article->urls; ?>
  </a>
</span>
<?php endif; ?>

<?php if (isset ($this->article->toc)) :
  echo $this->article->toc;
endif; ?>

<?php echo JFilterOutput::ampReplace($this->article->text); ?>

<?php echo $this->article->event->afterDisplayContent; ?>
