# Progression — Gestion des DMA

Dernière mise à jour : **2026-11-10**

## Où en est-on ?

- Version de travail : **0.1.0-dev**, définie dans [VERSION](VERSION).
- Phase : reprise du projet et identification des parties à terminer.
- Base de reprise : commit `b3971f5`, branche `main`.
- Dépôt récupéré en local et ouvert dans VS Code.
- Première lecture partielle du code effectuée. L’application n’a pas encore été démarrée ni testée pendant cette reprise.
- Prochaine action : préciser les fonctionnalités manquantes et le circuit métier attendu avec le porteur du projet, puis choisir le premier lot à développer.

## État des fonctionnalités

« Présent dans le code » ne signifie pas « testé et terminé ».

| Partie | État constaté | Reste à faire |
| --- | --- | --- |
| Connexion, utilisateurs et rôles | Présent dans le code, non validé | Vérifier les accès de chaque rôle |
| Création et modification des demandes | Présent, anomalies repérées | Corriger puis vérifier avec et sans pièces jointes |
| Circuit de validation | Présent, incohérences repérées | Confirmer les étapes métier et vérifier les transitions |
| Travaux du service technique | Présent, non validé | Vérifier les dates, les travaux et les statuts |
| Clôture et réception | Présent, incohérences repérées | Clarifier les acteurs et raccorder les actions |
| Tableaux de bord et notifications | Présent, non validé | Vérifier les données et les destinataires |
| Autres parties manquantes | À préciser | Recueillir les besoins avant de créer les tâches |

## Étapes vers la première livraison

| Étape | Tâches de référence | État | Condition de validation |
| --- | --- | --- | --- |
| Organiser le suivi | DMA-009 | Terminé | Fichiers de suivi créés et liens vérifiés |
| Définir les parties manquantes | DMA-001 | À préciser | Périmètre et circuit métier confirmés |
| Démarrer en local | DMA-002 | À faire | Application accessible et procédure documentée |
| Corriger et compléter le parcours | DMA-003 à DMA-007 | À faire | Critères de chaque tâche vérifiés |
| Valider la première livraison | DMA-008 | À faire | Parcours complet vérifié, anomalies bloquantes corrigées |

Les statuts détaillés et les critères de fin se trouvent dans [SUIVI_PROJET.md](SUIVI_PROJET.md). Mettre ce résumé à jour quand une tâche change l’état d’une étape.

## Points à clarifier

- Quelles parties ne sont pas encore créées ?
- Quel est le circuit exact de validation, de refus, de clôture et de réception ?
- Quels scénarios doivent être opérationnels pour la première livraison ?

Le périmètre étant encore à préciser, aucun pourcentage global ni date de fin ne sont annoncés.

## Dernière séance

Mise en place du suivi documentaire. Voir [JOURNAL.md](JOURNAL.md#2026-11-10--initialisation-du-suivi).
