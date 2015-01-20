#Extending Overwatch
Overwatch is designed to be easily extended with 3rd-party expectations and result reporters. This document explains how to write an extension to overwatch to include one or more of your own expectations or result reporters.

**Tip:** Overwatch keeps all of it's built in expectations and result reporters in the [ServiceBundle](../../../src/Overwatch/ServiceBundle), which may be a good template for your extension.

##Writing an extension
An Overwatch extension should take a form of a Symfony bundle. Avoid using Overwatch as the top-level namespace for your bundle, instead, use your username, for example: `zsturgess\OverwatchUDPExtensionBundle`.

Expectations and result reporters are simple classes that reside in your bundle, registered as Symfony services.

###Writing an expectation
Expectations should implement `Overwatch\ExpectationBundle\Expectation\ExpectationInterface`. When a test needs your expectation, Overwatch will call the `run()` method on your class, passing the actual and expected values as arguments.

Overwatch will assume that the expectation was successful if no exception is thrown by your expectation, and will use any return value as extra information to show on the test result screen.
Overwatch supplies `Overwatch\ExpectationBundle\Exception\ExpectationUnsatisfactoryException` and `Overwatch\ExpectationBundle\Exception\ExpectationFailedException`, which you should throw if the test being run should be marked as Unsatisfactory or Failed. The exception's error message will be shown as extra information on the test result screen.
If your expectation class throws any other exception, Overwatch will mark the test as having encountered an error. Again, the exception's error message will be shown as extra information on the test result screen.

Expectations should be registered as services with Symfony. The service should be tagged with `overwatch_expectation.expectation` and the alias for your expectation, for example:
```
overwatch_service.to_resolve_to:                                               
        class: Overwatch\ServiceBundle\Expectation\ToResolveToExpectation
        arguments: ["%overwatch_service.to_resolve_to%"]
        tags:
            -  { name: overwatch_expectation.expectation, alias: toResolveTo }
``` 

###Writing a result reporter
Result reporters should implement `Overwatch\ResultBundle\Reporter\ResultReporterInterface`. When a new test result is saved in the database, Overwatch will call the `notify()` method on your class, passing the test result object (of type `Overwatch\ResultBundle\Entity\TestResult`) as the only argument.

The onus is on the result reporter to decide which users to notify based on their alert settings and their group membership. Overwatch provides a helper method, `shouldBeAlerted()` on user objects that you can use to help you respect user's settings:
```
$receipients = [];
        
foreach ($result->getTest()->getGroup()->getUsers() as $user) {
    if ($user->shouldBeAlerted($result)) {
        $receipients[] = $user->getEmail();
    }
}
```

Result reporters should also be registered as services with Symfony, with the `overwatch_result.result_reporter` tag:
```
overwatch_service.email_reporter:
        class: Overwatch\ServiceBundle\Reporter\EmailReporter
        arguments: ["@service_container", "%overwatch_service.email_reporter%"]
        tags:
            -  { name: overwatch_result.result_reporter }
```