<?php

namespace App\Controllers\Documents;

use App\Controllers\BaseController;
use App\Models\DocumentReceivedModel;
use App\Models\DocumentSentModel;
use App\Models\AgencyModel;
use App\Models\DepartmentModel;
use App\Models\DocumentTypeModel;

/**
 * ค้นหาหนังสือ (รวมรับ+ส่ง) — พอร์ตจาก CI3 documents/Search
 */
class Search extends BaseController
{
    public function index()
    {
        $filters = [
            'doc_type'         => $this->request->getGet('doc_type') ?: 'all',
            'keyword'          => $this->request->getGet('keyword'),
            'date_from'        => $this->request->getGet('date_from'),
            'date_to'          => $this->request->getGet('date_to'),
            'agency_id'        => $this->request->getGet('agency_id'),
            'department_id'    => $this->request->getGet('department_id'),
            'document_type_id' => $this->request->getGet('document_type_id'),
        ];

        $hasFilter = (bool) ($filters['keyword'] || $filters['date_from'] || $filters['date_to']
            || $filters['agency_id'] || $filters['department_id'] || $filters['document_type_id']);

        $results = [];
        if ($hasFilter) {
            if ($filters['doc_type'] === 'all' || $filters['doc_type'] === 'received') {
                foreach ((new DocumentReceivedModel())->searchDocs($filters) as $d) {
                    $d->doc_kind = 'received';
                    $d->doc_no   = $d->received_no;
                    $d->doc_date = $d->received_date;
                    $results[]   = $d;
                }
            }
            if ($filters['doc_type'] === 'all' || $filters['doc_type'] === 'sent') {
                foreach ((new DocumentSentModel())->searchDocs($filters) as $d) {
                    $d->doc_kind = 'sent';
                    $d->doc_no   = $d->sent_no;
                    $d->doc_date = $d->sent_date;
                    $results[]   = $d;
                }
            }
            usort($results, static function ($a, $b) {
                $cmp = strcmp((string) $b->doc_date, (string) $a->doc_date);

                return $cmp !== 0 ? $cmp : ($b->id - $a->id);
            });
        }

        $data = $filters;
        $data['title']          = 'ค้นหาหนังสือ';
        $data['results']        = $results;
        $data['has_filter']     = $hasFilter;
        $data['agencies']       = (new AgencyModel())->getActive('agency_name');
        $data['departments']    = (new DepartmentModel())->getActive('department_name');
        $data['document_types'] = (new DocumentTypeModel())->getActive('type_name');

        return $this->render('documents/search/index', $data);
    }
}
