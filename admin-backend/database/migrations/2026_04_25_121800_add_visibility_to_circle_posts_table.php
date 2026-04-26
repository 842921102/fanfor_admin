<?php

use App\Support\CirclePostVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('circle_posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('circle_posts', 'visibility')) {
                $table->string('visibility', 24)
                    ->default(CirclePostVisibility::Public)
                    ->index()
                    ->after('publish_source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('circle_posts', function (Blueprint $table): void {
            if (Schema::hasColumn('circle_posts', 'visibility')) {
                $table->dropColumn('visibility');
            }
        });
    }
};
