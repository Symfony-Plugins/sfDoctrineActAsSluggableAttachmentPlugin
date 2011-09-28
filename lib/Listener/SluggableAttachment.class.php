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

  public function preUpdate(Doctrine_Event $event)
  {
    parent::postUpdate($event);
    if (array_key_exists($event->getInvoker()->getAttachmentFieldName("filename"), $event->getInvoker()->getModified(true)))
    {
      $event->getInvoker()->deleteAttachment(true);
    }
  }

  public function postDelete(Doctrine_Event $event)
  {
    parent::postDelete($event);
    $event->getInvoker()->deleteAttachment();
  }

  public function postUpdate(Doctrine_Event $event)
  {
    
  }

}
