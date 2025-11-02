<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{ 
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade');
            $table->string('code_unique')->unique(); // UID RFID ou QR code
            $table->enum('type', ['RFID', 'QR']); // type du badge
            $table->boolean('actif')->default(true); // badge désactivé si perdu/licenciement
            $table->timestamps();
        });
    }

    public function down(): void 
    {
        Schema::dropIfExists('badges');
    }
};
