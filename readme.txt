=== 500px Widget ===
Contributors: kutsy
Tags: widget, 500px, photos, gallery, image, plugin
Requires at least: 3.0
Tested up to: 3.6
Stable tag: 0.8.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

500px Widget works only as a sidebar widget and will retrieve images (based on a criteria) hosted on the 500px.com service.

== Description ==

500px Widget works only as a sidebar widget and will retrieve images (based on a criteria) hosted on the 500px.com service.

No 500px API key is required to use this plugin.

You can choose photo streams: 

* Popular Photos
* Upcoming Photos
* Editors' Choice Photos
* Fresh Today Photos
* Fresh Yesterday Photos
* Fresh This Week Photos
* User Photos
* User Friends Photos
* User Favorites Photos
* Tag Photos.

Also you can sort photo streams by: 

* Time of upload (Most recent first)
* Rating (Highest rated first)
* View count (Most viewed first)
* Votes count (Most voted first)
* Favorites count (Most favorited first)
* Comments count (Most commented first)
* Original date (Most recent first)

And you can filter results by one of 28 categories: Abstract, Animals, Black and White, Celebrities, City and Architecture, Commercial, Concert, Family, Fashion, Film, Fine Art, Food, Journalism, Landscapes, Macro, Nature, Nude, People, Performing Arts, Sport, Still Life, Street, Transporation, Travel, Underwater, Urban Exploration, Wedding and Uncategorized.

Also you can customize displaying elements by using predefined placeholders, such as `{photo_title}`, `{photo_url}`, `{photo_image_url}`, `{photo_width}` and `{photo_height}`.

== Installation ==

1. Upload `500px-widget` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the "Plugins" menu in WordPress
1. Use "500px Widget" from Widgets Management

== Screenshots ==

1. Widget's Settings
2. Widget's sample view at website

== Changelog ==

= 0.8.1 [2013-08-28] =
* Small fixes

= 0.8 [2013-08-28] =
* Added Custom CSS box. Now you can add your styles from Widget Management

= 0.7 [2013-05-14] =
* Display connection errors
* Fix some bugs

= 0.6 [2013-03-29] =
* Added customization of displaying elements by using predefined placeholders, such as `{photo_title}`, `{photo_url}`, `{photo_image_url}`, `{photo_width}` and `{photo_height}`. 

= 0.5 [2013-02-27] =
* Replaced `file_get_contents()` to `wp_remote_get()` for more flexibility.  

= 0.4 [2013-02-27] =
* Cache lifetime bug fix

= 0.3 [2013-02-20] =
* Every item is wrapped into `<span class="widget_500px_item">...</span>`
* Now you can wrap `title` into tags: `<a>`, `<b>`, `<strong>`, `<i>`, `<em>`, `<span>` and `<div>`
* Added categories in widget's filter
* Updated screenshots
* Fix some bugs

= 0.2 [2013-02-19] =
* Widget ID changes
* CSS class changes
* Fix bug on add widget from Widgets Management

= 0.1 [2013-02-18] =
* Initialization commit
