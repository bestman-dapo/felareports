<?php

use MongoDB\Client;
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;
use vendor\swiftmailer\swiftmailer\lib\swift_required;
require 'vendor/autoload.php';
require_once 'vendor\swiftmailer\swiftmailer\lib\swift_required.php';
session_start();


    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $jwt = null;
    // $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    // $arr = explode(" ", $authHeader);
    // $jwt = $arr[1];
    $key = 'A_JWT_SECRET'; 
    if ($_GET['security'] == "asdf:lkj") {
        // http_response_code(200);
        
        $data = json_decode(file_get_contents("php://input"));
        $datum = base64_decode($data->message->data);
        $datas = explode(" ",$datum);
        // print_r($data->message);
   $info = json_encode($datas);

   $file = fopen('new_map_data.json','w+');
   fwrite($file, $info);
   fclose($file);

        $startDate = $datas[1];
            $endDate = $datas[2];
            $fromTime = $startDate."00:00:00+0000";
            $toTime = $endDate."00:00:00+0000";
            $from = new \MongoDB\BSON\UTCDateTime(strtotime($fromTime))."000";
            $to = new \MongoDB\BSON\UTCDateTime(strtotime($toTime))."000";
        
        $client = new MongoDB\Client("mongodb://dashboardClient:npetu9VNL7Vhh6CzVt46jBe@134.209.30.188:10255");
        $collection = $client->felaprod->orders;
        $result = $collection->aggregate([
            [
                '$match' => 
                [
                    'status' => 'completed',
                    "createdAt" => 
                    [ 
                        '$gte' => new \MongoDB\BSON\UTCDateTime($from) , 
                        '$lt' => new \MongoDB\BSON\UTCDateTime($to)
                    ],
                ]
            ],
            [
                '$sort' => 
                [
                    '_id' => -1
                ]
                ],
            [
                '$lookup' => 
                [
                    'from'=> 'sources',
                    'localField'=> 'source.source',
                    'foreignField'=> 'name',
                    'as'=> 'sources'
                ],
            ], 
            [
                '$lookup' => 
                [
                    'from'=> 'payments',
                    'localField'=> 'payment',
                    'foreignField'=> '_id',
                    'as'=> 'payments'
                ],
            ], 
            [
                '$lookup' => 
                [
                    'from'=> 'offerings',
                    'localField'=> 'offering',
                    'foreignField'=> '_id',
                    'as'=> 'offering'
                ],
            ], 
            [
                '$project' =>
                [
                    'sources.name' => 1,
                    'source.sourceId' => 1,
                    'payments.method' => 1,
                    'offering.name' => 1,
                    'description' => 1,
                    'params.recipient' => 1,
                    'params.meter_number' => 1,
                    'params.bundle_code' => 1,
                    'params.smartcard_number' => 1,
                    'params.account_id' => 1,
                    'params.amount' => 1,
                    'params.qty' => 1,
                    'params.quantity' => 1,
                    
                    'reward.amountPaid' => 1,
                    'reward.status' => 1,
                    'reward.recipient' => 1,
                    'status' => 1,
                    '_id' => -1
                ]
                ],
            
            // [
            //     '$limit' => 50
            // ],
        ])->toArray();
        // $json = MongoDB\BSON\toArray(MongoDB\BSON\fromPHP($result));
        // var_dump($result);
        $file = [];
        
        $file['header'][] = "Description";
        $file['header'][] = "Initiator";
        $file['header'][] = "Status";
        $file['header'][] = "Recipient";
        $file['header'][] = "Amount";
        $file['header'][] = "Qty";
        $file['header'][] = "Offering";
        $file['header'][] = "Channel";
        $file['header'][] = "Payment method";
        
        
                $descriptionCounter = 1;
                foreach ($result as $key) {
                    if ($key["description"]) {
                        $file[$descriptionCounter][] = $key["description"];
                        $descriptionCounter++;
                    }
                }
        
        
                $initiator = null;
                foreach ($result as $key => $value) {
                    foreach ($value as $key => $val) {
                        if ($key == "source") {
                            foreach ($val as $init => $initiate) {
                                $initiator[] = $initiate;
                            }
                        }
                    }
                    
                }
                $initiatorCounter = 1;
                foreach ($initiator as $key => $value) {
                    $file[$initiatorCounter][] = $value;
                    $initiatorCounter++;
                }
        
                $statusCounter = 1;
                foreach ($result as $key ) {
                    $file[$statusCounter][] = $key["status"];
                    $statusCounter++;
                }
        
                $recipient = null;
                $amount = null;
                $qty = null;
                foreach ($result as $key) {
                    if (array_key_exists("recipient", iterator_to_array($key['params']))) {
                        $recipient[] = $key['params']['recipient'];
                    }
                    if (array_key_exists("meter_number", iterator_to_array($key['params']))) {
                        $recipient[] = $key['params']['meter_number'];
                    }
                    if (array_key_exists("smartcard_number", iterator_to_array($key['params']))) {
                        $recipient[] = $key['params']['smartcard_number'];
                    }
                    if (array_key_exists("account_id", iterator_to_array($key['params']))) {
                        $recipient[] = $key['params']['account_id'];
                    }
                    if (array_key_exists("amount", iterator_to_array($key['params']))) {
                        $amount[] = $key['params']['amount'];
                        $qty[] = "";
                    }elseif (array_key_exists("qty", iterator_to_array($key['params']))) {
                        $qty[] = $key['params']['qty'];
                        $amount[] = "";
                    }elseif (array_key_exists("quantity", iterator_to_array($key['params']))) {
                        $qty[] = $key['params']['quantity'];
                        $amount[] = "";
                    }elseif (array_key_exists("bundle_code", iterator_to_array($key['params']))) {
                        $amount[] = $key['reward']['amountPaid'];
                        $qty[] = "";
                    }elseif (array_key_exists("smartcard_number", iterator_to_array($key['params']))) {
                        $amount[] = $key['reward']['amountPaid'];
                        $qty[] = "";
                    }else {
                        $qty[] = "";
                            $amount[] = "";
                    }
                    
                    
                }
                $recipientCounter = 1;
                foreach ($recipient as $key => $value) {
                    $file[$recipientCounter][] = $value;
                    $recipientCounter++;
                }
                $amountCounter = 1;
                foreach ($amount as $key => $value) {
                    $file[$amountCounter][] = $value;
                    $amountCounter++;
                }
                $qtyCounter = 1;
                foreach ($qty as $key => $value) {
                    $file[$qtyCounter][] = $value;
                    $qtyCounter++;
                }
        
                $offering = null;
                foreach ($result as $key) {
                    if (array_key_exists("name", iterator_to_array(iterator_to_array($key['offering'])[0]))) {
                        $offering[] = iterator_to_array(iterator_to_array($key['offering'])[0])['name'];
                    }
                }
                $offeringCounter = 1;
                foreach ($offering as $key => $value) {
                    $file[$offeringCounter][] = $value;
                    $offeringCounter++;
                }
        
                $channel = null;
                foreach ($result as $key) {
                    if (array_key_exists("name", iterator_to_array(iterator_to_array($key['sources'])[0]))) {
                        $channel[] = iterator_to_array(iterator_to_array($key['sources'])[0])['name'];
                    }
                }
                $channelCounter = 1;
                foreach ($channel as $key => $value) {
                    $file[$channelCounter][] = $value;
                    $channelCounter++;
                }
        
                $paymentMethod = null;
                foreach ($result as $key) {
                    if (array_key_exists("method", iterator_to_array(iterator_to_array($key['payments'])[0]))) {
                    $paymentMethod[] = iterator_to_array(iterator_to_array($key['payments'])[0])['method'];
                    }
                }
                $methodCounter = 1;
                foreach ($paymentMethod as $key => $value) {
                    $file[$methodCounter][] = $value;
                    $methodCounter++;
                }
                
        
                $delimiter = ',';
                $enclosure = '"';
        
                $fp = fopen('salesreportforlastmonth.csv', 'w+');
                foreach ($file as $fields) {
                    fputcsv($fp, $fields, $delimiter, $enclosure);
                }
                $data_read="";
                rewind($fp);
                //read CSV
                while (!feof($fp)) {
                    $data_read .= fread($fp, 8192);
                }
                fclose($fp);

                try {
                    // Create the SMTP Transport
                    $transport = (new Swift_SmtpTransport('send.one.com', 25))
                        ->setUsername('oladapo@fisshboneandlestr.com')
                        ->setPassword('password_1234');
                 
                    // Create the Mailer using your created Transport
                    $mailer = new Swift_Mailer($transport);
                 
                    // Create a message
                    $message = new Swift_Message();
                 
                    // Set a "subject"
                    $message->setSubject("Orders report for $startDate to $endDate");
                 
                    // Set the "From address"
                    $message->setFrom(['oladapo@fisshboneandlestr.com' => 'Oladapo']);
                 
                    // Set the "To address" [Use setTo method for multiple recipients, argument should be array]
                    $message->addTo('dajooe@gmail.com','Oladapo');
                 
                    // // Add "CC" address [Use setCc method for multiple recipients, argument should be array]
                    // $message->addCc('recipient@gmail.com', 'recipient name');
                 
                    // // Add "BCC" address [Use setBcc method for multiple recipients, argument should be array]
                    // $message->addBcc('recipient@gmail.com', 'recipient name');
                 
                    // Add an "Attachment" (Also, the dynamic data can be attached)
                    $attachment = Swift_Attachment::fromPath('salesreportforlastmonth.csv');
                    // $attachment->setFilename('report.xls');
                    $message->attach($attachment);
                 
                    // Add inline "Image"
                    // $inline_attachment = Swift_Image::fromPath('nature.jpg');
                    // $cid = $message->embed($inline_attachment);
                 
                    // Set the plain-text "Body"
                    $message->setBody("This is the plain text body of the message.\nThanks,\nAdmin");
                 
                    // Set a "Body"
                    $message->addPart('Hi, hope this mail meets you well? <br>Attached is the Orders report you requested for.<br>Thanks,<br>Admin', 'text/html');
                 
                    // Send the message
                    $result = $mailer->send($message);
                    if ($result) {
                        echo "message sent";
                    }
                } catch (Exception $e) {
                  echo $e->getMessage();
                }
        
    }else {
        http_response_code(400);
    }

//     $startDate = "2022-03-01";
//     $endDate = "2022-03-10";
//     $fromTime = $startDate."00:00:00+0000";
//     $toTime = $endDate."00:00:00+0000";
//     $from = new \MongoDB\BSON\UTCDateTime(strtotime($fromTime))."000";
//     $to = new \MongoDB\BSON\UTCDateTime(strtotime($toTime))."000";

// $client = new MongoDB\Client("mongodb://dashboardClient:npetu9VNL7Vhh6CzVt46jBe@134.209.30.188:10255");
// $collection = $client->felaprod->orders;
// $result = $collection->aggregate([
//     [
//         '$match' => 
//         [
//             'status' => 'completed',
//             "createdAt" => 
//             [ 
//                 '$gte' => new \MongoDB\BSON\UTCDateTime($from) , 
//                 '$lt' => new \MongoDB\BSON\UTCDateTime($to)
//             ],
//         ]
//     ],
//     [
//         '$sort' => 
//         [
//             '_id' => -1
//         ]
//         ],
//     [
//         '$lookup' => 
//         [
//             'from'=> 'sources',
//             'localField'=> 'source.source',
//             'foreignField'=> 'name',
//             'as'=> 'sources'
//         ],
//     ], 
//     [
//         '$lookup' => 
//         [
//             'from'=> 'payments',
//             'localField'=> 'payment',
//             'foreignField'=> '_id',
//             'as'=> 'payments'
//         ],
//     ], 
//     [
//         '$lookup' => 
//         [
//             'from'=> 'offerings',
//             'localField'=> 'offering',
//             'foreignField'=> '_id',
//             'as'=> 'offering'
//         ],
//     ], 
//     [
//         '$project' =>
//         [
//             'sources.name' => 1,
//             'source.sourceId' => 1,
//             'payments.method' => 1,
//             'offering.name' => 1,
//             'description' => 1,
//             'params.recipient' => 1,
//             'params.meter_number' => 1,
//             'params.bundle_code' => 1,
//             'params.smartcard_number' => 1,
//             'params.account_id' => 1,
//             'params.amount' => 1,
//             'params.qty' => 1,
//             'params.quantity' => 1,
            
//             'reward.amountPaid' => 1,
//             'reward.status' => 1,
//             'reward.recipient' => 1,
//             'status' => 1,
//             '_id' => -1
//         ]
//         ],
    
//     // [
//     //     '$limit' => 50
//     // ],
// ])->toArray();
// // $json = MongoDB\BSON\toArray(MongoDB\BSON\fromPHP($result));
// // var_dump($result);
// $file = [];

// $file['header'][] = "Description";
// $file['header'][] = "Initiator";
// $file['header'][] = "Status";
// $file['header'][] = "Recipient";
// $file['header'][] = "Amount";
// $file['header'][] = "Qty";
// $file['header'][] = "Offering";
// $file['header'][] = "Channel";
// $file['header'][] = "Payment method";


//         $descriptionCounter = 1;
//         foreach ($result as $key) {
//             if ($key["description"]) {
//                 $file[$descriptionCounter][] = $key["description"];
//                 $descriptionCounter++;
//             }
//         }


//         $initiator = null;
//         foreach ($result as $key => $value) {
//             foreach ($value as $key => $val) {
//                 if ($key == "source") {
//                     foreach ($val as $init => $initiate) {
//                         $initiator[] = $initiate;
//                     }
//                 }
//             }
            
//         }
//         $initiatorCounter = 1;
//         foreach ($initiator as $key => $value) {
//             $file[$initiatorCounter][] = $value;
//             $initiatorCounter++;
//         }

//         $statusCounter = 1;
//         foreach ($result as $key ) {
//             $file[$statusCounter][] = $key["status"];
//             $statusCounter++;
//         }

//         $recipient = null;
//         $amount = null;
//         $qty = null;
//         foreach ($result as $key) {
//             if (array_key_exists("recipient", iterator_to_array($key['params']))) {
//                 $recipient[] = $key['params']['recipient'];
//             }
//             if (array_key_exists("meter_number", iterator_to_array($key['params']))) {
//                 $recipient[] = $key['params']['meter_number'];
//             }
//             if (array_key_exists("smartcard_number", iterator_to_array($key['params']))) {
//                 $recipient[] = $key['params']['smartcard_number'];
//             }
//             if (array_key_exists("account_id", iterator_to_array($key['params']))) {
//                 $recipient[] = $key['params']['account_id'];
//             }
//             if (array_key_exists("amount", iterator_to_array($key['params']))) {
//                 $amount[] = $key['params']['amount'];
//                 $qty[] = "";
//             }elseif (array_key_exists("qty", iterator_to_array($key['params']))) {
//                 $qty[] = $key['params']['qty'];
//                 $amount[] = "";
//             }elseif (array_key_exists("quantity", iterator_to_array($key['params']))) {
//                 $qty[] = $key['params']['quantity'];
//                 $amount[] = "";
//             }elseif (array_key_exists("bundle_code", iterator_to_array($key['params']))) {
//                 $amount[] = $key['reward']['amountPaid'];
//                 $qty[] = "";
//             }elseif (array_key_exists("smartcard_number", iterator_to_array($key['params']))) {
//                 $amount[] = $key['reward']['amountPaid'];
//                 $qty[] = "";
//             }else {
//                 $qty[] = "";
//                     $amount[] = "";
//             }
            
            
//         }
//         $recipientCounter = 1;
//         foreach ($recipient as $key => $value) {
//             $file[$recipientCounter][] = $value;
//             $recipientCounter++;
//         }
//         $amountCounter = 1;
//         foreach ($amount as $key => $value) {
//             $file[$amountCounter][] = $value;
//             $amountCounter++;
//         }
//         $qtyCounter = 1;
//         foreach ($qty as $key => $value) {
//             $file[$qtyCounter][] = $value;
//             $qtyCounter++;
//         }

//         $offering = null;
//         foreach ($result as $key) {
//             if (array_key_exists("name", iterator_to_array(iterator_to_array($key['offering'])[0]))) {
//                 $offering[] = iterator_to_array(iterator_to_array($key['offering'])[0])['name'];
//             }
//         }
//         $offeringCounter = 1;
//         foreach ($offering as $key => $value) {
//             $file[$offeringCounter][] = $value;
//             $offeringCounter++;
//         }

//         $channel = null;
//         foreach ($result as $key) {
//             if (array_key_exists("name", iterator_to_array(iterator_to_array($key['sources'])[0]))) {
//                 $channel[] = iterator_to_array(iterator_to_array($key['sources'])[0])['name'];
//             }
//         }
//         $channelCounter = 1;
//         foreach ($channel as $key => $value) {
//             $file[$channelCounter][] = $value;
//             $channelCounter++;
//         }

//         $paymentMethod = null;
//         foreach ($result as $key) {
//             if (array_key_exists("method", iterator_to_array(iterator_to_array($key['payments'])[0]))) {
//             $paymentMethod[] = iterator_to_array(iterator_to_array($key['payments'])[0])['method'];
//             }
//         }
//         $methodCounter = 1;
//         foreach ($paymentMethod as $key => $value) {
//             $file[$methodCounter][] = $value;
//             $methodCounter++;
//         }
        

//         $delimiter = ',';
//         $enclosure = '"';

//         $fp = fopen('salesreportforlastmonth.csv', 'w+');
//         foreach ($file as $fields) {
//             fputcsv($fp, $fields, $delimiter, $enclosure);
//         }
//         $data_read="";
//         rewind($fp);
//         //read CSV
//         while (!feof($fp)) {
//             $data_read .= fread($fp, 8192);
//         }
//         fclose($fp);


// print_r($file);