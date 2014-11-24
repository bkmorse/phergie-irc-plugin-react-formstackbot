<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/bkmorse/phergie-irc-plugin-react-formstackbot for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Plugin\React\Command
 */

namespace bkmorse\Phergie\Irc\Tests\Plugin\React\FormStackBot;

use Phergie\Irc\Bot\React\EventQueue;
use Phergie\Irc\Event\UserEvent;
use bkmorse\Phergie\Irc\Plugin\React\FormStackBot\Plugin;

/**
 * Tests for the Plugin class.
 *
 * @category Phergie
 * @package bkmorse\Phergie\Irc\Plugin\React\FormStackBot
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var Plugin */
    protected $plugin;

    public function setUp()
    {
        $this->plugin = new Plugin();
    }

    public function testGetSubscribedEventsReturnsArray()
    {
        $this->assertInternalType('array', $this->plugin->getSubscribedEvents());
    }

    public function testSubscribedEventListensToPrivMsg()
    {
        $config = $this->plugin->getSubscribedEvents();
        $this->assertArrayHasKey('irc.received.privmsg', $config);
    }

    public function testPluginDoesNothingForNonUserEvents()
    {
        $event = $this->getMock('Phergie\Irc\Event\Event');
        $event->expects($this->never())
            ->method('getNick');
        $event->expects($this->never())
            ->method('getSource');
        $event->expects($this->never())
            ->method('getParams');
        $queue = $this->getMock('Phergie\Irc\Bot\React\EventQueue');
        $queue->expects($this->never())
            ->method('ircPrivmsg');

        $this->assertNull($this->plugin->handlePrivateMessage($event, $queue));
    }

    /**
     * Tests that messages are handled as expected
     * @param string $nick Nick
     * @param string $channel Channel
     * @param string $message Message
     * @param string $expectedResponse Reponse from phergie
     *
     * @return void
     * @test
     * @dataProvider matchingMessageProvider
     */
    public function testMessagesAreHandledAsExpected($nick, $channel, $message, $expectedResponse)
    {
        $event = $this->getMockBuilder('\Phergie\Irc\Event\UserEvent')
            ->setMethods(
                ['getNick', 'getSource', 'getParams']
            )->getMock();
        $queue = $this->getMockBuilder('\Phergie\Irc\Bot\React\EventQueue')
            ->setMethods(['ircPrivmsg'])
            ->getMock();

        $event->expects($this->atLeastOnce())
            ->method('getNick')
            ->willReturn($nick);
        $event->expects($this->atLeastOnce())
            ->method('getSource')
            ->willReturn($channel);
        $event->expects($this->atLeastOnce())
            ->method('getParams')
            ->willReturn(['text' => $message]);

        $queue->expects($this->once())
            ->method('ircPrivmsg')
            ->with($channel, $expectedResponse);

        $this->plugin->handlePrivateMessage($event, $queue);
    }

    public function matchingMessageProvider()
    {
        // return
    }

    /**
     * Ensures that non-matching messages don't trigger a response
     *
     * @param $message
     *
     * @return void
     * @test
     * @dataProvider nonMatchingProvider
     */
    public function nonMatchingMessagesDoNotTriggerResponses($message)
    {
        $event = $this->getMockBuilder('\Phergie\Irc\Event\UserEvent')
            ->setMethods(
                ['getNick', 'getSource', 'getParams']
            )->getMock();
        $queue = $this->getMockBuilder('\Phergie\Irc\Bot\React\EventQueue')
            ->setMethods(['ircPrivmsg'])
            ->getMock();

        $event->expects($this->atLeastOnce())
            ->method('getNick')
            ->willReturn('whocares');
        $event->expects($this->atLeastOnce())
            ->method('getSource')
            ->willReturn('#doesnotmatter');
        $event->expects($this->atLeastOnce())
            ->method('getParams')
            ->willReturn(['text' => $message]);

        $queue->expects($this->never())
            ->method('ircPrivmsg');

        $this->plugin->handlePrivateMessage($event, $queue);
    }

    public function nonMatchingProvider()
    {
        // return
    }
}
