CHANGELOG for 1.1.1
=================
This changelog references the relevant changes (new features, changes and bugs) done in 1.1.1 version.
  * fix issue regarding not correctly formatted url in GET requests with payload

CHANGELOG for 1.1.0
=================
This changelog references the relevant changes (new features, changes and bugs) done in 1.1.0 version.
  * Added response code, Response and RequestHeader
  * Fix issue with default filters when doing GET requests with payload
  * deprecated `pingUsers()` method in favor of `pingInstance()`
  
  
Response will contain all the information based of `curl_getinfo()` and the actual result from `curl_exec()`. 

RequestHeader will contain all the data that has been send with the Curl request, this includes the payload if available