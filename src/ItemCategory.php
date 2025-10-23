<?php
namespace Trisnawan\MultiPaymentService;

/**
 * Class ItemCategory
 * 
 * Response data from Transaction Category.
 * Jalankan `new ItemCategory($data)` untuk data single atau jalankan
 * `ItemCategory::fromArrayList($data)` untuk data list.
 */
class ItemCategory {
    public string|null $id, $reference_id, $title, $description;
    public string|null $updated_at, $created_at;

    public function __construct(array|null $data = null) {
        $this->id = $data['id'] ?? null;
        $this->reference_id = $data['reference_id'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public static function fromArrayList(array $data): array{
        if(!isset($data[0]['id'])) [];
        
        $invoices = [];
        foreach($data as $val){
            $invoices[] = new self($val);
        }
        return $invoices;
    }
}