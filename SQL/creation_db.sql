CREATE DATABASE IF NOT EXISTS ecoride;
USE ecoride;

-- ROLES
CREATE TABLE roles (
    idRole INT AUTO_INCREMENT PRIMARY KEY,
    libel VARCHAR(50) NOT NULL
);

-- USERS
CREATE TABLE users (
    idUser INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    firstName VARCHAR(50) NOT NULL,
    pseudo VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    photo VARCHAR(255),
    credits INT UNSIGNED NOT NULL DEFAULT 20,
    is_driver BOOLEAN NOT NULL DEFAULT FALSE,
    is_passenger BOOLEAN NOT NULL DEFAULT TRUE,
    is_suspended BOOLEAN NOT NULL DEFAULT FALSE,
    idRole INT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idRole) REFERENCES roles(idRole)
        ON DELETE SET NULL
);

-- BRANDS
CREATE TABLE brands (
    idBrand INT AUTO_INCREMENT PRIMARY KEY,
    libel VARCHAR(50) NOT NULL
);

-- VEHICULES
CREATE TABLE vehicules (
    idVehicule INT AUTO_INCREMENT PRIMARY KEY,
    idDriver INT NOT NULL,
    idBrand INT NOT NULL,
    placesNbr TINYINT UNSIGNED NOT NULL,
    model VARCHAR(50) NOT NULL,
    color VARCHAR(50) NOT NULL,
    registration VARCHAR(50) NOT NULL,
    firstRegistration DATE NOT NULL,
    energy VARCHAR(50) NOT NULL,

    FOREIGN KEY (idDriver) REFERENCES users(idUser)
        ON DELETE CASCADE,

    FOREIGN KEY (idBrand) REFERENCES brands(idBrand)
        ON DELETE RESTRICT
);

-- COVOITURAGES
CREATE TABLE covoiturages (
    idCovoiturage INT AUTO_INCREMENT PRIMARY KEY,
    idDriver INT NOT NULL,
    idVehicule INT NOT NULL,
    price INT UNSIGNED NOT NULL,
    placesNbr TINYINT UNSIGNED NOT NULL,
    travelTime INT UNSIGNED NOT NULL,
    departureTime DATETIME NOT NULL,
    arrivalTime DATETIME NOT NULL,
    placeDeparture VARCHAR(100) NOT NULL,
    placeArrival VARCHAR(100) NOT NULL,
    statut ENUM(
        'PREVU',
        'EN_COURS',
        'TERMINE',
        'ANNULE'
    ) NOT NULL DEFAULT 'PREVU',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idDriver) REFERENCES users(idUser)
        ON DELETE CASCADE,

    FOREIGN KEY (idVehicule) REFERENCES vehicules(idVehicule)
        ON DELETE CASCADE
);

-- RESERVATIONS
CREATE TABLE reservations (
    idReservation INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT NOT NULL,
    idCovoiturage INT NOT NULL,
    statut ENUM(
        'EN_ATTENTE',
        'CONFIRMEE',
        'ANNULEE',
        'TERMINEE',
        'PROBLEME'
    ) NOT NULL DEFAULT 'EN_ATTENTE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idUser) REFERENCES users(idUser)
        ON DELETE CASCADE,

    FOREIGN KEY (idCovoiturage) REFERENCES covoiturages(idCovoiturage)
        ON DELETE CASCADE
);

-- NOTICES
CREATE TABLE notices (
    idNotice INT AUTO_INCREMENT PRIMARY KEY,
    idReservation INT NOT NULL,
    rating TINYINT NOT NULL,
    comment_notice TEXT NOT NULL,
    statut ENUM(
        'EN_ATTENTE',
        'VALIDE',
        'REFUSE'
    ) NOT NULL DEFAULT 'EN_ATTENTE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idReservation) REFERENCES reservations(idReservation)
        ON DELETE CASCADE
);

-- CREDIT_TRANSACTIONS
CREATE TABLE credit_transactions (
    idTransaction INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT,
    idReservation INT,
    amount INT UNSIGNED NOT NULL,
    type ENUM('DEBIT','CREDIT') NOT NULL,
    reason ENUM(
        'BONUS_INSCRIPTION',
        'PAIEMENT_RESERVATION',
        'COMMISSION_PLATEFORME',
        'PAIEMENT_CONDUCTEUR',
        'REMBOURSEMENT',
        'AJUSTEMENT_ADMIN'
    ) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idUser) REFERENCES users(idUser)
        ON DELETE SET NULL,

    FOREIGN KEY (idReservation) REFERENCES reservations(idReservation)
        ON DELETE SET NULL
);
