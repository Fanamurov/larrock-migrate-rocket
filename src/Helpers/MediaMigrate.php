<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Larrock\Core\Traits\AdminMethodsStore;

/**
 * Линкование фото и файлов к материалам
 * Используется как вспомогательный метод к методам импорта контента.
 *
 * Class MediaMigrate
 */
class MediaMigrate
{
    use AdminMethodsStore;

    public function __construct()
    {
        $this->allow_redirect = null;
    }

    /**
     * @param $content
     * @param $id_connect
     * @param $type_connect
     */
    public function attach($content, $id_connect, $type_connect)
    {
        $this->attachImages($content, $id_connect, $type_connect);
        $this->attachFiles($content, $id_connect, $type_connect);
    }

    public function attachImages($content, $id_connect, $type_connect)
    {
        $export_data = \DB::connection('migrate')->table('images')
            ->where('type_connect', '=', $type_connect)
            ->where('id_connect', '=', $id_connect)->get();

        foreach ($export_data as $media) {
            $src = base_path('export/'.$type_connect.'/big/'.$media->title);
            if (file_exists($src)) {
                echo 'I';
                $content->addMedia($src)->preservingOriginal()->withCustomProperties([
                    'alt' => 'photo', 'gallery' => $media->param,
                ])->toMediaCollection('images');
            } else {
                \Log::error('Файла '.$src.' не обнаружено');
            }
        }

        return true;
    }

    public function attachFiles($content, $id_connect, $type_connect)
    {
        $export_data = \DB::connection('migrate')->table('files')
            ->where('type_connect', '=', $type_connect)
            ->where('id_connect', '=', $id_connect)->get();

        foreach ($export_data as $media) {
            $src = base_path('export/'.$type_connect.'/big/'.$media->title);
            if (file_exists($src)) {
                echo 'F';
                $content->addMedia($src)->preservingOriginal()->withCustomProperties([
                    'alt' => 'file', 'gallery' => $media->param,
                ])->toMediaCollection('files');
            } else {
                \Log::error('Файла '.$src.' не обнаружено');
            }
        }

        return true;
    }
}
