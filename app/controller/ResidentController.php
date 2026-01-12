<?php

class ResidentController
{
    protected $residentRepository;

    public function __construct() {
        $db = (new Database())->connect();
        $this->residentRepository = new ResidentRepository($db);
    }

    public function getAllResidentNotEmployee()
    {
        return $this->residentRepository->getAllNotEmployee();
    }

    public function getAllResidents($id = null)
    {
        return $this->residentRepository->getAllWithRelations($id);
    }

    /**
     * Get paginated residents with search and filters
     *
     * @param int $page Current page number
     * @param int $perPage Records per page
     * @param string $searchQuery Search term (optional)
     * @param array $filters Optional filters: status_type, is_active
     * @return array
     */
    public function getPaginatedResidents($page = 1, $perPage = 10, $searchQuery = '', $filters = [])
    {
        return $this->residentRepository->getPaginated($page, $perPage, $searchQuery, $filters);
    }

    /**
     * Store a new resident with address, status, and biometrics
     *
     * @param array $data Resident data
     * @param array $addressData Address data (optional)
     * @param array $statusData Status data (optional)
     * @param array $biometricData Biometric data (optional)
     * @return array
     */
    public function store($data, $addressData = [], $statusData = [], $biometricData = null)
    {
        // Validate required fields
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['gender']) || empty($data['birthdate'])) {
            return [
                "success" => false,
                "message" => "Please fill in all required fields (First Name, Last Name, Gender, Birthdate)"
            ];
        }

        // Check for duplicate phil_sys_number if provided
        if (!empty($data['phil_sys_number']) && $this->residentRepository->existsByPhilSysNumber($data['phil_sys_number'])) {
            return [
                "success" => false,
                "message" => "PhilSys Number already exists. Please use a different number."
            ];
        }

        // Validate address required fields if address data is provided
        if (!empty($addressData) && (empty($addressData['barangay']) || empty($addressData['municipality_city']) || empty($addressData['province']))) {
            return [
                "success" => false,
                "message" => "Please fill in all required address fields (Barangay, Municipality/City, Province)"
            ];
        }

        return $this->residentRepository->createWithRelations($data, $addressData, $statusData, $biometricData);
    }

    /**
     * Update an existing resident with address, status, and biometrics
     *
     * @param string $residentId Resident ID
     * @param array $data Resident data
     * @param array $addressData Address data (optional)
     * @param array $statusData Status data (optional)
     * @param array $biometricData Biometric data (optional)
     * @return array
     */
    public function update($residentId, $data, $addressData = [], $statusData = [], $biometricData = null)
    {
        // Validate required fields
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['gender']) || empty($data['birthdate'])) {
            return [
                "success" => false,
                "message" => "Please fill in all required fields (First Name, Last Name, Gender, Birthdate)"
            ];
        }

        // Check for duplicate phil_sys_number if provided (excluding current resident)
        if (!empty($data['phil_sys_number']) && $this->residentRepository->existsByPhilSysNumber($data['phil_sys_number'], $residentId)) {
            return [
                "success" => false,
                "message" => "PhilSys Number already exists. Please use a different number."
            ];
        }

        // Validate address required fields if address data is provided
        if (!empty($addressData) && (empty($addressData['barangay']) || empty($addressData['municipality_city']) || empty($addressData['province']))) {
            return [
                "success" => false,
                "message" => "Please fill in all required address fields (Barangay, Municipality/City, Province)"
            ];
        }

        return $this->residentRepository->updateWithRelations($residentId, $data, $addressData, $statusData, $biometricData);
    }

    public function delete($residentId)
    {
        return $this->residentRepository->deleteWithRelations($residentId);
    }
}