<?php

spl_autoload_register(
    function ($class) {
        include str_replace('\\', '/', $class) . '.php';
    }
);

const TOKEN = "this is a token";

// Convert seconds to datetime
function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

use classes\{HTTPRequester, Ticket, DB};

// Get ticket paging
$url = "URL";
$response = HTTPRequester::HTTPGet($url, array("access_token" => TOKEN, "per_page" => "50"));
$response = json_decode($response);
$pages = $response->meta->pagination->total_pages;
$ticket_count = $response->meta->pagination->total_count;

// Loop pages to get all tickets
$tickets = [];
for ($x = 1; $x <= $pages; $x++) {
    $response = HTTPRequester::HTTPGet($url, array("access_token" => TOKEN, "per_page" => "50", "page" => $x));
    $response = json_decode($response);
    $temp =  $response->tickets;
    foreach ($temp as $item) {
        $ticket_status = $item->closed_by == null ? "open" : "closed";
        $ticket_closed_by = $item->closed_by == null ? "open" : $item->closed_by;
        $ticket_resolution_time = $item->resolution_time == null ? "" : secondsToTime($item->resolution_time);
        $tickets[$item->number] = new Ticket($item->number, $item->title,
            implode(", ",$item->tags), $ticket_status, date("Y-m-d H:i:s", strtotime($item->created_at)), $ticket_resolution_time, $ticket_closed_by);
    }
}

// Save tickets to database
try {
    $db = DB::getInstance();

    // Clear table first
    $clear_table = "TRUNCATE TABLE `tickets`";
    $clear_table_statement = $db->prepare($clear_table);
    $clear_table_statement->execute();
    echo "Clear existing table." . PHP_EOL;

    $save_ticket_statement = $db->prepare("INSERT INTO `tickets`(`id`,`ticket_number`,`ticket_title`,`ticket_tags`,`ticket_status`,`ticket_created`,`ticket_resolve_time`,`ticket_assignee`) VALUES (?,?,?,?,?,?,?,?)");
    foreach ($tickets as $ticket) {
        $save_ticket_statement->execute([null, $ticket->id, $ticket->title, $ticket->tags, $ticket->status, $ticket->created_date, $ticket->resolution_time, $ticket->closed_by]);
        echo "Saving ticket {$ticket->id}." . PHP_EOL;
    }
} catch(PDOException $err) {
    die($err->getMessage());
}
$db = null;

?>
