<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Eg_productexport extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'eg_productexport';
        $this->tab = 'export';
        $this->version = '1.0.0';
        $this->author = 'Evolutive Group';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('EG Product Export');
        $this->description = $this->l('export produt details');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {

        return parent::install() ;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }
    
    /**
     * export les details de commande
     * @void
     */
    private function export_details(){
        $id_lang = (int)Context::getContext()->language->id;
        $start = 0;
        $limit = 100000;
        $order_by = 'id_product';
        $order_way = 'DESC';
        $id_category = false;
        $only_active = true;
        $context = null;

        $file = 'productdetails.csv';
       
        $f=fopen('uploads_products/'.$file, 'w');
       
        $all_products = Product::getProducts($id_lang, $start, $limit, $order_by, $order_way, $id_category,
            $only_active, $context);

        fwrite($f, "Id_product;Nom;reference;description;descriptioncourte;caractéristique,caracatéristqiue_value,imageurl \r\n");
        $data[0]=array("Id_product","Nom","reference","description","descriptioncourte","caractéristique","caracatéristqiue_value","imageurl");
        foreach($all_products as $product){
             
            $productObj = new Product((int)$product['id_product'] ,$id_lang , 1 );
           
            /** get all features*/

            $features = $productObj->getFeatures();
            
            foreach ($features as $feature){
                 $feature_name = Feature::getFeature($id_lang, $feature["id_feature"]);
                 $featurevalues = FeatureValue::getFeatureValuesWithLang($id_lang,$feature["id_feature"] , false);       
            }
           
             /** get all images */
            $imgs = $productObj->getImages(Context::getContext()->language->id  , null);
            $img  = $productObj->getCover($product['id_product']);
            $link = new Link();
            //var_dump($productObj->name);die;
            $img_url = $link->getImageLink(isset($productObj->link_rewrite) ? $productObj->link_rewrite : $productObj->name , (int)$img['id_image']);
           
            $image_list = $img_url ;

            foreach($imgs  as $image){
                $img_url2 = $link->getImageLink(isset($productObj->link_rewrite) ? $productObj->link_rewrite : $productObj->name, (int)$image['id_image']);
                  if($img_url !== $img_url2 ){
                    $image_list .=",".$img_url2 ;
                  }
            }
            
            $id_produit = $productObj->id;
            $name = $productObj->name ;
            $reference = $productObj->reference ;
            $description_short = $productObj->description_short;
            $description = $productObj->description ; 
            
            $feature_ch = '';
            foreach($featurevalues as $featurevalue){
                $feature_ch .='-'.$featurevalue["value"].';';
            }
            
            $data[$productObj->id] = 
                array($id_produit,$name,$reference,$description,$description_short,$feature_name["name"],$feature_ch,$image_list);
            
        }  
        
            $fp = fopen(dirname(__FILE__).'/uploads_products/'.$file, 'w');
            
            foreach ($data as $fields) {
                if(is_array($fields)){
                    fputcsv($fp, $fields);
                }
            }
            fclose($fp);
        $filesize = filesize(dirname(__FILE__).'/uploads_products/'.$file);

        header('Content-Type: text/csv; charset=utf-8');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="'.$file.'"');
        header('Content-Length: '.$filesize);
        readfile(dirname(__FILE__).'/uploads_products/'.$file);
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitEg_productexportModule')) == true) {
            $this->export_details();
            $this->postProcess();
        }
        
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output;
    }

     
}
