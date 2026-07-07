# 🖼️ Maquettes — Vite & Gourmand

Trois écrans clés maquettés en deux niveaux de fidélité, en version **bureau (1440 px)** et **mobile (375 px)** — conformément au livrable ECF « wireframes & mockups, 3 maquettes bureautiques et 3 maquettes mobiles ».

Les couleurs et la typographie des mockups appliquent la [charte graphique](../charte-graphique.md).

| Écran | Wireframe bureau | Wireframe mobile | Mockup bureau | Mockup mobile |
|---|---|---|---|---|
| Accueil | [voir](wireframes/desktop-accueil.svg) | [voir](wireframes/mobile-accueil.svg) | [voir](mockups/desktop-accueil.svg) | [voir](mockups/mobile-accueil.svg) |
| Vue globale des menus | [voir](wireframes/desktop-menus.svg) | [voir](wireframes/mobile-menus.svg) | [voir](mockups/desktop-menus.svg) | [voir](mockups/mobile-menus.svg) |
| Détail d'un menu | [voir](wireframes/desktop-menu-detail.svg) | [voir](wireframes/mobile-menu-detail.svg) | [voir](mockups/desktop-menu-detail.svg) | [voir](mockups/mobile-menu-detail.svg) |

## Pourquoi ces trois écrans ?

Ils couvrent le **parcours principal** du visiteur (découvrir → filtrer → consulter → commander) et concentrent les exigences fortes du cahier des charges : filtres dynamiques sans rechargement, mise en évidence des conditions du menu, avis validés en page d'accueil.

L'enchaînement complet des écrans est schématisé dans [docs/conception/diagrammes.md](../conception/diagrammes.md) (§4).

## Partis pris mobile

- Menu de navigation replié en **burger**, icône compte toujours visible.
- Filtres regroupés dans un **accordéon** avec badge du nombre de filtres actifs + « chips » supprimables.
- Sur le détail d'un menu : **barre de commande fixe en bas d'écran** (prix + CTA toujours visibles).
- Avis et galerie en **carrousel** (swipe).

## Export pour les livrables PDF

Les SVG s'ouvrent dans n'importe quel navigateur → impression PDF ou capture PNG pour la charte graphique et le dossier projet (prévu en Phase 10).
