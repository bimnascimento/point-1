# Pure CSS Switch Button for WP Bones

Pure CSS Switch Button for WordPress/WP Bones

[![Latest Stable Version](https://poser.pugx.org/wpbones/pure-css-switch/v/stable)](https://packagist.org/packages/wpbones/pure-css-switch)
[![Total Downloads](https://poser.pugx.org/wpbones/pure-css-switch/downloads)](https://packagist.org/packages/wpbones/pure-css-switch)
[![License](https://poser.pugx.org/wpbones/pure-css-switch/license)](https://packagist.org/packages/wpbones/pure-css-switch)

## Installation

You can install third party packages by using:

    $ php bones require wpbones/pure-css-switch
   
I advise to use this command instead of `composer require` because doing this an automatic renaming will done.  

You can use composer to install this package:

    $ composer require wpbones/pure-css-switch

You may also to add `"wpbones/pure-css-switch": "~1.0"` in the `composer.json` file of your plugin:
 
```json
  "require": {
    "php": ">=5.5.9",
    "wpbones/wpbones": "~0.8",
    "wpbones/pure-css-switch": "~1.0"
  },
```


and run 

    $ composer install
    
Alternatively, you can get the `src/resources/assets/less/wpbones-switch.less` and then compile it, or get directly the `src/public/css/wpbones-switch.css` files.    
Also, you can get pre-compiled minified version `src/public/css/wpbones-switch.min.css`.

## Development installation

Use `yarn` to install the development tools. Next, use `gulp --production` to compile the resources.


## Enqueue for Controller

You can use the provider to enqueue the styles.

```php
public function index()
{
  // enqueue the minified version
  PureCSSSwitchProvider::enqueueStyles();
  
  // ...
  
}
```

## PureCSSSwitchProvider

This is a static class autoloaded by composer. You can use it to enqueue or get the styles path:

```php
// enqueue the minified version
PureCSSSwitchProvider::enqueueStyles();

// enqueue the flat version
PureCSSSwitchProvider::enqueueStyles( false );
    
// return the absolute path of the minified css
PureCSSSwitchProvider::css();

// return the absolute path of the flat css
PureCSSSwitchProvider::css();   
```

## Mode

To default the switch works as on/off button. You can change the mode by seeting `mode` property,

```php
<?php echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'test-switch-7' )->mode( 'select' ); ?>
```

In the above example, you can use it as selector instead of on/off button.


## Theme

Of course, you can switch theme by using `theme` property ot its fluent version.
Currently, we support two theme:

* `flat-round` - default
* `flat-square`

You should use something lokk like:

```php
<?php echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'test-switch-7' )->theme( 'flat-square' ); ?>
```


## HTML markup

In your view you can use the `WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton` class

```html
  <p>
    <?php echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'test-switch-1' ); ?>
  </p>

  <p>
    <?php echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'test-switch-2' )->left_label( 'Swipe me' ); ?>
  </p>

  <p>
    <?php echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'test-switch-3' )->right_label( 'Swipe me' ); ?>
  </p>

  <p>
    <?php echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'test-switch-4' )->left_label( 'Swipe me' )->right_label( 'Swipe me' ); ?>
  </p>
  
  <p>
    <?php echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'test-switch-5' )->left_label( 'Swipe me' )->checked( true ) ?>
  </p>

  <p>
    <?php echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'test-switch-6' )->left_label( 'Swipe me' )->disabled( true ) ?>
  </p>
  
  <p>
    <?php echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'test-switch-7' )->theme( 'flat-square' ); ?>
  </p>
  
  <p>
    <?php echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'test-switch-8' )->left_label( 'Turn left' )->right_label( 'Turn right' )->mode( 'select' ); ?>
  </p>
```