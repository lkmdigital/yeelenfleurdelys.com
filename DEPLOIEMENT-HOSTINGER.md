# Déploiement sur Hostinger (hébergement mutualisé) — yeelenfleurdelys.com

La plateforme est **une seule application Laravel 12** (PHP 8.2+ et MySQL). Le site public,
l'espace client, les tableaux de bord des établissements et l'administration sont servis par
Laravel. **Node.js n'est pas nécessaire sur le serveur** : toutes les offres mutualisées Hostinger
conviennent.

| Élément | Adresse |
|---|---|
| Site, espaces et API | `https://yeelenfleurdelys.com` (API sous `/api`) |
| Connexion (tous les comptes) | `https://yeelenfleurdelys.com/auth/login` |

## 0. Préparer les fichiers (sur l'ordinateur)

Les styles et scripts sont compilés **avant** l'envoi :

```bash
cd plateforme/backend
npm ci
npm run build          # produit public/build/
```

À envoyer : le contenu de `backend/` **avec** `public/build/`, **sans** `vendor/`,
`node_modules/`, `.env`, `storage/logs/*` ni `public/storage` (lien recréé sur le serveur).

À refaire après chaque modification de vue ou de style : `npm run build`, puis renvoyer
`public/build/` (et les fichiers modifiés).

## 1. Base de données (hPanel → Bases de données → MySQL)

Créer une base, un utilisateur et un mot de passe. Noter les trois valeurs.

## 2. Envoyer l'application

1. Envoyer le contenu de `backend/` dans `~/domains/yeelenfleurdelys.com/laravel/`
   (Gestionnaire de fichiers ou SFTP). Le code ne doit **pas** être dans `public_html`.
2. Faire pointer le domaine sur le dossier `public` de Laravel. En SSH
   (hPanel → Avancé → Accès SSH) :

   ```bash
   cd ~/domains/yeelenfleurdelys.com
   mv public_html public_html.ancien      # garder l'ancien contenu le temps de vérifier
   ln -s laravel/public public_html
   ```

## 3. Installer (SSH)

```bash
cd ~/domains/yeelenfleurdelys.com/laravel
composer install --no-dev --optimize-autoloader
cp .env.example .env
nano .env      # voir les valeurs ci-dessous
php artisan key:generate
php artisan migrate --seed --force      # noter les mots de passe affichés : à transmettre au client
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Valeurs `.env` à renseigner :

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yeelenfleurdelys.com
FRONTEND_URL=https://yeelenfleurdelys.com
DB_DATABASE=...  DB_USERNAME=...  DB_PASSWORD=...
SESSION_DOMAIN=null
SANCTUM_STATEFUL_DOMAINS=yeelenfleurdelys.com,www.yeelenfleurdelys.com
MAIL_HOST=smtp.hostinger.com  MAIL_PORT=465  MAIL_SCHEME=smtps
MAIL_USERNAME=reservations@yeelenfleurdelys.com  MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=reservations@yeelenfleurdelys.com
ADMIN_EMAIL=adresse-du-proprietaire@...   # compte administrateur créé par le seeder
```

Paiement en ligne (MaliaPay), WhatsApp Business, SMS et Google Maps : renseigner les clés
**du client** quand ses comptes sont ouverts. Sans clés, la réservation fonctionne avec
paiement sur place / garantie et confirmation par e-mail.

Après toute modification du `.env` : `php artisan config:cache`.

### Modèles WhatsApp à créer chez Meta

Les messages WhatsApp automatiques passent par des **modèles** à créer dans le WhatsApp Manager
du client (catégorie « Utilitaire », langue « fr »), **exactement sous ces noms** et avec les
variables dans cet ordre. Tant qu'un modèle n'est pas approuvé, le site se rabat sur l'e-mail.

| Modèle | Variables, dans l'ordre |
|---|---|
| `yfl_reservation_confirmee` | {{1}} établissement · {{2}} n° de réservation · {{3}} arrivée · {{4}} départ |
| `yfl_nouvelle_demande` (au gérant) | {{1}} établissement · {{2}} arrivée · {{3}} départ · {{4}} délai de réponse |
| `yfl_demande_recue` (au client) | aucune |
| `yfl_demande_acceptee` | {{1}} établissement · {{2}} arrivée · {{3}} départ · bouton « Payer » vers `https://yeelenfleurdelys.com/paiement/{{1}}` |
| `yfl_code_verification` | {{1}} code (modèle « Authentification » avec bouton « Copier le code ») |
| `yfl_alerte_supervision` (équipe) | {{1}} niveau · {{2}} titre · {{3}} détail |
| `yfl_rapport_megabo` (équipe) | {{1}} date · {{2}} résumé |

## 4. Tâches planifiées (hPanel → Avancé → Tâches Cron, toutes les minutes)

```
cd ~/domains/yeelenfleurdelys.com/laravel && php artisan schedule:run >> /dev/null 2>&1
cd ~/domains/yeelenfleurdelys.com/laravel && php artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
```

## 5. SSL

hPanel → Sécurité → SSL : activer pour `yeelenfleurdelys.com` et `www.yeelenfleurdelys.com`.

Vérifications :

- `https://yeelenfleurdelys.com` affiche l'accueil avec les 4 établissements ;
- `https://yeelenfleurdelys.com/api/accommodations` renvoie les 4 établissements.

## 6. Mise à jour du site plus tard

```bash
# sur l'ordinateur
cd plateforme/backend && npm run build
# envoyer les fichiers modifiés + public/build/, puis en SSH :
cd ~/domains/yeelenfleurdelys.com/laravel
composer install --no-dev --optimize-autoloader     # si composer.json a changé
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## 7. Après la mise en ligne

1. Se connecter sur `https://yeelenfleurdelys.com/auth/login` avec le compte administrateur.
2. Changer tous les mots de passe générés (chaque gérant le fait depuis Paramètres → Sécurité).
3. Pour chaque établissement : ajouter les photos, compléter les tarifs des résidences Yeelen
   et activer les chambres.
4. Relire les mentions légales (Admin → Mentions légales).
5. Faire une réservation test de bout en bout sur chaque établissement.
6. Une fois tout vérifié, supprimer `public_html.ancien`.
