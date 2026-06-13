# Sewn In Customize Profiles

Wordpress plugin that removes a lot of the stuff on user profiles that never really gets used and just makes the interface confusing for new users.

## Filters to turn items back on

```php

// don't remove personal options at the top.
apply_filters( 'customize_wordpress_profiles/personal_options', true );

// don't remove username
add_filter( 'customize_wordpress_profiles/user_login', '__return_false' );

// don't remove role
add_filter( 'customize_wordpress_profiles/role', '__return_false' );

// don't remove first name
add_filter( 'customize_wordpress_profiles/first_name', '__return_false' );

// don't remove last name
add_filter( 'customize_wordpress_profiles/last_name', '__return_false' );

// don't remove nickname
add_filter( 'customize_wordpress_profiles/nickname', '__return_false' );

// don't remove display name
add_filter( 'customize_wordpress_profiles/display_name', '__return_false' );

// remove biography
add_filter( 'customize_wordpress_profiles/description', '__return_true' );

// don't remove email
add_filter( 'customize_wordpress_profiles/email', '__return_false' );

// don't remove website
add_filter( 'customize_wordpress_profiles/url', '__return_false' );

// don't remove social
add_filter( 'customize_wordpress_profiles/aim', '__return_false' );
add_filter( 'customize_wordpress_profiles/yim', '__return_false' );
add_filter( 'customize_wordpress_profiles/jabber', '__return_false' );

```