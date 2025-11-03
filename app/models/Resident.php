<?php

require_once __DIR__ . '/../../app/models/Model.php';

class Resident extends Model {
    protected $table = "residents";
    protected $fillable = [ "phil_sys_number",
                            "first_name", "middle_name", "last_name", "suffix",
                            "gender", "birthdate", "place_of_birth_city", "place_of_birth_province",
                            "blood_type", "civil_status_id", "photo_path",];
}
