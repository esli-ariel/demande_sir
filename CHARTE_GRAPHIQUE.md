# Charte graphique — Gestion des DMA

Couleurs de référence validées le **2026-11-10** :

| Couleur | Valeur | Usage |
| --- | --- | --- |
| Vert SIR | `#2E8B57` | Actions principales, carte d’activité, accent de navigation et graphiques |
| Orange SIR | `#FF8C00` | Actions secondaires importantes, onglet actif, bordures et accents graphiques |

Les variables `--sir-green` et `--sir-orange` de `resources/css/admin.css` sont les références pour l’espace admin. Les noms Tailwind `sir-green` et `sir-orange` utilisent les mêmes valeurs.

## Lisibilité

- Sur le vert de marque : texte foncé `#001006` pour les petits libellés et boutons.
- Sur l’orange de marque : texte foncé `#163B25`.
- Pour les petits liens verts sur fond clair : variante foncée `#236B43`.
- En mode nuit : fond noir `#050505`, navbar noire et surfaces noir brillant avec des reflets discrets. Réserver le vert et l’orange aux actions et aux accents ; textes clairs pour les contenus et les liens.
- Les teintes de statut (refus, validation, travaux, réception) conservent leur sens ; le texte identifie toujours le statut en plus de la couleur.

Ne pas utiliser de texte blanc de petite taille sur `#FF8C00` ou `#2E8B57` : le contraste est insuffisant. Les couleurs de marque et leurs variantes de lisibilité remplissent des rôles distincts.

## Application

Charte appliquée aux dashboards, à la navbar et aux pages de l’administration et du demandeur. Étendre ces mêmes références aux autres rôles lors de leur refonte.

## Statuts des demandes

| Groupe | Fond | Texte |
| --- | --- | --- |
| Brouillon | Ardoise `#475569` | Blanc |
| En validation | Orange SIR `#FF8C00` | Vert très foncé `#163B25` |
| Travaux | Bleu `#2563EB` | Blanc |
| Clôture / réception attendue | Violet `#7C3AED` | Blanc |
| Clôturée et réceptionnée | Vert SIR `#2E8B57` | Très foncé `#001006` |
| Refusée | Rouge `#DC2626` | Blanc |
| Statut à vérifier | Rose `#BE185D` | Blanc |

Ces fonds restent identiques en jour et en nuit. Le libellé permet d’identifier le statut sans dépendre uniquement de la couleur. Les badges des autres rôles reprennent cette palette via `Demande::getStatutBadgeClass()`.

## Cartes et limites des sections

- Cartes de statistiques : même structure, titre et icône en tête, valeur alignée et repère coloré en bas. Espacement de 20 px sur ordinateur et 12 px sur mobile.
- En mode nuit : cartes en charbon avec reflet discret, panneaux `#17171A`, en-têtes `#27272D` et contours `#62626C` pour distinguer les blocs du fond noir.
- Les titres des panneaux ont un repère orange et un séparateur. Les lignes de tableau alternent entre deux fonds sombres et gardent une bordure visible.
- La version jour conserve les mêmes limites de sections avec des surfaces blanches, des bandeaux gris clair et des contours renforcés.
