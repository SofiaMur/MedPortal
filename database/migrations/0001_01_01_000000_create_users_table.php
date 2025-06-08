<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Аутентификация
            $table->string('login')->unique();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // Персональные данные
            $table->string('last_name')->nullable(); // Фамилия
            $table->string('first_name')->nullable(); // Имя
            $table->string('middle_name')->nullable(); // Отчество
            $table->enum('gender', ['male', 'female'])->nullable(); // Пол
            $table->date('birth_date')->nullable(); // Дата рождения

            // Контакты
            $table->string('phone')->nullable(); // Телефон 
            
            // Страховые и идентификационные данные
            $table->string('insurance_number')->nullable(); // Полис ОМС в формате XXX-XXX-XXX-XXXXX
            $table->string('snils')->unique()->nullable(); // СНИЛС в формате XXX-XXX-XXX XX
            
            // Медицинские данные
            $table->text('allergies')->nullable(); // Аллергии
            $table->text('chronic_diseases')->nullable(); // Хронические заболевания

            // Меняется после заполнения профиля
            $table->boolean('is_profile_completed')->default(false);
            
            $table->timestamps();
            $table->softDeletes(); // Для соблюдения GDPR
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};