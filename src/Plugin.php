<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/bkmorse/phergie-irc-plugin-react-formstackbot for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package bkmorse\Phergie\Irc\Plugin\React\FormStackBot
 */
namespace bkmorse\Phergie\Irc\Plugin\React\FormStackBot;

use \GuzzleHttp\Client;
use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueue;
use Phergie\Irc\Bot\React\EventQueueInterface;
use Phergie\Irc\Event\Event;
use Phergie\Irc\Event\UserEvent;

/**
 * Plugin for parsing messages
 *
 * @category Phergie
 * @package bkmorse\Phergie\Irc\Plugin\React\FormStackBot
 */
class Plugin extends AbstractPlugin
{

    protected $formstack_token;
    protected $formstack_form_id;

    public function __construct(array $config)
    {
        if (!isset($config['formstack_token'])) {
            throw new \DomainException('$config must contain a "formstack_token"');
        } else {
            $this->formstack_token = $config['formstack_token'];
        }

        if (!isset($config['formstack_form_id'])) {
            throw new \DomainException('$config must contain a "formstack_form_id"');
        } else {
            $this->formstack_form_id = $config['formstack_form_id'];
        }
    }

    // assign IRC actions to functions
    public function getSubscribedEvents()
    {
        $_SESSION['received_data'] = array(); 
        return [
            'irc.received.privmsg' => 'handlePrivateMessage',
        ];
    }

    // retrieve form fields from formstack api
    public function get_fields()
    {
        unset($_SESSION['fields']);

        if(isset($_SESSION['fields']))
            return true;

        $client = new \GuzzleHttp\Client();
        $api_url = "https://www.formstack.com/api/v2/form/" . $this->formstack_form_id . ".json?oauth_token=" . $this->formstack_token;
        $response = $client->get($api_url);
        $form_info = $response->getBody();
        $form_json = $response->json();

        // return $form_json["fields"];

        foreach($form_json["fields"] as $field):
            $_SESSION['fields'][] = array('id' => $field['id'] ,'name' => $field['name'], 'label' => $field['label']);
        endforeach;

        if(isset($_SESSION['fields']))
            return true;

        return false;
    }

    // function that is triggered anytime the user privately messages the bot
    public function     (Event $event, EventQueue $queue)
    {
        if ($event instanceof UserEvent) {
            $nick = $event->getNick();
            $channel = $event->getSource();
            $params = $event->getParams();
            $text = trim($params['text']);

            if($this->get_fields())
            { 
                $msg = $this->sign_user_up($text, $nick);
                $queue->ircPrivmsg($channel, $msg);
            } else {
                $msg = "Something happened, we could not retrieve the required information for you to fill out.";
                $queue->ircPrivmsg($channel, $msg);
            }
        }
    }

    // unset session data
    public function reset($reset)
    {
        if($reset)
        {
            unset($_SESSION['index']);
            unset($_SESSION['user_data']);
        }
    }

    // capture data from user and instruct them what to do next
    public function sign_user_up($text, $nick)
    {
        $msg = "Hi " . $nick . " I am a newsletter bot, to signup for a newsletter, type signmenup"; // default message bot sends initially
        $newsletter_signup = stripos($text, 'newsletter') !== false;
        $addme = stripos(trim($text), 'addme ') !== false;
        $signmeup = (trim($text) == 'signmeup' ? true : false);
        $reset = (trim($text) == 'reset' ? true : false);

        $this->reset($reset);

        $confirmed = (trim($text) == 'confirmed' ? true : false);

        if(!isset($_SESSION['index']))
        {
            $_SESSION['index'] = 0; // setting index in session to be used to go thru each field in fields array
        }

        if($signmeup)
        {
            $_SESSION['signmeup'] = "yes"; // user entered signmeup, lets remember that so we don't keep asking the user
        }

        if ($newsletter_signup) {
            // user entered newsletter, let them know about signing up
            $msg = "So you want to sign up for our newsletter? Just to be sure, reply with: signmeup";
            unset($_SESSION['signmeup']);
        } elseif(isset($_SESSION['signmeup'])) {

            $fields = $_SESSION['fields'];

            if(count($fields) > $_SESSION['index'] && isset($_SESSION['signmeup']))
            {
                $msg = "Thanks! Now please enter your " . $fields[$_SESSION['index']]['label'];

                if($text != "signmeup")
                {
                    $_SESSION['user_data']['field_' . $fields[$_SESSION['index']-1]['id']] = $text;
                }
                $_SESSION['index']++;

            } else {
                if($text != "signmeup")
                {
                    $_SESSION['user_data']['field_' . $fields[$_SESSION['index']-1]['id']] = $text;
                    $_SESSION['index']++;
                }

                if($this->post_submission())
                {
                    $user_data = implode(', ', array_map(function ($v, $k) { return $v; }, $_SESSION['user_data'], array_keys($_SESSION['user_data'])));
                    $msg = "Thanks! The following information was added: " . $user_data . ".";
                } else {
                    $msg = "Something happened, please try again.";
                }

                $this->reset(true);
            }
        }

        return $msg;
    }

    // post submission to formstack api
    public function post_submission()
    {
        $client = new \GuzzleHttp\Client();

        $api_url = "https://www.formstack.com/api/v2/form/" . $this->formstack_form_id . "/submission.json?oauth_token=" . $this->formstack_token;
        
        $response = $client->post($api_url, array(
            'headers' => array('Content-type' => 'application/json'),
            'body' => json_encode($_SESSION['user_data'])
            // 'body' => json_encode($data)
        ));

        return $response;
    }

}