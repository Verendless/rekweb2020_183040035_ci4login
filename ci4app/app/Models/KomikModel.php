<?php

namespace App\Models;

use CodeIgniter\Model;

class KomikModel extends Model
{
    protected $table = 'komik';
    protected $useTimestamps = true;
    protected $allowedFields = ['judul', 'slugh', 'penulis', 'penerbit', 'sampul'];

    public function getKomik($slugh = false)
    {
        if ($slugh == false) {
            return $this->findAll();
        }

        return $this->where(['slugh' => $slugh])->first();
    }
}
