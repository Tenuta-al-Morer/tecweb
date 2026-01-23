<?php
require_once 'common.php';

$ruoloUtente = $_SESSION['ruolo'] ?? 'guest'; 

$struttura = [
    'Home' => [
        'link' => 'home.php',
        'lang' => 'en',
        'breadcrumb' => ['Home'],
        'sub' => [
            'Tenuta' => [
                'link' => 'tenuta.php',
                'lang' => 'it',
                'breadcrumb' => ['Home', 'Tenuta']
            ],
            'Vini' => [
                'link' => 'vini.php',
                'lang' => 'it',
                'breadcrumb' => ['Home', 'Vini']
            ],
            'Esperienze' => [
                'link' => 'esperienze.php',
                'lang' => 'it',
                'breadcrumb' => ['Home', 'Esperienze']
            ],
            'Contatti' => [
                'link' => 'contatti.php',
                'lang' => 'it',
                'breadcrumb' => ['Home', 'Contatti']
            ],
            'Carrello' => [
                'link' => 'carrello.php',
                'lang' => 'it',
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
                        'lang' => 'it',
                        'breadcrumb' => ['Home', 'Login', 'Registrazione']
                    ]
                ]
            ],
            'Logout' => [
                'link' => 'logout.php',
                'lang' => 'en',
                'breadcrumb' => ['Home', 'Logout']
            ],
            'Area riservata' => [
                'link' => 'areaPersonale.php',
                'lang' => 'it',
                'breadcrumb' => ['Home', 'Area Riservata'],
                'sub' => [
                    'I Miei Ordini' => [
                        'link' => 'areaPersonale.php?view=esperienze#ordini', 
                        'lang' => 'it',
                        'breadcrumb' => ['Home', 'Area Riservata', 'Ordini']
                    ],
                    'Le Mie Esperienze' => [
                        'link' => 'areaPersonale.php?view=esperienze#esperienze', 
                        'lang' => 'it',
                        'breadcrumb' => ['Home', 'Area Riservata', 'Esperienze']
                    ],
                    'Dati Personali' => [
                        'link' => 'areaPersonale.php?view=esperienze#dati', 
                        'lang' => 'it',
                        'breadcrumb' => ['Home', 'Area Riservata', 'Profilo']
                    ],
                    'Sicurezza/Accesso' => [
                        'link' => 'areaPersonale.php?view=sicurezza#sicurezza', 
                        'lang' => 'it',
                        'breadcrumb' => ['Home', 'Area Riservata', 'Sicurezza']
                    ]
                ]
            ],
            'Area gestionale' => [
                'link' => 'gestionale.php',
                'lang' => 'it',
                'breadcrumb' => ['Home', 'Area Gestionale'],
                'sub' => [
                    'Ordini' => ['link' => 'gestionale.php?sezione=ordini', 'lang' => 'it'],
                    'Richieste esperienze' => ['link' => 'gestionale.php?sezione=esperienze', 'lang' => 'it'],
                    'Messaggi clienti' => ['link' => 'gestionale.php?sezione=messaggi', 'lang' => 'it']
                ]
            ],
            'Amministrazione' => [
                'link' => 'admin.php',
                'lang' => 'it',
                'breadcrumb' => ['Home', 'Amministrazione'],
                'sub' => [
                    'Gestione vini' => ['link' => 'admin.php?view=vini', 'lang' => 'it'],
                    'Gestione utenti' => ['link' => 'admin.php?view=utenti', 'lang' => 'it']
                ]
            ],
            'Note legali' => [
                'link' => 'policy.php',
                'lang' => 'it',
                'breadcrumb' => ['Home', 'Policy'],
                'sub' => [
                    'Privacy Policy' => [
                        'link' => 'policy.php#privacy-policy', 
                        'lang' => 'en',
                        'breadcrumb' => ['Home', 'Policy', 'Privacy Policy']
                    ],
                    'Accessibilità' => [
                        'link' => 'policy.php#accessibility',
                        'lang' => 'it',
                        'breadcrumb' => ['Home', 'Policy', 'Accessibilità']
                    ]
                ]
            ],
            'Mappa del sito' => [
                'link' => 'mappa.php',
                'lang' => 'it',
                'breadcrumb' => ['Home', 'Mappa del sito']
            ]
        ]
    ]
];

function ottieniChiaviDaEscludere($ruolo) {
    $daEscludere = [];

    switch ($ruolo) {
        case 'guest':
            $daEscludere = ['Amministrazione', 'Area gestionale', 'Checkout', 'Area riservata', 'Logout'];
            break;

        case 'user': 
            $daEscludere = ['Amministrazione', 'Area gestionale', 'Login'];
            break;

        case 'staff':
            $daEscludere = ['Amministrazione', 'Area riservata', 'Checkout', 'Carrello', 'Login'];
            break;

        case 'admin':
            $daEscludere = ['Area riservata', 'Checkout', 'Carrello', 'Login'];
            break;
    }

    return $daEscludere;
}

function filtraStruttura($items, $esclusioni) {
    $risultato = [];
    foreach ($items as $chiave => $dati) {
        if (in_array($chiave, $esclusioni)) {
            continue;
        }
        if (isset($dati['sub']) && !empty($dati['sub'])) {
            $dati['sub'] = filtraStruttura($dati['sub'], $esclusioni);
        }
        $risultato[$chiave] = $dati;
    }
    return $risultato;
}

$chiaviVietate = ottieniChiaviDaEscludere($ruoloUtente);
$strutturaFiltrata = filtraStruttura($struttura, $chiaviVietate);

$sitemapHTML = generaListaHTML($strutturaFiltrata);
$subHome = $strutturaFiltrata['Home']['sub'] ?? [];
$totalePagine = count($subHome);
$totaleSezioni = countTotalPages($strutturaFiltrata);

function generaListaHTML($items, $livello = 0) {
    if (empty($items)) return '';
    $classeLivello = 'level-' . min($livello, 3);
    $html = '<ul class="tree-list ' . $classeLivello . '">';
    foreach ($items as $label => $data) {
        $link = $data['link'] ?? '#';
        $sub = $data['sub'] ?? [];
        $lang = $data['lang'] ?? 'it';
        $breadcrumbText = isset($data['breadcrumb']) ? implode(' › ', $data['breadcrumb']) : '';
        $classeItem = ($livello > 0) ? ' class="child-item"' : '';
        $html .= '<li' . $classeItem . '>';
        $html .= '<a href="' . htmlspecialchars($link) . '" lang="' . $lang . '" aria-label="' . htmlspecialchars($label) . '"';
        if ($breadcrumbText) $html .= ' title="Percorso: ' . htmlspecialchars($breadcrumbText) . '"';
        $html .= '>' . htmlspecialchars($label) . '</a>';
        if (!empty($sub)) $html .= generaListaHTML($sub, $livello + 1);
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

echo caricaPagina('../../html/mappa.html', [
    '[sitemap_content]' => $sitemapHTML,  
    '[totale_pagine]'   => $totalePagine, 
    '[totale_sezioni]'  => $totaleSezioni 
]);
?>