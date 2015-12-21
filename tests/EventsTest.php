<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\EventContainer\EventContainer;
use Thunder\Shortcode\Event\FilterShortcodesEvent;
use Thunder\Shortcode\EventDispatcher\EventDispatcher;
use Thunder\Shortcode\Events;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class EventsTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterShortcodes()
    {
        $handlers = new HandlerContainer();
        $handlers->add('root', function(ShortcodeInterface $s) { return 'root['.$s->getContent().']'; });
        $handlers->add('yes', function(ShortcodeInterface $s) { return 'yes['.$s->getContent().']'; });
        $handlers->add('no', function(ShortcodeInterface $s) { return 'nope'; });

        $events = new EventContainer();
        $events->addListener(Events::FILTER_SHORTCODES, function(FilterShortcodesEvent $event) {
            $event->setShortcodes(array_filter($event->getShortcodes(), function(ShortcodeInterface $s) {
                return $s->getName() !== 'no';
            }));
        });

        $dispatcher = new EventDispatcher($events);

        $processor = new Processor(new RegularParser(), $handlers, $dispatcher);

        $this->assertSame('x root[ yes[ yes[] ] yes[ [no /] ] ] y', $processor->process('x [root] [yes] [yes/] [/yes] [yes] [no /] [/yes] [/root] y'));
    }
}
