<?php
namespace App\Models;
class AgencyModel extends BaseSoftModel
{
    protected $table = 'agencies';
    protected $allowedFields = ['agency_name','address','phone','created_at','created_by','updated_at','updated_by','deleted_at','deleted_by'];
}
