<?phpclass RemoteXhrResponder extends BaseModel{  protected $api , $apiInterface , $request = null , $raw_response = null, $response = null;  const STATUS_SUCCESS = "success";  const STATUS_ERROR = "error";  public function __construct( $apiInterface )  {    $this->apiInterface = $apiInterface;    $this->api = new RemoteXhrApiDelegate( $apiInterface , array($this,'sendRequest') );    parent::__construct();  }  private function curl_post( $url , $data )  {    $field_string = http_build_query($data);    //open connection    $ch = curl_init();    //set the url, number of POST vars, POST data    curl_setopt($ch, CURLOPT_URL, $url);    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    curl_setopt($ch, CURLOPT_POST, count($data));    curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);    $response = curl_exec($ch);    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);    curl_close($ch);    return  array($http_status,$response);  }  public function sendRequest( $method, $params )  {    $params['action'] = $method;    $url = $this->apiInterface->getUrl();    $this->request = array(      "url" => $url,      "params" => $params    );    list( $http_status , $raw_response ) = $this->curl_post( $url , $params ) ;    $this->raw_response = $raw_response;    $response = json_decode( $raw_response , true );    if ( $response === NULL )    {      throw new Exception("Cannot decode response: $response");    }    $this->response = $response;    if ( $http_status != 200 )    {      throw new Exception("Remote endpoint error: " . $http_status);    }    if ( $response['status'] != self::STATUS_SUCCESS )    {      throw new Exception( $response['message'] );    }    return $response['result'];  }  public function getRequest()  {    return $this->request;  }  public function getResponse()  {    return $this->response;  }  public function getRawResponse()  {    return $this->raw_response;  }}
