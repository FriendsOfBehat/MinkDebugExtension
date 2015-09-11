<?php

/*
 * This file is part of the Lakion package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Lakion\Behat\MinkDebugExtension\Listener;

use PhpSpec\ObjectBehavior;

/**
 * @mixin \Lakion\Behat\MinkDebugExtension\Listener\FailedStepListener
 *
 * @author Kamil Kokot <kamil.kokot@lakion.com>
 */
class FailedStepListenerSpec extends ObjectBehavior
{
    /**
     * @todo No typehint in method declaration due to issues with PHP7 and PhpSpec
     *
     * @param Behat\Mink\Mink $mink
     */
    function let($mink)
    {
        $this->beConstructedWith($mink, 'logDirectory', true, true);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lakion\Behat\MinkDebugExtension\Listener\FailedStepListener');
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }
}
