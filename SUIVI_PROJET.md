# Suivi du projet — Gestion des DMA

Dernière mise à jour : **2026-11-12**

## Documents de référence

- [PROGRESSION.md](PROGRESSION.md) : état actuel, étapes et prochaine action.
- [JOURNAL.md](JOURNAL.md) : historique des séances, décisions et vérifications.
- [CHANGELOG.md](CHANGELOG.md) : changements apportés au projet.
- [VERSION](VERSION) : numéro de version de travail.
- [REVUE_PROJET.md](REVUE_PROJET.md) : revue vérifiée des réalisations et du reste à faire.

Ce fichier conserve la liste de référence des tâches et leurs critères de fin.

## Travail à réaliser

Statuts : **À préciser**, **À faire**, **En cours**, **Bloqué**, **À vérifier**, **Terminé**. Une tâche n’est terminée qu’après vérification de ses critères de fin.

| ID | Priorité | Tâche | Statut | Critère de fin |
| --- | --- | --- | --- | --- |
| DMA-001 | Haute | Définir les parties manquantes et le circuit métier | À préciser | Fonctionnalités et étapes confirmées avec le porteur du projet |
| DMA-002 | Haute | Préparer et démarrer l’environnement local | Terminé | Application accessible et démarrage documenté sans secrets |
| DMA-003 | Haute | Nettoyer les routes et contrôler les accès | Terminé | Noms uniques, méthodes existantes et cache vérifié ; accès croisés des neuf rôles testés ; 69 tests réussis, 1 013 assertions |
| DMA-004 | Haute | Corriger la création des demandes et les pièces jointes | Terminé | Création vérifiée sans fichier, avec un fichier et avec plusieurs fichiers |
| DMA-005 | Haute | Fiabiliser les validations des structures spécialisées | Terminé | Affectations et décisions multiples conformes au circuit confirmé |
| DMA-006 | Haute | Compléter et raccorder clôture et réception | Terminé | Actions reliées à des méthodes existantes et parcours final vérifié |
| DMA-007 | Normale | Vérifier tableaux de bord et notifications | À vérifier | Statistiques, destinataires et liens cohérents avec les demandes et les rôles |
| DMA-008 | Haute | Vérifier le parcours complet avant livraison | À faire | Création, validation, refus, traitement et réception vérifiés ; anomalies bloquantes corrigées |
| DMA-011 | Normale | Ajouter les modes jour et nuit à l’administration | Terminé | Bascule accessible dans la navbar, préférence mémorisée, retour au jour et affichage mobile vérifiés |
| DMA-012 | Haute | Harmoniser les autres pages de l’administration | Terminé | Listes et formulaires utilisateurs/demandes, fiche DMA, profil et notifications harmonisés ; 12 tests réussis et revue visuelle effectuée |
| DMA-013 | Haute | Refaire les pages du demandeur | Terminé | Dashboard personnel, liste, formulaires, suivi, réception, notifications et profil ; droits et transitions couverts par tests isolés ; revue visuelle ordinateur/mobile |
| DMA-014 | Haute | Refaire le rôle exploitation | Terminé | Pages, droits, tests et revue visuelle vérifiés ; point de contrôle Git dédié |
| DMA-015 | Haute | Refaire le rôle DTS | Terminé | Pages, droits, tests et revue visuelle vérifiés ; point de contrôle Git dédié |
| DMA-016 | Haute | Refaire le rôle structures spécialisées | Terminé | Pages, droits, tests et revue visuelle vérifiés ; point de contrôle Git dédié |
| DMA-017 | Haute | Refaire le rôle contrôle avancé | Terminé | Pages rendues, droits et transitions couverts par tests isolés ; point de contrôle Git dédié |
| DMA-018 | Haute | Refaire le rôle service technique | Terminé | Pages, droits, tests et revue visuelle vérifiés ; point de contrôle Git dédié |
| DMA-019 | Haute | Refaire le rôle chef de structure | Terminé | Pages, droits, tests et revue visuelle vérifiés ; point de contrôle Git dédié |
| DMA-020 | Haute | Refaire le rôle réception | Terminé | Pages, droits, tests et revue visuelle vérifiés ; point de contrôle Git dédié |
| DMA-021 | Normale | Visualiser l’avancement de chaque DMA | Terminé | Diagramme partagé, refus et étapes non requises couverts ; 53 tests réussis et rendu jour/nuit ordinateur/mobile vérifié ; publié dans `fe657f3` |
| DMA-022 | Haute | Compléter les notifications du circuit DMA | Terminé | Destinataires, refus, clôture, filtres et lecture sécurisée vérifiés ; 60 tests réussis, 498 assertions ; publié dans `fe657f3` |
| DMA-023 | Haute | Protéger le téléchargement des pièces jointes | À faire | Stockage privé et téléchargement soumis aux droits de consultation de la DMA ; reprise des fichiers existants et tests de non-régression |
| DMA-024 | Haute | Préparer les règles debug et CORS de production | Terminé | Debug interdit hors local/testing, origines exactes sans joker, tests des réponses et prévols réussis ; configuration locale corrigée, validation sur serveur à faire lors du déploiement |
| DMA-025 | Haute | Corriger les cinq avis de sécurité Axios | Terminé | Version unique 1.21.1 verrouillée, contrôle OSV/npm sans avis Axios, compilation et client Laravel vérifiés ; disponible en local |
| DMA-026 | Haute | Corriger les autres dépendances signalées par npm | Terminé | Audit npm à zéro après mise à jour compatible et installation propre ; compilation réussie, Axios conservé en 1.21.1 |
| DMA-027 | Haute | Corriger les quatre avis de sécurité Laravel | Terminé | Framework 12.72.0 unique et verrouillé, audits OSV/Composer sans avis ; outils PHP corrigés, 92 tests réussis et 1 086 assertions |
| DMA-028 | Haute | Auditer les requêtes paramétrées et valider les entrées HTTP | Terminé | Données Eloquent liées, schéma DMA partagé, types/limites/auth contrôlés et tests JSON/injection ; 110 tests réussis, 1 241 assertions ; disponible en local |
| DMA-029 | Haute | Préparer le durcissement de la configuration Docker | Terminé | Non-root, images épinglées et secrets hors build ; contrôles Compose/shell et 113 tests réussis ; build et exécution réels à vérifier avant usage Docker |
| DMA-030 | Haute | Auditer les migrations et la base MySQL pour un usage en entreprise | Terminé | Métadonnées et contrôles agrégés vérifiés en lecture seule ; écarts et plan documentés dans AUDIT_BASE_DONNEES.md ; corrections non appliquées |
| DMA-031 | Haute | Préserver les dossiers et sécuriser les comptes techniques | Terminé | Désactivation/réactivation, sessions et jetons révoqués, quatre références RESTRICT ; compte SQL local restreint et migrations séparées, seeders protégés et création admin confidentielle ; vérifications réussies |
| DMA-032 | Haute | Réconcilier le schéma et fiabiliser la numérotation DMA | Terminé | Compteur annuel atomique, numéro et sélection uniques, statut non nullable ; schémas neuf/mis à niveau comparés, 32 créations concurrentes uniques et sauvegarde restaurée ; migrations appliquées en local |
| DMA-033 | Haute | Réconcilier les données et les historiques existants | À faire | Dossiers de démonstration identifiés, anomalies qualifiées et décisions préservées sans fabrication ; récupération documentaire coordonnée avec DMA-023 |
| DMA-034 | Haute | Préparer exploitation et reprise de la base entreprise | À faire | Cible MySQL maintenue, sauvegarde base/documents et restauration testées, objectifs RPO/RTO et contrôles de performance définis |
| DMA-035 | Haute | Consulter les archives des demandes clôturées | Terminé | Recherche/filtres/pagination et périmètres des neuf rôles vérifiés ; date de clôture et index appliqués après sauvegarde restaurée ; 131 tests réussis, 1 481 assertions ; rendu jour/nuit/mobile et navigation vérifiés sur données synthétiques |
| DMA-036 | Haute | Mettre en place le pipeline de sauvegarde et restauration | À faire | Étape suivante demandée : sauvegarde coordonnée base/fichiers, copie sécurisée hors serveur, contrôles et restauration isolée testée ; cible, rétention et objectifs de reprise définis |
| DMA-037 | Haute | Confier la réception et la clôture finale au demandeur propriétaire | Terminé | Visa/confirmation sur Mes réceptions et fiche, propriété/état sous verrou, réception en suivi ; historique/date/notifications/archives raccordés, 137 tests réussis (1 570 assertions) et parcours jour/nuit/mobile vérifié sur données synthétiques |
| DMA-038 | Normale | Harmoniser la connexion et l’inscription | Terminé | Photo de raffinerie conservée en filigrane, charte orange/verte et jour/nuit/mobile ; erreurs/champs accessibles et visibilité des mots de passe ; 141 tests réussis, 1 614 assertions et parcours Chrome inscription/connexion/déconnexion vérifiés sur données synthétiques |
| DMA-039 | Haute | Revoir la gestion du visa | À préciser | Prochaine étape demandée après les pages d’accès ; format du visa et exigences de traçabilité à définir |
| DMA-040 | Normale | Relancer le projet et établir la revue d’avancement | Terminé | Serveur/MySQL vérifiés, migrations sans attente, 141 tests réussis et revue datée des réalisations, limites et travaux restants dans REVUE_PROJET.md |
| DMA-041 | Haute | Consolider les modifications locales dans Git | Terminé | 105 fichiers contrôlés et regroupés dans 43ae8dd, poussé sur main le 12 novembre 2026 après fetch sans divergence ; secrets et sauvegardes exclus, 141 tests validés lors de la revue |
| DMA-010 | Haute | Refonte du tableau de bord administrateur | Terminé | Recherche, filtres, pagination et consultation vérifiés ; rendu ordinateur et mobile contrôlé ; tests des indicateurs réussis |
| DMA-009 | Normale | Mettre en place les fichiers de suivi | Terminé | Version, journal, suivi et liens README créés et relus |

## Constats de la première lecture

Constats initiaux et état après les corrections des rôles.

- `routes/web.php` : doublons et méthodes absentes corrigés dans DMA-003 ; toutes les routes métier exigent une authentification, actions regroupées par rôle. Téléchargement des pièces jointes à traiter séparément dans DMA-023.
- `DemandeController::store` : problème de redirection et d’arrêt après le premier fichier corrigé dans DMA-012/DMA-013 ; cas sans fichier et avec plusieurs fichiers couverts par tests isolés.
- DTS et structures : décision DTS unique et affectation explicite des comptes aux structures corrigées ; décisions multiples et refus couverts par tests isolés.
- Clôture et réception : parcours raccordé ; le demandeur propriétaire confirme la réception et clôture définitivement après travaux (DMA-037). Le rôle réception conserve un suivi en lecture seule. Le contrôle avancé peut vérifier les travaux avant la confirmation du demandeur.

## Journal de travail

Consulter [JOURNAL.md](JOURNAL.md) pour le détail chronologique des séances, décisions et vérifications.

## Convention de version

- `VERSION` est la source du numéro de version du projet, indépendamment des versions des dépendances.
- `0.1.0-dev` désigne la version de travail initiale, non livrée.
- Pour les livraisons `X.Y.Z` : `Z` pour les corrections, `Y` pour les fonctionnalités compatibles, `X` pour les changements incompatibles. Pendant la phase `0.x`, documenter explicitement toute incompatibilité.
- Première livraison prévue : `0.1.0`, après confirmation du périmètre et vérifications. Aucune date n’est fixée.
- Le tag d’une livraison reprend le numéro avec le préfixe `v`, par exemple `v0.1.0`. Aucun tag n’est créé lors de cette initialisation.

## À chaque séance de travail

1. Choisir une tâche et la passer « En cours » avant de la modifier.
2. Renseigner [CHANGELOG.md](CHANGELOG.md) et ajouter une séance à [JOURNAL.md](JOURNAL.md).
3. Noter les vérifications et les blocages ; passer la tâche « À vérifier » si ses critères ne sont pas encore validés.
4. Passer les tâches vérifiées « Terminé » et actualiser [PROGRESSION.md](PROGRESSION.md), notamment la prochaine action et les étapes.
5. Mettre à jour la date de ce document. Modifier `VERSION` lors d’un changement de version de travail ou de la préparation d’une livraison.

Ne jamais inscrire de mots de passe, de clés API ou de données personnelles réelles dans ces fichiers.
