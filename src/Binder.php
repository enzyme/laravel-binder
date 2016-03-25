<?php

namespace Enzyme\LaravelBinder;

use Illuminate\Contracts\Container\Container;

/**
 * Manages a set of context bindings using Laravel's service container.
 */
class Binder
{
    /**
     * A reference to the service container.
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * A collection of bindings.
     * @var array
     */
    protected $bindings = [];

    /**
     * A collection of aliases.
     * @var array
     */
    protected $aliases = [];

    /**
     * A collection of classes and their dependencies.
     * @var array
     */
    protected $needs = [];

    /**
     * Holds a record of the last binding that occured.
     * @var array
     */
    protected $lastBinding = [];

    /**
     * Create a new Binder from the given service container instance.
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Create an alias for the given class or interface.
     *
     * @param string $alias The alias.
     * @param string $fqn   The full namespaced path to the class or interface.
     */
    public function setAlias($alias, $fqn)
    {
        $this->aliases[$this->cleanAlias($alias)] = $fqn;
    }

    /**
     * Create a binding from an interface to a concrete class.
     *
     * @param string $alias     The alias for this binding.
     * @param string $interface The interface.
     * @param string $concrete  The concrete implementation.
     */
    public function setBinding($alias, $interface, $concrete)
    {
        $this->bindings[$this->cleanAlias($alias)] = [
            'interface' => $interface,
            'concrete'  => $concrete,
        ];

        $this->lastBinding = compact('alias', 'interface', 'concrete');

        return $this;
    }

    /**
     * Binds the previous virtual binding into the Laravel service container.
     * This will map the interface to the concrete class, then create an alias
     * for the interface so it can later be referenced by its short name.
     */
    public function solidify()
    {
        if (count($this->lastBinding) < 3) {
            throw new BindingException(
                "Container injection can't be completed ".
                "as a previous binding hasn't occured."
            );
        }

        $alias = $this->lastBinding['alias'];
        $interface = $this->lastBinding['interface'];
        $concrete = $this->lastBinding['concrete'];

        $this
            ->container
            ->bind($interface, $concrete);
        $this
            ->container
            ->bind($alias, function($app) use($interface) {
                return $app->make($interface);
            });
    }

    /**
     * Set the dependencies of a class.
     *
     * @param string $alias The alias of the class.
     * @param array  $needs An array of dependencies.
     */
    public function setNeeds($alias, array $needs)
    {
        $this->needs[$this->cleanAlias($alias)] = $needs;
    }

    /**
     * Register all the dependencies with the underlying service container.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->needs as $parent => $dependencies) {
            $this->registerDependencies($this->getFqn($parent), $dependencies);
        }
    }

    /**
     * Registers the given dependencies with the parent class.
     *
     * @param string $parent_fqn   The full namespaced class path.
     * @param array  $dependencies The collection of dependencies.
     *
     * @return void
     */
    protected function registerDependencies($parent_fqn, array $dependencies)
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

    /**
     * Get the fully qualified name for the given string if it's an alias
     * otherwise ensure the class/interface exists and return it. Will throw a
     * BindingException if the class/interface does not exist.
     *
     * @param string $string
     * @param bool   $concrete When getting the FQN of a binding, return the
     *                         concrete implementation instead of the interface.
     *
     * @return string
     */
    protected function getFqn($string, $concrete = false)
    {
        if (class_exists($string) || interface_exists($string)) {
            return $string;
        }

        if ($this->arrayHas($this->aliases, $string)) {
            return $this->getFqn($this->aliases[$string]);
        }

        if ($this->arrayHas($this->bindings, $string)) {
            $string = $concrete === true
                ? $this->bindings[$string]['concrete']
                : $this->bindings[$string]['interface'];

            return $this->getFqn($string);
        }

        throw new BindingException(
            "The class or alias [{$string}] does not exist."
        );
    }

    /**
     * Check if the given array has the key specified.
     *
     * @param array $array The array to check.
     * @param mixed $key   The key.
     *
     * @return boolean
     */
    protected function arrayHas(array $array, $key)
    {
        return isset($array[$key])
            && array_key_exists($key, $array);
    }

    /**
     * Return a clean alias and ensure it is a string and not of length zero.
     * Will throw a BindingException if the value given is not a string or a
     * string of length zero.
     *
     * @param string $alias
     *
     * @return string
     */
    protected function cleanAlias($alias)
    {
        if (is_string($alias) && strlen($alias) > 0)
        {
            return $alias;
        }

        throw new BindingException("The alias [{$alias}] is invalid.");
    }
}
