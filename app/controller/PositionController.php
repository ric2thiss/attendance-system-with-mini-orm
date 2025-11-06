<?php

class PositionController
{
    public function getAllPosition()
    {
        return Position::all();
    }
}