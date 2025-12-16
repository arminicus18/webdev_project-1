<?php
function sendTicketEmail($recipientEmail, $recipientName, $ticketDetails)
{

    // 1. CONFIGURATION
    $apiKey = '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';
    $url = 'https://api.brevo.com/v3/smtp/email';

    // 2. PREPARE EMAIL CONTENT
    $tourName = $ticketDetails['tour_name'];
    $refID = $ticketDetails['ref_id'];
    $date = $ticketDetails['date'];
    $pax = $ticketDetails['pax'];
    $price = $ticketDetails['price'];
    $joiner = $ticketDetails['joiner'];
    $pickup = $ticketDetails['pickup'];

    // HTML Design for the Ticket
    $htmlContent = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #333; background-color: #121212; color: #f0f0f0;'>
        <div style='background-color: #FFC107; padding: 20px; text-align: center; color: #000;'>
            <h1 style='margin:0; font-size: 24px;'>MOUNT PINOY</h1>
            <p style='margin:0; font-weight:bold;'>Adventure Ticket</p>
        </div>
        <div style='padding: 20px;'>
            <h2 style='color: #FFC107; margin-top: 0;'>$tourName</h2>
            <p style='margin-bottom: 5px;'><strong>Ref ID:</strong> #$refID</p>
            <p style='margin-bottom: 5px;'><strong>Name:</strong> $joiner</p>
            <hr style='border: 1px dashed #555; margin: 15px 0;'>
            
            <table style='width: 100%; color: #fff;'>
                <tr>
                    <td style='padding: 5px 0; color: #888;'>Travel Date:</td>
                    <td style='padding: 5px 0; font-weight: bold; text-align: right;'>$date</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0; color: #888;'>Guests:</td>
                    <td style='padding: 5px 0; font-weight: bold; text-align: right;'>$pax Pax</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0; color: #888;'>Pickup:</td>
                    <td style='padding: 5px 0; font-weight: bold; text-align: right;'>$pickup</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0; color: #888;'>Total Paid:</td>
                    <td style='padding: 5px 0; font-weight: bold; text-align: right; color: #FFC107;'>$price</td>
                </tr>
            </table>

            <hr style='border: 1px dashed #555; margin: 15px 0;'>
            <p style='text-align: center; color: #2ecc71; font-weight: bold; font-size: 18px;'>STATUS: CONFIRMED</p>
            <p style='text-align: center; font-size: 12px; color: #888; margin-top: 20px;'>Please present this email to your guide.</p>
        </div>
    </div>
    ";

    // 3. PREPARE JSON PAYLOAD
    $data = [
        "sender" => [
            "name" => "MountPinoy Reservations",
            "email" => "derosasarmin@gmail.com"
        ],
        "to" => [
            [
                "email" => $recipientEmail,
                "name" => $recipientName
            ]
        ],
        "subject" => "Booking Confirmed: $tourName (#$refID)",
        "htmlContent" => $htmlContent
    ];

    // 4. SEND VIA CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'api-key: ' . $apiKey,
        'content-type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode; // Returns 201 if successful
}
?>