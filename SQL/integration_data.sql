USE ecoride;

INSERT INTO roles (libel) VALUES
('UTILISATEUR'),
('EMPLOYE'),
('ADMIN');

INSERT INTO users (
  name, firstName, pseudo, email, password_hash, photo,
  credits, is_driver, is_passenger, is_suspended, idRole, created_at
) VALUES
('Admin', 'EcoRide', 'admin', 'admin@ecoride.fr', '$2y$10$adminhash', NULL, 0, 0, 0, 0, (SELECT idRole FROM roles WHERE libel='ADMIN'), NOW()),
('Employe', 'EcoRide', 'employe', 'employe@ecoride.fr', '$2y$10$employehash', NULL, 0, 0, 0, 0, (SELECT idRole FROM roles WHERE libel='EMPLOYE'), NOW()),
('Clerima', 'Kevin', 'kevin', 'kevin@mail.fr', '$2y$10$kevinhash', 'uploads/users/kevin.jpg', 20, 1, 1, 0, (SELECT idRole FROM roles WHERE libel='UTILISATEUR'), NOW()),
('Dupont', 'Marie', 'marie', 'marie@mail.fr', '$2y$10$mariehash', NULL, 20, 0, 1, 0, (SELECT idRole FROM roles WHERE libel='UTILISATEUR'), NOW()),
('Martin', 'Lucas', 'lucas', 'lucas@mail.fr', '$2y$10$lucashash', NULL, 20, 1, 0, 0, (SELECT idRole FROM roles WHERE libel='UTILISATEUR'), NOW());

INSERT INTO brands (libel) VALUES
('Tesla'),
('Renault'),
('Peugeot');

INSERT INTO vehicules (
  idDriver, idBrand, placesNbr, model, color, registration, firstRegistration, energy
) VALUES
(
  (SELECT idUser FROM users WHERE email='kevin@mail.fr'),
  (SELECT idBrand FROM brands WHERE libel='Tesla'),
  4, 'Model 3', 'Blanc', 'AA-123-AA', '2022-03-15', 'ELECTRIQUE'
),
(
  (SELECT idUser FROM users WHERE email='lucas@mail.fr'),
  (SELECT idBrand FROM brands WHERE libel='Renault'),
  3, 'Clio', 'Gris', 'BB-456-BB', '2019-09-10', 'ESSENCE'
);

INSERT INTO covoiturages (
  idDriver, idVehicule, price, placesNbr, travel_duration,
  departureTime, arrivalTime, placeDeparture, placeArrival, statut, created_at
) VALUES
(
  (SELECT idUser FROM users WHERE email='kevin@mail.fr'),
  (SELECT idVehicule FROM vehicules WHERE registration='AA-123-AA'),
  12, 3, 55,
  '2026-03-01 08:30:00', '2026-03-01 09:25:00', 'Paris', 'Versailles', 'PREVU', NOW()
),
(
  (SELECT idUser FROM users WHERE email='lucas@mail.fr'),
  (SELECT idVehicule FROM vehicules WHERE registration='BB-456-BB'),
  8, 2, 70,
  '2026-03-02 18:00:00', '2026-03-02 19:10:00', 'Conflans-Sainte-Honorine', 'La Défense', 'PREVU', NOW()
);

INSERT INTO reservations (idUser, idCovoiturage, statut, created_at) VALUES
(
  (SELECT idUser FROM users WHERE email='marie@mail.fr'),
  (SELECT idCovoiturage FROM covoiturages
     WHERE departureTime='2026-03-01 08:30:00'
       AND idDriver=(SELECT idUser FROM users WHERE email='kevin@mail.fr')
  ),
  'CONFIRMEE',
  NOW()
),
(
  (SELECT idUser FROM users WHERE email='marie@mail.fr'),
  (SELECT idCovoiturage FROM covoiturages
     WHERE departureTime='2026-03-02 18:00:00'
       AND idDriver=(SELECT idUser FROM users WHERE email='lucas@mail.fr')
  ),
  'EN_ATTENTE',
  NOW()
);

INSERT INTO notices (idReservation, rating, comment_notice, statut, created_at) VALUES
(
  (SELECT r.idReservation
   FROM reservations r
   JOIN users u ON u.idUser = r.idUser
   JOIN covoiturages c ON c.idCovoiturage = r.idCovoiturage
   WHERE u.email='marie@mail.fr'
     AND c.departureTime='2026-03-01 08:30:00'
  ),
  5,
  'Trajet tres agreable, conducteur ponctuel.',
  'EN_ATTENTE',
  NOW()
);

INSERT INTO credit_transactions (idUser, idReservation, amount, type, reason, created_at) VALUES
(
  (SELECT idUser FROM users WHERE email='marie@mail.fr'),
  NULL,
  20,
  'CREDIT',
  'BONUS_INSCRIPTION',
  NOW()
),
(
  (SELECT idUser FROM users WHERE email='marie@mail.fr'),
  (SELECT r.idReservation
   FROM reservations r
   JOIN users u ON u.idUser = r.idUser
   JOIN covoiturages c ON c.idCovoiturage = r.idCovoiturage
   WHERE u.email='marie@mail.fr'
     AND c.departureTime='2026-03-01 08:30:00'
  ),
  12,
  'DEBIT',
  'PAIEMENT_RESERVATION',
  NOW()
),
(
  NULL,
  (SELECT r.idReservation
   FROM reservations r
   JOIN users u ON u.idUser = r.idUser
   JOIN covoiturages c ON c.idCovoiturage = r.idCovoiturage
   WHERE u.email='marie@mail.fr'
     AND c.departureTime='2026-03-01 08:30:00'
  ),
  2,
  'CREDIT',
  'COMMISSION_PLATEFORME',
  NOW()
),
(
  (SELECT idUser FROM users WHERE email='kevin@mail.fr'),
  (SELECT r.idReservation
   FROM reservations r
   JOIN users u ON u.idUser = r.idUser
   JOIN covoiturages c ON c.idCovoiturage = r.idCovoiturage
   WHERE u.email='marie@mail.fr'
     AND c.departureTime='2026-03-01 08:30:00'
  ),
  10,
  'CREDIT',
  'PAIEMENT_CONDUCTEUR',
  NOW()
);
