<?php

# Retrieve Incident Details:
# https://developer.pagerduty.com/docs/tools-libraries/retrieve-incident-details/

require_once ("ZabbixApi.php");

$zbxUrl   = 'http://127.0.0.1/';
$zbxUser  = 'admin';
$zbxPass  = '';
$pd_api_token    = '';
$pd_webhook_auth = '';

function get_event_id($id, $ch)
{
    $url = 'https://api.pagerduty.com/log_entries/' . $id . '?include[]=channels';
    curl_setopt($ch, CURLOPT_URL, "$url");
    $json = json_decode(curl_exec($ch) , true);
    return $json['log_entry']['channel']['details']['event_id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' and $_SERVER['HTTP_X_AUTH_KEY'] == $pd_webhook_auth)
{
    $json = file_get_contents('php://input');
    $obj = json_decode($json, true);
    $zbx = new ZabbixApi();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Token token=' . $pd_api_token,
        'Accept: application/vnd.pagerduty+json;version=2'
    ));
    foreach ($obj['messages'] as $msg)
    {
        if ($msg['event'] == 'incident.acknowledge')
        {
            $message = $msg['log_entries'][0]['summary'];
            $id = $msg['incident']['first_trigger_log_entry']['id'];
            $event_id = get_event_id($id, $ch);
            $zbx->login($zbxUrl, $zbxUser, $zbxPass);
            $zbx->call('event.acknowledge', array(
                "eventids" => $event_id,
                "action" => 6,
                "message" => $message
            ));
        }
    }
    curl_close($ch);
}
