<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id$
 * @copyright       Copyright 2005-2008, Reinhard Hiebl
 * @license         CC-A 2.0/JYAML-C(all media,html,css,js,...) and GNU/GPL(php), 
                    - see http://www.jyaml.de/en/license-conditions.html
 * @link            http://www.jyaml.de
 * @package         yamljoomla
 * @revision        $Revision$
 * @lastmodified    $Date$
*/

/* No direct access to this file | Kein direkter Zugriff zu dieser Datei */
defined( '_JEXEC' ) or die( 'Restricted access' );

if ( ! defined('modMainMenuXMLCallbackDefined') )
{
  function modMainMenuXMLCallback(&$node, $args)
  {
    $user  = &JFactory::getUser();
    $menu  = &JSite::getMenu();
    
    $active  = $menu->getActive();
    $path  = isset($active) ? array_reverse($active->tree) : null;
  
    if (($args['end']) && ($node->attributes('level') >= $args['end']))
    {
      $children = $node->children();
      foreach ($node->children() as $child)
      {
        if ($child->name() == 'ul') 
       {
          $node->removeChild($child);
        }
      }
    }
  
    if ($node->name() == 'ul') 
    {
      foreach ($node->children() as $child)
      {
        if ($child->attributes('access') > $user->get('aid', 0)) 
        {
          $node->removeChild($child);
        }
      }
    }
    
    /**
     * JYAML added
     * Set first and last link class for more configurable menu styling
    **/
    if ($node->name() == 'ul') 
    {  
      if ( count($node->children()) >= 2 ) 
      {
        $max = count($node->children())-1;
        $node->_children[0]->addAttribute('class', $node->attributes('class').' first_item');
        $node->_children[$max]->addAttribute('class', $node->attributes('class').' last_item');
      }
    }
    
    /**
     * JYAML added
     * For dynamic menutitles with linktype seperator
     * @doc http://www.jyaml.de/index.php?option=com_content&task=view&id=122&Itemid=629
    **/  
    if ( $node->name() == 'li' && count($node->children()) ) 
    {  
      $children = $node->children();
      $child = $children[0];
      if ($child->name() == 'span' && $child->attributes('class')=='separator') 
      {
        $node->addAttribute('class', $node->attributes('class').' menutitle titlelevel'.$node->attributes('level'));
      }
    }
    
    /**
     * JYAML added
     * Subnames for Menuitems
    **/
    $subsep = '##';
    if ( $node->name() == 'a' || $node->name() == 'span' ) 
    {    
      if ( isset($node->span) ) 
      {
        $span = $node->span[0];
        if ( strpos($span->data(), $subsep) !== false )
        {
          // get maintitle and subnames
          $title = $span->data();
          $subnames = explode($subsep,$title);
          
          // Set main title
          $node->span[0]->setData( trim(array_shift($subnames)).' ' );
          
          // add subnames to new spans
          $i=1;
          foreach($subnames as $value) {
            $child = $node->addChild('span');
            $child->setData( trim(array_shift($subnames)).' ' );
            $child->addAttribute('class', 'subname'.$i);
            $i++;
          }
          
        }
      }
    }
  
    if (($node->name() == 'li') && isset($node->ul)) {
      $node->addAttribute('class', $node->attributes('class').' parent');
    }
  
    if (isset($path) && (in_array($node->attributes('id'), $path) || in_array($node->attributes('rel'), $path)))
    {
      $node->addAttribute('class', $node->attributes('class').' active');
      
      /**
       * JYAML added
       * Get a Tag and add class for active link
       * Is important for apply to once element without sublevels
      **/
      if (isset($node->a)) {
        $node->a[0]->addAttribute('class', $node->a[0]->attributes('class').' active_link');
      }
      if (isset($node->span)) {
        $node->span[0]->addAttribute('class', $node->span[0]->attributes('class').' active_link');
      }
    }
    else
    {
      if (isset($args['children']) && !$args['children'])
      {
        $children = $node->children();
        foreach ($node->children() as $child) {
          if ($child->name() == 'ul') {
            $node->removeChild($child);
          }
        }
      }
    }
  
    if (($node->name() == 'li') && ($id = $node->attributes('id'))) 
    {
      if ($node->attributes('class')) {
        $node->addAttribute('class', $node->attributes('class').' item'.$id);
      } else {
        $node->addAttribute('class', 'item'.$id);
      }
    }
  
    if (isset($path) && $node->attributes('id') == $path[0]) 
    {
      /* Change 'id' to 'class' if you have more then a menu in same start-level */
      $node->addAttribute('id', 'current');
  
      /**
       * JYAML added
       * Get a Tag and add class for active link
       * Is important for apply to once element without sublevels
      **/
      if (isset($node->a)) {
        $node->a[0]->addAttribute('class', 'active_link current_link');
      }
    } else {
      $node->removeAttribute('id');
    }
    
    $node->removeAttribute('rel');
    $node->removeAttribute('level');
    $node->removeAttribute('access');
  }
  
  define('modMainMenuXMLCallbackDefined', true);
}

modMainMenuHelper::render($params, 'modMainMenuXMLCallback');

?>