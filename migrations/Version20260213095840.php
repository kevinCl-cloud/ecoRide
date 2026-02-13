<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213095840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE brand (id INT AUTO_INCREMENT NOT NULL, libel VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE covoiturage (id INT AUTO_INCREMENT NOT NULL, price INT NOT NULL, places_nbr SMALLINT NOT NULL, travel_time INT NOT NULL, departure_time TIME NOT NULL, arrival_time TIME NOT NULL, place_departure VARCHAR(100) NOT NULL, place_arrival VARCHAR(100) NOT NULL, statut VARCHAR(255) NOT NULL, create_at DATETIME NOT NULL, id_driver_id INT NOT NULL, id_vehicule_id INT NOT NULL, INDEX IDX_28C79E894377852E (id_driver_id), INDEX IDX_28C79E895258F8E6 (id_vehicule_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE credit_transaction (id INT AUTO_INCREMENT NOT NULL, amount INT NOT NULL, transaction_type VARCHAR(255) NOT NULL, transaction_reason VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, id_user_id INT NOT NULL, id_reservation_id INT NOT NULL, INDEX IDX_5E1DE3E179F37AE5 (id_user_id), INDEX IDX_5E1DE3E185542AE1 (id_reservation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notice (id INT AUTO_INCREMENT NOT NULL, rating SMALLINT NOT NULL, comment_notice LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, id_reservation_id INT NOT NULL, INDEX IDX_480D45C285542AE1 (id_reservation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, id_user_id INT NOT NULL, id_covoiturage_id INT NOT NULL, INDEX IDX_42C8495579F37AE5 (id_user_id), INDEX IDX_42C849555F01A896 (id_covoiturage_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, libel VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, first_name VARCHAR(50) NOT NULL, pseudo VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, photo VARCHAR(255) DEFAULT NULL, credits INT NOT NULL, is_driver TINYINT NOT NULL, is_passenger TINYINT NOT NULL, is_supended TINYINT NOT NULL, created_at DATETIME NOT NULL, Role INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649F75B2554 (Role), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicule (id INT AUTO_INCREMENT NOT NULL, places_nbr SMALLINT NOT NULL, model VARCHAR(50) NOT NULL, color VARCHAR(50) NOT NULL, registration VARCHAR(50) NOT NULL, first_registration DATE NOT NULL, energy VARCHAR(50) NOT NULL, id_driver_id INT NOT NULL, id_brand_id INT NOT NULL, INDEX IDX_292FFF1D4377852E (id_driver_id), INDEX IDX_292FFF1D142E3C9D (id_brand_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E894377852E FOREIGN KEY (id_driver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E895258F8E6 FOREIGN KEY (id_vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE credit_transaction ADD CONSTRAINT FK_5E1DE3E179F37AE5 FOREIGN KEY (id_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE credit_transaction ADD CONSTRAINT FK_5E1DE3E185542AE1 FOREIGN KEY (id_reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE notice ADD CONSTRAINT FK_480D45C285542AE1 FOREIGN KEY (id_reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495579F37AE5 FOREIGN KEY (id_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849555F01A896 FOREIGN KEY (id_covoiturage_id) REFERENCES covoiturage (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F75B2554 FOREIGN KEY (Role) REFERENCES role (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1D4377852E FOREIGN KEY (id_driver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1D142E3C9D FOREIGN KEY (id_brand_id) REFERENCES brand (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E894377852E');
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E895258F8E6');
        $this->addSql('ALTER TABLE credit_transaction DROP FOREIGN KEY FK_5E1DE3E179F37AE5');
        $this->addSql('ALTER TABLE credit_transaction DROP FOREIGN KEY FK_5E1DE3E185542AE1');
        $this->addSql('ALTER TABLE notice DROP FOREIGN KEY FK_480D45C285542AE1');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495579F37AE5');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849555F01A896');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F75B2554');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1D4377852E');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1D142E3C9D');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE covoiturage');
        $this->addSql('DROP TABLE credit_transaction');
        $this->addSql('DROP TABLE notice');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vehicule');
    }
}
