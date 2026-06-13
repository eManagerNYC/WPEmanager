# ACF { Signature

A very simple Advanced Custom Fields add-on that creates a signature field using https://github.com/szimek/signature_pad.


### Compatibility

This add-on will work with:

* version 4 and up
* NOT version 3 and bellow


### Installation

This add-on can be treated as both a WP plugin.

**Install as Plugin**

1. Copy the folder into your plugins folder
2. Activate the plugin via the Plugins admin page

**Include within theme**

1.	Copy the folder into your theme folder (can use sub folders). You can place the folder anywhere inside the 'wp-content' directory
2.	Edit your functions.php file and add the code below (Make sure the path is correct to include the acf-signature.php file)

```php
include_once('acf-signature/acf-signature.php');
```