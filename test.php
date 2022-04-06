<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use vendor\swiftmailer\swiftmailer\lib\swift_required;
require 'vendor\autoload.php';

require 'vendor\phpmailer\phpmailer\src\Exception.php';
require 'vendor\phpmailer\phpmailer\src\PHPmailer.php';
require 'vendor\phpmailer\phpmailer\src\SMTP.php';
require_once 'vendor\swiftmailer\swiftmailer\lib\swift_required.php';


// $mail = new PHPMailer(TRUE); 
// try {
// $mail->isSMTP();                      
// $mail->Host = 'send.one.com';       
// $mail->SMTPAuth = true;               
// $mail->Username = 'oladapo@fisshboneandlestr.com';   
// $mail->Password = 'password_1234';   
// $mail->SMTPSecure = 'tls';            
// $mail->SMTPDebug = SMTP::DEBUG_SERVER;            
// $mail->Port = 465;    


// // Sender info 
// $mail->setFrom('oladapo@fisshboneandlestr.com', 'Oladapo'); 
// $mail->addReplyTo('oladapo@fisshboneandlestr.com', 'Oladapo'); 

// // Add a recipient 
// $mail->addAddress('dajooe@gmail.com');

// // Set email format to HTML 
// $mail->isHTML(true);

// // Mail subject 
// $mail->Subject = 'Email from Felareports'; 

// // Mail body content 
// $bodyContent = '<h1>How to Send orders report </h1>'; 
// $bodyContent .= '<p>This HTML email is sent from the Oladapo <b>Oyebanji</b></p>'; 
// $mail->Body    = $bodyContent;

// // Send email 
// $mail->send();
// }
// catch (Exception $e)
// {
//    echo $e->errorMessage();
// }
// catch (\Exception $e)
// {
//    echo $e->getMessage();
// }

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
    $message->addTo('oyebanjioladapo1@gmail.com','Oladapo');
 
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
    $message->addPart('Hi, hope this mail meets you well? <br>Attached is the Orders report you .<br>Thanks,<br>Admin', 'text/html');
 
    // Send the message
    $result = $mailer->send($message);
    if ($result) {
        echo "message sent";
    }
} catch (Exception $e) {
  echo $e->getMessage();
}