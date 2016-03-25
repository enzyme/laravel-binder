<?php

use Enzyme\LaravelBinder\Binder;
use Mockery as m;

class BinderTest extends PHPUnit_Framework_TestCase
{
    protected $test_controller = 'Enzyme\LaravelBinder\Tests\Controller';
    protected $test_interface = 'Enzyme\LaravelBinder\Tests\FooInterface';
    protected $test_concrete = 'Enzyme\LaravelBinder\Tests\Bar';
    protected $test_alias = 'foo';

    public function tearDown()
    {
        m::close();
    }

    public function test_basic_binder_registration()
    {
        $binder = new Binder($this->buildContainer());

        $binder->setAlias('controller', $this->test_controller);
        $binder->setAlias('interface', $this->test_interface);
        $binder->setBinding('bar', 'interface', $this->test_concrete);
        $binder->setNeeds('controller', ['bar']);

        $binder->register();
    }

    public function test_binder_stores_last_binding_correctly_and_solidifies_it_as_expected()
    {
        $binder = new Binder($this->buildSolidifyingContainer());

        $binder->setBinding(
            $this->test_alias,
            $this->test_interface,
            $this->test_concrete
        );

        $binder->solidify();
    }

    /**
     * @expectedException Enzyme\LaravelBinder\BindingException
     */
    public function test_binder_throws_exception_on_undefined_alias_class()
    {
        $binder = new Binder($this->buildDumbContainer());

        $binder->setAlias('controller', 'Acme\Tests\NotExist');
        $binder->setAlias('interface', $this->test_interface);
        $binder->setBinding('bar', 'interface', $this->test_concrete);
        $binder->setNeeds('controller', ['bar']);

        $binder->register();
    }

    /**
     * @expectedException Enzyme\LaravelBinder\BindingException
     */
    public function test_binder_throws_exception_on_bad_alias_name()
    {
        $binder = new Binder($this->buildDumbContainer());

        $binder->setAlias(123, 'Acme\Tests\NotExist');
    }

    /**
     * @expectedException Enzyme\LaravelBinder\BindingException
     */
    public function test_binder_throws_exception_on_empty_alias_name()
    {
        $binder = new Binder($this->buildDumbContainer());

        $binder->setAlias('', 'Acme\Tests\NotExist');
    }

    /**
     * @expectedException Enzyme\LaravelBinder\BindingException
     */
    public function test_binder_throws_exception_on_solidifying_non_existent_binding()
    {
        $binder = new Binder($this->buildDumbContainer());

        $binder->solidify();
    }

    protected function buildContainer($times = 1)
    {
        return m::mock('Illuminate\Contracts\Container\Container',
            function ($mock) use ($times) {
                $mock
                    ->shouldReceive('when')
                    ->with($this->test_controller)
                    ->atLeast()
                    ->times($times)
                    ->andReturn($mock);

                $mock
                    ->shouldReceive('needs')
                    ->with($this->test_interface)
                    ->atLeast()
                    ->times($times)
                    ->andReturn($mock);

                $mock
                    ->shouldReceive('give')
                    ->with($this->test_concrete)
                    ->atLeast()
                    ->times($times)
                    ->andReturn($mock);
            }
        );
    }

    protected function buildSolidifyingContainer()
    {
        return m::mock('Illuminate\Contracts\Container\Container',
            function($mock) {
                $mock
                    ->shouldReceive('bind')
                    ->atLeast()
                    ->times(2);
            }
        );
    }

    protected function buildDumbContainer()
    {
        return $this->buildContainer(0);
    }
}
