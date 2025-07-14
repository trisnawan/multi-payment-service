<?php
namespace Trisnawan\MultiPaymentService;

class ItemCategory {
    public string|null $id, $reference_id, $title, $description;
    public string|null $updated_at, $created_at;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->reference_id = $data['reference_id'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }
}