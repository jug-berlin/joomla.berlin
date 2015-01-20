<?php 
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: default_graph.php 467 2008-07-27 16:52:23Z hieblmedia $
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

<dl class="poll">
  <dt><?php echo JText::_( 'Number of Voters' ); ?></dt>
  <dd><?php echo $this->votes[0]->voters; ?></dd>
  <dt><?php echo JText::_( 'First Vote' ); ?></dt>
  <dd><?php echo $this->first_vote; ?></dd>
  <dt><?php echo JText::_( 'Last Vote' ); ?></dt>
  <dd><?php echo $this->last_vote; ?></dd>
</dl>

<h2>
  <?php echo $this->poll->title; ?>
</h2>

<table class="pollstableborder">
  <tr>
    <th id="itema" class="td_1"><?php echo JText::_( 'Hits' ); ?></th>
    <th id="itemb" class="td_2"><?php echo JText::_( 'Percent' ); ?></th>
    <th id="itemc" class="td_3"><?php echo JText::_( 'Graph' ); ?></th>
  </tr>
  <?php for ( $row = 0; $row < count( $this->votes ); $row++ ) :
    $vote = $this->votes[$row];
  ?>
  <tr>
    <td colspan="3" id="question<?php echo $row; ?>" class="question">
      <?php echo $vote->text; ?>
    </td>
  </tr>
  <tr class="sectiontableentry<?php echo $vote->odd; ?>">
    <td headers="itema question<?php echo $row; ?>" class="td_1">
      <?php echo $vote->hits; ?>
    </td>
    <td headers="itemb question<?php echo $row; ?>" class="td_2">
      <?php echo $vote->percent.'%' ?>
    </td>
    <td headers="itemc question<?php echo $row; ?>" class="td_3">
      <div class="<?php echo $vote->class; ?>" style="height:<?php echo $vote->barheight; ?>px;width:<?php echo $vote->percent; ?>% !important"></div>
    </td>
  </tr>
  <?php endfor; ?>
</table>
