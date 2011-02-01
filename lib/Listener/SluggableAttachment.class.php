<?php

/**
 * Doctrine_Template_Listener_SluggableAttachment
 *
 * @author Sydney Moutia <sydney@akhann.com>
 */
class Doctrine_Template_Listener_SluggableAttachment extends Doctrine_Template_Listener_Sluggable
{
  public function preInsert(Doctrine_Event $event)
  {
  }

  public function  preUpdate(Doctrine_Event $event)
  {
     parent::postUpdate($event);
    $event->getInvoker()->deleteAttachment(true);
  }

  public function  postDelete(Doctrine_Event $event)
  {
    parent::postDelete($event);
    $event->getInvoker()->deleteAttachment();
  }

  public function  postUpdate(Doctrine_Event $event)
  {
   
  }
}
