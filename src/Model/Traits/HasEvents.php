<?php

namespace TurkerExecutor\Model\Traits;

use TurkerExecutor\Events\EventDispatcher;

trait HasEvents
{
    protected static ?EventDispatcher $dispatcher = null;
    
    protected static array $events = [
        'creating', 'created',
        'updating', 'updated',
        'deleting', 'deleted',
        'saving', 'saved'
    ];

    public static function getEventDispatcher(): EventDispatcher
    {
        if (static::$dispatcher === null) {
            static::$dispatcher = new EventDispatcher();
        }
        return static::$dispatcher;
    }

    protected function fireModelEvent(string $event): void
    {
        if (in_array($event, static::$events)) {
            static::getEventDispatcher()->dispatch("model.{$event}", [
                'model' => $this,
                'event' => $event,
                'timestamp' => time()
            ]);
        }
    }

    protected function fireSaving(): bool
    {
        $this->fireModelEvent('saving');
        return true;
    }

    protected function fireSaved(): void
    {
        $this->fireModelEvent('saved');
    }

    protected function fireCreating(): bool
    {
        $this->fireModelEvent('creating');
        return true;
    }

    protected function fireCreated(): void
    {
        $this->fireModelEvent('created');
    }

    protected function fireUpdating(): bool
    {
        $this->fireModelEvent('updating');
        return true;
    }

    protected function fireUpdated(): void
    {
        $this->fireModelEvent('updated');
    }

    protected function fireDeleting(): bool
    {
        $this->fireModelEvent('deleting');
        return true;
    }

    protected function fireDeleted(): void
    {
        $this->fireModelEvent('deleted');
    }
} 