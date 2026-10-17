<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_qr_scan_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('attendance_session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('scanned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('scanned_at');
            $table->enum('event_type', ['entry'])->default('entry');
            $table->enum('processing_status', ['pending_review', 'attendance_recorded', 'manual_attendance_exists']);
            $table->timestamps();

            $table->unique(
                ['school_id', 'attendance_session_id', 'student_id', 'event_type'],
                'student_qr_scan_session_unique'
            );
            $table->index(['school_id', 'processing_status', 'scanned_at'], 'student_qr_scan_review_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_qr_scan_events');
    }
};
