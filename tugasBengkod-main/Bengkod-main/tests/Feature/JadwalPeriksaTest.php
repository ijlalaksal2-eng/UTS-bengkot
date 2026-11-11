<?php

namespace Tests\Feature;

use App\Models\JadwalPeriksa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JadwalPeriksaTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_dashboard_links_to_schedule_management(): void
    {
        $dokter = $this->createDokter('dokter@example.com');

        $this->actingAs($dokter)
            ->get(route('dokter.dashboard'))
            ->assertOk()
            ->assertSee(route('jadwal-periksa.index'));
    }

    public function test_dokter_can_create_a_schedule_with_valid_hours(): void
    {
        $dokter = $this->createDokter('dokter@example.com');

        $response = $this->actingAs($dokter)->post(route('jadwal-periksa.store'), [
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
        ]);

        $response->assertRedirect(route('jadwal-periksa.index'));
        $this->assertDatabaseHas('jadwal_periksa', [
            'id_dokter' => $dokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
        ]);

        $this->actingAs($dokter)
            ->from(route('jadwal-periksa.create'))
            ->post(route('jadwal-periksa.store'), [
                'hari' => 'Senin',
                'jam_mulai' => '12:00',
                'jam_selesai' => '08:00',
            ])
            ->assertSessionHasErrors('jam_selesai');
    }

    public function test_dokter_can_update_and_delete_their_schedule(): void
    {
        $dokter = $this->createDokter('dokter@example.com');
        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
        ]);

        $this->actingAs($dokter)
            ->put(route('jadwal-periksa.update', $jadwal), [
                'hari' => 'Selasa',
                'jam_mulai' => '09:00',
                'jam_selesai' => '13:00',
            ])
            ->assertRedirect(route('jadwal-periksa.index'));

        $this->assertDatabaseHas('jadwal_periksa', [
            'id' => $jadwal->id,
            'hari' => 'Selasa',
            'jam_mulai' => '09:00',
            'jam_selesai' => '13:00',
        ]);

        $this->actingAs($dokter)
            ->delete(route('jadwal-periksa.destroy', $jadwal))
            ->assertRedirect(route('jadwal-periksa.index'));

        $this->assertDatabaseMissing('jadwal_periksa', ['id' => $jadwal->id]);
    }

    public function test_dokter_cannot_access_another_doctors_schedule(): void
    {
        $dokter = $this->createDokter('dokter@example.com');
        $dokterLain = $this->createDokter('dokter-lain@example.com');
        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokterLain->id,
            'hari' => 'Rabu',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
        ]);

        $this->actingAs($dokter)
            ->get(route('jadwal-periksa.edit', $jadwal))
            ->assertNotFound();

        $this->actingAs($dokter)
            ->put(route('jadwal-periksa.update', $jadwal), [
                'hari' => 'Kamis',
                'jam_mulai' => '09:00',
                'jam_selesai' => '13:00',
            ])
            ->assertNotFound();

        $this->actingAs($dokter)
            ->delete(route('jadwal-periksa.destroy', $jadwal))
            ->assertNotFound();

        $this->assertDatabaseHas('jadwal_periksa', ['id' => $jadwal->id]);
    }

    private function createDokter(string $email): User
    {
        return User::create([
            'nama' => 'Dokter',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'dokter',
        ]);
    }
}