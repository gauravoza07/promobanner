<?php
/**
 * PromoBanner Module
 *
 * This module displays promotional banners on category pages based on configuration.
 * It allows admins to create multiple banners with images, titles, descriptions, CTAs,
 * assign them to categories, set active status, and schedule them.
 *
 * @author Gaurav
 * @version 1.0.0
 * @since PrestaShop 1.7
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Include the model class
include_once _PS_MODULE_DIR_.'promobanner/classes/PromoBannerModel.php';

class PromoBanner extends Module
{
    /**
     * Module constructor
     * Sets up basic module information and properties
     */
    public function __construct()
    {
        $this->name = 'promobanner';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Gaurav';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Promotional Banner');
        $this->description = $this->l('Display promotional banners based on categories');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    /**
     * Module installation
     * Creates database tables, registers hooks
     *
     * @return bool
     */
    public function install()
    {
        // Call parent install and check if successful
        if (!parent::install() ||
            // Register hooks for displaying banners and loading assets
            !$this->registerHook('displayHeader') ||
            !$this->registerHook('displayTop') ||
            // Create custom database table
            !$this->createTables()) {
            return false;
        }

        return true;
    }

    /**
     * Module uninstallation
     * Drops database tables
     *
     * @return bool
     */
    public function uninstall()
    {
        // Call parent uninstall and drop tables
        if (!parent::uninstall() || !$this->dropTables()) {
            return false;
        }

        return true;
    }

    /**
     * Create custom database table for banners
     *
     * @return bool
     */
    private function createTables()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'promobanner` (
            `id_banner` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text,
            `image` varchar(255),
            `cta_text` varchar(255),
            `cta_link` varchar(255),
            `categories` text,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `start_date` datetime,
            `end_date` datetime,
            PRIMARY KEY (`id_banner`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Drop custom database table
     *
     * @return bool
     */
    private function dropTables()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'promobanner`');
    }

    /**
     * Redirect to admin controller when accessing module configuration
     *
     * @return void
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminPromoBanner'));
    }

    /**
     * Hook for displayHeader - load CSS and JS assets
     *
     * @param array $params Hook parameters
     */
    public function hookDisplayHeader($params)
    {
        // Add module CSS and JS files
        $this->context->controller->addCSS($this->_path . 'views/css/promobanner.css');
        $this->context->controller->addJS($this->_path . 'views/js/promobanner.js');
    }

    /**
     * Hook for displayTop - display banners on category pages
     *
     * @param array $params Hook parameters
     * @return string HTML output
     */
    public function hookDisplayTop($params)
    {
        // Only show on category pages
        if ($this->context->controller->php_self != 'category') {
            return;
        }

        // Get current category ID
        $id_category = (int) Tools::getValue('id_category');
        if (!$id_category) {
            return;
        }

        // Get active banners for this category
        $banners = $this->getBannersForCategory($id_category);
        if (empty($banners)) {
            return;
        }

        // Assign variables to Smarty template
        $this->context->smarty->assign(array(
            'banners' => $banners,
            'promobanner_path' => $this->_path
        ));

        // Return rendered template
        return $this->display(__FILE__, 'views/templates/hook/display.tpl');
    }

    /**
     * Get banners for a specific category
     * Filters by active status, date range, and category assignment
     *
     * @param int $id_category Category ID
     * @return array Array of banner data
     */
    private function getBannersForCategory($id_category)
    {
        // Query banners that are active, within date range, and assigned to category
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'promobanner` 
                WHERE is_active = 1 
                AND (start_date IS NULL OR start_date <= NOW()) 
                AND (end_date IS NULL OR end_date >= NOW()) 
                AND categories LIKE "%,' . (int) $id_category . ',%"';

        $banners = Db::getInstance()->executeS($sql);
        // print_r($banners); // Debug: check retrieved banners

        // Add full image URL to each banner
        foreach ($banners as &$banner) {
            if ($banner['image']) {
                // Use getMediaLink to support SSL, CDN, and correct module path
                $banner['image_url'] = $this->context->link->getMediaLink($this->_path . 'views/img/' . $banner['image']);
            } else {
                $banner['image_url'] = ''; // no image available
            }
        }

        return $banners;
    }
}