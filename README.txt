CASA VERDE - HEADER MENU FIX
============================

What it does
  Adds the missing menu rows (Reviews, Journal, Yoga Reset) and puts the header in this order:
  Villas, Experiences, Wellness, Dining, Long Stay, DISCOVER (Reviews + Journal), YOGA RESET.
  About and Contact are switched off in the header (still in the admin and footer).

How to use (local, Laravel Herd)
  1. Extract this zip INTO your project folder (C:\Projects\casa-verde) so that the file lands in
     database\migrations\2026_11_16_000100_fix_header_menu_rows.php
  2. In PowerShell, inside the project folder:
        php artisan migrate
        php artisan optimize:clear
  3. Hard refresh the browser (Ctrl+F5).

For the live site (cPanel)
  Commit and push the file, pull it on the server, then run:
        php artisan migrate --force
        php artisan optimize:clear

Safe to run more than once - it never creates duplicate rows.
