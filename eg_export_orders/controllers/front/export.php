<?php

class Eg_export_ordersExportModuleFrontController extends \ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->export();

    }

    private function export()
    {
        $sql ='SELECT * FROM  `'._DB_PREFIX_.'order_detail`';
        $order_details = Db::getInstance()->executeS($sql);
//        print_r($order_details);die;

        $orders_final = [];
        foreach ($order_details as $od) {
//            $sql_order = 'SELECT o.`date_add`, o.`id_customer`  FROM  `'._DB_PREFIX_.'orders` o WHERE o.`id_order` = '.$od['id_order'].'';
//            $related_order = Db::getInstance()->getRow($sql_order);


            $sql_t = 'SELECT o.`id_order`, o.`date_add`, o.`id_customer`, c.`firstname`, c.`lastname`, o.`reference` FROM `' . _DB_PREFIX_ . 'orders` o 
                       LEFT JOIN `'._DB_PREFIX_.'customer` c ON (o.`id_customer` = c.`id_customer`)
                       WHERE o.`id_order` = '.$od['id_order'].'';
            $res = Db::getInstance()->getRow($sql_t);
//            print_r($res);die;
            $order_invoice = Db::getInstance()->getRow('
            SELECT * FROM ' . _DB_PREFIX_ . 'order_invoice o
            WHERE o.`id_order` = ' . $od['id_order'] . '');

//            $invoice_name = $this->getInvoiceFilename($order_invoice['number'], $order_invoice['date_add']);
//            print_r($invoice_name);die;

            $date_order = $res['date_add'];
            $id_customer = $res['id_customer'];
            $firstname = $res['firstname'];
            $lastname = $res['lastname'];
            $id_order = $res['id_order'];
            $reference = $res['reference'];
            /*
            $product_reference = $od['product_reference'];
            $product_name = $od['product_name'];
            $product_quantity = $od['product_quantity'];
            $price_ht = $od['total_price_tax_excl'];
            */



            $order['date_add'] = $date_order;
            $order['id_customer'] = $id_customer;
            $order['firstname'] = $firstname;
            $order['lastname'] = $lastname;
            $order['id_order'] = $id_order;
            $order['reference'] = $reference;

            /*
            $order['invoice_number'] = $invoice_name;
            $order['product_reference'] = $product_reference;
            $order['product_name'] = $product_name;
            $order['product_quantity'] = $product_quantity;
            $order['price_ht'] = $price_ht;
            */


            $orders_final [] = $order;


        }

//         print_r($orders_final);die;

        $arrayexport=array();
        $arrayexport[0][]="DATE";
        $arrayexport[0][]="ID Client";
        $arrayexport[0][]="Nom";
        $arrayexport[0][]='Prénom';
        $arrayexport[0][]='ID Commande';
        $arrayexport[0][]='Ref Commande';
        /*
        $arrayexport[0][]='N°Facture';
        $arrayexport[0][]='Ref produit';
        $arrayexport[0][]='Nom produit';
        $arrayexport[0][]='Quantité';
        $arrayexport[0][]='Prix HT';
        $arrayexport[0][]='Prix HT avec remise';
        $arrayexport[0][]='CA HT';
        $arrayexport[0][]='Frais de port HT';
        $arrayexport[0][]='Taux de TVA';
        $arrayexport[0][]='Montant TVA';
        $arrayexport[0][]='Total TTC';
        */



        foreach ($orders_final as $key => $o_final){

//            print_r($o_final);die;
            $arrayexport[$key + 1][]=$o_final['date_add'];
            $arrayexport[$key + 1][]=$o_final['id_customer'];
            $arrayexport[$key + 1][]=$o_final['firstname'];
            $arrayexport[$key + 1][]=$o_final['lastname'];
            $arrayexport[$key + 1][]=$o_final['id_order'];
            $arrayexport[$key + 1][]=$o_final['reference'];

//            $arrayexport[$key + 1][]=$o_final['invoice_number'];
//            $arrayexport[$key + 1][]=$o_final['product_reference'];
//            $arrayexport[$key + 1][]=$o_final['product_name'];
//            $arrayexport[$key + 1][]=$o_final['product_quantity'];
//            $arrayexport[$key + 1][]=$o_final['price_ht'];
//            $arrayexport[$key + 1][]=$vu_final['manufacturer'];
//            $arrayexport[$key + 1][]=$vu_final['manufacturer'];
//            $arrayexport[$key + 1][]=$vu_final['manufacturer'];
//            $arrayexport[$key + 1][]=$vu_final['manufacturer'];
//            $arrayexport[$key + 1][]=$vu_final['manufacturer'];
//            $arrayexport[$key + 1][]=$vu_final['manufacturer'];
//            $arrayexport[$key + 1][]=$vu_final['manufacturer'];
//            $arrayexport[$key + 1][]=$vu_final['manufacturer'];
//            $arrayexport[$key + 1][]=$vu_final['manufacturer'];





        }
// output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=orders.csv');

// create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
//$fp = fopen('export-catalogue.csv', 'w+');

        foreach ($arrayexport as $fields) {
            fputcsv($output, $fields,';');
        }

        fclose($output);
    }


//    function getInvoiceFilename($order_invoice_number, $date_add)
//    {
//        $id_lang = Context::getContext()->language->id;
//        $id_shop = (int)Context::getContext()->shop->id;
//        $format = '%1$s%2$06d';
//
//        if (Configuration::get('PS_INVOICE_USE_YEAR')) {
//            $format = Configuration::get('PS_INVOICE_YEAR_POS') ? '%1$s%3$s-%2$06d' : '%1$s%2$06d-%3$s';
//        }
//
//        return sprintf(
//            $format,
//            Configuration::get('PS_INVOICE_PREFIX', $id_lang, null, $id_shop),
//            $order_invoice_number,
//            date('Y', strtotime($date_add))
//        );
//    }



}