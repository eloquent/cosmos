# Cosmos

*A class name resolver for PHP namespaces.*

## Installation

Cosmos requires PHP 5.3 or later.

### With [Composer](http://getcomposer.org/)

* Add 'eloquent/cosmos' to the project's composer.json dependencies
* Run `composer install`

### Bare installation

* Clone from GitHub: `git clone git://github.com/eloquent/cosmos.git`
* Use a [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
  compatible autoloader (namespace 'Eloquent\Cosmos' in the 'src' directory)

## What is Cosmos?

Cosmos resolves PHP namespaced class names using the same rules as the PHP
interpreter.

It can be used anywhere it is necessary to determine the canonical form of a
class name, taking into account the current **namespace** and **use**
statements.

## Usage

To use Cosmos, first create a new resolver to represent the set of **namespace**
and **use** statements to resolve against.

The use statements are specified as an array, where the keys are canonical class
names, and the values are the alias, or 'as' portion of the use statement. The
value can be left as *null* represent no alias.

```php
<?php

use Eloquent\Cosmos\ClassNameResolver;

$resolver = new ClassNameResolver(
    'MilkyWay\SolarSystem',
    array(
        'MilkyWay\AlphaCentauri\ProximaCentauri' => null,
        'Andromeda\GalacticCenter' => 'Andromeda',
    )
);
```

The created resolver can now be used to determine the canonical version of any
class name.

```php
<?php

echo $resolver->resolve('Earth');            // outputs 'MilkyWay\SolarSystem\Earth'
echo $resolver->resolve('ProximaCentauri');  // outputs 'MilkyWay\AlphaCentauri\ProximaCentauri'
echo $resolver->resolve('Andromeda');        // outputs 'Andromeda\GalacticCenter'
echo $resolver->resolve('TNO\Pluto');        // outputs 'MilkyWay\SolarSystem\TNO\Pluto'
echo $resolver->resolve('\Betelgeuse');      // outputs 'Betelgeuse'
```

## Code quality

Cosmos strives to attain a high level of quality. A full test suite is
available, and code coverage is closely monitored.

### Latest revision test suite results
[![Build Status](https://secure.travis-ci.org/eloquent/cosmos.png)](http://travis-ci.org/eloquent/cosmos)

### Latest revision test suite coverage
<http://ci.ezzatron.com/report/cosmos/coverage/>
