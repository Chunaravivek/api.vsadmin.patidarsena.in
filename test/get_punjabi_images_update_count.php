<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$url = "http://localhost/api.vsadmin.patidarsena.in/v1/get_punjabi_images_update_count";
$ch = curl_init();

$headers = array(
//    "POST http://localhost/api.vsadmin.patidarsena.in/v1/register HTTP/1.1",
    "HOST: localhost",
    "Apikey: dfe5192739486d9f031ebb45ab2516a9",
    "Content-Type: application/x-www-form-urlencoded",
    "Accept: application/json",
);

$post_fields = array(
    "app_code" => 'eIoyV1',
//    "downloads" => 1,
    "views" => 1
);

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_HTTPGET, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

$output = curl_exec($ch);

curl_close($ch);

echo "<pre>";
print_r($output);
exit;