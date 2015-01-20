<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeeba\Engine\Postproc\Connector\Amazon\Symfony\Component\EventDispatcher\Debug;

use Akeeba\Engine\Postproc\Connector\Amazon\Symfony\Component\Stopwatch\Stopwatch;
use Akeeba\Engine\Postproc\Connector\Amazon\Symfony\Component\EventDispatcher\Event;
use Akeeba\Engine\Postproc\Connector\Amazon\Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class WrappedListener
{
    private $listener;
    private $name;
    private $called;
    private $stoppedPropagation;
    private $stopwatch;

    public function __construct($listener, $name, Stopwatch $stopwatch)
    {
        $this->listener = $listener;
        $this->name = $name;
        $this->stopwatch = $stopwatch;
        $this->called = false;
        $this->stoppedPropagation = false;
    }

    public function getWrappedListener()
    {
        return $this->listener;
    }

    public function wasCalled()
    {
        return $this->called;
    }

    public function stoppedPropagation()
    {
        return $this->stoppedPropagation;
    }

    public function __invoke(Event $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $this->called = true;

        $e = $this->stopwatch->start($this->name, 'event_listener');

        call_user_func($this->listener, $event, $eventName, $dispatcher);

        if ($e->isStarted()) {
            $e->stop();
        }

        if ($event->isPropagationStopped()) {
            $this->stoppedPropagation = true;
        }
    }
}
