<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'includes/db_connect.php';

// Initialize session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Fix path - adjust based on your directory structure
require_once '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

// Set timezone to EAT (Africa/Nairobi)
date_default_timezone_set('Africa/Nairobi');

// Initialize error and success messages
$error_message = '';
$success_message = '';

// Sender.net API configuration
define('SENDER_API_KEY', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYzI1ZjRmNWI0OTQ5YTQzZTRmNjFjNmEzYmZiMDYxMzE3NzVlNzAxMDU0NzQ3NGYyYTFkOWY0NWRkM2NlMDMyNDAxMGYxYWIzMDZiY2JlNTgiLCJpYXQiOiIxNzQ3MjA4Nzk2LjUxNzc1OCIsIm5iZiI6IjE3NDcyMDg3OTYuNTE3NzYwIiwiZXhwIjoiNDkwMDgwODc5Ni41MTY0MjkiLCJzdWIiOiI5NzY3NzYiLCJzY29wZXMiOltdfQ.f-AGxBpZwVsUuWSk4sThd5lrrG6-da-cSQ3549HUBPkq3O7_TXfQbwdTXMydVMrSgctWjgQiP5e6g3RWfsrA1w');
define('SENDER_API_URL', 'https://api.sender.net/v2/');

// Create a Guzzle client for consistent API calls
$client = new Client([
    'base_uri' => SENDER_API_URL,
    'headers' => [
        'Authorization' => 'Bearer ' . SENDER_API_KEY,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'User-Agent' => 'MUST-CU-EmailSender/2.3'
    ],
    'http_errors' => false,
    'timeout' => 30,
    'debug' => false
]);

// Enhanced logging function with detailed context
function logMessage($message, $details = null, $type = 'ERROR') {
    $log_file = 'email_errors.log';
    $timestamp = date('Y-m-d H:i:s T');
    $log_message = "[$timestamp] $type: $message";
    
    if ($details) {
        if (is_array($details) || is_object($details)) {
            $log_message .= " - Details: " . json_encode($details, JSON_PRETTY_PRINT);
        } else {
            $log_message .= " - Details: " . $details;
        }
    }
    
    error_log($log_message . PHP_EOL, 3, $log_file);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $recipient_type = $_POST['recipient_type'];
        $template = $_POST['template'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        $schedule_email = isset($_POST['schedule_email']) && $_POST['schedule_email'] === 'on';
        $schedule_datetime = $_POST['schedule_datetime'] ?? '';
        $send_scheduled_now = isset($_POST['send_scheduled_now']) && $_POST['send_scheduled_now'] === 'on';

        // Validate required fields
        if (empty($subject) || empty($message)) {
            throw new Exception("Subject and message are required");
        }

        // Validate schedule datetime if provided
        if ($schedule_email && !empty($schedule_datetime) && !$send_scheduled_now) {
            $schedule_time = strtotime($schedule_datetime);
            $current_time = time();
            if ($schedule_time === false) {
                throw new Exception("Invalid schedule date/time format");
            }
            if ($schedule_time <= $current_time + 300) { // 5 minutes buffer
                throw new Exception("Scheduled time must be at least 5 minutes in the future");
            }
        }

        // Verify API connectivity
        verifyApiConnection();
        
        // Fetch all available groups
        $groups = fetchGroups();
        
        if ($groups === false) {
            throw new Exception("Could not fetch recipient groups. Please check API connectivity.");
        }
        
        logMessage("Groups fetched successfully", ['count' => count($groups), 'groups' => $groups], 'INFO');
        
        // Map recipient type to appropriate groups
        $selected_groups = selectGroups($groups, $recipient_type);
        
        logMessage("Selected groups", ['recipient_type' => $recipient_type, 'selected_groups' => $selected_groups], 'INFO');
        
        if (empty($selected_groups)) {
            throw new Exception("No valid recipient groups found for type: " . $recipient_type);
        }

        // Format subject prefix based on template type
        $email_subject = formatEmailSubject($template, $subject);

        // Get email HTML content
        $emailContent = getEmailTemplate($template, $subject, $message);
        
        // Plain text alternative with unsubscribe link
        $plainTextContent = strip_tags(str_replace(
            ['<br>', '<p>', '</p>', '<div>', '</div>', '<h1>', '</h1>', '<h2>', '</h2>', '<h3>', '</h3>', '<h4>', '</h4>'], 
            ["\n", "\n", "\n\n", "", "", "\n\n", "\n\n", "\n\n", "\n\n", "\n\n", "\n\n", "\n\n"], 
            $message
        )) . "\n\nTo unsubscribe, click here: {{unsubscribe_link}}";

        // Process file attachment if present
        $attachment_data = processAttachment();

        // Create the campaign
        $campaign_result = createCampaign(
            $email_subject,
            $template,
            $emailContent,
            $plainTextContent,
            $selected_groups,
            $attachment_data,
            $schedule_email && !$send_scheduled_now ? $schedule_datetime : null
        );
        
        if ($campaign_result['success']) {
            $campaign_id = $campaign_result['campaign_id'];
            $recipient_count = countSubscribersInGroups($selected_groups, $recipient_type);
            $recipient_display = formatRecipientDisplay($recipient_type, $recipient_count);
            
            if ($schedule_email && !$send_scheduled_now) {
                $success_message = "Campaign successfully scheduled for $recipient_display on " . date('F j, Y, g:i a T', strtotime($schedule_datetime));
            } else {
                // Add delay before sending to prevent rate limiting
                sleep(2);
                $send_result = $schedule_email && $send_scheduled_now ? 
                    sendScheduledCampaign($campaign_id) : 
                    sendCampaignFixed($campaign_id);
                
                if (!$send_result['success']) {
                    logMessage("Campaign send failure details", [
                        'campaign_id' => $campaign_id,
                        'error' => $send_result['error'],
                        'status_code' => $send_result['status_code'],
                        'selected_groups' => $selected_groups,
                        'send_method' => $send_scheduled_now ? 'scheduled' : 'immediate'
                    ], 'ERROR');
                    throw new Exception("Campaign created but failed to send: " . $send_result['error']);
                }
                $success_message = "Campaign successfully created and sent to $recipient_display" . 
                    ($send_scheduled_now ? " (scheduled campaign sent immediately)" : "");
            }
            
            logMessage("Campaign " . ($schedule_email && !$send_scheduled_now ? "scheduled" : "sent") . " successfully", [
                'campaign_id' => $campaign_id,
                'subject' => $email_subject,
                'groups' => $selected_groups,
                'scheduled' => $schedule_email && !$send_scheduled_now ? $schedule_datetime : 'immediate',
                'send_scheduled_now' => $send_scheduled_now
            ], 'SUCCESS');
            
            $_SESSION['email_status'] = [
                'success' => true,
                'message' => $success_message,
                'campaign_id' => $campaign_id
            ];
            
            header("Location: send_email.php");
            exit();
        } else {
            throw new Exception("Failed to create campaign: " . $campaign_result['error']);
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logMessage($error_message, null, 'ERROR');
        
        $_SESSION['email_status'] = [
            'success' => false,
            'message' => $error_message
        ];
        
        header("Location: send_email.php");
        exit();
    }
}

/**
 * Verify API connection before attempting operations
 */
function verifyApiConnection() {
    global $client;
    
    try {
        $response = $client->get('me');
        $status_code = $response->getStatusCode();
        
        if ($status_code < 200 || $status_code >= 300) {
            $body = json_decode($response->getBody()->getContents(), true);
            $error_msg = isset($body['message']) ? $body['message'] : 'Unknown error';
            
            logMessage("API connection test failed", [
                'status_code' => $status_code,
                'error' => $error_msg
            ], 'ERROR');
            
            throw new Exception("API connection failed: $error_msg (Status: $status_code)");
        }
        
        return true;
    } catch (GuzzleException $e) {
        logMessage("API connection exception", [
            'message' => $e->getMessage()
        ], 'ERROR');
        
        throw new Exception("API connection failed: " . $e->getMessage());
    }
}

/**
 * Format email subject based on template type
 */
function formatEmailSubject($template, $subject) {
    switch ($template) {
        case 'announcement':
            return "📢🔊❗❗❗IMPORTANT ANNOUNCEMENT: " . $subject;
        case 'newsletter':
            return "📜📖MUST CU DAILY DEVOTION: " . $subject;
        case 'event':
            return "MUST CU EVENTS: " . $subject;
        default:
            return "MUST CU GENERAL REMINDER: " . $subject;
    }
}

/**
 * Format recipient display text
 */
function formatRecipientDisplay($recipient_type, $recipient_count) {
    switch($recipient_type) {
        case 'members':
            return "All Members ($recipient_count)";
        case 'leaders':
            return "All Leaders ($recipient_count)";
        case 'associates':
            return "All Associates ($recipient_count)";
        case 'all':
            return "All Recipients ($recipient_count)";
        default:
            return "Custom Group ($recipient_count)";
    }
}

/**
 * Process file attachment if present
 */
function processAttachment() {
    if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] != UPLOAD_ERR_OK) {
        return null;
    }
    
    $file_tmp = $_FILES['attachment']['tmp_name'];
    $file_name = $_FILES['attachment']['name'];
    $file_size = $_FILES['attachment']['size'];
    
    $max_size = 5 * 1024 * 1024;
    if ($file_size > $max_size) {
        throw new Exception("File size exceeds 5MB limit");
    }
    
    $allowed_types = [
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/png', 
        'image/jpeg'
    ];
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $file_type = $finfo->file($file_tmp);
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception("Invalid file type. Allowed types: PDF, DOC, DOCX, PNG, JPG");
    }
    
    return [
        'filename' => $file_name,
        'content' => base64_encode(file_get_contents($file_tmp)),
        'content_type' => $file_type
    ];
}

/**
 * Select appropriate groups based on recipient type
 */
function selectGroups($groups, $recipient_type) {
    $selected_groups = [];
    
    foreach ($groups as $group) {
        $group_title = strtolower($group['title']);
        
        switch ($recipient_type) {
            case 'members':
                if ($group_title === 'members' || $group_title === 'all members') {
                    return [$group['id']];
                }
                break;
            case 'leaders':
                if ($group_title === 'leaders' || $group_title === 'all leaders') {
                    return [$group['id']];
                }
                break;
            case 'associates':
                if ($group_title === 'associates' || $group_title === 'all associates') {
                    return [$group['id']];
                }
                break;
            case 'all':
                if ($group_title === 'all' || $group_title === 'all recipients') {
                    return [$group['id']];
                }
                break;
        }
    }
    
    foreach ($groups as $group) {
        $group_title = strtolower($group['title']);
        
        switch ($recipient_type) {
            case 'members':
                if (stripos($group_title, 'member') !== false && 
                    stripos($group_title, 'leader') === false) {
                    $selected_groups[] = $group['id'];
                }
                break;
            case 'leaders':
                if (stripos($group_title, 'leader') !== false) {
                    $selected_groups[] = $group['id'];
                }
                break;
            case 'associates':
                if (stripos($group_title, 'associate') !== false) {
                    $selected_groups[] = $group['id'];
                }
                break;
            case 'all':
                $selected_groups[] = $group['id'];
                break;
        }
    }
    
    if (!empty($selected_groups)) {
        return $selected_groups;
    }
    
    if ($recipient_type === 'all') {
        foreach ($groups as $group) {
            $selected_groups[] = $group['id'];
        }
    }
    
    return $selected_groups;
}

/**
 * Fetch all available groups from Sender.net API
 */
function fetchGroups() {
    global $client;
    
    try {
        $response = $client->get('groups');
        $status_code = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        
        if ($status_code >= 200 && $status_code < 300 && isset($data['data'])) {
            return $data['data'];
        } 
        
        logMessage("Failed to fetch groups from API", [
            'status_code' => $status_code,
            'response' => $data
        ], 'ERROR');
        
        $error_message = isset($data['message']) ? $data['message'] : 'Unknown error';
        
        if ($status_code == 401) {
            logMessage("API authorization failed - check your API key", null, 'ERROR');
            return false;
        }
        
        return false;
    } catch (GuzzleException $e) {
        logMessage("Exception when fetching groups: " . $e->getMessage(), null, 'ERROR');
        
        if ($e instanceof \GuzzleHttp\Exception\ConnectException) {
            logMessage("API connection failed - check internet connection and API endpoint", null, 'ERROR');
        }
        
        return false;
    }
}

/**
 * Count subscribers in specified groups
 */
function countSubscribersInGroups($group_ids, $recipient_type) {
    global $client;
    
    if (empty($group_ids)) {
        return 0;
    }
    
    try {
        $total_count = 0;
        
        foreach ($group_ids as $group_id) {
            $response = $client->get("groups/{$group_id}/subscribers/count");
            $status = $response->getStatusCode();
            
            if ($status >= 200 && $status < 300) {
                $data = json_decode($response->getBody()->getContents(), true);
                
                if (isset($data['data']['count'])) {
                    $total_count += intval($data['data']['count']);
                }
            }
        }
        
        if ($total_count > 0) {
            return $total_count;
        }
        
        $counts = [
            'members' => 465,
            'leaders' => 108,
            'associates' => 300,
            'all' => 573
        ];
        
        return $counts[$recipient_type] ?? 0;
    } catch (GuzzleException $e) {
        logMessage("Error counting subscribers: " . $e->getMessage(), null, 'ERROR');
        return 0;
    }
}

/**
 * Create a campaign and schedule it using dedicated endpoint
 */
function createCampaign($subject, $template_type, $html_content, $text_content, $group_ids, $attachment = null, $schedule_datetime = null) {
    global $client;
    
    try {
        $sender_email = defined('COMPANY_EMAIL') ? COMPANY_EMAIL : 'info@mustcu.or.ke';
        $sender_name = defined('COMPANY_NAME') ? COMPANY_NAME : 'MUST Christian Union';
        
        $campaign_title = 'MUST CU ' . ucfirst($template_type) . ' - ' . date('Y-m-d H:i:s');
        
        $payload = [
            'title' => $campaign_title,
            'subject' => $subject,
            'from' => $sender_name,
            'from_email' => $sender_email,
            'reply_to' => $sender_email,
            'preheader' => substr(strip_tags($subject), 0, 100),
            'content_type' => 'html',
            'google_analytics' => 1,
            'status' => 'draft' // Create as draft to allow scheduling
        ];
        
        $numeric_group_ids = array_map(function($id) {
            return is_numeric($id) ? (int)$id : $id;
        }, $group_ids);
        
        if (empty($numeric_group_ids)) {
            $payload['send_to_all'] = 1;
        } else {
            $payload['groups'] = $numeric_group_ids;
        }
        
        $payload['content'] = $html_content;
        
        if (!empty($text_content)) {
            $payload['text_content'] = $text_content;
        }
        
        logMessage("Creating campaign with payload", [
            'title' => $campaign_title,
            'subject' => $subject,
            'groups' => $numeric_group_ids,
            'has_html' => !empty($html_content),
            'has_text' => !empty($text_content)
        ], 'INFO');
        
        $response = $client->post('campaigns', [
            'json' => $payload
        ]);
        
        $status_code = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        
        $result = [
            'success' => false,
            'error' => '',
            'status_code' => $status_code,
            'campaign_id' => null
        ];
        
        if ($status_code >= 200 && $status_code < 300 && isset($data['data']['id'])) {
            $result['success'] = true;
            $result['campaign_id'] = $data['data']['id'];
            
            // Handle attachments
            if (!empty($attachment)) {
                $attach_result = attachFileToCampaign($result['campaign_id'], $attachment);
                if (!$attach_result['success']) {
                    logMessage("Attachment failed but campaign created", [
                        'campaign_id' => $result['campaign_id']
                    ], 'WARNING');
                }
            }
            
            // Schedule the campaign if requested
            if ($schedule_datetime) {
                $schedule_response = $client->post("campaigns/{$result['campaign_id']}/schedule", [
                    'json' => [
                        'schedule_time' => date('c', strtotime($schedule_datetime)) // ISO 8601, e.g., 2025-05-23T09:00:00+03:00
                    ]
                ]);
                
                $schedule_status = $schedule_response->getStatusCode();
                $schedule_body = $schedule_response->getBody()->getContents();
                $schedule_data = json_decode($schedule_body, true);
                
                logMessage("Scheduling campaign response", [
                    'campaign_id' => $result['campaign_id'],
                    'status_code' => $schedule_status,
                    'response' => $schedule_data,
                    'schedule_time' => date('c', strtotime($schedule_datetime))
                ], 'INFO');
                
                if ($schedule_status < 200 || $schedule_status >= 300) {
                    $error_msg = "Failed to schedule campaign: " . ($schedule_data['message'] ?? 'Unknown error');
                    logMessage($error_msg, ['response' => $schedule_data], 'ERROR');
                    $result['success'] = false;
                    $result['error'] = $error_msg;
                    return $result;
                }
            }
            
            return $result;
        } else {
            $error_msg = 'Campaign creation API Error: ' . $status_code;
            if (isset($data['message'])) {
                $error_msg .= ' - ' . $data['message'];
            }
            $result['error'] = $error_msg;
            logMessage($error_msg, ['response' => $data], 'ERROR');
            return $result;
        }
    } catch (GuzzleException $e) {
        logMessage("Exception when creating campaign: " . $e->getMessage(), null, 'ERROR');
        
        return [
            'success' => false,
            'error' => 'API Error: ' . $e->getMessage(),
            'campaign_id' => null
        ];
    }
}

/**
 * Attach a file to an existing campaign
 */
function attachFileToCampaign($campaign_id, $attachment) {
    global $client;
    
    try {
        $payload = [
            'filename' => $attachment['filename'],
            'content' => $attachment['content'],
            'content_type' => $attachment['content_type']
        ];
        
        $response = $client->post("campaigns/{$campaign_id}/attachments", [
            'json' => $payload
        ]);
        
        $status_code = $response->getStatusCode();
        
        $result = ['success' => ($status_code >= 200 && $status_code < 300)];
        
        if (!$result['success']) {
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            logMessage("Failed to attach file to campaign", [
                'campaign_id' => $campaign_id,
                'status_code' => $status_code,
                'response' => $data
            ], 'ERROR');
        }
        
        return $result;
    } catch (GuzzleException $e) {
        logMessage("Exception when attaching file: " . $e->getMessage(), null, 'ERROR');
        
        return ['success' => false];
    }
}

/**
 * Send a campaign (for immediate sends)
 */
function sendCampaignFixed($campaign_id) {
    global $client;
    
    try {
        logMessage("Attempting to send campaign", ['campaign_id' => $campaign_id], 'INFO');
        
        $checkResponse = $client->get("campaigns/{$campaign_id}");
        $checkStatus = $checkResponse->getStatusCode();
        
        if ($checkStatus !== 200) {
            logMessage("Campaign check failed before sending", [
                'campaign_id' => $campaign_id,
                'status_code' => $checkStatus
            ], 'ERROR');
            
            return [
                'success' => false,
                'error' => "Campaign validation failed with status: {$checkStatus}",
                'status_code' => $checkStatus
            ];
        }
        
        $checkBody = json_decode($checkResponse->getBody()->getContents(), true);
        $currentStatus = isset($checkBody['data']['status']) ? $checkBody['data']['status'] : 'unknown';
        
        logMessage("Campaign status before sending", [
            'campaign_id' => $campaign_id,
            'status' => $currentStatus
        ], 'INFO');
        
        if ($currentStatus === 'sending' || $currentStatus === 'sent' || $currentStatus === 'scheduled') {
            return [
                'success' => true,
                'error' => '',
                'status_code' => 200
            ];
        }
        
        $sendResponse = $client->post("campaigns/{$campaign_id}/actions/send", [
            'headers' => [
                'Cache-Control' => 'no-cache'
            ]
        ]);
        
        $sendStatus = $sendResponse->getStatusCode();
        $sendBody = json_decode($sendResponse->getBody()->getContents(), true);
        
        if ($sendStatus >= 200 && $sendStatus < 300) {
            logMessage("Campaign send successful", [
                'campaign_id' => $campaign_id,
                'status_code' => $sendStatus
            ], 'SUCCESS');
            
            return [
                'success' => true,
                'error' => '',
                'status_code' => $sendStatus
            ];
        }
        
        logMessage("Primary send method failed, trying PATCH method", [
            'campaign_id' => $campaign_id,
            'status_code' => $sendStatus,
            'response' => $sendBody
        ], 'WARNING');
        
        $patchResponse = $client->patch("campaigns/{$campaign_id}", [
            'json' => [
                'status' => 'sending'
            ],
            'headers' => [
                'Cache-Control' => 'no-cache'
            ]
        ]);
        
        $patchStatus = $patchResponse->getStatusCode();
        
        if ($patchStatus >= 200 && $patchStatus < 300) {
            logMessage("Campaign send successful using PATCH method", [
                'campaign_id' => $campaign_id,
                'status_code' => $patchStatus
            ], 'SUCCESS');
            
            return [
                'success' => true,
                'error' => '',
                'status_code' => $patchStatus
            ];
        }
        
        logMessage("All sending methods failed", [
            'campaign_id' => $campaign_id,
            'primary_status' => $sendStatus,
            'patch_status' => $patchStatus
        ], 'ERROR');
        
        return [
            'success' => false,
            'error' => "Failed to send campaign after multiple attempts. Please contact support.",
            'status_code' => $sendStatus
        ];
        
    } catch (GuzzleException $e) {
        $errorMessage = "Exception when sending campaign: " . $e->getMessage();
        logMessage($errorMessage, null, 'ERROR');
        
        return [
            'success' => false,
            'error' => 'API Error: ' . $e->getMessage(),
            'status_code' => ($e instanceof RequestException && $e->hasResponse()) ? 
                             $e->getResponse()->getStatusCode() : 0
        ];
    }
}

/**
 * Send a scheduled campaign immediately
 */
function sendScheduledCampaign($campaign_id) {
    global $client;
    
    try {
        logMessage("Attempting to send scheduled campaign immediately", ['campaign_id' => $campaign_id], 'INFO');
        
        // Check campaign status
        $checkResponse = $client->get("campaigns/{$campaign_id}");
        $checkStatus = $checkResponse->getStatusCode();
        
        if ($checkStatus !== 200) {
            logMessage("Scheduled campaign check failed", [
                'campaign_id' => $campaign_id,
                'status_code' => $checkStatus
            ], 'ERROR');
            
            return [
                'success' => false,
                'error' => "Scheduled campaign validation failed with status: {$checkStatus}",
                'status_code' => $checkStatus
            ];
        }
        
        $checkBody = json_decode($checkResponse->getBody()->getContents(), true);
        $currentStatus = isset($checkBody['data']['status']) ? $checkBody['data']['status'] : 'unknown';
        
        logMessage("Scheduled campaign status", [
            'campaign_id' => $campaign_id,
            'status' => $currentStatus
        ], 'INFO');
        
        if ($currentStatus === 'sending' || $currentStatus === 'sent') {
            return [
                'success' => true,
                'error' => '',
                'status_code' => 200
            ];
        }
        
        // If campaign is scheduled, update to remove schedule
        if ($currentStatus === 'scheduled') {
            $updateResponse = $client->patch("campaigns/{$campaign_id}", [
                'json' => [
                    'schedule_time' => null,
                    'status' => 'draft'
                ],
                'headers' => [
                    'Cache-Control' => 'no-cache'
                ]
            ]);
            
            $updateStatus = $updateResponse->getStatusCode();
            
            if ($updateStatus < 200 || $updateStatus >= 300) {
                $updateBody = json_decode($updateResponse->getBody()->getContents(), true);
                logMessage("Failed to update scheduled campaign to draft", [
                    'campaign_id' => $campaign_id,
                    'status_code' => $updateStatus,
                    'response' => $updateBody
                ], 'ERROR');
                
                return [
                    'success' => false,
                    'error' => "Failed to update scheduled campaign status: Status {$updateStatus}",
                    'status_code' => $updateStatus
                ];
            }
        }
        
        // Send the campaign
        $sendResponse = $client->post("campaigns/{$campaign_id}/actions/send", [
            'headers' => [
                'Cache-Control' => 'no-cache'
            ]
        ]);
        
        $sendStatus = $sendResponse->getStatusCode();
        $sendBody = json_decode($sendResponse->getBody()->getContents(), true);
        
        if ($sendStatus >= 200 && $sendStatus < 300) {
            logMessage("Scheduled campaign sent successfully", [
                'campaign_id' => $campaign_id,
                'status_code' => $sendStatus
            ], 'SUCCESS');
            
            return [
                'success' => true,
                'error' => '',
                'status_code' => $sendStatus
            ];
        }
        
        logMessage("Scheduled campaign send failed", [
            'campaign_id' => $campaign_id,
            'status_code' => $sendStatus,
            'response' => $sendBody
        ], 'ERROR');
        
        return [
            'success' => false,
            'error' => "Failed to send scheduled campaign: Status {$sendStatus}",
            'status_code' => $sendStatus
        ];
        
    } catch (GuzzleException $e) {
        $errorMessage = "Exception when sending scheduled campaign: " . $e->getMessage();
        logMessage($errorMessage, null, 'ERROR');
        
        return [
            'success' => false,
            'error' => 'API Error: ' . $e->getMessage(),
            'status_code' => ($e instanceof RequestException && $e->hasResponse()) ? 
                             $e->getResponse()->getStatusCode() : 0
        ];
    }
}

/**
 * Generate HTML email content based on template
 */
function getEmailTemplate($template, $subject, $message, $recipientName = '') {
    $greeting = !empty($recipientName) ? "Dear $recipientName," : "Greetings,";
    $site_url = defined('SITE_URL') ? SITE_URL : 'https://mustcu.or.ke';
    $current_year = date('Y');
    
    switch ($template) {
        case 'default':
            return '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>' . htmlspecialchars($subject) . '</title>
                </head>
                <body style="background-color: #f8f9fa; padding: 20px; font-family: Arial, sans-serif; margin: 0;">
                    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                        <div style="background-color: #0207BA; padding: 20px; text-align: center;">
                            <h2 style="color: white; margin: 0;">MUST Christian Union</h2>
                        </div>
                        <div style="padding: 30px;">
                            <p style="color: #333; margin-top: 0;">' . $greeting . '</p>
                            <h3 style="color: #333; margin-top: 0;">' . htmlspecialchars($subject) . '</h3>
                            <div style="color: #666; line-height: 1.5;">' . nl2br(htmlspecialchars($message)) . '</div>
                            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666;">
                                <p>Best regards,<br>MUST Christian Union Team</p>
                            </div>
                        </div>
                        <div style="background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #666;">
                            <p>© ' . $current_year . ' MUST Christian Union. All rights reserved.</p>
                            <p>Meru University of Science and Technology</p>
                            <p>
                                <a href="{{unsubscribe_link}}">{{unsubscribe_text}}</a> | 
                                <a href="https://mustcu.or.ke/privacy" style="color: #0207BA; text-decoration: none; margin: 0 5px;">Privacy Policy</a> | 
                                <a href="https://mustcu.or.ke/newsletter" style="color: #0207BA; text-decoration: none; margin: 0 5px;">View Online</a>
                            </p>
                        </div>
                    </div>
                </body>
                </html>
            ';
        case 'announcement':
            return '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>' . htmlspecialchars($subject) . '</title>
                </head>
                <body style="background-color: #f8f9fa; padding: 20px; font-family: Arial, sans-serif; margin: 0;">
                    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                        <div style="background-color: #0207BA; padding: 30px; text-align: center;">
                            <h1 style="color: white; margin: 0; text-transform: uppercase; letter-spacing: 2px;">Important Announcement</h1>
                        </div>
                        <div style="background-color: #333; padding: 15px; text-align: center;">
                            <h3 style="color: white; margin: 0; font-size: 18px;">' . htmlspecialchars($subject) . '</h3>
                        </div>
                        <div style="padding: 30px;">
                            <h2 style="color: #000000; margin-top: 0;">' . $greeting . '</h2>
                            <div style="border-left: 4px solid #FF7900; padding-left: 15px; margin-bottom: 20px;">
                                <p style="font-weight: bold; color: #333; font-size: 16px;">Kindly go through the announcement below</p>
                            </div>
                            <div style="color: #555; line-height: 1.6;">' . nl2br(htmlspecialchars($message)) . '</div>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="https://mustcu.or.ke/announcements" style="background-color: #FF7900; color: white; text-decoration: none; padding: 12px 30px; border-radius: 50px; font-weight: bold; display: inline-block;">Read More</a>
                            </div>
                            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666;">
                                <p>Best regards,<br>MUST Christian Union Media Team</p>
                            </div>
                        </div>
                        <div style="background-color: #333; padding: 15px; text-align: center; color: white;">
                            <p style="margin: 0;">© ' . $current_year . ' MUST Christian Union. All rights reserved.</p>
                            <p style="font-weight: bold; color: #ff7900; font-size: 10px;">You are receiving this email because you are a registered member of MUST CU</p>
                            <p>
                                <a href="{{unsubscribe_link}}">{{unsubscribe_text}}</a> | 
                                <a href="https://mustcu.or.ke/privacy" style="color: #FF7900; text-decoration: none; margin: 0 5px;">Privacy Policy</a>
                            </p>
                        </div>
                    </div>
                </body>
                </html>
            ';
        case 'newsletter':
            return '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>' . htmlspecialchars($subject) . '</title>
                </head>
                <body style="background-color: #f8f9fa; padding: 20px; font-family: Arial, sans-serif; margin: 0;">
                    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                        <div style="background-color:#0207ba; padding: 20px; text-align: center;">
                            <h2 style="color: #ffffff; margin: 0;">' . htmlspecialchars($subject) . '</h2>
                            <h3 style="color:#fff000; margin: 5px 0 0;">Daily Devotional Verses - ' . date('F Y') . '</h3>
                        </div>
                        <div style="padding: 30px;">
                            <h3 style="color: #333; margin-top: 0;">' . $greeting . '</h3>
                            <h3 style="color:rgb(8, 8, 8); border-bottom: 2px solid #0207ba; padding-bottom: 10px;">Today\'s Daily Devotion Highlights</h3>
                            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                                <h4 style="color:rgb(8, 8, 8); margin-top: 0;">God\'s Word For Us Today</h4>
                                <div style="color:rgb(0, 0, 0); line-height: 1.6;">' . nl2br(htmlspecialchars($message)) . '</div>
                            </div>
                            <div style="text-align: center; margin: 30px 0 20px;">
                                <a href="https://mustcu.or.ke/index.php" style="background-color: #0207ba; color: white; text-decoration: none; padding: 12px 30px; border-radius: 4px; font-weight: bold; display: inline-block;">Read More</a>
                            </div>
                            <div style="margin-top: 30px; text-align: center; padding-top: 20px; border-top: 1px solid #eee; color: #000000;">
                                <p>Best regards,<br>MUST Christian Union Team</p>
                            </div>
                        </div>
                        <div style="background-color: #0207ba; padding: 20px; text-align: center; color: white;">
                            <p>© ' . $current_year . ' MUST Christian Union. All rights reserved.</p>
                            <p>
                                <a href="{{unsubscribe_link}}">{{unsubscribe_text}}</a> | 
                                <a href="https://mustcu.or.ke/newsletter" style="color: #ff7900; text-decoration: none; margin: 0 5px;">View Online</a> | 
                                <a href="https://mustcu.or.ke/archive" style="color: #ff7900; text-decoration: none; margin: 0 5px;">Archive</a>
                            </p>
                        </div>
                    </div>
                </body>
                </html>
            ';
        case 'event': 
            return '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>' . htmlspecialchars($subject) . '</title>
                </head>
                <body style="background-color: #f8f9fa; padding: 20px; font-family: Arial, sans-serif; margin: 0;">
                    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                        <div style="height: 200px; background-color: #0207ba; position: relative;">
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                <h1 style="color: white; margin: 0; font-size: 28px; text-shadow: 1px 1px 3px rgba(0,0,0,0.6);">You\'re Invited!</h1>
                                <h1 style="color: white; margin: 10px 0 0; text-shadow: 1px 1px 3px rgba(0,0,0,0.6);">' . htmlspecialchars($recipientName) . '</h1>
                            </div>
                        </div>
                        <div style="padding: 30px;">
                            <h2 style="color: #333; margin-top: 0;">' . $greeting . '</h2>
                            <div style="text-align: center; margin-bottom: 30px;">
                                <h2 style="color: #333; margin: 0 0 15px;">Join Us For Our Special Event</h2>
                                <div style="color: #666; margin: 0;">' . nl2br(htmlspecialchars($message)) . '</div>
                            </div>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="https://mustcu.or.ke/events/rsvp" style="background-color: #0207ba; color: white; text-decoration: none; padding: 15px 40px; border-radius: 50px; font-weight: bold; display: inline-block; font-size: 16px;">RSVP Now</a>
                            </div>
                            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666;">
                                <p>Best regards,<br>MUST Christian Union Team</p>
                            </div>
                        </div>
                        <div style="background-color: #333; padding: 20px; text-align: center; color: white;">
                            <p style="margin: 0;">© ' . $current_year . ' MUST Christian Union. All rights reserved.</p>
                            <p>
                                <a href="{{unsubscribe_link}}">{{unsubscribe_text}}</a> | 
                                <a href="https://mustcu.or.ke/privacy" style="color: #0207ba; text-decoration: none; margin: 0 5px;">Privacy Policy</a>
                            </p>
                        </div>
                    </div>
                </body>
                </html>
            ';
        default:
            return '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>' . htmlspecialchars($subject) . '</title>
                </head>
                <body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2>' . htmlspecialchars($subject) . '</h2>
                    <p>' . $greeting . '</p>
                    <div>' . nl2br(htmlspecialchars($message)) . '</div>
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666;">
                        <p>Best regards,<br>MUST Christian Union Team</p>
                    </div>
                    <p style="color: #777; font-size: 12px; text-align: center; margin-top: 20px;">
                        © ' . $current_year . ' MUST Christian Union. All rights reserved.<br>
                        <a href="{{unsubscribe_link}}">{{unsubscribe_text}}</a>
                    </p>
                </body>
                </html>
            ';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUST CU Email Campaign Manager</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container1 {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .header {
            background-color: #0207ba;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        select, input[type="text"], textarea, input[type="datetime-local"], input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn1 {
            background-color: #0207ba;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            display: inline-block;
        }
        .btn1:hover {
            background-color: #ff7900;
        }
        .error-message, .success-message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .error-message {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .success-message {
            background-color: #dcfce7;
            color: #15803d;
        }
        .attachment-note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
 <?php include 'includes/header.php'; ?>
    <div class="container1">
        <div class="header">
            <h1>MUST Christian Union Email Campaign Manager</h1>
        </div>

        <?php if (isset($_SESSION['email_status'])): ?>
            <div class="<?php echo $_SESSION['email_status']['success'] ? 'success-message' : 'error-message'; ?>">
                <?php echo htmlspecialchars($_SESSION['email_status']['message']); ?>
            </div>
            <?php unset($_SESSION['email_status']); ?>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="recipient_type">Recipient Type</label>
                <select name="recipient_type" id="recipient_type" required>
                    <option value="">Select Recipient Type</option>
                    <option value="all">All Recipients</option>
                    <option value="members">Members Only</option>
                    <option value="leaders">Leaders Only</option>
                    <option value="associates">Associates Only</option>
                </select>
            </div>

            <div class="form-group">
                <label for="template">Email Template</label>
                <select name="template" id="template" required>
                    <option value="">Select Template</option>
                    <option value="default">Default</option>
                    <option value="announcement">Announcement</option>
                    <option value="newsletter">Newsletter</option>
                    <option value="event">Event</option>
                </select>
            </div>

            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" name="subject" id="subject" required placeholder="Enter email subject">
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea name="message" id="message" required placeholder="Enter your message here"></textarea>
            </div>

            <div class="form-group">
                <label for="attachment">Attachment (Optional)</label>
                <input type="file" name="attachment" id="attachment">
                <p class="attachment-note">Supported formats: PDF, DOC, DOCX, PNG, JPG (Max 5MB)</p>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="schedule_email" id="schedule_email">
                    <label for="schedule_email">Schedule Email</label>
                </div>
            </div>

            <div class="form-group" id="schedule_datetime_group" style="display: none;">
                <label for="schedule_datetime">Schedule Date & Time</label>
                <input type="datetime-local" name="schedule_datetime" id="schedule_datetime">
            </div>

            <div class="form-group" id="send_scheduled_now_group" style="display: none;">
                <div class="checkbox-group">
                    <input type="checkbox" name="send_scheduled_now" id="send_scheduled_now">
                    <label for="send_scheduled_now">Send Scheduled Email Immediately</label>
                </div>
            </div>

            <button type="submit" class="btn1">Send Email</button>
        </form>
    </div>

    <script>
        const scheduleCheckbox = document.getElementById('schedule_email');
        const scheduleDatetimeGroup = document.getElementById('schedule_datetime_group');
        const sendScheduledNowGroup = document.getElementById('send_scheduled_now_group');

        scheduleCheckbox.addEventListener('change', function() {
            scheduleDatetimeGroup.style.display = this.checked ? 'block' : 'none';
            sendScheduledNowGroup.style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>