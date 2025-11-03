<?php

class ResidentController
{
    public function getAllResident()
    {
        $residents = Resident::query()
        ->select("*")
        ->whereRaw("resident_id NOT IN (SELECT resident_id FROM employees)")
        ->get();

        return $residents;
    }
}