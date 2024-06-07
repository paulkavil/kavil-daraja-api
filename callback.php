<?php
// callback.php
$data = file_get_contents('php://input');
$decoded = json_decode($data, true);

// Log the callback data for debugging purposes
file_put_contents('callback_log.txt', print_r($decoded, true), FILE_APPEND);

// Process the callback data (you can add your custom logic here)
if (isset($decoded['Body']['stkCallback']['ResultCode'])) {
    $resultCode = $decoded['Body']['stkCallback']['ResultCode'];
    if ($resultCode == 0) {
        // Payment was successful
        $amount = $decoded['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
        $mpesaReceiptNumber = $decoded['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
        $phoneNumber = $decoded['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];
        
        // Implement your business logic here
    }
}

// Send a response back to M-Pesa
header("Content-Type: application/json");
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Success"]);
?>
