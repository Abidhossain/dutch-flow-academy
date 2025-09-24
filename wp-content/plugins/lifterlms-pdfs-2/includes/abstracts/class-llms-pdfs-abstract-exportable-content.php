<?php
/**
 * LLMS_PDFS_Abstract_Exportable_Content abstract class file.
 *
 * @package LifterLMS/Abstracts
 *
 * @since 2.0.0
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base class for exportable content.
 *
 * @since 2.0.0
 */
abstract class LLMS_PDFS_Abstract_Exportable_Content {

	/**
	 * Unique content type ID.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Content type description.
	 *
	 * Used as the checkbox description on the settings page.
	 *
	 * Set by the return of {@see LLMS_PDFS_Abstract_Exportable_Content::set_description()}.
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Content type title.
	 *
	 * Used as the title/label for the checkbox on the settings page.
	 *
	 * Set by the return of {@see LLMS_PDFS_Abstract_Exportable_Content::set_title()}.
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * PDF orientation.
	 *
	 * @var string
	 */
	protected $orientation = 'portrait';

	/**
	 * Registration priority.
	 *
	 * Used to order the content type on the settings page.
	 *
	 * @var integer
	 */
	protected $priority = 100;

	/**
	 * Sets the content type's description.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	abstract protected function set_description();

	/**
	 * Sets the content type's title.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	abstract protected function set_title();

	/**
	 * Constructor.
	 *
	 * Configures class variables, and adds an init action.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		if ( empty( $this->id ) ) {
			_doing_it_wrong( get_called_class(), __( 'Extending classes must define an id.', 'lifterlms-pdfs' ), '2.0.0' );
		}

		$this->title       = $this->set_title();
		$this->description = $this->set_description();

		add_action( 'init', array( $this, 'init' ), 15 );

	}

	/**
	 * Determines if dependencies / requirements are met to load the content type.
	 *
	 * For example, if another plugin is required to load the dependency this stub
	 * can be redefined to check whether or not that plugin is activated.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	protected function are_requirements_met() {
		return true;
	}

	/**
	 * Initialize and load the content type.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean Returns `truen` if the export type is enabled and should load and `false` otherwise.
	 */
	public function init() {

		/**
		 * Programmatically disable the content type.
		 *
		 * The dynamic portion of this hook `{$this->id}` refers to the ID of the
		 * content type.
		 *
		 * @since 2.0.0
		 *
		 * @param boolaen $disabled Returning `true` will disable the content type.
		 */
		if ( apply_filters( "llms_pdfs_disable_{$this->id}_content_type", false ) ) {
			return false;
		}

		add_filter( 'llms_pdfs_exportable_content_types', array( $this, 'register' ), $this->priority );

		if ( ! $this->should_load() ) {
			return false;
		}

		return true;

	}

	/**
	 * Retrieves the content type's description.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Retrieves the content type's id.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Retrieves the content type's title.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Registers the content type with the plugin.
	 *
	 * @since 2.0.0
	 *
	 * @param LLMS_PDFS_Abstract_Exportable_Content[] $types Array of exportable content type classes.
	 * @return LLMS_PDFS_Abstract_Exportable_Content[]
	 */
	public function register( $types ) {
		$types[ $this->id ] = $this;
		return $types;
	}

	/**
	 * Determines if the content type should be loaded.
	 *
	 * To be loaded, the requirements must be met and it must be explicitly enabled
	 * in the integrations settings.
	 *
	 * @since 2.0.0
	 *
	 * @return boolaen
	 */
	protected function should_load() {
		return $this->are_requirements_met() && llms_pdfs()->get_integration()->is_content_type_enabled( $this->id );
	}

}
