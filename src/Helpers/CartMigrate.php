<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use LarrockCart;
use LarrockCatalog;
use Illuminate\Http\Request;
use Larrock\Core\Models\Link;
use Larrock\ComponentCatalog\Models\Param;
use Larrock\Core\Traits\AdminMethodsStore;
use Larrock\ComponentMigrateRocket\Models\MigrateDB;
use Larrock\ComponentMigrateRocket\Exceptions\MigrateRocketCartItemException;

class CartMigrate
{
    use AdminMethodsStore;

    public function __construct()
    {
        $this->allow_redirect = null;
    }

    /**
     * @throws MigrateRocketCartItemException
     */
    public function import()
    {
        $request = new Request();
        $migrateDBLog = new MigrateDBLog();

        $this->config = LarrockCart::getConfig();

        $export_data = \DB::connection('migrate')->table('cart')->get();

        foreach ($export_data as $item) {
            echo '.';
            if (! $migrateDBLog->getNewIdByOldId($item->id, 'cart')) {
                $add_to_request = [
                    'order_id' => $item->order_id,
                    'user' => $migrateDBLog->getNewIdByOldId($item->user, 'users'),
                    'items' => $this->parseItems($item->items, $item->order_id),
                    'address' => $item->user_address,
                    'fio' => $item->user_name,
                    'tel' => $item->user_phone,
                    'email' => $item->user_email,
                    'cost' => $item->cost,
                    'cost_discount' => $item->cost_discount,
                    'cost_delivery' => $this->parseCostDelivery($item->delivery),
                    //'discount' => '',
                    'kupon' => $item->kupon,
                    'status_order' => $item->status_order,
                    'status_pay' => $item->status_pay,
                    'method_delivery' => $this->parseMethodDelivery($item->delivery),
                    'comment' => $item->user_comment,
                    'comment_admin' => $item->comment,
                    //'pay_at' => '',
                    //'invoiceId' => '',
                    //'payment_data' => '',
                    'updated_at' => $item->date,
                    'created_at' => $item->date,
                    //'discount_id' => '',
                    'active' => 1,
                ];

                //dd($add_to_request);

                if ($add_to_request['items'] && ! empty($add_to_request['items'])) {
                    $request = $request->merge($add_to_request);
                    if ($store = $this->store($request)) {
                        //Ведем лог изменений id
                        $migrateDBLog->log($item->id, $store->id, 'cart');
                    }
                }
            }
        }
    }

    /**
     * Получение товаров в заказе.
     * @param $data
     * @param $order_id
     * @return \Illuminate\Support\Collection
     * @throws MigrateRocketCartItemException
     */
    protected function parseItems($data, $order_id)
    {
        if (@unserialize($data) !== false) {
            \Cart::instance('temp')->destroy();

            $data = unserialize($data);
            if (\is_array($data)) {
                foreach ($data as $item) {
                    $options = [];
                    $id_modify = null;

                    if (! empty($item['modify']) && \is_array($item['modify'])) {
                        foreach ($item['modify'] as $modify) {
                            $item['cost'] = (float) $modify['cost'];
                            $options['costValue']['title'] = $modify['value'];
                            $options['costValue']['cost'] = $item['cost'];
                        }
                    }

                    if (empty($item['cart_count'])) {
                        $item['cart_count'] = 1;
                    }

                    if ($tovar = MigrateDB::whereOldId($item['id'])->whereTableName('catalog')->first()) {
                        //Обработка заказанной модификации товара
                        if (! empty($item['modify']) && \is_array($item['modify'])) {
                            foreach ($item['modify'] as $modify) {
                                if ($link = Link::whereCost($item['cost'])->whereModelParent(LarrockCatalog::getModelName())
                                    ->whereModelChild(Param::class)->whereIdParent($tovar->new_id)->first()) {
                                    $id_modify = $options['costValue']['id'] = $link->id;
                                }
                            }
                        }

                        \Cart::instance('temp')->add($tovar->new_id.$id_modify, $item['title'], $item['cart_count'], $item['cost'], $options)
                            ->associate(LarrockCatalog::getModelName());
                    } else {
                        if (! empty($item['modify']) && \is_array($item['modify'])) {
                            foreach ($item['modify'] as $modify) {
                                $item['title'] .= ' '.$modify['value'];
                            }
                        }
                        \Cart::instance('temp')->add($item['id'].$id_modify, $item['title'], $item['cart_count'], $item['cost'], $options);
                    }
                }
            } else {
                throw new MigrateRocketCartItemException('У заказа #'.$order_id.' не удалось получить товары');
            }

            /* @noinspection PhpVoidFunctionResultUsedInspection */
            return \Cart::instance('temp')->content();
        }
        \Cart::instance('temp')->destroy();

        return null;
        throw new MigrateRocketCartItemException('У заказа #'.$order_id.' не удалось получить товары');
    }

    /**
     * Получение цены доставки.
     * @param $data
     * @return float|null
     */
    protected function parseCostDelivery($data)
    {
        if (@unserialize($data) !== false) {
            $data = unserialize($data);
            if (\is_array($data) && isset($data[0]['cost'])) {
                return (float) $data[0]['cost'];
            }
        }

        return null;
    }

    /**
     * Получение метода доставки.
     * @param $data
     * @return null
     */
    protected function parseMethodDelivery($data)
    {
        if (@unserialize($data) !== false) {
            $data = unserialize($data);
            if (\is_array($data) && isset($data[0]['title'])) {
                return $data[0]['title'];
            }
        }

        return null;
    }
}
