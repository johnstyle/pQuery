<?php

namespace Johnstyle\pQuery\Component;

/**
 * Class TraversingTrait
 *
 * @author  Jonathan SAHM <contact@johnstyle.fr>
 * @package Johnstyle\pQuery\Component
 */
trait TraversingTrait
{
    /**
     * @param  int $index
     * @return $this
     */
    public function eq ($index)
    {
        $this->index = (int) $index;

        return $this;
    }

    /**
     * @param  callable $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach($this->matches as $this->index=>$this->html) {

            call_user_func_array($callback, array(&$this));
        }

        return $this;
    }
}
