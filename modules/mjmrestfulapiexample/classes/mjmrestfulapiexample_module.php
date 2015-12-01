<?
class MjmRestfulApiExample_Module extends Core_ModuleBase {

    protected function createModuleInfo() {
        return new Core_ModuleInfo(
        "MJM RESTful API Usage Example",
        "Example for enabling REST access",
        "Lost Lemon User"
        );
    }


    public function register_access_points(){
        /*
         * Register your access point for the API,
         * and direct it to a function.
         *
         * In this case url: http://{your site root}/api_v1
         * runs function run_rest_api_v1
         */
        return array(
        'api_v1'=>'run_rest_api_v1',
        );
    }


    public function run_rest_api_v1($url_params){

    //load the restful router
    $router = new MjmRestful_Router();

    /*
     * MjmRestful_Authenticate_Customer
     * adds a customer authentication layer to router
     * LOGIN ROUTE GET: /session/customer/:id  , if not authenticated send parameters `email` `password`
     * RESET PASSWORD GET|POST|PUT: /session/customer_reset_pw/
     * REGISTER CUSTOMER POST: /session/customer/
     * UPDATE CUSTOMER PUT: /session/customer/:id
     * LOGOUT CUSTOMER DELETE: /session/customer/:id
     */
        $auth = new MjmRestful_Authenticate_Customer($router);

    /*
     * MjmRestful_Location_API
     * adds routes to retrieve country and states
     * COUNTIES GET: /location/countries/:id
     * STATES GET: /location/states/:country_id/
     * STATE GET: /location/state/:id/
     */
        $location_api = new MjmRestful_Location_API($router);




        //load your api handler class, this is where you keep your functions for your routes
        $api = new MjmRestfulApiExample_V1();

        /*
         * Now you can set your routes.
           BELOW ARE SOME EXAMPLE ROUTES:
        /*
         * Dealing with a specific product review
         * Just as an example: how to set up get, put, post and delete methods on the same route uri
         */
        //SET THE ROUTE FOR MANAGING A PRODUCT REVIEW RECORD
        $route_for_product_review = '/product/review/:review_id';
        /*
         * GET Product Review Details
         * Creates an access point to retrieve the product review
         * Method: GET
         */
        //set which function should be called from the API
        $callback = array($api, 'get_ProductReview');
        //attach the callback to the get route.
        $router->get($route_for_product_review, $callback);

        /*
         * UPDATE Product Review Details
         * Creates an access point to update a review
         * Method: PUT
         */
        //update review - requires customer login
        $options = array('requires_authentication'=>true);
        $callback = array($api, 'update_ProductReview');
        //attach the callback to the put route.
        $router->put($route_for_product_review, $callback);


        /*
         * CREATE Product Review Details
         * Creates an access point to create a new review
         * Method: POST
         */
        //update review - requires customer login
        $options = array('requires_authentication'=>true);
        $callback = array($api, 'create_ProductReview');
        //attach callback to a post path with authentication requirement
        $router->post($route_for_product_review, $callback);


        /*
         * DELETE Product Review Details
         * Creates an access point to delete a review
         * Method: DELETE
         */
        //delete review - requires customer login
        $options = array('requires_authentication'=>true);
        $callback = array($api, 'delete_ProductReview');
        //attach callback to a delete path with authentication requirement
        $router->delete($route_for_product_review,$callback);



    //OTHER ROUTE FORMAT EXAMPLES

        //ALL CUSTOMER REVIEWS
        $route  ='/customer/products/reviews/';

        //ALL PRODUCT REVIEWS
        $route  ='/product/reviews/:product_id';

        //ALL PRODUCTS
        $route = '/products/';

        //A PRODUCTS CATEGORIES
        $route = '/product/categories/:product_id';



        /*
         * END ROUTES
         * Hopefully you get the idea.
         * FOR EACH ROUTE URL you can set a GET,POST,PUT,DELETE,PATCH method.
         * Notice that all url parameters must be at the end of the route.
         * The routing on this module has some limitations, so keep it simple.
         * You cannot do
         * /product/:id/categories/
         */


        /**
         *  START YOUR API
         *  set the run options and run it.
         */

        //DEFAULT RESPONSE FORMAT
        //specify format to respond with
        $run_options = array('format'=>'json');

        //RUN API APP
        $router->run($url_params,$run_options );

        //This module is now serving.
        }
}