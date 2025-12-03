<?php

declare(strict_types=1);

use App\Livewire\BackupDashboard;
use App\Models\BackupLog;
use App\Models\BpjsClaim;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('backup');
    Storage::fake('shared');
});

test('backup dashboard can be rendered', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(BackupDashboard::class)
        ->assertStatus(200)
        ->assertSee('Backup Dashboard');
});

test('backup dashboard shows stats correctly', function () {
    $user = User::factory()->admin()->create();

    // Create some backup logs
    BackupLog::create([
        'source_path' => '/test/file1.pdf',
        'backup_path' => '/backup/file1.pdf',
        'file_type' => 'merged',
        'file_size' => 1024,
        'status' => 'success',
    ]);

    BackupLog::create([
        'source_path' => '/test/file2.pdf',
        'file_type' => 'merged',
        'status' => 'failed',
        'error_message' => 'Disk not accessible',
    ]);

    Livewire::actingAs($user)
        ->test(BackupDashboard::class)
        ->assertSee('1') // 1 success
        ->assertSee('Sukses')
        ->assertSee('Gagal');
});

test('backup dashboard can filter by status', function () {
    $user = User::factory()->admin()->create();

    BackupLog::create([
        'source_path' => '/test/success.pdf',
        'backup_path' => '/backup/success.pdf',
        'file_type' => 'merged',
        'status' => 'success',
    ]);

    BackupLog::create([
        'source_path' => '/test/failed.pdf',
        'file_type' => 'merged',
        'status' => 'failed',
    ]);

    Livewire::actingAs($user)
        ->test(BackupDashboard::class)
        ->set('statusFilter', 'success')
        ->assertSee('success.pdf')
        ->assertDontSee('failed.pdf');
});

test('backup dashboard can retry failed backup', function () {
    // Use queue fake to prevent actual job execution
    Queue::fake();

    $user = User::factory()->admin()->create();

    $claim = BpjsClaim::create([
        'no_rm' => 'RM001',
        'no_kartu_bpjs' => '1234567890',
        'no_sep' => 'SEP123',
        'jenis_rawatan' => 'RJ',
        'tanggal_rawatan' => now(),
        'nama_pasien' => 'Test Patient',
        'kelas_rawatan' => '1',
    ]);

    $log = BackupLog::create([
        'bpjs_claims_id' => $claim->id,
        'source_path' => '/test/failed.pdf',
        'file_type' => 'merged',
        'status' => 'failed',
        'error_message' => 'Test error',
    ]);

    Livewire::actingAs($user)
        ->test(BackupDashboard::class)
        ->call('retryBackup', $log->id);

    $log->refresh();
    expect($log->status)->toBe('pending');
    expect($log->error_message)->toBeNull();
});

test('backup dashboard is not accessible by operator', function () {
    $operator = User::factory()->operator()->create();

    $this->actingAs($operator)
        ->get(route('backup.dashboard'))
        ->assertForbidden();
});

test('backup dashboard is accessible by admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('backup.dashboard'))
        ->assertOk();
});

test('backup log model has correct scopes', function () {
    BackupLog::create([
        'source_path' => '/test/pending.pdf',
        'file_type' => 'merged',
        'status' => 'pending',
    ]);

    BackupLog::create([
        'source_path' => '/test/success.pdf',
        'file_type' => 'merged',
        'status' => 'success',
    ]);

    BackupLog::create([
        'source_path' => '/test/failed.pdf',
        'file_type' => 'merged',
        'status' => 'failed',
    ]);

    expect(BackupLog::pending()->count())->toBe(1);
    expect(BackupLog::success()->count())->toBe(1);
    expect(BackupLog::failed()->count())->toBe(1);
});

test('backup log can mark as success', function () {
    $log = BackupLog::create([
        'source_path' => '/test/file.pdf',
        'file_type' => 'merged',
        'status' => 'pending',
    ]);

    $log->markAsSuccess('/backup/file.pdf', 2048);

    expect($log->status)->toBe('success');
    expect($log->backup_path)->toBe('/backup/file.pdf');
    expect($log->file_size)->toBe(2048);
    expect($log->completed_at)->not->toBeNull();
});

test('backup log can mark as failed', function () {
    $log = BackupLog::create([
        'source_path' => '/test/file.pdf',
        'file_type' => 'merged',
        'status' => 'pending',
    ]);

    $log->markAsFailed('Disk full');

    expect($log->status)->toBe('failed');
    expect($log->error_message)->toBe('Disk full');
    expect($log->retry_count)->toBe(1);
});

test('backup log formats file size correctly', function () {
    $log = BackupLog::create([
        'source_path' => '/test/file.pdf',
        'file_type' => 'merged',
        'status' => 'success',
        'file_size' => 1536000, // ~1.5 MB
    ]);

    expect($log->formatted_file_size)->toContain('MB');
});

test('backup log returns correct badge color', function () {
    $successLog = BackupLog::create([
        'source_path' => '/test/success.pdf',
        'file_type' => 'merged',
        'status' => 'success',
    ]);

    $failedLog = BackupLog::create([
        'source_path' => '/test/failed.pdf',
        'file_type' => 'merged',
        'status' => 'failed',
    ]);

    $pendingLog = BackupLog::create([
        'source_path' => '/test/pending.pdf',
        'file_type' => 'merged',
        'status' => 'pending',
    ]);

    expect($successLog->status_badge_color)->toBe('emerald');
    expect($failedLog->status_badge_color)->toBe('rose');
    expect($pendingLog->status_badge_color)->toBe('amber');
});
