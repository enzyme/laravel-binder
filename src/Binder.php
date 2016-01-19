<?php

namespace Enzyme\LaravelBinder;

use Illuminate\Contracts\Container\Container;

class Binder
{
    protected $container;
    protected $bindings;
    protected $aliases;
    protected $needs;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->bindings = [];
    }

    public function setAlias($alias, $fqn)
    {
        $this->aliases[$alias] = $fqn;
    }

    public function setBinding($alias, $interface, $concrete)
    {
        $this->bindings[$alias] = [
            'interface' => $interface,
            'concrete'  => $concrete,
        ];
    }

    public function setNeeds($alias, array $needs)
    {
        $this->needs[$alias] = $needs;
    }

    public function register()
    {
        foreach ($needs as $parent => $dependencies) {
            $this->registerDependencies($this->aliases[$parent], $dependencies);
        }
    }

    protected function registerDependencies($parent_fqn, $dependencies)
    {
        foreach ($dependencies as $dependency) {
            $dependency_fqns = $this->bindings[$dependency];

            $this
                ->container
                ->when($parent_fqn)
                ->needs($dependency_fqns['interface'])
                ->give($dependency_fqns['concrete']);
        }
    }
}
