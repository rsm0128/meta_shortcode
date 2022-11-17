# WordPress Meta Shortcode Plugin
When you build layout template with builder, it's needed to render meta value in some cases. Works well with metabox.io or ACF plugin.

## Shortcodes
This plugin provides 3 shortcodes `[meta_value]`, `[user_meta]`, and `[author_meta]`.

### [meta_value]
   This shortcode can be used to render post meta value on single post or archive page template.
   - Attributes: **name**, **type**, **index**, **post_id**
     - **name** (Required): Meta key text
     - **type** (Optional): Meta value type. It can be one of the values of `image`, `terms`, `file`.
     - **index** (Optional):For multiple value field, we can choose a value by index. It starts from 1. If index value is not specified for the multiple value field, it will be rendered as a comma separated string (for image type, just concatenation of img tags without comma)
     - **post_id** (Optional): Post ID. If omitted current post ID will be used.

### [user_meta]
   Shortcode to show user meta data.
   - Attributes: **name**, **type**, **index**, **user_id**
     - **name**: Required. Meta key text. Support `email`, `first_name`, `last_name`, and other custom user meta.
     - **user_id**: Required. User ID

### [author_meta]
   This shortcode can be used to render meta data for post authors.
   - Attributes: **name**, **type**, **index**

## Examples