<?php
/**
 * PromoBannerModel
 *
 * ObjectModel for the promobanner table
 * Handles database operations for banner records
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PromoBannerModel extends ObjectModel
{
    // Database fields
    public $id_banner;
    public $title;
    public $description;
    public $image;
    public $cta_text;
    public $cta_link;
    public $categories; // Comma-separated category IDs
    public $is_active;
    public $start_date;
    public $end_date;

    /**
     * ObjectModel definition
     * Defines table structure and field validations
     */
    public static $definition = array(
        'table' => 'promobanner',
        'primary' => 'id_banner',
        'fields' => array(
            'title' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 255
            ),
            'description' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
            'image' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isFileName',
                'size' => 255
            ),
            'cta_text' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255
            ),
            'cta_link' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isUrl',
                'size' => 255
            ),
            'categories' => array(
                'type' => self::TYPE_STRING,
                'size' => 255
            ),
            'is_active' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool'
            ),
            'start_date' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ),
            'end_date' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ),
        ),
    );

    /**
     * Constructor
     * Initialize object with default values
     */
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        // Set default values
        if (!$this->id) {
            $this->is_active = 1;
        }
    }
}