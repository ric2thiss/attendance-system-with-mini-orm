<?php

require_once __DIR__ . '/BaseRepository.php';

class FingerprintsRepository extends BaseRepository {
    protected function getModelClass(): string {
        return Fingerprints::class;
    }

    /**
     * Get all fingerprints with limited fields
     * 
     * @return array
     */
    public function getAllLimited(): array {
        return Fingerprints::query()
            ->select("employee_id", "template")
            ->get();
    }

    /**
     * Check if employee fingerprint exists
     * 
     * @param string $employeeId
     * @return bool
     */
    public function existsByEmployeeId(string $employeeId): bool {
        $fingerprint = $this->findBy('employee_id', $employeeId);
        return $fingerprint !== null;
    }
}
