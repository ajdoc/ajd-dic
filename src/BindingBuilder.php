<?php

namespace AjDic;

use AjDic\AjDic as Container;
use AjDic\Helper;
use AjDic\Contracts\BindingBuilderInterface;

class BindingBuilder implements BindingBuilderInterface
{
    /**
     * The underlying container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The concrete instance.
     *
     * @var string|array
     */
    protected $concrete;

    /**
     * The abstract target.
     *
     * @var string
     */
    protected $needs;

    /**
     * Create a new contextual binding builder.
     *
     * @param  \AjDic\AjDic  $container
     * @param  string|array  $concrete
     * @return void
     */
    public function __construct(Container $container, $concrete)
    {
        $this->concrete = $concrete;
        $this->container = $container;
    }

    /**
     * Define the abstract target that depends on the context.
     *
     * @param  string  $abstract
     * @return $this
     */
    public function needs($abstract)
    {
        $this->needs = $abstract;

        return $this;
    }

    /**
     * Define the implementation for the contextual binding.
     *
     * @param  \Closure|string|array  $implementation
     * @return void
     */
    public function give($implementation)
    {
        foreach (Helper::arrayWrap($this->concrete) as $concrete) {
            $this->container->addContextualBinding($concrete, $this->needs, $implementation);
        }
    }

    /**
     * Define tagged services to be used as the implementation for the contextual binding.
     *
     * @param  string  $tag
     * @return void
     */
    public function giveTagged($tag)
    {
        $this->give(function ($container) use ($tag) {
            $taggedServices = $container->tagged($tag);

            return is_array($taggedServices) ? $taggedServices : iterator_to_array($taggedServices);
        });
    }
}