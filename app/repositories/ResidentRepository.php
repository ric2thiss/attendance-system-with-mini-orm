<?php

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../query/QueryBuilder.php';

class ResidentRepository extends BaseRepository {
    protected function getModelClass(): string {
        return Resident::class;
    }

    /**
     * Get all residents not assigned as employees
     * 
     * @return array
     */
    public function getAllNotEmployee(): array {
        return Resident::query()
            ->select("*")
            ->whereRaw("resident_id NOT IN (SELECT resident_id FROM employees)")
            ->get();
    }

    /**
     * Get all residents with all related data
     * 
     * @param string|null $id
     * @return array|object|null Returns array of residents, or single resident object if ID provided, or null if not found
     */
    public function getAllWithRelations(?string $id = null) {
        $query = Resident::query()
            ->select(
                "residents.resident_id as main_resident_id",
                "residents.*",
                "occupations.*",
                "addresses.*",
                "family_relationships.*",
                "relatives.resident_id as relative_id",
                "relatives.first_name as relative_first_name",
                "relatives.last_name as relative_last_name",
                "family_relationships.relationship_type",
                "resident_biometrics.*",
                "resident_contacts.*",
                "resident_ids.*",
                "resident_status.*",
                "civil_status.*"
            )
            ->leftJoin("occupations", "occupations.resident_id", "=", "residents.resident_id")
            ->leftJoin("addresses", "addresses.resident_id", "=", "residents.resident_id")
            ->leftJoin("family_relationships", "family_relationships.resident_id", "=", "residents.resident_id")
            ->leftJoin("residents as relatives", "relatives.resident_id", "=", "family_relationships.relative_id")
            ->leftJoin("resident_biometrics", "resident_biometrics.resident_id", "=", "residents.resident_id")
            ->leftJoin("resident_contacts", "resident_contacts.resident_id", "=", "residents.resident_id")
            ->leftJoin("resident_ids", "resident_ids.resident_id", "=", "residents.resident_id")
            ->leftJoin("resident_status", "resident_status.resident_id", "=", "residents.resident_id")
            ->leftJoin("civil_status", "civil_status.civil_status_id", "=", "residents.civil_status_id");

        if ($id) {
            $query->where("residents.resident_id", $id);
        }

        $rows = $query->get();

        if (empty($rows)) {
            return $id ? null : [];
        }

        // Group results by resident
        $residents = [];
        foreach ($rows as $row) {
            $mainId = is_object($row) ? $row->main_resident_id : $row['main_resident_id'];

            if (!isset($residents[$mainId])) {
                $residents[$mainId] = [
                    'resident_id'        => $mainId,
                    'phil_sys_number'    => is_object($row) ? ($row->phil_sys_number ?? null) : ($row['phil_sys_number'] ?? null),
                    'first_name'         => is_object($row) ? ($row->first_name ?? null) : ($row['first_name'] ?? null),
                    'middle_name'        => is_object($row) ? ($row->middle_name ?? null) : ($row['middle_name'] ?? null),
                    'last_name'          => is_object($row) ? ($row->last_name ?? null) : ($row['last_name'] ?? null),
                    'gender'             => is_object($row) ? ($row->gender ?? null) : ($row['gender'] ?? null),
                    'birthdate'          => is_object($row) ? ($row->birthdate ?? null) : ($row['birthdate'] ?? null),
                    'civil_status_id'    => is_object($row) ? ($row->civil_status_id ?? null) : ($row['civil_status_id'] ?? null),
                    'civil_status'       => is_object($row) ? ($row->status_name ?? null) : ($row['status_name'] ?? null),
                    'blood_type'         => is_object($row) ? ($row->blood_type ?? null) : ($row['blood_type'] ?? null),
                    'contact_value'      => is_object($row) ? ($row->contact_value ?? null) : ($row['contact_value'] ?? null),
                    'job_title'          => is_object($row) ? ($row->job_title ?? null) : ($row['job_title'] ?? null),
                    'employer'           => is_object($row) ? ($row->employer ?? null) : ($row['employer'] ?? null),
                    'income_bracket'     => is_object($row) ? ($row->income_bracket ?? null) : ($row['income_bracket'] ?? null),
                    'is_owner'           => is_object($row) ? ($row->is_owner ?? null) : ($row['is_owner'] ?? null),
                    'months_of_residency'=> is_object($row) ? ($row->months_of_residency ?? null) : ($row['months_of_residency'] ?? null),
                    'address_type'       => is_object($row) ? ($row->address_type ?? null) : ($row['address_type'] ?? null),
                    'house_number'       => is_object($row) ? ($row->house_number ?? null) : ($row['house_number'] ?? null),
                    'building_name'      => is_object($row) ? ($row->building_name ?? null) : ($row['building_name'] ?? null),
                    'street_name'        => is_object($row) ? ($row->street_name ?? null) : ($row['street_name'] ?? null),
                    'subdivision_village'=> is_object($row) ? ($row->subdivision_village ?? null) : ($row['subdivision_village'] ?? null),
                    'purok'              => is_object($row) ? ($row->purok ?? null) : ($row['purok'] ?? null),
                    'sitio'              => is_object($row) ? ($row->sitio ?? null) : ($row['sitio'] ?? null),
                    'barangay'           => is_object($row) ? ($row->barangay ?? null) : ($row['barangay'] ?? null),
                    'district'           => is_object($row) ? ($row->district ?? null) : ($row['district'] ?? null),
                    'municipality_city'   => is_object($row) ? ($row->municipality_city ?? null) : ($row['municipality_city'] ?? null),
                    'province'           => is_object($row) ? ($row->province ?? null) : ($row['province'] ?? null),
                    'region'             => is_object($row) ? ($row->region ?? null) : ($row['region'] ?? null),
                    'postal_code'        => is_object($row) ? ($row->postal_code ?? null) : ($row['postal_code'] ?? null),
                    'id_type'            => is_object($row) ? ($row->id_type ?? null) : ($row['id_type'] ?? null),
                    'id_number'          => is_object($row) ? ($row->id_number ?? null) : ($row['id_number'] ?? null),
                    'issue_date'         => is_object($row) ? ($row->issue_date ?? null) : ($row['issue_date'] ?? null),
                    'expiry_date'        => is_object($row) ? ($row->expiry_date ?? null) : ($row['expiry_date'] ?? null),
                    'biometric_type'     => is_object($row) ? ($row->biometric_type ?? null) : ($row['biometric_type'] ?? null),
                    'is_active'          => is_object($row) ? ($row->is_active ?? null) : ($row['is_active'] ?? null),
                    'photo_path'         => is_object($row) ? ($row->photo_path ?? null) : ($row['photo_path'] ?? null),
                    'place_of_birth_city' => is_object($row) ? ($row->place_of_birth_city ?? null) : ($row['place_of_birth_city'] ?? null),
                    'place_of_birth_province' => is_object($row) ? ($row->place_of_birth_province ?? null) : ($row['place_of_birth_province'] ?? null),
                    'status_type'        => is_object($row) ? ($row->status_type ?? null) : ($row['status_type'] ?? null),
                    'suffix'             => is_object($row) ? ($row->suffix ?? null) : ($row['suffix'] ?? null),
                    'status_name'        => is_object($row) ? ($row->status_name ?? null) : ($row['status_name'] ?? null),
                    'relatives'          => [],
                ];
            }

            // Add relatives if available
            $relativeId = is_object($row) ? ($row->relative_id ?? null) : ($row['relative_id'] ?? null);
            if (!empty($relativeId)) {
                $residents[$mainId]['relatives'][] = [
                    'resident_id'        => $relativeId,
                    'first_name'         => is_object($row) ? ($row->relative_first_name ?? null) : ($row['relative_first_name'] ?? null),
                    'last_name'          => is_object($row) ? ($row->relative_last_name ?? null) : ($row['relative_last_name'] ?? null),
                    'relationship_type'  => is_object($row) ? ($row->relationship_type ?? null) : ($row['relationship_type'] ?? null),
                ];
            }
        }

        $result = array_values($residents);
        
        // If ID was provided, return single item or null
        if ($id) {
            return $result[0] ?? null;
        }

        return $result;
    }

    /**
     * Get paginated residents with search and filters
     * 
     * @param int $page
     * @param int $perPage
     * @param string $searchQuery
     * @param array $filters Optional filters: status_type, is_active
     * @return array
     */
    public function getPaginated(int $page, int $perPage, string $searchQuery = '', array $filters = []): array {
        $offset = ($page - 1) * $perPage;

        $countQuery = Resident::query()
            ->select("COUNT(DISTINCT residents.resident_id) as total")
            ->leftJoin("resident_status", "resident_status.resident_id", "=", "residents.resident_id");

        if (!empty($searchQuery)) {
            $countQuery->whereRaw("(CONCAT(residents.first_name, ' ', residents.last_name) LIKE ? OR residents.phil_sys_number LIKE ? OR residents.resident_id LIKE ?)", ["%{$searchQuery}%", "%{$searchQuery}%", "%{$searchQuery}%"]);
        }

        // Apply filters
        if (!empty($filters['status_type'])) {
            $countQuery->where("resident_status.status_type", $filters['status_type']);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $countQuery->where("resident_status.is_active", $filters['is_active']);
        }

        $totalCountQuery = $countQuery->first();
        $totalRecords = is_object($totalCountQuery) ? (int) $totalCountQuery->total : (int) ($totalCountQuery['total'] ?? 0);
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $perPage) : 1;

        $baseQuery = Resident::query()
            ->select(
                "residents.resident_id as main_resident_id",
                "residents.*",
                "occupations.*",
                "addresses.*",
                "family_relationships.*",
                "relatives.resident_id as relative_id",
                "relatives.first_name as relative_first_name",
                "relatives.last_name as relative_last_name",
                "family_relationships.relationship_type",
                "resident_biometrics.*",
                "resident_contacts.*",
                "resident_ids.*",
                "resident_status.*",
                "civil_status.*"
            )
            ->leftJoin("occupations", "occupations.resident_id", "=", "residents.resident_id")
            ->leftJoin("addresses", "addresses.resident_id", "=", "residents.resident_id")
            ->leftJoin("family_relationships", "family_relationships.resident_id", "=", "residents.resident_id")
            ->leftJoin("residents as relatives", "relatives.resident_id", "=", "family_relationships.relative_id")
            ->leftJoin("resident_biometrics", "resident_biometrics.resident_id", "=", "residents.resident_id")
            ->leftJoin("resident_contacts", "resident_contacts.resident_id", "=", "residents.resident_id")
            ->leftJoin("resident_ids", "resident_ids.resident_id", "=", "residents.resident_id")
            ->leftJoin("resident_status", "resident_status.resident_id", "=", "residents.resident_id")
            ->leftJoin("civil_status", "civil_status.civil_status_id", "=", "residents.civil_status_id");

        if (!empty($searchQuery)) {
            $baseQuery->whereRaw("(CONCAT(residents.first_name, ' ', residents.last_name) LIKE ? OR residents.phil_sys_number LIKE ? OR residents.resident_id LIKE ?)", ["%{$searchQuery}%", "%{$searchQuery}%", "%{$searchQuery}%"]);
        }

        // Apply filters
        if (!empty($filters['status_type'])) {
            $baseQuery->where("resident_status.status_type", $filters['status_type']);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $baseQuery->where("resident_status.is_active", $filters['is_active']);
        }

        $residents = $baseQuery
            ->groupBy("residents.resident_id")
            ->limit($perPage)
            ->offset($offset)
            ->get();

        // Process residents
        $processedResidents = [];
        foreach ($residents as $row) {
            if (is_object($row)) {
                $row = json_decode(json_encode($row), true);
            }

            $mainId = $row['main_resident_id'] ?? $row['resident_id'] ?? null;
            if (!$mainId) continue;

            if (!isset($processedResidents[$mainId])) {
                $processedResidents[$mainId] = [
                    'resident_id'        => $mainId,
                    'phil_sys_number'    => $row['phil_sys_number'] ?? null,
                    'first_name'         => $row['first_name'] ?? null,
                    'middle_name'        => $row['middle_name'] ?? null,
                    'last_name'          => $row['last_name'] ?? null,
                    'gender'             => $row['gender'] ?? null,
                    'birthdate'          => $row['birthdate'] ?? null,
                    'civil_status_id'    => $row['civil_status_id'] ?? null,
                    'civil_status'       => $row['status_name'] ?? null,
                    'blood_type'         => $row['blood_type'] ?? null,
                    'contact_value'      => $row['contact_value'] ?? null,
                    'job_title'          => $row['job_title'] ?? null,
                    'employer'           => $row['employer'] ?? null,
                    'income_bracket'     => $row['income_bracket'] ?? null,
                    'is_owner'           => $row['is_owner'] ?? null,
                    'months_of_residency'=> $row['months_of_residency'] ?? null,
                    'address_type'       => $row['address_type'] ?? null,
                    'house_number'       => $row['house_number'] ?? null,
                    'building_name'      => $row['building_name'] ?? null,
                    'street_name'        => $row['street_name'] ?? null,
                    'subdivision_village'=> $row['subdivision_village'] ?? null,
                    'purok'              => $row['purok'] ?? null,
                    'sitio'              => $row['sitio'] ?? null,
                    'barangay'           => $row['barangay'] ?? null,
                    'district'           => $row['district'] ?? null,
                    'municipality_city'  => $row['municipality_city'] ?? null,
                    'province'           => $row['province'] ?? null,
                    'region'             => $row['region'] ?? null,
                    'postal_code'        => $row['postal_code'] ?? null,
                    'id_type'            => $row['id_type'] ?? null,
                    'id_number'          => $row['id_number'] ?? null,
                    'issue_date'         => $row['issue_date'] ?? null,
                    'expiry_date'        => $row['expiry_date'] ?? null,
                    'biometric_type'     => $row['biometric_type'] ?? null,
                    'is_active'          => $row['is_active'] ?? null,
                    'status_type'        => $row['status_type'] ?? null,
                    'relatives'          => [],
                ];
            }

            if (!empty($row['relative_id'])) {
                $processedResidents[$mainId]['relatives'][] = [
                    'resident_id'        => $row['relative_id'],
                    'first_name'         => $row['relative_first_name'] ?? null,
                    'last_name'          => $row['relative_last_name'] ?? null,
                    'relationship_type'  => $row['relationship_type'] ?? null,
                ];
            }
        }

        return [
            "residents" => array_values($processedResidents),
            "pagination" => [
                "currentPage" => $page,
                "totalPages" => $totalPages,
                "totalRecords" => $totalRecords,
                "perPage" => $perPage,
                "startRecord" => $offset + 1,
                "endRecord" => min($offset + $perPage, $totalRecords),
            ],
            "searchQuery" => $searchQuery
        ];
    }

    /**
     * Check if resident exists by PhilSys number
     * 
     * @param string $philSysNumber
     * @param string|null $excludeId
     * @return bool
     */
    public function existsByPhilSysNumber(string $philSysNumber, ?string $excludeId = null): bool {
        $query = Resident::query()->where('phil_sys_number', $philSysNumber);
        
        if ($excludeId) {
            $query->whereRaw('resident_id != ?', [$excludeId]);
        }
        
        return $query->exists();
    }

    /**
     * Check if resident is an employee
     * 
     * @param string $residentId
     * @return bool
     */
    public function isEmployee(string $residentId): bool {
        $query = new QueryBuilder($this->pdo);
        $employee = $query->table("employees")
            ->where("resident_id", $residentId)
            ->first();
        
        return $employee !== null;
    }

    /**
     * Create resident with related data (address, status, biometrics)
     * 
     * @param array $data
     * @param array $addressData
     * @param array $statusData
     * @param array|null $biometricData
     * @return array
     */
    public function createWithRelations(array $data, array $addressData = [], array $statusData = [], ?array $biometricData = null): array {
        try {
            // Create resident
            $residentId = $this->create($data);

            if (!$residentId) {
                return [
                    "success" => false,
                    "message" => "Failed to register resident."
                ];
            }

            $query = new QueryBuilder($this->pdo);

            // Create address if provided
            if (!empty($addressData) && !empty($addressData['barangay'])) {
                $addressData['resident_id'] = $residentId;
                $addressCreated = $query->table("addresses")->insert($addressData);
                
                if (!$addressCreated) {
                    return [
                        "success" => false,
                        "message" => "Resident created but failed to create address."
                    ];
                }
            }

            // Create resident status if provided
            if (!empty($statusData) && !empty($statusData['status_type'])) {
                $statusData['resident_id'] = $residentId;
                $statusCreated = $query->table("resident_status")->insert($statusData);
                
                if (!$statusCreated) {
                    return [
                        "success" => false,
                        "message" => "Resident created but failed to create resident status."
                    ];
                }
            }

            // Create biometric record if provided
            if (!empty($biometricData) && !empty($biometricData['biometric_type']) && !empty($biometricData['file_path'])) {
                $biometricData['resident_id'] = $residentId;
                $biometricCreated = $query->table("resident_biometrics")->insert($biometricData);
                
                if (!$biometricCreated) {
                    return [
                        "success" => false,
                        "message" => "Resident created but failed to create biometric record."
                    ];
                }
            }

            return [
                "success" => true,
                "message" => "Resident registered successfully!",
                "resident_id" => $residentId
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Update resident with related data
     * 
     * @param string $residentId
     * @param array $data
     * @param array $addressData
     * @param array $statusData
     * @param array|null $biometricData
     * @return array
     */
    public function updateWithRelations(string $residentId, array $data, array $addressData = [], array $statusData = [], ?array $biometricData = null): array {
        try {
            $query = new QueryBuilder($this->pdo);

            // Update resident
            $updated = Resident::query()
                ->where('resident_id', $residentId)
                ->update($data);

            if ($updated === false) {
                return [
                    "success" => false,
                    "message" => "Failed to update resident."
                ];
            }

            // Update or create address if provided
            if (!empty($addressData) && !empty($addressData['barangay'])) {
                $existingAddress = $query->table("addresses")
                    ->where("resident_id", $residentId)
                    ->first();
                
                if ($existingAddress) {
                    $query->table("addresses")
                        ->where("resident_id", $residentId)
                        ->update($addressData);
                } else {
                    $addressData['resident_id'] = $residentId;
                    $query->table("addresses")->insert($addressData);
                }
            }

            // Update or create resident status if provided
            if (!empty($statusData) && !empty($statusData['status_type'])) {
                $existingStatus = $query->table("resident_status")
                    ->where("resident_id", $residentId)
                    ->first();
                
                if ($existingStatus) {
                    $query->table("resident_status")
                        ->where("resident_id", $residentId)
                        ->update($statusData);
                } else {
                    $statusData['resident_id'] = $residentId;
                    $query->table("resident_status")->insert($statusData);
                }
            }

            // Update or create biometric record if provided
            if (!empty($biometricData) && !empty($biometricData['biometric_type']) && !empty($biometricData['file_path'])) {
                $existingBiometric = $query->table("resident_biometrics")
                    ->where("resident_id", $residentId)
                    ->first();
                
                if ($existingBiometric) {
                    $query->table("resident_biometrics")
                        ->where("resident_id", $residentId)
                        ->update($biometricData);
                } else {
                    $biometricData['resident_id'] = $residentId;
                    $query->table("resident_biometrics")->insert($biometricData);
                }
            }

            return [
                "success" => true,
                "message" => "Resident updated successfully!",
                "resident_id" => $residentId
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Delete resident with all related records
     * 
     * @param string $residentId
     * @return array
     */
    public function deleteWithRelations(string $residentId): array {
        try {
            $query = new QueryBuilder($this->pdo);
            
            // Check if resident exists
            $resident = $query->table("residents")
                ->where("resident_id", $residentId)
                ->first();
            
            if (!$resident) {
                return [
                    "success" => false,
                    "message" => "Resident not found."
                ];
            }
            
            // Check if resident is an employee
            if ($this->isEmployee($residentId)) {
                return [
                    "success" => false,
                    "message" => "Cannot delete resident. This resident is currently an employee. Please remove them from the employee directory first."
                ];
            }
            
            // Delete related records
            $query->table("family_relationships")->where("resident_id", $residentId)->delete();
            $query->table("family_relationships")->where("relative_id", $residentId)->delete();
            $query->table("resident_status")->where("resident_id", $residentId)->delete();
            $query->table("resident_contacts")->where("resident_id", $residentId)->delete();
            $query->table("resident_ids")->where("resident_id", $residentId)->delete();
            $query->table("resident_biometrics")->where("resident_id", $residentId)->delete();
            $query->table("occupations")->where("resident_id", $residentId)->delete();
            $query->table("addresses")->where("resident_id", $residentId)->delete();

            // Delete profile picture if exists
            if (!empty($resident->photo_path) && file_exists(__DIR__ . "/../../" . $resident->photo_path)) {
                @unlink(__DIR__ . "/../../" . $resident->photo_path);
            }
            
            // Delete resident
            $deleted = $query->table("residents")->where("resident_id", $residentId)->delete();
            
            if ($deleted) {
                return [
                    "success" => true,
                    "message" => "Resident deleted successfully."
                ];
            } else {
                return [
                    "success" => false,
                    "message" => "Failed to delete resident."
                ];
            }
        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ];
        }
    }
}
