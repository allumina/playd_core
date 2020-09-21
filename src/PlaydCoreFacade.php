<?php

namespace Allumina\PlaydCore;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Allumina\PlaydCore\Skeleton\SkeletonClass
 */
class PlaydCoreFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'playd_core';
    }
}
