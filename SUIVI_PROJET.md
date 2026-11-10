# Suivi du projet — Gestion des DMA

Dernière mise à jour : **2026-11-10**

## Documents de référence

- [PROGRESSION.md](PROGRESSION.md) : état actuel, étapes et prochaine action.
- [JOURNAL.md](JOURNAL.md) : historique des séances, décisions et vérifications.
- [CHANGELOG.md](CHANGELOG.md) : changements apportés au projet.
- [VERSION](VERSION) : numéro de version de travail.

Ce fichier conserve la liste de référence des tâches et leurs critères de fin.

## Travail à réaliser

Statuts : **À préciser**, **À faire**, **En cours**, **Bloqué**, **À vérifier**, **Terminé**. Une tâche n’est terminée qu’après vérification de ses critères de fin.

| ID | Priorité | Tâche | Statut | Critère de fin |
| --- | --- | --- | --- | --- |
| DMA-001 | Haute | Définir les parties manquantes et le circuit métier | À préciser | Fonctionnalités et étapes confirmées avec le porteur du projet |
| DMA-002 | Haute | Préparer et démarrer l’environnement local | Terminé | Application accessible et démarrage documenté sans secrets |
| DMA-003 | Haute | Nettoyer les routes et contrôler les accès | À faire | Routes cohérentes, accès autorisés et interdits vérifiés pour les rôles concernés |
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
| DMA-010 | Haute | Refonte du tableau de bord administrateur | Terminé | Recherche, filtres, pagination et consultation vérifiés ; rendu ordinateur et mobile contrôlé ; tests des indicateurs réussis |
| DMA-009 | Normale | Mettre en place les fichiers de suivi | Terminé | Version, journal, suivi et liens README créés et relus |

## Constats de la première lecture

Constats initiaux et état après les corrections des rôles.

- `routes/web.php` : routes et noms déclarés plusieurs fois, avec des protections différentes ; certaines actions visent des méthodes absentes du contrôleur ciblé.
- `DemandeController::store` : problème de redirection et d’arrêt après le premier fichier corrigé dans DMA-012/DMA-013 ; cas sans fichier et avec plusieurs fichiers couverts par tests isolés.
- DTS et structures : décision DTS unique et affectation explicite des comptes aux structures corrigées ; décisions multiples et refus couverts par tests isolés.
- Clôture et réception : parcours raccordé ; réception clôture définitivement après travaux. Le demandeur suit la réception sans pouvoir clôturer. Le contrôle avancé peut vérifier les travaux avant réception.

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
