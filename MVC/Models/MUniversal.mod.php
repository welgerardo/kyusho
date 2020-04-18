<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MWorkshops
 *
 * @author Gerardo
 */
class MUniversal {

    private $Db;
    private $Core;
    private $Reg;
    private static $SAll;
    private static $SSingle;
    private static $SCategories;
    private static $lang;

    function __construct(MGPDINAMIC $registry, $object) {

        $this->Reg = $registry;

        $this->Core = $this->Db = $registry->getObject("core");

        self::$lang = $registry->get_language();

        $settings = json_decode($object, TRUE);

        if (is_array($settings)) {

            self::$SCategories = (empty($settings['categories'])) ? NULL : $settings['categories'];
            self::$SAll = (empty($settings['all'])) ? NULL : $settings['all'];
            self::$SSingle = (empty($settings['single'])) ? NULL : $settings['single'];
        }
    }

    public function getProductPageUrl() {

        return self::$Sproducts['link'];
    }

    /**
     * 
     * @param type $params
     * @return type
     */
    public function itens($params = NULL) {

        $result = NULL;

        if (is_null($params) || is_array($params)) {            

            try {
                
                $result = $this->Db->make_call(self::$SAll['spall'], $params);
                
            } catch (Exception $ex) {

                return $result;
            }
        }

        if (is_array($result)) {

            foreach ($result as $key => $value) {

                $result[$key]['url'] = _CUR_URL . $this->singleItemUrl($value) . $this->singleItemQueryString($value);
            }
        }


        return $result;
    }

    /**
     * 
     * @param type $params
     * @return type
     */
    public function item($params = NULL) {

        $result = NULL;

        if (is_null($params) || is_array($params)) {

            try {
                $result = $this->Db->make_call(self::$SSingle['spsingle'], $params);
            } catch (Exception $ex) {

                return $result;
            }
        }

        if (is_array($result)) {

            $result[0]['url'] = _CUR_URL . $this->singleItemUrl($result[0]) . $this->singleItemQueryString($result[0]);
        }


        return $result[0];
    }

    /**
     * 
     * @param array $product
     * @return string
     */
    private function singleItemUrl(array $product) {

        $url = NULL;

        foreach (self::$SSingle['param'] as $value) {

            $url .= "/" . $this->Core->clean_space($product[$value]);
        }

        return "/".self::$SSingle['link'].$url;
    }

    private function singleItemQueryString(array $product) {

        $query_string = NULL;

        if (is_array(self::$SSingle['querystring'])) {

            foreach (self::$SSingle['querystring'] as $key => $value) {

                $query_string .= "&" . $key . "=" . $product[$value];
            }

            return "?" . trim($query_string, "&");
        }

        return $query_string;
    }

    public function categories($params = NULL) {

        foreach (self::$Scategories as $procedure_key => $procedure) {

            try {

                $result = $this->Db->make_call($procedure, $params);
            } catch (Exception $exc) {

                return NULL;
            }

            if (is_array($result)) {

                foreach ($result as $key => $value) {

                    $categories[$procedure_key][$value['categoria']]['id'] = $value['toke'];
                    $categories[$procedure_key][$value['categoria']]['url'] = _CUR_URL . "/" . self::$Sproducts['link'] . "/" . $value['categoria'];

                    if ($value['subcategoria']) {

                        $categories[$procedure_key][$value['categoria']]['subcategories'] = $this->makeSubcategories($value['subcategoria'], $categories[$procedure_key][$value['categoria']]['url']);
                    }
                }
            } else {

                return NULL;
            }
        }

        return $categories;
    }

    private function makeSubcategories($subcat, $cat_url) {

        $subcategories = NULL;
        $subcat_data = explode(",", $subcat);

        if (is_array($subcat_data)) {

            foreach ($subcat_data as $value) {

                $subcategories[$value]['id'] = (empty($value['toke'])) ? NULL : $value['toke'];
                $subcategories[$value]['url'] = $cat_url . "/" . $value;
            }
        }

        return $subcategories;
    }

}
