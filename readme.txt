=== Testimonials by WooThemes ===
Contributors: woothemes, mattyza, jameskoster
Donate link: http://woothemes.com/
Tags: testimonials, widget, shortcode, template-tag, feedback, customers
Requires at least: 3.4.2
Tested up to: 3.5-beta2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show off what your customers are saying about your business and how great they say you are, using our shortcode, widget or template tag.

== Description ==

"Testimonials by WooThemes" is a clean and easy-to-use testimonials management system for WordPress. Load in what your customers are saying about your business, and display the testimonials via a shortcode, widget or template tag on your website.

=== Usage ===

To display your testimonials via a theme or a custom plugin, please use the following code:

`<?php do_action( 'woothemes_testimonials' ); ?>`

To add arguments to this, please use any of the following arguments, using the syntax provided below:

* 'limit' => 5 (the maximum number of items to display)
* 'orderby' => 'menu_order' (how to order the items - accepts all default WordPress ordering options)
* 'order' => 'DESC' (the order direction)
* 'id' => 0 (display a specific item)
* 'display_author' => true (whether or not to display the author information)
* 'display_url' => true (whether or not to display the URL information)
* 'echo' => true (whether to display or return the data - useful with the template tag)
* 'size' => 50 (the pixel dimensions of the image)
* 'title' => '' (an optional title)

`<?php do_action( 'woothemes_testimonials', array( 'limit' => 10, 'display_author' => false ) ); ?>`

The same arguments apply to the shortcode which is `[woothemes_testimonials]` and the template tag, which is `<?php woothemes_testimonials(); ?>`.

== Installation ==

Installing "Testimonials by WooThemes" can be done either by searching for "Testimonials by WooThemes" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org.
1. Upload the ZIP file through the "Plugins > Add New > Upload" screen in your WordPress dashboard.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action( 'woothemes_testimonials' ); ?>` in your templates, or use the provided widget or shortcode.

== Frequently Asked Questions ==

= The plugin looks unstyled when I activate it. Why is this? =

"Testimonials by WooThemes" is a lean plugin that aims to keep it's purpose as clean and clear as possible. Thus, we don't load any preset CSS styling, to allow full control over the styling within your theme or child theme.

= How do I contribute? =

We encourage everyone to contribute their ideas, thoughts and code snippets. This can be done by forking the [repository over at GitHub](http://github.com/woothemes/testimonials/).

== Screenshots ==

1. The testimonials management screen within the WordPress admin.

== Upgrade Notice ==

= 1.0.0 =
* Initial release. Woo!

== Changelog ==

= 1.0.0 =
* Initial release. Woo!