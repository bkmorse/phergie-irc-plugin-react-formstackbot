# phergie/phergie-irc-plugin-react-formstackbot

A plugin for [Phergie](http://github.com/phergie/phergie-irc-bot-react/) to react to users who privately message the bot and ask them to sign up and submit data to formstack.com api"

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "phergie/phergie-irc-plugin-react-formstackbot": "dev-master"
    }
}
```

See Phergie documentation for more information on installing plugins.

## Configuration

```php
new \bkmorse\Phergie\Irc\Plugin\React\FormStackBot\Plugin(array(
    'prefix' => '!', // string denoting the start of a command
    'pattern' => '/^!/', // PCRE regular expression denoting the presence of a
    'nick' => true, // true to match common ways of addressing the bot by its
                    // connection nick
    'formstack_form_id' => '', // id of form in formstack.com dashboard
    'formstack_token'   => '', // token from when you create an application on formstack.com
)),
```

### Usage

This plugin monitors `PRIVMSG` events attempting."

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```