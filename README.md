# Cosmos

*A library for representing and manipulating PHP symbols.*

[![The most recent stable version is 2.3.1][version-image]][Semantic versioning]
[![Current build status image][build-image]][Current build status]
[![Current coverage status image][coverage-image]][Current coverage status]

## Installation and documentation

* Available as [Composer] package [eloquent/cosmos].
* [API documentation] available.

## What is *Cosmos*?

*Cosmos* is a library for representing and manipulating PHP symbols. Supported
symbol types include class, interface, trait, namespace, function, and constant
names. *Cosmos* features a comprehensive API for performing many tasks,
including:

- Parsing *resolution contexts* (sets of `namespace` and/or `use` statements)
  from source code
- Resolving symbols relative to a resolution context
- Finding the shortest reference to a qualified symbol relative to a resolution
  context
- Generating an optimal resolution context for a set of symbols

*Cosmos* is primarily designed to aid in code generation, and resolution of
symbols contained in comment annotations, but should be useful in any
circumstance where run-time symbol resolution is involved.

<!-- References -->

[API documentation]: http://lqnt.co/cosmos/artifacts/documentation/api/
[Composer]: http://getcomposer.org/
[build-image]: http://img.shields.io/travis/eloquent/cosmos/develop.svg "Current build status for the develop branch"
[Current build status]: https://travis-ci.org/eloquent/cosmos
[coverage-image]: http://img.shields.io/coveralls/eloquent/cosmos/develop.svg "Current test coverage for the develop branch"
[Current coverage status]: https://coveralls.io/r/eloquent/cosmos
[eloquent/cosmos]: https://packagist.org/packages/eloquent/cosmos
[Semantic versioning]: http://semver.org/
[version-image]: http://img.shields.io/:semver-2.3.1-brightgreen.svg "This project uses semantic versioning"
