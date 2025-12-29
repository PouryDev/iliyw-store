<?php

namespace App\Actions;

/**
 * Base action class for all actions
 * 
 * Actions represent a single unit of business logic
 * They should be focused, testable, and reusable
 */
abstract class BaseAction
{
    /**
     * Execute the action
     *
     * @param mixed ...$params
     * @return mixed
     */
    abstract public function execute(...$params): mixed;
}

