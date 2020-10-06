<?php
@ini_set('max_execution_time', -1);
@ini_set('memory_limit', '2G');
include('../../config/config.inc.php');
include('../../init.php');

$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'orders`
		WHERE valid = 1 AND date_add BETWEEN "' . Configuration::get('EG_EXPORT_ORDERS_START_DATE') . ' 00:00:00" AND "' . Configuration::get('EG_EXPORT_ORDERS_END_DATE') . ' 23:59:59"
		ORDER BY date_add DESC';

$orders = Db::getInstance()->executeS($sql);
$file = 'Commandes_'.date("Ymd").'_'.date("His").'.csv';

$f=fopen(dirname(__FILE__). '/export/'.$file, 'w');
fwrite($f, "DATE;ID Client;Nom;Prénom;ID Commande;Ref Commande;N°Facture;Ref produit;Libelle;Quantité;Prix HT;CA HT;Taux de TVA;Montant TVA;Frais de port HT;TVA Frais de port;Montant TVA Frais de port;Réduction HT;TVA Réduction;Total TTC;Type \r\n");

foreach ($orders as $order)
{
    $orderObj = new Order($order["id_order"]);

    $order_date = date("d/m/Y", strtotime($orderObj->date_add));
    $order_customer = $orderObj->id_customer;

    $customer = new Customer ($orderObj->id_customer);

    $customer_lastname = $customer->lastname;
    $customer_firstname = $customer->firstname;

    $order_id = $orderObj->id;
    $order_reference = $orderObj->reference;

    $order_invoice = Db::getInstance()->getRow('
            SELECT * FROM ' . _DB_PREFIX_ . 'order_invoice o
            WHERE o.`id_order` = ' . $orderObj->id . '');

    $invoice_name = getInvoiceFilename($order_invoice['number'], $order_invoice['date_add']);

    // products line
    $products = $orderObj->getProducts();

    foreach ($products as $product)
    {
//        print_r($product);die;
        $product_reference = $product['product_reference'];
        $product_name = $product['product_name'];
        $product_quantity = $product['product_quantity'];
        $unit_price_tax_excl = Tools::ps_round($product['unit_price_tax_excl'],2);
        $total_price_tax_excl = Tools::ps_round($product['total_price_tax_excl'],2);
        $tax_rate = $product['tax_rate'];
        $tax = Tools::ps_round($product['total_price_tax_incl'] - $product['total_price_tax_excl'],2);
        $total_price_tax_incl = Tools::ps_round($product['total_price_tax_incl'],2);

        fwrite($f, "$order_date;$order_customer;$customer_lastname;$customer_firstname;$order_id;$order_reference;$invoice_name;$product_reference;$product_name;$product_quantity;$unit_price_tax_excl;$total_price_tax_excl;$tax_rate;$tax;;;;;;$total_price_tax_incl;Produit \r\n");

    }

    $carrierObj = new Carrier($orderObj->id_carrier,Configuration::get("PS_LANG_DEFAULT"));
//    print_r($carrierObj);die;
    $total_shipping_tax_excl = Tools::ps_round($order['total_shipping_tax_excl'],2);
    $carrier_tax_rate = $order['carrier_tax_rate'];
    $carrier_tax = Tools::ps_round($order['total_shipping_tax_incl'] - $order ['total_shipping_tax_excl'],2);
    $total_shipping_tax_incl = Tools::ps_round($order['total_shipping_tax_incl'],2);

    fwrite($f, "$order_date;$order_customer;$customer_lastname;$customer_firstname;$order_id;$order_reference;$invoice_name;;$carrierObj->name;;;;;;$total_shipping_tax_excl;$carrier_tax_rate;$carrier_tax;;;$total_shipping_tax_incl;Transporteur \r\n");

    $discounts = $orderObj->getDiscounts();
//    print_r($discounts);echo '<br>';
    foreach ($discounts as $discount)
    {
        $cart_rule = new CartRule($discount['id_cart_rule']);
        $discount_name = $discount['name'];
        $value_tax_excl = '-'.Tools::ps_round($discount['value_tax_excl'],2);
        $value = '-'.Tools::ps_round($discount['value'],2);
        $tva_reduction = $discount['value'] - $discount['value_tax_excl'];
        fwrite($f, "$order_date;$order_customer;$customer_lastname;$customer_firstname;$order_id;$order_reference;$invoice_name;;$discount_name;;;;;;;;;$value_tax_excl;$tva_reduction;$value;Réduction \r\n");
    }




}

$filesize = filesize(dirname(__FILE__). '/export/'.$file);

header('Content-Type: text/csv; charset=utf-8');
header('Cache-Control: no-store, no-cache');
header('Content-Disposition: attachment; filename="'.$file.'"');
header('Content-Length: '.$filesize);
readfile(dirname(__FILE__) . '/export/'.$file);


function getInvoiceFilename($order_invoice_number, $date_add)
{
    $id_lang = Context::getContext()->language->id;
    $id_shop = (int)Context::getContext()->shop->id;
    $format = '%1$s%2$06d';

    if (Configuration::get('PS_INVOICE_USE_YEAR')) {
        $format = Configuration::get('PS_INVOICE_YEAR_POS') ? '%1$s%3$s-%2$06d' : '%1$s%2$06d-%3$s';
    }

    return sprintf(
        $format,
        Configuration::get('PS_INVOICE_PREFIX', $id_lang, null, $id_shop),
        $order_invoice_number,
        date('Y', strtotime($date_add))
    );
}
