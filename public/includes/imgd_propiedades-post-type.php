<?php

add_action('init', 'create_propiedades_post_types');

function create_propiedades_post_types() {
    // Definición de nombres
    $name = 'Propiedades';
    $name_sing = 'Propiedad';

    register_post_type('imgd_propiedades', array(
            'labels' => array(
                'name' => __($name,'imgd_framework'),
                'all_items'           => 	$name,
                'singular_name' => __($name_sing,'imgd_framework'),
                'add_new' => __('Agregar ' . $name_sing,'imgd_framework'),
                'add_new_item' => __('Agregue una Nueva ' . $name_sing,'imgd_framework'),
                'edit' => __('Editar','imgd_framework'),
                'edit_item' => __('Editar una ' . $name_sing,'imgd_framework'),
                'new_item' => __('Nueva ' . $name_sing,'imgd_framework'),
                'view' => __('Ver las ' . $name,'imgd_framework'),
                'view_item' => __('Ver la ' . $name_sing,'imgd_framework'),
                'search_items' => __('Buscar ' . $name,'imgd_framework'),
                'items_archive' =>	__('Listado de '.$name, 'imgd_framework'),
                'not_found' => __('No se encontraron ' . $name,'imgd_framework'),
                'not_found_in_trash' => __('No se encontraron ' . $name . ' en la Papelera','imgd_framework'),
            ),
            'public' => true,
            'supports' => array('title', 'editor', 'comments', 'thumbnail'),
            'hierarchical' => false,
            'has_archive' => true,
            'rewrite' => array('slug' => 'propiedad'),
            'show_in_nav_menus' => true,
            'menu_position' => 5,
        )
    );

}


// Add custom taxonomy
add_action('init', 'imgd_propiedades_create_taxonomies', 0);

function imgd_propiedades_create_taxonomies() {

    // Ciudad type
    register_taxonomy('prop_categoria', array('imgd_propiedades'), array(
        'hierarchical' => true,
        'label' => 'Categorias',
        'singular_name' => 'Categoria',
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'categoria', 'with_front' => false)
    ));

    // Ciudad type
    register_taxonomy('prop_ciudad', array('imgd_propiedades'), array(
        'hierarchical' => true,
        'label' => 'Ciudades',
        'singular_name' => 'ciudad',
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'ciudad', 'with_front' => false)
    ));

    // Categoría type
    register_taxonomy('prop_tipo', array('imgd_propiedades'), array(
        'hierarchical' => false,
        'label' => 'Tipo de Propiedad',
        'singular_name' => 'tipo',
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'tipo')
    ));
}


add_filter('manage_imgd_propiedades_posts_columns', 'posts_columns', 5);
add_action('manage_imgd_propiedades_posts_custom_column', 'posts_custom_columns', 5, 2);

function posts_columns($defaults) {
    $defaults = array(
        "cb" => "<input type=\"checkbox\" />",
        "imagen" => __('Imagen'),
        "hom" => "H",
        "des" => "D",
        "title" => "Titulo",
        "description" => "Descripción",
        "ciudad" => "Ciudad",
        "tipo" => "Tipo",
        "precio" => "Precio"
    );

    return $defaults;
}

function posts_custom_columns($column_name, $id) {
    if ($column_name === 'precio') {
        $data = rwmb_meta('imgd_propiedades_precio');
        if ($data != 0 || !empty($data)) {
            $moneda = rwmb_meta('imgd_propiedades_moneda');
            if ($moneda === "")
                $moneda = '$';
            echo $moneda . ' ' . $data . '.-';
        } else {
            echo 'Consultar';
        }
    }
    if ($column_name === 'description') {
        //echo data;
        echo imgd_prop_datos();
    }
    if ($column_name === 'hom') {
        $meta_desc = rwmb_meta('imgd_propiedades_destacada', 'type=checkbox_list');
        foreach ($meta_desc as $value) {
            if ($value === 'frontpage')
                echo "√";
        }
    }
    if ($column_name === 'des') {
        $meta_desc = rwmb_meta('imgd_propiedades_destacada', 'type=checkbox_list');
        foreach ($meta_desc as $value) {
            if ($value === 'destacada')
                echo "√";
        }
    }

    if ($column_name === 'imagen') {
        echo the_post_thumbnail(array(50, 50));
    }
    if ($column_name === 'ciudad') {
        echo the_terms('', 'imgd_propiedades_ciudad');
    }
    if ($column_name === 'tipo') {
        echo the_terms('', 'imgd_propiedades_tipo');
    }
    if ($column_name === 'destacada') {

    }
}

/**
 * IMGD prop Datos
 * Verifica y recolecta los datos de la propiedad
 *
 * @return string Un párrafo con los datos de la Propiedad
 */
function imgd_prop_datos($archive = 0) {
    $datos = '';

    $plan = rwmb_meta('imgd_propiedades_plantas');
    if (!empty($plan)) {
        $datos .= $plan . " plantas, ";
    }

    $dorm = rwmb_meta('imgd_propiedades_dormitorios');
    if (!empty($dorm)) {
        $datos .= $dorm . " dormitorios, ";
    }

    $ambi = rwmb_meta('imgd_propiedades_ambientes');
    if (!empty($ambi)) {
        $datos .= $ambi . " ambientes, ";
    }

    $banio = rwmb_meta('imgd_propiedades_banios');
    if (!empty($banio)) {
        $datos .= $banio . " baños, ";
    }

    $m2 = rwmb_meta('prop_metroscubiertos');
    if (!empty($m2)) {
        $datos .= $m2 . " m<sup>2</sup> cubiertos, ";
    }

    $mt = rwmb_meta('imgd_propiedades_metrosterreno');
    if (!empty($mt)) {
        $datos .= $mt . " m<sup>2</sup> terreno, ";
    }

    if ($archive === 0) {
        $datos .= '<br>' . substr(strip_tags(get_the_content()), 0, 50);
    }
    return $datos;
}

/**
 * IMGD Propiedad Destacada
 * Devuelve la clase destacada si está seleccionada en el Administrador
 *
 * @return string Clase 'destacada'
 */
function imgd_prop_destacada() {
    return rwmb_meta('imgd_propiedades_destacada');
}

/* * ******************* META BOX DEFINITIONS ********************** */

/**
 * Prefix of meta keys (optional)
 * Use underscore (_) at the beginning to make keys hidden
 * Alt.: You also can make prefix empty to disable it
 */
// Better has an underscore as last sign
$prefix = 'imgd_propiedades_';

if (!isset($meta_boxes)){
    global $meta_boxes;
    $meta_boxes = array();
}

$meta_boxes[] = array(
    // Meta box id, UNIQUE per meta box. Optional since 4.1.5
    'id' => $prefix . 'home',
    // Meta box title - Will appear at the drag and drop handle bar. Required.
    'title' => 'Display Options',
    // Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
    'pages' => array('imgd_propiedades'),
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
    'pages' => array('imgd_propiedades'),
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


// 2nd meta box
/*$meta_boxes[] = array(
    'id' => 'datos_propiedad',
    'pages' => array('imgd_propietarios'),
    'title' => 'Datos Propietarios',
    'fields' => array(
        array(
            'id' => $prefix .'address',
            'name' => 'Dirección',
            'type' => 'text',
        ),
        array(
            'id' =>  $prefix .'loc',
            'name' => 'Ubicación',
            'type' => 'map',
            'std' => '-41.1334722,-71.3102778, 15', // 'latitude,longitude[,zoom]' (zoom is optional)
            'style' => 'width: 500px; height: 300px',
            'address_field' => $prefix .'address', // Name of text field where address is entered. Can be list of text fields, separated by commas (for ex. city, state)
        ),
        array(
            'id' => $prefix .'telefono',
            'name' => 'Teléfono',
            'type' => 'number',
            'size' => 10,
        ),
        array(
            'id' => $prefix .'codpostal',
            'name' => 'Cód. Postal',
            'type' => 'text',
            'size' => 10,
        ),
        array(
            'id' => $prefix .'provincia',
            'name' => 'Provincia',
            'type' => 'select',
            'options' => array(
                'rio negro' => 'Río Negro',
                'neuquen' => 'Neuquén',
            )
        ),


    ),
);
*/

/* Jacuzzi.
 * Hogar.
 * TV LCD 32" con DirecTV.
 * DVD.
 * Home Theatre. Microondas. Lavavajillas. Tostadora. Cafetera. Desayuno continental servido en la unidad.Servicio de mucama diario. Estacionamiento. Acceso WiFi Internet.
  Otros: Estacionamiento, Ropa de Cama, Vista, Hogar, Televisión, Servicio Doméstico incluído básico (5 a 8 horas semanales),
 */
$meta_boxes[] = array(
    'id' => 'mapa',
    'pages' => array('imgd_propiedades'),
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
    'pages' => array('imgd_propiedades'),
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
