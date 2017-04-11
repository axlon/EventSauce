<?php

namespace EventSauce\EventSourcing;

use function explode;
use Generator;
use function get_class;

abstract class AggregateRoot
{
    /**
     * @var Event[]
     */
    private $recordedEvents;

    /**
     * @var AggregateRootId
     */
    private $aggregateRootId;

    final public function __construct(AggregateRootId $aggregateRootId)
    {
        $this->recordedEvents = [];
        $this->aggregateRootId = $aggregateRootId;
    }

    public function aggregateRootId(): AggregateRootId
    {
        return $this->aggregateRootId;
    }

    protected function recordThat(Event $event)
    {
        $this->apply($event);
        $this->recordedEvents[] = $event;
    }

    /**
     * @return Event[]
     */
    public function releaseEvents(): array
    {
        $releasedEvents = $this->recordedEvents;
        $this->recordedEvents = [];

        return $releasedEvents;
    }

    private function apply(Event $event)
    {
        $parts = explode('\\', get_class($event));
        $this->{'apply'.end($parts)}($event);
    }

    /**
     * @param AggregateRootId $aggregateRootId
     * @param Generator $events
     *
     * @return static
     */
    public static function reconstituteFromEvents(AggregateRootId $aggregateRootId, Generator $events)
    {
        $aggregateRoot = new static($aggregateRootId);

        /** @var Event $event */
        foreach ($events as $event) {
            $aggregateRoot->apply($event);
        }

        return $aggregateRoot;
    }
}