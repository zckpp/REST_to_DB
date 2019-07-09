<?php

namespace classes;

class Ticket {
    public $id, $title, $tags, $status, $created_date, $resolution_time, $closed_by;

    public function __construct(int $id, string $title, string $tags, string $status, string $created_date, string $resolution_time, string $closed_by) {
        $this->id = $id??null;
        $this->title = $title??null;
        $this->tags = $tags??null;
        $this->status = $status??null;
        $this->created_date = $created_date??null;
        $this->resolution_time = $resolution_time??null;
        $this->closed_by = $closed_by??null;
    }
}