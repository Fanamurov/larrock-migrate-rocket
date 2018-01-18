<?php

namespace Larrock\ComponentMigrateRocket;

use Larrock\Core\Component;
use Larrock\Core\Helpers\FormBuilder\FormInput;
use Larrock\Core\Helpers\FormBuilder\FormSelectKey;
use Larrock\Core\Helpers\FormBuilder\FormTextarea;
use Larrock\Core\Models\Seo;
use Larrock\ComponentAdminSeo\Facades\LarrockSeo;

class MigrateRocketComponent extends Component
{
    public function __construct()
    {
        $this->name = $this->table = 'seo';
        $this->title = 'SEO';
        $this->description = 'Кастомные seo-настройки материалов';
        $this->model = \config('larrock.models.seo', Seo::class);
        $this->addRows();
    }

    protected function addRows()
    {
        $row = new FormInput('seo_title', 'Title');
        $this->rows['seo_title'] = $row->setValid('max:255|required')->setTypo()->setInTableAdmin();

        $row = new FormTextarea('seo_description', 'Description');
        $this->rows['seo_description'] = $row->setTypo()->setInTableAdmin()->setNotEditor();

        $row = new FormTextarea('seo_keywords', 'Keywords');
        $this->rows['seo_keywords'] = $row->setNotEditor();

        $row = new FormInput('seo_id_connect', 'ID материала (опционально)');
        $this->rows['seo_id_connect'] = $row->setInTableAdmin()->setCssClassGroup('uk-width-1-3');

        $row = new FormInput('seo_url_connect', 'URL материала (опционально)');
        $this->rows['seo_url_connect'] = $row->setInTableAdmin()->setCssClassGroup('uk-width-1-3');

        $row = new FormSelectKey('seo_type_connect', 'Тип seo');
        $this->rows['seo_type_connect'] = $row->setOptions([
            'postfix_global' => 'Постфикс для всего сайта',
            'prefix_global' => 'Префикс для всего сайта',
            'catalog_category_postfix' => 'Постфикс для раздела каталога',
            'catalog_category_prefix' => 'Префикс для раздела каталога',
            'catalog_item_postfix' => 'Постфикс для страницы товара каталога',
            'catalog_item_prefix' => 'Префикс для страницы товара каталога',
            'catalog' => 'Материал каталога',
            'page' => 'Материал статичной страницы',
            'feed' => 'Материал ленты',
            'category' => 'Материал раздела',
            'url' => 'URL',
        ])->setCssClassGroup('uk-width-1-3')->setInTableAdmin();

        return $this;
    }

    public function renderAdminMenu()
    {
        $count = \Cache::remember('count-data-admin-'. LarrockSeo::getName(), 1440, function(){
            return LarrockSeo::getModel()->count(['id']);
        });
        return view('larrock::admin.sectionmenu.types.default', ['count' => $count, 'app' => LarrockSeo::getConfig(), 'url' => '/admin/'. LarrockSeo::getName()]);
    }
}