<?php
/* Copyright (C) 2024 WebAuxilium <https://webauxilium.com> */

// Protection contre l'accès direct
if (!defined('NOREQUIREUSER')) die('Forbidden');

header('Content-type: text/css');
?>

.stockorderlist-positive {
    color: #00AA00;
    font-weight: bold;
}

.stockorderlist-negative {
    color: #CC0000;
    font-weight: bold;
}

.stockorderlist-neutral {
    color: #666666;
}

.stockorderlist-table th {
    white-space: nowrap;
}

.stockorderlist-actions {
    min-width: 120px;
}

.stockorderlist-actions a {
    margin-right: 5px;
}

.stockorderlist-qty {
    text-align: right;
    font-family: monospace;
}

.stockorderlist-search-form {
    margin-bottom: 20px;
    padding: 10px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
}

.stockorderlist-summary {
    margin-bottom: 15px;
    padding: 10px;
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}