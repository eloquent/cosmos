# Cosmos

*A library for representing and manipulating PHP class names.*

[![The most recent stable version is 2.3.1][version-image]][Semantic versioning]
[![Current build status image][build-image]][Current build status]
[![Current coverage status image][coverage-image]][Current coverage status]

## Installation and documentation

* Available as [Composer] package [eloquent/cosmos].
* [API documentation] available.

## What is *Cosmos*?

*Cosmos* is a library for representing and manipulating PHP class names. It
features a comprehensive API for performing many tasks, including:

- Parsing *resolution contexts* (sets of `namespace` and/or `use` statements)
  for classes, interfaces, and traits
- Resolving class name references relative to a resolution context
- Finding the shortest reference to a qualified class name relative to a
  resolution context
- Generating an optimal set of `use` statements for a set of class names

*Cosmos* is primarily designed to aid in code generation, and resolution of
class names contained in comment annotations, but should be useful in any
circumstance where run-time class name resolution is involved.

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
