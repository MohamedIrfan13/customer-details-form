
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/mail-config.php';

use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Please submit the customer form.');
}

$fields = [
    'party_name'      => 'Party Name',
    'party_roles'     => 'Party Roles',
    'contact_person'  => 'Contact Person',
    'city'            => 'City',
    'po_box'          => 'P.O. Box',
    'phone'           => 'Phone',
    'fax'             => 'Fax',
    'customer_email'  => 'Customer Email',
    'country'         => 'Country',
    'customer_code'   => 'Customer Code',
    'cr_no'           => 'CR No'
];

$data = [];

foreach ($fields as $key => $label) {
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) {
        exit('Invalid form submission.');
    }

    $value = trim($_POST[$key]);

    if ($value === '') {
        exit('Please complete all required fields.');
    }

    $data[$key] = $value;
}

if (!filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
    exit('Please enter a valid customer email address.');
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = GMAIL_ADDRESS;
    $mail->Password = GMAIL_APP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom(GMAIL_ADDRESS, 'Customer Form');
    $mail->addAddress(RECEIVER_EMAIL);

    $mail->addReplyTo($data['customer_email']);

    $mail->isHTML(true);
    $mail->Subject = 'New Customer Details';

    $body = '<h2>New Customer Details</h2>';
    $body .= '<table border="1" cellpadding="8" cellspacing="0">';

    foreach ($fields as $key => $label) {
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $safeValue = htmlspecialchars(
            $data[$key],
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $body .= "<tr><th>$safeLabel</th><td>$safeValue</td></tr>";
    }

    $body .= '</table>';

    $mail->Body = $body;
    $mail->AltBody = "New customer details received.";

    $mail->send();

    echo '<h2>Success!</h2>';
    echo '<p>Customer details have been sent to your email.</p>';

} catch (\Throwable $e) {

    error_log('Customer email error: ' . $e->getMessage());

    http_response_code(500);
    echo '<h2>Email could not be sent.</h2>';
    echo '<p>Please check your Gmail SMTP configuration.</p>';
}
