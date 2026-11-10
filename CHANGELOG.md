# Historique des modifications — Gestion des DMA

Ce journal résume les changements du projet. Le détail technique reste dans Git.

## Non publié

### Ajouts

- 2026-11-10 : création des fichiers `VERSION`, `CHANGELOG.md` et `SUIVI_PROJET.md` pour suivre les versions, les modifications et l’avancement.
- Ajout de `JOURNAL.md` pour les séances et de `PROGRESSION.md` pour l’état global ; centralisation des tâches dans `SUIVI_PROJET.md`.
- Ajout des liens de suivi dans le README.

### Vérifications

- Fichiers de suivi et liens locaux vérifiés.
- Aucun test de l’application exécuté pour cette modification documentaire.

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
