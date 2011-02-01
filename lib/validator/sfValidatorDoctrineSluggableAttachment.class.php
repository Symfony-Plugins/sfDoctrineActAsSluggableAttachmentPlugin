<?php

/**
 * sfValidatorDoctrineSluggableAttachment
 *
 * @author Sydney Moutia <sydney@akhann.com>
 */
class sfValidatorDoctrineSluggableAttachment extends sfValidatorFile
{
  protected $_record;

  public function  __construct($record, $options = array(), $messages = array())
  {
    $this->_record = $record;
    $default_options = array(
      'path' => $record->getAttachmentPath(),
      'validated_file_class' => 'sfValidatedDoctrineSluggableAttachment',
      'required' => $record->isAttachmentRequired(),
      'max_size' => $record->getAttachmentMaxSize(),
      'mime_types' => $record->getAttachmentMimeType()
    );
    $merged_options = array_merge($default_options, $options);
    parent::__construct($merged_options, $messages);
  }

  protected function doClean($value)
  {
    $validated = parent::doClean($value);
    $validated->setRecord($this->_record);
    return $validated;
  }
}
