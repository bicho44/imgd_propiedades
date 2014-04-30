<?php
/**
 * Plugin Name.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 *
 * @package IMGD_Propiedades
 * @author  Federico Reinoso <admin@imgdigital.com.ar>
 */
class IMGD_Propiedades {

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since   1.0.0
     *
     * @var     string
     */
    const VERSION = '1.0.0';

    /**
     *
     * Unique identifier for your plugin.
     *
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * plugin file.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_slug = 'imgd-propiedades';

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     *
     * @since     1.0.0
     */
    private function __construct() {

        // Load plugin text domain
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

        // Activate plugin when new blog is added
        //add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

        // Load public-facing style sheet and JavaScript.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        /* Define la Custom Post Type */
        add_action( 'init', array ($this, 'imgd_propiedades'));
        add_action( 'init', array ($this, 'imgd_propiedades_taxonomies'));

        //add_action( 'init', array ($this, 'imgd_propiedades_servicios'));

        /* Define las Metaboxes
        * @TODO ver de generar algún tipo de aviso de la dependencia del Meta Box posiblemente haya que pasar toda la definición a la clase del admin
        */
        if (class_exists('RW_Meta_Box')) {
            add_filter( 'rwmb_meta_boxes', array ($this, 'register_meta_boxes' ));
        }

        /* Filter the single_template or archive_template with our custom function*/
        //add_filter('single_template', array ($this, 'imgd_propiedades_single_template'));
        //add_filter('archive_template',array ($this, 'imgd_prpoiedades_archive_template')) ;

    }

    /**
     * Return the plugin slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_plugin_slug() {
        return $this->plugin_slug;
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses
     *                                       "Network Activate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       activated on an individual blog.
     */
    public static function activate( $network_wide ) {

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {

            if ( $network_wide  ) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ( $blog_ids as $blog_id ) {

                    switch_to_blog( $blog_id );
                    self::single_activate();
                }

                restore_current_blog();

            } else {
                self::single_activate();
            }

        } else {
            self::single_activate();
        }

    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses
     *                                       "Network Deactivate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       deactivated on an individual blog.
     */
    public static function deactivate( $network_wide ) {

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {

            if ( $network_wide ) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ( $blog_ids as $blog_id ) {

                    switch_to_blog( $blog_id );
                    self::single_deactivate();

                }

                restore_current_blog();

            } else {
                self::single_deactivate();
            }

        } else {
            self::single_deactivate();
        }

    }

    /**
     * Fired when a new site is activated with a WPMU environment.
     *
     * @since    1.0.0
     *
     * @param    int    $blog_id    ID of the new blog.
     */
    public function activate_new_site( $blog_id ) {

        if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
            return;
        }

        switch_to_blog( $blog_id );
        self::single_activate();
        restore_current_blog();

    }

    /**
     * Get all blog ids of blogs in the current network that are:
     * - not archived
     * - not spam
     * - not deleted
     *
     * @since    1.0.0
     *
     * @return   array|false    The blog ids, false if no matches.
     */
    private static function get_blog_ids() {

        global $wpdb;

        // get an array of blog ids
        $sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

        return $wpdb->get_col( $sql );

    }

    /**
     * Fired for each blog when the plugin is activated.
     *
     * @since    1.0.0
     */
    private static function single_activate() {
        // include ('includes/imgd_propiedades-post-type.php');

    }

    /**
     * Fired for each blog when the plugin is deactivated.
     *
     * @since    1.0.0
     */
    private static function single_deactivate() {
        // @TODO: Define deactivation functionality here
    }


    public function imgd_propiedades() {

        // Definición de nombres
        $name = 'Propiedades';
        $name_sing = 'Propiedad';

        $labels = array(
            'name'                => _x( $name, 'Post Type General Name', 'imgd' ),
            'singular_name'       => _x( $name_sing, 'Post Type Singular Name', 'imgd' ),
            'menu_name'           => __( $name_sing, 'imgd' ),
            'parent_item_colon'   => __( $name_sing.' Pariente:', 'imgd' ),
            'all_items'           => __( 'Todas las '.$name_sing, 'imgd' ),
            'view_item'           => __( 'Ver '.$name_sing, 'imgd' ),
            'add_new_item'        => __( 'Agregue un nueva '.$name_sing, 'imgd' ),
            'add_new'             => __( 'Nuevo '.$name_sing, 'imgd' ),
            'edit_item'           => __( 'Editar '.$name_sing, 'imgd' ),
            'update_item'         => __( 'Actualizar '.$name_sing, 'imgd' ),
            'search_items'        => __( 'Buscar '.$name_sing, 'imgd' ),
            'not_found'           => __( 'No se encontraron '.$name_sing, 'imgd' ),
            'not_found_in_trash'  => __( 'No hay '.$name_sing.' en la basura', 'imgd' ),
        );
        $rewrite = array(
            'slug'                => 'propiedad',
            'with_front'          => true,
            'pages'               => true,
            'feeds'               => true,
        );
        $args = array(
            'label'               => __( $name_sing, 'imgd' ),
            'description'         => __( 'Descripcion de cada '.$name_sing, 'imgd' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', ),
            'taxonomies'          => array( 'categoria', 'servicios' ),
            'hierarchical'        => true,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => plugins_url('assets/icono.png', __FILE__ ),
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'query_var'           => 'imgd',
            'rewrite'             => $rewrite,
            'capability_type'     => 'page',
        );
        register_post_type( 'imgd_propiedad', $args );

    }

    public function imgd_propiedades_taxonomies() {


        /* Categoría de las Propiedades */

        $labels = array(
            'name'                       => _x( 'Categorias', 'Taxonomy General Name', 'imgd' ),
            'singular_name'              => _x( 'Categoria', 'Taxonomy Singular Name', 'imgd' ),
            'menu_name'                  => __( 'Categoria', 'imgd' ),
            'all_items'                  => __( 'Categorias', 'imgd' ),
            'parent_item'                => __( 'Categoria', 'imgd' ),
            'parent_item_colon'          => __( 'Categoría:', 'imgd' ),
            'new_item_name'              => __( 'Nueva Categoría', 'imgd' ),
            'add_new_item'               => __( 'Agregar Nueva Categoría', 'imgd' ),
            'edit_item'                  => __( 'Editar Categoría', 'imgd' ),
            'update_item'                => __( 'Actualizar Categoría', 'imgd' ),
            'separate_items_with_commas' => __( 'Separado por comas', 'imgd' ),
            'search_items'               => __( 'Buscar Categorias', 'imgd' ),
            'add_or_remove_items'        => __( 'Agregar o Borrar Categorías', 'imgd' ),
            'choose_from_most_used'      => __( 'Seleccione sobre las más usadas', 'imgd' ),
            'not_found'                  => __( 'No se encontraron Categorías', 'imgd' ),
        );
        $rewrite = array(
            'slug'                       => 'categoria',
            'with_front'                 => true,
            'hierarchical'               => false,
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'query_var'                  => 'prop_categoria',
            'rewrite'                    => $rewrite,
        );
        register_taxonomy( 'prop_categoria', array( 'imgd_propiedad' ), $args );

        /* Ciudad */
        $labels = array(
            'name'                       => _x( 'Ciudades', 'Taxonomy General Name', 'imgd' ),
            'singular_name'              => _x( 'Ciudad', 'Taxonomy Singular Name', 'imgd' ),
            'menu_name'                  => __( 'Ciudad', 'imgd' ),
            'all_items'                  => __( 'Ciudades', 'imgd' ),
            'parent_item'                => __( 'Ciudad', 'imgd' ),
            'parent_item_colon'          => __( 'Ciudad:', 'imgd' ),
            'new_item_name'              => __( 'Nueva Ciudad', 'imgd' ),
            'add_new_item'               => __( 'Agregar Nueva Ciudad', 'imgd' ),
            'edit_item'                  => __( 'Editar Ciudad', 'imgd' ),
            'update_item'                => __( 'Actualizar Ciudad', 'imgd' ),
            'separate_items_with_commas' => __( 'Separado por comas', 'imgd' ),
            'search_items'               => __( 'Buscar Ciudades', 'imgd' ),
            'add_or_remove_items'        => __( 'Agregar o Borrar Ciudades', 'imgd' ),
            'choose_from_most_used'      => __( 'Seleccione sobre las más usadas', 'imgd' ),
            'not_found'                  => __( 'No se encontraron Ciudades', 'imgd' ),
        );
        $rewrite = array(
            'slug'                       => 'ciudad',
            'with_front'                 => true,
            'hierarchical'               => false,
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'query_var'                  => 'prop_ciudad',
            'rewrite'                    => $rewrite,
        );
        register_taxonomy( 'prop_ciudad', array( 'imgd_propiedad' ), $args );

        /* Categoría type */

        $labels = array(
            'name'                       => _x( 'Tipos de Propiedades', 'Taxonomy General Name', 'imgd' ),
            'singular_name'              => _x( 'Tipo de Propiedad', 'Taxonomy Singular Name', 'imgd' ),
            'menu_name'                  => __( 'Tipo', 'imgd' ),
            'all_items'                  => __( 'Tipos', 'imgd' ),
            'parent_item'                => __( 'Tipo', 'imgd' ),
            'parent_item_colon'          => __( 'Tipo:', 'imgd' ),
            'new_item_name'              => __( 'Nuevo Tipo', 'imgd' ),
            'add_new_item'               => __( 'Agregar Nuevo Tipo', 'imgd' ),
            'edit_item'                  => __( 'Editar Tipo', 'imgd' ),
            'update_item'                => __( 'Actualizar Tipo', 'imgd' ),
            'separate_items_with_commas' => __( 'Separado por comas', 'imgd' ),
            'search_items'               => __( 'Buscar Tipos', 'imgd' ),
            'add_or_remove_items'        => __( 'Agregar o Borrar Tipos', 'imgd' ),
            'choose_from_most_used'      => __( 'Seleccione sobre las más usadas', 'imgd' ),
            'not_found'                  => __( 'No se encontraron Tipos', 'imgd' ),
        );
        $rewrite = array(
            'slug'                       => 'tipo',
            'with_front'                 => true,
            'hierarchical'               => false,
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'query_var'                  => 'prop_tipo',
            'rewrite'                    => $rewrite,
        );
        register_taxonomy( 'prop_tipo', array( 'imgd_propiedad' ), $args );

    }
    /*
     * Register Meta Boxes
     *
     * @return array $meta_boxes
     */
    public function register_meta_boxes(){

        /* * ******************* META BOX DEFINITIONS ********************** */

        /**
         * Prefix of meta keys (optional)
         * Use underscore (_) at the beginning to make keys hidden
         * Alt.: You also can make prefix empty to disable it
         */
        // Better has an underscore as last sign
        $prefix = 'imgd_propiedad_';

        if (!isset($meta_boxes)){
            //global $meta_boxes;
            $meta_boxes = array();
        }

        $meta_boxes[] = array(
            // Meta box id, UNIQUE per meta box. Optional since 4.1.5
            'id' => $prefix . 'home',
            // Meta box title - Will appear at the drag and drop handle bar. Required.
            'title' => 'Display Options',
            // Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
            'pages' => array('imgd_propiedad'),
            // Where the meta box appear: normal (default), advanced, side. Optional.
            'context' => 'side',
            // Order of meta box: high (default), low. Optional.
            'priority' => 'high',
            // List of meta fields
            'fields' => array(
                array(
                    // Field name - Will be used as label
                    'name' => __('Home Page', 'imgd_framework'),
                    // Field ID, i.e. the meta key
                    'id' => 'imgd_slideshow',
                    // Field description (optional)
                    'desc' => __('Se muestra en la Home Page en el Slide Show', 'imgd_framework'),
                    // CLONES: Add to make the field cloneable (i.e. have multiple value)
                    'clone' => false,
                    'type' => 'checkbox'
                ),

            )
        );
        /**
         * Registering meta boxes
         *
         * All the definitions of meta boxes are listed below with comments.
         * Please read them CAREFULLY.
         *
         * You also should read the changelog to know what has been changed before updating.
         *
         * For more information, please visit:
         * @link http://www.deluxeblogtips.com/meta-box/docs/define-meta-boxes
         */
// 1st meta box
        $meta_boxes[] = array(
            // Meta box id, UNIQUE per meta box. Optional since 4.1.5
            'id' => 'imgd_propiedades_datos',
            // Meta box title - Will appear at the drag and drop handle bar. Required.
            'title' => __('Datos Propiedad', 'imgd_framework'),
            // Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
            'pages' => array('imgd_propiedad'),
            // Where the meta box appear: normal (default), advanced, side. Optional.
            'context' => 'side',
            // Order of meta box: high (default), low. Optional.
            'priority' => 'high',
            // List of meta fields
            'fields' => array(
                array(
                    // Field name - Will be used as label
                    'name' => __('Código Propiedad', 'imgd_framework'),
                    // Field ID, i.e. the meta key
                    'id' => $prefix . 'codigo',
                    // Field description (optional)
                    'desc' => __('AlfaNumérico, opcional', 'imgd_framework'),
                    // CLONES: Add to make the field cloneable (i.e. have multiple value)
                    'clone' => false,
                    'std'=>'',
                    'size' => 8,
                    'type' => 'text'
                ),

                array(
                    'name' => __('Destacada', 'imgd_framework'),
                    'id' => $prefix.'destacada',
                    'clone' => false,
                    'type' => 'checkbox',
                    'desc' => __('Destacada se refiere a
                que la propiedad será destacada en la lista de propiedades', 'imgd_framework')
                ),

                array(
                    // Field name - Will be used as label
                    'name' => __('Vendido / Alquilado', 'imgd_framework'),
                    // Field ID, i.e. the meta key
                    'id' => $prefix . 'estado',
                    // Field description (optional)
                    'desc' => __('Propiedad Vendida o Alquilada', 'imgd_framework'),
                    // CLONES: Add to make the field cloneable (i.e. have multiple value)
                    'clone' => false,
                    'type' => 'checkbox'
                ),
                array(
                    'name' => __('Moneda', 'imgd_framework'),
                    'id' => $prefix . 'moneda',
                    'type' => 'select',
                    // Array of 'value' => 'Label' pairs for select box
                    'options' => array(
                        '$' => 'pesos',
                        'u$s' => 'dólares USA',
                    ),
                    // Select multiple values, optional. Default is false.
                    'multiple' => false,
                ),
                array(
                    // Field name - Will be used as label
                    'name' => __('Precio', 'imgd_framework'),
                    // Field ID, i.e. the meta key
                    'id' => $prefix . 'precio',
                    // Field description (optional)
                    'desc' => __('Valor de la Propiedad', 'imgd_framework'),
                    // CLONES: Add to make the field cloneable (i.e. have multiple value)
                    'clone' => false,
                    'std' => 0,
                    'type' => 'number'
                ),
                array(
                    // Field name - Will be used as label
                    'name' => __('Plantas', 'imgd_framework'),
                    // Field ID, i.e. the meta key
                    'id' => $prefix . 'plantas',
                    // Field description (optional)
                    'desc' => __('Cantidad de Plantas', 'imgd_framework'),
                    // CLONES: Add to make the field cloneable (i.e. have multiple value)
                    'clone' => false,
                    'size' => 10,
                    'type' => 'number'
                ),
                array(
                    // Field name - Will be used as label
                    'name' => 'Dormitorios',
                    // Field ID, i.e. the meta key
                    'id' => $prefix . 'dormitorios',
                    // Field description (optional)
                    'desc' => 'Cantidad de Dormitorios',
                    // CLONES: Add to make the field cloneable (i.e. have multiple value)
                    'clone' => false,
                    'size' => 10,
                    'type' => 'number'
                ),
                array(
                    // Field name - Will be used as label
                    'name' => 'Ambientes',
                    // Field ID, i.e. the meta key
                    'id' => $prefix . 'ambientes',
                    // Field description (optional)
                    'desc' => 'Cantidad de Ambientes',
                    // CLONES: Add to make the field cloneable (i.e. have multiple value)
                    'clone' => false,
                    'size' => 10,
                    'type' => 'number'
                ),
                array(
                    // Field name - Will be used as label
                    'name' => 'Baños',
                    // Field ID, i.e. the meta key
                    'id' => $prefix . 'banios',
                    // Field description (optional)
                    'desc' => 'Cantidad de Baños',
                    // CLONES: Add to make the field cloneable (i.e. have multiple value)
                    'clone' => false,
                    'size' => 10,
                    'type' => 'number'
                ),
            ),
            'validation' => array(
                'rules' => array(
                    // optionally make post/page title required
                    'post_title' => array(
                        'required' => true
                    ),
                ),
                // optional override of default jquery.validate messages
                'messages' => array(
                )
            )
        );

        $meta_boxes[] = array(
            'id' => 'mapa',
            'pages' => array('imgd_propiedad'),
            'title' => 'Google Map',
            'fields' => array(
                array(
                    'id' => 'address',
                    'name' => 'Dirección',
                    'type' => 'text',
                    'std' => 'San Carlos de Bariloche, Argentina',
                ),
                array(
                    'id' => 'loc',
                    'name' => 'Ubicación',
                    'type' => 'map',
                    'std' => '-41.1334722,-71.3102778, 15', // 'latitude,longitude[,zoom]' (zoom is optional)
                    'style' => 'width: 500px; height: 300px',
                    'address_field' => 'address', // Name of text field where address is entered. Can be list of text fields, separated by commas (for ex. city, state)
                ),
            ),
        );

        $meta_boxes[] = array(
            'id' => 'info-propiedad',
            'title' => 'Información de la Propiedad',
            'pages' => array('imgd_propiedad'),
            // Order of meta box: high (default), low. Optional.
            'priority' => 'high',
            'fields' => array(
                array(
                    // Field name - Will be used as label
                    'name' => 'Metros Cubiertos',
                    'id' => $prefix . 'metroscubiertos',
                    'clone' => false,
                    'size' => 10,
                    'type' => 'text'
                ),
                array(
                    // Field name - Will be used as label
                    'name' => 'Metros Terreno',
                    'id' => $prefix . 'metrosterreno',
                    'clone' => false,
                    'size' => 10,
                    'type' => 'text'
                ),
                array(
                    'name' => 'Esparcimiento',
                    'id' => "{$prefix}esparcimiento",
                    'type' => 'checkbox_list',
                    // Options of checkboxes, in format 'key' => 'value'
                    'options' => array(
                        'TV' => 'TV',
                        'LCD' => 'LCD',
                        'LED' => 'LED',
                        'DVD' => 'DVD',
                        'Cable' => 'Cable',
                        'Direct TV' => 'Direct TV',
                        'Home Theatre' => 'Home Theatre',
                        'Consola de Video Juegos' => 'Consola de Video Juegos',
                        'Mesa de Pool' => 'Mesa de Pool',
                        'Tennis de Mesa' => 'Tennis de Mesa'
                    )
                ),
                array(
                    'name' => 'Comodidades',
                    'id' => "{$prefix}comodidades",
                    'type' => 'checkbox_list',
                    // Options of checkboxes, in format 'key' => 'value'
                    'options' => array(
                        'living' => 'Living',
                        'comedor' => 'Comedor',
                        'living-comedor' => 'Living-Comedor',
                        'estar' => 'Estar',
                        'cocina' => 'Cocina',
                        'cocina-comedor' => 'Cocina-Comedor',
                        'piscina' => 'Piscina',
                        'jardin' => 'Jardín',
                        'parque' => 'Parque',
                        'jardinero' => 'Jardinero',
                        'Servicio de mucama diario' => 'Servicio de mucama diario',
                        'escritorio' => 'Escritorio',
                        'lavadero' => 'Lavadero',
                        'vestidor' => 'Vestidor',
                        'Entrada de Auto' => 'Entrada de Auto',
                        'Estacionamiento' => 'Estacionamiento',
                        'Garage Cubierto' => 'Garage Cubierto',
                        'Garage Semicubierto' => 'Garage Semicubierto',
                        'Garage Descubierto' => 'Garage Descubierto',
                        'Entrada de Servicio' => 'Entrada de Servicio',
                        'Altillo' => 'Altillo',
                        'Baulera' => 'Baulera',
                        'Quincho' => 'Quincho',
                        'Parrilla' => 'Parrilla',
                        'Balcón' => 'Balcón',
                        'Piscina' => 'Piscina',
                        'Jardín' => 'Jardín',
                        'Patio' => 'Patio',
                        'Parque' => 'Parque',
                        'Playroom' => 'Playroom',
                        'Calle Pavimentada' => 'Calle Pavimentada',
                        'Calle de Ripio' => 'Calle de Ripio',
                        'Todos los Servicios' => 'Todos los Servicios',
                        'Sin Cloacas' => 'Sin Cloacas',
                        'Vistas Panorámicas' => 'Vistas Panorámicas',
                        'Muy luminoso' => 'Muy luminoso',
                        'Nuevo' => 'Nuevo',
                        'Accesibilidad' => 'Accesibilidad'
                    )
                ),
                array(
                    'name' => 'Equipamento',
                    'id' => "{$prefix}equipamiento",
                    'type' => 'checkbox_list',
                    // Options of checkboxes, in format 'key' => 'value'
                    'options' => array(
                        'jacuzzi' => 'Jacuzzi',
                        'microondas' => 'Microondas',
                        'lavavajillas' => 'Lavavajillas',
                        'internet' => 'Internet',
                        'wifi' => 'Wi-Fi',
                        'Aire Acondicionado' => 'Aire Acondicionado',
                        'Hidromasaje' => 'Hidromasaje',
                        'Amenities' => 'Amenities',
                        'hogar' => 'Hogar',
                        'Calefacción Central' => 'Calefacción Central',
                        'Calefacción por Radiadores' => 'Calefacción por Radiadores',
                        'Calefacción por Tiro Balanceado' => 'Calefacción por Tiro Balanceado',
                        'Calefacción por Losa Radiante' => 'Calefacción por Losa Radiante',
                    )
                ),
                array(
                    'name' => 'Otros',
                    'id' => "{$prefix}otros",
                    'type' => 'checkbox_list',
                    // Options of checkboxes, in format 'key' => 'value'
                    'options' => array(
                        'tostadora' => 'Tostadora',
                        'cafetera' => 'Cafetera',
                        'Ropa de cama' => 'Ropa de Cama',
                        'Ropa Blanca' => 'Ropa Blanca',
                        'Microondas' => 'Microondas',
                        'Lavavajillas' => 'Lavavajillas',
                        'Heladera' => 'Heladera',
                        'Cafetera' => 'Cafetera',
                        'vajilla' => 'Vajilla',
                        'Caja de Seguridad' => 'Caja de Seguridad',
                        'Alarma' => 'Alarma'
                    )
                ),
            )
        );

        /* * ******************* META BOX REGISTERING ********************** */

        /**
         * Register meta boxes
         *
         * @return void
         */

        return $meta_boxes;

        // wp_enqueue_style('imgd', get_stylesheet_directory_uri() . '/assets/css/admin.css');

    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        $domain = $this->plugin_slug;
        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

        load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

    }

    /**
     * Register and enqueue public-facing style sheet.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
    }

    /**
     * Register and enqueues public-facing JavaScript files.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
    }

    /**
     * NOTE:  Actions are points in the execution of a page or process
     *        lifecycle that WordPress fires.
     *
     *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
     *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
     *
     * @since    1.0.0
     */
    public function action_method_name() {
        // @TODO: Define your action hook callback here
    }

    /**
     * NOTE:  Filters are points of execution in which WordPress modifies data
     *        before saving it or sending it to the browser.
     *
     *        Filters: http://codex.wordpress.org/Plugin_API#Filters
     *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
     *
     * @since    1.0.0
     */
    public function filter_method_name() {
        // @TODO: Define your filter hook callback here
    }

}
