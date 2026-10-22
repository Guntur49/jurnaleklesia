<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JournalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_staff_can_submit_journal_during_their_schedule_and_view_full_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-22 17:00:00', 'Asia/Jakarta'));
        $staff = User::factory()->create([
            'name' => 'Sari Staff',
            'role' => 'staff',
            'journal_start_time' => '16:00',
            'journal_end_time' => '18:00',
        ]);

        $this->actingAs($staff)
            ->post(route('journals.store'), [
                'content' => 'Menyelesaikan laporan mingguan.',
                'result' => 'Laporan mingguan selesai dan siap ditinjau.',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertTrue(Journal::where('user_id', $staff->id)
            ->whereDate('journal_date', '2026-10-22')
            ->where('content', 'Menyelesaikan laporan mingguan.')
            ->where('result', 'Laporan mingguan selesai dan siap ditinjau.')
            ->exists());

        $this->get(route('journals.index', [
            'staff_id' => $staff->id,
            'month' => 10,
            'year' => 2026,
        ]))
            ->assertOk()
            ->assertSee('calendar-day--complete', false)
            ->assertSee('calendar-day--empty', false)
            ->assertSee('data-status="complete"', false)
            ->assertSee('data-status="empty"', false)
            ->assertSee('id="journal-report-modal"', false)
            ->assertSee('Menyelesaikan laporan mingguan.')
            ->assertSee('Laporan mingguan selesai dan siap ditinjau.')
            ->assertSee('31 Oktober 2026')
            ->assertSee('Belum ada jurnal yang diisi untuk tanggal ini.');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Laporan mingguan selesai dan siap ditinjau.');
    }

    public function test_user_can_sign_in_with_username_and_password(): void
    {
        User::factory()->create([
            'username' => 'staff-login',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'username' => 'staff-login',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->get(route('dashboard'))->assertOk()->assertSee('Halo');
    }

    public function test_staff_cannot_submit_outside_their_schedule(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-22 15:00:00', 'Asia/Jakarta'));
        $staff = User::factory()->create([
            'journal_start_time' => '16:00',
            'journal_end_time' => '18:00',
        ]);

        $this->actingAs($staff)
            ->from(route('dashboard'))
            ->post(route('journals.store'), ['content' => 'Menyelesaikan laporan mingguan.'])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('journals', 0);
    }

    public function test_staff_must_include_today_result_when_submitting_journal(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-22 17:00:00', 'Asia/Jakarta'));
        $staff = User::factory()->create([
            'journal_start_time' => '16:00',
            'journal_end_time' => '18:00',
        ]);

        $this->actingAs($staff)
            ->from(route('dashboard'))
            ->post(route('journals.store'), [
                'content' => 'Menyelesaikan laporan mingguan.',
                'result' => '',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('result');

        $this->assertDatabaseCount('journals', 0);
    }

    public function test_overnight_staff_schedule_is_supported(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-23 01:00:00', 'Asia/Jakarta'));
        $staff = User::factory()->create([
            'journal_start_time' => '22:00',
            'journal_end_time' => '02:00',
        ]);

        $this->assertTrue($staff->canSubmitJournalAt(now()));
    }

    public function test_admin_can_manage_staff_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('accounts.store'), [
            'name' => 'Dina Staff',
            'position' => 'Administrasi',
            'username' => 'dina',
            'role' => 'staff',
            'password' => 'password-rahasia',
            'journal_start_time' => '16:00',
            'journal_end_time' => '18:00',
        ])->assertRedirect(route('accounts.index'));

        $staff = User::where('username', 'dina')->firstOrFail();
        $this->assertSame('staff', $staff->role);
        $this->assertTrue(Hash::check('password-rahasia', $staff->password));
        $this->get(route('accounts.index'))->assertOk()->assertSee('Dina Staff');

        $this->put(route('accounts.update', $staff), [
            'name' => 'Dina Baru',
            'position' => 'Koordinator',
            'username' => 'dina',
            'role' => 'staff',
            'password' => '',
            'journal_start_time' => '15:00',
            'journal_end_time' => '17:00',
        ])->assertRedirect(route('accounts.index'));

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Dina Baru',
        ]);
        $this->assertStringStartsWith('15:00', $staff->fresh()->journal_start_time);

        $this->delete(route('accounts.destroy', $staff))
            ->assertRedirect(route('accounts.index'));
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_owner_can_download_staff_month_as_pdf_but_cannot_submit_a_journal(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $staff = User::factory()->create(['name' => 'Sari Staff']);
        Journal::create([
            'user_id' => $staff->id,
            'journal_date' => '2026-10-02',
            'content' => 'Menyiapkan materi rapat.',
            'result' => 'Materi rapat selesai dan dibagikan.',
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Menyiapkan materi rapat.')
            ->assertSee('Materi rapat selesai dan dibagikan.');

        $this->actingAs($owner)
            ->post(route('journals.store'), ['content' => 'Catatan owner'])
            ->assertForbidden();

        $this->get(route('journals.pdf', [
            'user' => $staff,
            'month' => 10,
            'year' => 2026,
        ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition');
    }

    public function test_regular_staff_cannot_manage_accounts_or_download_pdf(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->get(route('accounts.index'))
            ->assertForbidden();
    }
}
