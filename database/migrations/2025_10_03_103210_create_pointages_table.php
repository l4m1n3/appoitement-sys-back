<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pointages', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('employe_id')
                  ->constrained('employes')
                  ->onDelete('cascade');
                  
            $table->foreignId('badge_id')
                  ->constrained('badges')
                  ->onDelete('cascade');
                  
            $table->foreignId('portique_id')
                  ->constrained('portiques')
                  ->onDelete('set null');
                  
            // Données du pointage
            $table->timestamp('date_heure')->useCurrent();
            $table->enum('type', ['entrée', 'sortie'])->index();
            $table->enum('methode', ['bluetooth', 'gps', 'manuel'])
                  ->default('bluetooth')
                  ->comment('Source du pointage');

            // Métadonnées
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Index pour requêtes rapides
            $table->index(['employe_id', 'date_heure']);
            $table->index('portique_id');
            $table->index('methode');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointages');
    }
};