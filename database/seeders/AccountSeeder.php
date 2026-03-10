<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ASET
            ['code' => '1-0000', 'name' => 'Aset',             'type' => 'asset'],
            ['code' => '1-1000', 'name' => 'Kas dan Setara Kas', 'type' => 'asset'],
            ['code' => '1-1001', 'name' => 'Kas',               'type' => 'asset'],
            ['code' => '1-1002', 'name' => 'Bank',              'type' => 'asset'],
            ['code' => '1-2000', 'name' => 'Piutang Dagang',    'type' => 'asset'],
            ['code' => '1-3000', 'name' => 'Persediaan Barang', 'type' => 'asset'],
            // LIABILITAS
            ['code' => '2-0000', 'name' => 'Liabilitas',        'type' => 'liability'],
            ['code' => '2-1000', 'name' => 'Hutang Dagang',     'type' => 'liability'],
            ['code' => '2-2000', 'name' => 'Hutang Pajak (PPN)','type' => 'liability'],
            // EKUITAS
            ['code' => '3-0000', 'name' => 'Ekuitas',           'type' => 'equity'],
            ['code' => '3-1000', 'name' => 'Modal Disetor',     'type' => 'equity'],
            ['code' => '3-2000', 'name' => 'Laba Ditahan',      'type' => 'equity'],
            // PENDAPATAN
            ['code' => '4-0000', 'name' => 'Pendapatan',        'type' => 'revenue'],
            ['code' => '4-1000', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue'],
            ['code' => '4-2000', 'name' => 'Pendapatan Jasa',   'type' => 'revenue'],
            // BEBAN
            ['code' => '5-0000', 'name' => 'Beban',             'type' => 'expense'],
            ['code' => '5-1000', 'name' => 'Harga Pokok Penjualan (HPP)', 'type' => 'expense'],
            ['code' => '5-2000', 'name' => 'Beban Gaji',        'type' => 'expense'],
            ['code' => '5-3000', 'name' => 'Beban Operasional',  'type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(['code' => $account['code']], $account);
        }
    }
}
