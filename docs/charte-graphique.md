# 🎨 Charte graphique — Vite & Gourmand

> Version source de la charte graphique. L'export PDF pour les livrables sera généré depuis ce document (Phase 10).
> Identité recherchée : **artisanal, chaleureux, haut de gamme accessible** — un traiteur bordelais avec 25 ans de savoir-faire.

## 1. Palette de couleurs

| Rôle | Nom | Hex | Usage |
|---|---|---|---|
| **Primaire** | Bordeaux | `#7B2D35` | Header, boutons principaux, titres, liens |
| Primaire foncé | Bordeaux profond | `#5E2129` | Hover des boutons, footer |
| **Secondaire** | Crème | `#FAF4EA` | Fond des pages |
| **Accent** | Or ambré | `#C99B3F` | Prix, étoiles des avis, badges, soulignements décoratifs |
| Neutre foncé | Charbon | `#2D2A26` | Texte courant |
| Neutre clair | Blanc cassé | `#FFFFFF` | Cartes, fonds de formulaires |
| Fonctionnel | Vert validation | `#2E7D32` | Messages de succès, statut « terminée » |
| Fonctionnel | Rouge alerte | `#B3261E` | Erreurs de formulaire, conditions du menu mises en évidence |

### Variables CSS (à reprendre dans `style.css`)

```css
:root {
  --bordeaux:        #7B2D35;
  --bordeaux-fonce:  #5E2129;
  --creme:           #FAF4EA;
  --or:              #C99B3F;
  --charbon:         #2D2A26;
  --blanc:           #FFFFFF;
  --succes:          #2E7D32;
  --alerte:          #B3261E;
}
```

### Contrastes (conformité RGAA — ratio minimum 4,5:1 pour le texte)

| Combinaison | Ratio approx. | Verdict |
|---|---|---|
| Charbon `#2D2A26` sur Crème `#FAF4EA` | ≈ 12,9:1 | ✅ Texte courant |
| Bordeaux `#7B2D35` sur Crème `#FAF4EA` | ≈ 7,6:1 | ✅ Titres, liens |
| Blanc `#FFFFFF` sur Bordeaux `#7B2D35` | ≈ 8,4:1 | ✅ Boutons |
| Or `#C99B3F` sur Crème | ≈ 2,4:1 | ⚠️ **Jamais pour du texte** — décoratif uniquement (icônes accompagnées de texte, bordures) |
| Charbon sur Or `#C99B3F` | ≈ 5,4:1 | ✅ Badges (texte foncé sur fond or) |

## 2. Typographie

| Usage | Police | Graisse | Fallback |
|---|---|---|---|
| Titres (h1–h3) | **Playfair Display** | 600–700 | Georgia, serif |
| Texte courant, formulaires, boutons | **Lato** | 400 / 700 | Arial, Helvetica, sans-serif |

- Source : Google Fonts (à **auto-héberger** en production — RGPD, pas d'appel à un CDN tiers).
- Taille de base : `16px` (1rem) ; échelle : h1 `2.5rem`, h2 `2rem`, h3 `1.5rem`, texte `1rem`, mentions `0.875rem`.
- Interlignage : `1.6` pour le texte courant (lisibilité / RGAA).

```css
h1, h2, h3 { font-family: "Playfair Display", Georgia, serif; }
body       { font-family: "Lato", Arial, Helvetica, sans-serif; font-size: 1rem; line-height: 1.6; }
```

## 3. Composants

### Boutons
- **Primaire** : fond bordeaux, texte blanc, coins arrondis `8px`, padding `12px 24px`. Hover : bordeaux foncé.
- **Secondaire** : bordure bordeaux 2px, texte bordeaux, fond transparent. Hover : fond bordeaux, texte blanc.
- Focus visible (RGAA) : anneau `3px` or autour du bouton.

### Cartes menu
- Fond blanc, ombre douce `0 2px 8px rgba(45,42,38,.08)`, coins arrondis `12px`.
- Image en haut, titre Playfair, prix en or foncé sur badge, bouton « Voir le détail ».

### Encart « Conditions du menu »
- Fond crème rosé, **bordure gauche 4px rouge alerte** `#B3261E`, icône ⚠️ — impossible à manquer (exigence du cahier des charges §3.5).

### Formulaires
- Labels **toujours visibles** au-dessus des champs (jamais de placeholder seul — RGAA).
- Erreurs : texte rouge alerte + bordure rouge + message explicite sous le champ.

## 4. Iconographie & images

- Icônes : set simple et cohérent (lignes fines), toujours accompagnées d'un libellé ou d'un `aria-label`.
- Photos : plats lumineux, tons chauds, fond naturel (bois, lin) — cohérence avec la palette.
- Toute image porte un attribut `alt` pertinent.

## 5. Grille & responsive

- **Mobile-first**, breakpoints : `768px` (tablette), `1024px` (desktop).
- Conteneur max : `1200px` centré.
- Cartes menu : 1 colonne (mobile), 2 (tablette), 3 (desktop).
