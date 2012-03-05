<?php

/**
 * Doctrine_Template_SluggableAttachment
 *
 * @author Sydney Moutia <sydney@akhann.com>
 */
class Doctrine_Template_SluggableAttachment extends Doctrine_Template
{

  /**
   * SluggableAttachment options
   */
  protected $_options = array(
    "name" => "attachment",
    "fields" => array(
      "filename" => array(
        "postfix" => "_filename",
        "type" => "string",
        "size" => 255,
        "options" => array("notnull" => true, "unique" => false)),
      "extension" => array(
        "postfix" => "_extension",
        "type" => "string",
        "size" => 255,
        "options" => array("notnull" => true, "unique" => false))
    ),
    'builder' => array('Doctrine_Inflector', 'urlize'),
    "max_size" => "5242880",
    "image" => true,
    "image_original_dir" => "original",
    "mime_type" => array(),
    "required" => true
  );

  protected $_image_mime_type = array(
    'image/pjpeg',
    'image/jpeg',
    'image/png',
    'image/x-png',
    'image/gif',
  );
  protected $_listener = null;

  public function setTableDefinition()
  {
    parent::setTableDefinition();
    $name = $this->_options["name"];
    $filename = $this->getAttachmentFieldName("filename");
    $extension = $this->getAttachmentFieldName("extension");

    $this->hasColumn($filename, $this->_options["fields"]["filename"]["type"],
      $this->_options["fields"]["filename"]["size"],
      $this->_options["fields"]["filename"]["options"]);
    $this->hasColumn($extension, $this->_options["fields"]["extension"]["type"],
      $this->_options["fields"]["extension"]["size"],
      $this->_options["fields"]["extension"]["options"]);

    $listener_options = array_merge($this->_options, array(
        "name" => $filename,
        "uniqueBy" => array($extension)
      ));

    $this->_listener = new Doctrine_Template_Listener_SluggableAttachment(
        $listener_options);
    $this->addListener($this->_listener);
  }

  public function getAttachmentField($field)
  {
    $field_name = $this->getAttachmentFieldName($field);
    $record = $this->getInvoker();
    return $record->$field_name;
  }

  public function setAttachment($filename, $extension)
  {
    $field_filename = $this->getAttachmentFieldName("filename");
    $field_extension = $this->getAttachmentFieldName("extension");
    $record = $this->getInvoker();
    $record->$field_extension = $extension;
    $record->$field_filename = $this->getUniqueSlug($filename, $extension);
  }

  public function getFullFilename($old=false)
  {
    if ($old)
    {
      $old_record = $this->getInvoker()->getModified($old);
      $filename = isset($old_record[$this->getAttachmentFieldName("filename")]) ?
        $old_record[$this->getAttachmentFieldName("filename")] :
        $this->getAttachmentField("filename");
      $extension = isset($old_record[$this->getAttachmentFieldName("extension")]) ?
        $old_record[$this->getAttachmentFieldName("extension")] :
        $this->getAttachmentField("extension");
      return $filename.$extension;
    }
    else
    {
      return $this->getAttachmentField("filename") .
      $this->getAttachmentField("extension");
    }
  }

  public function getAttachmentMimeType()
  {
    if ($this->_options["image"] && empty($this->_options["mime_type"]))
    {
      return $this->_image_mime_type;
    }
    else
    {
      return $this->_options["mime_type"];
    }
  }

  public function getAttachmentMaxSize()
  {
    return $this->_options["max_size"];
  }

  public function getAttachmentPath($ignore_image=false)
  {
    $path = sfConfig::get('sf_upload_dir').DIRECTORY_SEPARATOR;
    $path .= sfInflector::underscore(get_class($this->getInvoker()));
    if ($this->_options["image"] && !$ignore_image)
    {
      $path .= DIRECTORY_SEPARATOR;
      $path .= $this->_options["image_original_dir"];
    }
    return $path;
  }

  public function isAttachmentRequired()
  {
    return $this->_options["required"];
  }

  public function isAttachmentImage()
  {
    return $this->_options["image"];
  }

  public function getAttachmentURL($style="")
  {
    $is_image = $this->_options["image"];
    $style = ($is_image && $style==null)? $this->_options["image_original_dir"] : $style;
    $style = $is_image ? "/".$style : "";
    $url = "/".basename(sfConfig::get('sf_upload_dir'))."/";
    $url .= sfInflector::underscore(get_class($this->getInvoker()));
    return $url.$style."/".$this->getFullFilename();
  }

  public function getAttachmentStyles()
  {
    $defaultStles = array($this->_options["image_original_dir"] => array(
        "thumbnailing" => "none"
      ));
    $models = sfConfig::get('app_actAsDoctrineSluggableAttachment_models');
    return array_merge($defaultStles, $models[sfInflector::underscore(get_class($this->getInvoker()))]);
  }

  public function attachImage($input_image, $path)
  {
    foreach($this->getAttachmentStyles() as $styleNane => $style)
    {
      $img = new sfImage($input_image);
      $directory = dirname($path).DIRECTORY_SEPARATOR.$styleNane;
      $this->checkImageDirectory($directory);
      if ($style["thumbnailing"] != "none")
      {
        $dimension = split('x', $style["size"]);
        if (!($style["force"] == false && $img->getWidth() < $dimension[0] && $img->getHeight() < $dimension[1]))
        {
          $img->thumbnail($dimension[0], $dimension[1], $style["thumbnailing"]);
        }
      }
      $img->saveAs($directory.DIRECTORY_SEPARATOR.$this->getFullFilename());
    }
  }

  public function refreshThumbnail()
  {
    $path = $this->getAttachmentPath();
    $this->attachImage($path.DIRECTORY_SEPARATOR.$this->getFullFilename(),
      $path);
  }

  public function deleteAttachment($old=false)
  {
    if ($this->isAttachmentImage())
    {
      foreach($this->getAttachmentStyles() as $style_name => $style)
      {
        $path = $this->getAttachmentPath(true).DIRECTORY_SEPARATOR.$style_name;
        $path .= DIRECTORY_SEPARATOR.$this->getFullFilename($old);
        if (file_exists($path)) {
          unlink($path);
        }
      }
    }
    else
    {
      $path = $this->getAttachmentPath(true);
      $path .= DIRECTORY_SEPARATOR.$this->getFullFilename($old);
      if (file_exists($path)) {
        unlink($path);
      }
    }
  }

  public function getAttachmentFieldName($field)
  {
    return $filename = $this->_options["name"] .
    $this->_options["fields"][$field]["postfix"];
  }

  protected function getUniqueSlug($filename, $extension)
  {
    $field_name = $this->getAttachmentFieldName("filename");
    $record = $this->getInvoker();

    $record->$field_name = $this->_listener->getUniqueSlug(
        $record, basename($filename, $extension));
    return $record->$field_name;
  }

  protected function saveImageAs($source, $type, $toPath, $to_filename=null, $toType = null, $fileMode = 0666, $dirMode = 0777)
  {
    $toType = ($toType == null) ? $type  : $toType;
    $to_filename = ($to_filename == null) ? basename($source) : $to_filename;

    $img = new sfImage($source, $type);
    $this->checkImageDirectory($toPath);
    $img->saveAs($toPath.DIRECTORY_SEPARATOR.$to_filename);
  }

  protected function checkImageDirectory($directory, $dirMode=0777)
  {
    if (!is_readable($directory))
    {
      if (!@mkdir($directory, $dirMode, true))
      {
        // failed to create the directory
        throw new Exception(sprintf('Failed to create file upload directory "%s".', $directory));
      }

      // chmod the directory since it doesn't seem to work on recursive paths
      chmod($directory, $dirMode);
    }

    if (!is_dir($directory))
    {
      // the directory path exists but it's not a directory
      throw new Exception(sprintf('File upload path "%s" exists, but is not a directory.', $directory));
    }

    if (!is_writable($directory))
    {
      // the directory isn't writable
      throw new Exception(sprintf('File upload path "%s" is not writable.', $directory));
    }
  }
}
