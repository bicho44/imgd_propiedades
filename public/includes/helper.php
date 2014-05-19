<?php
/**
 * IMGD prop Datos
 * Verifica y recolecta los datos de la propiedad
 *
 * @return string Un párrafo con los datos de la Propiedad
 */

function imgd_propiedad_datos($archive = 0) {
    $datos = '';

    $plan = rwmb_meta('imgd_propiedad_plantas');
    if (!empty($plan)) {
        $datos .= $plan . " plantas, ";
    }

    $dorm = rwmb_meta('imgd_propiedad_dormitorios');
    if (!empty($dorm)) {
        $datos .= $dorm . " dormitorios, ";
    }

    $ambi = rwmb_meta('imgd_propiedad_ambientes');
    if (!empty($ambi)) {
        $datos .= $ambi . " ambientes, ";
    }

    $banio = rwmb_meta('imgd_propiedad_banios');
    if (!empty($banio)) {
        $datos .= $banio . " baños, ";
    }

    $m2 = rwmb_meta('imgd_propiedad_metroscubiertos');
    if (!empty($m2)) {
        $datos .= $m2 . " m<sup>2</sup> cubiertos, ";
    }

    $mt = rwmb_meta('imgd_propiedad_metrosterreno');
    if (!empty($mt)) {
        $datos .= $mt . " m<sup>2</sup> terreno, ";
    }

    if ($archive === 0) {
	    if($datos!=='') $datos.='<br>'; // Si datos está vacío no necesito el renglón extra.
        $datos .= substr(strip_tags(get_the_content()), 0, 50);
    }
    return $datos;
}
