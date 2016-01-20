<?php

use Enzyme\LaravelBinder\Binder;
use Mockery as m;

class BinderTest extends PHPUnit_Framework_TestCase
{
    protected $test_controller = 'Enzyme\LaravelBinder\Tests\Controller';
    protected $test_interface = 'Enzyme\LaravelBinder\Tests\FooInterface';
    protected $test_concrete = 'Enzyme\LaravelBinder\Tests\Bar';

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

    protected function buildContainer()
    {
        return m::mock('Illuminate\Contracts\Container\Container',
            function ($mock) {
                $mock
                    ->shouldReceive('when')
                    ->with($this->test_controller)
                    ->atLeast()
                    ->times(1)
                    ->andReturn($mock);

                $mock
                    ->shouldReceive('needs')
                    ->with($this->test_interface)
                    ->atLeast()
                    ->times(1)
                    ->andReturn($mock);

                $mock
                    ->shouldReceive('give')
                    ->with($this->test_concrete)
                    ->atLeast()
                    ->times(1)
                    ->andReturn($mock);
            }
        );
    }
}
