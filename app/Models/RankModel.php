<?php
namespace App\Models;
class RankModel extends BaseSoftModel
{
    protected $table = 'ranks';
    protected $allowedFields = ['rank_name','rank_short_name','code','created_at','created_by','updated_at','updated_by','deleted_at','deleted_by'];
}
