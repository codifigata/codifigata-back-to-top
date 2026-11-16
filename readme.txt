=== Codifigata Back to Top ===
Contributors: codifigata
Tags: back to top, scroll to top, button, accessibility
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pulsante "torna su" leggero, accessibile e senza file esterni.

== Description ==

Aggiunge un pulsante per tornare all'inizio della pagina con scorrimento fluido.

* Nessun file CSS o JS esterno: tutto inline nel footer, peso trascurabile.
* Colore automatico: usa il colore principale di Elementor o della palette del tema, oppure un colore a scelta.
* Tre stili (tenue, pieno, contorno), tre forme, due icone, posizione a destra o a sinistra.
* Accessibile: pulsante vero con etichetta, focus visibile da tastiera, rispetta "riduci movimento".
* Nascosto in stampa e negli editor visuali (Elementor, Bricks, Divi, Beaver Builder, Oxygen, Brizy).
* Tradotto in italiano.

Impostazioni in Impostazioni > Torna su.

== Per sviluppatori ==

Nascondere il pulsante su alcune pagine:

`add_filter( 'codifigata_btt_enabled', function ( $show ) { return is_front_page() ? false : $show; } );`

Forzare le impostazioni da codice:

`add_filter( 'codifigata_btt_settings', function ( $s ) { $s['color'] = '#1d4f91'; return $s; } );`

== Changelog ==

= 1.0.0 =
* Prima versione.
