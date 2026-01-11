<?php

class PositionController
{
    protected $positionRepository;

    public function __construct() {
        $db = (new Database())->connect();
        $this->positionRepository = new PositionRepository($db);
    }

    public function getAllPosition()
    {
        return $this->positionRepository->findAll();
    }
}