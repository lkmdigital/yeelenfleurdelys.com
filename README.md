# Yeelen & Fleur de Lys — plateforme de réservation et de gestion

Plateforme du groupe **Hôtel Fleur de Lys** (Tiassalé) et **Résidences Yeelen** (Abidjan),
construite à partir du code BoSéjour et adaptée à un propriétaire unique de plusieurs établissements.

| Espace | URL | Qui |
|---|---|---|
| Site public : accueil + une fiche par établissement + réservation | `/`, `/accommodations/{id}` | Clients |
| Tableau de bord d'un établissement | `/dashboard/host` | Gérant de l'établissement (+ ses réceptionnistes) |
| Administration globale | `/dashboard/admin` | Propriétaire du groupe |

## Organisation des comptes

- **1 administrateur** (le propriétaire) : vue globale « par établissement », réservations, comptabilité, gérants.
- **1 compte gérant par établissement** : son tableau de bord ne montre que son établissement.
  Il invite ses réceptionnistes depuis « Utilisateurs » avec des droits au choix.
- L'inscription publique ne crée **que** des comptes clients. Les comptes gérants sont créés par
  l'administrateur (Utilisateurs → Nouveau, rôle « host ») ou par le seeder.

## Établissements créés par le seeder

`php artisan migrate --seed` crée les rôles, les moyens de paiement, puis
(`database/seeders/EtablissementsSeeder.php`) :

| Établissement | Chambres |
|---|---|
| Hôtel Fleur de Lys — Tiassalé | Chambre simple 25 000 F · Chambre avec balcon 30 000 F |
| Résidences Yeelen — Angré Mahou | Résidence meublée — **tarif à compléter** |
| Résidences Yeelen — Riviera Palmeraie | Résidence meublée — **tarif à compléter** |
| Résidences Yeelen — Koumassi Bia Sud | Résidence meublée — **tarif et téléphone à compléter** |

Les chambres sans tarif sont créées **inactives** (non réservables) : le gérant les active
après avoir saisi le prix. Les mots de passe générés sont affichés **une seule fois** dans la console.
Un compte créé ensuite par l'administrateur reçoit un e-mail pour choisir lui-même son mot de passe.
Le seeder peut être relancé sans créer de doublons ni modifier les mots de passe existants.

## Technique

Une seule application **Laravel 12** (PHP 8.2+, MySQL) : aucun Node.js sur le serveur.

- Pages : Blade (`backend/resources/views/web/`), Tailwind, Alpine.js et Chart.js, compilés
  par Vite dans `backend/public/build` (à refaire avec `npm run build` après toute modification
  de vue ou de style, puis à envoyer sur le serveur).
- Les contrôleurs web (`app/Http/Controllers/Web/`) réutilisent l'API existante
  (`routes/api.php`) par des appels internes (`App\Support\InternalApi`), sans requête HTTP :
  toutes les règles métier restent au même endroit.
- Connexion unique pour tous les comptes : `/auth/login`, puis redirection vers l'espace du compte.
- Couleurs : une seule charte pour tout le site, bleu `#23318C` et jaune `#F5C518`
  (variables `--brand` dans `backend/resources/css/app.css`).
- L'ancien site Next.js a été archivé hors de la plateforme
  (`../archives/frontend-next-ancien`) ; il n'est plus utilisé.

## Développement local

```bash
cd backend
cp .env.example .env    # puis DB_DATABASE / DB_USERNAME / DB_PASSWORD, APP_ENV=local, APP_URL=http://localhost:8000
composer install
npm ci && npm run build # ou « npm run dev » pendant le travail sur les vues
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8000
```

Tests : `cd backend && php artisan test`.

## Retiré par rapport à BoSéjour

Inscription des partenaires externes, conformité et documents partenaires, inspections, retraits et
commissions côté partenaire, parrainage, base touristique et « Découvertes », espace entreprise,
assistants IA, film et animation d'introduction, contenus marketplace de l'accueil (fausses offres,
destinations). Le code backend correspondant reste en place mais n'est plus exposé dans l'interface.

## À compléter avec le client

- Tarifs, nombre et photos des résidences Yeelen ; téléphone de Koumassi Bia Sud.
- Photos de l'Hôtel Fleur de Lys (la fiche affiche « Photos à venir » en attendant).
- Positions GPS exactes (le seeder utilise des positions approximatives).
- Mentions légales : CGU, CGV et politique de confidentialité (Admin → Mentions légales).
  Les textes hérités ont été renommés mais **doivent être relus** : raison sociale, RCCM, adresse.
- Comptes de paiement (MaliaPay), e-mail d'envoi (SMTP), WhatsApp Business : comptes propres au client.
- Logo : le site utilise un logo texte provisoire (« YF » + nom), à remplacer si le client en fournit un.

Déploiement : voir [DEPLOIEMENT-HOSTINGER.md](DEPLOIEMENT-HOSTINGER.md).
