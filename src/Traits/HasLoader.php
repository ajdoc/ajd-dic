<?php

namespace AjDic\Traits;

use Loady\Loady;

trait HasLoader 
{
    protected Loady $loader;

    public function setLoader(Loady $loader): void
    {
        $this->loader = $loader;
    }

    public function getLoader(): Loady|null
    {
        if (is_null($this->loader) && ! \class_exists(Loady::class)) {
            return null;
        }

        if (is_null($this->loader) && \class_exists(Loady::class)) {
            $this->loader = new Loady;
        }

        return $this->loader;
    }
}