<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241110183759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contrat (id INT AUTO_INCREMENT NOT NULL, designation_id INT NOT NULL, date_promettant DATETIME DEFAULT NULL, date_beneficiaire DATETIME DEFAULT NULL, date_maitre DATETIME DEFAULT NULL, repertoir VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_60349993FAC7D83F (designation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contrat_personne_physique (contrat_id INT NOT NULL, personne_physique_id INT NOT NULL, INDEX IDX_51BC62761823061F (contrat_id), INDEX IDX_51BC627654472AC9 (personne_physique_id), PRIMARY KEY(contrat_id, personne_physique_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contrat_personne_morale (contrat_id INT NOT NULL, personne_morale_id INT NOT NULL, INDEX IDX_78D4F4341823061F (contrat_id), INDEX IDX_78D4F43435FE3BF6 (personne_morale_id), PRIMARY KEY(contrat_id, personne_morale_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE designation (id INT AUTO_INCREMENT NOT NULL, numero_bien VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, etage VARCHAR(255) NOT NULL, superficie INT NOT NULL, superficie_text VARCHAR(255) NOT NULL, numero_divise VARCHAR(255) NOT NULL, titre_foncier VARCHAR(255) NOT NULL, titre_foncier_mere VARCHAR(255) NOT NULL, indivision VARCHAR(255) NOT NULL, residence VARCHAR(255) NOT NULL, montant_ttc DOUBLE PRECISION NOT NULL, montant_ttc_text VARCHAR(255) NOT NULL, tva DOUBLE PRECISION NOT NULL, tva_text VARCHAR(255) NOT NULL, montant_ht DOUBLE PRECISION NOT NULL, montant_ht_text VARCHAR(255) NOT NULL, delai INT NOT NULL, nombre_salon INT NOT NULL, nombre_chambre INT NOT NULL, nombre_cuisine INT NOT NULL, nombre_sallon_de_bain INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partner (id INT AUTO_INCREMENT NOT NULL, personne_physique_id INT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, mariage_date DATETIME NOT NULL, mariage_place VARCHAR(255) NOT NULL, INDEX IDX_312B3E1654472AC9 (personne_physique_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE personne_morale (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE personne_physique (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, cin VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, situation VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, prenom_pere VARCHAR(255) NOT NULL, prenom_mere VARCHAR(255) NOT NULL, genre VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_personne_physique (role_id INT NOT NULL, personne_physique_id INT NOT NULL, INDEX IDX_1CC2EE5DD60322AC (role_id), INDEX IDX_1CC2EE5D54472AC9 (personne_physique_id), PRIMARY KEY(role_id, personne_physique_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_personne_morale (role_id INT NOT NULL, personne_morale_id INT NOT NULL, INDEX IDX_99D00180D60322AC (role_id), INDEX IDX_99D0018035FE3BF6 (personne_morale_id), PRIMARY KEY(role_id, personne_morale_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_60349993FAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('ALTER TABLE contrat_personne_physique ADD CONSTRAINT FK_51BC62761823061F FOREIGN KEY (contrat_id) REFERENCES contrat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contrat_personne_physique ADD CONSTRAINT FK_51BC627654472AC9 FOREIGN KEY (personne_physique_id) REFERENCES personne_physique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contrat_personne_morale ADD CONSTRAINT FK_78D4F4341823061F FOREIGN KEY (contrat_id) REFERENCES contrat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contrat_personne_morale ADD CONSTRAINT FK_78D4F43435FE3BF6 FOREIGN KEY (personne_morale_id) REFERENCES personne_morale (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE partner ADD CONSTRAINT FK_312B3E1654472AC9 FOREIGN KEY (personne_physique_id) REFERENCES personne_physique (id)');
        $this->addSql('ALTER TABLE role_personne_physique ADD CONSTRAINT FK_1CC2EE5DD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_personne_physique ADD CONSTRAINT FK_1CC2EE5D54472AC9 FOREIGN KEY (personne_physique_id) REFERENCES personne_physique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_personne_morale ADD CONSTRAINT FK_99D00180D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_personne_morale ADD CONSTRAINT FK_99D0018035FE3BF6 FOREIGN KEY (personne_morale_id) REFERENCES personne_morale (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_60349993FAC7D83F');
        $this->addSql('ALTER TABLE contrat_personne_physique DROP FOREIGN KEY FK_51BC62761823061F');
        $this->addSql('ALTER TABLE contrat_personne_physique DROP FOREIGN KEY FK_51BC627654472AC9');
        $this->addSql('ALTER TABLE contrat_personne_morale DROP FOREIGN KEY FK_78D4F4341823061F');
        $this->addSql('ALTER TABLE contrat_personne_morale DROP FOREIGN KEY FK_78D4F43435FE3BF6');
        $this->addSql('ALTER TABLE partner DROP FOREIGN KEY FK_312B3E1654472AC9');
        $this->addSql('ALTER TABLE role_personne_physique DROP FOREIGN KEY FK_1CC2EE5DD60322AC');
        $this->addSql('ALTER TABLE role_personne_physique DROP FOREIGN KEY FK_1CC2EE5D54472AC9');
        $this->addSql('ALTER TABLE role_personne_morale DROP FOREIGN KEY FK_99D00180D60322AC');
        $this->addSql('ALTER TABLE role_personne_morale DROP FOREIGN KEY FK_99D0018035FE3BF6');
        $this->addSql('DROP TABLE contrat');
        $this->addSql('DROP TABLE contrat_personne_physique');
        $this->addSql('DROP TABLE contrat_personne_morale');
        $this->addSql('DROP TABLE designation');
        $this->addSql('DROP TABLE partner');
        $this->addSql('DROP TABLE personne_morale');
        $this->addSql('DROP TABLE personne_physique');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE role_personne_physique');
        $this->addSql('DROP TABLE role_personne_morale');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
