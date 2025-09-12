<?php
require './bootstrap.php';
$confirmToken = "860ecd7f5333586ea0d0e2209171c2ac";
$data = ["employee_id"=>2020,"status"=>"verified"];
VerificationToken::create([
                "employee_id" => $data["employee_id"],
                "status" => $data["status"],
                "token" => $confirmToken,
            ]);