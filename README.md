# Binder

[![Build Status](https://travis-ci.org/enzyme/laravel-binder.svg?branch=master)](https://travis-ci.org/enzyme/laravel-binder)
[![Coverage Status](https://coveralls.io/repos/github/enzyme/laravel-binder/badge.svg?branch=master)](https://coveralls.io/github/enzyme/laravel-binder?branch=master)

A service container contextual binding helper for Laravel 5.

# Example

#### From the service provider.

```php
use Enzyme\LaravelBinder\Binder;

// Inside the app service provider...
public function register()
{
    $binder = new Binder($this->app);

    $binder->setAlias(
        'controller.listing',
        'App\Http\Controllers\ListingController'
    );

    $binder->setAlias(
        'repos.interface',
        'Acme\Repositories\RepositoryInterface'
    );

    // Option 1 for binding, using aliases.
    $binder->setBinding(
        'repos.listing',
        'repos.interface',
        'Acme\Repositories\ListingRepository'
    );

    // Option 2 for binding, using FQNs.
    $binder->setBinding(
        'factories.listing',
        'Acme\Factories\FactoryInterface',
        'Acme\Factories\ListingFactory'
    );

    // Tell the service container that the ListingController
    // needs the ListingRepository & ListingFactory.
    $binder->setNeeds(
        'controller.listing',
        ['repos.listing', 'factories.listing']
    );

    $binder->register();
}
```

#### From the controller

```php
// You don't need to inject the specific concrete classes, just the
// interfaces. The Binder + Service Container has taken care of it for you.
public function __construct(RepositoryInterface $repo, FactoryInterface $factory)
{
    $this->repo = $repo;
    $this->factory = $factory;
}
```

# What's the purpose?

With your service provider(s) now being the sole authority of which classes get what implementations, if you ever want to switch those implementation out with another, it's easy!

No longer do you have to go into your controllers or service classes and update the references to the concrete implementations. **Laravel** has provided this functionality for you, all **Binder** is doing is making the management of these bindings a touch easier, especially when you have to manage a lot of them.
