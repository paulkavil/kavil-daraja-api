<?php
// Include database configuration
include 'dbconnection.php';

// Get the callback data
$data = file_get_contents('php://input');
$decoded = json_decode($data, true);

// Log the callback data for debugging purposes
file_put_contents('callback_log.txt', print_r($decoded, true), FILE_APPEND);

// Process the callback data
if (isset($decoded['Body']['stkCallback']['ResultCode'])) {
    $merchantRequestID = $decoded['Body']['stkCallback']['MerchantRequestID'];
    $checkoutRequestID = $decoded['Body']['stkCallback']['CheckoutRequestID'];
    $resultCode = $decoded['Body']['stkCallback']['ResultCode'];
    $resultDesc = $decoded['Body']['stkCallback']['ResultDesc'];
    
    if ($resultCode == 0) {
        // Payment was successful
        $amount = $decoded['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
        $mpesaReceiptNumber = $decoded['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
        $transactionDate = $decoded['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'];
        $phoneNumber = $decoded['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];

        // Convert transaction date to MySQL datetime format
        $transactionDate = DateTime::createFromFormat('YmdHis', $transactionDate)->format('Y-m-d H:i:s');
    } else {
        // Payment failed, set empty values for optional fields
        $amount = 0;
        $mpesaReceiptNumber = "";
        $transactionDate = null;
        $phoneNumber = "";
    }

    // Prepare SQL query to insert data
    $stmt = $conn->prepare("INSERT INTO transactions (merchant_request_id, checkout_request_id, result_code, result_desc, amount, mpesa_receipt_number, transaction_date, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisdsss", $merchantRequestID, $checkoutRequestID, $resultCode, $resultDesc, $amount, $mpesaReceiptNumber, $transactionDate, $phoneNumber);

    // Execute query and check for errors
    if ($stmt->execute()) {
        // Insert successful
        $response = ["ResultCode" => 0, "ResultDesc" => "Success"];
    } else {
        // Insert failed
        $response = ["ResultCode" => 1, "ResultDesc" => "Failed to save transaction data"];
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}

// Send a response back to M-Pesa
header("Content-Type: application/json");
echo json_encode($response);
?>
