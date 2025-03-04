<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vendor;
use App\Models\Tagihan;
use App\Models\User;
use App\Models\Daerah;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagihanTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_only_shows_current_month_tagihan()
    {
        // Set waktu sekarang ke 15 Maret 2025
        Carbon::setTestNow(Carbon::create(2025, 3, 15));

        // Buat record daerah untuk memastikan foreign key valid
        $daerah = Daerah::factory()->create();  // Pastikan factory untuk Daerah sudah ada

        // Buat vendor dan assign daerah_id dari record daerah yang baru dibuat
        $vendor = Vendor::factory()->create(['daerah_id' => $daerah->id]);

        // Buat data tagihan untuk bulan Maret 2025 (bulan berjalan)
        $tagihanCurrent = Tagihan::factory()->create([
            'vendor_id'       => $vendor->id,
            'tanggal_masuk'   => Carbon::now(), // 15 Maret 2025
            'status_kunjungan'=> 'ada orang',
            'uang_masuk'      => 20000,
            'daerah_id'       => $vendor->daerah_id, // pastikan foreign key valid
        ]);

        // Buat data tagihan untuk bulan Februari 2025 (data lama)
        $tagihanPrev = Tagihan::factory()->create([
            'vendor_id'       => $vendor->id,
            'tanggal_masuk'   => Carbon::create(2025, 2, 15),
            'status_kunjungan'=> 'ada orang',
            'uang_masuk'      => 20000,
            'daerah_id'       => $vendor->daerah_id,
        ]);

        // Buat user admin (pastikan factory User sudah ada dan memiliki kolom role)
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Lakukan request ke route index
        $response = $this->actingAs($admin)->get(route('tagihan.index'));
        $response->assertStatus(200);

        // Ambil data vendor yang dikirim ke view
        $vendors = $response->viewData('vendors');

        // Pastikan vendor ditemukan dan hanya tagihan untuk bulan Maret yang tampil
        $this->assertNotNull($vendors);
        $this->assertEquals(1, $vendors->first()->tagihan->count());
        $this->assertEquals($tagihanCurrent->id, $vendors->first()->tagihan->first()->id);

        // Reset waktu test now
        Carbon::setTestNow();
    }
}
