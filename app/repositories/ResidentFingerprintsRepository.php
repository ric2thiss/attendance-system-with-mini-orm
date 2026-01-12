<?php

require_once __DIR__ . '/BaseRepository.php';

class ResidentFingerprintsRepository extends BaseRepository {
    protected function getModelClass(): string {
        return ResidentFingerprints::class;
    }

    /**
     * Get all resident fingerprints with limited fields
     * 
     * @return array
     */
    public function getAllLimited(): array {
        return ResidentFingerprints::query()
            ->select("resident_id", "template")
            ->get();
    }

    /**
     * Check if resident fingerprint exists
     * 
     * @param int $residentId
     * @return bool
     */
    public function existsByResidentId(int $residentId): bool {
        $fingerprint = $this->findBy('resident_id', $residentId);
        return $fingerprint !== null;
    }

    /**
     * Get fingerprint by resident ID
     * 
     * @param int $residentId
     * @return object|null
     */
    public function findByResidentId(int $residentId) {
        return $this->findBy('resident_id', $residentId);
    }
}
