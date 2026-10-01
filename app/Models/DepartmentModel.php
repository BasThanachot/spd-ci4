<?php
namespace App\Models;
class DepartmentModel extends BaseSoftModel
{
    protected $table = 'departments';
    protected $allowedFields = ['department_code','department_name','created_at','created_by','updated_at','updated_by','deleted_at','deleted_by'];
}
