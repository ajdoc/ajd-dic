<?php

namespace AjDic\Traits;

use Loady\Loady;

trait HasLoader 
{
    protected Loady|null $loady = null;

    public function setLoader(Loady $loady): void
    {
        $this->loady = $loady;
    }

    public function getLoader(): Loady|null
    {
        if (is_null($this->loady) && ! \class_exists(Loady::class)) {
            return null;
        }

        if (is_null($this->loady) && \class_exists(Loady::class)) {
            $this->loady = new Loady;
        }

        return $this->loady;
    }
}