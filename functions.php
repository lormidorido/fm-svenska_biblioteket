<?php
/**
 * Bygger sorteringsparameter för FileMaker API
 * 
 * @param string $field Fältnamn att sortera på
 * @param string $order 'ascend' eller 'descend'
 * @return array Sorteringsarray som FileMaker förstår
 */
function buildSortParams($field = '', $order = 'ascend') {
    if (empty($field)) return [];

    $order = strtolower($order);
    if (!in_array($order, ['ascend', 'descend'])) {
        $order = 'ascend';
    }

    return [[
        'fieldName' => $field,
        'sortOrder' => $order
    ]];
}
?>
