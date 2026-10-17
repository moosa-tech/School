<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->text('qr_token')->nullable();
            $table->char('qr_token_hash', 64)->nullable()->unique();
            $table->timestamp('qr_token_issued_at')->nullable();
            $table->timestamp('qr_token_revoked_at')->nullable();
        });

        DB::table('students')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($students): void {
                foreach ($students as $student) {
                    $token = bin2hex(random_bytes(32));
                    DB::table('students')
                        ->where('id', $student->id)
                        ->update([
                            'qr_token' => Crypt::encryptString($token),
                            'qr_token_hash' => hash('sha256', $token),
                            'qr_token_issued_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->dropUnique(['qr_token_hash']);
            $table->dropColumn([
                'qr_token',
                'qr_token_hash',
                'qr_token_issued_at',
                'qr_token_revoked_at',
            ]);
        });
    }
};
