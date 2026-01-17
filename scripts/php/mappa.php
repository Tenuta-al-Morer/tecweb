<?php
require_once 'common.php';

$ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : null;
$isLogged = isset($_SESSION['utente']);

$sitemapTree = '
<ul class="sitemap-tree-root">
    <li>
        <a href="home.php"><span lang="en">Home</span></a>
        <ul>
            <li><a href="tenuta.php">Tenuta</a></li>
            <li><a href="vini.php">Vini</a></li>
            <li><a href="esperienze.php">Esperienze</a></li>
            <li><a href="contatti.php">Contatti</a></li>
            <li>
                <a href="carrello.php">Carrello</a>';

if ($ruolo === 'user') {
    $sitemapTree .= '
                <ul>
                    <li><a href="checkout.php"><span lang="en">Checkout</span></a></li>
                </ul>';
}

$sitemapTree .= '
            </li>';

if ($ruolo === 'user') {
    $sitemapTree .= '
            <li><a href="areaPersonale.php">Area riservata</a></li>';
}

if ($ruolo === 'staff') {
    $sitemapTree .= '
            <li>
                <a href="gestionale.php">Area gestionale</a>
                <ul>
                    <li><a href="gestionale.php?sezione=ordini">Ordini</a></li>
                    <li><a href="gestionale.php?sezione=esperienze">Richieste esperienze</a></li>
                    <li><a href="gestionale.php?sezione=messaggi">Messaggi clienti</a></li>
                </ul>
            </li>';
}

if ($ruolo === 'admin') {
    $sitemapTree .= '
            <li>
                <a href="gestionale.php">Area gestionale</a>
                <ul>
                    <li><a href="gestionale.php?sezione=ordini">Ordini</a></li>
                    <li><a href="gestionale.php?sezione=esperienze">Richieste esperienze</a></li>
                    <li><a href="gestionale.php?sezione=messaggi">Messaggi clienti</a></li>
                </ul>
            </li>
            <li>
                <a href="admin.php">Amministrazione</a>
                <ul>
                    <li><a href="admin.php?view=vini">Gestione vini</a></li>
                    <li><a href="admin.php?view=utenti">Gestione utenti</a></li>
                </ul>
            </li>';
}

if (!$isLogged) {
    $sitemapTree .= '
            <li>
                <a href="login.php"><span lang="en">Login</span></a>
                <ul>
                    <li><a href="registrazione.php">Registrazione</a></li>
                </ul>
            </li>';
}

$sitemapTree .= '
            <li>
                <a href="policy.php">Note legali</a>
                <ul>
                    <li><a href="policy.php#privacy-policy"><span lang="en">Privacy Policy</span></a></li>
                    <li><a href="policy.php#accessibility">Accessibilita</a></li>
                </ul>
            </li>
            <li><a href="mappa.php">Mappa del sito</a></li>
        </ul>
    </li>
</ul>';

echo caricaPagina('../../html/mappa.html', [
    '[sitemap_tree]' => $sitemapTree
]);
?>
