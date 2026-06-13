# Sewn In Remove WordPress Feeds

WordPress plugin that disables all WordPress feeds. Good for sites that don't use feeds. Can also be customized to only allow some feeds to still exists.

## Filters to allow certain feeds

```php

// customize array of feeds to remove. add more? remove some?
apply_filters( 'sewn_remove_feeds/all_feeds', 'custom_sewn_remove_feeds' );
function custom_sewn_remove_feeds( $feeds )
{
	unset( $feeds['rss'] );
	return $feeds;
}

// don't remove rss feed
apply_filters( 'sewn_remove_feeds/type=rss', '__return_false' );

```