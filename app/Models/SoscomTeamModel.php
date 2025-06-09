<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk tabel soscom_teams
 */
class SoscomTeamModel extends Model
{
    protected $table            = 'soscom_teams';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'team_code',
        'team_name',
    ];
}
