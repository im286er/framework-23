<?php
// [$QueryPHP] The PHP Framework For Code Poem As Free As Wind. <Query Yet Simple>
// ©2010-2017 http://queryphp.com All rights reserved.
namespace queryyetsimple\i18n\translations;

<<<queryphp
##########################################################
#   ____                          ______  _   _ ______   #
#  /     \       ___  _ __  _   _ | ___ \| | | || ___ \  #
# |   (  ||(_)| / _ \| '__|| | | || |_/ /| |_| || |_/ /  #
#  \____/ |___||  __/| |   | |_| ||  __/ |  _  ||  __/   #
#       \__   | \___ |_|    \__  || |    | | | || |      #
#     Query Yet Simple      __/  |\_|    |_| |_|\_|      #
#                          |___ /  Since 2010.10.03      #
##########################################################
queryphp;

use queryyetsimple\i18n\entry;

/**
 * 翻译语言
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.09.18
 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/pomo/
 * @version 1.0
 */
abstract class translations {
    
    /**
     * prop
     *
     * @var array
     */
    var $entries = array ();
    var $headers = array ();
    
    /**
     * Add entry to the PO structure
     *
     * @param
     *            array|Translation_Entry &$entry
     * @return bool true on success, false if the entry doesn't have a key
     */
    function add_entry($entry) {
        if (is_array ( $entry )) {
            $entry = new entry ( $entry );
        }
        $key = $entry->key ();
        if (false === $key)
            return false;
        $this->entries [$key] = &$entry;
        return true;
    }
    
    /**
     *
     * @param array|Translation_Entry $entry            
     * @return bool
     */
    function add_entry_or_merge($entry) {
        if (is_array ( $entry )) {
            $entry = new entry ( $entry );
        }
        $key = $entry->key ();
        if (false === $key)
            return false;
        if (isset ( $this->entries [$key] ))
            $this->entries [$key]->merge_with ( $entry );
        else
            $this->entries [$key] = &$entry;
        return true;
    }
    
    /**
     * Sets $header PO header to $value
     *
     * If the header already exists, it will be overwritten
     *
     * TODO: this should be out of this class, it is gettext specific
     *
     * @param string $header
     *            header name, without trailing :
     * @param string $value
     *            header value, without trailing \n
     */
    function set_header($header, $value) {
        $this->headers [$header] = $value;
    }
    
    /**
     *
     * @param array $headers            
     */
    function set_headers($headers) {
        foreach ( $headers as $header => $value ) {
            $this->set_header ( $header, $value );
        }
    }
    
    /**
     *
     * @param string $header            
     */
    function get_header($header) {
        return isset ( $this->headers [$header] ) ? $this->headers [$header] : false;
    }
    
    /**
     *
     * @param Translation_Entry $entry            
     */
    function translate_entry(&$entry) {
        $key = $entry->key ();
        return isset ( $this->entries [$key] ) ? $this->entries [$key] : false;
    }
    
    /**
     *
     * @param string $singular            
     * @param string $context            
     * @return string
     */
    function translate($singular, $context = null) {
        $entry = new entry ( array (
                'singular' => $singular,
                'context' => $context 
        ) );
        $translated = $this->translate_entry ( $entry );
        return ($translated && ! empty ( $translated->translations )) ? $translated->translations [0] : $singular;
    }
    
    /**
     * Given the number of items, returns the 0-based index of the plural form to use
     *
     * Here, in the base Translations class, the common logic for English is implemented:
     * 0 if there is one element, 1 otherwise
     *
     * This function should be overridden by the sub-classes. For example MO/PO can derive the logic
     * from their headers.
     *
     * @param integer $count
     *            number of items
     */
    function select_plural_form($count) {
        return 1 == $count ? 0 : 1;
    }
    
    /**
     *
     * @return int
     */
    function get_plural_forms_count() {
        return 2;
    }
    
    /**
     *
     * @param string $singular            
     * @param string $plural            
     * @param int $count            
     * @param string $context            
     */
    function translate_plural($singular, $plural, $count, $context = null) {
        $entry = new entry ( array (
                'singular' => $singular,
                'plural' => $plural,
                'context' => $context 
        ) );
        $translated = $this->translate_entry ( $entry );
        $index = $this->select_plural_form ( $count );
        $total_plural_forms = $this->get_plural_forms_count ();
        if ($translated && 0 <= $index && $index < $total_plural_forms && is_array ( $translated->translations ) && isset ( $translated->translations [$index] ))
            return $translated->translations [$index];
        else
            return 1 == $count ? $singular : $plural;
    }
    
    /**
     * Merge $other in the current object.
     *
     * @param
     *            Object &$other Another Translation object, whose translations will be merged in this one
     * @return void
     *
     */
    function merge_with(&$other) {
        foreach ( $other->entries as $entry ) {
            $this->entries [$entry->key ()] = $entry;
        }
    }
    
    /**
     *
     * @param object $other            
     */
    function merge_originals_with(&$other) {
        foreach ( $other->entries as $entry ) {
            if (! isset ( $this->entries [$entry->key ()] ))
                $this->entries [$entry->key ()] = $entry;
            else
                $this->entries [$entry->key ()]->merge_with ( $entry );
        }
    }
}