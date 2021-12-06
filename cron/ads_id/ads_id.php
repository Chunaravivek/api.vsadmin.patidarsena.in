<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//$conn = mysqli_connect("localhost", "vsadmin", "vsadmin", "za8zKY8FXT8WSsYH");
$conn = mysqli_connect("127.0.0.1", "root", "s4ittech@123", "mvs");
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
mysqli_set_charset($conn, "utf8");
//
$response = array();
$response['Add'] = 0;
$response['Updated'] = 0;
$response['Total'] = 0;
$response['response'] = '';
$ads_again_details = "SELECT * FROM `ads` WHERE update_status = 2";
$res = mysqli_query($conn, $ads_again_details);

if ($res->num_rows > 0) {
    $u = 0;
    while ($ad = $res->fetch_assoc()) {
        
        $get_id = $ad['id'];
        unset($ad['acc_id']);
        $app_code = $ad['app_code'];
        
//        $filename = $_SERVER['DOCUMENT_ROOT'].'ads_json/'.$app_code.'_ad_id.json';
        $filename = $_SERVER['DOCUMENT_ROOT'].'/api.vsadmin.patidarsena.in/ads_json/'.$app_code.'_ad_id.json';
        $upd_ads_query = "UPDATE `ads` SET `json_path`= '$app_code' where `id` = $get_id";
       
        mysqli_query($conn, $upd_ads_query);
        unset($ad['app_code']);
        unset($ad['id']);
        $Ads_Id = array(
            'google_appopen'        => trim($ad['google_appopen']),
            'google_appopen_2'      => isset($ad['google_appopen_2']) ?  trim($ad['google_appopen_2']) : '',
            'google_appopen_3'      => isset($ad['google_appopen_3']) ? trim($ad['google_appopen_3']) : '',
            'google_banner'         => trim($ad['google_banner']),
            'google_fullad'         => trim($ad['google_fullad']),
            'google_fullad_2'       => isset($ad['google_fullad_2']) ? trim($ad['google_fullad_2']) : '',
            'google_fullad_3'       => isset($ad['google_fullad_3']) ? trim($ad['google_fullad_3']) : '',
            'google_fullad_splash'  => trim($ad['google_fullad_splash']),
            'google_reward_ad'      => trim($ad['google_reward_ad']),
            'google_native'         => trim($ad['google_native']),
            'google_native_2'       => isset($ad['google_native_2']) ? trim($ad['google_native_2']) : '',
            'google_native_3'       => isset($ad['google_native_3']) ? trim($ad['google_native_3']) : '',
            'fb_full_ad'            => trim($ad['fb_full_ad']),
            'fb_banner'             => trim($ad['fb_banner']),
            'fb_full_native'        => trim($ad['fb_full_native']),
            'fb_native_banner'      => trim($ad['fb_native_banner']),
            'fb_dialog'             => trim($ad['fb_dialog']),
            'mediation'             => $ad['mediation'],
            'status'                => $ad['status'],
            'update_status'         => $ad['update_status'],
            'ad_dialogue'           => $ad['ad_dialogue'],
            'open_inter'            => $ad['open_inter'],
            'in_house'              => $ad['in_house'],
        );
        
        $ads_groups = array(
            'ad_call'             => $ad['ad_call'],
            'ac_name'             => trim($ad['ac_name']),
            'adptive_banner'      => $ad['adptive_banner'],
            'anim'                => $ad['anim'],
            'email'               => $ad['email'],
            'back_ad_count'       => $ad['back_ad_count'],
            'forward_ad_count'    => $ad['forward_ad_count'],
            'forward_ad'          => isset($ad['forward_ad']) ? $ad['forward_ad'] : '',
            'back_ad'             => isset($ad['back_ad']) ? $ad['back_ad'] : '',
            'native_end_time'     => trim($ad['native_end_time']),
            'native_start_time'   => trim($ad['native_start_time']),
            'native_size'         => $ad['native_size'],
            'exit_native_ad'      => $ad['exit_native_ad'],
            'qureka_ad'           => $ad['qureka_ad'],
            'qureka_url'          => trim($ad['qureka_url']),
            'xcount'              => $ad['xcount'],
            'xminute'             => $ad['xminute'],
            'position1'           => isset($ad['position1']) ? $ad['position1'] : '',
            'position2'           => isset($ad['position2']) ? $ad['position2'] : '',
            'position3'           => isset($ad['position3']) ? $ad['position3'] : '',
        );
        
        $path = trim($ad['path']);
        $app_status = $ad['app_status'];
        $ads_id = array('ad_ids' => $Ads_Id, 'ads' => $ads_groups, 'path' => $path, 'app_status' => $app_status);
        $json_array = json_encode($ads_id);
        $response['Updated']++;
        if (file_exists($filename)) {
            file_put_contents($filename, json_encode($ads_id));
        } else {
            file_put_contents($filename, json_encode($ads_id));
        }
        $u++;
    }
} 
//
$ads_details = "SELECT * FROM `ads` WHERE update_status = 1";
$res = mysqli_query($conn, $ads_details);

if ($res->num_rows > 0) {
    while ($ad = $res->fetch_assoc()) {
        $get_id = $ad['id'];
        unset($ad['acc_id']);
        $app_code = $ad['app_code'];
        
//        $filename = $_SERVER['DOCUMENT_ROOT'].'ads_json/'.$app_code.'_ad_id.json';
        $filename = $_SERVER['DOCUMENT_ROOT'].'/api.vsadmin.patidarsena.in/ads_json/'.$app_code.'_ad_id.json';
       
        $upd_ads_query = "UPDATE `ads` SET `json_path`= '$app_code' where `id` = $get_id";
        mysqli_query($conn, $upd_ads_query);
        unset($ad['app_code']);
        unset($ad['id']);
        
        $Ads_Id = array(
            'google_appopen'        => trim($ad['google_appopen']),
            'google_appopen_2'      => isset($ad['google_appopen_2']) ?  trim($ad['google_appopen_2']) : '',
            'google_appopen_3'      => isset($ad['google_appopen_3']) ? trim($ad['google_appopen_3']) : '',
            'google_banner'         => trim($ad['google_banner']),
            'google_fullad'         => trim($ad['google_fullad']),
            'google_fullad_2'       => isset($ad['google_fullad_2']) ? trim($ad['google_fullad_2']) : '',
            'google_fullad_3'       => isset($ad['google_fullad_3']) ? trim($ad['google_fullad_3']) : '',
            'google_fullad_splash'  => trim($ad['google_fullad_splash']),
            'google_reward_ad'      => trim($ad['google_reward_ad']),
            'google_native'         => trim($ad['google_native']),
            'google_native_2'       => isset($ad['google_native_2']) ? trim($ad['google_native_2']) : '',
            'google_native_3'       => isset($ad['google_native_3']) ? trim($ad['google_native_3']) : '',
            'fb_full_ad'            => trim($ad['fb_full_ad']),
            'fb_banner'             => trim($ad['fb_banner']),
            'fb_full_native'        => trim($ad['fb_full_native']),
            'fb_native_banner'      => trim($ad['fb_native_banner']),
            'fb_dialog'             => trim($ad['fb_dialog']),
            'mediation'             => $ad['mediation'],
            'status'                => $ad['status'],
            'update_status'         => $ad['update_status'],
            'ad_dialogue'           => $ad['ad_dialogue'],
            'open_inter'            => $ad['open_inter'],
            'in_house'              => $ad['in_house'],
        );
        
        $ads_groups = array(
            'ad_call'             => $ad['ad_call'],
            'ac_name'             => trim($ad['ac_name']),
            'adptive_banner'      => $ad['adptive_banner'],
            'anim'                => $ad['anim'],
            'email'               => $ad['email'],
            'back_ad_count'       => $ad['back_ad_count'],
            'forward_ad_count'    => $ad['forward_ad_count'],
            'forward_ad'          => isset($ad['forward_ad']) ? $ad['forward_ad'] : '',
            'back_ad'             => isset($ad['back_ad']) ? $ad['back_ad'] : '',
            'native_end_time'     => trim($ad['native_end_time']),
            'native_start_time'   => trim($ad['native_start_time']),
            'native_size'         => $ad['native_size'],
            'exit_native_ad'      => $ad['exit_native_ad'],
            'qureka_ad'           => $ad['qureka_ad'],
            'qureka_url'          => trim($ad['qureka_url']),
            'xcount'              => $ad['xcount'],
            'xminute'             => $ad['xminute'],
            'position1'           => isset($ad['position1']) ? $ad['position1'] : '',
            'position2'           => isset($ad['position2']) ? $ad['position2'] : '',
            'position3'           => isset($ad['position3']) ? $ad['position3'] : '',
        );
        
        $path = trim($ad['path']);
        $app_status = $ad['app_status'];
        $ads_id = array('ad_ids' => $Ads_Id, 'ads' => $ads_groups, 'path' => $path, 'app_status' => $app_status);
        $response['Add']++;
        $response['response'] = $ads_id;
        $json_array = json_encode($ads_id);
        if (!file_exists($filename)) {
            file_put_contents($filename, json_encode($ads_id), FILE_APPEND);
        }
    }
}
$response['Total'] = $response['Add'] + $response['Updated'];

echo json_encode($response); exit;
