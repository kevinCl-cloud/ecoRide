# 🌿 EcoRide

EcoRide est une plateforme de covoiturage écologique développée avec Symfony.  
Le projet a été réalisé dans le cadre du Titre Professionnel Développeur Web et Web Mobile.

L’objectif de l’application est de permettre aux utilisateurs de proposer et réserver des trajets en voiture tout en favorisant une mobilité économique et respectueuse de l’environnement.

---

## 🚀 Fonctionnalités principales

- Inscription et authentification sécurisée
- Gestion des rôles (Utilisateur / Chauffeur / Admin)
- Recherche de covoiturages par ville et date
- Filtres (prix maximum, note, durée, aspect écologique)
- Création de trajets par les chauffeurs
- Réservation avec gestion des crédits
- Historique des trajets
- Espace personnel utilisateur
- Modification du profil
- Interface administrateur

---

## 🛠️ Technologies utilisées

- PHP 8+
- Symfony 6
- Twig
- Doctrine ORM
- MySQL
- Bootstrap 5
- Git / GitHub

---

## 📦 Installation du projet

### 1️⃣ Cloner le dépôt

```bash
git clone https://github.com/kevinCl-cloud/ecoRide.git
cd ecoRide
```

### 2️⃣ Installer les dépendances

```bash
composer install
```

### 3️⃣ Configuration de l’environnement

Créer un fichier `.env.local` et configurer la base de données :

```env
DATABASE_URL="mysql://USER:PASSWORD@127.0.0.1:3306/ecoride"
```

### 4️⃣ Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5️⃣ Lancer le serveur

Avec Symfony CLI :

```bash
symfony server:start
```

Ou avec PHP :

```bash
php -S localhost:8000 -t public
```

---

## 📂 Structure du projet

```
config/         Configuration Symfony
public/         Point d'entrée de l'application
src/            Controllers, Entities, Forms, etc.
templates/      Fichiers Twig
migrations/     Migrations de la base de données
```

---

## 👤 Comptes de test (exemple)

Admin :
- Email : admin@ecoride.com
- Mot de passe : password

Utilisateur :
- Email : user@ecoride.com
- Mot de passe : password

---

## 🔐 Sécurité

- Mots de passe hashés
- Accès protégé aux routes sensibles
- Vérification des rôles
- Protection CSRF via Symfony
- Validation des données via FormType

---

## 📈 Gestion du versionnement

Le projet respecte une organisation Git professionnelle :

- main → branche principale stable
- develop → branche de développement
- feature/* → branches par fonctionnalité

---

## 🎓 Contexte pédagogique

Ce projet couvre :

- Développement front-end
- Développement back-end
- Mise en place d'une base de données relationnelle
- Sécurisation d'une application web
- Gestion de projet via Kanban

---

## 📌 Auteur

Kevin Clerima  
Projet réalisé dans le cadre de la formation Développeur Web et Web Mobile.

---

## 📄 Licence

Projet réalisé à des fins pédagogiques.
