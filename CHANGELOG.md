# Historique des modifications — Gestion des DMA

Ce journal résume les changements du projet. Le détail technique reste dans Git.

## Non publié

### Ajouts

- Espace exploitation : Dashboard, file filtrée, décisions personnelles, fiche avec accord/refus, visa et motif de refus ; décision transactionnelle sans doublon, brouillons non exposés. Socle visuel commun aux prochains rôles. Correction du rendu conditionnel de la fiche partagée et du typage nullable PHP.

- Espace demandeur : dashboard personnel, liste filtrée et paginée, formulaires, suivi détaillé, réception avec visa, notifications, profil et navigation mobile en jour/nuit.
- Tests d’intégration des droits et transitions du demandeur, avec base de test partagée en mémoire.

- Pages admin harmonisées pour utilisateurs, demandes, détail DMA, notifications et profil ; recherche, filtres, pagination et formulaires adaptés aux thèmes jour/nuit.
- Marquage des notifications personnelles comme lues et tests d’intégration isolés en mémoire.

- Modes jour et nuit dans la navbar admin, préférence mémorisée dans le navigateur et thème initial adapté au système.

- 2026-11-10 : création des fichiers `VERSION`, `CHANGELOG.md` et `SUIVI_PROJET.md` pour suivre les versions, les modifications et l’avancement.
- Ajout de `JOURNAL.md` pour les séances et de `PROGRESSION.md` pour l’état global ; centralisation des tâches dans `SUIVI_PROJET.md`.
- Ajout des liens de suivi dans le README.

- Documentation du démarrage local dans `DEMARRAGE_LOCAL.md` et mise à jour de la progression (DMA-002).

### Modifications

- Cartes admin restructurées avec icônes, valeurs alignées, repères colorés et contours renforcés ; séparation plus nette des titres, filtres et tableaux en jour et nuit.

- Mode nuit admin sur fonds noirs et surfaces noir brillant, avec accents SIR ; remplacement des badges pastel par une palette vive commune aux demandes de tous les rôles.

- Charte SIR fixée au vert `#2E8B57` et à l’orange `#FF8C00`, appliquée aux composants admin en jour et nuit ; contrastes des libellés ajustés et charte documentée.

- Navbar admin harmonisée avec les couleurs vert et orange SIR ; déclinaison sombre du dashboard et des éléments de navigation.

- Ajustement du dashboard admin : accents orange SIR dans la navigation, le bouton utilisateurs et les graphiques ; retrait des textes explicatifs secondaires et simplification du titre.

- Refonte du dashboard administrateur : navigation SIR, demandes avant les graphiques, recherche, filtres par étape, pagination française, accès au détail et présentation mobile en cartes.
- Répartition complète des demandes, libellés métier et graphiques sans Chart.js externe ; calcul admin centralisé.

### Corrections

- Isolation des demandes et des statistiques par auteur ; soumission réservée aux brouillons complets, réception réservée à l’auteur au statut attendu ; protections contre les doubles actions et les suppressions de pièces après soumission.
- Structures spécialisées : modèle et validations alignés sur `structures_specialisees`, déjà référencée par les associations ; correction des migrations source pour les nouvelles installations et tests portables du statut `cloturee`.

- Création utilisateur avec les rôles existants et redirections vers `users.index` ; validation des rôles et protection du compte admin courant dans la gestion utilisateurs.
- Création de demande : redirection après tous les fichiers, y compris sans pièce jointe.
- Notifications : affichage des champs action/objet lorsque le message est absent et authentification requise.

- Les travaux terminés ne sont plus comptés parmi les refus ; les brouillons et les statuts inconnus sont pris en compte dans la répartition.

### Vérifications

- Ensemble admin/demandeur : 20 tests réussis (126 assertions) en mémoire ; compilation et revue visuelle ordinateur/mobile. Aucun dossier réel modifié pendant les vérifications.

- Pages admin : 12 tests réussis (62 assertions), compilation Vite et Blade, contrôle navigateur des nouvelles pages et d’un formulaire mobile en mode nuit.

- Dashboard admin : 4 tests unitaires réussis (9 assertions), syntaxe PHP, Vite et Blade vérifiés ; recherche, filtres, pagination, détail et rendu ordinateur/mobile contrôlés.

- 2026-11-10 : `php artisan migrate --seed` exécuté avec succès sur MySQL local ; migrations à jour, `RoleSeeder` et `UserSeeder` exécutés ; 28 utilisateurs, 34 demandes et 9 rôles après exécution.

- Fichiers de suivi et liens locaux vérifiés.
- Pour le démarrage local : compilation Vite réussie, connexion MySQL et état des migrations vérifiés, formulaire de connexion et fichiers CSS/JS accessibles en HTTP 200. Parcours métier non vérifiés.

## Point de départ du suivi — 2026-11-10

- Référence Git : `b3971f5`, sur `main` lors de la récupération du dépôt.
- Version de travail initiale : `0.1.0-dev`. Ce numéro initialise le suivi et ne correspond pas à une livraison validée.
- Les fonctionnalités existantes restent à vérifier ; leur présence dans le code ne garantit pas leur bon fonctionnement.
- L’historique antérieur reste accessible avec `git log`. Aucune version publiée n’est reconstituée à partir de ces commits.

## Comment tenir ce journal

1. Ajouter les changements dans « Non publié », sous Ajouts, Corrections, Modifications ou Suppressions selon le besoin.
2. Indiquer les vérifications réellement effectuées et les limites restantes.
3. À la livraison, regrouper les entrées sous `X.Y.Z — AAAA-MM-JJ`, mettre à jour `VERSION`, puis créer le tag Git correspondant après commit.
4. Conserver une section « Non publié » pour la suite.
