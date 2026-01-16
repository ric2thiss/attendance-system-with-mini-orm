<?php

class AccountController {
    /**
     * Get paginated accounts with search and filters
     *
     * @param int $page Current page number
     * @param int $perPage Records per page
     * @param string $searchQuery Search term (optional)
     * @param array $filters Optional filters: role, is_active
     * @return array
     */
    public function getPaginatedAccounts($page = 1, $perPage = 10, $searchQuery = '', $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        
        $query = Admin::query();
        
        // Apply search
        if (!empty($searchQuery)) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('username', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('email', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('full_name', 'LIKE', "%{$searchQuery}%");
            });
        }
        
        // Apply filters
        if (isset($filters['role']) && !empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', intval($filters['is_active']));
        }
        
        // Get total count
        $totalRecords = count($query->get());
        
        // Get paginated results
        $accounts = $query->orderBy('created_at', 'DESC')
                         ->limit($perPage)
                         ->offset($offset)
                         ->get();
        
        $totalPages = ceil($totalRecords / $perPage);
        $startRecord = $totalRecords > 0 ? $offset + 1 : 0;
        $endRecord = min($offset + $perPage, $totalRecords);
        
        return [
            'accounts' => $accounts,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords,
                'perPage' => $perPage,
                'startRecord' => $startRecord,
                'endRecord' => $endRecord
            ],
            'searchQuery' => $searchQuery
        ];
    }

    /**
     * Get account by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getAccountById(int $id): ?array
    {
        return Admin::find($id);
    }

    /**
     * Create new account
     * 
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        // Validate input
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => "Username, email, and password are required"
            ];
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => "Invalid email format"
            ];
        }

        // Check if username already exists
        $existingUsername = Admin::query()
            ->where('username', $data['username'])
            ->first();
        
        if ($existingUsername) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => "Username already exists"
            ];
        }

        // Check if email already exists
        $existingEmail = Admin::query()
            ->where('email', $data['email'])
            ->first();
        
        if ($existingEmail) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => "Email already exists"
            ];
        }

        try {
            // Hash password
            $hashedPassword = Admin::hashPassword($data['password']);
            
            // Prepare account data
            $accountData = [
                'username' => trim($data['username']),
                'email' => trim($data['email']),
                'password' => $hashedPassword,
                'full_name' => !empty($data['full_name']) ? trim($data['full_name']) : null,
                'role' => !empty($data['role']) ? $data['role'] : 'staff',
                'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1
            ];

            $accountId = Admin::create($accountData);

            if ($accountId) {
                return [
                    "success" => true,
                    "status"  => 201,
                    "message" => "Account successfully created.",
                    "id" => $accountId
                ];
            }

            return [
                "success" => false,
                "status"  => 500,
                "error"   => "Failed to create account."
            ];

        } catch (PDOException $err) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => $this->getUserFriendlyErrorMessage($err)
            ];
        } catch (Exception $err) {
            return [
                "success" => false,
                "status"  => 500,
                "error"   => "An unexpected error occurred. Please try again or contact support if the problem persists."
            ];
        }
    }

    /**
     * Update account
     * 
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data)
    {
        // Check if account exists
        $existingAccount = Admin::find($id);
        if (!$existingAccount) {
            return [
                "success" => false,
                "status"  => 404,
                "error"   => "Account not found"
            ];
        }

        // Validate email if provided
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => "Invalid email format"
            ];
        }

        try {
            $updateData = [];

            // Update fields if provided
            if (isset($data['username'])) {
                // Check if username already exists (excluding current account)
                $existingUsername = Admin::query()
                    ->where('username', $data['username'])
                    ->where('id', '!=', $id)
                    ->first();
                
                if ($existingUsername) {
                    return [
                        "success" => false,
                        "status"  => 400,
                        "error"   => "Username already exists"
                    ];
                }
                $updateData['username'] = trim($data['username']);
            }

            if (isset($data['email'])) {
                // Check if email already exists (excluding current account)
                $existingEmail = Admin::query()
                    ->where('email', $data['email'])
                    ->where('id', '!=', $id)
                    ->first();
                
                if ($existingEmail) {
                    return [
                        "success" => false,
                        "status"  => 400,
                        "error"   => "Email already exists"
                    ];
                }
                $updateData['email'] = trim($data['email']);
            }

            if (isset($data['full_name'])) {
                $updateData['full_name'] = !empty($data['full_name']) ? trim($data['full_name']) : null;
            }

            if (isset($data['role'])) {
                $updateData['role'] = $data['role'];
            }

            if (isset($data['password']) && !empty($data['password'])) {
                $updateData['password'] = Admin::hashPassword($data['password']);
            }

            if (isset($data['is_active'])) {
                $updateData['is_active'] = intval($data['is_active']);
            }

            if (empty($updateData)) {
                return [
                    "success" => false,
                    "status"  => 400,
                    "error"   => "No valid fields to update"
                ];
            }

            $updated = Admin::query()
                ->where('id', $id)
                ->update($updateData);

            if ($updated) {
                return [
                    "success" => true,
                    "status"  => 200,
                    "message" => "Account successfully updated."
                ];
            }

            return [
                "success" => false,
                "status"  => 500,
                "error"   => "Failed to update account."
            ];

        } catch (PDOException $err) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => $this->getUserFriendlyErrorMessage($err)
            ];
        } catch (Exception $err) {
            return [
                "success" => false,
                "status"  => 500,
                "error"   => "An unexpected error occurred. Please try again or contact support if the problem persists."
            ];
        }
    }

    /**
     * Delete account
     * 
     * @param int $id
     * @return array
     */
    public function delete(int $id)
    {
        try {
            // Check if account exists
            $account = Admin::find($id);
            if (!$account) {
                return [
                    "success" => false,
                    "status"  => 404,
                    "error"   => "Account not found"
                ];
            }

            // Prevent deleting own account
            $currentUser = currentUser();
            if ($currentUser && isset($currentUser['id']) && $currentUser['id'] == $id) {
                return [
                    "success" => false,
                    "status"  => 400,
                    "error"   => "You cannot delete your own account"
                ];
            }

            // Delete account
            $deleted = Admin::query()
                ->where('id', $id)
                ->delete();

            if ($deleted) {
                return [
                    "success" => true,
                    "status"  => 200,
                    "message" => "Account successfully deleted."
                ];
            }

            return [
                "success" => false,
                "status"  => 500,
                "error"   => "Failed to delete account."
            ];

        } catch (PDOException $err) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => $this->getUserFriendlyErrorMessage($err)
            ];
        } catch (Exception $err) {
            return [
                "success" => false,
                "status"  => 500,
                "error"   => "An unexpected error occurred. Please try again or contact support if the problem persists."
            ];
        }
    }

    /**
     * Lock/Unlock account
     * 
     * @param int $id
     * @param bool $lock
     * @return array
     */
    public function toggleLock(int $id, bool $lock)
    {
        try {
            // Check if account exists
            $account = Admin::find($id);
            if (!$account) {
                return [
                    "success" => false,
                    "status"  => 404,
                    "error"   => "Account not found"
                ];
            }

            // Prevent locking own account
            $currentUser = currentUser();
            if ($currentUser && isset($currentUser['id']) && $currentUser['id'] == $id) {
                return [
                    "success" => false,
                    "status"  => 400,
                    "error"   => "You cannot lock your own account"
                ];
            }

            $isActive = $lock ? 0 : 1;
            $updated = Admin::query()
                ->where('id', $id)
                ->update(['is_active' => $isActive]);

            if ($updated) {
                return [
                    "success" => true,
                    "status"  => 200,
                    "message" => $lock ? "Account successfully locked." : "Account successfully unlocked."
                ];
            }

            return [
                "success" => false,
                "status"  => 500,
                "error"   => "Failed to update account status."
            ];

        } catch (PDOException $err) {
            return [
                "success" => false,
                "status"  => 400,
                "error"   => $this->getUserFriendlyErrorMessage($err)
            ];
        } catch (Exception $err) {
            return [
                "success" => false,
                "status"  => 500,
                "error"   => "An unexpected error occurred. Please try again or contact support if the problem persists."
            ];
        }
    }

    /**
     * Convert database errors to user-friendly messages
     * 
     * @param PDOException $exception
     * @return string
     */
    private function getUserFriendlyErrorMessage(PDOException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();
        
        // Handle duplicate entry errors (1062)
        if ($errorCode == 23000 || strpos($errorMessage, '1062') !== false) {
            if (preg_match("/Duplicate entry '([^']+)' for key 'username'/", $errorMessage)) {
                return "Username already exists. Please choose a different username.";
            }
            if (preg_match("/Duplicate entry '([^']+)' for key 'email'/", $errorMessage)) {
                return "Email already exists. Please use a different email address.";
            }
            return "This record already exists. Please check your input and try again.";
        }
        
        // Generic database error
        return "Unable to save the account information. Please check all fields and try again. If the problem persists, contact support.";
    }
}
