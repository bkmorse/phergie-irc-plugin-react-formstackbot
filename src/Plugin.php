<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/dstockto/phergie-irc-plugin-react-daddy for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Plugin\React\Daddy
 */
namespace Phergie\Irc\Plugin\React\FormStackBot;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueue;
use Phergie\Irc\Event\Event;
use Phergie\Irc\Event\UserEvent;

/**
 * Plugin for parsing messages
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\Daddy
 */
class Plugin extends AbstractPlugin
{
    /**
     * Returns a mapping of events to applicable callbacks.
     *
     * @return array Associative array keyed by event name referencing strings
     *         containing names of instance methods in the class implementing
     *         this interface or valid callables
     */
    public function getSubscribedEvents()
    {
        return [
            'irc.received.privmsg' => 'handleDaddyMessage',
        ];
    }

    public function handleDaddyMessage(Event $event, EventQueue $queue)
    {
        if ($event instanceof UserEvent) {
            $nick = $event->getNick();
            $channel = $event->getSource();
            $params = $event->getParams();
            $text = $params['text'];
            $matched = preg_match("/\s*who'?s (?:your|ya) ([^?]+)[?]?/i", $text, $matches);
            if ($matched) {
                $msg = "You're my " . $matches[1] . ', ' . $nick . '!';
                $queue->ircPrivmsg($channel, $msg);
            }
        }
    }
}
