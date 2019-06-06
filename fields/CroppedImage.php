<?php

class CroppedImage extends Kirby\CMS\File {

  private $original;
  private $cropField = null;
  private $cropData = null;

  const FIELD_TYPE = 'imagecrop';


  public function __construct($original) {
    $this->__debuginfo();
    $this->original = $original;
    $cropData = $this->getCropData();

    if(is_array($cropData)){      
      $w = A::get($cropData, "width", $original->width());
      $h = A::get($cropData, "height", $original->height());
      $x = A::get($cropData, "x", 0);
      $y = A::get($cropData, "y", 0);

      $originalParts = pathinfo($original->root());
      $croppedFileName = sprintf("%s-cropped-w%sh%s-x%sy%s.%s",
        F::safeName($originalParts['filename']),
        $w, $h, $x, $y,
        $original->extension()
      );
      $croppedPath = strtolower(dirname($original->root()));
      $croppedRoot = strtolower($croppedPath . '/' . $croppedFileName);

      $oldRoot = strtolower($croppedPath . '/' . F::safeName($original->filename()));
      $oldCropped = F::similar($oldRoot, "-cropped-*");

      $props = array(
        'root'      => $croppedRoot,
        'filename'  => $croppedFileName,
        'parent'    => $original->parent()
      );

      parent::__construct($props);

      if(!$this->exists()){
        if(!file_exists($croppedPath)){
          mkdir($croppedPath, 0770, true);
        }
        
        foreach($oldCropped as $old){
          F::remove($old);
        }
        
        $cropped = new \Gumlet\ImageResize($original->root());
        $cropped->freecrop($w, $h, $x, $y);
        $cropped->save($croppedRoot);
  
        $cropConfig = $this->getCropField();
        $targetSize = $cropConfig["targetSize"];

        if(is_array($targetSize)){
          $targetW = A::get($targetSize, "width");
          $targetH = A::get($targetSize, "height");
          if($targetW){
            $image = new \Gumlet\ImageResize($croppedRoot);
            $image->resizeToWidth($targetW, $allow_enlarge = true);
            $image->save($croppedRoot);
          }
    
          if($targetH){
            $image = new \Gumlet\ImageResize($croppedRoot);
            $image->resizeToHeight($targetH, $allow_enlarge = true);
            $image->save($croppedRoot);
          }
        }
      }
    } 
  }

  public function getCropData() {
    if($this->cropData){
      return $this->cropData;
    }

    if($this->original){
      $field = $this->getCropField();
      $fieldName = $field["name"];
      $this->cropData = $this->original->content()->{$fieldName}()->yaml();
      return $this->cropData;
    }

    return null;
  }

  public function getCropField() {
    if($this->cropField){
      return $this->cropField;
    }

    if($this->original){
      $fields = $this->original ->blueprint()->fields();

      foreach($fields as $field){
        if($field["type"] == CroppedImage::FIELD_TYPE){
          $this->cropField = $field;
          return $this->cropField;
        }
      }
    }

    return null;
  }

  public static function croppedImage($requestedFile) {
    $media = new CroppedImage($requestedFile);
    if($media->exists()){
        return $media;
    } else {
        return $requestedFile;
    } 
  }

  public function __debuginfo(): array {
    try {
      $parent = $this->toArray();
    } catch(Throwable $e) {
      $parent = [];
    }

    return array_merge($parent, [
      'original'  => $this->original,
      'cropField' => $this->cropField,
      'cropData'  => $this->cropData
    ]);
  }
}