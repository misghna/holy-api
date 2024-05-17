<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DictionarySeeder extends Seeder
{
    public function run()
    {
        $data = [
            // English 
            ['key' => 'tenant', 'value' => 'Tenant', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'document', 'value' => 'Documents', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'language', 'value' => 'Language', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'settings', 'value' => 'Settings', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'languages', 'value' => 'Languages', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'theme_mode', 'value' => 'Theme Mode', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'page_config', 'value' => 'Page Config', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'theme_color', 'value' => 'Theme Color', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'search_title', 'value' => 'Search', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'translations', 'value' => 'Translations', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'admin_settings', 'value' => 'Admin Settings', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'other_settings', 'value' => 'Other Settings', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'content_manager', 'value' => 'Contents', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'action_menu_save', 'value' => 'Save', 'language' => 'english', 'tenant_id' => 1, 'updated_by' => 1],

            // Spanish 
            ['key' => 'tenant', 'value' => 'Inquilino', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'document', 'value' => 'Documentos', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'language', 'value' => 'Idioma', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'settings', 'value' => 'Configuraciones', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'languages', 'value' => 'Idiomas', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'theme_mode', 'value' => 'Modo Temático', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'page_config', 'value' => 'Configuración de Página', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'theme_color', 'value' => 'Color del Tema', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'search_title', 'value' => 'Buscar', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'translations', 'value' => 'Traducciones', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'admin_settings', 'value' => 'Configuraciones del Administrador', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'other_settings', 'value' => 'Otras Configuraciones', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'content_manager', 'value' => 'Gestor de Contenidos', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
            ['key' => 'action_menu_save', 'value' => 'Guardar', 'language' => 'spanish', 'tenant_id' => 1, 'updated_by' => 1],
        ];

        DB::table('dictionary')->insert($data);
    }
}
