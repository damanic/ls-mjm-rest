A module that enables simple RESTful API modules.

The modules in this repository can be used for trial and development purposes, if you find it useful please install via the lemonstand market place for support and stable updates.

Code contributions are welcome.


/* @TODO
 *  - Allow to set header OPTIONS on router __construct
 *  - Auto add routes to header OPTIONS
 *  - Allow set config on router run()
 *  - Allow set config on route add()
 *  - Force HTTPS on authentication routes by default
 *  - Allow caching
 *  - Clean up trigger to delete old keys
 *  - Set up a Config page to set:
 *      - API TOKEN NAME
 *      - API TOKEN EXPIRY IN DAYS
 *      - LOCK TOKEN TO DEVICE+IP  ON/OFF
 *      - force_correct_request_type  !!!! REDUNDANT SEE  = !*!&!
 *      - force HTTPS for authentication ON/OFF
 *  = !*!&! A path must be able to take combination of PUT/GET/POST/DELETE
 *          and route correctly based on detected request type.
 *  - GET parameters Could be supported eg.  /products/?q=search_query
 *  - Logout / Delete Token Route. Reserved
 *  - MjmRestful_Authenticate is extended by MjmRestful_AuthCustomer and MjmRestful_AuthAdmin
 *  - MjmRestful_Router takes and extension of MjmRestful_Authenticate, defaults to MjmRestful_AuthCustomer
 *
 *
 */