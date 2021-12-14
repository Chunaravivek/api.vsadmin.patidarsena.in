<?php


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
                $tag_name = $tag_data["name"];
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
    
// ----------------- END Text Categories API ----------------- //
    
    
    
    
    public function getPunjabiTextSearchCount($search) {
        $totalcount = 0;
        
        $cond = " WHERE `text` like '%$search%'";
        $count_qry = "SELECT COUNT(*) as totalcount FROM `punjabi_text_status` $cond";
        
        $video_res = $this->conn->query($count_qry);
        if ($video_res->num_rows > 0) {
            $res = $video_res->fetch_assoc();
            $totalcount = $res['totalcount'];
        }
        
        if ($totalcount == 0) {
            $count_qry = "SELECT COUNT(*) as totalcount FROM `punjabi_text_status`";
        
            $video_res = $this->conn->query($count_qry);
            if ($video_res->num_rows > 0) {
                $res = $video_res->fetch_assoc();
                $totalcount = $res['totalcount'];
            }
        }
        return $totalcount;
    }
    
    public function getGodTextSearchCount($search) {
        $totalcount = 0;
        
        $cond = " WHERE `text` like '%$search%'";
        $count_qry = "SELECT COUNT(*) as totalcount FROM `god_text_status` $cond";
        
        $video_res = $this->conn->query($count_qry);
        if ($video_res->num_rows > 0) {
            $res = $video_res->fetch_assoc();
            $totalcount = $res['totalcount'];
        }
        return $totalcount;
    }
    
    public function PunjabisearchText($search){
        $result = array();
        $vid_qry = $this->conn->query("SELECT * FROM `punjabi_text_status` where  `text` like '%$search%' ORDER by `id` DESC");
        
        if($vid_qry->num_rows > 0) {
            $i = 0;
            while ($res = $vid_qry->fetch_assoc()) {
                //tag array
                $tag_ids = $res["tag_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name`  FROM `punjabi_tags` where `id` IN ($tag_ids)");
                if ($tag_res->num_rows > 0) {
                    while($tagdata = $tag_res->fetch_assoc()) {
                        $res["tag_arr"][] = $tagdata;
                    }
                }
                unset($res['tag_id']);
                $result[] = $res;
            }
        } else {
            $vid_qry = $this->conn->query("SELECT * FROM `punjabi_text_status` ORDER BY `id` DESC");
            
            $i = 0;
            while ($res = $vid_qry->fetch_assoc()) {
                //tag array
                $tag_ids = $res["tag_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name`  FROM `punjabi_tags` where `id` IN ($tag_ids)");
                if ($tag_res->num_rows > 0) {
                    while($tagdata = $tag_res->fetch_assoc()) {
                        $res["tag_arr"][] = $tagdata;
                    }
                }
                unset($res['tag_id']);
                $result[] = $res;
            }
        }
        return $result;
    }
    
    public function GodsearchText($search){
        $res = array();
        $vid_qry = $this->conn->query("SELECT * FROM `god_text_status` where  `text` like '%$search%' ORDER by `id` DESC");
        
        if($vid_qry->num_rows > 0) {
            $i = 0;
            while ($row = $vid_qry->fetch_assoc()) {
                $res[] = $row;
            }
        }
        return $res;
    }
    
    public function searchPunjabiVideos($string){
        $res = array();
        
        $vid_qry = "SELECT * FROM `punjabi_videos` where  `title` like '%$string%' ORDER BY `id` DESC";
        
        $res_qry = $this->conn->query($vid_qry);
        
        if($res_qry->num_rows > 0) {
            $i = 0;
            while ($row = $res_qry->fetch_assoc()) {
                $res[$i]['id']             = $row['id'];
                $res[$i]['title']          = $row['title'];
                $res[$i]['url']            = $row['url'];
                $res[$i]['tags_id']        = $row['tags_id'];
                $res[$i]['tags']           = $row['tags'];
                $res[$i]['views']          = $row['views'];
                $res[$i]['downloads']      = $row['downloads'];
                $res[$i]['created_date']   = $row['created_date'];
                $res[$i]['modified_date']  = $row['modified_date'];
                $res[$i]['status']         = $row['status'];
                $i++;
            }
        }
        return $res;
    }
    
    public function searchGodVideos($string){
        $res = array();
        
        $vid_qry = "SELECT * FROM `god_videos` where  `title` like '%$string%' ORDER BY `id` DESC";
        
        $res_qry = $this->conn->query($vid_qry);
        
        if($res_qry->num_rows > 0) {
            $i = 0;
            while ($row = $res_qry->fetch_assoc()) {
                $res[$i]['id']             = $row['id'];
                $res[$i]['title']          = $row['title'];
                $res[$i]['url']            = $row['url'];
                $res[$i]['tags_id']        = $row['tags_id'];
                $res[$i]['tags']           = $row['tags'];
                $res[$i]['views']          = $row['views'];
                $res[$i]['downloads']      = $row['downloads'];
                $res[$i]['created_date']   = $row['created_date'];
                $res[$i]['modified_date']  = $row['modified_date'];
                $res[$i]['status']         = $row['status'];
                $i++;
            }
        }
        return $res;
    }
    
    // ----------------- START Text Categories2 API FUNCTIONS ----------------- // 
    
    public function getPunjabiTextCategories2() {
        $this->conn->query("SET NAMES utf8");
        $result = array();
        $punjabi_cat_res = $this->conn->query("SELECT * FROM `punjabi_text_categories2` ORDER BY `order`");
        if ($punjabi_cat_res->num_rows > 0) {
            while ($res = $punjabi_cat_res->fetch_assoc()) {
                $result[] = $res;
            }
        }
        return $result;
    }
    
    public function getGodTextCategories2() {
        $this->conn->query("SET NAMES utf8");
        $result = array();
        $punjabi_cat_res = $this->conn->query("SELECT * FROM `god_text_categories2` ORDER BY `order`");
        if ($punjabi_cat_res->num_rows > 0) {
            while ($res = $punjabi_cat_res->fetch_assoc()) {
                $result[] = $res;
            }
        }
        return $result;
    }
// ----------------- END Text Categories2 API FUNCTIONS ----------------- // 
 
// ----------------- START Text Categories API FUNCTIONS ----------------- // 
    public function getPunjabiTextCategories() {
        $this->conn->query("SET NAMES utf8");
        $result = array();
        $punjabi_cat_res = $this->conn->query("SELECT * FROM `punjabi_text_categories` ORDER BY `order`");
        if ($punjabi_cat_res->num_rows > 0) {
            while ($res = $punjabi_cat_res->fetch_assoc()) {
                $result[] = $res;
            }
        }
        return $result;
    }
    
    public function getGodTextCategories() {
        $this->conn->query("SET NAMES utf8");
        $result = array();
        $punjabi_cat_res = $this->conn->query("SELECT * FROM `god_text_categories` ORDER BY `order`");
        if ($punjabi_cat_res->num_rows > 0) {
            while ($res = $punjabi_cat_res->fetch_assoc()) {
                $result[] = $res;
            }
        }
        return $result;
    }
// ----------------- END Text Categories API FUNCTIONS ----------------- //  
    
    public function getPunjabiRelatedImages($tag_id=79, $tag_name="dp and status", $limit = 30) {
        $result = array();
        $cond = " WHERE `status` = 1 AND `tags` Like '%$tag_name%'";
        $sort = "ORDER BY RAND() LIMIT $limit";
        $vid_qry = "SELECT `id`, `title`, `image`, `thumb`, `tags_id`, `views`, `downloads`, `created_date`, `modified_date`, `status` FROM `punjabi_images` $cond $sort";
      
        $video_res = $this->conn->query($vid_qry);
        if ($video_res->num_rows > 0) {
            while ($res = $video_res->fetch_assoc()) {
                //tag array
                $tag_ids = $res["tags_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name`,`tag_type`,`tag_type_text` FROM `punjabi_tags` where `id` IN ($tag_ids)");
                if ($tag_res->num_rows > 0) {
                    while($tagdata = $tag_res->fetch_assoc()) {
                        $res["tag_arr"][] = $tagdata;
                    }
                }
                unset($res['tags_id']);
                $result[] = $res;
            }
        }
        return $result;
    }
    
    public function getGodRelatedImages($tag_id=79, $tag_name="dp and status", $limit = 30) {
        $result = array();
        $cond = " WHERE `status` = 1 AND `tags` Like '%$tag_name%'";
        $sort = "ORDER BY RAND() LIMIT $limit";
        $vid_qry = "SELECT `id`, `title`, `image`, `thumb`, `tags_id`, `views`, `downloads`, `created_date`, `modified_date`, `status` FROM `god_images` $cond $sort";
        $video_res = $this->conn->query($vid_qry);
        if ($video_res->num_rows > 0) {
            while ($res = $video_res->fetch_assoc()) {
                //tag array
                $tag_ids = $res["tags_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name`,`tag_type`,`tag_type_text` FROM `god_tags` where `id` IN ($tag_ids)");
                if ($tag_res->num_rows > 0) {
                    while($tagdata = $tag_res->fetch_assoc()) {
                        $res["tag_arr"][] = $tagdata;
                    }
                }
                unset($res['tags_id']);
                $result[] = $res;
            }
        }
        return $result;
    }
    
    public function getPunjabiImageSearchCount($search) {
        $totalcount = 0;
        $cond = " WHERE `title` like '%$search%' and `status` = 1";
        $count_qry = "SELECT COUNT(*) as totalcount FROM `punjabi_images` $cond";
        $video_res = $this->conn->query($count_qry);
        if ($video_res->num_rows > 0) {
            $res = $video_res->fetch_assoc();
            $totalcount = $res['totalcount'];
        }
        return $totalcount;
    }
    
    public function searchPunjabiImage($search){
        $result = array();
        $vid_qry = $this->conn->query("SELECT `id`, `title`, `image`, `thumb`, `tags_id`, `views`, `downloads`, `created_date`, `modified_date`, `status`
                    FROM `punjabi_images` where  `title` like '%$search%' and `status` = 1 ORDER by `id` DESC");
        if($vid_qry->num_rows > 0) {
            $i = 0;
            while ($res = $vid_qry->fetch_assoc()) {
                //tag array
                $tag_ids = $res["tags_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name`,`tag_type`,`tag_type_text` FROM `punjabi_tags` where `id` IN ($tag_ids)");
                
                if ($tag_res->num_rows > 0) {
                    while($tagdata = $tag_res->fetch_assoc()) {
                        $res["tag_arr"][] = $tagdata;
                    }
                }
                unset($res["tags_id"]);
                $result[] = $res;
            }
        }
        return $result;
    }
    
    public function getGodImageSearchCount($search) {
        $totalcount = 0;
        $cond = " WHERE `title` like '%$search%' and `status` = 1";
        $count_qry = "SELECT COUNT(*) as totalcount FROM `god_images` $cond";
        $video_res = $this->conn->query($count_qry);
        if ($video_res->num_rows > 0) {
            $res = $video_res->fetch_assoc();
            $totalcount = $res['totalcount'];
        }
        return $totalcount;
    }
    
    public function searchGodImage($search){
        $result = array();
        $vid_qry = $this->conn->query("SELECT `id`, `title`, `image`, `thumb`, `tags_id`, `views`, `downloads`, `created_date`, `modified_date`, `status`
                    FROM `god_images` where  `title` like '%$search%' and `status` = 1 ORDER by `id` DESC");
        if($vid_qry->num_rows > 0) {
            $i = 0;
            while ($res = $vid_qry->fetch_assoc()) {
                //tag array
                $tag_ids = $res["tags_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name`,`tag_type`,`tag_type_text` FROM `god_tags` where `id` IN ($tag_ids)");
                
                if ($tag_res->num_rows > 0) {
                    while($tagdata = $tag_res->fetch_assoc()) {
                        $res["tag_arr"][] = $tagdata;
                    }
                }
                unset($res["tags_id"]);
                $result[] = $res;
            }
        }
        return $result;
    }
    
    public function updatePunjabiImageViews($id){
        $views = 0;
        $exist = $this->conn->query("SELECT `views` FROM `punjabi_images` where  id = $id");
        if($exist->num_rows > 0) {
            $this->conn->query("UPDATE punjabi_images SET views= views+1 WHERE id = '$id'");
            while ($row = $exist->fetch_assoc()) {
                $views = $row['views']+1;
            }
        }
        return $views;
    }
    
    public function updatePunjabiImageDownloads($id){
        $download = 0;
        $exist = $this->conn->query("SELECT `downloads` FROM `punjabi_images` where  id = '$id'");
        if($exist->num_rows > 0) {
            $this->conn->query("UPDATE punjabi_images SET downloads= downloads+1 WHERE id = '$id'");
            while ($row = $exist->fetch_assoc()) {
                $download = $row['downloads']+1;
            }
        }
        return $download;
    }
    
    public function updateGodImageViews($id){
        $views = 0;
        $exist = $this->conn->query("SELECT `views` FROM `god_images` where  id = $id");
        if($exist->num_rows > 0) {
            $this->conn->query("UPDATE punjabi_images SET views= views+1 WHERE id = '$id'");
            while ($row = $exist->fetch_assoc()) {
                $views = $row['views']+1;
            }
        }
        return $views;
    }
    
    public function updateGodImageDownloads($id){
        $download = 0;
        $exist = $this->conn->query("SELECT `downloads` FROM `god_images` where  id = '$id'");
        if($exist->num_rows > 0) {
            $this->conn->query("UPDATE punjabi_images SET downloads= downloads+1 WHERE id = '$id'");
            while ($row = $exist->fetch_assoc()) {
                $download = $row['downloads']+1;
            }
        }
        return $download;
    }