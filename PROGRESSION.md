# Progression — Gestion des DMA

Dernière mise à jour : **2026-11-11**

## Où en est-on ?

- Version de travail : **0.1.0-dev**, définie dans [VERSION](VERSION).
- Phase : espaces de tous les rôles harmonisés ; recette fonctionnelle avant livraison à poursuivre.
- Base de reprise : commit `b3971f5`, branche `main`.
- Dépôt récupéré en local et ouvert dans VS Code.
- Environnement local démarré avec PHP et le serveur MySQL déjà installé, sans Docker. Base existante `bd_demande1` connectée : 28 utilisateurs et 34 demandes au contrôle initial.
- Page de connexion et fichiers CSS/JS vérifiés (HTTP 200). Toutes les migrations du dépôt sont déjà appliquées. La commande `migrate --seed` a ensuite été exécutée avec succès : `RoleSeeder` et `UserSeeder`, 28 utilisateurs, 34 demandes et 9 rôles après exécution. Les espaces de chaque rôle ont ensuite été vérifiés.
- Dashboard administrateur refondu (DMA-010) : recherche, filtres, pagination, consultation et affichage mobile vérifiés. Après revue : accents orange SIR ajoutés et textes explicatifs secondaires retirés.
- Modes jour/nuit disponibles dans la navbar admin (DMA-011), préférence mémorisée et rendu mobile vérifié.
- Autres pages admin harmonisées (DMA-012) : utilisateurs, demandes, fiche DMA, profil et notifications. Douze tests réussis (62 assertions) dans une base isolée.
- Charte de référence : vert `#2E8B57`, orange `#FF8C00`. Voir [CHARTE_GRAPHIQUE.md](CHARTE_GRAPHIQUE.md).
- Ajustement visuel : mode nuit noir brillant et badges de demande plus vifs ; contrastes vérifiés et charte mise à jour.
- Cartes admin et limites de sections retravaillées : contours renforcés, bandeaux de titre, repères colorés et espacement cohérent sur ordinateur/mobile.
- Point de contrôle admin poussé sur GitHub : `afd10d3` sur `main`.
- Espace demandeur refondu (DMA-013), avec 20 tests réussis (126 assertions). La refonte est regroupée dans le point de contrôle demandeur sur `main`.
- Les 11 structures spécialisées existantes sont désormais accessibles dans le formulaire ; incohérence de nom de table corrigée.
- Rôle exploitation livré : DMA-014, 24 tests réussis (159 assertions) ; point de contrôle dédié sur `main`.
- Rôle DTS livré : DMA-015, 27 tests réussis (178 assertions) ; point de contrôle dédié sur `main`.
- Rôle structures spécialisées livré : DMA-016, 30 tests réussis (206 assertions) ; point de contrôle dédié sur `main`.
- Rôle contrôle avancé livré : DMA-017, 33 tests réussis (229 assertions) ; point de contrôle dédié sur `main`.
- Rôle service technique livré : DMA-018, 34 tests réussis (253 assertions) ; point de contrôle dédié sur `main`.
- Rôle chef de structure livré : DMA-019, 35 tests réussis (274 assertions) ; point de contrôle dédié sur `main`.
- Rôle réception livré : DMA-020, 37 tests réussis (320 assertions) ; point de contrôle dédié sur `main`.
- Fiche de consultation : libellés et valeurs différenciés, séparations ajoutées ; compilation et rendu jour/nuit vérifiés, correction locale.
- Diagramme d’avancement ajouté à chaque fiche DMA (DMA-021), adapté aux thèmes et au mobile ; 53 tests réussis, 432 assertions. Disponible en local.
- Notifications du circuit raccordées (DMA-022) : prochains intervenants, suivi du demandeur, structures ciblées, lecture et compteur automatique ; 60 tests réussis, 498 assertions. Disponible en local.
- Prochaine action : recette métier avec le porteur du projet, identification des parties encore manquantes et nettoyage des routes historiques restantes.

## État des fonctionnalités

« Présent dans le code » ne signifie pas « testé et terminé ».

| Partie | État constaté | Reste à faire |
| --- | --- | --- |
| Connexion, utilisateurs et rôles | Espaces et accès couverts par tests isolés | Compléter la recette métier des comptes existants |
| Création et modification des demandes | Vérifiées avec et sans pièces jointes | Recette utilisateur |
| Circuit de validation | Transitions et affectations des structures testées | Recette métier et audit des routes anciennes |
| Travaux du service technique | Début et fin distincts, dates et compte rendu testés | Recette utilisateur |
| Clôture et réception | Réception clôture définitivement après travaux, contrôle final possible avant réception | Recette métier |
| Tableaux de bord et notifications | Espaces harmonisés pour les neuf rôles, notifications de suivi vérifiées | Compléter la recette métier |
| Autres parties manquantes | À préciser | Recueillir les besoins avant de créer les tâches |

## Étapes vers la première livraison

| Étape | Tâches de référence | État | Condition de validation |
| --- | --- | --- | --- |
| Organiser le suivi | DMA-009 | Terminé | Fichiers de suivi créés et liens vérifiés |
| Définir les parties manquantes | DMA-001 | À préciser | Périmètre et circuit métier confirmés |
| Démarrer en local | DMA-002 | Terminé | Application accessible et procédure documentée |
| Corriger et compléter le parcours | DMA-003 à DMA-007 | À vérifier | Critères de chaque tâche vérifiés |
| Valider la première livraison | DMA-008 | À faire | Parcours complet vérifié, anomalies bloquantes corrigées |

Les statuts détaillés et les critères de fin se trouvent dans [SUIVI_PROJET.md](SUIVI_PROJET.md). Mettre ce résumé à jour quand une tâche change l’état d’une étape.

## Points à clarifier

- Quelles parties ne sont pas encore créées ?
- Quelles règles supplémentaires de périmètre métier doivent être appliquées aux équipes ? Chef : visibilité après exploitation ; réception : clôture définitive après travaux, règles confirmées.
- Quels scénarios doivent être opérationnels pour la première livraison ?

Le périmètre étant encore à préciser, aucun pourcentage global ni date de fin ne sont annoncés.

## Dernière séance

Refonte des rôles exploitation à réception (DMA-014 à DMA-020), 37 tests réussis et 320 assertions sur base isolée, avec scénario complet. Voir [JOURNAL.md](JOURNAL.md) et [DEMARRAGE_LOCAL.md](DEMARRAGE_LOCAL.md).
