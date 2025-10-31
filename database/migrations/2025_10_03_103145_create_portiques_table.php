<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portiques', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Exemple : "Entrée principale"
            $table->string('emplacement'); // Ex: "Bâtiment A"
            $table->string('mac_address')->unique(); // ✅ Adresse Bluetooth du portique
            $table->boolean('actif')->default(true); // portique activé/désactivé
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portiques');
    }
};
