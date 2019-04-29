<?php
/**
 * SpanAlarm Event
 *
 *
 * Note: Auto Generated using xsltproc
 *
 * PHP Version 5
 *
 * @category   Pami
 * @package    Message
 * @subpackage Event
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @author     Diederik de Groot <ddegroot@gmail.com>
 * @license    http://marcelog.github.com/PAMI/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://marcelog.github.com/PAMI/
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
namespace PAMI\Message\Event;

use PAMI\Message\Event\EventMessage;

/**
 * SpanAlarm Event
 *
 *
 * Note: Auto Generated using xsltproc
 *
 * PHP Version 5
 *
 * @category   Pami
 * @package    Message
 * @subpackage Event
 * @author     Diederik de Groot <ddegroot@gmail.com>
 * @license    http://dkgroot.github.com/PAMI/ Apache License 2.0
 * @link       http://github.com/dkgroot/PAMI/
 */
class SpanAlarmEvent extends EventMessage
{
    /**
     * get Span
     *
     * Returns:The span on which the alarm occurred.
     *
     * @return string
     */
    public function getSpan()
    {
        return $this->getKey('Span');
    }

    /**
     * get Alarm
     *
     * Returns:A textual description of the alarm that occurred.
     *
     * @return string
     */
    public function getAlarm()
    {
        return $this->getKey('Alarm');
    }
}