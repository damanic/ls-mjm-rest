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

    //add customer authentication layer to router
    $auth = new MjmRestful_Authenticate_Customer($router);

    //load your api handler class, where you have placed all the functions for your Routes
    $api = new MjmRestfulApiExample_V1();


        /*
         * Now you can set your routes.
         * There is a reserved POST route: /authentication/login
         * This route is set up by the router on construct
         * and is always available for customer logins.
         * To login post: username , post: password
         * The route will return field:token on successful login.
         * You can store the token in your APP for future authorisation.
         * To use the token in a request, place it in the header: X-Lemonstand-Api-Token


           HERE ARE SOME EXAMPLE ROUTES:
        */

        /**
         * GET Customer Account Details
         * Creates a route to retrieve the logged in customers account details.
         * Method: GET
         */
        //The callback for this route. Fires RestfulApiExample_V1::get_Customer
        $callback = array($api, 'get_Customer');
        //authentication required
        $options = array('requires_authentication'=>true);
        //set the route URI. This becomes accessible via {yoursitedomain}/api_v1/session/customer
        $router->get('/session/customer/', $callback, $options);



        /**
         * Dealing with a specific product review
         * Just as an example, how to get, update and delete a review
         */
        //SET THE ROUTE FOR ACCESSING A PRODUCT REVIEW RECORD
        $route_for_product_review = '/product/review/:review_id';


        /**
         * GET Product Review Details
         * Creates an access point to retrieve the product review
         * Method: GET
         */
        //set which function should be called from the API
        $callback = array($api, 'get_ProductReview');
        //attach the callback to a path.
        //here :id is added as a required url parameter
        $router->get($route_for_product_review, $callback);

        /**
         * UPDATE Product Review Details
         * Creates an access point to update a review
         * Method: PUT
         */
        //update review - requires customer login
        $options = array('requires_authentication'=>true);
        $callback = array($api, 'update_ProductReview');
        //attach callback to a put path with authentication requirement
        $router->put($route_for_product_review, $callback);


        /**
         * CREATE Product Review Details
         * Creates an access point to create a new review
         * Method: POST
         */
        //update review - requires customer login
        $options = array('requires_authentication'=>true);
        $callback = array($api, 'create_ProductReview');
        //attach callback to a put path with authentication requirement
        $router->post($route_for_product_review, $callback);


        /**
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
         * FOR EACH ROUTE URL you can set a GET,POST,PUT,DELETE method.
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
        }
}