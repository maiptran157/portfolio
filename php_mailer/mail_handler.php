<?php
require_once('email_config.php');
require('PHPMailer/src/PHPMailer.php');
require('PHPMailer/src/SMTP.php');


//Validate POST inputs
$message = [];
$output = [
    'success' => null,
    // 'subject' => '',
    'messages' => []
];

//Sanitize name field
$message['name'] = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
if(empty($message['name'])){
    $output['success'] = false;
    $output['messages'][] = 'missing name key';
};

//Validate email field
$message['email'] = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if(empty($message['email'])){
    $output['success'] = false;
    $output['messages'][] = 'invalid email key';
};

//Sanitize message
$message['message'] = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
if(empty($message['message'])){
    $output['success'] = false;
    $output['messages'][] = 'missing message key';
};

// Sanitixe subject
// $message['subject'] = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
// if(empty($message['subject'])){
//     $output['success'] = false;
//     $output['messages'][] = 'missing subject key';
// };

//Sanitize phone number
//$message['phone'] = preg_replace('/[^0-9]','',$_POST['phone_number']);
// if(empty($message['phone']) && count($message['phone']) >= 10){
//     $output['success'] = false;
//     $output['messages'][] = 'missing phone key';
// };

if($output['success'] !== null) {
    http_response_code(422); // 422 is unprocessible entity. 400 is bad request
    echo json_encode($output);
    exit();
}

//Set up email object
// $mail = new PHPMailer;
$mail = new PHPMailer\PHPMailer\PHPMailer;
$mail->SMTPDebug = 3;           // Enable verbose debug output. Change to 0 to disable debugging output.
$mail->isSMTP();                // Set mailer to use SMTP.
$mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers.
$mail->SMTPAuth = true;         // Enable SMTP authentication
$mail->Username = EMAIL_USER;   // SMTP username
$mail->Password = EMAIL_PASS;   // SMTP password
$mail->SMTPSecure = 'tls';      // Enable TLS encryption, `ssl` also accepted, but TLS is a newer more-secure encryption
$mail->Port = 587;              // TCP port to connect to
$options = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
$mail->smtpConnect($options); //sett those options above

// $mail->From = $message['email'];  // sender's email address (shows in "From" field)
// $mail->FromName = $message['name'];   // sender's name (shows in "From" field)
// $mail->addAddress(EMAIL_TO_ADDRESS, EMAIL_USERNAME);  // Add a recipient

$mail->From = EMAIL_USER;  // sender's email address (shows in "From" field)
$mail->FromName = EMAIL_USERNAME;   // sender's name (shows in "From" field)
$mail->addAddress(EMAIL_TO_ADDRESS, EMAIL_USERNAME);  // Add a recipient

//$mail->addAddress('ellen@example.com');                        // Name is optional
$mail->addReplyTo($message['email'], $message['name']);                          // Add a reply-to address
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');
//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML
// $mail->Subject = 'Here is the subject';
// $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

$message['subject'] = $message['name']."has sent you a message on your porfolio";

//HTML email
$message['message'] = nl2br($message['message']);
$mail->Body    = $message['message'];
$mail->AltBody = htmlentities($message['message']);
// OR //plain text email
$mail->isHTML(false);
$mail->Body = $message['message'];



//Attempt email send, ouput result to client
if(!$mail->send()) {
    $output['success'] = false;
    $ouput['messages'][] = $mail->ErrorInfo;
} else {
    $ouput['success'] = true;
}
echo json_encode($output);
?>