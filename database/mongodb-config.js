// ============================================================
// Vite & Gourmand — Base NoSQL (MongoDB)
// Collection de statistiques alimentant l'espace administrateur :
//   - nombre de commandes par menu (graphique comparatif)
//   - chiffre d'affaires par menu, filtrable par menu et par période
// Usage : mongosh --quiet database/mongodb-config.js
//         (exécution en mode fichier : s'arrête avec un code d'erreur si un problème survient)
// ============================================================

const db = connect("mongodb://localhost:27017/vite_et_gourmand_stats");

// Repartir d'une collection propre (script rejouable, comme les fichiers SQL)
db.stats_commandes.drop();

// Création avec validation de schéma : garantit la qualité des données
// insérées par le back-end à chaque changement de statut d'une commande
db.createCollection("stats_commandes", {
  validator: {
    $jsonSchema: {
      bsonType: "object",
      required: ["numero_commande", "menu_id", "titre_menu", "date_commande",
                 "nb_personnes", "prix_total", "statut"],
      properties: {
        numero_commande: { bsonType: "string", description: "clé de rapprochement avec MySQL" },
        menu_id:       { bsonType: "int",    description: "menu_id MySQL correspondant" },
        titre_menu:    { bsonType: "string" },
        date_commande: { bsonType: "date" },
        // "number" accepte int, long, double et decimal : un montant rond (468.0)
        // est sérialisé en Int32 par mongosh/PHP, un "double" strict le rejetterait
        nb_personnes:  { bsonType: "number", minimum: 1 },
        prix_total:    { bsonType: "number", minimum: 0 },
        statut:        { enum: ["cree", "accepte", "en_preparation", "en_livraison",
                                "livre", "attente_materiel", "terminee", "annulee"] }
      }
    }
  }
});

// Index : rapprochement MySQL, agrégations par menu et filtres par période
db.stats_commandes.createIndex({ numero_commande: 1 }, { unique: true });
db.stats_commandes.createIndex({ menu_id: 1 });
db.stats_commandes.createIndex({ date_commande: 1 });

// ------------------------------------------------------------
// Données de démonstration — miroir des commandes de fixtures.sql
// ------------------------------------------------------------
db.stats_commandes.insertMany([
  {
    menu_id: NumberInt(1),
    numero_commande: "CMD-2026-0001",
    titre_menu: "Menu Noël Prestige",
    date_commande: ISODate("2026-06-01T10:15:00Z"),
    nb_personnes: NumberInt(13),
    prix_total: 468.0,
    statut: "terminee"
  },
  {
    menu_id: NumberInt(3),
    numero_commande: "CMD-2026-0002",
    titre_menu: "Menu Classique Bordelais",
    date_commande: ISODate("2026-06-05T14:30:00Z"),
    nb_personnes: NumberInt(6),
    prix_total: 159.72,
    statut: "terminee"
  },
  {
    menu_id: NumberInt(4),
    numero_commande: "CMD-2026-0003",
    titre_menu: "Menu Végétarien du Marché",
    date_commande: ISODate("2026-06-10T09:00:00Z"),
    nb_personnes: NumberInt(6),
    prix_total: 150.9,
    statut: "terminee"
  },
  {
    menu_id: NumberInt(5),
    numero_commande: "CMD-2026-0004",
    titre_menu: "Menu Vegan Découverte",
    date_commande: ISODate("2026-06-15T16:45:00Z"),
    nb_personnes: NumberInt(12),
    prix_total: 261.0,
    statut: "terminee"
  },
  {
    menu_id: NumberInt(6),
    numero_commande: "CMD-2026-0005",
    titre_menu: "Menu Évènement Grand Format",
    date_commande: ISODate("2026-06-12T11:20:00Z"),
    nb_personnes: NumberInt(25),
    prix_total: 628.47,
    statut: "attente_materiel"
  },
  {
    menu_id: NumberInt(2),
    numero_commande: "CMD-2026-0006",
    titre_menu: "Menu Pâques Gourmand",
    date_commande: ISODate("2026-07-02T18:00:00Z"),
    nb_personnes: NumberInt(6),
    prix_total: 180.0,
    statut: "cree"
  }
]);

// Exemple d'agrégation utilisée par l'espace admin (nb de commandes + CA par menu) :
// db.stats_commandes.aggregate([
//   { $match: { statut: { $ne: "annulee" },
//               date_commande: { $gte: ISODate("2026-06-01"), $lte: ISODate("2026-06-30") } } },
//   { $group: { _id: "$menu_id",
//               titre: { $first: "$titre_menu" },
//               nb_commandes: { $sum: 1 },
//               chiffre_affaires: { $sum: "$prix_total" } } },
//   { $sort: { nb_commandes: -1 } }
// ]);

print("Collection stats_commandes créée : " + db.stats_commandes.countDocuments() + " documents.");
