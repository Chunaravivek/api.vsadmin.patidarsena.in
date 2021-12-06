<?php

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


        //To Check Add Client Key
        $app_code_array = array("ADA2BR");
        if(in_array($app_code, $app_code_array)) {  

            $line = "------NEW ENTRY--------";
            $txt = " app_code: ".$app_code." clientkey: ".$headers['Clientkey']. " APIKEY: ".$api_key;
            $resp['response'] = $app->request->post();        
            $myfile = file_put_contents('log_clientkey.txt', $txt.PHP_EOL.$line.PHP_EOL. print_r($resp, true) , FILE_APPEND | LOCK_EX);

        }
        echoRespnse(201, $response);
    });
    
// ----------------- END REGISTER API ----------------- //

// ----------------- START Text Categories2 API ----------------- //
    
    $app->post('/get_list_punjabi_categories2', function() use ($app) {
        verifyRequiredParams(array('app_code'));
       
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = "";
        $headers = apache_request_headers();
        
        if (isset($headers['Apikey'])) {
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            $app_code = $app->request->post('app_code');
            
            $res = $db->isValidApi($apikey,$app_code);
            
            if (!$res->num_rows > 0) {
                
                $response['data'] = [];
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey and Appcode didn't matched";
                
            } else {
                
                $result = $db->getPunjabiTextCategories2();
            
                if (count($result) > 0 && !empty($result)) {
                    $response["success"] = "1";
                    $response["count"] = count($result);
                    $response['data'] = $result;
                } else {
                    $response['data'] = [];
                    $response["success"] = "0";
                    $response["error"] = "Punjabi Text Categories2 Not Available.";
                }
            }
        } else {
            $response['data'] = [];
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    }); 
    
    $app->post('/get_list_god_categories2', function() use ($app) {
        verifyRequiredParams(array('app_code'));
       
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = "";
        $headers = apache_request_headers();
        
        if (isset($headers['Apikey'])) {
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            $app_code = $app->request->post('app_code');
            
            $res = $db->isValidApi($apikey,$app_code);
            
            if (!$res->num_rows > 0) {
                
                $response['data'] = [];
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey and Appcode didn't matched";
                
            } else {
                
                $result = $db->getPunjabiTextCategories2();
            
                if (count($result) > 0 && !empty($result)) {
                    $response["success"] = "1";
                    $response["count"] = count($result);
                    $response['data'] = $result;
                } else {
                    $response['data'] = [];
                    $response["success"] = "0";
                    $response["error"] = "God Text Categories Not Available.";
                }
            }
        } else {
            $response['data'] = [];
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    });
    
// ----------------- END Text Categories2 API ----------------- //

// ----------------- START Text Categories API ----------------- //
    
    $app->post('/get_list_punjabi_categories', function() use ($app) {
        verifyRequiredParams(array('app_code'));
       
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = "";
        $headers = apache_request_headers();
        
        if (isset($headers['Apikey'])) {
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            $app_code = $app->request->post('app_code');
            
            $res = $db->isValidApi($apikey,$app_code);
            
            if (!$res->num_rows > 0) {
                
                $response['data'] = [];
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey and Appcode didn't matched";
                
            } else {
                
                $result = $db->getPunjabiTextCategories();
            
                if (count($result) > 0 && !empty($result)) {
                    $response["success"] = "1";
                    $response["count"] = count($result);
                    $response['data'] = $result;
                } else {
                    $response['data'] = [];
                    $response["success"] = "0";
                    $response["error"] = "Punjabi Text Categories2 Not Available.";
                }
            }
        } else {
            $response['data'] = [];
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    }); 
    
    $app->post('/get_list_god_categories', function() use ($app) {
        verifyRequiredParams(array('app_code'));
       
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = "";
        $headers = apache_request_headers();
        
        if (isset($headers['Apikey'])) {
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            $app_code = $app->request->post('app_code');
            
            $res = $db->isValidApi($apikey,$app_code);
            
            if (!$res->num_rows > 0) {
                
                $response['data'] = [];
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey and Appcode didn't matched";
                
            } else {
                
                $result = $db->getPunjabiTextCategories();
            
                if (count($result) > 0 && !empty($result)) {
                    $response["success"] = "1";
                    $response["count"] = count($result);
                    $response['data'] = $result;
                } else {
                    $response['data'] = [];
                    $response["success"] = "0";
                    $response["error"] = "God Text Categories Not Available.";
                }
            }
        } else {
            $response['data'] = [];
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    }); 
    
// ----------------- END Text Categories API ----------------- //
    
// ----------------- START Videos API ----------------- //
    
    $app->post('/get_list_Punjabi_videos', function() use ($app) {
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
                    
                    $tag_data = $db->getPunjabiTagInfo($tag_id);
                    if(!empty($tag_data)) {
                        $tag_name = $tag_data["tag"];
                    }
                    
                } else {
                    $tag_name = "";
                }
                $total_count = $db->getPunjabiVideoCount($tag_name);
                if ($offset >= $total_count && $total_count != 0) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Invalid value for offset";
                    $response["count"] = 0;
                    $response['data'] = array();
                } else {
                    $result = $db->getPunjabiVideos($tag_id, $tag_name, $limit, $offset, $order_field, $order);
                    
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
    
    $app->post('/get_list_god_videos', function() use ($app) {
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
                        $tag_name = $tag_data["tag"];
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
    
    $app->post('/get_search_punbjabi_videos',function() use ($app) {
      
        verifyRequiredParams(array('keyword', 'token'),'search');
        $response = array();
        $response["error"] = false;
        $response["message"] = "videos";
        $response["count"] = 0;
        $response['data'] = [];
      
        $token = $app->request->post('token');
        $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
           "token:".$token.PHP_EOL.
           "keyword:".$app->request->post('keyword').PHP_EOL.
           "-------------------------".PHP_EOL;
        //Save string to log, use FILE_APPEND to append.
        file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

        if($token == 'yh12sEjLhLgGWnn853QB9/VWTpM=' || $token == 'B6n31cKHfLkpE5eo0gP5ddfEuhA=' || $token == "4po0kYMgWb8Om0H2TcatU9D0aqE=" || $token == "flLD3pmevcgB4ylyIiIKqFKcYSc=" || $token == "D/EJYNsFtRuF4rhMkMg+EpFZmiU=") {
            $db = new DbHandler();
            $keyword = $app->request->post('keyword');
            $videos = $db->searchPunjabiVideos($keyword);

            if (count($videos) > 0) {
                $response["error"] = false;
                $response["message"] = "videos";
                $response["count"] = count($videos);
                $response['data'] = $videos;
            } else {
                $response["error"] = true;
                $response["message"] = "sorry, no videos found";
            }
        } else {
            $response["error"] = true;
            $response["message"] = "sorry, invalid token";
        }
        echoRespnse(200, $response);
    }); 
    
    $app->post('/get_search_god_videos',function() use ($app) {
      
        verifyRequiredParams(array('keyword', 'token'),'search');
        $response = array();
        $response["error"] = false;
        $response["message"] = "videos";
        $response["count"] = 0;
        $response['data'] = [];
      
        $token = $app->request->post('token');
        $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
           "token:".$token.PHP_EOL.
           "keyword:".$app->request->post('keyword').PHP_EOL.
           "-------------------------".PHP_EOL;
        //Save string to log, use FILE_APPEND to append.
        file_put_contents('./log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

        if($token == 'yh12sEjLhLgGWnn853QB9/VWTpM=' || $token == 'B6n31cKHfLkpE5eo0gP5ddfEuhA=' || $token == "4po0kYMgWb8Om0H2TcatU9D0aqE=" || $token == "flLD3pmevcgB4ylyIiIKqFKcYSc=" || $token == "D/EJYNsFtRuF4rhMkMg+EpFZmiU=") {
            $db = new DbHandler();
            $keyword = $app->request->post('keyword');
            $videos = $db->searchPunjabiVideos($keyword);

            if (count($videos) > 0) {
                $response["error"] = false;
                $response["message"] = "videos";
                $response["count"] = count($videos);
                $response['data'] = $videos;
            } else {
                $response["error"] = true;
                $response["message"] = "sorry, no videos found";
            }
        } else {
            $response["error"] = true;
            $response["message"] = "sorry, invalid token";
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
    
    $app->post('/get_list_punjabi_textstatus', function() use ($app) {
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
                $limit = 10;
            }

            if($app->request->post('offset')) {
                $offset = $app->request->post('offset');
            } else {
                $offset = 0;
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
                $total_count = $db->getPunjabiTextCount($cat_id);
                
                if ($offset >= $total_count) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Invalid value for offset";
                    $response["count"] = 0;
                    $response['data'] = [];
                } else {
                    
                    $result = $db->getPunjabiTextStatus($cat_id, $limit, $offset,  $order);
                    
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
    
    $app->post('/get_list_god_textstatus', function() use ($app) {
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
                $limit = 10;
            }

            if($app->request->post('offset')) {
                $offset = $app->request->post('offset');
            } else {
                $offset = 0;
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
    
    $app->post('/get_punjabi_text_search',function() use ($app) {
        $response = array();
        verifyRequiredParams(array('search', 'app_code'));
        
        $search = $app->request->post('search');
        $app_code = $app->request->post('app_code');
        
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = "";
        if (isset($headers['Apikey'])) {
            $apikey = $headers['Apikey'];
            $db = new DbHandler();

            $res = $db->isValidApi($apikey,$app_code);
            if (!$res->num_rows > 0) {
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = [];
            } else {
                // Limit Offset
                $total_count = $db->getPunjabiTextSearchCount($search);
                $result = $db->PunjabisearchText($search);
                if (count($result) > 0 && !empty($result)) {
                    $response["success"] = "1";
                    $response["count"] = (int)$total_count;
                    $response['data'] = $result;
                } else {
                    $response["success"] = "0";
                    $response["error"] = "Data Not Available.";
                    $response["count"] = (int)$total_count;
                    $response['data'] = [];
                }
            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    }); 
    
    $app->post('/get_god_text_search',function() use ($app) {
        $response = array();
        verifyRequiredParams(array('search', 'app_code'));
        
        $search = $app->request->post('search');
        $app_code = $app->request->post('app_code');
        
        $headers = apache_request_headers();
        $response = array();
        $response["succsess"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = "";
        if (isset($headers['Apikey'])) {
            $apikey = $headers['Apikey'];
            $db = new DbHandler();

            $res = $db->isValidApi($apikey,$app_code);
            if (!$res->num_rows > 0) {
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = [];
            } else {
                // Limit Offset
                $total_count = $db->getGodTextSearchCount($search);
                $result = $db->GodSearchText($search);
                
                if (count($result) > 0 && !empty($result)) {
                    $response["success"] = "1";
                    $response["count"] = (int)$total_count;
                    $response['data'] = $result;
                } else {
                    $response["success"] = "0";
                    $response["error"] = "Data Not Available.";
                    $response["count"] = (int)$total_count;
                    $response['data'] = [];
                }
            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    });
    
// ----------------- END Text Status API ----------------- //
    
// ----------------- START Tags API ----------------- //
    
    $app->post('/get_punjabi_tags', function() use ($app) {
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
                
                $result = $db->getPunjabiTags();
                
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
    
    $app->post('/get_list_Punjabi_images', function() use ($app) {
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
                    
                    $tag_data = $db->getPunjabiTagInfo($tag_id);
                    if(!empty($tag_data)) {
                        $tag_name = $tag_data["tag"];
                    }
                    
                } else {
                    $tag_name = "";
                }
                $total_count = $db->getPunjabiImageCount($tag_name);
                if ($offset >= $total_count && $total_count != 0) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Invalid value for offset";
                    $response["count"] = 0;
                    $response['data'] = array();
                } else {
                    $result = $db->getPunjabiImages($tag_id, $tag_name, $limit, $offset, $order_field, $order);
                    
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
    
    $app->post('/get_list_god_images', function() use ($app) {
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
                        $tag_name = $tag_data["tag"];
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
    
    $app->post('/get_punjabi_images_related', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('tag_id','app_code'));
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
            
            $tag_id = $app->request->post('tag_id');
            $app_code = $app->request->post('app_code');
            
            if($app->request->post('limit')) {
                $limit = $app->request->post('limit');
            } else {
                $limit = 30;
            }
            
            $tag_data = $db->getPunjabiTagInfo($tag_id);
            
            if(!empty($tag_data)) {
                $tag_name = $tag_data["tag"];
                $res = $db->isValidApi($apikey, $app_code);
                if (!$res->num_rows > 0) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Apikey is invalid";
                    $response["count"] = 0;
                    $response['data'] = [];
                } else {
                    // Limit Offset
                    $total_count = $db->getPunjabiImageCount($tag_name);
                    $result = $db->getPunjabiRelatedImages($tag_id, $tag_name, $limit);
                    
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
            } else {
                $response["success"] = "0";
                $response["error"] = "Invalid tag_id.";
                $response['data'] = [];
            }

        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
            $response['data'] = [];
        }
        echoRespnse(200, $response);
    });
    
    $app->post('/get_god_images_related', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('tag_id','app_code'));
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
            
            $tag_id = $app->request->post('tag_id');
            $app_code = $app->request->post('app_code');
            
            if($app->request->post('limit')) {
                $limit = $app->request->post('limit');
            } else {
                $limit = 30;
            }
            
            $tag_data = $db->getGodTagInfo($tag_id);
            
            if(!empty($tag_data)) {
                $tag_name = $tag_data["tag"];
                $res = $db->isValidApi($apikey, $app_code);
                if (!$res->num_rows > 0) {
                    $response["success"] = "0";
                    $response["error"] = "Sorry, Apikey is invalid";
                    $response["count"] = 0;
                    $response['data'] = [];
                } else {
                    // Limit Offset
                    $total_count = $db->getGodImageCount($tag_name);
                    $result = $db->getGodRelatedImages($tag_id, $tag_name, $limit);
                    
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
            } else {
                $response["success"] = "0";
                $response["error"] = "Invalid tag_id.";
                $response['data'] = [];
            }

        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
            $response['data'] = [];
        }
        echoRespnse(200, $response);
    });
    
    $app->post('/get_punjabi_image_search',function() use ($app) {
        $response = array();
        
        verifyRequiredParams(array('search', 'app_code'));
        $search = $app->request->post('search');
        $app_code = $app->request->post('app_code');
        
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = array();
        if (isset($headers['Apikey'])) {
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            $res = $db->isValidApi($apikey, $app_code);
            if (!$res->num_rows > 0) {
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = array();
            } else {
                $total_count = $db->getPunjabiImageSearchCount($search);
                $result = $db->searchPunjabiImage($search);
                if (count($result) > 0 && !empty($result)) {
                    $response["success"] = "1";
                    $response["count"] = (int)$total_count;
                    $response['data'] = $result;
                } else {
                    $response["success"] = "0";
                    $response["error"] = "Data Not Available.";
                    $response["count"] = (int)$total_count;
                    $response['data'] = array();
                }
            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    }); 
    
    $app->post('/get_god_image_search',function() use ($app) {
        $response = array();
        
        verifyRequiredParams(array('search', 'app_code'));
        $search = $app->request->post('search');
        $app_code = $app->request->post('app_code');
        
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        $response["count"] = 0;
        $response['data'] = array();
        if (isset($headers['Apikey'])) {
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            $res = $db->isValidApi($apikey, $app_code);
            if (!$res->num_rows > 0) {
                $response["success"] = "0";
                $response["error"] = "Sorry, Apikey is invalid";
                $response["count"] = 0;
                $response['data'] = array();
            } else {
                
                $total_count = $db->getGodImageSearchCount($search);
                $result = $db->searchGodImage($search);
                
                if (count($result) > 0 && !empty($result)) {
                    $response["success"] = "1";
                    $response["count"] = (int)$total_count;
                    $response['data'] = $result;
                } else {
                    $response["success"] = "0";
                    $response["error"] = "Data Not Available.";
                    $response["count"] = (int)$total_count;
                    $response['data'] = array();
                }
            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    }); 
    
    $app->post('/get_punjabi_images_update_count',function() use ($app) {
        $response = array();
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        if (isset($headers['Apikey'])) {
            
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            $app_code = $app->request->post('app_code');
            $res = $db->isValidApi($apikey,$app_code);
            
            if (!$res->num_rows > 0) {
                $response["success"] = "0";
                $response["error"] = "invalid Apikey";
            } else {
                if($app->request->post('views')) {
                    
                    $view = $app->request->post('views');
                    $views = $db->updatePunjabiImageViews($view);
                    
                    if($views == 0) {
                        $response["success"] = "0";
                        $response["error"] = "no video found";
                        $response["views"] = null;
                    } else {
                        $response["success"] = "1";
                        $response["error"] = "";
                        $response["views"] = $views;
                    }
                } else if($app->request->post('downloads')) {
                    
                    $download = $app->request->post('downloads');
                    $downloads = $db->updatePunjabiImageDownloads($download);
                    
                    if($downloads == 0) {
                        $response["success"] = "0";
                        $response["error"] = "no record found";
                        $response["downloads"] = null;
                    } else {
                        $response["success"] = "1";
                        $response["error"] = "";
                        $response["downloads"] = $downloads;
                    }                
                } else {
                    $response["success"] = "0";
                    $response["error"] = "parameter views/downloads missing";
                }
            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
        }
        echoRespnse(200, $response);
    }); 
    
    $app->post('/get_god_images_update_count',function() use ($app) {
        $response = array();
        $headers = apache_request_headers();
        $response = array();
        $response["success"] = "0";
        $response["error"] = "";
        if (isset($headers['Apikey'])) {
            
            $apikey = $headers['Apikey'];
            $db = new DbHandler();
            $app_code = $app->request->post('app_code');
            $res = $db->isValidApi($apikey,$app_code);
            
            if (!$res->num_rows > 0) {
                $response["success"] = "0";
                $response["error"] = "invalid Apikey";
            } else {
                if($app->request->post('views')) {
                    
                    $view = $app->request->post('views');
                    $views = $db->updateGodImageViews($view);
                    
                    if($views == 0) {
                        $response["success"] = "0";
                        $response["error"] = "no video found";
                        $response["views"] = null;
                    } else {
                        $response["success"] = "1";
                        $response["error"] = "";
                        $response["views"] = $views;
                    }
                } else if($app->request->post('downloads')) {
                    
                    $download = $app->request->post('downloads');
                    $downloads = $db->updateGodImageDownloads($download);
                    
                    if($downloads == 0) {
                        $response["success"] = "0";
                        $response["error"] = "no record found";
                        $response["downloads"] = null;
                    } else {
                        $response["success"] = "1";
                        $response["error"] = "";
                        $response["downloads"] = $downloads;
                    }                
                } else {
                    $response["success"] = "0";
                    $response["error"] = "parameter views/downloads missing";
                }
            }
        } else {
            $response["success"] = "0";
            $response["error"] = "Apikey is missing";
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