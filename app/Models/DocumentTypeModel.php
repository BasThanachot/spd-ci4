<?php
namespace App\Models;
class DocumentTypeModel extends BaseSoftModel
{
    protected $table = 'document_types';
    protected $allowedFields = ['type_name','created_at','created_by','updated_at','updated_by','deleted_at','deleted_by'];
}
