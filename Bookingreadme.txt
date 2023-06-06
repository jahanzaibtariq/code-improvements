Here is the core Points which I Updated
1. Code Organization: The code updated with grouping related methods together, improving the maintainability.

2. Dependency Injection: The `BookingRepository` is now injected into the `BookingController` constructor using dependency injection.

3. Null Return: In the `getHistory` method, if no user ID is provided, the method now returns `null` instead of an empty response.

4. Conditional Simplification: In the `distanceFeed` method, the conditional checks for `distance` and `time` have been simplified using the null coalescing operator (`??`). If the values are not provided in the request, empty strings are used as defaults.

5. Database Updates: The database update operations in the `distanceFeed` method updated. The `update` method now directly accepts an array of column-value pairs instead of using `array_except` to remove specific keys.

6. Response Messages: The response messages have been updated and more professional. The method now returns a response with the message "Record updated!" after successful updates.

7. Exception Handling: In the `resendSMSNotifications` method, an exception handling block has been added to catch any exceptions that may occur during the sending of SMS notifications. The exception message is included in the response to provide feedback in case of errors.