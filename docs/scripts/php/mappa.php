<?php
require_once 'common.php';

// Struttura completa del sito 
$struttura = [
    'Home' => [
        'link' => 'home.php',
        'lang' => 'en',
        'breadcrumb' => ['Home'],
        'sub' => [
            'Tenuta' => [
                'link' => 'tenuta.php',
                'breadcrumb' => ['Home', 'Tenuta']
            ],
            'Vini' => [
                'link' => 'vini.php',
                'breadcrumb' => ['Home', 'Vini']
            ],
            'Esperienze' => [
                'link' => 'esperienze.php',
                'breadcrumb' => ['Home', 'Esperienze']
            ],
            'Contatti' => [
                'link' => 'contatti.php',
                'breadcrumb' => ['Home', 'Contatti']
            ],
            'Carrello' => [
                'link' => 'carrello.php',
                'breadcrumb' => ['Home', 'Carrello'],
                'sub' => [
                    'Checkout' => [
                        'link' => 'checkout.php',
                        'lang' => 'en',
                        'breadcrumb' => ['Home', 'Carrello', 'Spedizione']
                    ]
                ]
            ],
            'Login' => [
                'link' => 'login.php',
                'lang' => 'en',
                'breadcrumb' => ['Home', 'Login'],
                'sub' => [
                    'Registrazione' => [
                        'link' => 'registrazione.php',
                        'breadcrumb' => ['Home', 'Login', 'Registrazione']
                    ]
                ]
            ],
            'Area riservata' => [
                'link' => 'areaPersonale.php',
                'breadcrumb' => ['Home', 'Area Riservata']
            ],
            'Area gestionale' => [
                'link' => 'gestionale.php',
                'breadcrumb' => ['Home', 'Area Gestionale'],
                'sub' => [
                    'Ordini' => ['link' => 'gestionale.php?sezione=ordini'],
                    'Richieste esperienze' => ['link' => 'gestionale.php?sezione=esperienze'],
                    'Messaggi clienti' => ['link' => 'gestionale.php?sezione=messaggi']
                ]
            ],
            'Amministrazione' => [
                'link' => 'admin.php',
                'breadcrumb' => ['Home', 'Amministrazione'],
                'sub' => [
                    'Gestione vini' => ['link' => 'admin.php?view=vini'],
                    'Gestione utenti' => ['link' => 'admin.php?view=utenti']
                ]
            ],
            'Note legali' => [
                'link' => 'policy.php',
                'breadcrumb' => ['Home', 'Policy'],
                'sub' => [
                    'Privacy Policy' => [
                        'link' => 'policy.php#privacy-policy', 
                        'lang' => 'en',
                        'breadcrumb' => ['Home', 'Policy', 'Privacy Policy']
                    ],
                    'Accessibilità' => [
                        'link' => 'policy.php#accessibility',
                        'breadcrumb' => ['Home', 'Policy', 'Accessibilità']
                    ]
                ]
            ],
            'Mappa del sito' => [
                'link' => 'mappa.php',
                'breadcrumb' => ['Home', 'Mappa del sito']
            ]
        ]
    ]
];

/* Funzione ricorsiva per generare la mappa HTML */
function generaListaHTML($items, $livello = 0) {
    if (empty($items)) return '';
    
    $classeLivello = 'level-' . min($livello, 3);
    
    $html = '<ul class="tree-list ' . $classeLivello . '">';
    
    foreach ($items as $label => $data) {
        $link = $data['link'] ?? '#';
        $sub = $data['sub'] ?? [];
        $langAttr = isset($data['lang']) ? ' <span lang="'.$data['lang'].'">' : '';
        $langClose = isset($data['lang']) ? '</span>' : '';
        $breadcrumb = isset($data['breadcrumb']) ? ' title="Percorso: ' . implode(' &gt; ', $data['breadcrumb']) . '"' : '';
        
        $classeItem = ($livello > 0) ? ' class="child-item"' : '';

        $html .= '<li' . $classeItem . '>';
        $html .= '<a href="' . htmlspecialchars($link) . '"' . $breadcrumb . '>' . 
                 $langAttr . htmlspecialchars($label) . $langClose . '</a>';
        
        if (!empty($sub)) {
            $html .= generaListaHTML($sub, $livello + 1);
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    return $html;
}

function countTotalPages($items) {
    $count = 0;
    foreach ($items as $item) {
        $count++;
        if (isset($item['sub'])) {
            $count += countTotalPages($item['sub']);
        }
    }
    return $count;
}

$sitemapHTML = generaListaHTML($struttura);

$totalePagine = count($struttura['Home']['sub']);
$totaleSezioni = countTotalPages($struttura);


echo caricaPagina('../../html/mappa.html', [
    '[sitemap_content]' => $sitemapHTML,  
    '[totale_pagine]'   => $totalePagine, 
    '[totale_sezioni]'  => $totaleSezioni 
]);
?>