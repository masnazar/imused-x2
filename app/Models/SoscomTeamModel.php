<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk Soscom Team
 */
class SoscomTeamModel extends Model
{
    protected $table = 'soscom_teams';
    protected $primaryKey = 'id';
    protected $allowedFields = ['team_code', 'team_name', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    protected $returnType = 'array';
}
