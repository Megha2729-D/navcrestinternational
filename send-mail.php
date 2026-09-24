<?php use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/phpmailer/vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/vendor/phpmailer/phpmailer/src/SMTP.php';
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: contact-us.html");
    exit;
} /* |-------------- | SPAM PREVENTION - HONEYPOT |-------------- */
if (!empty($_POST['user_url'])) {
    header("Location: success.html");
    exit;
} /* |-------------- | GOOGLE reCAPTCHA VERIFICATION |--------------*/
$recaptchaSecret = '6Lf03cotAAAAAF2WscPi2tqu-7iU38NprIqbS_97';
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
if (empty($recaptchaResponse)) {
    header("Location: failed.html");
    exit;
}
$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
$postData = http_build_query(['secret' => $recaptchaSecret, 'response' => $recaptchaResponse]);
$ch = curl_init($verifyUrl);
curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $postData, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,]);
$verifyResponse = curl_exec($ch);
if ($verifyResponse === false) {
    curl_close($ch);
    header("Location: failed.html");
    exit;
}   
curl_close($ch);
$responseData = json_decode($verifyResponse);
if (!$responseData || empty($responseData->success)) {
    header("Location: failed.html");
    exit;
} 
/* |-------------- | COLLECT FORM DATA |-------------- */
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$country = trim($_POST['country'] ?? '');
$message = trim($_POST['message'] ?? '');
$products = $_POST['products'] ?? [];

$adminEmail = 'ddsm2729@gmail.com';
if (!is_array($products)) {
    $products = [];
} /* |-------------------------------------------------------------------------- | SERVER-SIDE VALIDATION |-------------------------------------------------------------------------- */
if ($name === '' || mb_strlen($name) < 2) {
    header("Location: failed.html");
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: failed.html");
    exit;
}
if ($country === '') {
    header("Location: failed.html");
    exit;
}
if (empty($products)) {
    header("Location: failed.html");
    exit;
}
if ($message === '' || mb_strlen($message) < 10) {
    header("Location: failed.html");
    exit;
} /* |-------------------------------------------------------------------------- | SPAM LINK CHECK |-------------------------------------------------------------------------- */
$messageLower = strtolower($message);
if (substr_count($messageLower, 'http') > 2 || substr_count($messageLower, 'www.') > 2 || strpos($messageLower, '<a href') !== false) {
    header("Location: failed.html");
    exit;
} /* |-------------------------------------------------------------------------- | SANITIZE PRODUCTS |-------------------------------------------------------------------------- */
$allowedProducts = ['5 KG Coco Peat Blocks', '5 KG Custom Mix Blocks', '5 KG Buffered Coco Peat Blocks', 'Coco Chips 5 KG Blocks', 'Coco Peat 650G Bricks', 'Flat Bed Grow Bags'];
$selectedProducts = [];
foreach ($products as $product) {
    if (in_array($product, $allowedProducts, true)) {
        $selectedProducts[] = $product;
    }
}
if (empty($selectedProducts)) {
    header("Location: failed.html");
    exit;
}
$productsText = implode(', ', $selectedProducts);

$mail = new PHPMailer(true);

try {

    /* |-------------------------------------------------------------------------- */
    /* | SMTP SETTINGS */
    /* |-------------------------------------------------------------------------- */

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'jawahar@navcrestinternational.com';
    $mail->Password = 'ybenohcqwgjwskvr';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->Timeout = 30;

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
    $mail->setFrom('jawahar@navcrestinternational.com', 'Navcrest International');
    $mail->addAddress('meghu27022002@gmail.com', 'Admin');
    $mail->addReplyTo($email, $name);
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safeCountry = htmlspecialchars($country, ENT_QUOTES, 'UTF-8');
    $safeProducts = htmlspecialchars($productsText, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(
        htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
    );

    $mail->isHTML(true);

    $mail->Subject = 'New Product Inquiry - Navcrest International';

    $mail->Body = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <style>
            body {
                margin: 0;
                padding: 0;
                background: #f5f7f6;
                font-family: Arial, Helvetica, sans-serif;
                color: #333333;
            }

            .wrapper {
                width: 100%;
                padding: 40px 15px;
                box-sizing: border-box;
            }

            .container {
                max-width: 650px;
                margin: 0 auto;
                background: #ffffff;
                border-radius: 12px;
                overflow: hidden;
            }

            .header {
                background: #1f4d3a;
                padding: 35px 30px;
                text-align: center;
            }

            .header h2 {
                margin: 0;
                color: #ffffff;
                font-size: 24px;
                letter-spacing: 1px;
            }

            .body {
                padding: 35px 30px;
            }

            .body h3 {
                margin: 0 0 20px;
                color: #1f4d3a;
                font-size: 20px;
            }

            .intro {
                color: #555555;
                line-height: 1.6;
                margin-bottom: 25px;
            }

            .info-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 25px;
                background: #f7f9f8;
            }

            .info-table td {
                padding: 13px 15px;
                border-bottom: 1px solid #e8ecea;
                vertical-align: top;
            }

            .info-table td:first-child {
                width: 120px;
                font-weight: bold;
                color: #1f4d3a;
            }

            .message-box {
                background: #fafafa;
                border: 1px solid #e5e8e6;
                border-radius: 8px;
                padding: 20px;
                margin-top: 20px;
            }

            .message-box h4 {
                margin: 0 0 12px;
                color: #1f4d3a;
            }

            .message-text {
                color: #555555;
                line-height: 1.7;
            }

            .footer {
                background: #f1f4f2;
                padding: 20px 30px;
                text-align: center;
                color: #777777;
                font-size: 12px;
                line-height: 1.6;
            }

            a {
                color: #1f4d3a;
            }
        </style>
    </head>

    <body>

        <div class="wrapper">

            <div class="container">

                <div class="header">
                    <h2>Navcrest International</h2>
                </div>

                <div class="body">

                    <h3>New Product Inquiry</h3>

                    <p class="intro">
                        You have received a new inquiry through the
                        Navcrest International website contact form.
                    </p>

                    <table class="info-table">

                        <tr>
                            <td>Name</td>
                            <td>' . $safeName . '</td>
                        </tr>

                        <tr>
                            <td>Email</td>
                            <td>
                                <a href="mailto:' . $safeEmail . '">
                                    ' . $safeEmail . '
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td>Country</td>
                            <td>' . $safeCountry . '</td>
                        </tr>

                        <tr>
                            <td>Products</td>
                            <td>' . $safeProducts . '</td>
                        </tr>

                    </table>

                    <div class="message-box">

                        <h4>Message</h4>

                        <div class="message-text">
                            ' . $safeMessage . '
                        </div>

                    </div>

                    <p style="margin-top:25px;">

                        <a
                            href="mailto:' . $safeEmail . '"
                            style="
                                display:inline-block;
                                padding:12px 24px;
                                background:#1f4d3a;
                                color:#ffffff;
                                text-decoration:none;
                                border-radius:5px;
                            "
                        >
                            Reply to ' . $safeName . '
                        </a>

                    </p>

                </div>

                <div class="footer">
                    This is an automated message sent from the
                    Navcrest International website contact form.
                </div>

            </div>

        </div>

    </body>
    </html>
    ';

    /* |-------------------------------------------------------------------------- */
    /* | PLAIN TEXT VERSION */
    /* |-------------------------------------------------------------------------- */

    $mail->AltBody =
        "New Product Inquiry - Navcrest International\n\n" .
        "Name: " . $name . "\n" .
        "Email: " . $email . "\n" .
        "Country: " . $country . "\n" .
        "Products: " . $productsText . "\n\n" .
        "Message:\n" . $message;

    /* |-------------------------------------------------------------------------- */
    /* | SEND EMAIL */
    /* |-------------------------------------------------------------------------- */

    $mail->send();

    header("Location: success.html?name=" . urlencode($name));
    exit;

} catch (Exception $e) {

    echo "Mailer Error: " . $mail->ErrorInfo;
    exit;
}
