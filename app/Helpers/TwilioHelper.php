<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class TwilioHelper
{
    /**
     * Send an SMS via Twilio
     * Note: The actual Twilio keys will be provided by the client
     *
     * @param string $to
     * @param string $message
     * @return bool
     */
    public static function sendSMS(string $to, string $message): bool
    {
        try {
            // Format the phone number to international format if needed
            $formattedNumber = ValidationHelper::formatZimbabweanPhoneNumber($to);
            
            // This is a placeholder for the actual Twilio implementation
            // The client will provide the API keys later
            $accountSid = env('TWILIO_SID', 'PLACEHOLDER');
            $authToken = env('TWILIO_AUTH_TOKEN', 'PLACEHOLDER');
            $twilioNumber = env('TWILIO_NUMBER', 'PLACEHOLDER');
            
            if ($accountSid === 'PLACEHOLDER' || $authToken === 'PLACEHOLDER') {
                // Log for development/testing
                Log::info("SMS would be sent to: {$formattedNumber}");
                Log::info("Message: {$message}");
                return true;
            }
            
            // Uncomment when Twilio credentials are available
            /*
            $client = new \Twilio\Rest\Client($accountSid, $authToken);
            $client->messages->create(
                $formattedNumber,
                [
                    'from' => $twilioNumber,
                    'body' => $message
                ]
            );
            */
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send SMS: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send an account application confirmation SMS
     *
     * @param string $to
     * @param string $applicationNumber
     * @return bool
     */
    public static function sendAccountApplicationConfirmation(string $to, string $applicationNumber): bool
    {
        $message = "Thank you for applying for your ZB individual Account. We will inform you of your account number when its open, at which time you will then be able to apply for a credit facility after your salary has been deposited at least once.";
        return self::sendSMS($to, $message);
    }
    
    /**
     * Send a loan application confirmation SMS
     *
     * @param string $to
     * @param string $applicationNumber
     * @return bool
     */
    public static function sendLoanApplicationConfirmation(string $to, string $applicationNumber): bool
    {
        $message = "Thank you for your application. Your application number is {$applicationNumber}. You can use it to track the progress of your application.";
        return self::sendSMS($to, $message);
    }
}