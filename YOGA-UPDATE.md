# Yoga Reset page update

1. Copy these folders over your project (they only contain code, no photos or database):
   app, config, database, resources, routes, tests.
2. Run `php artisan migrate`  (loads the starting content into the admin; or `php artisan db:seed --class=YogaContentSeeder`).
3. Run `npm run build`        (compiles the new styles).
4. Open the admin > Yoga Reset. Page layout switches blocks on or off and changes their order.
5. Optional: `php artisan test --filter=YogaPageTest`
