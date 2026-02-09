# 📚 Médiathèque - Application de Gestion (Symfony 7.2)

Une application web complète pour la gestion d'une médiathèque, développée avec Symfony 7.2, EasyAdmin 4, et une interface moderne.

## 🚀 Installation & Démarrage

1.  **Prérequis** : PHP 8.2+, Composer, Symfony CLI, Base de données (MySQL/MariaDB).
2.  **Cloner et Installer** :
    ```bash
    git clone <url_du_repo>
    cd mediatheque
    composer install
    ```
3.  **Base de Données** :
    Configurer `.env.local` puis :
    ```bash
    symfony console doctrine:database:create
    symfony console doctrine:migrations:migrate
    ```
4.  **Jeux de Données (Fixtures)** :
    ```bash
    symfony console doctrine:fixtures:load --no-interaction
    ```
5.  **Lancer le serveur** :
    ```bash
    symfony server:start
    ```
    Accéder à : [http://127.0.0.1:8000](http://127.0.0.1:8000)

## 🔑 Comptes de Test

| Rôle | Email | Mot de passe | Accès |
| :--- | :--- | :--- | :--- |
| **Administrateur** | `admin@mediatheque.fr` | `Admin123!` | Backend Complet + Gestion Users |
| **Bibliothécaire** | `biblio@mediatheque.fr` | `Biblio123!` | Backend (Sauf Users) + Dashboard |
| **Adhérent** | `jean.dupont@email.fr` | `Jean123!` | Espace Adhérent + Catalogue |
| **Adhérent** | `sophie.martin@email.fr` | `Sophie123!` | Espace Adhérent + Catalogue |

## 🌟 Fonctionnalités

### 🟢 Partie Publique (Frontend)
- **Catalogue** : Recherche par titre/auteur, filtres par type (Livre, DVD, etc.) et disponibilité.
- **Design** : Interface responsive, mode sombre par défaut, animations douces.
- **Espace Adhérent** : Dashboard personnel, liste des emprunts en cours (avec alertes retard), historique complet.

### 🔴 Partie Administration (Backend EasyAdmin)
- **Dashboard** : KPIs temps réel (Adhérents actifs, Emprunts, Retards), Graphiques interactifs (Chart.js).
- **Gestion Documents** : CRUD complet, état du stock.
- **Gestion Adhérents** : Suivi des inscriptions, historique des prêts.
- **Gestion Emprunts** :
  - **Création** : Vérification automatique des règles (Adhérent actif ? Document disponible ? Pas de retards ?).
  - **Retour** : Libération immédiate du document, calcul automatique du statut (En retard / Terminé).
- **Sécurité** : Gestion des utilisateurs et rôles (Strictement réservé à l'Admin).

## 🛠 Architecture Technique

- **Framework** : Symfony 7.2
- **Admin Generator** : EasyAdmin 4
- **ORM** : Doctrine
- **Base de données** : MySQL / MariaDB
- **Frontend** : Twig, CSS Natif (Variables CSS), AssetMapper (Pas de Webpack/Node.js requis).
- **Sécurité** : Voters (`EmpruntVoter`, `AdherentVoter`), Hashage de mots de passe, Firewalls stricts.

## 📝 Règles de Gestion

1.  **Disponibilité** : Un document emprunté devient automatiquement indisponible. Il redevient disponible au retour.
2.  **Retards** : Un emprunt est marqué "En retard" si la date de retour prévue est dépassée.
3.  **Blocage** : Un adhérent ayant des retards ou étant inactif ne peut pas emprunter de nouveaux documents (Vérifié par `EmpruntService`).
4.  **Emprunt** : Durée par défaut de 3 semaines (modifiable dans le Service).

## 👥 Auteurs

Projet réalisé pour le BTS SIO.
par christ

