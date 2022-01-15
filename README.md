# Kirby Image Crop Field

This plugin provides a field for cropping images visually and very flexibly.

![kirby-plugin-image-crop-field](https://user-images.githubusercontent.com/10421363/59161680-0b683280-8ae6-11e9-8bc4-5b9145f34387.gif)

The field is based on [vue-cropperjs](https://github.com/Agontuk/vue-cropperjs
) and [gumlet/php-image-resize](https://github.com/gumlet/php-image-resize).

## Installation

Use one of the alternatives below.

### Download

Download and copy this repository to `/site/plugins/kirby-plugin-image-crop-field.

### Git submodule

```
git submodule add https://github.com/steirico/kirby-plugin-image-crop-field.git site/plugins/kirby-plugin-image-crop-field
```

### Composer

```
composer require steirico/kirby-plugin-image-crop-field
```

## Usage

### File Blueprint Usage

The plugin defines the new field type `imagecrop` which can be used in [file blueprints](https://getkirby.com/docs/reference/panel/blueprints/file).
Define an appropriate file blueprint for images and add the field as follow:

>   `/site/blueprints/files/image.yml`:
>   ```yaml
>   fields:
>     crop:
>       label: Image Crop
>       type: imagecrop
>       minSize:
>         width: 700
>         height: 250
>       targetSize:
>         width: 1400
>         height: 500
>       preserveAspectRatio: true
>   ```

This plugin also provides a dynamically generated blueprint to display crop zones for [Kirby's default thumbs](https://getkirby.com/docs/reference/system/options/thumbs#presets).
>   `/site/blueprints/files/image.yml`:
>   ```yaml
>   fields:
>     defaultthumbcropzones:
>       type: group
>       extends: fields/defaultkirbythumbscropfields
>   ```

### Plugin Options
There are a few options available to configure the output of the dynamic blueprint `defaultkirbythumbscropfields`.
- `thumbscropfields.fieldtitle`    (string)       : The title of the default thumbnail crop zones.
- `thumbscropfields.provideselect` (bool)         : Enable to include a select field to select the thumb crop zone to show.
- `thumbscropfields.providetabs`   (bool)         : Enable to include a tabs field to select the thumb crop zone to show. Needs https://github.com/Daandelange/kirby3-TabsField
- `thumbscropfields.whenfield`     (string|false) : A field name that holds the value of the thumb preset to show : "thumb_{{THUMBNAME}}" (ex: thumb_default). If false, no when condition is set and the fields are all visible at once.
- `thumbscropfields.thumbnames`    (array)        : An array with thumb preset names as keys and their respective values as values.

### Blueprint Options

#### `minSize`

Defines the minimum allowed size of the area that can be cropped from the original image.

* `width`: Minimum allowed width, bigger or equal to `1`
* `height`: Minimum allowed height, bigger or equal to `1`

Defaults:

* `width`: `1`
* `height`: `1`


#### `targetSize`

Target size of the image after it has been cropped. The resulting image will be scaled to `width` as defined by `targetSize.width` and `height` as defined by `targetSize.height`.

* `width`: Width of the target image, bigger or equal to `1`
* `height`: Minimum allowed height, bigger or equal to `1`

For both, `width` and `height`, negative values are interpreted as absolute values. 

Defaults: The resulting image represents the cropped area and is not scaled.

#### `preserveAspectRatio`

Whether to preserve the aspect ratio of the crop area as defined by `minSize.width / minSize.height` or to allow free cropping:

* `true`: Preserve aspect ratio
* `false`: Free cropping

Default: `false`

### Cropped Image in the Panel

The plugin provides the [file method](https://getkirby.com/docs/reference/plugins/extensions/file-methods) called `croppedImage`. Applied as any other file method, `croppedImage` provides a `file` object of the cropped version of origin image.

The following configuration previews the cropped image in a `files sections`:

>   `/site/blueprints/pages/album.yml`:
>   ```yaml
>   title: Album
> 
>   sections:
>     images:
>       type: files
>       layout: cards
>       template: image
>       info: "{{ file.dimensions }}"
>       image:
>         ratio: 16/9
>         cover: false
>         query: file.croppedImage
>       min: 1
>       size: small
>```

### Use Cropped Image in Templates and Snippets

Use the the [file method](https://getkirby.com/docs/reference/plugins/extensions/file-methods) called `croppedImage` in order to work with the cropped image in templates and snippets:

>   ```html
>   <figure>
>     <?= $page->image()->croppedImage() ?>
>   </figure>

Or alternatively, get the crop zone for a specific thumb preset.
>   ```html
>   <div>
>     <?php dump($page->image()->cropZoneForThumbPreset('default')) ?>
>   </div>


## Issues

Feel free to file an [issue](https://github.com/steirico/kirby-plugin-image-crop-field/issues) if you encounter any problems or unexpected behaviour.
  
Currently there is a know issue that cropped images appear twice when getting images by `$page->images()`. 

## License

MIT

## Credits

* [Rico Steiner](https://github.com/steirico)
* [vue-cropperjs](https://github.com/Agontuk/vue-cropperjs)
* [gumlet/php-image-resize](https://github.com/gumlet/php-image-resize)
