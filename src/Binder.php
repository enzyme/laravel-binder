<?php

namespace Enzyme\LaravelBinder;

use Illuminate\Contracts\Container\Container;

class Binder
{
    protected $container;
    protected $bindings = [];
    protected $aliases = [];
    protected $needs = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function setAlias($alias, $fqn)
    {
        $this->aliases[$this->cleanAlias($alias)] = $fqn;
    }

    public function setBinding($alias, $interface, $concrete)
    {
        $this->bindings[$this->cleanAlias($alias)] = [
            'interface' => $interface,
            'concrete'  => $concrete,
        ];
    }

    public function setNeeds($alias, array $needs)
    {
        $this->needs[$this->cleanAlias($alias)] = $needs;
    }

    public function register()
    {
        foreach ($this->needs as $parent => $dependencies) {
            $this->registerDependencies($this->getFqn($parent), $dependencies);
        }
    }

    protected function registerDependencies($parent_fqn, $dependencies)
    {
        foreach ($dependencies as $dependency) {
            $dependency_tree = $this->bindings[$dependency];

            $this
                ->container
                ->when($parent_fqn)
                ->needs($this->getFqn($dependency_tree['interface']))
                ->give($this->getFqn($dependency_tree['concrete']));
        }
    }

    protected function getFqn($string)
    {
        if (class_exists($string) || interface_exists($string)) {
            return $string;
        }

        if ($this->arrayHas($this->aliases, $string)) {
            return $this->getFqn($this->aliases[$string]);
        }

        throw new BindingException(
            "The class or alias [{$string}] does not exist."
        );
    }

    protected function arrayHas($array, $key)
    {
        return isset($array[$key])
            && array_key_exists($key, $array);
    }

    protected function cleanAlias($alias)
    {
        if (is_string($alias) && strlen($alias) > 0)
        {
            return $alias;
        }

        throw new BindingException("The alias [{$alias}] is invalid.");
    }
}
