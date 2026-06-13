# ACF { Edit Title & Content

Can be used to edit the title and content of posts in front end forms.

## Compatibility

This add-on will work with:

* version 4 and up

## Installation

This add-on can be treated as a WP plugin.

### Install as Plugin

1. Copy the folder into your plugins folder
2. Activate the plugin via the Plugins admin page

## Use

**post_title**

Create a field with the name 'form_post_title' in your field group, and include your field group in your acf_form() on the front end.

**post_content**

Create a field with the name 'form_post_content' in your field group, and include your field group in your acf_form() on the front end.

**Use predefined field group**

There is a predefined field group provided so that you don't have to worry about adding the title and content to the meta fields in the backend:

```php
acf_form( array(
	'post_id' => 'new_post'
	'field_groups' => array('acf_post-title-content', 42)
) );
```

Where our field group is 'acf_post-title-content' and 42 represents a field group with other meta data for the post.

## Filters

* `acf/edit_title_content/title/title` Change the title of the post_title field in the predefined group. Default: "Title"
* `acf/edit_title_content/content/title` Change the title of the post_content field in the predefined group. Default: "Content"
* `acf/edit_title_content/title/name` Change the name of the post_title field to match your form. Default: form_post_title
* `acf/edit_title_content/content/name` Change the name of the post_content field to match your form. Default: form_post_content
* `acf/edit_title_content/content/type` Change the field type of the post_content. Default: wysiwyg
* `acf/edit_title_content/title/add` Return false to remove the title field from the predefined form. Default: true
* `acf/edit_title_content/content/add` Return false to remove the content field from the predefined form. Default: true