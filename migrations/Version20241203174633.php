<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203174633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE procuration CHANGE date_mandant date_mandant DATETIME DEFAULT NULL, CHANGE date_mandataire date_mandataire DATETIME DEFAULT NULL, CHANGE date_maitre date_maitre DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE procuration CHANGE date_mandant date_mandant DATETIME NOT NULL, CHANGE date_mandataire date_mandataire DATETIME NOT NULL, CHANGE date_maitre date_maitre DATETIME NOT NULL');
    }
}
