<?php
/**
 * This class will test the ami client
 *
 * PHP Version 5
 *
 * @category   Pami
 * @package    Test
 * @subpackage Client
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://marcelog.github.com/
 *
 * Copyright 2011 Marcelo Gornstein <marcelog@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */
namespace PAMI\Client\Impl {
    use PHPUnit\Framework\TestCase as BaseTestCase;

    /**
     * This class will test the ami client
     *
     * PHP Version 5
     *
     * @category   Pami
     * @package    Test
     * @subpackage Client
     * @author     Marcelo Gornstein <marcelog@gmail.com>
     * @license    http://marcelog.github.com/ Apache License 2.0
     * @link       http://marcelog.github.com/
     */
    class Test_Client extends BaseTestCase
    {
        /**
         * @test
         */
        public function can_get_client()
        {
            $options = array(
            'host' => 'tcp://1.1.1.1',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $this->assertTrue($client instanceof \PAMI\Client\Impl\ClientImpl);
        }
        /**
         * @test
         */
        public function can_set_logger()
        {
            $options = array(
            'host' => 'tcp://1.1.1.1',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->setLogger(new \Psr\Log\NullLogger);
            $this->assertTrue($client->getLogger() instanceof \Psr\Log\NullLogger);
        }
        /**
         * @test
         */
        public function can_connect_timeout()
        {
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 3,
            'read_timeout' => 10
            );
            $start = time();
            try {
                $client = new \PAMI\Client\Impl\ClientImpl($options);
                $client->open();
            } catch (\Exception $e) {
            }
            $length = time() - $start;
            $this->assertTrue($length >= 2 && $length <= 5);
        }
        /**
         * @test
         * expectedException \PAMI\Client\Exception\ClientException
         */
        public function can_detect_other_peer()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $read = array('Whatever');
            $write = array();
            setFgetsMock($read, $write);
            $this->expectException(\PAMI\Client\Exception\ClientException::class);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->open();
        }
        /**
         * @test
         */
        public function can_register_event_listener()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->registerEventListener(new SomeListenerClass);
            $client->open();
            $event = array(
            'Event: PeerStatus',
            'Privilege: system,all',
            'ChannelType: SIP',
            'Peer: SIP/someguy',
            'PeerStatus: Registered',
            ''
            );
            setFgetsMock($event, $event);
            for ($i = 0; $i < 6; $i++) {
                $client->process();
            }
            $event = SomeListenerClass::$event;
            $this->assertEquals($event->getName(), 'PeerStatus');
            $this->assertTrue($event instanceof \PAMI\Message\Event\PeerStatusEvent);
        }

        /**
         * @test
         */
        public function can_register_closure_event_listener()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $resultVariable = false;
            $client->registerEventListener(function ($event) use (&$resultVariable) {
                $resultVariable = $event;
            });
            $client->open();
            $event = array(
            'Event: PeerStatus',
            'Privilege: system,all',
            'ChannelType: SIP',
            'Peer: SIP/someguy',
            'PeerStatus: Registered',
            ''
            );
            setFgetsMock($event, $event);
            for ($i = 0; $i < 6; $i++) {
                $client->process();
            }
            $this->assertEquals($resultVariable->getName(), 'PeerStatus');
            $this->assertTrue($resultVariable instanceof \PAMI\Message\Event\PeerStatusEvent);
        }

        /**
         * @test
         */
        public function can_register_method_event_listener()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $resultVariable = false;
            $listener = new SomeListenerClass;
            $client->registerEventListener(array($listener, 'handle'));
            $client->open();
            $event = array(
            'Event: PeerStatus',
            'Privilege: system,all',
            'ChannelType: SIP',
            'Peer: SIP/someguy',
            'PeerStatus: Registered',
            ''
            );
            setFgetsMock($event, $event);
            for ($i = 0; $i < 6; $i++) {
                $client->process();
            }
            $event = SomeListenerClass::$event;
            $this->assertEquals($event->getName(), 'PeerStatus');
            $this->assertTrue($event instanceof \PAMI\Message\Event\PeerStatusEvent);
        }

        /**
         * @test
         */
        public function can_unregister_event_listener()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            SomeListenerClass::$event = null;
            $id = $client->registerEventListener(new SomeListenerClass);
            $client->open();
            $event = array(
            'Event: PeerStatus',
            'Privilege: system,all',
            'ChannelType: SIP',
            'Peer: SIP/someguy',
            'PeerStatus: Registered',
            ''
            );
            setFgetsMock($event, $event);
            $client->unregisterEventListener($id);
            for ($i = 0; $i < 6; $i++) {
                $client->process();
            }
            $event = SomeListenerClass::$event;
            $this->assertNull(SomeListenerClass::$event);
        }

        /**
         * @test
         */
        public function can_filter_with_predicate()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $resultVariable = false;
            $client->registerEventListener(
                function ($event) use (&$resultVariable) {
                    $resultVariable = $event;
                },
                function ($event) {
                    return false;
                }
            );
            $client->open();
            $event = array(
            'Event: PeerStatus',
            'Privilege: system,all',
            'ChannelType: SIP',
            'Peer: SIP/someguy',
            'PeerStatus: Registered',
            ''
            );
            setFgetsMock($event, $event);
            for ($i = 0; $i < 6; $i++) {
                $client->process();
            }
            $this->assertFalse($resultVariable);
        }

        /**
         * @test
         */
        public function can_login()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->open();
            $this->assertTrue($client instanceof \PAMI\Client\Impl\ClientImpl);
            $client->close();
        }
        /**
         * @test
         * expectedException \PAMI\Client\Exception\ClientException
         */
        public function cannot_send()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            'fwrite error'
            );
            setFgetsMock($standardAMIStart, $write);
            $this->expectException(\PAMI\Client\Exception\ClientException::class);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->open();
        }

        /**
         * @test
         * expectedException \PAMI\Client\Exception\ClientException
         */
        public function cannot_login()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mock_stream_get_meta_data_return;
            global $mockTime;
            global $standardAMIStartBadLogin;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $mock_stream_get_meta_data_return = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 1,
            'read_timeout' => 1
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStartBadLogin, $write);
            $this->expectException(\PAMI\Client\Exception\ClientException::class);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->open();
        }
        /**
         * @test
         * expectedException \PAMI\Client\Exception\ClientException
         */
        public function cannot_read()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $this->expectException(\PAMI\Client\Exception\ClientException::class);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->open();
            setFgetsMock(array(false), $write);
            $client->send(new \PAMI\Message\Action\LoginAction('asd', 'asd'));
        }
        /**
         * @test
         * expectedException \PAMI\Client\Exception\ClientException
         */
        public function cannot_read_by_read_timeout()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mock_stream_timeout;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $mock_stream_timeout = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            //'connect_timeout' => 10,
            'connect_timeout' => 1,
            'read_timeout' => 1
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $this->expectException(\PAMI\Client\Exception\ClientException::class);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->open();
            setFgetsMock(array(10, 4), $write);
            $start = \time();
            $client->send(new \PAMI\Message\Action\LoginAction('asd', 'asd'));
            //$this->assertEquals(\time() - $start, 10);
            $this->assertEquals(\time() - $start, 3);
        }
        /**
         * @test
         */
        public function can_get_response_with_associated_events()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->registerEventListener(new SomeListenerClass);
            $client->open();
            $event = array(
            'Response: Success',
            'ActionID: 1432.123',
            'Eventlist: start',
            'Message: Channels will follow',
            '',
            'Event: CoreShowChannelsComplete',
            'EventList: Complete',
            'ListItems: 0',
            'ActionID: 1432.123',
            ''
            );
            $write = array(
            "action: CoreShowChannels\r\nactionid: 1432.123\r\n"
            );
            setFgetsMock($event, $write);
            $result = $client->send(new \PAMI\Message\Action\CoreShowChannelsAction);
            $this->assertTrue($result instanceof \PAMI\Message\Response\Response);
            $events = $result->getEvents();
            $this->assertEquals(count($events), 1);
            $this->assertTrue($events[0] instanceof \PAMI\Message\Event\CoreShowChannelsCompleteEvent);
            $this->assertEquals(
                $events[0]->getRawContent(),
                implode("\r\n", array(
                'Event: CoreShowChannelsComplete',
                'EventList: Complete',
                'ListItems: 0',
                'ActionID: 1432.123',
                ))
            );
        }

        /**
         * @test
         */
        public function can_serialize_response_and_events()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->registerEventListener(new SomeListenerClass);
            $client->open();
            $event = array(
            'Response: Success',
            'ActionID: 1432.123',
            'Eventlist: start',
            'Message: Channels will follow',
            '',
            'Event: CoreShowChannelsComplete',
            'EventList: Complete',
            'ListItems: 0',
            'ActionID: 1432.123',
            ''
            );
            $write = array(
            "action: CoreShowChannels\r\nactionid: 1432.123\r\n"
            );
            setFgetsMock($event, $write);
            $result = $client->send(new \PAMI\Message\Action\CoreShowChannelsAction);
            $ser = serialize($result);
            $result2 = unserialize($ser);
            $events = $result2->getEvents();
            $this->assertEquals($result2->getMessage(), 'Channels will follow');
            $this->assertEquals($events[0]->getName(), 'CoreShowChannelsComplete');
            $this->assertEquals($events[0]->getListItems(), 0);
        }

        /**
         * @test
         */
        public function can_get_response_events_without_actionid_and_event()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 1000,
            'read_timeout' => 1000
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->registerEventListener(new SomeListenerClass);
            $client->open();
            $event = array(
            'Response: Success',
            'ActionID: 1432.123',
            'Eventlist: start',
            'Message: Channels will follow',
            '',
            'Channel: pepe',
            'Count: Blah',
            '',
            'Event: CoreShowChannelsComplete',
            'EventList: Complete',
            'ListItems: 0',
            'ActionID: 1432.123',
            ''
            );
            $write = array(
            "action: CoreShowChannels\r\nactionid: 1432.123\r\n"
            );
            setFgetsMock($event, $write);
            $result = $client->send(new \PAMI\Message\Action\CoreShowChannelsAction);
            $events = $result->getEvents();
            $this->assertEquals($events[0]->getName(), 'ResponseEvent');
            $this->assertEquals($events[0]->getKey('Channel'), 'pepe');
            $this->assertEquals($events[0]->getKey('Count'), 'Blah');
            $this->assertEquals($events[1]->getName(), 'CoreShowChannelsComplete');
            $this->assertEquals($events[1]->getListItems(), 0);
        }

        /**
         * @test
         */
        public function can_get_set_variable()
        {
            $now = time();
            $action = new \PAMI\Message\Action\LoginAction('a', 'b');
            $this->assertGreaterThanOrEqual($now, $action->getCreatedDate());
            $action->setVariable('variable', 'value');
            $this->assertEquals($action->getVariable('variable'), 'value');
            $this->assertNull($action->getVariable('variable2'));
        }

        /**
         * @test
         */
        public function can_get_set_variable_with_multiple_values()
        {
            global $mockTime;
            $mockTime = true;
            $now = time();
            $action = new \PAMI\Message\Action\LoginAction('a', 'b');
            $this->assertEquals($now, $action->getCreatedDate());
            $action->setVariable('variable', array('value1', 'value2'));
            $text
            = "action: Login\r\n"
            . "actionid: 1432.123\r\n"
            . "username: a\r\n"
            . "secret: b\r\n"
            . "Variable: variable=value1\r\n"
            . "Variable: variable=value2\r\n"
            . "\r\n"
            ;
            $this->assertEquals($text, $action->serialize());
        }

        /**
         * @test
         */
        public function can_report_unknown_event()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->registerEventListener(new SomeListenerClass);
            $client->open();
            $event = array(
            'Event: MyOwnImaginaryEvent',
            'Privilege: system,all',
            'ChannelType: SIP',
            ''
            );
            setFgetsMock($event, $event);
            for ($i = 0; $i < 4; $i++) {
                $client->process();
            }
            $this->assertTrue(SomeListenerClass::$event instanceof \PAMI\Message\Event\UnknownEvent);
        }

        /**
         * @test
         * @group channel_vars
         * ChanVariable is sent without a channel name and without a "channel"
         * key.
         * https://github.com/marcelog/PAMI/issues/85
         */
        public function can_get_channel_variables_without_default_channel_name()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->registerEventListener(new SomeListenerClass);
            $client->open();
            $event = array(
            'Event: Dial',
            'Privilege: call,all',
            'SubEvent: Begin',
            'Destination: SIP/jw1034-00000010',
            'CallerIDNum: 1201',
            'CallerIDName: <unknown>',
            'ConnectedLineNum: strategy-sequential',
            'ConnectedLineName: <unknown>',
            'UniqueID: pbx-1439974866.33',
            'DestUniqueID: pbx-1439974866.34',
            'Dialstring: jw1034',
            'ChanVariable: var1',
            'ChanVariable: var2=v2',
            ''
            );
            setFgetsMock($event, $event);
            for ($i = 0; $i < 14; $i++) {
                $client->process();
            }
            $event = SomeListenerClass::$event;
            $varChan = array(
            'var1' => '',
            'var2' => 'v2'
            );
            $channelVars = array(
            'default' => $varChan
            );
            $this->assertEquals($channelVars, $event->getAllChannelVariables());
            $this->assertEquals($varChan, $event->getChannelVariables());
            $this->assertEquals($varChan, $event->getChannelVariables('default'));
        }


        /**
         * @test
         * @group channel_vars
         * ChanVariable is sent without a channel name but with a "channel" key.
         * https://github.com/marcelog/PAMI/issues/85
         */
        public function can_get_channel_variables_with_default_channel_name()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->registerEventListener(new SomeListenerClass);
            $client->open();
            $event = array(
            'Event: Dial',
            'Privilege: call,all',
            'Channel: Local/0@pbx_dial_callroute_to_endpoint-00000008;2',
            'SubEvent: Begin',
            'Destination: SIP/jw1034-00000010',
            'CallerIDNum: 1201',
            'CallerIDName: <unknown>',
            'ConnectedLineNum: strategy-sequential',
            'ConnectedLineName: <unknown>',
            'UniqueID: pbx-1439974866.33',
            'DestUniqueID: pbx-1439974866.34',
            'Dialstring: jw1034',
            'ChanVariable: var1',
            'ChanVariable: var2=v2',
            ''
            );
            setFgetsMock($event, $event);
            for ($i = 0; $i < 15; $i++) {
                $client->process();
            }
            $event = SomeListenerClass::$event;
            $varChan = array(
            'var1' => '',
            'var2' => 'v2'
            );
            $channelVars = array(
            'local/0@pbx_dial_callroute_to_endpoint-00000008;2' => $varChan
            );
            $this->assertEquals($channelVars, $event->getAllChannelVariables());
            $this->assertEquals($varChan, $event->getChannelVariables());
            $this->assertEquals(
                $varChan,
                $event->getChannelVariables(
                    'Local/0@pbx_dial_callroute_to_endpoint-00000008;2'
                )
            );
        }

        /**
         * @test
         * @group channel_vars
         * ChanVariable is sent with a channel name and with a "channel" key.
         * https://github.com/marcelog/PAMI/issues/85
         */
        public function can_get_channel_variables()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mockTime;
            global $standardAMIStart;
            $mockTime = true;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 10,
            'read_timeout' => 10
            );
            $write = array(
            "action: Login\r\nactionid: 1432.123\r\nusername: asd\r\nsecret: asd\r\n"
            );
            setFgetsMock($standardAMIStart, $write);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->registerEventListener(new SomeListenerClass);
            $client->open();
            $event = array(
            'Event: Dial',
            'Privilege: call,all',
            'SubEvent: Begin',
            'Channel: Local/0@pbx_dial_callroute_to_endpoint-00000008;2',
            'Destination: SIP/jw1034-00000010',
            'CallerIDNum: 1201',
            'CallerIDName: <unknown>',
            'ConnectedLineNum: strategy-sequential',
            'ConnectedLineName: <unknown>',
            'UniqueID: pbx-1439974866.33',
            'DestUniqueID: pbx-1439974866.34',
            'Dialstring: jw1034',
            'ChanVariable: var1',
            'ChanVariable: var2=value2',
            'ChanVariable(Local/0@pbx_dial_callroute_to_endpoint-00000008;2): var3=value3',
            'ChanVariable(Local/0@pbx_dial_callroute_to_endpoint-00000008;2): var4=value4',
            'ChanVariable(Local/0@pbx_dial_callroute_to_endpoint-00000008;2): var5=value5',
            'ChanVariable(SIP/jw1034-00000010): var12=value12',
            'ChanVariable(SIP/jw1034-00000010): var22=value22',
            'ChanVariable(SIP/jw1034-00000010): var32=value32',
            ''
            );
            setFgetsMock($event, $event);
            for ($i = 0; $i < 21; $i++) {
                $client->process();
            }
            $event = SomeListenerClass::$event;
            $varChan1 = array(
            'var1' => '',
            'var2' => 'value2',
            'var3' => 'value3',
            'var4' => 'value4',
            'var5' => 'value5'
            );
            $varChan2 = array(
            'var12' => 'value12',
            'var22' => 'value22',
            'var32' => 'value32'
            );
            $channelVars = array(
            'local/0@pbx_dial_callroute_to_endpoint-00000008;2' => $varChan1,
            'sip/jw1034-00000010' => $varChan2
            );
            $this->assertEquals($varChan1, $event->getChannelVariables());
            $this->assertEquals($channelVars, $event->getAllChannelVariables());
            $this->assertNull($event->getChannelVariables('unknown'));
        }

        /**
         * @test
         * expectedException \PAMI\Client\Exception\ClientException
         */
        public function can_detect_socket_returning_false()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            $mock_stream_socket_client = false;
            $mock_stream_set_blocking = true;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 1,
            'read_timeout' => 2
            );
            $read = array('Whatever');
            $write = array();
            setFgetsMock($read, $write);
            $this->expectException(\PAMI\Client\Exception\ClientException::class);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->open();
        }

        /**
         * @test
         * expectedException \PAMI\Client\Exception\ClientException
         */
        public function can_detect_socket_set_blocking_returning_false()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = false;
            $options = array(
            'host' => '2.3.4.5',
            'scheme' => 'tcp://',
            'port' => 9999,
            'username' => 'asd',
            'secret' => 'asd',
            'connect_timeout' => 1,
            'read_timeout' => 2
            );
            $read = array('Whatever');
            $write = array();
            setFgetsMock($read, $write);
            $this->expectException(\PAMI\Client\Exception\ClientException::class);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->open();
        }

        /**
         * @test
         * expectedException \PAMI\Client\Exception\ClientException
         */
        public function can_detect_socket_set_timeout_returning_false()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            global $mock_stream_set_timeout;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $mock_stream_set_timeout = false;
            $options = array(
                'host' => '2.3.4.5',
                'scheme' => 'tcp://',
                'port' => 9999,
                'username' => 'asd',
                'secret' => 'asd',
                'connect_timeout' => 1,
                'read_timeout' => 2
            );
            $read = array('Whatever');
            $write = array();
            setFgetsMock($read, $write);
            $this->expectException(\PAMI\Client\Exception\ClientException::class);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->open();
        }

        /**
         * @test
         * expectedException \PAMI\Client\Exception\ClientException
         */
        public function can_detect_open_returning_non_asteriskId()
        {
            global $mock_stream_socket_client;
            global $mock_stream_set_blocking;
            $mock_stream_socket_client = true;
            $mock_stream_set_blocking = true;
            $testAMIStart = array(
                'Something Else Call Manager/1.1',
                'Response: Success',
                'ActionID: 1432.123',
                'Message: Authentication accepted',
                '',
                'Response: Goodbye',
                'ActionID: 1432.123',
                'Message: Thanks for all the fish.',
                ''
            );
            $options = array(
                'host' => '2.3.4.5',
                'scheme' => 'tcp://',
                'port' => 9999,
                'username' => 'asd',
                'secret' => 'asd',
                'connect_timeout' => 1,
                'read_timeout' => 2
            );
            $write = array();
            setFgetsMock($testAMIStart, $write);
            $this->expectException(\PAMI\Client\Exception\ClientException::class);
            $client = new \PAMI\Client\Impl\ClientImpl($options);
            $client->registerEventListener(new SomeListenerClass);
            $client->open();
        }
    }
}
