USAGE of the Marello Bridge API
=================


## Client Setup
In order to start calling the Bridge API you need to setup the Client appropriately. The next example will show the correct usage of setting up the Client.

```php
namespace My\Super\Cool\Namespace;
    
use Marello\Api\Client;
    
class MyClassWhichIsGoingToUseTheClient 
{
    /** @var Client $client */
    protected $client;
    
    /**
    * {@inheritdoc}
    */
    public function __construct()
    {
        $this->client = new Client('http://example.com');
        $this->client->setAuth([
            'username' => 'test',
            'api_key' => 'test'
        ]);
    }
}
```

Before start calling the API make sure you've setup the Authentication, or it will fail to actually connect to the Marello Instance.
You can setup the authentication by calling `setAuth()` on the client with an array as parameter. This array should contain the username of the user and the API key generated for this user.


## Rest Calls
With the Bridge API you can do different types of calls, GET, POST, PUT and DELETE. Every type of call will be explained below.

### Rest GET (without payload)
In order to create a rest GET call, all you have to do is specify the endpoint (method) you would like to hit. Don't forget to setup the Client first ;)

```php
namespace My\Super\Cool\Namespace;
    
use Marello\Api\Client;
    
class MyClassWhichIsGoingToUseTheClient 
{
    /** @var Client $client */
    protected $client;
    
    /**
    * {@inheritdoc}
    */
    public function __construct()
    {
        $this->client = new Client('http://example.com');
        $this->client->setAuth([
            'username' => 'test',
            'api_key' => 'test'
        ]);
    }
    
    /**
    * will hit the Marello Instance API with the /users endpoint and will get a list of users
    */
    public function getUsers()
    {
        $this->client->restGet('/users');
    }
}
```

### Rest GET (with payload)
In order to create a rest GET call with a payload, you need to give the payload with the `restGet()` as a parameter. The payload will be an array of keys corresponding with available filters and or params in the Marello instance.

```php
namespace My\Super\Cool\Namespace;
    
use Marello\Api\Client;
    
class MyClassWhichIsGoingToUseTheClient 
{
    /** @var Client $client */
    protected $client;
    
    /**
    * {@inheritdoc}
    */
    public function __construct()
    {
        $this->client = new Client('http://example.com');
        $this->client->setAuth([
            'username' => 'test',
            'api_key' => 'test'
        ]);
    }
    
    /**
    * Will hit the Marello Instance API with the /users endpoint and will get a list of users
    * from page 2 and will set the limit to 200
    */
    public function getUsers()
    {
        $this->client->restGet('/users', ['page' => 2, 'limit' => 200]);
    }
    
    /**
    * Will hit the Marello Instance API with the /users endpoint and will get a single user
    * based on the id
    * @param int $id
    */
    public function getUserById($id)
    {
        $this->client->restGet('/users', ['id' => 1]);
    }
}
```

The GET call with the payload `['page' => 2, 'limit' => 200]`, will translate into something like `http://example.com/api/rest/latest/users?page=2&limit=200`
 
### Rest POST
In order to create a rest POST call, you need to give the payload with the `restPost()` as a parameter. The payload will be an array of keys corresponding with available fields exposed in the API in the Marello instance.
 
```php
namespace My\Super\Cool\Namespace;
     
use Marello\Api\Client;
     
class MyClassWhichIsGoingToUseTheClient 
{
    /** @var Client $client */
    protected $client;
     
    /**
    * {@inheritdoc}
    */
    public function __construct()
    {
        $this->client = new Client('http://example.com');
        $this->client->setAuth([
            'username' => 'test',
            'api_key' => 'test'
        ]);
    }
     
    /**
    * will hit the Marello Instance API with the /users endpoint and will create a new order with an existing customer
    */
    public function createNewOrder()
    {
        $data = [
            'orderReference'  => 333444,
            'salesChannel'    => 'my_channel_code,
            'subtotal'        => 365.00,
            'totalTax'        => 76.65,
            'grandTotal'      => 365.00,
            'paymentMethod'   => 'creditcard',
            'paymentDetails'  => 'Visa card, ref: xxxxxx-xxxx-xxxx',
            'shippingMethod'  => 'freeshipping',
            'discountAmount'  => 10,
            'couponCode'      => 'XFZDSFSDFSFSD',
            'shippingAmountInclTax'  => 8,
            'shippingAmountExclTax'  => 5,
            'customer'        => 1,
            'billingAddress'  => [
                'firstName'  => 'John',
                'lastName'   => 'Doe',
                'country'    => 'NL',
                'street'     => 'ToeseeStreet 20',
                'city'       => 'Somewhere',
                'region'     => 'NL-NB',
                'postalCode' => '3000 XX',
            ],
            'shippingAddress' => [
                'firstName'  => 'John',
                'lastName'   => 'Doe',
                'country'    => 'NL',
                'street'     => 'ToeseeStreet 20',
                'city'       => 'Somewhere',
                'region'     => 'NL-NB',
                'postalCode' => '3000 XX',
            ],
            'items'           => [
                [
                    'product'               => 'p1',
                    'quantity'              => 1,
                    'price'                 => 150.10,
                    'originalPriceInclTax'  => 150.10,
                    'originalPriceExclTax'  => 140.10,
                    'purchasePriceIncl'     => 190.00,
                    'tax'                   => 39.90,
                    'taxPercent'            => 0.21,
                    'rowTotalInclTax'       => 190.00,
                    'rowTotalExclTax'       => 180.00,
                ],
                [
                    'product'               => 'p2',
                    'quantity'              => 1,
                    'price'                 => 138.25,
                    'originalPriceInclTax'  => 138.25,
                    'originalPriceExclTax'  => 128.25,
                    'purchasePriceIncl'     => 175.00,
                    'tax'                   => 36.75,
                    'taxPercent'            => 0.21,
                    'rowTotalInclTax'       => 175.00,
                    'rowTotalExclTax'       => 165.00,
                ],
            ],
        ];
        $this->client->restPost('/orders', $data);
    }
}
 ```
 
### Rest PUT
In order to create a rest PUT call, you need to give the payload with the `restPut()` as a parameter. The payload will be an array of keys corresponding with available fields exposed to update in the API in the Marello instance.
  
```php
namespace My\Super\Cool\Namespace;
  
use Marello\Api\Client;
  
class MyClassWhichIsGoingToUseTheClient 
{
     /** @var Client $client */
     protected $client;
      
     /**
     * {@inheritdoc}
     */
     public function __construct()
     {
         $this->client = new Client('http://example.com');
         $this->client->setAuth([
             'username' => 'test',
             'api_key' => 'test'
         ]);
     }
      
     /**
     * will hit the Marello Instance API with the /orders endpoint and will try to update an existing order
     * @param int $orderId
     */
     public function updateExistingOrder($orderId)
     {
         $time = new \DateTime();
         $data = [
             'id' => $orderId,
             'billingAddress'  => [
                 'firstName'  => 'Han',
                 'lastName'   => 'Solo',
                 'country'    => 'US',
                 'street'     => 'Hollywood Blvd',
                 'city'       => 'Beverly Hills',
                 'region'     => 'US-CA',
                 'postalCode' => '91020',
             ],
             'shippingAddress' => [
                 'firstName'  => 'Han',
                 'lastName'   => 'Solo',
                 'country'    => 'US',
                 'street'     => 'Hollywood Blvd',
                 'city'       => 'Alderaan',
                 'region'     => 'US-CA',
                 'postalCode' => '90210',
             ],
             'paymentReference'  => 1223456,
             'invoicedAt'        => $time->format('d-m-Y H:i:s'),
             'invoiceReference'  => 666555444
         ];     
         $this->client->restPut('/orders', $data);
     }
}
```

### Rest DELETE
In order to create a rest DELETE call, you need to give the payload with the `restDelete()` as a parameter. The payload will be an array with the ID of the user in the Marello instance.
  
```php
namespace My\Super\Cool\Namespace;
  
use Marello\Api\Client;
  
class MyClassWhichIsGoingToUseTheClient 
{
      /** @var Client $client */
      protected $client;
      
      /**
      * {@inheritdoc}
      */
      public function __construct()
      {
          $this->client = new Client('http://example.com');
          $this->client->setAuth([
              'username' => 'test',
              'api_key' => 'test'
          ]);
      }
      
      /**
      * Will hit the Marello Instance API with the /users endpoint and will delete the user with id 1
      * @param int $userId
      */
      public function deleteUser($userId)
      {
          $this->client->restDelete('/users', ['id' => 1]);
      }
}
``` 