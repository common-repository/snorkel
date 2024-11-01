<?php
/**
    Utils for the woocommerce integration
    Largley, they convert objects to json-encodable arrays
**/
function product_to_json($product) {
    return array(
        'id' => $product->get_id(),
        'type' => $product->get_type(),
        'name' => $product->get_name(),
        'created_at' => $product->get_date_created()->getTimestamp(),
        'updated_at' => $product->get_date_modified()->getTimestamp(),
        'price' => $product->get_price(),
        'regular_price' => $product->get_regular_price(),
        'sale_price' => $product->get_sale_price(),
        'attributes' => $product->get_attributes()
    );
}
