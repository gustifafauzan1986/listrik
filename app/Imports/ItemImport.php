<?php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Imports\ItemImport;

class ItemImport implements ToModel, ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Item([
            //
        ]);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Validasi minimal: pastikan ada kode dan nama barang
            if (empty($row['kode']) || empty($row['nama_barang'])) {
                continue;
            }

            $code = trim($row['kode']);

            // Cek duplikasi berdasarkan kode barang
            if (Item::where('code', $code)->exists()) {
                $this->skippedCount++;
                continue; // Skip jika kode sudah ada di database
            }

            DB::transaction(function () use ($row, $code) {
                $stock = isset($row['stok_awal']) ? (int) $row['stok_awal'] : 0;
                
                // 1. Simpan Master Barang
                $item = Item::create([
                    'code'        => $code,
                    'name'        => trim($row['nama_barang']),
                    'unit'        => $row['satuan'] ?? 'Pcs',
                    'description' => $row['deskripsi'] ?? null,
                    'stock'       => $stock,
                ]);

                // 2. Jika ada stok awal, otomatis buat riwayat transaksinya
                if ($stock > 0) {
                    InventoryTransaction::create([
                        'item_id'        => $item->id,
                        'user_id'        => Auth::id(),
                        'type'           => 'in',
                        'quantity'       => $stock,
                        'date'           => now(),
                        'funding_source' => $row['sumber_dana'] ?? 'Tidak Diketahui',
                        'year'           => $row['tahun_anggaran'] ?? date('Y'),
                        'notes'          => 'Stok Awal (Hasil Import)',
                    ]);
                }

                $this->importedCount++;
            });
        }
    }
}
