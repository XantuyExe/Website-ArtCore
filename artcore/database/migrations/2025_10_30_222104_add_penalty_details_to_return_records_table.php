<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('return_records')) {
            return;
        }

        Schema::table('return_records', function (Blueprint $table) {
            if (!Schema::hasColumn('return_records', 'late_fee')) {
                $table->unsignedBigInteger('late_fee')->default(0)->after('damage_fee');
            }
            if (!Schema::hasColumn('return_records', 'total_penalty')) {
                $table->unsignedBigInteger('total_penalty')->default(0)->after('late_fee');
            }
            if (!Schema::hasColumn('return_records', 'penalty_paid')) {
                $table->unsignedBigInteger('penalty_paid')->default(0)->after('total_penalty');
            }
            if (!Schema::hasColumn('return_records', 'deposit_used')) {
                $table->unsignedBigInteger('deposit_used')->default(0)->after('penalty_paid');
            }
            if (!Schema::hasColumn('return_records', 'delay_days')) {
                $table->integer('delay_days')->default(0)->after('deposit_used');
            }
            if (!Schema::hasColumn('return_records', 'rent_fee_snapshot')) {
                $table->unsignedBigInteger('rent_fee_snapshot')->default(0)->after('delay_days');
            }
            if (!Schema::hasColumn('return_records', 'deposit_paid_snapshot')) {
                $table->unsignedBigInteger('deposit_paid_snapshot')->default(0)->after('rent_fee_snapshot');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('return_records')) {
            return;
        }

        Schema::table('return_records', function (Blueprint $table) {
            foreach ([
                'deposit_paid_snapshot',
                'rent_fee_snapshot',
                'delay_days',
                'deposit_used',
                'penalty_paid',
                'total_penalty',
                'late_fee',
            ] as $column) {
                if (Schema::hasColumn('return_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
