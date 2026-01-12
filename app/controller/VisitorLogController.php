<?php

class VisitorLogController {
    protected $visitorLogRepository;
    protected $residentRepository;

    public function __construct() {
        $db = (new Database())->connect();
        $this->visitorLogRepository = new VisitorLogRepository();
        $this->residentRepository = new ResidentRepository($db);
    }

    /**
     * Create a visitor log entry
     * 
     * @param array $data Visitor log data
     * @return array
     */
    public function store(array $data): array {
        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'address', 'purpose'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return [
                    'success' => false,
                    'error' => "Field '{$field}' is required"
                ];
            }
        }

        // Set defaults
        $logData = [
            'first_name' => trim($data['first_name']),
            'middle_name' => isset($data['middle_name']) ? trim($data['middle_name']) : null,
            'last_name' => trim($data['last_name']),
            'address' => trim($data['address']),
            'purpose' => trim($data['purpose']),
            'is_resident' => isset($data['is_resident']) ? ($data['is_resident'] ? 1 : 0) : 0,
            'had_booking' => isset($data['had_booking']) ? ($data['had_booking'] ? 1 : 0) : 0,
            'booking_id' => isset($data['booking_id']) ? trim($data['booking_id']) : null,
        ];

        // Handle resident_id
        if (isset($data['resident_id']) && !empty($data['resident_id'])) {
            $logData['resident_id'] = (int)$data['resident_id'];
            $logData['is_resident'] = 1;
        } else {
            $logData['resident_id'] = null;
            $logData['is_resident'] = 0;
        }

        // Handle birthdate (required for non-residents, optional for residents)
        if (isset($data['birthdate']) && !empty($data['birthdate'])) {
            $logData['birthdate'] = $data['birthdate'];
        } elseif (!$logData['is_resident']) {
            // Birthdate is required for non-residents
            return [
                'success' => false,
                'error' => 'Birthdate is required for non-resident visitors'
            ];
        }

        try {
            $log = $this->visitorLogRepository->createLog($logData);
            
            return [
                'success' => true,
                'message' => 'Visitor log created successfully',
                'data' => $log
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to create visitor log',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get visitor logs with filters
     * 
     * @param array $filters Optional filters
     * @param int $limit Optional limit
     * @param int $offset Optional offset
     * @return array
     */
    public function index(array $filters = [], int $limit = null, int $offset = null): array {
        try {
            $logs = $this->visitorLogRepository->getLogs($filters, $limit, $offset);
            $count = $this->visitorLogRepository->getCount($filters);

            return [
                'success' => true,
                'data' => $logs,
                'count' => $count
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch visitor logs',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get statistics for visitor logs
     * 
     * @param string $dateFrom Start date (Y-m-d H:i:s)
     * @param string $dateTo End date (Y-m-d H:i:s)
     * @return array
     */
    public function getStatistics(string $dateFrom, string $dateTo): array {
        try {
            $filters = ['date_from' => $dateFrom, 'date_to' => $dateTo];
            
            $totalVisitors = $this->visitorLogRepository->getCount($filters);
            $residentVisitors = $this->visitorLogRepository->getCount(array_merge($filters, ['is_resident' => 1]));
            $nonResidentVisitors = $this->visitorLogRepository->getCount(array_merge($filters, ['is_resident' => 0]));
            $withBooking = $this->visitorLogRepository->getCount(array_merge($filters, ['had_booking' => 1]));
            $withoutBooking = $this->visitorLogRepository->getCount(array_merge($filters, ['had_booking' => 0]));

            return [
                'success' => true,
                'total_visitors' => $totalVisitors,
                'resident_visitors' => $residentVisitors,
                'non_resident_visitors' => $nonResidentVisitors,
                'with_booking' => $withBooking,
                'without_booking' => $withoutBooking
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch statistics',
                'message' => $e->getMessage()
            ];
        }
    }
}
