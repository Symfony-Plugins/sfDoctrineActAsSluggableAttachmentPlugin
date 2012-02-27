<?php

/**
 * sfValidatedDoctrineSluggableAttachment
 *
 * @author Sydney Moutia <sydney@akhann.com>
 */
class sfValidatedDoctrineSluggableAttachment extends sfValidatedFile
{
  protected $_record;

  public function setRecord($record)
  {
    $this->_record = $record;
  }

  public function save($file = null, $fileMode = 0666, $create = true, $dirMode = 0777)
  {
    $this->_record->setAttachment($this->getOriginalName(), $this->getExtension());

    if ($this->_record->isAttachmentImage())
    {
      $this->_record->attachImage($this->getTempName(), $this->getPath());
    }
    else
    {
      parent::save($this->_record->getFullFilename(), $fileMode, $create, $dirMode);
    }
    
    return $this->_record->getAttachmentField("filename");
  }
  
  public function getExtension($default = '')
  {
    preg_match("/\.([^\.]+)$/", $this->originalName, $matches);  
    return reset($matches);
  }
}