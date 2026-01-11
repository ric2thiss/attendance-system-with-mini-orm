<?php

class DepartmentController
{
    protected $departmentRepository;

    public function __construct() {
        $db = (new Database())->connect();
        $this->departmentRepository = new DepartmentRepository($db);
    }

    public function store()
    {
        return;
    }

    public function getDepartmentLists()
    {
        return $this->departmentRepository->findAll();
    }
}