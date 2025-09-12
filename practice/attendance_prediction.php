<?php
$data = ["employee_id" => 101];
$options = [
    "http" => [
        "header"  => "Content-type: application/json",
        "method"  => "POST",
        "content" => json_encode($data),
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents("http://127.0.0.1:5000/predict", false, $context);
echo $result; // {"risk_of_absence":1}
