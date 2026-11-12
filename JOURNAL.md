# Journal de travail — Gestion des DMA

Ce fichier conserve les séances dans l’ordre chronologique. Ajouter une entrée à chaque séance, même si elle se termine par un blocage. Ne pas réécrire les anciennes décisions ; expliquer leurs évolutions dans une nouvelle entrée.

## 2026-11-10 — Initialisation du suivi

- **Objectif :** préparer le suivi avant de développer les parties manquantes.
- **Tâche :** DMA-009.
- **Version de travail :** `0.1.0-dev`.
- **Base Git :** `b3971f5`, branche `main` au début de la reprise.
- **Travail réalisé :** récupération du dépôt, ouverture dans VS Code, première lecture partielle du code ; création de `VERSION`, `CHANGELOG.md`, `SUIVI_PROJET.md`, `JOURNAL.md` et `PROGRESSION.md` ; ajout des liens au README.
- **Décisions :** suivre les séances dans ce journal, l’état courant dans `PROGRESSION.md`, les tâches dans `SUIVI_PROJET.md` et les changements dans `CHANGELOG.md`. Une fonctionnalité présente dans le code reste à vérifier avant d’être déclarée terminée.
- **Vérifications :** cohérence de la version, liens locaux et espaces contrôlés. Aucun démarrage ni test de l’application effectué.
- **Résultat :** suivi documentaire initialisé. À ce stade, les fichiers sont locaux et non publiés sur GitHub.
- **Points ouverts :** liste des parties manquantes et règles du circuit métier à préciser.
- **Prochaine action :** préciser DMA-001 avec le porteur du projet, puis préparer DMA-002.

## 2026-11-10 — Démarrage local

- **Tâche :** DMA-002.
- **Travail réalisé :** installation des dépendances Composer et npm, compilation Vite, création du fichier local `.env` ignoré par Git et génération de la clé d’application. Lancement de Laravel sur `http://127.0.0.1:8000` et ouverture de MySQL Workbench.
- **Décision :** utiliser PHP et le MySQL déjà installé sur le Mac, sans Docker, conformément à la préférence du porteur du projet. La tentative Docker a été interrompue pendant le téléchargement de l’image ; aucun conteneur du projet n’a été démarré par cette tentative.
- **Base :** connexion à la base existante `bd_demande1` sur le port 3306. Aucune migration ni aucun seeder exécuté. Aucune réinitialisation des données.
- **Vérifications :** toutes les migrations du dépôt déjà appliquées ; 28 utilisateurs et 34 demandes au contrôle initial ; formulaire de connexion et fichiers CSS/JS accessibles en HTTP 200. Compilation Vite réussie.
- **Limites :** parcours authentifiés et fonctionnalités métier non vérifiés. Vite signale une référence d’image non résolue à la compilation dans les ressources existantes.
- **Suite :** vérification manuelle de l’application par le porteur du projet et identification des parties manquantes.

## 2026-11-10 — Exécution des migrations et seeders

- **Demande :** exécuter les migrations et les seeders sur le MySQL local.
- **Commande :** `php artisan migrate --seed --no-interaction --no-ansi`.
- **Résultat :** aucune migration en attente ; `DatabaseSeeder` a exécuté `RoleSeeder` et `UserSeeder` avec succès.
- **Vérification :** toutes les migrations marquées « Ran » ; 28 utilisateurs, 34 demandes et 9 rôles après exécution. Les comptes existants sont conservés par `firstOrCreate` ; leurs mots de passe ne sont pas réinitialisés.
- **Périmètre :** seeders configurés dans `DatabaseSeeder` uniquement. `DemandeTestSeeder` et `StructuresSpecialiseesSeeder` ne sont pas appelés par cette commande.
- **Suite :** vérifier l’application avec les comptes existants et préciser les parties manquantes.

## 2026-11-10 — Refonte du dashboard administrateur

- **Tâche :** DMA-010.
- **Objectif :** rendre les demandes consultables rapidement et clarifier les indicateurs de suivi.
- **Travail réalisé :** nouvelle présentation aux couleurs SIR ; navigation dédiée à l’administration ; demandes placées avant les graphiques ; recherche par numéro, objet ou demandeur ; filtres par étape ; pagination en français ; liens de consultation ; cartes de demandes sur mobile ; répartition exhaustive et graphique mensuel sans dépendance Chart.js externe.
- **Correction :** `terminee_agent` n’est plus compté comme refus. Les statuts inconnus sont isolés dans « Statut à vérifier ». Les points d’entrée admin de `DashboardController` utilisent désormais `AdminController`, qui vérifie le rôle admin.
- **Répartition observée :** 34 demandes = 13 brouillons + 11 en validation + 2 à traiter/en travaux + 2 à clôturer/réceptionner + 1 clôturée/réceptionnée + 5 refusées.
- **Vérifications :** 4 tests unitaires réussis (9 assertions), syntaxe PHP valide, compilation Vite et compilation Blade réussies ; recherche sans résultat, filtre brouillons, seconde page filtrée et ouverture du détail d’un dossier vérifiés dans le navigateur. Rendu contrôlé à 1000 px et 390 px ; menu mobile vérifié.
- **Limites :** les libellés décrivent les statuts existants ; les règles métier de clôture restent à confirmer dans DMA-001/DMA-006. Le graphique de 2026 est vide car aucune demande existante n’a été créée en 2026. Les autres dashboards et le détail d’une DMA ne sont pas refondus dans ce lot.
- **Données :** aucune modification des demandes, des comptes ou du schéma pour cette refonte.
- **Suite :** revue visuelle par le porteur du projet ; changements locaux, non poussés à ce stade.

## 2026-11-10 — Orange SIR et simplification du dashboard admin

- **Tâche :** DMA-010, ajustement après revue du porteur du projet.
- **Demande :** intégrer un peu d’orange à la charte du dashboard et enlever les petits commentaires visibles.
- **Travail réalisé :** accents orange dans l’onglet actif, le bouton de gestion des utilisateurs, une bordure de carte et les graphiques. Retrait du message de bienvenue, des sous-textes des indicateurs, des descriptions de sections et des notes de bas de page. Titre simplifié en « Tableau de bord administrateur ».
- **Vérifications :** compilation Vite et Blade ; revue visuelle du dashboard.
- **Données :** aucun changement des calculs ou des données.
- **Suite :** validation visuelle par le porteur du projet ; modifications locales.

## 2026-11-10 — Navbar SIR et modes jour/nuit

- **Tâche :** DMA-011.
- **Travail réalisé :** navbar avec fond clair chaud et liseré vert/orange ; bouton jour/nuit avec icônes et libellé accessible ; préférence conservée dans le navigateur. Sans préférence enregistrée, le thème suit le réglage du système au chargement.
- **Périmètre :** navigation de l’administration et thème du dashboard admin ; adaptation des fonds et champs des pages existantes ouvertes depuis cet espace.
- **Fichiers :** `resources/views/layouts/app.blade.php`, `resources/views/layouts/admin-navigation.blade.php`, `resources/js/app.js`, `resources/css/admin.css`.
- **Vérifications :** compilation Vite et Blade réussies ; bascule jour vers nuit, maintien du thème après actualisation, rendu mobile à 390 px et retour au jour vérifiés dans le navigateur.
- **Résultat :** thème jour laissé actif ; bouton « Nuit » disponible dans la navbar sur ordinateur et mobile. Aucun changement des données.
- **Suite :** revue visuelle par le porteur du projet ; modifications locales non poussées.

## 2026-11-10 — Autres pages de l’administration

- **Tâche :** DMA-012 ; correction partielle de DMA-004.
- **Pages :** liste utilisateurs avec recherche/rôle/pagination ; création et modification d’un compte ; liste des demandes avec recherche/statut/pagination ; création et modification des demandes autorisées ; fiche DMA avec informations, travaux, pièces jointes et décisions enregistrées dans `demandes_validations` ; notifications et profil.
- **UI :** charte SIR vert/orange, modes jour/nuit, formulaires étiquetés, erreurs de validation, tables adaptées au mobile, retours et annulations explicites.
- **Corrections utilisateurs :** création avec les rôles web réellement disponibles ; validation du rôle à la modification ; redirection vers la liste utilisateurs ; blocage de l’auto-suppression depuis cette liste et de la perte de son propre rôle admin.
- **Corrections demandes :** la réponse de création est maintenant renvoyée après l’ensemble des fichiers, y compris sans pièce jointe. Les sélections de structures du formulaire admin sont validées avec les identifiants de la table existante.
- **Notifications :** affichage du message ou de l’action/objet existants ; marquage comme lue limité aux notifications du compte connecté ; routes protégées par authentification.
- **Autorisations :** les règles existantes de modification et suppression des DMA sont conservées ; les boutons sont affichés selon ces règles. Un admin ne peut donc pas modifier une demande d’un autre auteur avec la politique actuelle. Ce point métier reste à confirmer.
- **Vérifications :** 12 tests réussis, 62 assertions, avec SQLite en mémoire (sans connexion à la base MySQL réelle). Scénarios couverts : rôles, création et mise à jour des comptes, accès admin, protection du compte courant, appartenance des notifications, création sans fichier et avec deux fichiers, rendu des pages et indicateurs. Vite et Blade compilés ; revue navigateur des utilisateurs, formulaires, demandes, fiche DMA, profil et notifications ; formulaire mobile en mode nuit contrôlé.
- **Environnement :** lien local `public/storage` créé pour servir les pièces jointes. Les fichiers historiques absents du checkout doivent être restaurés séparément si nécessaire.
- **Données :** aucun compte ni demande réels créés, modifiés ou supprimés lors de cette refonte. Aucun changement de schéma ni migration supplémentaire.
- **Limites :** `structure_specialisees` ne fournit actuellement aucune option au formulaire admin sur la base locale. La cohérence entre structures et comptes spécialisés reste à traiter dans DMA-005 ; aucune structure n’a été inventée ou ajoutée. Tests de bout en bout sur le circuit métier complet toujours à réaliser.
- **Suite :** revue manuelle de l’espace admin et confirmation des droits métier ; changements locaux non poussés.

## 2026-11-10 — Couleurs de référence de la charte SIR

- **Demande :** revoir la charte avec le vert `#2E8B57` et l’orange `#FF8C00`.
- **Travail réalisé :** variables de marque centralisées dans `resources/css/admin.css`, accents et composants admin alignés sur les valeurs exactes ; références Tailwind harmonisées ; création de `CHARTE_GRAPHIQUE.md` et lien dans le README.
- **Lisibilité :** textes foncés sur les fonds de marque et variante verte foncée pour les petits liens sur fond clair. Contrastes calculés : 4,59:1 sur le vert, 5,34:1 sur l’orange et 6,45:1 pour le lien vert foncé sur blanc.
- **Vérifications :** Vite et Blade compilés ; couleurs calculées dans le navigateur confirmées en jour et en nuit : `rgb(46, 139, 87)` et `rgb(255, 140, 0)`.
- **Suite :** utiliser ces références pour les prochaines refontes. Mode jour laissé actif ; changements locaux non poussés.

## 2026-11-10 — Statuts vifs et mode nuit noir brillant

- **Demande :** remplacer les badges trop fades et rendre le mode nuit noir ou noir brillant.
- **Travail réalisé :** fond noir `#050505`, navbar noire, panneaux noirs à léger dégradé et reflet statique ; retrait des fonds teintés de vert. La carte « Dans le circuit » reste noire en mode nuit, avec une bordure verte SIR.
- **Statuts :** ardoise pour brouillons, orange SIR pour validation, bleu pour travaux, violet pour clôture/réception attendue, vert SIR pour clôture reçue, rouge pour refus et rose pour statut à vérifier. Palette commune en jour et nuit ; reprise sur les badges des autres rôles via `Demande::getStatutBadgeClass()`.
- **Vérifications :** syntaxe PHP, compilation Vite et Blade, contrôle navigateur du fond noir, du dégradé et des badges. Contrastes des sept badges calculés entre 4,59:1 et 7,58:1.
- **Résultat :** mode nuit laissé actif pour la revue. Charte et progression actualisées ; données inchangées ; modifications locales non poussées.

## 2026-11-10 — Cartes et délimitation des sections

- **Demande :** améliorer les cartes et rendre les limites entre sections plus visibles.
- **Travail réalisé :** cartes de statistiques uniformes avec icônes, valeurs alignées et repères colorés ; espacement renforcé ; contours plus visibles ; bandeaux de titre séparés des contenus ; filtres, en-tête et lignes de tableau mieux délimités. Adaptation jour/nuit et mobile.
- **Vérifications :** compilation Vite et Blade, contrôle visuel sur ordinateur et mobile ; aucun changement des calculs ou des données.
- **Suite :** revue visuelle par le porteur du projet ; changements locaux non poussés.

## 2026-11-10 — Publication du point de contrôle admin

- **Demande :** pousser le projet avant de commencer les pages demandeur.
- **Résultat :** commit `afd10d3` (« refondre l’espace administrateur et la charte SIR ») poussé sur `origin/main` avec succès avant le début des modifications demandeur.
- **Périmètre :** refonte admin, charte, modes jour/nuit, statuts, cartes, tests et documentation présents à ce point de contrôle.

## 2026-11-10 — Espace demandeur

- **Tâche :** DMA-013 ; finalisation des cas de création DMA-004.
- **Pages :** dashboard limité aux dossiers du compte connecté, liste avec recherche et filtre, création et reprise de brouillons, détail et historique des décisions, réceptions, notifications et profil. Navbar adaptée au rôle, sans liens de gestion admin.
- **UI :** reprise de la charte SIR, cartes délimitées, badges vifs, mode jour/nuit mémorisé et menu mobile. Formulaires et fiche DMA partagés avec l’admin pour maintenir une présentation cohérente.
- **Actions :** envoi d’un brouillon complet uniquement par son auteur, puis verrouillage de la modification et de la suppression ; refus d’une nouvelle soumission ; suppression de pièce jointe également interdite après soumission. Réception uniquement par l’auteur d’une demande `cloturee`, avec visa et enregistrement d’une décision `reception_demandeur`, puis statut `cloturee_receptionnee`. Transitions d’envoi et de réception protégées par verrouillage transactionnel.
- **Structures :** incohérence découverte par les tests : modèle et validations pointaient sur `structure_specialisees` (vide), tandis que les associations pointaient sur `structures_specialisees` (11 lignes dans la base locale). Modèle et validations alignés sur la table réellement référencée ; les 11 structures existantes sont désormais proposées. Aucune donnée copiée, créée ou supprimée pour cette correction.
- **Migrations source :** création des structures alignée sur le nom canonique pour les nouvelles installations ; migration d’ajout du statut `cloturee` exprimée avec le Schema Builder pour pouvoir tester le schéma final sous SQLite. Ces migrations étaient déjà appliquées localement ; aucune migration ni modification de schéma exécutée sur le MySQL réel pendant cette séance. Installation complète neuve sous MySQL non retestée.
- **Vérifications :** 20 tests réussis, 126 assertions, sur SQLite en mémoire : isolation des dossiers/statistiques par propriétaire, accès interdits, formulaires, création avec pièces et structures, soumission unique et complète, verrouillage après envoi, réception par propriétaire et sans doublon, plus régressions admin. Compilation Vite et Blade réussie. Revue navigateur avec le compte demandeur existant : dashboard (20 dossiers du compte), liste, création avec 11 structures, fiche et reprise d’un brouillon, réceptions vides, profil, notifications, thèmes et menu mobile.
- **Données :** aucun dossier réel envoyé, réceptionné, créé, modifié ou supprimé lors du contrôle visuel. Actions modifiant les données vérifiées uniquement dans la base de test isolée.
- **Limites :** les étapes précédant la réception restent à fiabiliser dans DMA-006 ; le contrôle avancé doit produire le statut `cloturee` pour qu’une réception apparaisse. Les anciennes routes dupliquées restent à traiter dans DMA-003.
- **Suite :** revue manuelle de l’espace demandeur par le porteur du projet. Le point de contrôle admin est publié ; les modifications demandeur restent locales, non poussées.

## 2026-11-10 — Point de contrôle demandeur

- **Demande :** publier la refonte demandeur sur GitHub.
- **Contenu du commit :** pages demandeur, composants partagés, droits et transitions, cohérence des structures, tests et documentation.
- **Vérifications avant publication :** 20 tests et 126 assertions déjà réussis lors de la refonte ; compilation Vite et Blade réussie ; absence d’erreurs d’espacement vérifiée avant commit.
- **Branche cible :** `main`, dépôt `esli-ariel/demande_sir`.

## 2026-11-10 — Rôle exploitation

- **Tâche :** DMA-014.
- **Travail réalisé :** Dashboard, file filtrée, décisions personnelles, fiche avec accord/refus, visa et motif de refus ; décision transactionnelle sans doublon, brouillons non exposés. Socle visuel commun aux prochains rôles. Correction du rendu conditionnel de la fiche partagée et du typage nullable PHP.
- **Vérifications :** 24 tests réussis, 159 assertions sur base isolée ; compilation et revue navigateur du rôle. Aucune décision réelle enregistrée pendant les contrôles.
- **Publication :** point de contrôle dédié au rôle sur `main`, conformément à la demande de faire un push après chaque rôle.

## 2026-11-10 — Rôle DTS

- **Tâche :** DMA-015.
- **Travail réalisé :** Dashboard et file DTS, consultation et avis dans la fiche, refus motivé ; une seule décision enregistrée, aucune fausse validation de structure, passage direct au contrôle avancé sans structure sélectionnée.
- **Vérifications :** 27 tests réussis, 178 assertions sur base isolée ; compilation et revue navigateur du rôle. Aucune décision réelle enregistrée pendant les contrôles.
- **Publication :** point de contrôle dédié au rôle sur `main`, conformément à la demande de faire un push après chaque rôle.

## 2026-11-10 — Rôle structures spécialisées

- **Tâche :** DMA-016.
- **Travail réalisé :** Files limitées aux structures affectées ; choix de la structure représentée, une décision par structure, refus motivé et attente de tous les accords. Affectations explicites dans la gestion utilisateurs ; migration locale appliquée avec reprise de 11 correspondances exactes, sans modification des statuts métier.
- **Vérifications :** 30 tests réussis, 206 assertions sur base isolée ; compilation et revue navigateur du rôle. Aucune décision réelle enregistrée pendant les contrôles.
- **Publication :** point de contrôle dédié au rôle sur `main`, conformément à la demande de faire un push après chaque rôle.

## 2026-11-10 — Rôle contrôle avancé

- **Tâche :** DMA-017.
- **Travail réalisé :** File de contrôle, avis sécurisés, numérotation conservée et vérification des travaux avant réception.
- **Vérifications :** 33 tests réussis, 229 assertions sur base isolée ; rendu des vues vérifié par les tests. Aucune décision réelle enregistrée pendant les contrôles.
- **Publication :** point de contrôle dédié au rôle sur `main`, conformément à la demande de faire un push après chaque rôle.

## 2026-11-10 — Rôle service technique

- **Tâche :** DMA-018.
- **Travail réalisé :** Démarrage et fin des travaux distincts, dates cohérentes, compte rendu et historique des interventions.
- **Vérifications :** 34 tests réussis, 253 assertions sur base isolée ; compilation et revue navigateur du rôle. Aucune décision réelle enregistrée pendant les contrôles.
- **Publication :** point de contrôle dédié au rôle sur `main`, conformément à la demande de faire un push après chaque rôle.

## 2026-11-10 — Rôle chef de structure

- **Tâche :** DMA-019.
- **Travail réalisé :** Consultation après validation exploitation, suivi des dossiers en cours, clôturés et refusés, sans nouveau pouvoir de décision.
- **Vérifications :** 35 tests réussis, 274 assertions sur base isolée ; compilation et revue navigateur du rôle. Aucune décision réelle enregistrée pendant les contrôles.
- **Publication :** point de contrôle dédié au rôle sur `main`, conformément à la demande de faire un push après chaque rôle.

## 2026-11-10 — Rôle réception

- **Tâche :** DMA-020.
- **Travail réalisé :** Clôture définitive après travaux, réservée à la réception ; visa, historique et notification ; demandeur en consultation. Circuit complet testé.
- **Vérifications :** 37 tests réussis, 320 assertions sur base isolée ; compilation et revue navigateur du rôle. Aucune décision réelle enregistrée pendant les contrôles.
- **Publication :** point de contrôle dédié au rôle sur `main`, conformément à la demande de faire un push après chaque rôle.

## 2026-11-10 — Lisibilité de la fiche de consultation

- **Demande :** distinguer les noms des champs des données affichées.
- **Modification :** libellés en orange contrasté et capitales, valeurs plus grandes avec couleur explicite, séparations entre les informations ; fiche partagée par tous les rôles.
- **Vérifications :** compilation Vite réussie et contrôle visuel dans les modes jour et nuit ; aucune donnée métier modifiée.
- **État :** correction disponible en local.

## 2026-11-10 — Diagramme d’avancement des DMA

- **Tâche :** DMA-021.
- **Demande :** visualiser l’avancement de chaque demande dans un diagramme workflow.
- **Modification :** parcours à sept étapes sur la fiche partagée : soumission, exploitation, DTS, structures spécialisées, contrôle avancé, travaux et réception finale. État calculé à partir du statut ; étapes franchies, actuelle, à venir, non requises et arrêt sur refus. Aucun pourcentage ni date supposée.
- **Vérifications :** 53 tests réussis, 432 assertions sur base isolée ; compilation Vite réussie ; contrôle visuel jour/nuit et ordinateur/mobile. Aucune demande existante modifiée.
- **État :** disponible en local ; inclut les ajustements précédents de distinction entre libellés et valeurs.

## 2026-11-11 — Notifications du circuit DMA

- **Tâche :** DMA-022.
- **Modification :** service central de notifications dans l’application ; alerte aux prochains intervenants et suivi du demandeur, chef prévenu après exploitation, structures ciblées selon leurs affectations. Avis partiels notifiés au demandeur, transmission au contrôle après tous les accords, réception et contrôle prévenus à la fin des travaux.
- **Centre :** filtres toutes/non lues, lecture individuelle ou globale, consultation d’une DMA qui marque la notification comme lue après vérification des droits ; compteur de navbar actualisé toutes les 30 secondes lorsque l’onglet est visible.
- **Fiabilité :** destinataires dédupliqués, auteur de l’action exclu, enregistrement atomique avec la transition et notifications persistantes en base. Les anciens messages restent lisibles ; aucun envoi rétroactif.
- **Vérifications :** 60 tests réussis, 498 assertions sur base isolée ; compilation Vite et centre vide/non lu vérifiés dans le navigateur, sans notification artificielle ni transition sur les demandes existantes.
- **État :** disponible en local. Canal dans l’application uniquement ; aucun serveur mail ni processus de file d’attente nécessaire.

## 2026-11-11 — Publication puis nettoyage des routes et des accès

- **Publication demandée :** `fe657f3` poussé sur `main` : distinction libellés/valeurs, diagramme d’avancement et notifications du circuit.
- **Tâche suivante :** DMA-003.
- **Modification :** routes uniques regroupées sous authentification et par rôle, ressources déclarées une seule fois ; alias administrateur protégés, actions inexistantes corrigées, anciens traitements sans contrôle retirés. Les anciennes actions `responsable_S` répondent désormais HTTP 410 sans modifier de demande. Les anciennes URL de traitement technique et contrôle utilisent les mêmes méthodes protégées que le circuit actuel.
- **Accès :** listes de clôture sans collision avec `/demandes/{demande}`, réception distincte du demandeur, refus d’accès aux comptes sans rôle reconnu ; modification et suppression réservées aux propriétaires admin/demandeur de leurs brouillons. Champs de statut, propriétaire et rôle injectés dans les formulaires ordinaires sans effet.
- **Nettoyage :** suppression des contrôleurs et branches historiques devenus inaccessibles ; correction du calcul mensuel admin pour permettre sa vérification sur SQLite sans changer le calcul MySQL.
- **Vérifications :** 69 tests réussis, 1 013 assertions sur base isolée ; 84 routes inventoriées, noms uniques et méthodes existantes ; cache des routes créé puis retiré avec succès. En local, `/dashboard` et `/admin` redirigent un visiteur anonyme vers `/login` (HTTP 302). Aucune donnée métier existante modifiée.
- **Suite :** préciser les périmètres par structure/unité et les règles après refus ; protéger les fichiers joints actuellement servis depuis le stockage public avant mise en service.
- **Publication :** point de contrôle dédié sur `main` après les vérifications.

## 2026-11-11 — Préparation sécurité : debug et CORS

- **Tâche :** DMA-024.
- **Signalement :** recommandation de désactiver le debug en production et de limiter CORS aux domaines de confiance. Le porteur du projet confirme qu’aucun déploiement de production n’existe encore.
- **Constats :** configuration Render historique en mode local/debug et absence de configuration CORS propre au projet ; le framework utilisait ses origines permissives par défaut sur les chemins API.
- **Correction :** debug possible uniquement en local/testing, désactivé en production même si APP_DEBUG est mal renseigné ; valeurs Render/Docker alignées. CORS désormais explicite sur tous les chemins, sans origine externe par défaut, méthodes/en-têtes limités et cookies cross-origin non activés. Les origines éventuellement ajoutées doivent être exactes ; jokers, chemins et identifiants rejetés. Mot de passe codé en dur retiré du manifeste Render.
- **Vérifications :** suite de 86 tests réussis (1 061 assertions), puis 15 tests de configuration réussis (16 assertions), dont deux cas complémentaires pour préserver le debug local/testing. Réponses JSON de production sans détail interne, domaines tiers/sous-domaines/ports non autorisés et prévols CORS couverts. Cache de configuration créé puis retiré ; environnement local et debug local conservés, liste CORS vide.
- **Documentation :** SECURITE_DEPLOIEMENT.md précise les réglages à fournir lors du futur déploiement. Aucun serveur de production ni conteneur modifié ou lancé.
- **État :** changements disponibles en local ; vérification sur le futur domaine HTTPS à effectuer lors de la mise en production.

## 2026-11-11 — Mise à jour de sécurité Axios

- **Tâche :** DMA-025.
- **Signalement :** GHSA-35jp-ww65-95wh, GHSA-3g43-6gmg-66jw, GHSA-3p68-rc4w-qgx5, GHSA-3w6x-2g7m-8v23 et GHSA-42h9-826w-cgv3.
- **Constat :** Axios 1.12.2 installé, plage déclarée ^1.11.0. Les avis des mainteneurs indiquent des correctifs allant de 1.15.0 à 1.18.0.
- **Correction :** version stable 1.21.1 vérifiée sur npm, verrouillée exactement dans package.json et package-lock.json ; dépendances transitives nécessaires actualisées par npm, aucune mise à niveau globale forcée.
- **Vérifications :** npm ls confirme une seule version 1.21.1 ; requête OSV pour cette version sans avis ; npm audit sans avis Axios ; compilation Vite réussie. Initialisation du client Laravel, en-tête X-Requested-With et décodage JSON vérifiés avec un adaptateur de test sans réseau.
- **Limite constatée :** l’audit npm complet reste non nul : 16 alertes sur d’autres dépendances (3 modérées, 10 élevées, 3 critiques), à traiter dans DMA-026. Les fichiers de préparation debug/CORS restent également en local.
- **État :** correction Axios disponible en local, sans push demandé pour cette séance.

## 2026-11-11 — Correction des dépendances npm et Laravel

- **Tâches :** DMA-026 et DMA-027.
- **Demande :** corriger les 16 alertes npm restantes et les quatre avis Laravel GHSA-5vg9-5847-vvmq, GHSA-crmm-hgp2-wgrp, GHSA-f9x3-7vq9-cph6, GHSA-jh5r-qr3c-85q8.
- **Correction :** Laravel 12.72.0 verrouillé exactement, sans passage à Laravel 13. Dépendances npm actualisées dans leurs branches existantes, versions minimales relevées et Axios 1.21.1 conservé. Avis Composer résiduels corrigés dans les outils de développement, dont Pest 4.7.8, PHPUnit 12.5.33, PsySH 0.12.24 et Symfony YAML 7.4.23.
- **Audits :** zéro alerte npm, zéro avis Composer et aucun paquet abandonné ; OSV ne retourne aucun avis pour Laravel 12.72.0. Une seule copie du framework installée.
- **Vérifications :** installation npm propre et compilation réussies ; exigences PHP satisfaites et verrouillage Composer vérifié. 92 tests réussis, 1 086 assertions sur base isolée, comprenant le circuit DMA et des cas de connexion/déconnexion, identifiants invalides et injection CR/LF dans les emails. Cache des routes et vues compilés, puis cache des routes retiré.
- **Résultat :** corrections disponibles en local. Aucune migration, aucun seeder et aucune modification des données métier. Détails et sources dans SECURITE_DEPENDANCES.md.

## 2026-11-11 — Requêtes paramétrées et validation des entrées

- **Tâche :** DMA-028.
- **Demande :** utiliser des requêtes paramétrées partout et valider les entrées à la frontière HTTP/API.
- **Audit SQL :** aucune donnée HTTP concaténée dans du SQL brut détectée dans les routes actives ; Eloquent/Query Builder conservent leurs bindings PDO. Agrégats SQL explicitement constants, migrations de trigger statiques. Aucun identifiant de table/colonne/tri choisi par le client.
- **Correction :** schéma FormRequest partagé pour créer/modifier une DMA, données validées seules persistées ; listes, fichiers et longueurs bornés. Revalidation de l’autorisation sous verrou à la modification. Filtres, pagination, types des rôles et identifiants de route renforcés. Dates ISO validées avant les opérations techniques et contrôlées à nouveau par rapport au début en base.
- **Authentification :** mots de passe, emails, jetons et option remember contrôlés ; confirmation/suppression de compte rejettent les types invalides, octets nuls refusés avant hachage. Inscription publique limitée au rôle demandeur, données validées consommées par les contrôleurs.
- **Vérifications :** 110 tests réussis, 1 241 assertions ; compilation Vite et syntaxe PHP réussies. Tests JSON 422, valeurs imbriquées, chemins de fichiers factices, quotas de fichiers, rôles, dates et identifiants. Fragments SQL vérifiés comme bindings sur les quatre espaces de recherche, périmètre demandeur conservé et données textuelles SQL stockées sans exécution.
- **État :** disponible en local, sans migration ni modification de données métier existantes. Détails et limites documentés dans SECURITE_ENTREES.md.

## 2026-11-11 — Préparation du durcissement Docker

- **Tâche :** DMA-029.
- **Contexte :** le projet reste utilisé nativement, sans Docker ; demande de corriger l’exécution root, les images non épinglées et les secrets dans le build.
- **Constats :** ancien hook d’entrée absent, génération de clé pendant le build, verrouillage Composer exclu du contexte, images flottantes et identifiants codés en dur dans Compose.
- **Correction :** build multi-stage avec images officielles PHP/Node/Composer/MySQL épinglées aux digests du registre, application UID/GID 10001:10001 sur 8080 et MySQL sous son compte dédié. Capabilities retirées, no-new-privileges, application en lecture seule, volumes limités ; secrets montés à l’exécution et fichiers sensibles/cache/stockage exclus du build. Aucun bootstrap de clé, migration ou seeder automatique.
- **Vérifications :** digests et plateformes amd64/arm64 vérifiés via métadonnées du registre ; syntaxe Compose et shell valides. Tests du script avec commandes PHP simulées, sans secrets réels ; suite complète de 113 tests réussis, 1 274 assertions.
- **Limite explicite :** aucun conteneur lancé et aucune image construite ; le build, le démarrage réel et la procédure de migration sont à valider avant usage Docker. Le PHP/MySQL local n’est pas modifié. Voir docker/README.md.
- **État :** configuration corrigée et préparée en local, non déployée.

## 2026-11-11 — Audit des migrations et de la base MySQL

- **Tâche :** DMA-030 ; audit terminé, corrections DMA-031 à DMA-034 à faire, protection documentaire DMA-023 conservée.
- **Périmètre :** 14 migrations du dépôt et base locale MySQL 9.3.0 ; métadonnées, contraintes, index, trigger, privilèges et contrôles agrégés en transactions en lecture seule, rollback systématique.
- **Constats :** 23 tables InnoDB/utf8mb4, 34 demandes, 28 utilisateurs ; aucun doublon de numéro ni référence orpheline dans les relations métier contrôlées. Deux anciennes migrations enregistrées manquent au dépôt, règles de suppression différentes pour uploaded_by et statut nullable en base.
- **Anomalies :** trois dossiers terminés/réceptionnés sans dates ni compte rendu, trois dossiers historiques validés DTS sans structure à examiner pour leur progression (structures facultatives), neuf dossiers avancés sans historique moderne ; huit fichiers référencés absents du disque configuré. Origine démonstration/historique à qualifier avant reprise.
- **Risques :** cascades lors des suppressions de comptes, numérotation COUNT+1 sans unicité, connexion root, mots de passe de démonstration dans les seeders et sauvegarde/restauration non démontrée.
- **Livrable :** AUDIT_BASE_DONNEES.md, avec mesures, sources officielles et plan ordonné. Aucune migration, aucun seeder et aucune correction de donnée exécutés ; aucun secret ou contenu personnel dans le rapport.

## 2026-11-11 — Relance et précision sur les structures spécialisées

- **Demande :** relancer le projet ; le passage par une structure spécialisée est facultatif.
- **Vérification :** le contrôleur DTS prévoit déjà le passage direct au contrôle avancé sans structure sélectionnée. L’audit est corrigé : aucune obligation d’affectation ; seuls les éventuels blocages de dossiers historiques restent à examiner.
- **Relance :** serveur Laravel local sur 127.0.0.1:8000, connexion MySQL vérifiée par SELECT 1 ; aucun changement des données.

## 2026-11-11 — Conservation des dossiers et fiabilisation du schéma

- **Tâches :** DMA-031 et DMA-032 terminées en local ; DMA-023, DMA-033 et DMA-034 restent à faire.
- **Comptes :** désactivation/réactivation à la place de la suppression ; authentification, sessions et remember-me filtrés sur les comptes actifs, sessions en base et jetons révoqués. Profil demandeur désactivable avec mot de passe, auto-désactivation administrateur refusée ; relations métier conservées par quatre FK RESTRICT.
- **Accès SQL :** compte applicatif généré et limité aux lectures/écritures de la seule base DMA, DDL refusés lors du contrôle. Configuration administrative native privée hors dépôt ; commande dma:migrate-local ajoutée et vérifiée. Seeders de démonstration interdits hors local/testing, commande dma:create-admin confidentielle et modèle .env.example sans secrets.
- **Numérotation :** trigger COUNT+1 remplacé par compteur annuel atomique initialisé sur les références existantes, format annuel conservé, contrainte UNIQUE. Import explicite MS fait avancer le compteur ; pas de remise à zéro après suppression, pas de troncature à 10000. Sélections spécialisées uniques et statut NOT NULL ; aucune structure imposée.
- **Historique :** deux anciens noms de migrations expliqués par les renommages du commit 55fba09 ; aucune entrée historique supprimée et aucune ancienne migration recréée en doublon. Ancienne table vide conservée.
- **Incident de vérification :** la première copie de test réutilisait le nom de connexion mysql et a appliqué le DDL de conservation à la base active, sans modification des données. Connexion isolée corrigée et explicitement contrôlée ; migration rendue rejouable après interruption puis correctement enregistrée. Incident signalé au porteur du projet.
- **Vérifications MySQL :** sauvegardes privées et restauration testée ; schémas neuf/mis à niveau comparés (colonnes, index, FK) hors table historique vide. Empreintes de données comparées avant/après sur les copies, 32 créations concurrentes uniques, doublon refusé, passage à 10000 et non-réutilisation après suppression. Bases temporaires supprimées ; migrations appliquées en local.
- **État réel :** 28 utilisateurs, 34 demandes, 15 décisions modernes, 8 références de pièces jointes ; zéro compte désactivé pendant l’intervention. Numérotation avec le compte restreint vérifiée dans une transaction annulée. Page de connexion HTTP 200 et rechargement du serveur après changement de configuration.
- **Tests :** suite complète Integration/Unit : 122 tests réussis, 1 359 assertions ; compilation des vues et syntaxe PHP contrôlées, git diff --check sans erreur. Création/numérotation avec le compte SQL restreint vérifiée en transaction annulée. Procédures dans EXPLOITATION_BASE_DONNEES.md et DEMARRAGE_LOCAL.md.

## 2026-11-11 — Archives des demandes clôturées

- **Tâche :** DMA-035 ; DMA-036 créée comme prochaine étape demandée pour le pipeline de sauvegarde/restauration.
- **Fonctionnalité :** page /archives et lien de navigation commun, recherche sur numéro/objet/demandeur, filtre unité exacte et année/période de clôture, pagination stable avec filtres conservés jusqu’au retour depuis le dossier.
- **Périmètre :** seulement cloturee_receptionnee. Travaux achevés et contrôle final en attente de réception restent dans le suivi actif. Administrateur : dossiers accessibles ; demandeur : ses dossiers ; structures spécialisées : dossiers sélectionnés pour leurs structures ; autres rôles : périmètres existants inchangés. Pas d’élargissement des droits via les compteurs ou années disponibles.
- **Consultation :** fiche complète en lecture seule, décisions/travaux/pièces jointes existantes, liens de retour vers les archives ; aucun nouveau déplacement ou suppression de données/fichiers. La protection des fichiers publics reste DMA-023.
- **Dates :** closed_at enregistré sous verrou lors de la réception définitive, jamais modifié par une seconde réception refusée ; anciens dossiers laissés à date inconnue. Affichage des décisions DATE sans heure artificielle.
- **Base réelle :** sauvegarde privée avant migration, restauration et mise à niveau vérifiées sur copie MySQL, données initiales préservées ; migration 2026_11_11_000003 enregistrée en local, index (statut, closed_at, id). Aucun ancien statut modifié ni date inventée.
- **Tests :** neuf tests dédiés (112 assertions) ; suite complète 131 tests réussis, 1 481 assertions. Recherche, dates inclusives, période inversée, tableaux malformés, périmètres des neuf rôles, lecture seule, dates historiques inconnues et pagination vérifiés. Compilation Vite réussie ; rendu jour/nuit/mobile vérifié dans Chrome sur prévisualisation SQLite exclusivement synthétique. Menu mobile accessible, aucun débordement horizontal, filtre d’année et retour conservant les filtres vérifiés, aucune erreur JavaScript. Captures dans les outputs du workspace.
- **Nettoyage :** après expiration du premier contrôle automatique, arrêt explicitement autorisé par le porteur du projet et effectué : les cinq processus de prévisualisation sur le port 8001 sont terminés. Aucun processus ne reste en écoute sur 8001 ; serveur principal 8000 préservé.
- **Prochaine action :** définir le stockage de sauvegarde, la rétention et les objectifs de reprise, puis implémenter DMA-036 avec restauration isolée ; ne pas confondre la consultation des archives et une sauvegarde contre panne.

## 2026-11-11 — Clôture finale par le demandeur propriétaire

- **Tâche :** DMA-037.
- **Règle confirmée :** le demandeur doit clôturer le workflow depuis sa page Réceptions. Cette instruction remplace la règle antérieure donnant la clôture définitive au rôle réception ; aucun historique antérieur n’est réécrit.
- **Réalisation :** formulaire sur Mes réceptions et sur la fiche, visa, commentaire facultatif, confirmation explicite requise ; tri stable des dossiers en attente. Propriétaire/rôle/état contrôlés avant validation et à nouveau sous verrou. Éligibilité après fin des travaux ou ancien statut de contrôle final ; aucun nouveau passage obligatoire par une structure spécialisée.
- **Intégrité :** décision rôle demandeur avec son auteur et visa, date serveur et statut final dans la même transaction que les notifications ; seconde confirmation refusée, injection d’un autre propriétaire ou d’une date ignorée, rollback si l’enregistrement des notifications échoue.
- **Rôles et suivi :** réception devient une vue de suivi, anciennes actions de clôture réception refusées, demandeur invité après travaux puis équipes réception/service technique/contrôle informées de sa confirmation. Archives alimentées après confirmation, diagramme et libellés actualisés. Dossiers déjà clôturés et anciennes décisions conservés.
- **Vérifications :** 137 tests réussis, 1 570 assertions ; six tests dédiés de clôture/sécurité (94 assertions), dont refus des neuf profils étrangers, validation typée, reprise legacy, date immuable, rejet des doublons et rollback. Recette Chrome jour/nuit/mobile sur SQLite synthétique : dossier étranger masqué, visa/confirmation puis clôture depuis Mes réceptions, retrait des attentes et présence dans les archives, sans erreur JavaScript ni débordement horizontal. Serveur temporaire lancé et arrêté automatiquement dans le script de test. Compilation Vite, vues et syntaxe PHP vérifiées. Aucune migration, aucun changement de mot de passe et aucune clôture réelle effectuée pendant les tests.
- **Prochaine étape :** pipeline de sauvegarde/restauration DMA-036 après cette correction métier.

## 2026-11-12 — Connexion et inscription avec photo en filigrane

- **Tâche :** DMA-038 terminée ; DMA-039 créée pour le visa, prochaine étape demandée. Le pipeline de sauvegarde/restauration DMA-036 reste dans le suivi.
- **Préférence confirmée :** conserver la photo existante de raffinerie en filigrane. Asset Raffinerie-SIR.jpeg réutilisé avec sa casse réelle pour les serveurs Linux, photo non remplacée ni modifiée ; opacité adaptée au jour/nuit.
- **Interface :** composants partagés auth-shell/auth-field, charte #FF8C00/#2E8B57, thème persistant dma-theme commun aux espaces connectés, carte claire ou noire et présentation mobile sans débordement. Suppression des anciennes animations et scripts dupliqués des deux pages.
- **Formulaires :** libellés/autocomplete adaptés, visibilité indépendante des deux mots de passe, retour des valeurs textuelles sûres après erreur, erreurs liées aux champs via aria-describedby/aria-invalid et résumé focalisé. Mot de passe jamais réaffiché après validation ; bouton de soumission indique le traitement.
- **Règles métier :** inscription demandeur uniquement, sans sélecteur de rôles privilégiés ; validation serveur et authentification inchangées, y compris remember, sessions, comptes désactivés et récupération. Aucun visa modifié dans cette tâche.
- **Vérifications :** 141 tests Integration/Unit réussis, 1 614 assertions ; quatre nouveaux tests de pages/parcours (44 assertions), régression ciblée de 30 tests réussie. Compilation Vite, vues et diff vérifiés.
- **Recette Chrome :** connexion/inscription en jour/nuit et à 390 px, image réellement chargée avec opacité de filigrane, visibilité indépendante des mots de passe, focus des erreurs, conservation e-mail et effacement du mot de passe, thème conservé entre pages, inscription demandeur puis déconnexion et reconnexion avec remember. Aucune erreur JavaScript. Prévisualisation SQLite synthétique uniquement, serveur temporaire automatiquement arrêté.
- **État :** disponible en local, aucune migration ni modification des comptes/demandes réels. Captures de contrôle dans outputs/connexion-*-filigrane.png et outputs/inscription-*-filigrane.png du workspace.

## 2026-11-12 — Couleur d’origine sur la photo de fond

- **Tâche :** ajustement DMA-038 demandé après la refonte des pages d’accès.
- **Préférence :** conserver la coloration de l’image de fond utilisée auparavant, en plus de la photo existante.
- **Correction :** dégradé vert/orange d’origine rétabli au-dessus de la photo de raffinerie sur connexion et inscription, avec une version assombrie en nuit. Formulaires clairs/noirs conservés, textes de présentation et marque adaptés au fond coloré pour rester lisibles.
- **Vérifications :** compilation Vite réussie ; recette Chrome jour/nuit/mobile sur les deux pages, dégradé réellement rendu, photo chargée, aucun débordement horizontal ni erreur JavaScript. Captures *-teinte-originale.png dans outputs. Aucun changement des règles d’authentification ou des données ; aucun nouveau test fonctionnel nécessaire pour cet ajustement CSS. Serveur synthétique arrêté automatiquement.
- **Prochaine étape :** visa (DMA-039), conformément à la demande.

## 2026-11-12 — Relance et revue d’avancement

- **Tâche :** DMA-040 terminée ; consolidation Git suivie dans DMA-041.
- **Demande :** relancer le projet et distinguer ce qui est terminé du reste à faire.
- **Relance :** aucun serveur sur le port 8000 au contrôle initial ; Laravel relancé sur 127.0.0.1:8000. MySQL natif répond, compte applicatif distinct de root ; aucune migration en attente. Aucun seeder, migration ou changement de dossier exécuté.
- **Vérifications :** connexion/inscription HTTP 200, archives anonymes redirigées vers la connexion ; suite Integration/Unit isolée relancée, 141 tests réussis et 1 614 assertions. Diff sans erreur de whitespace.
- **Données :** 28 utilisateurs, 34 demandes, 15 décisions modernes, trois validations anciennes et huit pièces référencées toujours absentes du stockage configuré. Trois dossiers terminés incomplets, une ancienne clôture sans date et neuf dossiers avancés sans historique moderne restent à qualifier. Les trois attentes DTS sans sélection ne justifient aucune affectation forcée, l’étape spécialisée restant facultative.
- **Revue :** REVUE_PROJET.md décrit réalisations, limites, priorités et état Git. Dernières corrections encore non commitées ; aucun push ni déploiement effectué dans cette séance. Sauvegardes/restaurations manuelles antérieures distinguées du pipeline d’exploitation restant à créer.
- **Suivi :** résumé de progression actualisé et incohérences de dates/résumés anciens corrigées. Visa conservé comme prochaine étape demandée, puis fichiers privés, sauvegarde/restauration et recette entreprise.

## 2026-11-12 — Consolidation et publication Git

- **Tâche :** DMA-041 en cours, push explicitement demandé par le porteur du projet.
- **Périmètre :** correctifs sécurité et dépendances, conservation des comptes/dossiers, contraintes et numérotation, archives, clôture par le demandeur, refonte des accès, tests et documentation.
- **Avant commit :** fetch de main effectué, aucune divergence avec origin/main ; 105 fichiers modifiés/nouveaux contrôlés, aucun secret de configuration correspondant ni motif de clé privée/token détecté, aucun dump/SQLite/.env privé inclus. Sauvegardes et prévisualisations restent hors dépôt.
- **Validation disponible :** 141 tests réussis, 1 614 assertions lors de la revue du jour ; dernière compilation Vite réussie et diff sans erreur de whitespace. Aucun code métier modifié depuis ces contrôles.
- **Publication :** commit de consolidation et vérification du push en cours ; résultat à inscrire après confirmation distante.

## Modèle pour les prochaines séances

Copier ce bloc en ajoutant une entrée datée avant cette section :

```markdown
## AAAA-MM-JJ — Sujet de la séance

- **Objectif :**
- **Tâche(s) :** DMA-...
- **Version de travail :**
- **Travail réalisé :**
- **Fichiers concernés :**
- **Décisions :**
- **Vérifications et résultats :**
- **Blocages ou points ouverts :**
- **Prochaine action :**
- **Commit(s), si créés :**
```

Ne pas enregistrer de secrets ni de données personnelles réelles.
