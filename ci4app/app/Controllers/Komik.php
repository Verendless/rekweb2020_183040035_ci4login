<?php

namespace App\Controllers;

use App\Models\KomikModel;

class Komik extends BaseController
{
    protected $komikModel;
    public function __construct()
    {
        $this->komikModel = new KomikModel();
    }
    public function index()
    {
        // $komik = $this->komikModel->findAll();
        $data = [
            'title' => 'Daftar Komik',
            'komik' => $this->komikModel->getKomik()
        ];


        // $komikModel = new \App\Models\KomikModel();
        // $komikModel = new KomikModel();


        return view('komik/index', $data);
    }

    public function detail($slugh)
    {
        $data = [
            'title' => 'Detail Komik',
            'komik' =>  $this->komikModel->getKomik($slugh)
        ];

        // Jika komik tidak ada di table
        if (empty($data['komik'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Judul Komik ' . $slugh . ' Tidak di temukan');
        }

        return view('komik/detail', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Form Tambah Data Komik',
            'validation' => \Config\Services::validation()
        ];

        return view('komik/create', $data);
    }

    public function save()
    {
        // validasi input
        if (!$this->validate([
            'judul' => [
                'rules' => 'required|is_unique[komik.judul]',
                'errors' => [
                    'required' => '{field} komik harus diisi.',
                    'is_unique' => '{field} komik sudah terdaftar.'
                ]
            ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jgp,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar terlalu basar',
                    'is_image' => 'Yang anda pilih, bukan gambar',
                    'mime_in' => 'Yang anda pilih, bukan gambar'
                ]
            ]
        ])) {
            // $validation = \Config\Services::validation();
            // return redirect()->to('/komik/create')->withInput()->with('validation', $validation);
            return redirect()->to('/komik/create')->withInput();
        }

        // ambil gambar
        $filesampul =  $this->request->getFile('sampul');
        // cek apakah tidak ada gambar yang diupload
        if ($filesampul->getError() == 4) {
            $namaSampul = 'default.jpg';
        } else {
            // generate namma sampul random
            $namaSampul = $filesampul->getRandomName();
            // pindahkan file ke folder img
            $filesampul->move('img', $namaSampul);
            // ambil nama file sampul
            $namaSampul = $filesampul->getName();
        }

        $slugh = url_title($this->request->getVar('judul'), '-', true);
        $this->komikModel->save([
            'judul' => $this->request->getVar('judul'),
            'slugh' => $slugh,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $namaSampul
        ]);

        session()->setFlashdata('pesan', 'Data berhasil ditambahkan.');

        return redirect()->to('/komik');
    }

    public function delete($id)
    {
        // cari gambar berdasarkan id
        $komik = $this->komikModel->find(($id));

        // cek jika file gambarnya default.jpg
        if ($komik['sampul'] != 'default.jpg') {
            // hapus gambar
            unlink('img/' . $komik['sampul']);
        }


        $this->komikModel->delete($id);
        session()->setFlashdata('pesan', 'Data berhasil dihapus.');
        return redirect()->to('/komik');
    }

    public function edit($slugh)
    {
        $data = [
            'title' => 'Form Ubah Data Komik',
            'validation' => \Config\Services::validation(),
            'komik' => $this->komikModel->getKomik($slugh)
        ];

        return view('komik/edit', $data);
    }

    public function update($id)
    {
        // cek judul
        $komikLama = $this->komikModel->getKomik($this->request->getVar('slugh'));
        if ($komikLama['judul'] == $this->request->getVar('judul')) {
            $rule_judul = 'required';
        } else {
            $rule_judul = 'required|is_unique[komik.judul]';
        }
        if (!$this->validate([
            'judul' => [
                'rules' => $rule_judul,
                'errors' => [
                    'required' => '{field} komik harus diisi.',
                    'is_unique' => '{field} komik sudah terdaftar.'
                ]
            ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jgp,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar terlalu basar',
                    'is_image' => 'Yang anda pilih, bukan gambar',
                    'mime_in' => 'Yang anda pilih, bukan gambar'
                ]
            ]
        ])) {
            return redirect()->to('/komik/edit/' . $this->request->getVar('slugh'))->withInput();
        }

        $fileSampul = $this->request->getFile('sampul');
        $sampulLama = $this->request->getVar('sampulLama');
        // cek gambar, apakah tetap gambar lama
        if ($fileSampul->getError() == 4) {
            $namaSampul = $sampulLama;
        } else {
            // generate nama file random
            $namaSampul = $fileSampul->getRandomName();
            // pindahkan gambar
            $fileSampul->move('img', $namaSampul);
            // hapus file lama
            $komik = $this->komikModel->find(($id));
            if ($komik['sampul'] != 'default.jpg') {
                unlink('img/' . $this->request->getVar('sampulLama'));
            }
        }

        $slugh = url_title($this->request->getVar('judul'), '-', true);
        $this->komikModel->save([
            'id' => $id,
            'judul' => $this->request->getVar('judul'),
            'slugh' => $slugh,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $namaSampul
        ]);

        session()->setFlashdata('pesan', 'Data berhasil diubah.');

        return redirect()->to('/komik');
    }
}
