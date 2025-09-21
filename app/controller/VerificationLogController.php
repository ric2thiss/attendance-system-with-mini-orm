<?php

class VerificationLogController
{

    public function store(array $data)
    {
        // Save log in verification_logs
        VerificationLog::create([
            "employee_id" => $data["employee_id"],
            "status"      => $data["status"],
            "device_id"   => $data["device_id"] ?? "KIOSK-01",
            "ip_address"  => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        // Generate one-time confirmation token
        $confirmToken = bin2hex(random_bytes(16));

        // Save token to DB with current timestamp
        VerificationToken::create([
            "employee_id" => $data["employee_id"],
            "status"      => $data["status"],
            "token"       => $confirmToken,
            "created_at"  => date("Y-m-d H:i:s"),
        ]);

        return $confirmToken;

    }

    public function confirm(string $token)
    {
        // Fetch token from DB
        $record = VerificationToken::query()
            ->where("token", $token)
            ->first();

        if (!$record) {
            return "<h2>❌ Invalid or expired token.</h2>";
        }

        // Calculate token age
        $createdAt = new DateTime($record->created_at);
        $now       = new DateTime();
        $diff      = $now->getTimestamp() - $createdAt->getTimestamp();

        // Check if token expired (older than 1 minute)
        if ($diff > 60) {
            return "<h2>❌ Token expired.</h2>";
        }

        // Token is valid, return info
        $employeeId = htmlspecialchars($record->employee_id);
        $status     = htmlspecialchars($record->status);

        return "<h2>✅ Verification Successful</h2>
                <p>Employee ID: {$employeeId}</p>
                <p>Status: {$status}</p>
                <p>Token: {$token}</p>";
    }



}
