# phergie/phergie-irc-plugin-react-formstackbot

A plugin for [Phergie](http://github.com/phergie/phergie-irc-bot-react/) to react to phrases like "Who's your *?"

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "bkmorse/phergie-irc-plugin-react-formstackbot": "dev-master"
    }
}
```

See Phergie documentation for more information on installing plugins.

## Configuration

```php
new \bkmorse\Phergie\Irc\Plugin\React\FormStackBot\Plugin()
```

### Usage

This plugin monitors `PRIVMSG` events attempting to locate messages like "Who's your *?". When it finds 
one, it responds to the user who posted the message that they are, in fact, Phergie's *. For example, if IRC user
"bob" says "Who's my fish monger?", Phergie will respond with "You're my fish monger, bob!"

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
