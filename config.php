<?php
	
// Format a series of image-crop-fields using Kirby's default Thumbs presets
$thumbPresets = kirby()->option('thumbs.presets',[]);
$cropZonesBlueprint = [];
$cropZonesOptions = [];
{
	// Get options
	// Note: At this point user settings have been loaded, but not the plugin defaults (they are below). So make sure to provide the correct defaults here "hardcoded".
	$provideSelect = kirby()->option('steirico.kirby-plugin-image-crop-field.thumbscropfields.provideselect', false)?true:false;
	$provideTabs =   kirby()->option('steirico.kirby-plugin-image-crop-field.thumbscropfields.providetabs', false)?true:false;
	$whenFieldName = kirby()->option('steirico.kirby-plugin-image-crop-field.thumbscropfields.whenfield', false);
	$thumbNames =    kirby()->option('steirico.kirby-plugin-image-crop-field.thumbscropfields.thumbnames', ['default' => 'Default Thumbnail', 'cover' => 'Cover Image']);
	
	// Sanitize options
	if(!is_array($thumbNames)) $thumbNames = [];
	if(!is_string($whenFieldName)) $whenFieldName = false;
	if($provideSelect){
		$whenFieldName = 'thumbscropfieldsselector';
		$provideTabs = false;
	}
	if($provideTabs){
		// Only when the plugin is available !
		if( array_key_exists('daandelange/tabsfield', kirby()->plugins()) ) $whenFieldName = 'thumbscropfieldstabs';
		else $provideTabs = false;
	}
	
	// Format data
	if($thumbPresets) foreach($thumbPresets as $key => $p){
	  // todo: skip if $key='default' ?
	  $cropZonesBlueprint['thumb_'.$key]=[
	    'type'          => 'imagecrop',
	    'label'         => ($whenFieldName?'':(array_key_exists($key, $thumbNames)?$thumbNames[$key]:('Crop zone for thumbnail '.$key) )),
	    'translate'     => false,
	    'minSize' => [
	      'width'   => $p['width'] ?? null,
	      'height'  => $p['height'] ?? null
	    ],
	    'preserveAspectRatio' => (isset($p['width']) && isset($p['height'])),
	    'when'        	=> $whenFieldName?[
	      $whenFieldName	=> 'thumb_'.$key,
	    ]:false,
	  ];
	  if($provideSelect || $provideTabs ){
		  $thumbTitle = (array_key_exists($key, $thumbNames)?$thumbNames[$key]:ucfirst($key));
	    $cropZonesOptions['thumb_'.$key] = [
	      'name'      => 'thumb_'.$key,  // Key for tabs
	      'value'     => 'thumb_'.$key,  // Key for select
	      'text'      => $thumbTitle,    // Title for select
	      'label'     => $thumbTitle,    // Title for tabs
	      'icon'      => 'image',        // For tabs only
	    ];
	  }
	}
}

Kirby::plugin('steirico/kirby-plugin-image-crop-field', [
    'fileMethods' => [
        'croppedImage' => function() {
            return CroppedImage::croppedImage($this);
        },
        'cropZoneForThumbPreset' => function($preset) {
	        	// Query thumb presets
	        	$thumbPresets = kirby()->option('thumbs.presets',[]);
	        	if($thumbPresets) foreach($thumbPresets as $key => $p){
		        	if($key===$preset){//dump($this);
			        	if($this->content()->has('thumb_'.$preset)){
				        $cropZone = $this->{'thumb_'.$preset}();
								//dump($cropZone);die();				        
				        	if( $cropZone && $cropZone->isNotEmpty() ){
				        		return $cropZone->yaml()??null;
				        	}
				        //	else return null;
			        	}
		        	}
	        	}
          return null;
        },
    ],
    'fields' => [
        'imagecrop' => [
            'props' => [
                'image' => function() {
                    return $this->model()->url();
                },

                'minSize' => function(array $minSize = []) {
                    $width = max(A::get($minSize, 'width', 1), 1);
                    $height = max(A::get($minSize, 'height', 1), 1);
                    return array(
                        'width' => $width,
                        'height' => $height
                    ); 
                },

                'targetSize' => function(array $targetSize = []) {
                    return $targetSize; 
                },

                'preserveAspectRatio' => function(bool $preserveAspectRatio = false){
                    return $preserveAspectRatio;
                },

                'value' => function($value = []){
                    $method = kirby()->request()->method();
                    if(($method == "PATCH") || ($method == "POST")) {
                        new CroppedImage($this->model());
                    }

                    if(is_array($value)){
                        // Not sure how to make this an object properly. Returning an array throws a vue type-warning in the panel when in panel develop mode.
                        // return (object) $value;
                        return $value;
                    } else {
                        $val = Data::decode($value, 'yaml');
                        if(is_array($val)) $val = (object)$val;//json_decode(json_encode($val), FALSE);
                        return $val;
                    }
                }
            ]
        ],
    ],
    'hooks' => [
        'file.delete:before' => function ($file) {
            $croppedImage = $file->croppedImage();
            if ($croppedImage->exists()) {
                $croppedImage->delete();
            }
        }
    ],
    'blueprints' => [
	    // An optional dynamic blueprint to serve a cropzone for each kirby thumb preset
	    // Note: Available since Kirby 3.6 but they used to work in a 3.5 installation too !
	    'fields/defaultkirbythumbscropfields' => [
        'type' => 'group', // Note: Not the best way since K3.6, but a group also works dynamically in K3.5 (and below?) !
        'fields' => A::merge([
          'thumbscropfieldstitle' => [
              'type'      => 'headline',
              'label'     => kirby()->option('steirico.kirby-plugin-image-crop-field.thumbscropfields.title', 'Default Thumbs Crop Zones'),
              'numbered'  => false,
          ],
          'thumbscropfieldsselector' => $provideSelect?[
	          'type'		=> 'select',
	          'label' => 'Select a thumb to edit its crop zone',
	          'options' => $cropZonesOptions,
          ]:false,
          'thumbscropfieldstabs' => $provideTabs?[
	          'type'		=> 'tabs',
	          'tabs' => array_values($cropZonesOptions),
          ]:false,
        ], $cropZonesBlueprint),
			],
		],
		'options' => [
			// Settings for the defaultkirbythumbscropfields field
			'thumbscropfields' => [
				'fieldtitle'        => 'Default Thumbs Crop Zones',
				'provideselect'     => false, // Enable to include a select field to select the thumb cropzone to show.
				'providetabs'       => false, // Enable to include a tabs field to select the thumb cropzone to show. Needs https://github.com/Daandelange/kirby3-TabsField
				'whenfield'         => false, // A fieldname that holds the value of the thumb preset to show : "thumb_{{THUMBNAME}}" (ex: thumb_default). If false, no when condition is set and the fields are all visible at once.
				'thumbnames'        => [      // An array with thumb preset names as keys and their respective values as values.
					'default' => 'Default Thumbnail',
					'cover'   => 'Cover Image',
					'icon'    => 'Icon',
				],
			],
		],
]);