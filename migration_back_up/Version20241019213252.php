<?php

declare(strict_types=1);

namespace migration_back_up;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019213252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE designation (id INT AUTO_INCREMENT NOT NULL, numero_bien VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, etage VARCHAR(255) NOT NULL, superficie INT NOT NULL, superficie_text VARCHAR(255) NOT NULL, numero_divise VARCHAR(255) NOT NULL, titre_foncier VARCHAR(255) NOT NULL, titre_foncier_mere VARCHAR(255) NOT NULL, indivision VARCHAR(255) NOT NULL, residence VARCHAR(255) NOT NULL, montant_ttc DOUBLE PRECISION NOT NULL, montant_ttc_text VARCHAR(255) NOT NULL, tva DOUBLE PRECISION NOT NULL, tva_text VARCHAR(255) NOT NULL, montant_ht DOUBLE PRECISION NOT NULL, montant_ht_text VARCHAR(255) NOT NULL, delai INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE designation');
    }
}
