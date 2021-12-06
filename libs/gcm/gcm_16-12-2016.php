<?php
/**
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class GCM {

    // constructor
    function __construct() {
        
    }

    // sending push message to single user by gcm registration id
    public function send($to, $message) {
        $fields = array(
            'to' => $to,
            'data' => $message,
        );
        return $this->sendPushNotification($fields);
    }

    // Sending message to a topic by topic id
    public function sendToTopic($to, $message,$firebase_id) {
        
      

                
       $feedback_android=$total_android=0;
        
        $url = 'https://fcm.googleapis.com/fcm/send';
        
        $fields = array(
            'registration_ids' => $to,
            'data' => $message,
            'type' => 7
        );
  
        $headers = array(
            'Authorization: key='.$firebase_id,
            'Content-Type: application/json'
        );      
        
        
        $ch = curl_init();
        if (!$ch)
            die("Android Failed to connect $err $errstr\n");
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields,true));
        $result = curl_exec($ch);
        
//        echo "<pre>";
//        print_r($result);
//        exit;

        // close cURL resource, and free up system resources
        curl_close($ch);

        return $result;

    }

    // sending push message to multiple users by gcm registration ids
    public function sendMultiple($registration_ids, $message) {
        $fields = array(
            'registration_ids' => $registration_ids,
            'data' => $message,
            'type' => 7,
        );

        return $this->sendPushNotification($fields);
    }

    // function makes curl request to gcm servers
    private function sendPushNotification($fields,$firebase_id) {
//        echo "<pre>";
//        print_r($fields);
//        exit;

        // include config
//        include_once __DIR__ . '/../../include/config.php';

        // Set POST variables
        $url = 'https://fcm.googleapis.com/fcm/send';
//        $url = 'https://gcm-http.googleapis.com/gcm/send';

        $headers = array(
            'Authorization: key=' . $firebase_id,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        echo "<pre>";
//        print_r($fields);
//        exit;

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);

        return $result;
    }

}

?>