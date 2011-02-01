<?php

/**
 * sfDoctrineSluggableAttachmentRefreshThumbnailsTask
 *
 * @author Sydney Moutia <sydney@akhann.com>
 */
class sfDoctrineSluggableAttachmentRefreshThumbnailsTask extends sfDoctrineBaseTask
{

  protected function configure()
  {
    parent::configure();

    $this->addArguments(array(
      new sfCommandArgument('model', sfCommandArgument::REQUIRED, 'The model'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true)
    ));

    $this->namespace = 'sluggable-attachment';
    $this->name = 'refresh-thumbnails';
    $this->briefDescription = 'Re-Create thumbnail for given model';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $model = $arguments['model'];
    $databaseManager = new sfDatabaseManager($this->configuration);
    $objects = Doctrine::getTable($model)->findAll();
    foreach ($objects as $object)
    {
      if ($object->isAttachmentImage())
      {
        if (is_dir($object->getAttachmentPath()))
        {
          $tempName = $object->getAttachmentPath() . $object->getFullFilename();
          try
          {
            $object->refreshThumbnail();
          }
          catch (sfImageTransformException $e)
          {
            $this->log("Error: cannot find file: $tempName");
          }
        }
        else
        {
          $this->log('Error: cannot open dir: ' . $object->getAttachmentPath());
        }
      }
      else
      {
          $this->log('Error: Attachment is not declared as image');
      }
    }
    $this->log('done.');
  }

}
