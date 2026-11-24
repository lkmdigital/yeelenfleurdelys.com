import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Site public + espaces client / gérant / admin (resources/css/app.css, resources/js/app.js)
// et espace interne « Ops ». Compilé une fois (npm run build) : public/build est envoyé tel
// quel sur l'hébergement, aucun Node.js n'est nécessaire sur le serveur.
export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/ops.css', 'resources/js/ops.js'],
      refresh: true,
    }),
  ],
});
