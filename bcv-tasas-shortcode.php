<?php 
/*
Plugin Name: Tasas BCV Avanzado (USD/EUR)
Description: Muestra tasas oficiales del BCV (USD/EUR) con diseño moderno, sin íconos ni emojis.
Version: 3.0
Author: Dervis Martinez
*/

function tasas_bcv_avanzado($atts) {
    $atts = shortcode_atts([
        'modo' => 'cuadro' // valores: cuadro o marquesina
    ], $atts);

    $monedas = [
        'usd' => ['nombre' => 'Dólar (USD)'],
        'eur' => ['nombre' => 'Euro (EUR)']
    ];

    $tasas = [];

    foreach ($monedas as $codigo => $info) {
        $response = wp_remote_get("https://pydolarve.org/api/v2/tipo-cambio?currency={$codigo}&format_date=timestamp&rounded_price=false");
        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($data['price'])) {
                $tasas[] = [
                    'nombre' => $info['nombre'],
                    'precio' => number_format($data['price'], 2, ',', '.'),
                    'fecha' => date("d/m/Y H:i", $data['last_update'])
                ];
            }
        }
    }

    $output = '<style>
        .bcv-tasas-wrap { max-width: 700px; margin: 20px auto; font-family: Arial, sans-serif; }
        .bcv-tasas-table {
            border-collapse: collapse;
            width: 100%;
            box-shadow: 0 4px 12px #cc0707;
            border-radius: 8px;
            overflow: hidden;
        }
        .bcv-tasas-table thead { background-color:rgb(195, 6, 6); color: #fff; }
        .bcv-tasas-table th, .bcv-tasas-table td {
            padding: 12px 16px;
            text-align: left;
        }
        .bcv-tasas-table tbody tr:nth-child(even) { background-color: #f2f2f2; }
        .bcv-tasas-marquesina {
            white-space: nowrap;
            overflow: hidden;
            background:rgb(197, 0, 0);
            color: #fff;
            padding: 12px;
            border-radius: 8px;
        }
        .bcv-tasas-marquesina span {
            display: inline-block;
            padding-right: 50px;
            animation: marquee 15s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>';

    if ($atts['modo'] === 'marquesina') {
        $output .= '<div class="bcv-tasas-wrap"><div class="bcv-tasas-marquesina"><span>';
        foreach ($tasas as $t) {
            $output .= "{$t['nombre']}: <strong>{$t['precio']} Bs</strong> ({$t['fecha']}) &nbsp;&nbsp;&nbsp;";
        }
        $output .= '</span></div></div>';
    } else {
        $output .= '<div class="bcv-tasas-wrap"><h3 style="text-align:center;">Tasas oficiales del BCV</h3>';
        $output .= '<table class="bcv-tasas-table"><thead><tr><th>Moneda</th><th>Tasa</th><th>Última Actualización</th></tr></thead><tbody>';
        foreach ($tasas as $t) {
            $output .= "<tr><td>{$t['nombre']}</td><td>{$t['precio']} Bs</td><td>{$t['fecha']}</td></tr>";
        }
        $output .= '</tbody></table></div>';
    }

    return $output;
}

add_shortcode('tasas_bcv', 'tasas_bcv_avanzado');
