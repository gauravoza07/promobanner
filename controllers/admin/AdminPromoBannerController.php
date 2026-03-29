<?php
/**
 * AdminPromoBannerController
 *
 * Admin controller for managing promotional banners
 * Provides CRUD interface for banner management
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Include the PromoBannerModel class
include_once dirname(__FILE__) . '/../../classes/PromoBannerModel.php';

class AdminPromoBannerController extends AdminController
{
    /**
     * Controller constructor
     * Sets up the admin interface configuration
     */
    public function __construct()
    {
        parent::__construct(); // Call parent constructor first

        $this->bootstrap = true; // Use Bootstrap styling
        $this->table = 'promobanner'; // Database table name
        $this->className = 'PromoBannerModel'; // ObjectModel class
        $this->lang = false; // No multi-language fields
        $this->identifier = 'id_banner'; // Primary key column name
        $this->_orderBy = 'id_banner'; // Default sort field
        $this->_orderWay = 'DESC'; // Default sort direction
        $this->list_no_filter = true; // Disable filters
        $this->search = false; // Disable search
        $this->addRowAction('edit'); // Add edit action
        $this->addRowAction('delete'); // Add delete action

        // Bulk actions
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?')
            )
        );

        // List view fields
        $this->fields_list = array(
            'id_banner' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'image' => array(
                'title' => $this->l('Image'),
                'align' => 'center',
                'callback' => 'renderImage', // Custom callback to display image
                'orderby' => false,
                'search' => false
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'align' => 'left'
            ),
            'is_active' => array(
                'title' => $this->l('Active'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center'
            ),
            /*
            'start_date' => array(
                'title' => $this->l('Start Date'),
                'type' => 'date'
            ),
            'end_date' => array(
                'title' => $this->l('End Date'),
                'type' => 'date'
            ),
            */
        );

        // Form fields for add/edit
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Banner')
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Title'),
                    'name' => 'title',
                    'required' => true,
                    'hint' => $this->l('The banner title that will be displayed')
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description'),
                    'name' => 'description',
                    'class' => 'rte', // Rich text editor
                    'autoload_rte' => true,
                    'hint' => $this->l('Banner description with HTML support')
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Image'),
                    'name' => 'image',
                    'display_image' => true,
                    'hint' => $this->l('Upload a banner image (JPG, PNG, GIF)')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('CTA Button Text'),
                    'name' => 'cta_text',
                    'hint' => $this->l('Text for the call-to-action button')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('CTA Link URL'),
                    'name' => 'cta_link',
                    'hint' => $this->l('URL where the CTA button links to')
                ),
                array(
                    'type' => 'categories',
                    'label' => $this->l('Categories'),
                    'name' => 'categories',
                    'tree' => array(
                        'id' => 'categories-tree',
                        'selected_categories' => array(), // Will be set in renderForm()
                        'root_category' => 1, // Start from root category
                        'use_search' => true,
                        'use_checkbox' => true
                    ),
                    'hint' => $this->l('Select categories where this banner should appear')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'is_active',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'hint' => $this->l('Enable or disable this banner')
                ),
                array(
                    'type' => 'date',
                    'label' => $this->l('Start Date'),
                    'name' => 'start_date',
                    'hint' => $this->l('Date when banner becomes active (leave empty for immediate)')
                ),
                array(
                    'type' => 'date',
                    'label' => $this->l('End Date'),
                    'name' => 'end_date',
                    'hint' => $this->l('Date when banner becomes inactive (leave empty for no end date)')
                )
            ),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );
    }

    /**
     * Process form submission
     * Handle image upload and category data
     */
    public function postProcess()
    {
        // Handle form submission
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            // Process categories array to comma-separated string
            $categories = Tools::getValue('categories');
            if (is_array($categories)) {
                $_POST['categories'] = ',' . implode(',', $categories) . ',';
            }

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['name']) {
                $image_name = $this->uploadBannerImage();
                if ($image_name) {
                    $_POST['image'] = $image_name;
                }
            }
        }

        parent::postProcess();
    }
    public function renderImage($image, $row)
    {
        if ($image) {
            // Build URL dynamically - use shop base URL for localhost:8080 support
            $image_url = $this->context->shop->getBaseURL() . 'modules/promobanner/views/img/' . $image;
            return '<img src="' . $image_url . '" alt="' . htmlspecialchars($row['title']) . '" style="max-width: 100px; max-height: 50px;" />';
        }
        return '';
    }

    /**
     * Upload banner image
     * Validates and moves uploaded file
     *
     * @return string|bool Image filename or false on failure
     */
    protected function uploadBannerImage()
    {
        $target_dir = _PS_MODULE_DIR_ . 'promobanner/views/img/';

        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Get file extension
        $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        // Check file type
        $allowed_types = array('jpg', 'png', 'jpeg', 'gif');
        if (!in_array($imageFileType, $allowed_types)) {
            $this->errors[] = $this->l('Only JPG, JPEG, PNG & GIF files are allowed.');
            return false;
        }

        // Generate a safe filename
        $safe_filename = 'banner_' . time() . '_' . uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $safe_filename;

        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            return $safe_filename;
        }

        $this->errors[] = $this->l('Failed to upload image.');
        return false;
    }

    /**
     * Get selected categories for form
     * Used when editing existing banner
     *
     * @return array Selected category IDs
     */
    private function getSelectedCategories()
    {
        if ($this->object && $this->object->categories) {
            // Remove leading/trailing commas and split
            $cats = trim($this->object->categories, ',');
            return explode(',', $cats);
            // print_r($cats); 
        }
        return array();
    }

    /**
     * Render form
     * Set selected categories and image path for display
     *
     * @return string Form HTML
     */
    public function renderForm()
    {
        // Set selected categories for edit view - NOW object is loaded
        if ($this->object && $this->object->categories) {
            // Remove leading/trailing commas and split
            $cats = trim($this->object->categories, ',');
            $selected_cats = explode(',', $cats);
            
            // Update the categories field with selected values
            foreach ($this->fields_form['input'] as &$field) {
                if ($field['name'] == 'categories') {
                    $field['tree']['selected_categories'] = $selected_cats;
                    break;
                }
            }
        }

        // Set image path for existing banners
        // if ($this->object && $this->object->image) {
        //     $this->fields_form['input'][2]['image'] = _PS_MODULE_DIR_ . 'promobanner/views/img/' . $this->object->image;
        // }

        return parent::renderForm();
    }
}