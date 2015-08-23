#Overwatch REST API
This page describes the resources that make up the official Overwatch API.

##Overview
The Overwatch API is accessed via the `/api` path, for example, an Overwatch installation at `https://overwatch.example.com/` will expose it's API on `https://overwatch.example.com/api`.
If you plan on using the Overwatch API on your installation, it is *highly recommended* that you set up communication over HTTPS.

##Authentication
The Overwatch API is stateless, meaning you must provide authentication details for each and every API request.
To Authenticate, you must attach 3 headers to your request:

| Header | Contents | Type | Example |
| ------ | -------- | ---- | ------- |
| X-API-User | Your User ID, which you can find on the _My Account_ page | integer | 3 |
| X-API-Timestamp | The Unix timestamp of the request with second-resolution (i.e. the output from PHP's `time()` function) | timestamp | 1440334057 |
| X-API-Token | The timestamp, signed with your API Key | string | bbf...b86 |

The timestamp provided must be no more than 60 seconds in the past or the future.
The token should be identical to the string `timestamp=<X-API-Timestamp>` once passed through HMAC-SHA256 with your API Key as the secret. Or, in PHP:
````
hash_hmac(
    "sha256",
    "timestamp=" . $headers['X-API-Timestamp'],
    $YOUR_API_KEY
)
````

##Endpoints & Inputs
The list of API Endpoints is given in the auto-generated API documentation, a link to which you can find on the _My Account_ page.
Each endpoint can be clicked on for a list of requirements, filters and parameters:
* The _Requirements_ section details what each `{route parameter}` is for, and what kind of data to pass.
* The _Filters_ section (GET requests only) details optional filtering query parameters you can pass. (e.g. `?pageSize=11`)
* The _Parameters_ section (POST/PUT requests only) details parameters that are expected in the JSON-encoded body of the request.

##Responses
The Overwatch API endevours to respond with an appropriate HTTP response code and details in a JSON-encoded body in all cases:

| HTTP Code | Reason |
| ---- | ---- |
| 200 OK | The request was handled successfully. |
| 201 Created | The request was handled successfully, causing the creation of a new entity. |
| 204 No Content | The request was handled successfully, and there is no further information. (Common after a DELETE request) |
| 400 Bad Request | The request body could not be JSON-decoded. Ensure that the body is valid JSON, or that your request sends an appropriate `Content-Type` header. |
| 401 Unauthorized | The authentication details you provided were incorrect or invalid. The response body will contain more information. |
| 403 Forbidden | The API user that has been authenticated is not authorized to perform the request. |
| 404 Not Found | The API endpoint you are using does not exist, or you are trying to perform an action on an entity that does not exist |
| 422 Unprocessable Entity | The request could not be completed because it contained invalid data. The response body will contain more information. |
| 500 Internal Server Error | The request could not be completed. There is likely a bug in the Overwatch API. |