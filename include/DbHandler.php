<?php


class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }
    
    // ----------------- REGISTER API FUNCTIONS ----------------- //
    public function createUser($memcache, $app_code, $app_version, $tag, $device_type, $android_id, $device_token, $timezone, $device_model, $device_name, $device_memory, $device_os) {
        $response = array();
             
        // First check if user already existed in db
        $exist_user = $this->isUserExists($memcache, $app_code, $app_version, $android_id, $device_token, $device_type);
        
        if (isset($exist_user['id']) && $exist_user['id'] > 0) {
            
            $res["message"] = $exist_user['updated'];
            $res["api_key"] = $exist_user["api_key"];
            
            return $res;
        } else {    
            
            $api_key = $this->generateApiKey();
          
            $time = time();
            $dateime = date('Y-m-d H:i:s');
           
            $user_qry = "INSERT INTO `users`(`name`, `email`, `subscribe`, `install_status`, `notifs_status`, `tag`, `token_updated`, `last_sent`, `time`, `last_lat`, `last_long`, `pin_code`, `timezone`, `device_model`, `device_name`, `device_memory`, `device_os`, `device_id`, `app_code`, `app_version`, `api_key`, `device_token`, `device_type`, `android_id`, `fb_id`, `mobile`, `gender`, `last_access`, `device_api`, `created_date`, `modified_date`, `status`)
                        VALUES('','',0,1,1,'$tag',0,'$dateime','$dateime','','','','$timezone','$device_model','$device_name','$device_memory','$device_os','','$app_code', '$app_version', '$api_key', '$device_token','$device_type', '$android_id','','','','$dateime','','$time', '$time', '1')";
            
//            $user_qry = "INSERT INTO users(app_code, app_version,tag, api_key, device_type, android_id, device_token, timezone, device_model, device_name, device_memory, device_os, created_date, modified_date, status) values('$app_code', '$app_version','$tag', '$api_key', '$device_type', '$android_id', '$device_token', '$timezone', '$device_model', '$device_name', '$device_memory', '$device_os', '$time', '$time', '1')";
           
            $result = $this->conn->query($user_qry);
          
            if ($this->conn->affected_rows == 1) {
               
                $last_id = $this->conn->insert_id;
                $sel_qry = "SELECT * from users WHERE id = $last_id";
                $setKey = md5($sel_qry);
               
                $getCacheDetail = false;
                if ($getCacheDetail) {
                   
                    $res = $getCacheDetail;
                    return $res;
                } else {
                    
                    $res_user = $this->conn->query($sel_qry);
                    $user = $res_user->fetch_assoc();
                    if ($res_user->num_rows > 0) {
                        $res["message"] = 1;
                        $res["api_key"] = $user["api_key"];
                        return $res;
                    }
                }
            } else {
                
                return USER_CREATE_FAILED;
            }
        }
    }

    public function isUserExists($memcache, $app_code, $app_version, $android_id, $device_token, $device_type) {        
        $time = time();
      
        $exst_user = "SELECT `api_key`,`id`,`device_token`,`app_version` from users WHERE android_id = '$android_id' and app_code = '$app_code' and device_type = '$device_type'";
        $setKey = md5($exst_user);
        $getCacheDetail = false;
        $user = array();
        
        $user_st = 2;
        
        if ($getCacheDetail) {
           
            $user = $getCacheDetail;
            $user['updated'] = 1;
            $user_st = 1;
            
        } else {
           
            $result = $this->conn->query($exst_user);
            
            if ($result->num_rows > 0) {
                $user_st = 1;
                while ($row = $result->fetch_assoc()) {
                  
                    $user = $row;
                }
            }
            $user['updated'] = 1;
        }
      
        if ($user_st == 1) {       
            $id = $user['id'];
            if ($user['device_token'] <> $device_token || $user['app_version'] <> $app_version) {
               $user_upd_qry = "UPDATE users SET app_version='$app_version', device_token='$device_token',install_status='1',token_updated='$time' WHERE id = '$id'";
              
                $this->conn->query($user_upd_qry);
            }
          
            $user['updated'] = 2;
        }
       
        return $user;
    }

    public function verifyClientKey($client_key,$app_code) {
        $query = "SELECT * from `keys` WHERE `app_code` = '$app_code' AND (
                    `client_key1` = '$client_key'
                    OR `client_key2` = '$client_key'
                    OR `client_key3` = '$client_key'
                    OR `client_key4` = '$client_key'
                    OR `client_key5` = '$client_key')";
        $result = $this->conn->query($query);
        $key = array();
        if ($result->num_rows > 0) {
            $key = $result->fetch_assoc();
        }
        return $key;
    }
    
    public function AddClientKey($client_key,$app_code) {
        $response = array();
        
        $insert = $this->conn->query("INSERT INTO keys (client_key5) values($client_key)");
        
        if ($insert) {
            $response['error'] = false;
            $response['insert_id'] = $this->conn->insert_id;
            
        } else {
            $response['error'] = true;
            $response['message'] = 'Failed to send message ';
        }
        return $response;
    }
    
    public function createUser2($memcache, $app_code, $app_version, $tag, $device_type, $android_id, $device_token, $timezone, $device_model, $device_name, $device_memory, $device_os) {
        $response = array();

        // First check if user already existed in db
        $exist_user = $this->isUserExists2($memcache, $app_code, $app_version, $android_id, $device_token, $device_type);

        if (isset($exist_user['id']) && $exist_user['id'] > 0) {
            $res["message"] = $exist_user['updated'];
            $res["api_key"] = $exist_user["api_key"];
            return $res;
        } else {
            $api_key = $this->generateApiKey();
            $time = time();
//            echo "INSERT INTO users(app_code, app_version,tag, api_key, device_type, android_id, device_token, timezone, device_model, device_name, device_memory, device_os, created_date, modified_date, status) values('$app_code', '$app_version','$tag', '$api_key', '$device_type', '$android_id', '$device_token', '$timezone', '$device_model', '$device_name', '$device_memory', '$device_os', '$time', '$time', '1')";
//            exit;
            $result = $this->conn->query("INSERT INTO users(app_code, app_version,tag, api_key, device_type, android_id, device_token, timezone, device_model, device_name, device_memory, device_os, created_date, modified_date, status) values('$app_code', '$app_version','$tag', '$api_key', '$device_type', '$android_id', '$device_token', '$timezone', '$device_model', '$device_name', '$device_memory', '$device_os', '$time', '$time', '1')");

            if ($result) {
                $last_id = $this->conn->insert_id;
                $sel_qry = "SELECT * from users WHERE id = $last_id";
                $setKey = md5($sel_qry);

                // $getCacheDetail = $memcache->get($setKey);
                $getCacheDetail = false;
                if ($getCacheDetail) {
                    $res = $getCacheDetail;
                    return $res;
                } else {
                    $res_user = $this->conn->query($sel_qry);
                    $user = $res_user->fetch_assoc();
                    if ($res_user->num_rows > 0) {
                        $res["message"] = 1;
                        $res["api_key"] = $user["api_key"];
                        // $memcache->set($setKey, $res, 1000);
                        return $res;
                    }
                }
            } else {
                return USER_CREATE_FAILED;
            }
        }
    }

    public function isUserExists2($memcache, $app_code, $app_version, $android_id, $device_token, $device_type) {
        $time = time();

        $exst_user = "SELECT `api_key`,`id`,`device_token`,`app_version` from users WHERE android_id = '$android_id' and app_code = '$app_code' and device_type = '$device_type'";
        $setKey = md5($exst_user);
        // $getCacheDetail = $memcache->get($setKey);
        $getCacheDetail = false;
        $user = array();
        $user_st = 2;
        if ($getCacheDetail) {
            $user = $getCacheDetail;
            $user['updated'] = 1;
            $user_st = 1;
        } else {
            $result = $this->conn->query($exst_user);
            if ($result->num_rows > 0) {
                $user_st = 1;
                while ($row = $result->fetch_assoc()) {
                    $user = $row;
                }
                // $memcache->set($setKey, $user, 1000);
            }
            $user['updated'] = 1;
        }
        if ($user_st == 1) {
            $id = $user['id'];
            if ($user['device_token'] <> $device_token || $user['app_version'] <> $app_version) {
//                echo "UPDATE users SET app_version='$app_version', device_token='$device_token',install_status='1',token_updated='$time' WHERE id = '$id'";
//                exit;
                $this->conn->query("UPDATE users SET app_version='$app_version', device_token='$device_token',install_status='1',token_updated='$time' WHERE id = '$id'");
            }
            $user['updated'] = 2;
        }
        return $user;
    }

    public function isValidClientKey($client_key, $app_code) {
        $auth_qry = "SELECT `api_key` FROM `keys` WHERE  `app_code` = '$app_code' AND (
                    `client_key1` = '$client_key'
                    OR `client_key2` = '$client_key'
                    OR `client_key3` = '$client_key'
                    OR `client_key4` = '$client_key'
                    OR `client_key5` = '$client_key'
                    OR `client_key6` = '$client_key'
                    )";
       
        $result = $this->conn->query($auth_qry);
        
        if($result->num_rows == 0) {
            $string_length = strlen($client_key);
            $lastchar = substr($client_key, -1);  
           
            if ($string_length == 28 && $lastchar == '=') {
               
                $check_qry = "SELECT * FROM `keys` WHERE  `app_code` = '$app_code'";
                $chk_result = $this->conn->query($check_qry);
                
                if($chk_result->num_rows > 0){
                    
                    $update = $this->conn->query("UPDATE `keys` SET `client_key5`= '".$client_key."' where `app_code` = '".$app_code."'");
                    
                } else {
                    
//                    $account_id = $this->getAccountid($app_code);
                    $api_key =  md5(uniqid(rand(), true));
                    
                    $query= "INSERT INTO `keys` (`client_key5`, `app_code`, `api_key`,`created_date`, `modified_date`) values ('$client_key', '$app_code', '$api_key',".time().",".time().")";
                   
                    $insert = $this->conn->query($query);
                
                }
                $result = $this->conn->query($auth_qry);
            } 
        }
       
        return $result;
    }

    public function isValidApi($apikey,$app_code) {
        $auth_qry = "SELECT * FROM `keys` WHERE  `app_code` = '$app_code' AND `api_key` = '$apikey'";
        $result = $this->conn->query($auth_qry);
        return $result;
    }

    public function isValidApiKey($api_key, $memcache) {
        $query = "SELECT `api_key` from users WHERE api_key = '$api_key'";
        $setKey = md5($query);
        $getCacheDetail = false;
        $result = array();
        $result['from'] = '';
        if ($getCacheDetail) {
            $result = $getCacheDetail;
            $result['from'] = 'cache';
        } else {
            $user_res = $this->conn->query($query);
            if ($user_res->num_rows > 0) {
                while ($user = $user_res->fetch_assoc()) {
                    $assoc = $user;
                    $result = $user;
                }
                $this->conn->close();
                $result['from'] = 'db';
            }
        }
        return $result;
    }

    private function generateApiKey() {      
        return md5(uniqid(rand(), true));
    }
    
    public function checkApp($app_code) {
        $result = $this->conn->query("SELECT * FROM `applications` WHERE `app_code` = '$app_code'");
        return $result;
    }
    
    public function updateUsers($android_id) {
        $last_access=date('Y-m-d');

        $update_query="UPDATE users SET `last_access`='$last_access' WHERE `android_id` = '$android_id'";
        $result = $this->conn->query($update_query);

       return $result;
    }   
    
    
    public function chkExistUrl($app_code) {
        $urlcount = 0;
        $result = $this->conn->query("SELECT count(*) as urlcount FROM `livetvstatus` WHERE `app_code` = '$app_code' LIMIT 1");
        if ($result->num_rows > 0) {
            $res_arr = $result->fetch_assoc();
            if (!empty($res_arr)) {
                $urlcount = $res_arr['urlcount'];
            }
        }
        return $urlcount;
    }
    
// ----------------- INSTALL API FUNCTIONS ----------------- //    

    public function getAllAccounts() {
        $result = array();
        $this->conn->query("SET NAMES utf8");
        $ac_sql = $this->conn->query("SELECT * FROM `accounts`");
        $accounts = array();
        if ($ac_sql->num_rows > 0) {
            while ($account = $ac_sql->fetch_assoc()) {
                array_push($accounts, $account);
            }
        }
        return $accounts;
        $this->conn->close();
    }
    
    public function getAccountId($app_code) {
        $result = $this->conn->query("SELECT `account_id` FROM `applications` WHERE `app_code` = '$app_code'");
        $account = $result->fetch_assoc();
       
        return $account['account_id'];
    }

    public function getApps($account_id) {
        $result = array();
        $this->conn->query("SET NAMES utf8");
        $result = $this->conn->query("SELECT * FROM `applications` WHERE `account_id` = '$account_id'");
        if ($result->num_rows > 0) {
            $apps = array();
            while ($app = $result->fetch_assoc()) {
                array_push($apps, $app);
            }
            return $apps;
        } else {
            return array();
        }
        $this->conn->close();
    }

    public function getInstalls($app_id, $start_date, $end_date) {
        $result = array();
        $this->conn->query("SET NAMES utf8");
        $result = $this->conn->query("SELECT * FROM `applications` WHERE `id` = '$app_id'");
        $response = array();
        if ($result->num_rows > 0) {
            $response['message'] = "Installs";
            $app = $result->fetch_assoc();
            $app_code = $app['app_code'];
            $users_res = $this->conn->query("SELECT * FROM `users` WHERE `app_code`='$app_code' AND `created_date` BETWEEN '$start_date' AND '$end_date'");
            $response['Users'] = $users_res;
        } else {
            $response['message'] = "APP NOT FOUND";
        }
        return $response;
        $this->conn->close();
    }

// ----------------- END INSTALL API FUNCTIONS ----------------- // 
    
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

// ----------------- START Videos API FUNCTIONS ----------------- // 
    
    public function getPunjabiVideoCount($tag = "") {
        $totalcount = 0;
        $cond = " WHERE `status` = 1";
        if($tag != "") {
            $cond .= " AND `tags` Like '%$tag%'";
        }          
        $count_qry = "SELECT COUNT(*) as totalcount FROM `punjabi_videos` $cond";
      
        $video_res = $this->conn->query($count_qry);
        
        if ($video_res->num_rows > 0) {
            $res = $video_res->fetch_assoc();
            $totalcount = $res['totalcount'];
        }
        
        if ($totalcount == 0) {
            $count_qry = "SELECT COUNT(*) as totalcount FROM `punjabi_videos`";
      
            $video_res = $this->conn->query($count_qry);

            if ($video_res->num_rows > 0) {
                $res = $video_res->fetch_assoc();
                $totalcount = $res['totalcount'];
            }
        }
        return $totalcount;
    }
    
    public function getGodVideoCount($tag = "") {
        $totalcount = 0;
        $cond = " WHERE `status` = 1";
        if($tag != "") {
            $cond .= " AND `tags` Like '%$tag%'";
        }            
        $count_qry = "SELECT COUNT(*) as totalcount FROM `god_videos` $cond";
        $video_res = $this->conn->query($count_qry);
        if ($video_res->num_rows > 0) {
            $res = $video_res->fetch_assoc();
            $totalcount = $res['totalcount'];
        }
        return $totalcount;
    }
    
    public function getPunjabiVideos($tag_id = 0, $tag_name = "", $limit = 10, $offset = 0, $order_field = 'id', $order = 'desc') {
        $result = array();
        $cond = " WHERE `status` = 1 ";
        
        if($tag_id != 0 && $tag_name != "") {
            $cond .= " AND `tags` Like '%$tag_name%'";
        }  
        
        $sort = "ORDER by `$order_field` desc";
        
        if($order != 'desc') {
            $sort = "ORDER by `$order_field` $order";
        }
        
        $limit_condition = "LIMIT $offset , $limit";
        
        $img_qry = "SELECT `id`, `title`, `url`, `tags_id`, `views`, `downloads`, `created_date`, `modified_date`, `status` FROM `punjabi_videos` $cond $sort $limit_condition";
      
        $img_res = $this->conn->query($img_qry);
        if ($img_res->num_rows > 0) {
            while ($res = $img_res->fetch_assoc()) {
                //tag array
                $tag_ids = $res["tags_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name` FROM `punjabi_tags` where `id` IN ($tag_ids)");
                if ($tag_res->num_rows > 0) {
                    while($tagdata = $tag_res->fetch_assoc()) {
                        $res["tag_arr"][] = $tagdata;
                    }
                }
                unset($res['tags_id']);
                $result[] = $res;
            }
        } else {
            $img_qry = "SELECT `id`, `title`, `url`, `tags_id`, `views`, `downloads`, `created_date`, `modified_date`, `status` FROM `punjabi_videos` $sort $limit_condition";
      
            $img_res = $this->conn->query($img_qry);
           
            while ($res = $img_res->fetch_assoc()) {
               
                //tag array
                $tag_ids = $res["tags_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name` FROM `punjabi_tags` where `id` IN ($tag_ids)");
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
    
    public function getGodVideos($tag_id = 0, $tag_name = "", $limit = 10, $offset = 0, $order_field = 'id', $order = 'desc') {
        $result = array();
        $cond = " WHERE `status` = 1 ";
        if($tag_id != 0 && $tag_name != "") {
            $cond .= " AND `god_tags` Like '%$tag_name%'";
        }            
        $sort = "ORDER by `$order_field` desc";
        if($order != 'desc') {
            $sort = "ORDER by `$order_field` $order";
        }
        $limit_condition = "LIMIT $offset , $limit";
        
        $img_qry = "SELECT `id`, `title`, `url`, `tags_id`, `views`, `downloads`, `created_date`, `modified_date`, `status` FROM `god_videos` $cond $sort $limit_condition";
   
        $img_res = $this->conn->query($img_qry);
        if ($img_res->num_rows > 0) {
            while ($res = $img_res->fetch_assoc()) {
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
    
// ----------------- END Videos API FUNCTIONS ----------------- // 
    
// ----------------- START Get Ads id API FUNCTIONS ----------------- // 
    
    public function getAdIds(){
        $res = array();
        $ad_qry = $this->conn->query("SELECT * FROM `applications` where `id` = 1");
        $escape_keys = array("id","modified_date","firebase_id");
        
        if($ad_qry->num_rows > 0) {
            while ($ad = $ad_qry->fetch_assoc()) {
                foreach ($ad as $key => $value) {
                    if(!in_array($key, $escape_keys)) {
                        $res[$key] = $value;
                    }
                }
            }
        }
        
        return $res;
    } 
    
    public function getAdIds2($app_code){
        
        $res = array();
        $ad_qry = $this->conn->query("SELECT * FROM `applications` where `app_code` ='$app_code'");
        
        $escape_keys = array("id","modified_date","fcm_key");
        if($ad_qry->num_rows > 0) {
            while ($ad = $ad_qry->fetch_assoc()) {
                foreach ($ad as $key => $value) {
                    if(!in_array($key, $escape_keys)) {
                        $res[$key] = $value;
                    }
                }
            }
        }
        return $res;
    }
    
// ----------------- END Get Ads id API FUNCTIONS ----------------- // 

// ----------------- START TEXT STATUS API FUNCTIONS ----------------- // 
    
    public function getPunjabiTextCount($tag_id = 0) {
        $totalcount = 0;
        $cond = " WHERE 1=1";
        if($tag_id != 0) {
            $cond .= " AND `tag_id` = '$tag_id'";
        }  
        
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
    
    public function getGodTextCount($cat_id = 0) {
        $totalcount = 0;
        $cond = " WHERE 1=1";
        
        if($cat_id != 0) {
            $cond .= " AND `cat_id` = '$cat_id'";
        }     
        
        $count_qry = "SELECT COUNT(*) as totalcount FROM `god_text_status` $cond";
        
        $video_res = $this->conn->query($count_qry);
        if ($video_res->num_rows > 0) {
            $res = $video_res->fetch_assoc();
            $totalcount = $res['totalcount'];
        }
        return $totalcount;
    }
    
    public function getPunjabiTextStatus($cat_id = 0, $limit = 10, $offset = 0, $order = 'desc') {
        $result = array();
        $cond = " WHERE 1=1";
        
        if($cat_id != 0) {
            $cond .= " AND `tag_id` = '$cat_id'";
        }  
        
        $sort = "ORDER by `id` desc";
        if($order != 'desc') {
            $sort = "ORDER by `id` $order";
        }
        
        $limit_condition = "LIMIT $offset , $limit";  
        
        $vid_qry = "SELECT * FROM `punjabi_text_status` $cond $sort $limit_condition";
        $video_res = $this->conn->query($vid_qry);
        
        if ($video_res->num_rows > 0) {
            while ($res = $video_res->fetch_assoc()) {
                //tag array
                $tag_ids = $res["tag_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name` FROM `punjabi_tags` where `id` IN ($tag_ids)");
                if ($tag_res->num_rows > 0) {
                    while($tagdata = $tag_res->fetch_assoc()) {
                        $res["tag_arr"][] = $tagdata;
                    }
                }
                unset($res['tag_id']);
                $result[] = $res;
            }
        } else {
            $vid_qry = "SELECT * FROM `punjabi_text_status` $sort $limit_condition";
            $video_res = $this->conn->query($vid_qry);
            
            while ($res = $video_res->fetch_assoc()) {
                //tag array
                $tag_ids = $res["tag_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name` FROM `punjabi_tags` where `id` IN ($tag_ids)");
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
    
    public function getGodTextStatus($cat_id = 0, $limit = 10, $offset = 0, $order = 'desc') {
        $result = array();
        $cond = " WHERE 1=1";
        if($cat_id != 0) {
            $cond .= " AND `cat_id` = '$cat_id'";
        }            
        $sort = "ORDER by `id` desc";
        if($order != 'desc') {
            $sort = "ORDER by `id` $order";
        }
        $limit_condition = "LIMIT $offset , $limit";        
        $vid_qry = "SELECT * FROM `god_text_status` $cond $sort $limit_condition";
        $video_res = $this->conn->query($vid_qry);
        if ($video_res->num_rows > 0) {
            while ($res = $video_res->fetch_assoc()) {
                $result[] = $res;
            }
        }
        return $result;
    }
    
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
    
// ----------------- END TEXT STATUS API FUNCTIONS ----------------- // 

// ----------------- START Tags API FUNCTIONS ----------------- // 
    
    public function getPunjabiTags() {
        $this->conn->query("SET NAMES utf8");
        $result = array();
//        $type_res = $this->conn->query("SELECT * FROM `punjabi_tag_types` WHERE `status` = 1 ORDER BY `order`");
        
//        if ($type_res->num_rows > 0) {
            $i = 0;
//            while ($type = $type_res->fetch_assoc()) {
//                $type_id = $type["id"];
//                $result[$i] = $type;
//                $result[$i]["tags"] = array();
                $video_res = $this->conn->query("SELECT * FROM `punjabi_tags` WHERE `status` = 1 ORDER BY `order`");
                if ($video_res->num_rows > 0) {
                    while ($res = $video_res->fetch_assoc()) {
//                        $type = $res["tag_type_text"];
                        $result[$i]["tags"][] = $res;
                        $i++;
                    }
                }
//            }
//        }
        
        return $result;
    } 
    
    public function getGodTags() {
        $this->conn->query("SET NAMES utf8");
        $result = array();
//        $type_res = $this->conn->query("SELECT * FROM `punjabi_tag_types` WHERE `status` = 1 ORDER BY `order`");
        
//        if ($type_res->num_rows > 0) {
            $i = 0;
//            while ($type = $type_res->fetch_assoc()) {
//                $type_id = $type["id"];
//                $result[$i] = $type;
//                $result[$i]["tags"] = array();
                $video_res = $this->conn->query("SELECT * FROM `god_tags` WHERE `status` = 1 ORDER BY `order`");
                if ($video_res->num_rows > 0) {
                    while ($res = $video_res->fetch_assoc()) {
//                        $type = $res["tag_type_text"];
                        $result[$i]["tags"][] = $res;
                        $i++;
                    }
                }
//            }
//        }
        return $result;
    } 
    
    public function getPunjabiTagInfo($tag_id) {
        $this->conn->query("SET NAMES utf8");
        $result = array();
        $tag_qry = "SELECT `name` FROM `Punjabi_tags` where `id` = $tag_id";
       
        $video_res = $this->conn->query($tag_qry);
        if ($video_res->num_rows > 0) {
            $result = $video_res->fetch_assoc();
        }
        return $result;
    } 
    
    public function getGodTagInfo($tag_id) {
        $this->conn->query("SET NAMES utf8");
        $result = array();
        $tag_qry = "SELECT `tag` FROM `god_tags` where `id` = $tag_id";
        $video_res = $this->conn->query($tag_qry);
        if ($video_res->num_rows > 0) {
            $result = $video_res->fetch_assoc();
        }
        return $result;
    } 
    
// ----------------- END Tags API FUNCTIONS ----------------- // 

// ----------------- START IMAGES API FUNCTIONS ----------------- //   
    
    public function getPunjabiImageCount($tag = "") {
        $totalcount = 0;
        $cond = " WHERE `status` = 1";
        if($tag != "") {
            $cond .= " AND `tags` Like '%$tag%'";
        }          
        $count_qry = "SELECT COUNT(*) as totalcount FROM `punjabi_images` $cond";
       
        $video_res = $this->conn->query($count_qry);
        if ($video_res->num_rows > 0) {
            $res = $video_res->fetch_assoc();
            $totalcount = $res['totalcount'];
        }
        
        if ($totalcount == 0) {
            $count_qry = "SELECT COUNT(*) as totalcount FROM `punjabi_images`";
       
            $video_res = $this->conn->query($count_qry);
            
            if ($video_res->num_rows > 0) {
                $res = $video_res->fetch_assoc();
                $totalcount = $res['totalcount'];
            }
        }
        return $totalcount;
    }
    
    public function getGodImageCount($tag = "") {
        $totalcount = 0;
        $cond = " WHERE `status` = 1";
        
        if($tag != "") {
            $cond .= " AND `tags` Like '%$tag%'";
        }            
        
        $count_qry = "SELECT COUNT(*) as totalcount FROM `god_images` $cond";
        
        $video_res = $this->conn->query($count_qry);
        
        if ($video_res->num_rows > 0) {
            $res = $video_res->fetch_assoc();
            $totalcount = $res['totalcount'];
        }
        return $totalcount;
    }
    
    public function getPunjabiImages($tag_id = 0, $tag_name = "", $limit = 10, $offset = 0, $order_field = 'id', $order = 'desc') {
        $result = array();
        $cond = " WHERE `status` = 1 ";
        if($tag_id != 0 && $tag_name != "") {
            $cond .= " AND `tags` Like '%$tag_name%'";
        }            
        $sort = "ORDER by `$order_field` desc";
        if($order != 'desc') {
            $sort = "ORDER by `$order_field` $order";
        }
        $limit_condition = "LIMIT $offset , $limit";
        
        $img_qry = "SELECT `id`, `title`, `image`, `thumb`, `tags_id`, `views`, `downloads`, `created_date`, `modified_date`, `status` FROM `punjabi_images` $cond $sort $limit_condition";
   
        $img_res = $this->conn->query($img_qry);
        if ($img_res->num_rows > 0) {
            while ($res = $img_res->fetch_assoc()) {
                //tag array
                $tag_ids = $res["tags_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name` FROM `punjabi_tags` where `id` IN ($tag_ids)");
                if ($tag_res->num_rows > 0) {
                    while($tagdata = $tag_res->fetch_assoc()) {
                        $res["tag_arr"][] = $tagdata;
                    }
                }
                unset($res['tags_id']);
                $result[] = $res;
            }
        } else {
            $img_qry = "SELECT `id`, `title`, `image`, `thumb`, `tags_id`, `views`, `downloads`, `created_date`, `modified_date`, `status` FROM `punjabi_images` $sort $limit_condition";
   
            $img_res = $this->conn->query($img_qry);
            while ($res = $img_res->fetch_assoc()) {
                //tag array
                $tag_ids = $res["tags_id"];
                $tag_res = $this->conn->query("SELECT `id`,`name` FROM `punjabi_tags` where `id` IN ($tag_ids)");
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
    
    public function getGodImages($tag_id = 0, $tag_name = "", $limit = 10, $offset = 0, $order_field = 'id', $order = 'desc') {
        $result = array();
        $cond = " WHERE `status` = 1 ";
        if($tag_id != 0 && $tag_name != "") {
            $cond .= " AND `god_tags` Like '%$tag_name%'";
        }            
        $sort = "ORDER by `$order_field` desc";
        if($order != 'desc') {
            $sort = "ORDER by `$order_field` $order";
        }
        $limit_condition = "LIMIT $offset , $limit";
        
        $img_qry = "SELECT `id`, `title`, `image`, `thumb`, `tags_id`, `views`, `downloads`, `created_date`, `modified_date`, `status` FROM `god_images` $cond $sort $limit_condition";
        $img_res = $this->conn->query($img_qry);
        if ($img_res->num_rows > 0) {
            while ($res = $img_res->fetch_assoc()) {
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
    
// ----------------- END IMAGES API FUNCTIONS ----------------- // 

}
?>
