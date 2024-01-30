<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'branch_tbl';
    protected $primaryKey       = 'branch_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['branch_id', 'branch_name', 'latitude', 'longitude', 'barangay', 'opening_time', 'closing_time', 'contact_number', 'email', 'CS_invite_code', 'BA_invite_code', 'is_open_for_invitation', 'created_at'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
