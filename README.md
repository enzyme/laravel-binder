# Binder
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

With your application service provider(s) now being the arbiter of what classes get what implementations, if you ever want to switch those implementation out with another, it's easy. No longer do you have to go into your controller or service class etc and update the references to the concrete implementations, you only have the interfaces listed. Laravel has provided this functionality, all **Binder** is doing is making the management of these bindings just a touch easier, especially if you have a lot of them.
