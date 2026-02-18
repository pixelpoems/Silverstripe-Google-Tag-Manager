<?php

declare(strict_types=1);

namespace CyberDuck\GTM;

/**
 * GTMdata
 *
 * This class creates a persistent container for all dataLayer values and is 
 * used by the GTM class to store and retrieve data.
 *
 * @package silverstripe-google-tag-manager
 * @license MIT License https://github.com/cyber-duck/silverstripe-google-tag-manager/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class GTMdata
{
    /**
     * The Tag Manager dataLayer array of values
     *
     * @since 1.0.0
     *
     * @var array
     */
    private static $data = [];

    /**
     * The datalayer JSON string
     *
     * @since 1.0.0
     */
    private static string $json = '';

    /**
     * The current dataLayer currency e.g EUR
     *
     * @since 1.0.0
     *
     * @var string
     */
    private static $currency;

    /**
     * Push a key value pair to the data array
     *
     * @since 1.0.0
     *
     * @param string $name  DataLayer var name
     * @param mixed  $value DataLayer var value
     */
    public static function pushData($name, $value): void
    {
        self::$data[$name] = $value;
    }

    /**
     * Push an event to the data array
     *
     * @since 1.0.0
     *
     * @param string $name  The event name
     */
    public static function pushEvent($name): void
    {
        self::$data['event'] = $name;
    }

    /**
     * Push a transaction currency the data array
     *
     * @since 1.0.0
     *
     * @param string $code ISO 4217 format currency code e.g. EUR
     */
    public static function pushTransactionCurrency($code): void
    {
        self::$currency = $code;

        self::$data['ecommerce']['currencyCode'] = $code;
    }

    /**
     * Push a product impression to the data array
     *
     * @since 1.0.0
     *
     * @param array $fields An array of item fields
     */
    public static function pushProductImpression(array $fields): void
    {
        $defaults = [
            'item_id'   => '',
            'item_name' => ''
        ];
        self::$data['ecommerce']['impressions'][] = self::getDefaults($fields, $defaults);
    }

    /**
     * Push a product promotional impression to the data array
     *
     * @since 1.0.0
     *
     * @param array $fields An array of item fields
     */
    public static function pushProductPromoImpression(array $fields): void
    {
        $defaults = [
            'item_id'   => '',
            'item_name' => ''
        ];
        self::$data['ecommerce']['promoView']['promotions'][] = self::getDefaults($fields, $defaults);
    }

    /**
     * Push a product detail view fields to the data array
     *
     * @since 1.0.0
     *
     * @param array $fields An array of a purchase item fields
     */
    public static function pushProductDetail(array $fields): void
    {
        $defaults = [
            'item_id'   => '',
            'item_name' => ''
        ];
        self::$data['ecommerce']['detail']['items'][] = self::getDefaults($fields, $defaults);
    }

    /**
     * Push a cart add action to the data array
     *
     * @since 1.0.0
     *
     * @param array $fields An array of item fields
     */
    public static function pushAddToCart(array $fields): void
    {
        self::pushCartAction('add', 'addToCart', $fields);
    }

    /**
     * Push a cart remove action to the data array
     *
     * @since 1.0.0
     *
     * @param array $fields An array of item fields
     */
    public static function pushRemoveFromCart(array $fields): void
    {
        self::pushCartAction('remove', 'removeFromCart', $fields);
    }

    /**
     * Push a purchase to the data array
     *
     * @since 1.0.0
     *
     * @param array $fields An array of purchase fields
     */
    public static function pushPurchase(array $fields): void
    {
        $defaults = [
            'transaction_id'           => '',
            'currency' => self::$currency,
            'value'      => '0.00',
            'tax'          => '0.00',
            'shipping'     => '0.00'
        ];
        self::$data['ecommerce']['purchase']['actionField'] = self::getDefaults($fields, $defaults);
    }

    /**
     * Push a purchase item fields to the data array
     *
     * @since 1.0.0
     *
     * @param array $fields An array of a purchase item fields
     */
    public static function pushPurchaseItem(array $fields): void
    {
        $defaults = [
            'item_id'   => '',
            'item_name' => ''
        ];
        self::$data['ecommerce']['purchase']['items'][] = self::getDefaults($fields, $defaults);
    }

    /**
     * Push a refund to the data array
     *
     * @since 1.0.0
     *
     * @param string $id The id of the transaction to refund
     */
    public static function pushRefundTransaction($id): void
    {
        self::$data['ecommerce']['refund']['actionField'] = ['id' => $id];
    }

    /**
     * Push a refund item to the data array
     *
     * @since 1.0.0
     *
     * @param string $id        The id of the transaction
     * @param string $productId The id of the item
     * @param int    $quantity  The quantity to refund
     */
    public static function pushRefundTransactionItem($id, $productId, $quantity): void
    {
        self::pushRefundTransaction($id);

        self::$data['ecommerce']['refund']['products'][] = ['id' => $productId, 'quantity' => $quantity];
    }

    /**
     * Push a cart action to the data array
     *
     * @since 1.0.0
     *
     * @param array $action The cart action
     * @param array $event  The event name of the action
     * @param array $fields An array of item fields
     */
    public static function pushCartAction($action, $event, array $fields): void
    {
        self::pushCurrent();

        $defaults = [
            'item_id'       => '',
            'item_name'     => '',
            'quantity' => 1
        ];
        self::$data['ecommerce'][$action]['items'][] = self::getDefaults($fields, $defaults);

        // add to cart actions require their own event action and push
        self::pushEvent($event);
        self::pushCurrent();
    }

    /**
     * Get the complete formatted dataLayer
     *
     * @since 1.0.0
     */
    public static function getDataLayer(): string
    {
        self::pushCurrent();

        return self::$json;
    }

    /**
     * Compare an array against an array of required fields for a dataLayer property
     *
     * @since 1.0.0
     *
     * @param array $fields   Fields to check
     * @param array $defaults Default fields for this array
     */
    private static function getDefaults(array $fields, array $defaults): array
    {
        foreach ($defaults as $key => $value) {
            if (!isset($fields[$key])) {
                $fields[$key] = $value;
            }
        }

        return $fields;
    }

    /**
     * Create a dataLayer push from the current data array
     *
     * @since 1.0.0
     */
    private static function pushCurrent(): void
    {
        if (!empty(self::$data)) {
            self::$json .= 'dataLayer.push('.json_encode(self::$data, JSON_PRETTY_PRINT).');';
            self::$data = [];
        }
    }

    /**
     * Private constructor
     *
     * @since version 1.0.0
     **/
    private function __construct(){}

    /**
     * Private clone
     *
     * @since version 1.0.0
     **/
    private function __clone(){}
}
