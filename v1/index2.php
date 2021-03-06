<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('memory_limit', '-1');
require_once '../include/DbHandler.php';
require '.././libs/Slim/Slim.php';
// require '.././vendor/autoload.php';

// apache_request_headers replicement for nginx
if (!function_exists('apache_request_headers')) {

    function apache_request_headers() {
       
        foreach ($_SERVER as $key => $value) {
            
            if (substr($key, 0, 5) == "HTTP_") {
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                $out[$key] = $value;
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }
}

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
// $app = new \Slim\App();
//Global Variable
$api_key = NULL;
$from = NULL;

// Checking if the request has valid api key in the 'Authorization' header
function authenticate(\Slim\Route $route) {
    
    $headers = apache_request_headers();
   
    $response = array();
    $app = \Slim\Slim::getInstance();
    // Verifying Authorization Header

    if (isset($headers['Clientkey']) && isset($headers['Appcode'])) {
        $db = new DbHandler();

        // validating api key
        $client_key = $headers['Clientkey'];
        $app_code = $headers['Appcode'];
        
        
        $res = $db->isValidClientKey($client_key,$app_code);
        if (!$res->num_rows > 0) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "ClientKey and Appcode didn't match";
            $response["api_key"] = "";
            $response["app_version"] = '';
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $api_key;
            $key_res = $res->fetch_assoc();
            $api_key = $key_res['api_key'];
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "ClientKey OR Appcode key is misssing";
        $response["api_key"] = "";
        $response["app_version"] = '';
        echoRespnse(400, $response);
        $app->stop();
    }
}

// Verifying Required Parameters
function verifyRequiredParams($required_fields,$api = null) {
   
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        if($api == 'search') {
            $response["count"] = 0;
            $response['videos'] = [];
        }
        echoRespnse(400, $response);
        $app->stop();
    }
}

// ----------------- REGISTER API ----------------- //

    $app->post('/register', function() use ($app) {
        $headers = apache_request_headers();

        verifyRequiredParams(array('app_code', 'device_type', 'android_id', 'device_token'));
        $app_code = $app->request->post('app_code');
        $android_id = $app->request->post('android_id');
        $db = new DbHandler();

        $device_type = $app->request->post('device_type');
        $device_token = $app->request->post('device_token');
        $app_version = $app->request->post('app_version');
        $timezone = $app->request->post('timezone');
        $device_model = $app->request->post('device_model');
        $device_name = $app->request->post('device_name');
        $device_memory = $app->request->post('device_memory');
        $device_os = $app->request->post('device_os');
        $tag = $app->request->post('tag');

        if ($tag == '') {
            $tag = 2;
        }

        $response = array();
        $response["error"] = true;
        $response["message"] = '';
        $response["api_key"] = "";
        $response["app_version"] = '';
        $response["web_key"] = '';

        $app_res = $db->checkApp($app_code);


        if ($app_res->num_rows > 0) {

            $headers = apache_request_headers();

            if (isset($headers['Clientkey'])) { 
                $client_key = $headers['Clientkey'];

                $res1 = $db->isValidClientKey($client_key,$app_code);

                if ($res1->num_rows > 0) {

                    $key_res = $res1->fetch_assoc();
                    global $api_key;

                    $api_key = $key_res['api_key'];

                    if($app_code == "NbFBpD") {
                        $response["share_url"] = "https://nofile.io/f/BK56d7yAgre";
                    }

                    //WITH REGISTER
                    global $memcache;
                    // $memcache->addServer('localhost', 11211) or die ("Could not connect");
                    $res = $db->createUser($memcache,$app_code, $app_version, $tag, $device_type, $android_id, $device_token, $timezone, $device_model, $device_name, $device_memory, $device_os);


                    $application = $app_res->fetch_assoc();

                    $app_version = $application['app_version'];

                    if ($res == USER_CREATE_FAILED) {

                        $response["message"] = "Oops! An error occurred while registereing";

                    } else if ($res['message'] == 1) {

                        $response["error"] = false;
                        $response["message"] = "Registered Successfully";
                        $response["api_key"] = $api_key;
                        $response["app_version"] = $app_version;

                    } else if ($res['message'] == 2) {

                        $response["error"] = false;
                        $response["message"] = "User Already exist";
                        $response["api_key"] = $api_key;
                        $response["app_version"] = $app_version;

                    }         
                } else {
                    $response["message"] = "ClientKey and Appcode didn't match";
                }
            } else {
                $response["message"] = "ClientKey OR Appcode key is misssing";
            }

        } else {
            $response["message"] = "Sorry, no application available for this app_code";
        }
        echoRespnse(201, $response);
    });
    
// ----------------- END REGISTER API ----------------- //
    
// ----------------- START Videos API ----------------- //
    
    $app->post('/get_punjabi_videos', function() use ($app) {
       
        // check for required params
        // $response = array();
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
//        $response['images'] = array();
        
        if (isset($headers['Apikey'])) {
            
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            
            // reading post params
            $app_code =$app->request->post('app_code');
            
            if($app->request->post('limit')) {
                $limit = $app->request->post('limit');
            } else {
                $limit = 20;
            }

            if($app->request->post('page')) {
                $page = $app->request->post('page');
            } else {
                $page = 1;
            }

            if($app->request->post('order_field')) {
                $order_field = $app->request->post('order_field');
                if(!in_array($order_field, array('id', 'views', 'downloads', 'created_date', 'modified_date'))) {
                    $order_field = 'id';
                }
            } else {
                $order_field = 'id';
            }

            if($app->request->post('order')) {
                $order = $app->request->post('order');
            } else {
                $order = 'desc';
            }

            if($app->request->post('tag_id')) {
                $tag_id = $app->request->post('tag_id');
            } else {
                $tag_id = 0;
            }

            $res = $db->isValidApi($apikey,$app_code);
            if (!$res->num_rows > 0) {
                
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = array();
                
            } else {
                // Limit Offset
                $tag_name = "";
                if($tag_id != 0) {
                    
                    $tag_data = $db->getPunjabiTagInfo($tag_id);
                    if(!empty($tag_data)) {
                        $tag_name = $tag_data["name"];
                    }
                    
                } else {
                    $tag_name = "";
                }
                $total_count = $db->getPunjabiVideoCount($tag_name);
               
                if ($page >= $total_count && $total_count != 0) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Invalid value for offset";
                    $response["count"] = 0;
                    $response['data'] = array();
                } else {
                    $result = $db->getPunjabiVideos($tag_id, $tag_name, $limit, $page, $order_field, $order);
                    
                    if (count($result) > 0 && !empty($result)) {
                        $response["success"] = "1";
                        $response["count"] = (int)$total_count;
                        $response['data'] = $result;
                    } else {
                        $response["success"] = "1";
                        $response["error"] = "Data Not Available.";
                        $response['data'] = array();
                    }
                }

            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
//            $response['images'] = array();
        }
        echoRespnse(200, $response);
    });
    
    $app->post('/get_god_videos', function() use ($app) {
        // check for required params
        $response = array();
        $headers = apache_request_headers();
        
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['images'] = array();
        
        if (isset($headers['Apikey'])) {
            
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            
            // reading post params
            $app_code =$app->request->post('app_code');
            
            if($app->request->post('limit')) {
                $limit = $app->request->post('limit');
            } else {
                $limit = 10;
            }

            if($app->request->post('offset')) {
                $offset = $app->request->post('offset');
            } else {
                $offset = 0;
            }

            if($app->request->post('order_field')) {
                $order_field = $app->request->post('order_field');
                if(!in_array($order_field, array('id', 'views', 'downloads', 'created_date', 'modified_date'))) {
                    $order_field = 'id';
                }
            } else {
                $order_field = 'id';
            }

            if($app->request->post('order')) {
                $order = $app->request->post('order');
            } else {
                $order = 'desc';
            }

            if($app->request->post('tag_id')) {
                $tag_id = $app->request->post('tag_id');
            } else {
                $tag_id = 0;
            }

            $res = $db->isValidApi($apikey,$app_code);
            if (!$res->num_rows > 0) {
                
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = array();
                
            } else {
                // Limit Offset
                $tag_name = "";
                if($tag_id != 0) {
                    
                    $tag_data = $db->getGodTagInfo($tag_id);
                    if(!empty($tag_data)) {
                        $tag_name = $tag_data["name"];
                    }
                    
                } else {
                    $tag_name = "";
                }
                $total_count = $db->getGodVideoCount($tag_name);
                if ($offset >= $total_count && $total_count != 0) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Invalid value for offset";
                    $response["count"] = 0;
                    $response['data'] = array();
                } else {
                    $result = $db->getGodVideos($tag_id, $tag_name, $limit, $offset, $order_field, $order);
                    
                    if (count($result) > 0 && !empty($result)) {
                        $response["success"] = "1";
                        $response["count"] = (int)$total_count;
                        $response['data'] = $result;
                    } else {
                        $response["success"] = "1";
                        $response["error"] = "Data Not Available.";
                        $response['data'] = array();
                    }
                }

            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
            $response['images'] = array();
        }
        echoRespnse(200, $response);
    }); 
    
// ----------------- END Videos API ----------------- //

// ----------------- START Get Ad ids API ----------------- //
    
    $app->post('/get_ad_ids', function() use ($app) {
        verifyRequiredParams(array('app_code'));
       
        $response = array();
        $headers = apache_request_headers();
        
        $response['ResponseState'] = [];
        $response['ResponseState']["error"] = false;
        $response['ResponseState']["message"] = "AdIds";
        $response['ResponseState']["update"] = 0;
        $response['ad_ids'] = array();
        
        if (isset($headers['Apikey'])) {
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            $app_code = $app->request->post('app_code');
          
            $res = $db->isValidApi($apikey,$app_code);
            if (!$res->num_rows > 0) {
                $response['ResponseState']["error"] = true;
                $response['ResponseState']["message"] = "Sorry, Apikey Invalid";
                $response['ResponseState']["update"] = 1;
            } else {
                $params = $db->getAdIds();
                if (count($params) > 0) {
                    $response['ResponseState']["error"] = false;
                    $response['ResponseState']["message"] = "AdIds";
                    $response['ResponseState']["Count"] = count($params);
                    $response['ad_ids'] = $params;
                } else {
                    $response['ResponseState']["error"] = true;
                    $response['ResponseState']["message"] = "Sorry, No Ad Ids available";
                    $response['ResponseState']["Count"] = 0;
                    $response['ad_ids'] = array();
                }
            }
        } else {
            $response['ResponseState']["error"] = true;
            $response['ResponseState']["message"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    });
    
    $app->post('/get_ad_ids2', function() use ($app) {
        verifyRequiredParams(array('app_code'));
        $app_code = $app->request->post('app_code');

        $response = array();

        $response['ResponseState'] = [];
        $response['ResponseState']["error"] = false;
        $response['ResponseState']["message"] = "AdIds";
        $response['ResponseState']["update"] = 0;
        $response['ad_ids'] = array();
        
        if($app_code != '' ){
            $db = new DbHandler();
            $params = $db->getAdIds2($app_code);
            if (count($params) > 0) {
                $response['ResponseState']["error"] = false;
                $response['ResponseState']["message"] = "AdIds";
                $response['ResponseState']["Count"] = count($params);
                $response['ad_ids'] = $params;
            } else {
                $response['ResponseState']["error"] = true;
                $response['ResponseState']["message"] = "Sorry, No Ad Ids available";
                $response['ResponseState']["Count"] = 0;
                $response['ad_ids'] = array();
            }
        }else {
            $response['ResponseState']["error"] = true;
            $response['ResponseState']["message"] = "App Code Can not be blank!";
            $response['ResponseState']["Count"] = 0;
            $response['ad_ids'] = array();
        }
        echoRespnse(200, $response);
    });
    
// ----------------- END Get Ad ids API ----------------- //

// ----------------- START Text Status API ----------------- //
    
    $app->post('/get_punjabi_textstatus', function() use ($app) {
        // check for required params
        $response = array();
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = "";
        
        if (isset($headers['Apikey'])) {
            
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            
            // reading post params
            $app_code = $app->request->post('app_code');
            
            if($app->request->post('limit')) {
                $limit = $app->request->post('limit');
            } else {
                $limit = 20;
            }
            
            if($app->request->post('page')) {
                $page = $app->request->post('page');
            } else {
                $page = 1;
            }

            if($app->request->post('order')) {
                $order = $app->request->post('order');
            } else {
                $order = 'desc';
            }

            if($app->request->post('tag_id')) {
                $tag_id = $app->request->post('tag_id');
            } else {
                $tag_id = 0;
            }
            
            $res = $db->isValidApi($apikey, $app_code);
            if (!$res->num_rows > 0) {
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = [];
            } else {
                // Limit Offset
                $total_count = $db->getPunjabiTextCount($tag_id);
                
                if ($page >= $total_count) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Invalid value for offset";
                    $response["count"] = 0;
                    $response['data'] = [];
                } else {
                    
                    $result = $db->getPunjabiTextStatus($tag_id, $page, $limit,  $order);
                   
                    if (count($result) > 0 && !empty($result)) {
                        $response["success"] = "1";
                        $response["count"] = (int)$total_count;
                        $response['data'] = $result;
                    } else {
                        $response["success"] = "0";
                        $response["error"] = "Data Not Available.";
                        $response['data'] = [];
                    }
                }
            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
            $response['data'] = [];
        }
        echoRespnse(200, $response);
    });
    
    $app->post('/get_god_textstatus', function() use ($app) {
        // check for required params
        $response = array();
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = "";
        
        if (isset($headers['Apikey'])) {
            
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            
            // reading post params
            $app_code = $app->request->post('app_code');
            
//            if($app->request->post('limit')) {
//                $limit = $app->request->post('limit');
//            } else {
//                $limit = 10;
//            }
//
//            if($app->request->post('offset')) {
//                $offset = $app->request->post('offset');
//            } else {
//                $offset = 0;
//            }
            
            if($app->request->post('page')) {
                $page = $app->request->post('page');
            } else {
                $page = 1;
            }

            if($app->request->post('order')) {
                $order = $app->request->post('order');
            } else {
                $order = 'desc';
            }

            if($app->request->post('cat_id')) {
                $cat_id = $app->request->post('cat_id');
            } else {
                $cat_id = 0;
            }
            
            $res = $db->isValidApi($apikey, $app_code);
            if (!$res->num_rows > 0) {
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = [];
            } else {
                
                // Limit Offset
                $total_count = $db->getGodTextCount($cat_id);
                
                if ($offset >= $total_count) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Invalid value for offset";
                    $response["count"] = 0;
                    $response['data'] = [];
                } else {
                    
                    $result = $db->getGodTextStatus($cat_id, $limit, $offset,  $order);
                    
                    if (count($result) > 0 && !empty($result)) {
                        $response["success"] = "1";
                        $response["count"] = (int)$total_count;
                        $response['data'] = $result;
                    } else {
                        $response["success"] = "0";
                        $response["error"] = "Data Not Available.";
                        $response['data'] = [];
                    }
                }
            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
            $response['data'] = [];
        }
        echoRespnse(200, $response);
    });
    
// ----------------- END Text Status API ----------------- //
    
// ----------------- START Makers API ----------------- //
    
    $app->post('/get_punjabi_makers', function() use ($app) {
        // check for required params
        $response = array();
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = "";
        
        if (isset($headers['Apikey'])) {
            
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            
            // reading post params
            $app_code = $app->request->post('app_code');
            
            if($app->request->post('limit')) {
                $limit = $app->request->post('limit');
            } else {
                $limit = 20;
            }
            
            if($app->request->post('page')) {
                $page = $app->request->post('page');
            } else {
                $page = 1;
            }

            if($app->request->post('order')) {
                $order = $app->request->post('order');
            } else {
                $order = 'desc';
            }

            if($app->request->post('tag_id')) {
                $tag_id = $app->request->post('tag_id');
            } else {
                $tag_id = 0;
            }
            
            if($app->request->post('order_field')) {
                $order_field = $app->request->post('order_field');
                if(!in_array($order_field, array('id', 'views', 'downloads', 'created_date', 'modified_date'))) {
                    $order_field = 'id';
                }
            } else {
                $order_field = 'id';
            }
            
            $res = $db->isValidApi($apikey, $app_code);
            if (!$res->num_rows > 0) {
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = [];
            } else {
                // Limit Offset
                $tag_name = "";
                if($tag_id != 0) {
                    
                    $tag_data = $db->getPunjabiTagInfo($tag_id);
                    if(!empty($tag_data)) {
                        $tag_name = $tag_data["name"];
                    }
                    
                } else {
                    $tag_name = "";
                }
              
                $total_count = $db->getPunjabiMakersCount($tag_name);
            
                if ($page >= $total_count && $total_count != 0) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Invalid value for offset";
                    $response["count"] = 0;
                    $response['data'] = array();
                } else {
                    $result = $db->getPunjabiMakers($tag_id, $tag_name, $limit, $page,$order, $order_field);
                  
                    if (count($result) > 0 && !empty($result)) {
                        $response["success"] = "1";
                        $response["count"] = (int)$total_count;
                        $response['data'] = $result;
                    } else {
                        $response["success"] = "1";
                        $response["error"] = "Data Not Available.";
                        $response['data'] = array();
                    }
                }
            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
            $response['data'] = [];
        }
        echoRespnse(200, $response);
    });
    
    $app->post('/get_god_makers', function() use ($app) {
        // check for required params
        $response = array();
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = "";
        
        if (isset($headers['Apikey'])) {
            
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            
            // reading post params
            $app_code = $app->request->post('app_code');
            
//            if($app->request->post('limit')) {
//                $limit = $app->request->post('limit');
//            } else {
//                $limit = 10;
//            }
//
//            if($app->request->post('offset')) {
//                $offset = $app->request->post('offset');
//            } else {
//                $offset = 0;
//            }
            
            if($app->request->post('page')) {
                $page = $app->request->post('page');
            } else {
                $page = 1;
            }

            if($app->request->post('order')) {
                $order = $app->request->post('order');
            } else {
                $order = 'desc';
            }

            if($app->request->post('cat_id')) {
                $cat_id = $app->request->post('cat_id');
            } else {
                $cat_id = 0;
            }
            
            $res = $db->isValidApi($apikey, $app_code);
            if (!$res->num_rows > 0) {
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = [];
            } else {
                
                // Limit Offset
                $total_count = $db->getGodTextCount($cat_id);
                
                if ($offset >= $total_count) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Invalid value for offset";
                    $response["count"] = 0;
                    $response['data'] = [];
                } else {
                    
                    $result = $db->getGodTextStatus($cat_id, $limit, $offset,  $order);
                    
                    if (count($result) > 0 && !empty($result)) {
                        $response["success"] = "1";
                        $response["count"] = (int)$total_count;
                        $response['data'] = $result;
                    } else {
                        $response["success"] = "0";
                        $response["error"] = "Data Not Available.";
                        $response['data'] = [];
                    }
                }
            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
            $response['data'] = [];
        }
        echoRespnse(200, $response);
    });
    
// ----------------- END Makers API ----------------- //
    
// ----------------- START Tags API ----------------- //
    
    $app->post('/get_punjabi_tags', function() use ($app) {
        verifyRequiredParams(array('app_code'));
        
        $app_code = $app->request->post('app_code');
        $tag_type = $app->request->post('tag_type');
        
        $response = array();
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = array();
        
        if (isset($headers['Apikey'])) {
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            $res = $db->isValidApi($apikey,$app_code);
            if (!$res->num_rows > 0) {
                $response['data'] = array();
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
            } else {
                
                $result = $db->getPunjabiTags();
//                $result = $db->getPunjabiTagsTest($tag_type);
                
                if (count($result) > 0 && !empty($result)) {
                    $response["success"] = "1";
                    $response["count"] = count($result);
                    $response['data'] = $result;
                } else {
                    $response['data'] = array();
                    $response["success"] = "0";
                    $response["error"] = "Data Not Available.";
                }
            }
        } else {
            $response['data'] = array();
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    }); 
    
    $app->post('/get_god_tags', function() use ($app) {
        verifyRequiredParams(array('app_code'));
        
        $app_code = $app->request->post('app_code');
        
        $response = array();
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = array();
        if (isset($headers['Apikey'])) {
            
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            $res = $db->isValidApi($apikey,$app_code);
            
            if (!$res->num_rows > 0) {
                $response['data'] = array();
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
            } else {
                $result = $db->getGodTags();
                
                if (count($result) > 0 && !empty($result)) {
                    $response["success"] = "1";
                    $response["count"] = count($result);
                    $response['data'] = $result;
                } else {
                    $response['data'] = array();
                    $response["success"] = "0";
                    $response["error"] = "Data Not Available.";
                }
            }
        } else {
            $response['data'] = array();
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    }); 
    
// ----------------- END Tags API ----------------- //
    
// ----------------- START Images API ----------------- //
    
    $app->post('/get_punjabi_images', function() use ($app) {
      
        // check for required params
        $response = array();
        $headers = apache_request_headers();
        
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['images'] = array();
      
        if (isset($headers['Apikey'])) {
            
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            
            // reading post params
            $app_code =$app->request->post('app_code');
            
            if($app->request->post('limit')) {
                $limit = $app->request->post('limit');
            } else {
                $limit = 20;
            }

            if($app->request->post('page')) {
                $page = $app->request->post('page');
            } else {
                $page = 1;
            }

            if($app->request->post('order_field')) {
                $order_field = $app->request->post('order_field');
                if(!in_array($order_field, array('id', 'views', 'downloads', 'created_date', 'modified_date'))) {
                    $order_field = 'id';
                }
            } else {
                $order_field = 'id';
            }

            if($app->request->post('order')) {
                $order = $app->request->post('order');
            } else {
                $order = 'desc';
            }

            if($app->request->post('tag_id')) {
                $tag_id = $app->request->post('tag_id');
            } else {
                $tag_id = 0;
            }

            $res = $db->isValidApi($apikey,$app_code);
            if (!$res->num_rows > 0) {
                
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = array();
                
            } else {
                // Limit Offset
                $tag_name = "";
                if($tag_id != 0) {
                    
                    $tag_data = $db->getPunjabiTagInfo($tag_id);
                    if(!empty($tag_data)) {
                        $tag_name = $tag_data["name"];
                    }
                    
                } else {
                    $tag_name = "";
                }
                $total_count = $db->getPunjabiImageCount($tag_name);
                if ($page >= $total_count && $total_count != 0) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Invalid value for offset";
                    $response["count"] = 0;
                    $response['data'] = array();
                } else {
                    $result = $db->getPunjabiImages($tag_id, $tag_name, $limit, $page, $order_field, $order);
                   
                    if (count($result) > 0 && !empty($result)) {
                        $response["success"] = "1";
                        $response["count"] = (int)$total_count;
                        $response['data'] = $result;
                    } else {
                        $response["success"] = "1";
                        $response["error"] = "Data Not Available.";
                        $response['data'] = array();
                    }
                }

            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
            $response['images'] = array();
        }
        echoRespnse(200, $response);
    });
    
    $app->post('/get_god_images', function() use ($app) {
        // check for required params
        $response = array();
        $headers = apache_request_headers();
        
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['images'] = array();
        
        if (isset($headers['Apikey'])) {
            
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            
            // reading post params
            $app_code =$app->request->post('app_code');
            
            if($app->request->post('limit')) {
                $limit = $app->request->post('limit');
            } else {
                $limit = 10;
            }

            if($app->request->post('offset')) {
                $offset = $app->request->post('offset');
            } else {
                $offset = 0;
            }

            if($app->request->post('order_field')) {
                $order_field = $app->request->post('order_field');
                if(!in_array($order_field, array('id', 'views', 'downloads', 'created_date', 'modified_date'))) {
                    $order_field = 'id';
                }
            } else {
                $order_field = 'id';
            }

            if($app->request->post('order')) {
                $order = $app->request->post('order');
            } else {
                $order = 'desc';
            }

            if($app->request->post('tag_id')) {
                $tag_id = $app->request->post('tag_id');
            } else {
                $tag_id = 0;
            }

            $res = $db->isValidApi($apikey,$app_code);
            if (!$res->num_rows > 0) {
                
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = array();
                
            } else {
                // Limit Offset
                $tag_name = "";
                if($tag_id != 0) {
                    
                    $tag_data = $db->getGodTagInfo($tag_id);
                    if(!empty($tag_data)) {
                        $tag_name = $tag_data["name"];
                    }
                    
                } else {
                    $tag_name = "";
                }
                $total_count = $db->getGodImageCount($tag_name);
                if ($offset >= $total_count && $total_count != 0) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Invalid value for offset";
                    $response["count"] = 0;
                    $response['data'] = array();
                } else {
                    $result = $db->getGodImages($tag_id, $tag_name, $limit, $offset, $order_field, $order);
                    
                    if (count($result) > 0 && !empty($result)) {
                        $response["success"] = "1";
                        $response["count"] = (int)$total_count;
                        $response['data'] = $result;
                    } else {
                        $response["success"] = "1";
                        $response["error"] = "Data Not Available.";
                        $response['data'] = array();
                    }
                }

            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
            $response['images'] = array();
        }
        echoRespnse(200, $response);
    });
    
// ----------------- END Images API ----------------- //

// Response encoding
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json');
    echo json_encode($response);
}

function time_elapsed_string($ptime) {
    $etime = time() - $ptime;

    if ($etime < 1) {
        return '0 seconds';
    }

    $a = array(365 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    $a_plural = array('year' => 'years',
        'month' => 'months',
        'day' => 'days',
        'hour' => 'hours',
        'minute' => 'minutes',
        'second' => 'seconds'
    );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;

        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
        }
    }
}

$app->run();