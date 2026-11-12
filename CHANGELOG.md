# Historique des modifications — Gestion des DMA

Ce journal résume les changements du projet. Le détail technique reste dans Git.

## Non publié

### Ajouts

- Revue d’avancement datée dans REVUE_PROJET.md : réalisations locales, travaux restants, état des données et consolidation Git ; relance du serveur et 141 tests vérifiés.

- Connexion et inscription harmonisées avec la charte orange/verte, thème jour/nuit partagé avec l’application et photo de raffinerie conservée en filigrane avec sa coloration vert/orange d’origine. Formulaires mobiles, visibilité indépendante des mots de passe, erreurs associées aux champs et focus sur le résumé ; inscription limitée au rôle demandeur, règles d’authentification préservées.

- Règle de clôture corrigée : le demandeur propriétaire réceptionne et clôture sa DMA après travaux depuis Mes réceptions ou la fiche, avec visa et confirmation explicite. Autorisation recontrôlée sous verrou, décision/date/notifications transactionnelles ; rôle réception en suivi sans pouvoir de clôture, archives et diagramme cohérents.

- Archives des demandes définitivement clôturées : navigation commune jour/nuit/mobile, recherche, filtres par unité/année/période de clôture et pagination ; dossier complet en lecture seule avec droits existants conservés.
- Date de clôture enregistrée à la réception définitive et index de consultation ; les dates historiques inconnues restent non renseignées. L’historique des décisions stockées au jour n’affiche plus une heure de minuit artificielle.

- Conservation des dossiers : désactivation/réactivation des utilisateurs, contrôle des comptes actifs à la connexion/session/récupération et quatre références métier RESTRICT ; sessions et jetons révoqués.
- Numérotation DMA annuelle atomique et unique, sélections spécialisées sans doublons et statut non nullable ; références historiques conservées, aucune obligation de structure spécialisée.
- Accès MySQL applicatif restreint en local, commande de migration administrative séparée et création confidentielle d’un administrateur ; seeders de démonstration réservés à local/testing et modèle d’environnement sans secrets.

- Audit documentaire des migrations et de la base MySQL locale : mesures d’intégrité, écarts du schéma, conservation des dossiers, numérotation et plan entreprise dans AUDIT_BASE_DONNEES.md ; aucune donnée ni migration modifiée.

- Configuration Docker optionnelle durcie : exécution non-root, images officielles épinglées par digest, secrets à l’exécution, build multi-stage avec verrouillages et contexte nettoyé ; aucun conteneur démarré.

- Validation HTTP renforcée : schéma partagé des DMA, limites de listes/fichiers/textes, authentification et pagination typées ; tests prouvant la liaison des valeurs SQL et le rejet sans écriture des entrées invalides.

- Dépendances corrigées : Laravel 12.72.0 verrouillé, mises à jour npm compatibles et outils PHP de développement corrigés ; audits npm et Composer sans alerte, 92 tests réussis.

- Sécurité des dépendances : Axios mis à jour et verrouillé en version 1.21.1 ; les cinq avis GHSA signalés sont absents pour cette version dans OSV et npm.

- Préparation sécurité : debug désactivé hors local/testing, CORS fermé par défaut avec origines exactes configurables et réglages de déploiement corrigés ; secret en clair retiré du manifeste Render.

- Routes et accès : déclarations dédupliquées, protections par rôle, anciennes actions hors circuit neutralisées, alias de clôture distincts et tests des accès croisés entre les neuf rôles.

- Notifications du circuit DMA aux intervenants concernés et au demandeur, avec affectations des structures respectées ; filtres, lecture globale, ouverture sécurisée et compteur actualisé automatiquement.

- Diagramme d’avancement sur chaque fiche DMA : étapes franchies, étape actuelle, étapes à venir, refus et structures non requises ; rendu horizontal sur ordinateur et vertical sur mobile.

- Fiche de consultation : distinction renforcée entre libellés et valeurs en modes jour et nuit, avec séparations entre les informations.

- Espace réception : Clôture définitive après travaux, réservée à la réception ; visa, historique et notification ; demandeur en consultation. Circuit complet testé.

- Espace chef de structure : Consultation après validation exploitation, suivi des dossiers en cours, clôturés et refusés, sans nouveau pouvoir de décision.

- Espace service technique : Démarrage et fin des travaux distincts, dates cohérentes, compte rendu et historique des interventions.

- Espace contrôle avancé : File de contrôle, avis sécurisés, numérotation conservée et vérification des travaux avant réception.

- Espace structures spécialisées : Files limitées aux structures affectées ; choix de la structure représentée, une décision par structure, refus motivé et attente de tous les accords. Affectations explicites dans la gestion utilisateurs ; migration locale appliquée avec reprise de 11 correspondances exactes, sans modification des statuts métier.

- Espace DTS : Dashboard et file DTS, consultation et avis dans la fiche, refus motivé ; une seule décision enregistrée, aucune fausse validation de structure, passage direct au contrôle avancé sans structure sélectionnée.

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
