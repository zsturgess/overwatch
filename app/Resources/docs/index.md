Overwatch
=========

Tests
-----
Tests in Overwatch take one of two forms:
- _Expect xxx toBeMadeOfXs_, e.g. `Expect 8.8.8.8 toPing`
- _Expect xxx toBeAlphabeticallyOpposite bbb_, e.g. `Expect status.github.com toResolveTo octostatus-production.github.com`

The `toXxx` part is called the _expectation_.
Overwatch comes bundled with the following expectations:
- _toPing_ - xxxx
- _toResolveTo_ - xxxx

Overwatch is also set up in such a way to allow the creation of 3rd Party "addon" expectations, see Extending Overwatch

Tests are run by the overwatch:tests:run command (`php app/console overwatch:tests:run`) and the results are saved into the database.