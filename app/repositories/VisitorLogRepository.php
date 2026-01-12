<?php

require_once __DIR__ . '/BaseRepository.php';

class VisitorLogRepository extends BaseRepository {
    public function __construct(PDO $pdo = null) {
        // Use provided PDO or create new connection
        if ($pdo === null) {
            $pdo = (new Database())->connect();
        }
        parent::__construct($pdo);
    }

    protected function getModelClass(): string {
        return VisitorLog::class;
    }

    /**
     * Get visitor logs with optional filters
     * 
     * @param array $filters Optional filters: resident_id, is_resident, had_booking, date_from, date_to
     * @param int $limit Optional limit
     * @param int $offset Optional offset
     * @return array
     */
    public function getLogs(array $filters = [], int $limit = null, int $offset = null): array {
        $query = VisitorLog::query();

        // Apply filters
        if (isset($filters['resident_id'])) {
            $query->where('resident_id', $filters['resident_id']);
        }

        if (isset($filters['is_resident'])) {
            $query->where('is_resident', $filters['is_resident'] ? 1 : 0);
        }

        if (isset($filters['had_booking'])) {
            $query->where('had_booking', $filters['had_booking'] ? 1 : 0);
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        } elseif (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        } elseif (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Order by most recent first
        $query->orderBy('created_at', 'DESC');

        // Apply limit and offset
        if ($limit !== null) {
            $query->limit($limit);
            if ($offset !== null) {
                $query->offset($offset);
            }
        }

        return $query->get();
    }

    /**
     * Get visitor logs count with filters
     * 
     * @param array $filters Optional filters
     * @return int
     */
    public function getCount(array $filters = []): int {
        $query = VisitorLog::query();

        // Apply same filters as getLogs
        if (isset($filters['resident_id'])) {
            $query->where('resident_id', $filters['resident_id']);
        }

        if (isset($filters['is_resident'])) {
            $query->where('is_resident', $filters['is_resident'] ? 1 : 0);
        }

        if (isset($filters['had_booking'])) {
            $query->where('had_booking', $filters['had_booking'] ? 1 : 0);
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        } elseif (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        } elseif (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->count();
    }

    /**
     * Create a visitor log entry
     * 
     * @param array $data Visitor log data
     * @return array|object|null
     */
    public function createLog(array $data) {
        return VisitorLog::create($data);
    }
}
