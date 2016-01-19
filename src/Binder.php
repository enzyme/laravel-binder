<?php

namespace Enzyme\LaravelBinder;

use Illuminate\Contracts\Container\Container;

class Binder
{
    protected $container;
    protected $bindings;
    protected $requesters;
    protected $dependencies;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->bindings = [];
    }

    public function setRequesters(array $requesters)
    {
        //
    }

    public function setBindings(array $bindings)
    {
        //
    }

    public function setDependencies(array $dependencies)
    {
        //
    }

    public function register()
    {
        foreach ($this->dependencies as $base => $dependencies) {
            $base_fqn = $this->requesters[$base];

            foreach ($dependencies as $dependency) {
                $dependency_fqn = $this->bindings[$dependency]['fqn'];
                $binding = $this->bindings[$dependency]['binding'];

                $this
                    ->container
                    ->when($base_fqn)
                    ->needs($dependency_fqn)
                    ->give($binding);
            }
        }
    }
}
