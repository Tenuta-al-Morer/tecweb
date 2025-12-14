<?php

session_start();
require_once 'common.php';
require_once 'DBConnection.php'; 
use DB\DBConnection;

if (!isset($_SESSION['utente_id'])) { 
    header("location: login.php");
    exit();
}

$idUtente = $_SESSION['utente_id']; 
$ruoloUtente = $_SESSION['ruolo'];

if ($ruoloUtente !== 'user') {
    header("location: 403.php");
    exit();
}

$db = new DBConnection();
$ordini = $db->getOrdiniUtente($idUtente);
$db->closeConnection();

function formatDate($dateString) {
    return (new DateTime($dateString))->format('d/m/Y H:i');
}

function getStatusBadge($stato) {
    $mappatura = [
        'in_attesa' => 'In attesa di pagamento',
        'pagato' => 'Pagato/In lavorazione',
        'in_preparazione' => 'In Preparazione',
        'spedito' => 'Spedito',
        'consegnato' => 'Consegnato',
        'annullato' => 'Annullato'
    ];
    $testo = $mappatura[$stato] ?? 'Sconosciuto';
    return '<span class="order-status-badge status-' . $stato . '">' . htmlspecialchars($testo) . '</span>';
}

// GENERAZIONE TABELLA ORDINI
$tabellaOrdini = '';

if (empty($ordini)) {
    $tabellaOrdini = '<div class="alert-box"><p><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Non hai ancora effettuato ordini. Visita la sezione <a href="vini.php">Vini</a>!</p></div>';
} else {
    $tabellaOrdini .= '<div class="table-container">';
    $tabellaOrdini .= '<table class="table-data order-summary-table">';
    $tabellaOrdini .= '<caption>Storico dei tuoi ordini</caption>';
    $tabellaOrdini .= '<thead><tr>';
    $tabellaOrdini .= '<th>N. Ordine</th>';
    $tabellaOrdini .= '<th>Data</th>';
    $tabellaOrdini .= '<th>Stato</th>';
    $tabellaOrdini .= '<th class="td_richiesta_degustazione">Totale</th>';
    $tabellaOrdini .= '<th class="td_richiesta_degustazione">Dettagli</th>';
    $tabellaOrdini .= '</tr></thead><tbody>';

    foreach ($ordini as $ordine) {
        $id_ordine = htmlspecialchars($ordine['id']);
        
        $tabellaOrdini .= '<tr data-order-id="' . $id_ordine . '">';
        $tabellaOrdini .= '<td data-title="N. Ordine">#' . $id_ordine . '</td>';
        $tabellaOrdini .= '<td data-title="Data">' . formatDate($ordine['data_creazione']) . '</td>';
        $tabellaOrdini .= '<td data-title="Stato">' . getStatusBadge($ordine['stato_ordine']) . '</td>';
        $tabellaOrdini .= '<td data-title="Totale" class="td_richiesta_degustazione">€ ' . number_format($ordine['totale_finale'], 2, ',', '.') . '</td>';
        $tabellaOrdini .= '<td class="td_richiesta_degustazione">';
        $tabellaOrdini .= '<button type="button" class="btn-secondary toggle-details-btn" data-order-id="' . $id_ordine . '" aria-expanded="false" aria-controls="details-row-' . $id_ordine . '">';
        $tabellaOrdini .= 'Mostra <i class="fas fa-chevron-down" aria-hidden="true"></i>';
        $tabellaOrdini .= '</button>';
        $tabellaOrdini .= '</td>';
        $tabellaOrdini .= '</tr>';
        
        // Riga dettagli 
        $tabellaOrdini .= '<tr class="order-details-row is-hidden" id="details-row-' . $id_ordine . '">';
        $tabellaOrdini .= '<td colspan="5" class="order-details-cell">';
        $tabellaOrdini .= '<div class="details-content">';
        
        // Sezione Prodotti
        $tabellaOrdini .= '<div class="details-section">';
        $tabellaOrdini .= '<h4>Prodotti Ordinati:</h4>';
        $tabellaOrdini .= '<ul class="details-products-list">';
        foreach ($ordine['elementi'] as $item) {
            $tabellaOrdini .= '<li>';
            $tabellaOrdini .= '<span>' . htmlspecialchars($item['quantita']) . 'x ' . htmlspecialchars($item['nome_vino_storico']) . '</span>';
            $tabellaOrdini .= '<span>€ ' . number_format($item['prezzo_acquisto'], 2, ',', '.') . ' (cad.)</span>';
            $tabellaOrdini .= '</li>';
        }
        $tabellaOrdini .= '</ul>';
        $tabellaOrdini .= '</div>'; 
        
        // Sezione Riepilogo e Indirizzo
        $tabellaOrdini .= '<div class="details-section">';
        $tabellaOrdini .= '<h4>Riepilogo e Spedizione:</h4>';
        $tabellaOrdini .= '<p><strong>Pagamento:</strong> ' . htmlspecialchars($ordine['metodo_pagamento']) . '</p>';
        $tabellaOrdini .= '<p><strong>Indirizzo Spedizione:</strong> ' . nl2br(htmlspecialchars($ordine['indirizzo_spedizione'])) . '</p>';
        
        $tabellaOrdini .= '<div class="details-summary">';
        $tabellaOrdini .= '<p>Totale Prodotti: € ' . number_format($ordine['totale_prodotti'], 2, ',', '.') . '</p>';
        $tabellaOrdini .= '<p>Costo Spedizione: € ' . number_format($ordine['costo_spedizione'], 2, ',', '.') . '</p>';
        $tabellaOrdini .= '<p><strong>Totale Finale: <span>€ ' . number_format($ordine['totale_finale'], 2, ',', '.') . '</span></strong></p>';
        $tabellaOrdini .= '</div>'; 
        
        $tabellaOrdini .= '</div>'; 
        
        $tabellaOrdini .= '</div>'; 
        $tabellaOrdini .= '</td>';
        $tabellaOrdini .= '</tr>';
    }

    $tabellaOrdini .= '</tbody></table>';
    $tabellaOrdini .= '</div>';
}

$htmlContent = caricaPagina('../../html/user.html');
$emailUtente = htmlspecialchars($_SESSION['utente']);

$htmlContent = str_replace("[email_utente]", $emailUtente, $htmlContent);
$htmlContent = str_replace("[riferimento]", $ruoloUtente, $htmlContent);
$htmlContent = str_replace("[TABELLA_ORDINI]", $tabellaOrdini, $htmlContent); 

echo $htmlContent;

?>